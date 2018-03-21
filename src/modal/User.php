<?php
namespace modal;

use fw\database\Entity;
use src\modal\City;

class User extends Entity
{

    public static $table = 'users';

    public static $primaryKey = 'id';

    public static $relationship = Array(
        'city' => 'id_city'
    );

    function __construct()
    {
        $this->city = new City();
    }

    public $id;

    public $name;

    public $email;

    public $sex;

    public $city;
}

