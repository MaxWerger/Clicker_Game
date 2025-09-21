<?php
require __DIR__ . '/vendor/autoload.php';

use App\terminal\MigrationRunner;
use App\DBManager;

if ($argc < 2 || !in_array($argv[1], ['up', 'down'])) {
    fwrite(STDERR, "Использование:\n".
        "  php index.php up             # применить все\n".
        "  php index.php down           # откатить все\n".
        "  php index.php up   <name>    # применить одну (имя файла без .php, FQCN или хвост)\n".
        "  php index.php down <name>    # откатить одну\n");
    exit(1);
}

$command = $argv[1];
$target  = $argv[2] ?? null;

try {
    $dbManager = DBManager::init(
        username: "max",
        password: "rambler1998",
        host: "mysql",
        dbname: "mydb"
    );
    $pdo = $dbManager->getConnect();

    $runner = new MigrationRunner(
        pdo: $pdo,
        dir: __DIR__ . '/src/migrations',
        namespace: 'App\\migrations'
    );

    if ($command === 'up') {
        $target ? $runner->upOne($target) : $runner->upAll();
    } else {
        $target ? $runner->downOne($target) : $runner->downAll();
    }
} catch (\Throwable $e) {
    fwrite(STDERR, $e->getMessage() . PHP_EOL);
    exit(1);
}