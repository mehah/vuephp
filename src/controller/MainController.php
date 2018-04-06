<?php
namespace src\controller;

use fw\ComponentController;

abstract class MainController extends ComponentController {

	protected $login;

	protected function init(): void {
		$this->login = $this->getSession()->getUserPrincipal();
		$this->setRootData('logged', $this->login ? true : false);
	}

	public function logout() {
		$this->getSession()->destroy();
	}
}

