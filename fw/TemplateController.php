<?php
namespace fw;

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

    private function getMethods(): \stdClass
    {
        $o = null;
        if (isset($this->_VUE_DATA->m)) {
            $o = $this->_VUE_DATA->m;
        } else {
            $o = $this->_VUE_DATA->m = new \stdClass();
        }
        
        return $o;
    }

    protected function setData($name, $value): void
    {
        $this->getData()->{$name} = $value;
    }

    protected function executeMethod(string $name, ...$args): void
    {
        $this->getMethods()->{$name} = $args;
    }

    protected function goback()
    {
        $this->executeMethod("goback");
    }
}

