<?php
namespace fw\database;

class DatabaseConnection {
	private static $configs = Array();

	public static function getInstance(String $name = "default"): \PDO {
		$config = self::$configs[$name];
		
		try {
			return new \PDO($config['dbType'].':host=' . $config['host'] . ';dbname=' . $config['dbName'], $config['user'], $config['password'], $config['options']);
		} catch (\PDOException $e) {
			echo ('Não foi possivel estabelecer uma conexão.');
		}
		
		return null;
	}
	
	public static function register(String $name, String $dbType, String $host, String $dbName, String $user, String $password = null, Array $options = null) : void {
		self::$configs[$name] = Array(
			'dbType' => $dbType,
			'host' => $host,
			'dbName' => $dbName,
			'user' => $user,
			'password' => $password,
			'options' => $options,
		);
	}
}
?>
