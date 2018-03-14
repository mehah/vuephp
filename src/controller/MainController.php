<?php
namespace src\controller;

use fw\TemplateController;

abstract class MainController extends TemplateController
{
    protected function showModal($msg, $data = null): void
    {
        $this->executeMethod('showModal', $msg, $data);
    }
}

