<?php
require_once 'auth.php';
require_login();
$page = 'settings';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Settings — Oil &amp; Gas Sales DBMS</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main">
  <header class="topbar">
    <button class="mobile-menu-btn" id="mobileMenuBtn">☰</button>
    <div class="topbar-left">
      <h1>Settings</h1>
      <p class="page-sub">System configuration and preferences</p>
    </div>
  </header>
  <div class="content">
    <div class="panel">
      <div class="panel-header"><span class="panel-title">⚙️ Preferences</span></div>
      <div class="panel-body">
        <p style="color:var(--text-muted)">The settings module is ready for future integrations such as business profile updates, export preferences, and notification controls.</p>
      </div>
    </div>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
  const mobileMenuBtn = document.getElementById('mobileMenuBtn');
  const sidebar = document.querySelector('.sidebar');
  if (mobileMenuBtn) {
    mobileMenuBtn.addEventListener('click', function(e) {
      e.stopPropagation();
      sidebar.classList.toggle('mobile-open');
    });
    document.querySelectorAll('.nav-item').forEach(item => item.addEventListener('click', function() { sidebar.classList.remove('mobile-open'); }));
    document.addEventListener('click', function(e) { if (!sidebar.contains(e.target) && !mobileMenuBtn.contains(e.target)) sidebar.classList.remove('mobile-open'); });
  }
});
</script>
</body>
</html>
