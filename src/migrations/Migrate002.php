<?php

namespace App\migrations;

use Maximka\Migrations\Column;
use Maximka\Migrations\Key;
use Maximka\Migrations\Migrate;
use Maximka\Migrations\Query;
use Maximka\Migrations\Table;

class Migrate002 extends Migrate
{

    function up(): void
    {

        $query = new Query($this->pdo);

        $table = new Table('tokens', [
            new Column('id', 'INT PRIMARY KEY AUTO_INCREMENT'),
            new Column('token', 'VARCHAR(64) UNIQUE'),
            new Column('user_id', 'INT NOT NULL'),
        ], [new Key('fk_user', 'user_id', 'users(id)')]);


        $query->createTable($table);
    }

    function down(): void
    {
        $query = new Query($this->pdo);

        $query->dropTable("tokens");
    }
}