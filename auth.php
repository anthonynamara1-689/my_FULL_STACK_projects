<?php
// auth.php — simple session-based auth helpers for employer login
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function is_logged_in(): bool {
    return !empty($_SESSION['employer_logged_in']);
}

function require_login(): void {
    if (!is_logged_in()) {
        $redirect = $_SERVER['REQUEST_URI'] ?? '/index.php';
        header('Location: login.php?redirect=' . urlencode($redirect));
        exit;
    }
}

function attempt_login(PDO $pdo, string $username, string $password): bool {
    $username = trim($username);
    if ($username === '' || $password === '') return false;

    $tables = ['Employers','employers','Users','users','admins','Admins','administrators','Administrators'];
    $pw_fields = ['password', 'password_hash', 'pass', 'Pass', 'Password'];

    foreach ($tables as $tbl) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM {$tbl} WHERE username = ? LIMIT 1");
            $stmt->execute([$username]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                foreach ($pw_fields as $f) {
                    if (isset($row[$f])) {
                        $stored = $row[$f];
                        if (password_verify($password, $stored)) {
                            $_SESSION['employer_logged_in'] = true;
                            $_SESSION['employer_username']   = $username;
                            return true;
                        }
                        // fallback: plain-text match (not recommended but supported)
                        if ($stored === $password) {
                            $_SESSION['employer_logged_in'] = true;
                            $_SESSION['employer_username']   = $username;
                            return true;
                        }
                        return false;
                    }
                }
                // no recognizable password field — deny
                return false;
            }
        } catch (PDOException $e) {
            // table might not exist or other error; ignore and try next
        }
    }

    // Fallback to environment variables or default credentials
    $env_user = getenv('EMPLOYER_USER') ?: 'admin';
    $env_pass = getenv('EMPLOYER_PASS') ?: 'admin123';
    if ($username === $env_user && $password === $env_pass) {
        $_SESSION['employer_logged_in'] = true;
        $_SESSION['employer_username']   = $username;
        return true;
    }

    return false;
}

function logout(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'], $params['secure'], $params['httponly']
        );
    }
    session_destroy();
}

?>
