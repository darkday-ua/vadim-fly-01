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

    public function login(Connection $db, int $userId): void
    {
        $_SESSION[self::USER_ID_KEY] = $userId;
        
        // Record last login time
        $db->execute(
            'UPDATE users SET last_login_at = NOW() WHERE id = ?',
            [$userId]
        );
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

    /** Get current user data (username, last_login_at, click_counter, etc.) or null if not logged in. */
    public function user(Connection $db): ?array
    {
        $userId = $this->userId();
        if ($userId === null) {
            return null;
        }
        return $db->fetchOne(
            'SELECT id, username, last_login_at, click_counter, created_at FROM users WHERE id = ? LIMIT 1',
            [$userId]
        );
    }

    /** Increment click counter for current user. */
    public function incrementClickCounter(Connection $db): void
    {
        $userId = $this->userId();
        if ($userId === null) {
            return;
        }
        $db->execute(
            'UPDATE users SET click_counter = click_counter + 1 WHERE id = ?',
            [$userId]
        );
    }

    /** Decrement click counter for current user. */
    public function decrementClickCounter(Connection $db): void
    {
        $userId = $this->userId();
        if ($userId === null) {
            return;
        }
        $db->execute(
            'UPDATE users SET click_counter = GREATEST(0, click_counter - 1) WHERE id = ?',
            [$userId]
        );
    }
}
