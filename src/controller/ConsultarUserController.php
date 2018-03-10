<?php
namespace src\controller;

use fw\VueApp;
use modal\User;

class ConsultarUserController
{
    public function init() : \stdClass
    {
        $data = VueApp::getData();
        $data->users = User::all();
        return $data;
    }
}

