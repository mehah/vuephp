<?php
use fw\Router;
use src\controller\ConsultarCityController;
use src\controller\ConsultarUserController;
use src\controller\LoginController;
use src\controller\ManterCityController;
use src\controller\ManterUserController;
use src\controller\HomeController;

Router::registerController('/', HomeController::class, 'index.html');
Router::registerController('consultarCity', ConsultarCityController::class, 'index.html');
Router::registerController('consultarUser', ConsultarUserController::class, 'index.html');
Router::registerController('login', LoginController::class, 'index.html');
Router::registerController('manterCity', ManterCityController::class, 'index.html');
Router::registerController('manterUser', ManterUserController::class, 'index.html');