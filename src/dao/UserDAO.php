<?php
namespace dao;

use fw\database\DatabaseEntity;
use modal\User;

class UserDAO
{
    public static function load(User $entity): bool
    {
        return DatabaseEntity::load($entity);
    }

    public static function inset(User $entity) : bool
    {
        return DatabaseEntity::insert($entity);
    }

    public static function update(User $entity) : bool
    {
        return DatabaseEntity::update($entity);
    }

    public static function delete(User $entity) : bool
    {        
        return DatabaseEntity::delete($entity);
    }
}

