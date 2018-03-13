<?php
namespace src\dao;

use modal\User;

class UserDAO
{
    public static function deletarUsuariosPorId(Array $ids): bool
    {
        return User::deleteByFields(Array("id" => $ids));
    }
}

