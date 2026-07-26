<?php
// sidebar.php — included on every page
// $page must be set before including this file
$page = $page ?? '';
require_once 'auth.php';
?>
<aside class="sidebar">

  <div class="sidebar-logo">
    <div class="logo-badge">⛽</div>
    <div class="logo-text">
      <span class="logo-name">Oil &amp; Gas</span>
      <span class="logo-sub">Sales &amp; Customer DBMS</span>
    </div>
  </div>

  <?php include 'includes/navigation.php'; ?>

  <div class="sidebar-footer">
    <p><strong>Group Project — 2025BCS141</strong><br>
       Anthony · Oil &amp; Gas Cluster<br>
       Powered by XAMPP + MariaDB</p>
    <?php if (is_logged_in()): ?>
      <div style="margin-top:.5rem">Signed in as <strong><?= htmlspecialchars($_SESSION['employer_username'] ?? 'Employer') ?></strong></div>
      <div style="margin-top:.5rem"><a href="logout.php" class="btn btn-ghost btn-sm">Logout</a></div>
    <?php else: ?>
      <div style="margin-top:.5rem"><a href="login.php" class="btn btn-ghost btn-sm">Employer Login</a></div>
    <?php endif; ?>
  </div>

</aside>
