<?php
namespace src\controller;

use fw\RuleController;
use src\modal\User;
use src\service\UserService;

class ConsultarUserController extends MainController implements RuleController {

	public function init(): void {
		parent::init();
		
		$this->setData('users', User::all());
	}

	public function deletar(User $user): iterable {
		$msg = ($res = $user->delete()) ? 'Usuário \'' . $user->name . '\' removido com sucesso.' : 'Erro ao tentar remover o usuário \'' . $user->name . '\'.';
		
		return Array(
			$res,
			$msg
		);
	}

	public function deletarSelecionados(Array $users): iterable {
		$msg = ($res = UserService::deletarUsuarios($users)) ? 'Usuários selecionados foram removido com sucesso.' : 'Erro ao tentar remover todos os usuários.';
		
		return Array(
			$res,
			$msg
		);
	}

	public static function getRules(): ?array {
		return null;
	}
}

