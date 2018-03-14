<?php
namespace src\controller;

use fw\TemplateController;

abstract class MainController extends TemplateController
{

    protected function showModal($msg): void
    {
        $modal = new \stdClass();
        $modal->message = $msg;
        $modal->openned = true;
        $this->setData('modal', $modal);
    }
}

