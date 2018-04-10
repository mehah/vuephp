<?php
namespace src\validator;

use fw\validator\Validator;
use fw\ComponentController;

final class EmailValidator implements Validator {

	public function validate(ComponentController $controller, $entity, $name, $value, array $parameters, array &$sharedData) {
		return filter_var($value, FILTER_VALIDATE_EMAIL);
	}
}

