<?php
use fw\Project;
use fw\database\DatabaseConnection;

Project::$name = 'ARQUITETURA';
Project::$defaultModule = 'home';

Project::importJavascriptPlugin('app/logout.js');
Project::registerJavascript('app/modal.js');

DatabaseConnection::register('default', 'mysql', '127.0.0.1', 'arquitetura', 'root');