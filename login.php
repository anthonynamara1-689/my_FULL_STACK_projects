<?php
require 'db.php';
require_once 'auth.php';

if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$error = '';
$redirect = $_GET['redirect'] ?? 'index.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if (attempt_login($pdo, $username, $password)) {
        // guard open-redirects: allow only local paths
        if (strpos($redirect, '/') !== 0) $redirect = 'index.php';
        header('Location: ' . $redirect);
        exit;
    } else {
        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Login — Oil & Gas Sales DBMS</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/style.css">
  <style>
    .login-wrap{max-width:420px;margin:6vh auto;padding:2rem;background:var(--surface);border:1px solid var(--border);border-radius:8px}
    .login-title{font-size:1.25rem;margin-bottom:.25rem}
    .login-sub{color:var(--text-muted);font-size:.9rem;margin-bottom:1rem}
    .form-group{margin-bottom:.75rem}
  </style>
</head>
<body>

<div style="display:flex;align-items:center;justify-content:center;min-height:100vh;background:var(--bg)">
  <div class="login-wrap">
    <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1rem">
      <div style="font-size:1.6rem">⛽</div>
      <div>
        <div class="login-title">Employer Login</div>
        <div class="login-sub">Sign in to manage sales, customers and products</div>
      </div>
    </div>

    <?php if ($error): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">
      <div class="form-group">
        <label class="form-label">Username</label>
        <input class="form-control" type="text" name="username" required autofocus>
      </div>
      <div class="form-group">
        <label class="form-label">Password</label>
        <input class="form-control" type="password" name="password" required>
      </div>
      <div style="display:flex;justify-content:space-between;align-items:center;margin-top:1rem">
        <div style="font-size:.85rem;color:var(--text-muted)">Default: admin / admin123</div>
        <button type="submit" class="btn btn-primary">Sign in</button>
      </div>
    </form>
  </div>
</div>

</body>
</html>
