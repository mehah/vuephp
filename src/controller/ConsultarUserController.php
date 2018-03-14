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

    public function deletar(User $user): void
    {
        $user->delete();
    }

    public function deletarSelecionados(Array $users): void
    {
        UserService::deletarUsuarios($users);
    }
}

