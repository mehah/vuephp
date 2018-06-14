<?php
namespace fw;

use fw\http\HttpSession;
use fw\lib\HTMLMinifier;
use fw\lib\MatthiasMullie\Minify\JS;
use fw\router\Router;
if (! defined('LIBXML_HTML_NODEFDTD')) {
	define("LIBXML_HTML_NODEFDTD", 4);
	define("LIBXML_HTML_NOIMPLIED", 8192);
}

abstract class Core {

	private const PATH_SRC = 'src';

	public const PATH_BUILD = 'build';

	public const PATH_VIEW = 'view';

	public const PATH_PUBLIC = 'public';

	private const PATH_PROJECT_CONFIG = self::PATH_SRC . '/config.php';
	
	protected static $pageCodeResponse = array(); 

	protected static $CONTEXT_PATH;

	protected static $FW_JS_FILES = Array(
		'fw/js/events.js',
		'fw/js/crossbrowser.js',
		'fw/js/vue.js',
		'fw/js/vue.mixin.js',
		'fw/js/vue.util.js',
		'fw/js/vue.directive.js',
		'fw/js/vue.custom.js',
		'fw/js/vue.modalError.js'
	);

	private static $template;

	static function init(): void {
		spl_autoload_register(function ($class_name) {
			include str_replace('\\', '/', $class_name) . '.php';
		});
		
		if (is_file(self::PATH_PROJECT_CONFIG)) {
			include self::PATH_PROJECT_CONFIG;
		}
		
		if (! is_file($pathRouter = self::PATH_SRC . '/router.php')) {
			throw new \Exception('Não foi encontrado o arquivo de configuração de routeamento em: src/router.php');
		}
		
		if (isset($_REQUEST['url'])) {
			$APP_URL = $_REQUEST['url'];
			$APP_URL = substr($APP_URL, - 1) === '/' ? substr($APP_URL, 0, - 1) : $APP_URL;
		} else {
			$APP_URL = '/';
		}
		
		if (Project::liveReloadEnabled() && $APP_URL === 'check') {
			set_time_limit(0);
			
			$lastMTime = filemtime(self::PATH_PROJECT_CONFIG);
			
			self::$template = unserialize(file_get_contents(self::PATH_BUILD . '/template.src'));
			while (! self::hasModification($_REQUEST['app'] ?? '/')) {
				if ($lastMTime < filemtime(self::PATH_PROJECT_CONFIG)) {
					self::delete_files(self::PATH_BUILD);
					break;
				}
				
				if (connection_aborted()) {
					exit();
				}
				usleep(500000);
			}
			exit('1');
		}
		
		if (! is_dir(self::PATH_BUILD)) {
			mkdir(self::PATH_BUILD, 0777, true);
		}
		
		if (Project::getCheckModification()) {
			include $pathRouter;
		} else {
			$pathRouterSource = self::PATH_BUILD . '/router.src';
			
			$reflectionClass = new \ReflectionClass(Router::class);
			$propList = $reflectionClass->getProperty('list');
			$propList->setAccessible(true);
			
			if (! is_file($pathRouterSource)) {
				include $pathRouter;
				$list = $propList->getValue();
				file_put_contents($pathRouterSource, serialize($list));
			} else {
				$propList->setValue(unserialize(file_get_contents($pathRouterSource)));
			}
			
			$propList->setAccessible(false);
		}
		
		list ($data, $methodArguments) = Router::getData($APP_URL);
		
		$APP_CACHED = ! Project::getCheckModification() && ($_REQUEST['cached'] ?? false) === 'true';
		if (! $APP_CACHED) {
			if (! $data) {
				http_response_code(404);
				if($pagePath = self::$pageCodeResponse[404] ?? null) {
					readfile($pagePath);
					exit();
				} else {
					exit('PAGE NOT FOUND');
				}				
			}
		}
		
		self::$CONTEXT_PATH = str_replace('\\', '/', dirname($_SERVER['PHP_SELF'])) . '/';
		
		$templatePath = $data['templatePath'];
		$controllerClass = $data['controllerClass'];
		$requestedMethod = $data['methodName'];
		$applicationPath = $data['applicationPath'];
		$accessRule = $data['accessRule'];
		if ($applicationPath) {
			$applicationName = $data['applicationName'];
			$applicationPath = '/' . $applicationPath . '/' . $applicationName;
			$applicationFullPath = self::PATH_VIEW . $applicationPath;
			$appPathHTML = $applicationFullPath . '.html';
		}
		
		$controllerInicialized = $requestedMethod && $requestedMethod !== 'init';
		
		session_start();
		
		$methodsList = $vueDT = '{}';
		if ($controllerClass) {
			$session = &self::getSession();
			
			$controller = $controllerInicialized ? $session[$controllerClass] : null;
			
			if (! $controller) {
				$controller = new $controllerClass($data);
				if (! ($controller instanceof ComponentController)) {
					die('O controlador ' . $controllerClass . ' precisa extender a classe ComponentController.');
				}
				$session[$controllerClass] = $controller;
			}
			
			$reflectionClass = new \ReflectionClass($controllerClass);
			
			if ($reflectionClass->hasMethod($requestedMethod)) {
				$propData = new \ReflectionProperty(ComponentController::class, '_VUE_DATA');
				$propData->setAccessible(true);
				$vueDT = $propData->getValue($controller);
				
				$reflectionMethod = $reflectionClass->getMethod($requestedMethod);
				
				if (! $methodArguments) {
					if (isset($_REQUEST['args'])) {
						$methodArguments = json_decode($_REQUEST['args']);
					}
				}
				
				if ($methodArguments) {
					$params = $reflectionMethod->getParameters();
					
					$list = Array();
					for ($i = - 1, $s = count($params); ++ $i < $s;) {
						$classType = $params[$i]->getType();
						
						if ($classType && ! $classType->isBuiltin()) {
							$className = $classType->getName();
							$arg = new $className();
							self::setClassProps($methodArguments[$i], $arg);
							
							$list[] = $arg;
						} elseif ($arg = ($methodArguments[$i] ?? null)) {
							$list[] = $arg;
						}
					}
					
					$vueDT->ds = $reflectionMethod->invokeArgs($controller, $list);
				} else {
					$vueDT->ds = $reflectionMethod->invoke($controller);
				}
				
				$propData->setValue($controller, new \stdClass());
			}
			
			if ($accessRule && ! self::hasAccess($accessRule, $requestedMethod)) {
				http_response_code(401);
				if($pagePath = self::$pageCodeResponse[401] ?? null) {
					readfile($pagePath);
					exit();
				} else {
					exit('Você não está autorizado a executar essa ação.');
				}
			}
			
			$methodsList = '';			
			if (! $APP_CACHED) {
				$controllerBuildPath = self::PATH_BUILD . $applicationPath . '.methods.js';
				if (! is_file($controllerBuildPath) || filemtime(str_replace('\\', '/', $controllerClass) . '.php') > filemtime($controllerBuildPath)) {
					$methods = $reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC);
					foreach ($methods as $method) {
						$methodName = $method->getName();
						if ($method->isStatic() || $methodName === 'init' || $methodName === '__construct' || $method->getDeclaringClass()->getName() === ComponentController::class) {
							continue;
						}
						
						if ($methodsList) {
							$methodsList .= ',';
						}
						
						$countParam = $method->getNumberOfParameters() + 97;
						$args = '';
						for ($i = 96; ++ $i < $countParam;) {
							$args .= chr($i) . ',';
						}
						
						$methodsList .= '$' . $methodName . ':function(' . $args . 'z){this.request("' . self::$CONTEXT_PATH . $applicationName . '/' . $methodName . '", ' . $args . 'z);}';
					}
					$methodsList = '{' . $methodsList . '}';
					
					self::checkDir($controllerBuildPath);
					file_put_contents($controllerBuildPath, $methodsList);
				} else {
					$methodsList = file_get_contents($controllerBuildPath);
				}
			}
		}
		
