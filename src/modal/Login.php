<?php
namespace src\modal;

use fw\UserPrincipal;
use fw\validator\Validation;
use fw\validator\ValidationSetup;
use src\validator\RequiredValidator;

class Login implements UserPrincipal, Validation {

	public $user;

	public $password;

	public function getRules(): ?array {
		return Array(
			"ALTERAR_USER"
		);
	}

	public static function validationSetup(ValidationSetup $setup): void {
		$setup
			->register('user', RequiredValidator::class)
			->register('password', RequiredValidator::class);
	}
}

