<?php
namespace fw;

class Core
{

    public static $PROJECT_NAME;
    public static $PRINCIPAL_MODULE_NAME;

    public static function init(): void
    {
        include ('project.config.php');
        
        $TAGET_CLASS_NAME = $TARGET_NAME = null;
        $HAS_METHOD = false;
        $APP_URL = self::$PRINCIPAL_MODULE_NAME;
        
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
        
        include ('fw/TemplateController.php');
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
            $INDEX_CONTENT .= '<script type="text/javascript" src="' . $CONTEXT_PATH . 'fw/vue.min.js"></script>';
            
            $url = 'webcontent/main.js';
            if (file_exists($url)) {
                $INDEX_CONTENT .= '<script type="text/javascript" src="' . $CONTEXT_PATH .$url . '"></script>';
            }
            
            $url = 'webcontent/styles.css';
            if (file_exists($url)) {
                $INDEX_CONTENT .= '<link rel="stylesheet" type="text/css" href="'.$CONTEXT_PATH .$url.'">';
            }
            
            $INDEX_CONTENT .= file_get_contents('webcontent/index.html');
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
                $controller = new $className();
                if(!($controller instanceof TemplateController)) {
                    die('O controlador '.$className.' precisa extender a classe TemplateController.');
                }
                $_SESSION[$controllerPath] = $controller;
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
                
                $methodsList .= $methodName . ':function(p, c){this.request("' . $TARGET_NAME . '/' . $methodName . '", p, c);}';
            }
            
            $methodsList = '{' . $methodsList . '}';
            $methodName = $exURL[$posMethod];
            if ($reflectionClass->hasMethod($methodName)) {
                $reflectionMethod = $reflectionClass->getMethod($methodName);
                
                if (isset($_REQUEST['arg0']) && $reflectionMethod->getNumberOfParameters() == 1) {
                    $data = json_decode($_REQUEST['arg0']);
                    
                    $classType = $reflectionMethod->getParameters()[0]->getType();
                    
                    if (! $classType->isBuiltin()) {
                        $className = $classType->getName();
                        $reflectionClass = new \ReflectionClass($className);
                        $defaults = $reflectionClass->getDefaultProperties();
                        
                        $object = new $className();
                        
                        foreach ($defaults as $key => $value) {
                            if (array_key_exists($key, $data)) {
                                $value = &$data->{$key};
                                $object->{$key} = $value ? $value : null;
                            }
                        }
                    } else {
                        $object = $data;
                    }
                    
                    $reflectionMethod->invoke($controller, $object);
                } else {
                    $reflectionMethod->invoke($controller);
                }
                
                $reflectionClass = new \ReflectionClass('fw\TemplateController');
                $propData = $reflectionClass->getProperty("_VUE_DATA");
                $propData->setAccessible(true);
                $data = $propData->getValue ($controller);
                $propData->setValue($controller, new \stdClass);
            }
        }
        
        if ($HAS_METHOD) {
            if(isset($data->d)) {
                $INDEX_CONTENT = json_encode($data);
            }
        } else {
            $url = 'webcontent/app/' . $TARGET_NAME . '/' . $TARGET_NAME;
            $templateURL = $url . '.html';
            if (file_exists($templateURL)) {
                $executeMethods = '';
                if(isset($data->m)) {
                    foreach ($data->m as $methodName) {
                        $executeMethods .= 'this.'.$methodName.'();';
                    }
                }
                
                $appURL = $url . '.js';
                $script = '
                    var TEMP_OBJECT = {data: ' . (isset($data->d) ? json_encode($data->d) : '{}') . ', methods: ' . $methodsList . '};
                    ' . (! file_exists($appURL) ? '' : '
                        TEMP_FUNC = function() {' . file_get_contents($appURL) . '};
                        TEMP_FUNC.call(TEMP_OBJECT);
                    ') . '

                    var _VUE = VUE_CONTEXT[TEMP_OBJECT.el];
                    var elementPrincipal = document.querySelector(TEMP_OBJECT.el);
                    if(_VUE) {
                        elementPrincipal.innerHTML = _VUE.html;
                        _VUE.$destroy();
                        delete VUE_CONTEXT[TEMP_OBJECT.el];
                    } else {
                        html = elementPrincipal.innerHTML;
                    }

                    var content = document.createElement("content");
                    elementPrincipal.appendChild(content);

                    content.innerHTML = `' . addslashes(file_get_contents($templateURL)) . '`;

        			_VUE = VUE_CONTEXT[TEMP_OBJECT.el] = new Vue({el : TEMP_OBJECT.el, mixins: [clone(VUE_GLOBAL), TEMP_OBJECT], created: function(){'.$executeMethods.'}});
                    _VUE.html = html;
                ';
                
                $INDEX_CONTENT .= $IS_AJAX ? $script : '<script id="!script">'.$script.'document.getElementById("\!script").remove();</script>';
            }
        }
        
        echo $INDEX_CONTENT;
    }
}

