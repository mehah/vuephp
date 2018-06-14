<?php
namespace src\controller;

use src\modal\City;
use src\modal\User;

class ManterUserController extends MainController {

	public function init(int $id = null): void {
		parent::init();
		
		$entity = $id ? User::find($id) : new User();
		
		$this->setData('entity', $entity);
		$this->setData('citys', City::all());
	}

	public function inserir(User $entity): bool {
		return $entity->insert();
	}

	public function alterar(User $entity): bool {
		return $entity->update();
	}
}