<?php
namespace src\modal;

use fw\database\Entity;
use fw\validator\Validation;
use fw\validator\ValidationSetup;
use src\validator\RequiredValidator;

class City extends Entity implements Validation {

	public static $table = 'citys';

	public static $primaryKey = 'id';

	public $id;

	public $name;

	public static function validationSetup(ValidationSetup $setup): void {
		$setup->register('name', RequiredValidator::class);
	}
}

