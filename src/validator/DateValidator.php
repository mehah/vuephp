<?php
namespace src\validator;

use fw\ComponentController;
use fw\validator\Validator;
use DateTime;

final class DateValidator implements Validator {

	private const FORMAT = 'd/m/Y';

	public function validate(ComponentController $controller, $entity, $name, $value, array $parameters, array &$sharedData) {
		$d = DateTime::createFromFormat(self::FORMAT, $value);
		return $d && $d->format(self::FORMAT) == $value;
	}
}

