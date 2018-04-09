<?php
use fw\Project;
use fw\database\DatabaseConnection;

Project::$name = "ARQUITETURA";
Project::$defaultModule = "home";

Project::importJavascriptPlugin('plugins/locale/pt_BR.js');
Project::importJavascriptPlugin('plugins/vee-validate.min.js');

DatabaseConnection::register('default', 'mysql', '127.0.0.1', 'arquitetura', 'root');