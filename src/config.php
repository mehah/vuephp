<?php
use fw\Project;
use fw\database\DatabaseConnection;

Project::$name = 'ARQUITETURA';

DatabaseConnection::register('default', 'mysql', '127.0.0.1', 'arquitetura', 'root');

Project::initLiveReload();