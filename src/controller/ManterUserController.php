<?php
namespace src\controller;

use fw\VueApp;
use modal\User;

class ManterUserController
{

    public function init(int $id = null)
    {
        $user = new User();
        
        if ($id) {
            $user->id = $id;
            $user->load();
        }
        
        $data = VueApp::getData();
        $data->user = $user;
        
        return $data;
    }

    public function inserir(User $user)
    {
        $user->insert();
    }

    public function alterar(User $user)
    {
        $user->update();
    }

    public function remover(User $user)
    {
        $user->delete();
    }
}

