<?php
namespace fw;

final class Router {

	private static $list = array();

	public static function registerTemplate(string $urlPath, string $templatePath) {
		if (! $urlPath || ! $templatePath) {
			throw new \Exception();
		}
		
		if (! is_file(Core::PATH_VIEW . '/' . $templatePath)) {
			throw new \Exception('Template não encontrado: ' . $templatePath);
		}
		
		$config['templatePath'] = $templatePath;
		$config['controllerClass'] = null;
		$config['methodName'] = null;
		$config['applicationPath'] = null;
		$config['applicationName'] = null;
		
		self::$list[$urlPath] = $config;
	}

	public static function registerController(string $urlPath, string $controllerClass, ?string $templatePath = null): void {
		if (! $urlPath || ! $controllerClass) {
			throw new \Exception();
		}
		
		if ($templatePath) {
			if (! is_file(Core::PATH_VIEW . '/' . $templatePath)) {
				throw new \Exception('Template não encontrado: ' . $templatePath);
			}
		}
		
		if (! is_file($controllerClass . '.php')) {
			throw new \Exception('Controlador não encontrado: ' . $controllerClass);
		}
		
		$applicationPath = $controllerClass::getApplicationPath();
		$applicationName = null;
		
		if ($applicationPath) {
			if (! is_dir(Core::PATH_VIEW . '/' . $applicationPath)) {
				throw new \Exception('Aplicação não encontrada: ' . $applicationPath);
			}
			
			$pos = strripos($applicationPath, '/');
			if (! $pos) {
				$applicationName = $applicationPath;
			} else {
				$applicationName = substr($applicationPath, $pos + 1);
			}
		}
		
		$config['templatePath'] = $templatePath;
		$config['controllerClass'] = $controllerClass;
		$config['methodName'] = 'init';
		$config['applicationPath'] = $applicationPath;
		$config['applicationName'] = $applicationName;
		
		self::$list[$urlPath] = &$config;
		
		$reflectionClass = new \ReflectionClass($controllerClass);
		$methods = $reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC);
		foreach ($methods as $method) {
			$methodName = $method->getName();
			if ($method->isStatic() || $methodName === '__construct' || $method->getDeclaringClass()->getName() === ComponentController::class) {
				continue;
			}
			
			if ($methodName === 'init') {
				$qnt = $method->getNumberOfParameters();
				if ($qnt > 0) {
					$config['numberParameters'] = $qnt;
					
					$qnt += substr_count($urlPath, '/');
					if (! isset(self::$list[$qnt])) {
						$urls = array();
						self::$list[$qnt] = &$urls;
					} else {
						$urls = &self::$list[$qnt];
					}
					
					$urls[$urlPath] = &$config;
				}
				
				continue;
			}
			
			$_config = $config;
			$_config['methodName'] = $methodName;
			
			self::$list[$urlPath . '/' . $methodName] = $_config;
		}
	}

	public static function registerApplication(string $urlPath, string $templatePath, string $applicationPath) {
		if (! $urlPath || ! $templatePath || ! $applicationPath) {
			throw new \Exception();
		}
		
		if (! is_file(Core::PATH_VIEW . '/' . $templatePath)) {
			throw new \Exception('Template não encontrado: ' . $templatePath);
		}
		
		if (! is_dir(Core::PATH_VIEW . '/' . $applicationPath)) {
			throw new \Exception('Aplicação não encontrada: ' . $applicationPath);
		}
		
		$pos = strripos($applicationPath, '/');
		if (! $pos) {
			$applicationName = $applicationPath;
		} else {
			$applicationName = substr($applicationPath, $pos + 1);
		}
		
		$config['templatePath'] = $templatePath;
		$config['controllerClass'] = null;
		$config['methodName'] = null;
		$config['applicationPath'] = $applicationPath;
		$config['applicationName'] = $applicationName;
		
		self::$list[$urlPath] = $config;
	}

	public static function getData(string $url): ?array {
		$config = self::$list[$url] ?? null;
		if (! $config) {
			$qnt = substr_count($url, '/');
			$list = &self::$list[substr_count($url, '/')];
			
			if ($list) {
				foreach ($list as $key => $config) {
					if (strpos($url, $key) === 0) {
						$values = array_slice(explode('/', $url), $config['numberParameters']);
						return Array($config, $values);
					}
				}
			}
		}
		
		return Array($config, null);
	}
}

