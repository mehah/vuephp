<?php
namespace src\controller;

use src\modal\Login;

class LoginController extends MainController
{
    public function init(): void
    {
        parent::init();
        $this->setData('login', new Login);
    }
    
    public function entrar(Login $login) : void {
        if($login->user === "admin" && $login->password === "teste") {
            $this->getSession()->setAttribute("login", $login);
            $this->setData('logged', true);
            $this->redirect('home');
        } else {
            $this->showModal('Usuário ou senha invalido(s).');
        }
    }
    
    public function sair() {
        $this->getSession()->destroy();
    }
}

