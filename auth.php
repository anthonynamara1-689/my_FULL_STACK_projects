<?php
require_once __DIR__ . '/services/AuthService.php';

function is_logged_in(): bool {
    return AuthService::isLoggedIn();
}

function require_login(): void {
    AuthService::requireLogin();
}

function attempt_login(PDO $pdo, string $username, string $password): bool {
    return AuthService::attemptLogin($pdo, $username, $password);
}

function logout(): void {
    AuthService::logout();
}

