<?php
$PROJECT_NAME = 'ARQUITETURA';

$exURL = explode("/", $_REQUEST['url']);

$srcPath = 'src/controller/' . $TAGET_CLASS_NAME . 'Controller.php';

$methodsList = $data = '{}';

if (file_exists($srcPath)) {
    $controllerPath = $PROJECT_NAME . '/controller/' . $TAGET_CLASS_NAME;
    $className = 'src\controller\\' . $TAGET_CLASS_NAME . 'Controller';
    
    $controller = null;
    if ($HAS_METHOD) {
        $controller = $_SESSION[$controllerPath];
    } else {
        $_SESSION[$controllerPath] = $controller = new $className();
        
        $exURL[1] = 'init';
    }
    
    $posMethod = count($exURL) - 1;
    
    $reflectionClass = new ReflectionClass($className);
    
    $methodsList = '';
    
    $methods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);
    foreach ($methods as $method) {
        $methodName = $method->getName();
        if ($method->isStatic() || $methodName == 'init') {
            continue;
        }
        
        if ($methodsList) {
            $methodsList .= ',';
        }
        
        $methodsList .= $methodName . ':function(param){this.request("' . $exURL[0] . '/' . $methodName . '", param);}';
    }
    
    $methodsList = '{'.$methodsList.'}';
    
    if ($reflectionClass->hasMethod($exURL[$posMethod])) {
        $reflectionMethod = $reflectionClass->getMethod($exURL[$posMethod]);
        if (isset($_REQUEST['arg0']) && $reflectionMethod->getNumberOfParameters() == 1) {
            $data = $_REQUEST['arg0'];
            
            $classType = $reflectionMethod->getParameters()[0]->getType();
            
            if (! $classType->isBuiltin()) {
                $className = $classType->getName();
                $reflectionClass = new ReflectionClass($className);
                $defaults = $reflectionClass->getDefaultProperties();
                
                $object = new $className();
                
                foreach ($defaults as $key => $value) {
                    if (array_key_exists($key, $data)) {                        
                        $value = $data[$key];
                        $object->{$key} = $value ? $value : null;
                    }
                }
            } else {
                $object = $data;
            }
            
            $data = $reflectionMethod->invoke($controller, $object);
        } else {
            $data = $reflectionMethod->invoke($controller);
        }
        if ($data && is_object($data)) {
            $data = json_encode($data, JSON_FORCE_OBJECT);
        } else {
            $data = '{}';
        }
    }
}

if (! $HAS_METHOD) {
    $url = 'webcontent/app/' . $TARGET_NAME . '/';
    $templateURL = $url . 'view.html';
    if (file_exists($templateURL)) {
        $appURL = $url . 'controller.js';
        echo '<script>
            var TEMP_OBJECT = {data: ' . $data . ', methods: '.$methodsList.'};
            ' . (! file_exists($appURL) ? '' : '
                TEMP_FUNC = function() {' . file_get_contents($appURL) . '};
                TEMP_FUNC.call(TEMP_OBJECT);
            ') . '					

			new Vue({el : TEMP_OBJECT.el, data: {HTMLcontent: `' . addslashes(file_get_contents($templateURL)) . '`}});                    
			new Vue({el : TEMP_OBJECT.el, mixins: [TEMP_OBJECT, VUE_GLOBAL]});
        </script>';
    }
}
?>