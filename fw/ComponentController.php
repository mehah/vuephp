<?php
namespace fw;

use fw\http\HttpSession;

abstract class ComponentController {

	private $_VUE_DATA = null;

	public function __construct() {
		$this->_VUE_DATA = new \stdClass;
	}

	protected function getData(): object {
		return $this->_VUE_DATA->d ?? ($this->_VUE_DATA->d = new \stdClass);
	}

	protected function setData($name, $value): void {
		$this->getData()->{$name} = $value;
	}
	
	protected function getRootData(): object {
		return $this->_VUE_DATA->rd ?? ($this->_VUE_DATA->rd = new \stdClass);
	}
	
	protected function setRootData($name, $value): void {
		$this->getRootData()->{$name} = $value;
	}

	protected function getSession(): HttpSession {
		return Core::getSessionInstance();
	}
}

