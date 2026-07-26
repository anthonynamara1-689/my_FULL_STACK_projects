<?php
require_once 'auth.php';
require_login();
$page = 'reports';
require_once 'database/Database.php';

$db = Database::getInstance();
$pdo = $db->getPdo();
$report = $pdo->query("SELECT COUNT(*) AS orders, COALESCE(SUM(QuantityLiters),0) AS liters FROM SalesOrders")->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reports — Oil &amp; Gas Sales DBMS</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main">
  <header class="topbar">
    <button class="mobile-menu-btn" id="mobileMenuBtn">☰</button>
    <div class="topbar-left">
      <h1>Reports</h1>
      <p class="page-sub">Operational snapshots for the sales department</p>
    </div>
  </header>
  <div class="content">
    <div class="stats-grid">
      <div class="stat-card">
        <div>
          <div class="stat-label">Orders</div>
          <div class="stat-value"><?= (int) ($report['orders'] ?? 0) ?></div>
          <div class="stat-sub">Completed transactions</div>
        </div>
        <div class="stat-icon">📈</div>
      </div>
      <div class="stat-card">
        <div>
          <div class="stat-label">Liters Sold</div>
          <div class="stat-value"><?= number_format((float) ($report['liters'] ?? 0), 0) ?></div>
          <div class="stat-sub">Total volume moved</div>
        </div>
        <div class="stat-icon">⛽</div>
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
