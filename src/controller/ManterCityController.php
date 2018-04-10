<?php
namespace src\controller;

use fw\impl\AccessRule;
use src\modal\City;

class ManterCityController extends MainController implements AccessRule {

	public function init(int $id = null): void {
		parent::init();
		
		$entity = $id ? City::find($id) : new City();
		
		$this->setData('entity', $entity);
	}

	public function inserir(City $entity): bool {
		if ($this->validate($entity)->hasError()) {
			return false;
		}
		
		return $entity->insert();
	}

	public function alterar(City $entity): bool {
		if ($this->validate($entity)->hasError()) {
			return false;
		}
		
		return $entity->update();
	}

	public static function getRules(): ?array {
		return null;
	}
}

