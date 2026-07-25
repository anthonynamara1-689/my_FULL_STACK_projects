<?php
// ─────────────────────────────────────────────────
//  DATABASE CONNECTION — SQLite (Local)
//  Using SQLite for local development
// ─────────────────────────────────────────────────
$db_path = __DIR__ . '/sales.db';

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $dsn = "sqlite:" . $db_path;
    $pdo = new PDO($dsn, null, null, $options);
    // Enable foreign keys in SQLite
    $pdo->exec("PRAGMA foreign_keys = ON");
} catch (PDOException $e) {
    die('
    <!DOCTYPE html><html lang="en">
    <head><meta charset="UTF-8"><title>Connection Error</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
      body{background:#080a0b;color:#d8dde1;font-family:Source Sans Pro,sans-serif;
           display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;}
      .err{text-align:center;max-width:480px;padding:2rem;}
      .err h2{font-family:"Source Sans Pro",sans-serif;font-size:1.6rem;
              color:#e8820c;letter-spacing:.06em;margin-bottom:.75rem;}
      .err code{display:block;background:#141819;border:1px solid #1e2427;
                padding:1rem;border-radius:6px;font-size:.8rem;color:#d94040;
                margin:1rem 0;text-align:left;word-break:break-all;}
      .err p{font-size:.875rem;color:#56646f;line-height:1.6;}
      .err a{color:#e8820c;text-decoration:none;}
    </style></head><body>
    <div class="err">
      <div style="font-size:2.5rem;margin-bottom:1rem">⚠️</div>
      <h2>Database Connection Failed</h2>
      <code>' . htmlspecialchars($e->getMessage()) . '</code>
      <p>Database file: <code style="display:inline;padding:.1rem .4rem">' . htmlspecialchars($db_path) . '</code></p>
    </div></body></html>
    ');
}
?>
