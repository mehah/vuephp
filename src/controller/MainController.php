<?php
namespace src\controller;

use fw\TemplateController;

abstract class MainController extends TemplateController {

	protected $login;

	protected function init(): void {
		$this->login = $this->getSession()->getUserPrincipal();
		$this->setData('logged', $this->login ? true : false);
	}

	public function logout() {
		$this->getSession()->destroy();
	}
}

