<?php

namespace App;

use PDO;

class Tokens
{
    private PDO $pdo;
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }
    public function gen_token(): string
    {
        return bin2hex(random_bytes(16));
    }
    public function getUserByToken(string $token)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users u JOIN tokens t ON u.id = t.user_id WHERE t.token = :token");
        $stmt->execute(["token" => $token]);
        return $stmt->fetch();
    }
}