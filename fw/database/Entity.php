<?php
namespace fw\database;

abstract class Entity
{    
    public static function all(): Array
    {
        return DatabaseEntity::all(get_called_class());
    }
    
    public function load(): bool
    {
        return DatabaseEntity::load($this);
    }
    
    public function insert() : bool
    {
        return DatabaseEntity::insert($this);
    }
    
    public function update() : bool
    {
        return DatabaseEntity::update($this);
    }
    
    public function delete() : bool
    {
        return DatabaseEntity::delete($this);
    }
}

