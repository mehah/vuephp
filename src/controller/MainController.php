<?php
namespace src\controller;

use fw\TemplateController;

abstract class MainController extends TemplateController
{

    protected $login;

    protected function init(): void
    {
        $this->login = $this->getSession()->getAttribute('login');
        $this->setData('logged', $this->login ? true : false);
    }

    protected function showModal($msg, $data = null): void
    {
        $this->executeMethod('showModal', $msg, $data);
    }
}

