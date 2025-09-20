<?php

namespace App\migrations;

use Maximka\Migrations\Column;
use Maximka\Migrations\Key;
use Maximka\Migrations\Migrate;
use Maximka\Migrations\Query;
use Maximka\Migrations\Table;

class Migrate003 extends Migrate
{

    function up(): void
    {

        $query = new Query($this->pdo);

        $table = new Table('leaders', [
            new Column('id', 'INT PRIMARY KEY AUTO_INCREMENT'),
            new Column('name', 'VARCHAR(50)'),
            new Column('click', 'INT'),
        ], []);


        $query->createTable($table);
    }


    function down(): void
    {
        $query = new Query($this->pdo);

        $query->dropTable("leaders");
    }

}