<?php
namespace src\modal;

use fw\UserPrincipal;

class Login implements UserPrincipal {

	public $user;

	public $password;

	public function getRules(): ?array {
		return Array(
			"ALTERAR_USER"
		);
	}
}

