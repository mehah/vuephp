<?php
namespace src\controller;

use fw\RuleController;
use src\modal\City;
use src\modal\User;

class ManterUserController extends MainController implements RuleController
{

    public function init(int $id = null): void
    {
        parent::init();
        
        $user = null;
        if ($id) {
            $user = User::find($id);
        }
        
        $this->setData('user', $user ?? new User());
        $this->setData('citys', City::all());
    }

    public function inserir(User $user): Array
    {
        $res = false;
        if (! $user->name) {
            $msg = 'O campo nome é obrigatório';
        } else {
            $msg = ($res = $user->insert()) ? 'Cadastrado com sucesso.' : 'Erro ao tentar cadastrar.';
        }
        
        return Array(
            $res,
            $msg
        );
    }

    public function alterar(User $user): Array
    {
        $res = false;
        if (! $user->name) {
            $msg = 'O campo nome é obrigatório';
        } else {
            $msg = ($res = $user->update()) ? 'Atualizado com sucesso.' : 'Erro ao tentar atualizar.';
        }
        
        return Array(
            $res,
            $msg
        );
    }

    public static function getRules(): ?array
    {
        return null;
    }
}

