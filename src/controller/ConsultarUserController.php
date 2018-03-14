<?php
namespace src\controller;

use modal\User;
use src\service\UserService;

class ConsultarUserController extends MainController
{

    public function init(): void
    {
        $this->setData('users', User::all());
    }

    public function deletar(User $user): bool
    {
        $msg = ($res = $user->delete()) ? 'Usuário \'' . $user->name . '\' removido com sucesso.' : 'Erro ao tentar remover o usuário \'' . $user->name . '\'.';
        $this->showModal($msg);
        
        return $res;
    }

    public function deletarSelecionados(Array $users): bool
    {
        $msg = ($res = UserService::deletarUsuarios($users)) ? 'Usuários selecionados foram removido com sucesso.' : 'Erro ao tentar remover todos os usuários.';
        $this->showModal($msg);
        
        return $res;
    }
}

