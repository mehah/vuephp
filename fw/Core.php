<?php
namespace fw;

use fw\http\HttpSession;
use fw\impl\AccessRule;
use fw\lib\MatthiasMullie\Minify\JS;
use fw\lib\MatthiasMullie\Minify\CSS;
use fw\lib\Minify_HTML;

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
		'fw/js/vue.modalError.js',
	);

	static function init(): void {
		spl_autoload_register(function ($class_name) {
			include $class_name . '.php';
		});
		
		session_start();
		
		include self::PATH_SRC . '/project.config.php';
		
		$APP_CACHED = ($_REQUEST['cached'] ?? false) === 'true';
		$APP_URL = $_REQUEST['url'] ?? Project::$defaultModule;
		$exURL = explode("/", $APP_URL, 3);
		
		$CONTEXT_PATH = str_replace('\\', '/', dirname($_SERVER['PHP_SELF'])) . '/';
		$HAS_METHOD = false;
		
		$TARGET_NAME = $exURL[0];
		$TAGET_CLASS_NAME = ucfirst($TARGET_NAME);
		
		$INDEX_CONTENT = '';
		$IS_AJAX = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ! empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
		
		if (isset($exURL[1])) {
			if (is_numeric($exURL[1])) {
				$_REQUEST['arg0'] = '[' . $exURL[1] . ']';
			} else {
				$HAS_METHOD = (strlen($exURL[1]) > 0);
			}
		}
		
		if (! file_exists(self::PATH_BUILD)) {
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
			
			$filePath = self::PATH_BUILD . '/$package.js';
			if (! file_exists($filePath) || $lastTime > filemtime($filePath)) {
				$js = new JS();
				foreach (self::$JS_FILES as $fileName) {
					$js->add($fileName);
				}
				
				$js->minify($filePath);
			}
			
			$INDEX_CONTENT .= '<script type="text/javascript" src="' . $CONTEXT_PATH . $filePath . '" charset="' . Project::$chatset . '"></script>';
			
			$url = self::PATH_VIEW . '/styles.css';
			if (file_exists($url)) {
				$filePath = self::PATH_BUILD . '/styles.css';
				$INDEX_CONTENT .= '<link rel="stylesheet" type="text/css" href="' . $CONTEXT_PATH . $filePath . '">';
				if (! file_exists($filePath) || filemtime($url) > filemtime($filePath)) {
					(new CSS($url))->minify($filePath);
				}
			}
			
			$templateURL = self::PATH_VIEW . '/index.html';
			$filePath = self::PATH_BUILD . '/index.html';
			if (! file_exists($filePath) || filemtime($templateURL) > filemtime($filePath)) {
				Minify_HTML::minifySave($templateURL, $filePath);
			}
			
			$INDEX_CONTENT .= file_get_contents(self::PATH_BUILD . '/index.html');
		}
		
		$srcPath = self::PATH_SRC . '/controller/' . $TAGET_CLASS_NAME . 'Controller.php';
		$methodsList = $vueDT = '{}';
		if (file_exists($srcPath)) {
			$session = self::getSession();
			
			$controllerPath = Project::$name . '/controller/' . $TAGET_CLASS_NAME;
			$className = self::PATH_SRC . '\controller\\' . $TAGET_CLASS_NAME . 'Controller';
			
			$controller = null;
			$requestedMethod = null;
			
			if ($HAS_METHOD) {
				$requestedMethod = $exURL[1];
				$controller = $session[$TAGET_CLASS_NAME] ?? null;
			} else {
				$requestedMethod = 'init';
			}
			
			if (! $controller) {
				$controller = new $className();
				if (! ($controller instanceof ComponentController)) {
					die('O controlador ' . $className . ' precisa extender a classe ComponentController.');
				}
				$session[$TAGET_CLASS_NAME] = $controller;
			}
			
			if ($controller instanceof AccessRule && ! self::hasAccess($controller, $requestedMethod)) {
				header('HTTP/1.1 401 Unauthorized');
				die('Você não está autorizado a executar essa ação.');
			}
			
			$reflectionClass = new \ReflectionClass($className);
			
			$methodsList = '';
			
			if (! $APP_CACHED) {
				$methods = $reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC);
				foreach ($methods as $method) {
					$methodName = $method->getName();
					if ($method->isStatic() || $methodName == 'init' || $methodName == '__construct') {
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
			}
			
			if ($reflectionClass->hasMethod($requestedMethod)) {
				$reflectionMethod = $reflectionClass->getMethod($requestedMethod);
				
				$reflectionClass = new \ReflectionClass(ComponentController::class);
				$propData = $reflectionClass->getProperty("_VUE_DATA");
				$propData->setAccessible(true);
				$vueDT = $propData->getValue($controller);
				
				if (isset($_REQUEST['arg0'])) {
					$data = json_decode($_REQUEST['arg0']);
					
					$params = $reflectionMethod->getParameters();
					
					$list = Array();
					for ($i = - 1, $s = count($params); ++ $i < $s;) {
						$classType = $params[$i]->getType();
						
						if ($classType && ! $classType->isBuiltin()) {
							$className = $classType->getName();
							$reflectionClass = new \ReflectionClass($className);
							$defaults = $reflectionClass->getDefaultProperties();
							
							$arg = new $className();
							self::setClassProps($data[$i], $arg);
							
							array_push($list, $arg);
						} elseif ($arg = ($data[$i] ?? null)) {
							array_push($list, $arg);
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
			if (isset($vueDT->d) || isset($vueDT->ds) || isset($vueDT->rd)) {
				$INDEX_CONTENT = json_encode($vueDT);
			}
		} else {
			$script = null;
			if ($APP_CACHED) {
				$dataComponent = isset($vueDT->d) ? json_encode($vueDT->d) : '{}';
				$dataRoot = isset($vueDT->rd) ? json_encode($vueDT->rd) : '{}';
				$script = 'Vue.processApp("' . $TARGET_NAME . '", null, ' . $dataComponent . ', ' . $dataRoot . ');';
			} else {
				$appTargetPath = '/app/' . $TARGET_NAME . '/' . $TARGET_NAME;
				$url = self::PATH_VIEW . $appTargetPath;
				$templateURL = $url . '.html';
				if (file_exists($templateURL)) {
					$filePath = self::PATH_BUILD . $appTargetPath . '.html';
					if (! file_exists($filePath) || filemtime($templateURL) > filemtime($filePath)) {
						if (! file_exists(dirname($filePath))) {
							mkdir(dirname($filePath), 0777, true);
						}
						(new JS($templateURL))->minify($filePath);
					}
					$templateURL = $filePath;
					
					$dataComponent = isset($vueDT->d) ? json_encode($vueDT->d) : '{}';
					$dataRoot = isset($vueDT->rd) ? json_encode($vueDT->rd) : '{}';
					$appURL = $url . '.js';
					$appJSExist = file_exists($appURL);
					
					$filePath = self::PATH_BUILD . $appTargetPath . '.js';
					if ($appJSExist && (! file_exists($filePath) || filemtime($appURL) > filemtime($filePath))) {
						if (! file_exists(dirname($filePath))) {
							mkdir(dirname($filePath), 0777, true);
						}
						(new JS($appURL))->minify($filePath);
					}
					$appURL = $filePath;
					
					$script = 'Vue.processApp("' . $TARGET_NAME . '",' . json_encode(file_get_contents($templateURL)) . ', ' . $dataComponent . ', ' . $dataRoot . ', ' . $methodsList . ', ' . ($appJSExist ? 'function(App) {' . file_get_contents($appURL) . '}' : 'null') . ');';
				}
			}
			
			if (! $IS_AJAX) {
				$url = self::PATH_VIEW . '/main.js';
				if (file_exists($url)) {
					$filePath = self::PATH_BUILD . '/main.js';
					$INDEX_CONTENT .= '<script type="text/javascript" src="' . $CONTEXT_PATH . $filePath . '" charset="' . Project::$chatset . '"></script>';
					
					if (! file_exists($filePath) || filemtime($url) > filemtime($filePath)) {
						(new JS($url))->minify($filePath);
					}
				}
			}
			
			$INDEX_CONTENT .= $IS_AJAX ? $script : '<script id="!script">Vue.CONTEXT_PATH = ' . $CONTEXT_PATH . ';Vue.modalError = ' . $CONTEXT_PATH . ';' . $script . 'document.getElementById("\!script").remove();</script>';
		}
		
		echo $INDEX_CONTENT;
	}

	private static function hasAccess(ComponentController $controller, String $methodName): bool {
		$user = Core::getSessionInstance()->getUserPrincipal();
		if ($user == null) {
			return false;
		}
		
		$controllerRules = $controller::getRules();
		
		if ($controllerRules && count($controllerRules) > 0) {
			$rule = $controllerRules[$methodName] ?? null;
			if ($rule && $rule !== "*") {
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
		$reflectionClass = new \ReflectionClass($object);
		$defaults = $reflectionClass->getDefaultProperties();
		
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
