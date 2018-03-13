<?php
namespace src\dao;

use modal\User;

class UserDAO
{
    public static function deletarUsuariosPorId(Array $ids): bool
    {
        return User::deleteWithFilter(Array("id" => $ids));
    }
}

