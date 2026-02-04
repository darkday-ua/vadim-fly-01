#!/usr/bin/env php
<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$root = dirname(__DIR__);
$config = require $root . '/app/config/config.php';

$pdo = \App\Db\Connection::fromConfig($config['db']);
$db = new \App\Db\Connection($pdo);

$migrator = new \App\Db\Migrator($db, $root . '/app/migrations');
$migrator->run();

echo "Migrations completed.\n";
