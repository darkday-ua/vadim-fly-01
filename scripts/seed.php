#!/usr/bin/env php
<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$root = dirname(__DIR__);
$config = require $root . '/app/config/config.php';

$pdo = \App\Db\Connection::fromConfig($config['db']);
$db = new \App\Db\Connection($pdo);

$username = $config['auth']['default_username'];
$password = $config['auth']['default_password'];

// Check if user already exists
$existing = $db->fetchOne('SELECT id FROM users WHERE username = ?', [$username]);

if ($existing !== null) {
    echo "User '$username' already exists. Skipping seed.\n";
    exit(0);
}

// Create default user
$passwordHash = password_hash($password, PASSWORD_DEFAULT);
$db->execute(
    'INSERT INTO users (username, password_hash) VALUES (?, ?)',
    [$username, $passwordHash]
);

echo "Created default user:\n";
echo "  Username: $username\n";
echo "  Password: $password\n";
echo "\n⚠️  Change these credentials in production!\n";
