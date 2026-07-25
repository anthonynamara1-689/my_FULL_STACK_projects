<?php
require_once 'db.php';
require_once 'auth.php';

if (!is_logged_in()) {
    header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$current = $_SESSION['employer_username'] ?? '';
$admin_user = getenv('EMPLOYER_USER') ?: 'admin';
// allow only the primary admin (env var) to manage users
if ($current !== $admin_user) {
    http_response_code(403);
    echo "<p style='padding:2rem;font-family:sans-serif;color:#d8dde1;background:#080a0b'>Forbidden: only the administrator can manage users.</p>";
    exit;
}

$error = '';
$success = '';

// ensure users table exists (simple migration)
$pdo->exec("CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    role TEXT DEFAULT 'user'
)");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = in_array($_POST['role'] ?? 'user', ['admin','user']) ? $_POST['role'] : 'user';

    if ($username === '' || $password === '') {
        $error = 'Username and password are required.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        try {
            $stmt = $pdo->prepare('INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)');
            $stmt->execute([$username, $hash, $role]);
            $success = 'User created successfully.';
        } catch (PDOException $e) {
            $error = 'Failed to create user: ' . $e->getMessage();
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>User Management — Oil & Gas Sales DBMS</title>
  <link rel="stylesheet" href="assets/style.css">
  <style>.users-wrap{max-width:760px;margin:2rem auto;padding:1.25rem}</style>
</head>
<body>
<?php $page = 'users'; include 'sidebar.php'; ?>
<div class="main">
  <div class="topbar">
    <div class="topbar-left">
      <h1>Users</h1>
      <div class="page-sub">Create and manage application users</div>
    </div>
    <div class="topbar-right"></div>
  </div>

  <div class="content">
    <div class="panel users-wrap">
      <div class="panel-header"><div class="panel-title">Create User</div></div>
      <div class="panel-body">
        <?php if ($error): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
          <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST">
          <div class="form-group">
            <label class="form-label">Username</label>
            <input class="form-control" type="text" name="username" required>
          </div>
          <div class="form-group">
            <label class="form-label">Password</label>
            <input class="form-control" type="password" name="password" required>
          </div>
          <div class="form-group">
            <label class="form-label">Role</label>
            <select name="role" class="form-control">
              <option value="user">User</option>
              <option value="admin">Administrator</option>
            </select>
          </div>
          <div style="display:flex;justify-content:flex-end;gap:.5rem">
            <a href="index.php" class="btn btn-ghost">Cancel</a>
            <button class="btn btn-primary" type="submit">Create User</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
</body>
</html>
