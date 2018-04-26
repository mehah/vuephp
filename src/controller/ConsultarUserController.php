<?php
namespace src\controller;

use fw\impl\AccessRule;
use src\modal\User;
use src\service\UserService;

class ConsultarUserController extends MainController implements AccessRule {

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

	public static function getRules(): ?array {
		return null;
	}

	public static function getApplicationPath(): ?String {
		return "app/consultarUser";
	}
}

