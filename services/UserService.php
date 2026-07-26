<?php

declare(strict_types=1);

class UserService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function ensureUsersTable(): void
    {
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            password_hash TEXT NOT NULL,
            role TEXT DEFAULT 'user'
        )");
    }

    public function createUser(string $username, string $password, string $role = 'user'): int
    {
        $username = trim($username);
        $password = trim($password);
        $role = in_array($role, ['admin', 'user'], true) ? $role : 'user';

        if ($username === '' || $password === '') {
            throw new InvalidArgumentException('Username and password are required.');
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare('INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)');
        $stmt->execute([$username, $hash, $role]);

        return (int) $this->pdo->lastInsertId();
    }
}
