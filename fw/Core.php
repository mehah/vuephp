<?php
namespace fw;

use fw\http\HttpSession;
use fw\impl\AccessRule;
use fw\lib\Minify_HTML;
use fw\lib\MatthiasMullie\Minify\CSS;
use fw\lib\MatthiasMullie\Minify\JS;

abstract class Core {

	private const PATH_BUILD = 'build';

	private const PATH_SRC = 'src';

	private const PATH_VIEW = 'public_html';

	protected static $JS_FILES = Array(
		'fw/js/events.js',
		'fw/js/crossbrowser.js',
		'fw/js/vue.js',
		'fw/js/vue.mixin.js',
		'fw/js/vue.util.js',
		'fw/js/vue.directive.js',
		'fw/js/vue.custom.js',
		'fw/js/vue.modalError.js'
	);

	static function init(): void {
		spl_autoload_register(function ($class_name) {
			include $class_name . '.php';
		});
		
		include self::PATH_SRC . '/project.config.php';
		
		$APP_CACHED = ($_REQUEST['cached'] ?? false) === 'true';
		
		$APP_URL = $_REQUEST['url'] ?? Project::$defaultModule;
		$exURL = explode("/", $APP_URL, 3);
		
		$TARGET_NAME = $exURL[0];
		$TAGET_CLASS_NAME = ucfirst($TARGET_NAME);
		
		$controllerPath = self::PATH_SRC . '/controller/' . $TAGET_CLASS_NAME . 'Controller.php';
		$controllerExist = is_file($controllerPath);
		
		if (! $APP_CACHED) {
			$appTargetPath = '/app/' . $TARGET_NAME . '/' . $TARGET_NAME;
			$appTargetFullPath = self::PATH_VIEW . $appTargetPath;
			$appPathHTML = $appTargetFullPath . '.html';
			$appPathHTMLExist = is_file($appPathHTML);
			
			if (! $controllerExist || ! $appPathHTMLExist) {
				http_response_code(404);
				exit('PAGE NOT FOUND');
			}
		}
		
		session_start();
		
		$ARGUMENTS = $_REQUEST['arg0'] ?? null;
		
		$CONTEXT_PATH = str_replace('\\', '/', dirname($_SERVER['PHP_SELF'])) . '/';
		$HAS_METHOD = false;
		
		$IS_AJAX = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ! empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
		
		if (isset($exURL[1])) {
			if (is_numeric($exURL[1])) {
				$ARGUMENTS = '[' . $exURL[1] . ']';
			} else {
				$HAS_METHOD = (strlen($exURL[1]) > 0);
			}
		}
		
		if (! is_dir(self::PATH_BUILD)) {
			mkdir(self::PATH_BUILD, 0777, true);
		}
		
		if (! $IS_AJAX) {
			$lastTime = 0;
			foreach (self::$JS_FILES as $fileName) {
				$modifiedDate = filemtime($fileName);
				if ($lastTime < $modifiedDate) {
					$lastTime = $modifiedDate;
				}
			}
			
			$packageBuildPath = self::PATH_BUILD . '/$package.js';
			if (! is_file($packageBuildPath) || $lastTime > filemtime($packageBuildPath)) {
				$js = new JS();
				foreach (self::$JS_FILES as $fileName) {
					$js->add($fileName);
				}
				
				$js->add('Vue.CONTEXT_PATH = "' . $CONTEXT_PATH . '";');
				$js->minify($packageBuildPath);
			}
			
			$mainViewPath = self::PATH_VIEW . '/main.js';
			$mainBuildPath = self::PATH_BUILD . '/main.js';
			$hasMainFile = is_file($mainViewPath);
			if ($hasMainFile) {
				if (! is_file($mainBuildPath) || filemtime($mainViewPath) > filemtime($mainBuildPath)) {
					(new JS($mainViewPath))->minify($mainBuildPath);
				}
			}
			
			$styleViewPath = self::PATH_VIEW . '/styles.css';
			$styleBuildPath = self::PATH_BUILD . '/styles.css';
			$hasStyleFile = is_file($styleViewPath);
			if ($hasStyleFile) {
				if (! is_file($styleBuildPath) || filemtime($styleViewPath) > filemtime($styleBuildPath)) {
					(new CSS($styleViewPath))->minify($styleBuildPath);
				}
			}
			
			$templateViewPath = self::PATH_VIEW . '/index.html';
			$templateBuildPath = self::PATH_BUILD . '/index.html';
			if (! is_file($templateBuildPath) || filemtime($templateViewPath) > filemtime($templateBuildPath)) {
				$prependScriptHTML = '<script type="text/javascript" src="' . $CONTEXT_PATH . $packageBuildPath . '" charset="' . Project::$chatset . '"></script>';
				
				if ($hasStyleFile) {
					$prependScriptHTML .= '<link rel="stylesheet" type="text/css" href="' . $CONTEXT_PATH . $styleBuildPath . '"></link>';
				}
				
				$appendScriptHTML = '';
				if ($hasMainFile) {
					$appendScriptHTML = '<script type="text/javascript" src="' . $CONTEXT_PATH . $mainBuildPath . '" charset="' . Project::$chatset . '"></script>';
				}
				
				Minify_HTML::minifySave($templateViewPath, $templateBuildPath, $prependScriptHTML, $appendScriptHTML);
			}
			
			readfile($templateBuildPath);
		}
		
		$methodsList = $vueDT = '{}';
		if ($controllerExist) {
			$session = self::getSession();
			
			$controller = null;
			$requestedMethod = null;
			
			if ($HAS_METHOD) {
				$requestedMethod = $exURL[1];
				$controller = $session[$TAGET_CLASS_NAME] ?? null;
			} else {
				$requestedMethod = 'init';
			}
			
			$className = self::PATH_SRC . '\controller\\' . $TAGET_CLASS_NAME . 'Controller';
			if (! $controller) {
				$controller = new $className();
				if (! ($controller instanceof ComponentController)) {
					die('O controlador ' . $className . ' precisa extender a classe ComponentController.');
				}
				$session[$TAGET_CLASS_NAME] = $controller;
			}
			
			if ($controller instanceof AccessRule && ! self::hasAccess($controller, $requestedMethod)) {
				http_response_code(401);
				die('Você não está autorizado a executar essa ação.');
			}
			
			$reflectionClass = new \ReflectionClass($className);
			
			$methodsList = '';
			
			if (! $APP_CACHED) {
				$controllerBuildPath = self::PATH_BUILD . $appTargetPath . '.methods.js';				
				if (! is_file($controllerBuildPath) || filemtime($controllerPath) > filemtime($controllerBuildPath)) {
					$methods = $reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC);
					foreach ($methods as $method) {
						$methodName = $method->getName();
						if ($method->isStatic() || $methodName === 'init' || $methodName === '__construct') {
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
						
						$methodsList .= '$' . $methodName . ':function(' . $args . 'z){this.request("' . $CONTEXT_PATH . $TARGET_NAME . '/' . $methodName . '", ' . $args . 'z);}';
					}
					$methodsList = '{' . $methodsList . '}';
					
					file_put_contents($controllerBuildPath, $methodsList);
				} else {
					$methodsList = file_get_contents($controllerBuildPath);
				}
			}
			
			if ($reflectionClass->hasMethod($requestedMethod)) {
				$propData = new \ReflectionProperty(ComponentController::class, '_VUE_DATA');
				$propData->setAccessible(true);
				$vueDT = $propData->getValue($controller);
				
				$reflectionMethod = $reflectionClass->getMethod($requestedMethod);
				if ($ARGUMENTS) {
					$data = json_decode($ARGUMENTS);
					
					$params = $reflectionMethod->getParameters();
					
					$list = Array();
					for ($i = - 1, $s = count($params); ++ $i < $s;) {
						$classType = $params[$i]->getType();
						
						if ($classType && ! $classType->isBuiltin()) {
							$className = $classType->getName();
							$arg = new $className();
							self::setClassProps($data[$i], $arg);
							
							$list[] = $arg;
						} elseif ($arg = ($data[$i] ?? null)) {
							$list[] = $arg;
						}
					}
					
					$vueDT->ds = $reflectionMethod->invokeArgs($controller, $list);
				} else {
					$vueDT->ds = $reflectionMethod->invoke($controller);
				}
				
				$propData->setValue($controller, new \stdClass());
			}
		}
		
		if ($HAS_METHOD) {
			if ($vueDT->d ?? $vueDT->ds ?? $vueDT->rd ?? null) {
				echo json_encode($vueDT);
			}
		} else {
			if ($APP_CACHED) {
				$dataComponent = isset($vueDT->d) ? json_encode($vueDT->d) : '{}';
				$dataRoot = isset($vueDT->rd) ? json_encode($vueDT->rd) : '{}';
				echo 'Vue.processApp("' . $TARGET_NAME . '", null, ' . $dataComponent . ', ' . $dataRoot . ');';
			} else {
				if ($appPathHTMLExist) {
					$fileBuildPath = self::PATH_BUILD . $appTargetPath . '.html';
					if (! is_file($fileBuildPath) || filemtime($appPathHTML) > filemtime($fileBuildPath)) {
						$dir = dirname($fileBuildPath);
						if (! is_dir($dir)) {
							mkdir($dir, 0777, true);
						}
						(new JS($appPathHTML))->minify($fileBuildPath);
					}
					$appPathHTML = $fileBuildPath;
					
					$appURL = $appTargetFullPath . '.js';
					$appJSExist = is_file($appURL);
					
					$fileBuildPath = self::PATH_BUILD . $appTargetPath . '.js';
					if ($appJSExist && (! is_file($fileBuildPath) || filemtime($appURL) > filemtime($fileBuildPath))) {
						$dir = dirname($fileBuildPath);
						if (! is_dir($dir)) {
							mkdir($dir, 0777, true);
						}
						(new JS($appURL))->minify($fileBuildPath);
					}
					$appURL = $fileBuildPath;
					
					$dataComponent = isset($vueDT->d) ? json_encode($vueDT->d) : '{}';
					$dataRoot = isset($vueDT->rd) ? json_encode($vueDT->rd) : '{}';
					$script = 'Vue.processApp("' . $TARGET_NAME . '",' . json_encode(file_get_contents($appPathHTML)) . ', ' . $dataComponent . ', ' . $dataRoot . ', ' . $methodsList . ', ' . ($appJSExist ? 'function(App) {' . file_get_contents($appURL) . '}' : 'null') . ');';
					echo $IS_AJAX ? $script : '<script>' . $script . '</script>';
				}
			}
		}
	}

	private static function hasAccess(ComponentController $controller, String $methodName): bool {
		$user = $controller->getSession()->getUserPrincipal();
		if ($user === null) {
			return false;
		}
		
		$controllerRules = $controller::getRules();
		
		if ($controllerRules && count($controllerRules) > 0) {
			$rule = $controllerRules[$methodName] ?? null;
			if ($rule && $rule !== '*') {
				$userRules = $user->getRules();
				if ($userRules) {
					foreach ($userRules as $v) {
						if (in_array($v, $controllerRules)) {
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

	public static function getSessionInstance(): HttpSession {
		return $_SESSION[Project::$name]['INSTANCE'];
	}

	public static function getSession(): iterable {
		return $_SESSION[Project::$name] ?? $_SESSION[Project::$name] = Array(
			'INSTANCE' => new HttpSession()
		);
	}
}
