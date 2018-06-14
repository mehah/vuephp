<?php
namespace src\controller;

use src\modal\City;

class ManterCityController extends MainController {

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
}

