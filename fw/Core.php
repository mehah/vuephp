<?php
namespace fw;

use http\HttpSession\HttpSession;

class Core
{

    public static $PROJECT_NAME;

    public static $PRINCIPAL_MODULE_NAME;

    private const _ARGS = array(
        'a',
        'b',
        'c',
        'd',
        'e',
        'f',
        'g',
        'h',
        'i',
        'j',
        'k',
        'l',
        'm',
        'n',
        'o',
        'p',
        'q',
        'r',
        's',
        't',
        'u',
        'v',
        'w',
        'x ',
        'y',
        'z'
    );

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
                    $_REQUEST['arg0'] = '[' . $exURL[1] . ']';
                }
            }
        }
        
        include ('fw/TemplateController.php');
        include ('fw/database/DatabaseConnection.php');
        include ('fw/database/DatabaseEntity.php');
        include ('fw/database/Entity.php');
        include ('fw/http/HttpSession.php');
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
                $INDEX_CONTENT .= '<script type="text/javascript" src="' . $CONTEXT_PATH . $url . '"></script>';
            }
            
            $url = 'webcontent/styles.css';
            if (file_exists($url)) {
                $INDEX_CONTENT .= '<link rel="stylesheet" type="text/css" href="' . $CONTEXT_PATH . $url . '">';
            }
            
            $INDEX_CONTENT .= file_get_contents('webcontent/index.html');
        }
        
        $srcPath = 'src/controller/' . $TAGET_CLASS_NAME . 'Controller.php';
        
        $methodsList = $data = '{}';
        if (file_exists($srcPath)) {
            $session = self::getSession();
            
            $controllerPath = self::$PROJECT_NAME . '/controller/' . $TAGET_CLASS_NAME;
            $className = 'src\controller\\' . $TAGET_CLASS_NAME . 'Controller';
            
            $controller = null;
            if ($HAS_METHOD) {
                if (isset($session[$TAGET_CLASS_NAME])) {
                    $controller = $session[$TAGET_CLASS_NAME];
                }
            } else {
                $exURL[1] = 'init';
            }
            
            if (! $controller) {
                $controller = new $className();
                if (! ($controller instanceof TemplateController)) {
                    die('O controlador ' . $className . ' precisa extender a classe TemplateController.');
                }
                $session[$TAGET_CLASS_NAME] = $controller;
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
                
                $countParam = $method->getNumberOfParameters();
                $args = '';
                for ($i = - 1; ++ $i < $countParam;) {
                    $args .= self::_ARGS[$i] . ',';
                }
                
                $methodsList .= $methodName . ':function(' . $args . 'z){this.request("' . $TARGET_NAME . '/' . $methodName . '", ' . $args . 'z);}';
            }
            
            $methodsList = '{' . $methodsList . '}';
            $methodName = $exURL[$posMethod];
            if ($reflectionClass->hasMethod($methodName)) {
                $reflectionMethod = $reflectionClass->getMethod($methodName);
                
                $resMethod = null;
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
                    
                    $resMethod = $reflectionMethod->invokeArgs($controller, $list);
                } else {
                    $resMethod = $reflectionMethod->invoke($controller);
                }
                
                $reflectionClass = new \ReflectionClass('fw\TemplateController');
                $propData = $reflectionClass->getProperty("_VUE_DATA");
                $propData->setAccessible(true);
                $data = $propData->getValue($controller);
                $propData->setValue($controller, new \stdClass());
                
                if ($resMethod) {
                    $data->ds = $resMethod;
                }
            }
        }
        
        if ($HAS_METHOD) {
            if (isset($data->d) || isset($data->m)) {
                $INDEX_CONTENT = json_encode($data);
            }
        } else {
            $url = 'webcontent/app/' . $TARGET_NAME . '/' . $TARGET_NAME;
            $templateURL = $url . '.html';
            if (file_exists($templateURL)) {
                $executeMethods = '';
                if (isset($data->m)) {
                    foreach ($data->m as $methodName) {
                        $executeMethods .= 'this.' . $methodName . '();';
                    }
                }
                
                $jsonData = isset($data->d) ? json_encode($data->d) : '{}';
                $appURL = $url . '.js';
                $script = 'var TEMP_OBJECT = {data: ' . $jsonData . ', methods: ' . $methodsList . '};';
                $script .= ! file_exists($appURL) ? '' : 'TEMP_FUNC = function() { var $data=' . $jsonData . ';' . file_get_contents($appURL) . '};TEMP_FUNC.call(TEMP_OBJECT);';
                $script .= 'var _VUE = VUE_CONTEXT[TEMP_OBJECT.el];
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

                    content.innerHTML = ' . json_encode(file_get_contents($templateURL)) . ';

        			_VUE = VUE_CONTEXT[TEMP_OBJECT.el] = new Vue({el : TEMP_OBJECT.el, mixins: [VUE_GLOBAL, TEMP_OBJECT], created: function(){' . $executeMethods . '}});
                    _VUE.html = html;
                ';
                
                $INDEX_CONTENT .= $IS_AJAX ? $script : '<script id="!script">' . $script . 'document.getElementById("\!script").remove();</script>';
            }
        }
        
        echo $INDEX_CONTENT;
    }

    private static function setClassProps($data, $object): void
    {
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

    public static function getSession(): Array
    {
        if (isset($_SESSION[self::$PROJECT_NAME])) {
            $session = $_SESSION[self::$PROJECT_NAME];
        } else {
            $session = $_SESSION[self::$PROJECT_NAME] = Array(
                'INSTANCE' => new HttpSession()
            );
        }
        
        return $session;
    }
}

