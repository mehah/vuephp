<?php
namespace modal;

use fw\database\Entity;

class User extends Entity
{

    public static $table = 'users';

    public static $primaryKey = 'id';

    public $id;

    public $name;

    public $email;
}

