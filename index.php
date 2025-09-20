<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Auth;
use App\DBManager;
use App\Leaders;
use App\Router;

function get_pdo(): PDO
{
    $dbManager = DBManager::init('max', 'rambler1998', 'mysql', 'mydb');
    return $dbManager->getConnect();
}
function get_auth(): Auth
{
    $pdo = get_pdo();
    return new Auth($pdo);
}
function get_all():array
{
    $pdo = get_pdo();
    $auth = get_auth();
    $leaders = new Leaders($pdo);
    return [$auth,$leaders];
}


$router = new Router();

$router->addRoute('/register', function (){
    $connect = get_all();
    echo $connect[0]->register($_POST['name'], $_POST['password']);
});

$router->addRoute('/logIn', function () {
    $connect = get_all();
    echo $connect[0]->logIn($_POST['name'], $_POST['password']);
});

$router->addRoute('/logOut', function () {
    $connect = get_all();
    echo $connect[0]->logOut();
});

$router->addRoute('/deleteAccount', function () {
    $connect = get_all();
    echo $connect[0]->deleteAccount();
});

$router->addRoute('/saveGame', function () {
    $connect = get_all();
    echo $connect[0]->saveGame();
});

$router->addRoute('/getLeaders', function () {
    $connect = get_all();
    echo $connect[1]->getLeaders();
});

$router->addRoute('/getGames', function () {
    $connect = get_all();
    echo $connect[2]->getGames();
});

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
try {
    $router->handle($path);
} catch (Exception $e) {
    echo 'Ошибка: ' . $e->getMessage();
}