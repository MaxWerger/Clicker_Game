<?php

namespace App;

use DateTime;
use Exception;
use PDO;

require_once __DIR__ . '/../vendor/autoload.php';

class Auth
{
    private PDO $pdo;
    private Tokens $tokens;
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->tokens = new Tokens($pdo);
    }
    public function searchUbyT(): void
    {
        $token = $_POST['token'];
        $user = $this->tokens->getUserByToken($token);
        if (!$user) {
            throw new Exception('Неверный токен');
        }
    }
    public function register(string $username, string $password): string
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE name = :username");
        $stmt->execute(["username" => $username]);

        if ($stmt->fetch()) {
            http_response_code(400);
            return "Пользователь существует";
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $this->pdo->prepare("INSERT INTO users (name, password) VALUES (:username, :password)");
        $stmt->execute(["username" => $username, "password" => $hashedPassword]);
        return "Регистрация прошла успешно";
    }
    public function logIn(string $username, string $password): string
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE name = :username");
        $stmt->execute(["username" => $username]);

        $user = $stmt->fetch();
        if (!$user) {
            return "Неверный логин или пароль";
        }

        if (!password_verify($password, $user['password'])) {
            return 'Неверный пароль';
        }

        $stmt = $this->pdo->prepare("SELECT token FROM tokens WHERE user_id = :user_id");
        $stmt->execute(["user_id" => $user['id']]);
        $chek_token = $stmt->fetch();

        if ($chek_token) {
            return 'Вы уже авторизованы';
        }
        $pdo = $this->pdo;
        $classTokens = new Tokens($pdo);

        $stmt = $this->pdo->prepare("INSERT INTO tokens (user_id, token) VALUES (:user_id, :token)");
        $stmt->execute(["user_id" => $user['id'], "token" => $classTokens->gen_token()]);

        $giveTime = (new DateTime())->format("Y-m-d H:i:s");
        $stmt = $this->pdo->prepare("UPDATE users SET update_time = :update_time WHERE id = :user_id");
        $stmt->execute(["user_id" => $user['id'], "update_time" => $giveTime]);

        return "Авторизация прошла успешно";
    }
    public function logOut(): string
    {
        $user = $this->tokens->getUserByToken($_POST["token"]);

        if ($user['token'] === $_POST["token"]) {
            $stmt = $this->pdo->prepare("DELETE FROM tokens WHERE user_id = :user_id");
            $stmt->execute(["user_id" => $user['user_id']]);
            return "Вы вышли из аккаунта";
        }else{
            throw new Exception('Токен отсутствует');
        }
    }
    public function deleteAccount(): string
    {
        $user = $this->tokens->getUserByToken($_POST["token"]);

        $stmt = $this->pdo->prepare("DELETE FROM tokens WHERE user_id = :user_id");
        $stmt->execute(["user_id" => $user['id']]);

        $stmt = $this->pdo->prepare("DELETE FROM leaders WHERE user_id = :user_id");
        $stmt->execute(["user_id" => $user['id']]);

        $stmt = $this->pdo->prepare("DELETE FROM users WHERE user_id = :user_id");
        $stmt->execute(["user_id" => $user['id']]);

        return 'Аккаунт удалён';
    }
    public function saveGame(): string
    {
        $click = $_POST["click"] ?? '';

        $user = $this->tokens->getUserByToken($_POST["token"]);

        $giveTime = new DateTime();
        $userTimeInDB = $user['update_time'] ? new DateTime($user['update_time']) : null;
        $interval = $userTimeInDB ? $giveTime->getTimestamp() - $userTimeInDB->getTimestamp() : 0;

        var_dump($interval);

        $protectionClick = 15;
        $userClickInDB = (int)$user['click'];
        $allUserClicks = $userClickInDB + $click;
        $maxClickInTime = $protectionClick * $interval;

        if ($userClickInDB <= 0) {
            if ($allUserClicks <= $maxClickInTime || $maxClickInTime == 0) {
                $stmt = $this->pdo->prepare("UPDATE users SET click = :click, update_time = :update_time WHERE id = :user_id");
                $stmt->execute(['click' => $allUserClicks, 'update_time' => $giveTime->format("Y-m-d H:i:s"), 'user_id' => $user['user_id']]);

                $stmt = $this->pdo->prepare("INSERT INTO leaders (name, click) VALUES (:name, :click)");
                $stmt->execute(["name" => $user['name'], "click" => $allUserClicks]);

                return 'Первое сохранение';
            }
        } else {
            if ($click <= $maxClickInTime) {
                $stmt = $this->pdo->prepare("UPDATE users SET click = :click, update_time = :update_time WHERE id = :user_id");
                $stmt->execute(['click' => $allUserClicks, 'update_time' => $giveTime->format("Y-m-d H:i:s"), 'user_id' => $user['user_id']]);

                $stmt = $this->pdo->prepare("UPDATE leaders SET click = :click WHERE name = :username");
                $stmt->execute(['click' => $allUserClicks, 'username' => $user['name']]);

                return 'Игра сохранена';
            }
            return 'Сработало ограничение клик/сек';
        }
        return '';
    }
}



