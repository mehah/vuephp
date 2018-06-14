<?php
namespace src\controller;

use src\modal\User;
use src\service\UserService;

class ConsultarUserController extends MainController {

	public function init(): void {
		parent::init();
		
		$this->setData('entitys', User::all());
	}

	public function deletar(User $entity): bool {
		return $entity->delete();
	}

	public function deletarSelecionados(Array $entitys): bool {
		return UserService::deletarUsuarios($entitys);
	}
}

