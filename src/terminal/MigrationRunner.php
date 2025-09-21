<?php

namespace App\terminal;

use PDO;
use ErrorException;
use Maximka\Migrations\Migrate;

class MigrationRunner
{
    public function __construct(
        private readonly PDO $pdo,
        private string       $dir,
        private string       $namespace = 'App\\migrations'
    )
    {
        $this->dir = rtrim($dir, DIRECTORY_SEPARATOR);
        $this->namespace = rtrim($namespace, '\\');
    }


    public function upAll(): void
    {
        foreach ($this->discoverMigrations() as $name => $class) {
            $this->runOne($name, $class, 'up');
        }
    }


    public function downAll(): void
    {
        $all = $this->discoverMigrations();
        $names = array_keys($all);
        rsort($names, SORT_NATURAL);
        foreach ($names as $name) {
            $this->runOne($name, $all[$name], 'down');
        }
    }


    public function upOne(string $target): void
    {
        [$name, $class] = $this->resolveTarget($target);
        $this->runOne($name, $class, 'up');
    }


    public function downOne(string $target): void
    {
        [$name, $class] = $this->resolveTarget($target);
        $this->runOne($name, $class, 'down');
    }


    private function discoverMigrations(): array
    {
        $out = [];
        foreach (glob($this->dir . DIRECTORY_SEPARATOR . '*.php') ?: [] as $file) {
            $base = basename($file, '.php');              // напр. Migrate001
            $fqcn = $this->namespace . '\\' . $base;      // App\migrations\Migrate001

            if (!class_exists($fqcn)) {
                require_once $file;
            }
            if (class_exists($fqcn) && is_subclass_of($fqcn, Migrate::class)) {
                $out[$base] = $fqcn;
            }
        }
        ksort($out, SORT_NATURAL);
        return $out;
    }

    private function resolveTarget(string $target): array
    {
        $all = $this->discoverMigrations();

        if (isset($all[$target])) return [$target, $all[$target]];       // точное имя файла

        foreach ($all as $name => $class) {                               // FQCN
            if ($class === $target) return [$name, $class];
        }

        // По «хвосту» имени (удобно: "001" → "Migrate001")
        $cand = [];
        foreach ($all as $name => $class) {
            if (str_ends_with($name, $target)) $cand[$name] = $class;
        }
        if (count($cand) === 1) {
            $n = array_key_first($cand);
            return [$n, $cand[$n]];
        }

        throw new ErrorException("Не найдено миграции по: {$target}");
    }

    private function runOne(string $name, string $class, string $direction): void
    {
        /** @var Migrate $migration */
        $migration = new $class($this->pdo);

        // Транзакции могут быть полезны для DML, но DDL их «ломает».
        $this->pdo->beginTransaction();
        try {
            if ($direction === 'up') {
                $migration->up();
                echo "↑ {$name}: up\n";
            } else {
                $migration->down();
                echo "↓ {$name}: down\n";
            }

            if ($this->pdo->inTransaction()) {
                $this->pdo->commit();
            }
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw new \ErrorException("Ошибка {$direction} в {$name}: " . $e->getMessage(), 0, 1, $e->getFile(), $e->getLine(), $e);
        }
    }
}
