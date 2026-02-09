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
            'SELECT id, password_hash, is_locked FROM users WHERE username = ? LIMIT 1',
            [$username]
        );
        if ($row === null) {
            return null;
        }
        if (!empty($row['is_locked'])) {
            return null; // User is locked (cannot login)
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
            'SELECT id, username, last_login_at, click_counter, is_locked, is_muted, created_at FROM users WHERE id = ? LIMIT 1',
            [$userId]
        );
    }

    /** Increment click counter for current user (no-op if user is muted). */
    public function incrementClickCounter(Connection $db): void
    {
        $userId = $this->userId();
        if ($userId === null) {
            return;
        }
        $row = $db->fetchOne('SELECT is_muted FROM users WHERE id = ?', [$userId]);
        if (!empty($row['is_muted'])) {
            return; // Muted users cannot click
        }
        $db->execute(
            'UPDATE users SET click_counter = click_counter + 1 WHERE id = ?',
            [$userId]
        );
    }

    /** Decrement click counter for current user (no-op if user is muted). */
    public function decrementClickCounter(Connection $db): void
    {
        $userId = $this->userId();
        if ($userId === null) {
            return;
        }
        $row = $db->fetchOne('SELECT is_muted FROM users WHERE id = ?', [$userId]);
        if (!empty($row['is_muted'])) {
            return;
        }
        $db->execute(
            'UPDATE users SET click_counter = GREATEST(0, click_counter - 1) WHERE id = ?',
            [$userId]
        );
    }

    /** List all users (id, username, click_counter, is_locked, is_muted). */
    public function listUsers(Connection $db): array
    {
        $rows = $db->fetchAll('SELECT id, username, click_counter, is_locked, is_muted FROM users ORDER BY username');
        return array_map(function ($r) {
            return [
                'id' => (int) $r['id'],
                'username' => $r['username'],
                'click_counter' => (int) ($r['click_counter'] ?? 0),
                'is_locked' => !empty($r['is_locked']),
                'is_muted' => !empty($r['is_muted']),
            ];
        }, $rows);
    }

    /** Delete user by id. Returns true if deleted. */
    public function deleteUser(Connection $db, int $targetUserId): bool
    {
        $db->execute('DELETE FROM users WHERE id = ?', [$targetUserId]);
        return true;
    }

    /** Toggle lock (ability to login). Returns new state: true = locked, false = unlocked. */
    public function toggleUserLock(Connection $db, int $targetUserId): bool
    {
        $row = $db->fetchOne('SELECT is_locked FROM users WHERE id = ?', [$targetUserId]);
        if ($row === null) {
            return false;
        }
        $newLock = empty($row['is_locked']) ? 1 : 0;
        $db->execute('UPDATE users SET is_locked = ? WHERE id = ?', [$newLock, $targetUserId]);
        return (bool) $newLock;
    }

    /** Toggle mute (ability to click). Returns new state: true = muted, false = unmuted. */
    public function toggleUserMute(Connection $db, int $targetUserId): bool
    {
        $row = $db->fetchOne('SELECT is_muted FROM users WHERE id = ?', [$targetUserId]);
        if ($row === null) {
            return false;
        }
        $newMute = empty($row['is_muted']) ? 1 : 0;
        $db->execute('UPDATE users SET is_muted = ? WHERE id = ?', [$newMute, $targetUserId]);
        return (bool) $newMute;
    }

    /**
     * Validate password complexity.
     * Requirements: 8+ chars, contains a-zA-Z, 0-9, and special characters.
     * Returns error message string if invalid, null if valid.
     */
    public function validatePassword(string $password): ?string
    {
        if (strlen($password) < 8) {
            return 'Password must be at least 8 characters long';
        }

        if (!preg_match('/[a-zA-Z]/', $password)) {
            return 'Password must contain at least one letter (a-z, A-Z)';
        }

        if (!preg_match('/[0-9]/', $password)) {
            return 'Password must contain at least one digit (0-9)';
        }

        if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
            return 'Password must contain at least one special character';
        }

        return null; // Valid
    }

    /**
     * Create a new user.
     * Returns array: ['success' => true, 'userId' => int] or ['success' => false, 'error' => string]
     */
    public function createUser(Connection $db, string $username, string $password): array
    {
        $username = trim($username);
        if ($username === '') {
            return ['success' => false, 'error' => 'Username is required'];
        }

        if ($password === '') {
            return ['success' => false, 'error' => 'Password is required'];
        }

        // Validate password complexity
        $passwordError = $this->validatePassword($password);
        if ($passwordError !== null) {
            return ['success' => false, 'error' => $passwordError];
        }

        // Check for duplicate username
        $existing = $db->fetchOne('SELECT id FROM users WHERE username = ? LIMIT 1', [$username]);
        if ($existing !== null) {
            return ['success' => false, 'error' => 'Username already exists'];
        }

        // Create user
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $db->execute(
            'INSERT INTO users (username, password_hash) VALUES (?, ?)',
            [$username, $passwordHash]
        );

        return ['success' => true, 'userId' => (int) $db->lastInsertId()];
    }
}
