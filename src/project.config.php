<?php
use fw\Core;
use fw\database\DatabaseConnection;

Core::$PROJECT_NAME = "ARQUITETURA";
Core::$DEFAULT_MODULE_NAME = "home";

DatabaseConnection::register('default', 'mysql', '127.0.0.1', 'arquitetura', 'root');