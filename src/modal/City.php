<?php
namespace src\modal;

use fw\database\Entity;

class City extends Entity
{

    public static $table = 'citys';

    public static $primaryKey = 'id';

    public static $relationship = Array();

    public $id;

    public $name;
}

