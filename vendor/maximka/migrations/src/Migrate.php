<?php

namespace Maximka\Migrations;

use PDO;

abstract class Migrate
{
    protected PDO $pdo;


    function __construct(PDO $pdo)
    {
       $this->pdo = $pdo;
    }


    abstract function up();



    abstract function down();

}