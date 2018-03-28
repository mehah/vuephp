<?php
namespace src\controller;

use src\modal\Login;

class LoginController extends MainController
{

    public function init(): void
    {
        parent::init();
        $this->setData('login', new Login());
    }

    public function entrar(Login $login): void
    {
        $logged = $login->user === "admin" && $login->password === "teste";
        if ($logged) {
            $this->getSession()->setAttribute("login", $login);
        } else {
            $this->setData('msg', 'Usuário ou senha invalido(s).');
        }
        
        $this->setData('logged', $logged);
    }
}