		$httpX = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? null;
		$IS_AJAX = $httpX && strtolower($httpX) === 'xmlhttprequest';
		
		if (! $IS_AJAX && $templatePath) {
			$templateBuildPath = self::PATH_BUILD . '/' . $templatePath;
			
			if (Project::getCheckModification() || ! is_file($templateBuildPath)) {
				self::checkDir($templateBuildPath);
				{ // Javascript FW
					$jsFWBuildPath = self::PATH_BUILD . '/$fw.js';
					$lastTime = 0;
					foreach (self::$FW_JS_FILES as $fileName) {
						$modifiedDate = filemtime($fileName);
						if ($lastTime < $modifiedDate) {
							$lastTime = $modifiedDate;
						}
					}
					
					if (! is_file($jsFWBuildPath) || $lastTime > filemtime($jsFWBuildPath)) {
						$js = new JS();
						foreach (self::$FW_JS_FILES as $fileName) {
							$js->add($fileName);
						}
						
						$js->add('Vue.CONTEXT_PATH = "' . self::$CONTEXT_PATH . '";');
						$js->minify($jsFWBuildPath);
					}
				}
				
				$template = new Template($templatePath);
				file_put_contents(self::PATH_BUILD . '/template.src', serialize($template));
				
				if (! is_file($templateBuildPath) || $template->getModifiedDate() > filemtime($templateBuildPath)) {
					$prependScriptHTML = '<script type="text/javascript" src="' . self::$CONTEXT_PATH . $jsFWBuildPath . '" charset="' . Project::getChatset() . '"></script>';
					
					$html = $prependScriptHTML . $template->html;
					
					$options = array(
						'shift_script_tags_to_bottom' => true,
						'compression_mode' => 'all_whitespace',
						'show_signature' => false
					);
					
					file_put_contents($templateBuildPath, HTMLMinifier::process($html, $options));
				}
			}
			
			readfile($templateBuildPath);
		}
		
