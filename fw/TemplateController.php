<?php
namespace fw;

use http\HttpSession\HttpSession;

abstract class TemplateController
{

    private $_VUE_DATA = null;

    public function __construct()
    {
        $this->_VUE_DATA = new \stdClass();
    }

    protected function getData(): \stdClass
    {
        $o = null;
        if (isset($this->_VUE_DATA->d)) {
            $o = $this->_VUE_DATA->d;
        } else {
            $o = $this->_VUE_DATA->d = new \stdClass();
        }
        
        return $o;
    }

    protected function setData($name, $value): void
    {
        $this->getData()->{$name} = $value;
    }

    protected function getSession(): HttpSession
    {
        return $_SESSION[Core::$PROJECT_NAME]['INSTANCE'];
    }
}

