<?php
namespace modal;

class User
{

    public static $table = 'users';

    public static $primaryKey = 'id';

    public $id;

    public $name;

    public $email;
}