		if ($controllerInicialized) {
			if ($vueDT->d ?? $vueDT->ds ?? $vueDT->rd ?? null) {
				echo json_encode($vueDT);
			}
		} else {
			if ($APP_CACHED) {
				$dataComponent = isset($vueDT->d) ? json_encode($vueDT->d) : '{}';
				$dataRoot = isset($vueDT->rd) ? json_encode($vueDT->rd) : '{}';
				echo 'Vue.processApp("' . $applicationPath . '", null, ' . $dataComponent . ', ' . $dataRoot . ');' . (Project::liveReloadEnabled() ? 'Vue.liveReload.checkModification("' . $applicationPath . '");' : '');
			} else {
				if ($applicationPath) {
					$fileBuildPath = self::PATH_BUILD . $applicationPath . '.html';
					
					if (! is_file($fileBuildPath) || filemtime($appPathHTML) > filemtime($fileBuildPath)) {
						self::checkDir($fileBuildPath);
						(new JS($appPathHTML))->minify($fileBuildPath);
					}
					$appPathHTML = $fileBuildPath;
					
					$appJS = $applicationFullPath . '.js';
					$appJSExist = is_file($appJS);
					
					$fileBuildPath = self::PATH_BUILD . $applicationPath . '.js';
					if ($appJSExist && (! is_file($fileBuildPath) || filemtime($appJS) > filemtime($fileBuildPath))) {
						self::checkDir($fileBuildPath);
						(new JS($appJS))->minify($fileBuildPath);
					}
					$appJS = $fileBuildPath;
					
					$dataComponent = isset($vueDT->d) ? json_encode($vueDT->d) : '{}';
					$dataRoot = isset($vueDT->rd) ? json_encode($vueDT->rd) : '{}';
					$script = 'Vue.processApp("' . $applicationPath . '",' . json_encode(file_get_contents($appPathHTML)) . ', ' . $dataComponent . ', ' . $dataRoot . ', ' . $methodsList . ', ' . ($appJSExist ? 'function(App) {' . file_get_contents($appJS) . '}' : 'null') . ');';
					echo $IS_AJAX ? $script : '<script>' . $script . '</script>';
				}
			}
			
			if(Project::liveReloadEnabled()) {
				$code = 'Vue.liveReload.checkModification("' . $applicationPath . '");';
				echo $IS_AJAX ? $code : '<script>' . $code . '</script>';
			}
		}
	}

	private static function checkDir(string $path): void {
		$dir = dirname($path);
		if (! is_dir($dir)) {
			mkdir($dir, 0777, true);
		}
	}

	private static function hasAccess(Array $rules, String $methodName): bool {
		$user = self::getSessionInstance()->getUserPrincipal();
		if ($user === null) {
			return false;
		}
		
		if (count($rules) > 0) {
			$rule = $rules[$methodName] ?? null;
			if(!$rule) {
				$rule = $rules['*'] ?? null;
			}
			
			if ($rule && $rule !== '*') {
				$userRules = $user->getRules();
				if ($userRules) {
					foreach ($userRules as $v) {
						if (in_array($v, $rules)) {
							return true;
						}
					}
				}
				
				return false;
			}
		}
		
		return true;
	}

	private static function setClassProps($data, $object): void {
		$defaults = (new \ReflectionClass($object))->getDefaultProperties();
		
		foreach ($defaults as $key => $value) {
			if (array_key_exists($key, $data)) {
				$value = &$data->{$key};
				if ($_ref = $object->{$key}) {
					self::setClassProps($value, $_ref);
				} else {
					$object->{$key} = $value ?? null;
				}
			}
		}
	}

	public static function &getSessionInstance(): HttpSession {
		return $_SESSION[Project::getName()]['INSTANCE'];
	}

	public static function &getSession(): iterable {
		$projectName = Project::getName();
		if (isset($_SESSION[$projectName])) {
			return $_SESSION[$projectName];
		}
		
		$session = Array(
			'INSTANCE' => new HttpSession()
		);
		
		$_SESSION[$projectName] = &$session;
		
		return $session;
	}

	private static function hasModification(string $appTargetPath): bool {
		$jsFWBuildPath = self::PATH_BUILD . '/$fw.js';
		$lastTime = 0;
		foreach (self::$FW_JS_FILES as $fileName) {
			$modifiedDate = filemtime($fileName);
			if ($lastTime < $modifiedDate) {
				$lastTime = $modifiedDate;
			}
		}
		
		if (! is_file($jsFWBuildPath) || $lastTime > filemtime($jsFWBuildPath)) {
			return true;
		}
		
		if (self::$template->hasModification()) {
			if (self::$template->hasTemplateModified()) {
				self::delete_files(Self::PATH_BUILD . '/' . self::$template->getFilePath());
			}
			
			return true;
		}
		
		$appPathHTML = self::PATH_VIEW . $appTargetPath . '.html';
		$fileBuildPath = self::PATH_BUILD . $appTargetPath . '.html';
		if (! is_file($fileBuildPath) || filemtime($appPathHTML) > filemtime($fileBuildPath)) {
			return true;
		}
		
		$appURL = self::PATH_VIEW . $appTargetPath . '.js';
		$fileBuildPath = self::PATH_BUILD . $appTargetPath . '.js';
		if (is_file($appURL) && (! is_file($fileBuildPath) || filemtime($appURL) > filemtime($fileBuildPath))) {
			return true;
		}
		
		return false;
	}

	private static function delete_files($target): void {
		if (is_dir($target)) {
			$files = glob($target . '*', GLOB_MARK); // GLOB_MARK adds a slash to directories returned
			
			foreach ($files as $file) {
				self::delete_files($file);
			}
			
			rmdir($target);
		} elseif (is_file($target)) {
			unlink($target);
		}
	}
}
