<?php
namespace src\service;

use src\dao\UserDAO;

class UserService
{

    public static function find(int $id)
    {
        return UserDAO::find($id);
    }

    public static function deletarUsuarios(Array $users): bool
    {
        $ids = array_map(function ($o) {
            return $o->id;
        }, $users);
        
        return UserDAO::deletarUsuariosPorId($ids);
    }
}

