<?php

namespace App;

use ErrorException;
use PDO;
use PDOException;

class DBManager
{
    private static ?DBManager $instance = null;
    private static string $username;
    private static string $password;
    private static string $hostname;
    private static string $database;
    private PDO $pdo;

    /**
     * Передает данные для подключения
     * @param $username - Имя пользователя
     * @param $password - Пароль пользователя
     * @param $host     - Адрес БД
     * @param $dbname   - Имя БД
     * @return DBManager|null
     * @throws ErrorException - Ошибка, если параметры не переданы
     */
    public static function init(
        $username,
        $password,
        $host,
        $dbname
    ): ?DBManager
    {
        self::$username = $username;
        self::$password = $password;
        self::$hostname = $host;
        self::$database = $dbname;

        return self::getInstance();
    }


    /**
     * Подключение к БД
     */
    private function __construct()
    {

        try {
            $hostname = self::$hostname;
            $database = self::$database;
            $username = self::$username;
            $password = self::$password;

            $this->pdo = new PDO("mysql:host=$hostname;dbname=$database;charset=utf8", $username, $password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Тут ошибка вылезла, брат" . $e->getMessage());
        }
    }


    /**
     * @return DBManager|null
     * @throws ErrorException - Ошибка, если параметры не переданы
     */
    public static function getInstance(): ?DBManager
    {
        if (self::$instance == null) {

            if (isset(self::$username, self::$password, self::$hostname, self::$database)) {
                self::$instance = new DBManager();
            } else {
                throw new ErrorException("Не заданы параметры, брат");
            }

        }
        return self::$instance;
    }

    public function getConnect(): PDO
    {
        return $this->pdo;
    }
}
