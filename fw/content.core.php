<?php
$PROJECT_NAME = 'ARQUITETURA';

$exURL = explode("/", $_REQUEST['url']);

$srcPath = 'src/controller/' . $TAGET_CLASS_NAME . 'Controller.php';

$data = '{}';
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
    
    $posMethod = count($exURL)-1;
    
    if (method_exists($controller, $exURL[$posMethod])) {
        $reflectionMethod = new ReflectionMethod($className, $exURL[$posMethod]);
        if (isset($_REQUEST['arg0']) && $reflectionMethod->getNumberOfParameters() == 1) {
            $data = $_REQUEST['arg0'];
            
            $classType = $reflectionMethod->getParameters()[0]->getType();
            
            if (! $classType->isBuiltin()) {
                $className = $classType->getName();
                $object = new $className;
                $reflection = new ReflectionClass($className);
                $defaults = $reflection->getDefaultProperties();
                
                foreach ($defaults as $key => $value) {
                    if(array_key_exists($key, $data)) {
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
    $url = 'webcontent/app/' . $TARGET_NAME . '/' . $TARGET_NAME;
    $templateURL = $url . '.html';
    if (file_exists($templateURL)) {
        $appURL = $url . '.js';
        if (file_exists($appURL)) {
            echo '<script>
                    var TEMP_OBJECT = {data: '.$data.', methods: {}};
					TEMP_FUNC = function() {' . file_get_contents($appURL) . '};
                    TEMP_FUNC.call(TEMP_OBJECT);

					new Vue({el : "#content", data: {HTMLcontent: `' . addslashes(file_get_contents($templateURL)) . '`}});                    
					new Vue({el : "#content", mixins: [TEMP_OBJECT]});
                </script>';
        }
    }
}
?>