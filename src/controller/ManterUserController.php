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
        $data->id = $id;
        $data->user = $user;
        
        return $data;
    }

    public function inserir(User $user)
    {
        $user->insert();
    }

    public function atualizar(User $user)
    {
        $user->update();
    }

    public function deletar(User $user)
    {
        $user->delete();
    }
}

