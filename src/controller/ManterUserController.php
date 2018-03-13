<?php
namespace src\controller;

use fw\VueController;
use modal\User;

class ManterUserController extends VueController
{
    public function init(int $id = null) : void
    {
        $user = new User();
        
        if ($id) {
            $user->id = $id;
            $user->load();
        }
        
        $this->setData('user', $user);
        $this->setData('messageCadastro', null);
    }

    public function inserir(User $user) : void
    {        
        $user->insert();
        
        $this->setData('messageCadastro', 'Cadastrado com sucesso!');
    }

    public function alterar(User $user) : void
    {
        $user->update();
    }
}

