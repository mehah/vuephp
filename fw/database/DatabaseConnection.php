<?php
namespace fw\database;

class DatabaseConnection {

	public static $host;

	public static $password;

	public static $dbName;

	public static $user;

	public static function getInstance(): \PDO {
		try {
			return new \PDO('mysql:host=' . self::$host . ';dbname=' . self::$dbName, self::$user, self::$password);
		} catch (\PDOException $e) {
			echo ('Não foi possivel estabelecer uma conexão.');
		}
		
		return null;
	}
}
?>
