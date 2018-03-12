<?php
namespace fw;

class Core
{

    public static $PROJECT_NAME = 'ARQUITETURA';

    public static $APP_HOME_PATH = 'home';

    public static function init(): void
    {
        $TAGET_CLASS_NAME = $TARGET_NAME = null;
        $HAS_METHOD = false;
        $APP_URL = self::$APP_HOME_PATH;
        
        if (isset($_REQUEST['url'])) {
            $APP_URL = $_REQUEST['url'];
        }
        
        $exURL = explode("/", $APP_URL, 3);
        $CONTEXT_PATH = str_repeat('../', count($exURL) - 1);
        
        if (isset($exURL[1])) {
            $isNumeric = is_numeric($exURL[1]);
            if (! ($HAS_METHOD = (strlen($exURL[1]) > 0) && ! $isNumeric)) { // Se terminar com /, não executa nada.
                if (! $isNumeric) {
                    die();
                } else {
                    $_REQUEST['arg0'] = $exURL[1];
                }
            }
        }
        
        include ('fw/VueApp.php');
        include ('fw/database/DatabaseConnection.php');
        include ('fw/database/DatabaseEntity.php');
        include ('fw/database/Entity.php');
        include ('database.config.php');
        
        $TARGET_NAME = $exURL[0];
        $TAGET_CLASS_NAME = ucfirst($exURL[0]);
        
        spl_autoload_register(function ($class_name) {
            if (strpos($class_name, 'src') === false) {
                $class_name = 'src\\' . $class_name;
            }
            include $class_name . '.php';
        });
        
        session_start();
        
        $INDEX_CONTENT = '';
        $IS_AJAX = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ! empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
        
        if (! $IS_AJAX) {
            $INDEX_CONTENT = '
                <script type="text/javascript" src="' . $CONTEXT_PATH . 'fw/vue.min.js"></script>
            ' . file_get_contents('webcontent/index.html');
        }
        
        $srcPath = 'src/controller/' . $TAGET_CLASS_NAME . 'Controller.php';
        
        $methodsList = $data = '{}';
        if (file_exists($srcPath)) {
            $controllerPath = self::$PROJECT_NAME . '/controller/' . $TAGET_CLASS_NAME;
            $className = 'src\controller\\' . $TAGET_CLASS_NAME . 'Controller';
            
            $controller = null;
            if ($HAS_METHOD) {
                if (isset($_SESSION[$controllerPath])) {
                    $controller = $_SESSION[$controllerPath];
                }
            } else {
                $exURL[1] = 'init';
            }
            
            if (! $controller) {
                $_SESSION[$controllerPath] = $controller = new $className();
            }
            
            $posMethod = count($exURL) - 1;
            
            $reflectionClass = new \ReflectionClass($className);
            
            $methodsList = '';
            
            $methods = $reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC);
            foreach ($methods as $method) {
                $methodName = $method->getName();
                if ($method->isStatic() || $methodName == 'init') {
                    continue;
                }
                
                if ($methodsList) {
                    $methodsList .= ',';
                }
                
                $methodsList .= $methodName . ':function(p){this.request("' . $TARGET_NAME . '/' . $methodName . '", p);}';
            }
            
            $methodsList = '{' . $methodsList . '}';
            $methodName = $exURL[$posMethod];
            if ($reflectionClass->hasMethod($methodName)) {
                $reflectionMethod = $reflectionClass->getMethod($methodName);
                
                if (isset($_REQUEST['arg0']) && $reflectionMethod->getNumberOfParameters() == 1) {
                    $data = $_REQUEST['arg0'];
                    
                    $classType = $reflectionMethod->getParameters()[0]->getType();
                    
                    if (! $classType->isBuiltin()) {
                        $className = $classType->getName();
                        $reflectionClass = new \ReflectionClass($className);
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
            $url = 'webcontent/app/' . $TARGET_NAME . '/' . $TARGET_NAME;
            $templateURL = $url . '.html';
            if (file_exists($templateURL)) {
                $appURL = $url . '.js';
                $script = '
                    var TEMP_OBJECT = {data: ' . $data . ', methods: ' . $methodsList . '};
                    ' . (! file_exists($appURL) ? '' : '
                        TEMP_FUNC = function() {' . file_get_contents($appURL) . '};
                        TEMP_FUNC.call(TEMP_OBJECT);
                    ') . '
                        
                     document.querySelector(TEMP_OBJECT.el).innerHTML = `' . addslashes(file_get_contents($templateURL)) . '`;
        			new Vue({el : TEMP_OBJECT.el, mixins: [TEMP_OBJECT, VUE_GLOBAL]});
                ';
                
                $INDEX_CONTENT .= $IS_AJAX ? $script : '<script>'.$script.'</script>';
            }
        }
        
        echo $INDEX_CONTENT;
    }
}

