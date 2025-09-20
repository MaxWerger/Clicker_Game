<?php

namespace App\migrations;

use Maximka\Migrations\Column;
use Maximka\Migrations\Migrate;
use Maximka\Migrations\Query;
use Maximka\Migrations\Table;

class Migrate001 extends Migrate
{
    function up(): void
    {

        $query = new Query($this->pdo);

        $table = new Table('users', [
            new Column('id', 'INT PRIMARY KEY AUTO_INCREMENT'),
            new Column('name', 'VARCHAR(15) UNIQUE'),
            new Column('password', 'VARCHAR(255)'),
            new Column('click', 'INT'),
            new Column('money', 'INT'),
            new Column('wins', 'INT'),
            new Column('update_time', 'DATETIME(6)'),
        ], []);


        $query->createTable($table);
    }

    function down(): void
    {
        $query = new Query($this->pdo);

        $query->dropTable("users");
    }
}