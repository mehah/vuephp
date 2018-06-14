<?php
use fw\Router\Router;
use src\controller\ConsultarCityController;
use src\controller\ConsultarUserController;
use src\controller\LoginController;
use src\controller\ManterCityController;
use src\controller\ManterUserController;
use src\controller\HomeController;

Router::registerController('/', HomeController::class, 'index.html', 'app/home');
Router::registerController('consultarCity', ConsultarCityController::class, 'index.html', 'app/consultarCity');
Router::registerController('consultarUser', ConsultarUserController::class, 'index.html', 'app/consultarUser');
Router::registerController('login', LoginController::class, 'index.html', 'app/login');
Router::registerController('manterCity', ManterCityController::class, 'index.html', 'app/manterCity');
Router::registerController('manterUser', ManterUserController::class, 'index.html', 'app/manterUser', Array(
	'alterar' => 'ALTERAR_USER'
));