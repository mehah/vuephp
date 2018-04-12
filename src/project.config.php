<?php
use fw\Project;
use fw\database\DatabaseConnection;

Project::$name = 'ARQUITETURA';
Project::$defaultModule = 'home';

Project::registerJavascript('app/modal.js');
Project::importJavascriptPlugin('app/logout.js');

Project::importCSS('styles/main.css');
Project::importCSS('styles/modal.css');

DatabaseConnection::register('default', 'mysql', '127.0.0.1', 'arquitetura', 'root');

Project::initLiveView();