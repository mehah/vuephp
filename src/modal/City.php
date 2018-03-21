<?php
namespace src\modal;

use fw\database\Entity;

class City extends Entity
{

    public static $table = 'citys';

    public static $primaryKey = 'id';

    public $id;

    public $name;
}

