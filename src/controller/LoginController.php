<?php
namespace src\controller;

use src\modal\Login;

class LoginController extends MainController {

	public function init(): void {
		parent::init();
		$this->setData('login', new Login());
	}

	public function entrar(Login $login) {
		$validation = $this->validate($login);
		if($validation->hasError()) {
			return $validation->getData();
		}
		
		if ($login->user === "admin" && $login->password === "teste") {
			$this->getSession()->setUserPrincipal($login);
			$this->setData('logged', true);
		}
	}
}

