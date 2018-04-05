<?php
namespace src\dao;

use fw\database\DatabaseConnection;
use src\modal\User;

class UserDAO
{

    public static function find(int $id) : ?User
    {
        $conn = DatabaseConnection::getInstance();
        $conn->setAttribute(\PDO::ATTR_FETCH_TABLE_NAMES, true);
        
        $stmt = $conn->prepare('SELECT * FROM `users` as u LEFT JOIN `citys` as c on u.id_city = c.id WHERE u.id = ?');
        $stmt->bindValue(1, $id);
        
        if ($stmt->execute() && $res = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $entity = new User();
            
            $entity->id = $res['u.id'];
            $entity->name = $res['u.name'];
            $entity->sex = $res['u.sex'];
            
            $entity->city->id = $res['c.id'];
            $entity->city->name = $res['c.name'];
            
            return $entity;
        }
        
        return null;
    }

    public static function deletarUsuariosPorId(Array $ids): bool
    {
        return User::deleteWithFilter(Array(
            "id" => $ids
        ));
    }
}

