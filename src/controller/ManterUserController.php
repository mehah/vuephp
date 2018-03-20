<?php
namespace src\controller;

use modal\User;
use src\service\UserService;
use src\modal\City;

class ManterUserController extends MainController
{

    public function init(int $id = null): void
    {
        parent::init();
        
        $user = null;
        if ($id) {
            $user = UserService::find($id);
        }
        
        $this->setData('user', $user ? $user : new User());
        $this->setData('citys', City::all());
    }

    public function inserir(User $user): void
    {
        $res = false;
        if (! $user->name) {
            $msg = 'O campo nome é obrigatório';
        } else {
            $msg = ($res = $user->insert()) ? 'Cadastrado com sucesso.' : 'Erro ao tentar cadastrar.';
        }
        $this->showModal($msg, $res);
    }

    public function alterar(User $user): void
    {
        $res = false;
        if (! $user->name) {
            $msg = 'O campo nome é obrigatório';
        } else {
            $msg = ($res = $user->update()) ? 'Atualizado com sucesso.' : 'Erro ao tentar atualizar.';
        }
        $this->showModal($msg, $res);
    }
}

