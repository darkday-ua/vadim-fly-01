<?php

declare(strict_types=1);

namespace App\Auth;

use App\Db\Connection;

class Auth
{
    private const USER_ID_KEY = 'user_id';

    public function __construct(private array $config)
    {
    }

    /** Attempt login; returns user id on success, null on failure. */
    public function attempt(Connection $db, string $username, string $password): ?int
    {
        if ($username === '' || $password === '') {
            return null;
        }
        $row = $db->fetchOne(
            'SELECT id, password_hash FROM users WHERE username = ? LIMIT 1',
            [$username]
        );
        if ($row === null) {
            return null;
        }
        if (!password_verify($password, $row['password_hash'] ?? '')) {
            return null;
        }
        return (int) $row['id'];
    }

    public function login(int $userId): void
    {
        $_SESSION[self::USER_ID_KEY] = $userId;
    }

    public function logout(): void
    {
        unset($_SESSION[self::USER_ID_KEY]);
    }

    public function isLoggedIn(): bool
    {
        return isset($_SESSION[self::USER_ID_KEY]);
    }

    public function userId(): ?int
    {
        $id = $_SESSION[self::USER_ID_KEY] ?? null;
        return $id !== null ? (int) $id : null;
    }

    public function loginPath(): string
    {
        return $this->config['login_path'] ?? '/login';
    }
}
