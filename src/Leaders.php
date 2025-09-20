<?php

namespace App;

require_once __DIR__ . '/../vendor/autoload.php';

class Leaders
{
    private \PDO $pdo;
    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }
    public function saveLeaders(string $username): string
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE name = :username');
        $stmt->execute(["username" => $username]);
        $user = $stmt->fetch();

        $stmt = $this->pdo->prepare('INSERT INTO leaders (click, name) VALUES (:click, :name)');
        $stmt->execute(["name" => $user['name'], "click" => $user["click"]]);


        return 'Добавлен в Лидеры';
    }
    public function getLeaders(): string{

        $stmt = $this->pdo->prepare('SELECT * FROM leaders ORDER BY click DESC');
        $stmt->execute();
        $user = $stmt->fetchAll();

        return json_encode($user);
    }
}