#!/usr/bin/env php
<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$root = dirname(__DIR__);
$config = require $root . '/app/config/config.php';

// Validate required config
$username = $config['auth']['default_username'] ?? null;
$password = $config['auth']['default_password'] ?? null;

if (empty($username)) {
    fwrite(STDERR, "❌ Error: DEFAULT_USERNAME is not set in .env\n");
    exit(1);
}

if (empty($password)) {
    fwrite(STDERR, "❌ Error: DEFAULT_PASSWORD is not set in .env\n");
    exit(1);
}

$pdo = \App\Db\Connection::fromConfig($config['db']);
$db = new \App\Db\Connection($pdo);

// Check if --skip-update flag is provided (to skip updating existing users)
$skipUpdate = in_array('--skip-update', $argv);

// Check if user already exists
$existing = $db->fetchOne('SELECT id FROM users WHERE username = ?', [$username]);

if ($existing !== null) {
    if ($skipUpdate) {
        echo "ℹ️  User '$username' already exists. Skipping (--skip-update flag used).\n";
        exit(0);
    }
    
    // Update password for existing user
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $db->execute(
        'UPDATE users SET password_hash = ? WHERE username = ?',
        [$passwordHash, $username]
    );
    echo "✅ Updated password for existing user from .env:\n";
    echo "   Username: $username\n";
    echo "   Password: $password\n";
    echo "\n⚠️  Change DEFAULT_PASSWORD in .env for production!\n";
} else {
    // Create new user
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $db->execute(
        'INSERT INTO users (username, password_hash) VALUES (?, ?)',
        [$username, $passwordHash]
    );
    echo "✅ Created default user from .env:\n";
    echo "   Username: $username\n";
    echo "   Password: $password\n";
    echo "\n⚠️  Change DEFAULT_PASSWORD in .env for production!\n";
}
