<?php
namespace src\controller;

use modal\User;

class ManterUserController extends MainController
{
    public function init(int $id = null) : void
    {
        $user = new User();
        
        if ($id) {
            $user->id = $id;
            $user->load();
        }
        
        $this->setData('user', $user);
    }

    public function inserir(User $user) : void
    {
        $msg = $user->insert() ? 'Cadastrado com sucesso.' : 'Erro ao tentar cadastrar.';
        $this->showModal($msg);
    }

    public function alterar(User $user) : void
    {
        $msg = $user->update() ? 'Atualizado com sucesso.' : 'Erro ao tentar atualizar.';
        $this->showModal($msg);
    }
}

