<?php
require_once 'auth.php';
require_login();
$page = 'inventory';
require_once 'database/Database.php';

$db = Database::getInstance();
$pdo = $db->getPdo();
$inventory = $pdo->query('SELECT ProductID, FuelType, UnitPrice FROM Products ORDER BY ProductID')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Inventory — Oil &amp; Gas Sales DBMS</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main">
  <header class="topbar">
    <button class="mobile-menu-btn" id="mobileMenuBtn">☰</button>
    <div class="topbar-left">
      <h1>Inventory</h1>
      <p class="page-sub">Review product availability and pricing</p>
    </div>
  </header>
  <div class="content">
    <div class="panel">
      <div class="panel-header"><span class="panel-title">📦 Available Products</span></div>
      <div class="table-wrap">
        <table>
          <thead><tr><th>ID</th><th>Fuel Type</th><th>Unit Price</th></tr></thead>
          <tbody>
            <?php if (empty($inventory)): ?>
            <tr><td colspan="3"><div class="empty-state"><p>No inventory records available.</p></div></td></tr>
            <?php else: foreach ($inventory as $item): ?>
            <tr>
              <td class="td-id">#<?= (int) ($item['ProductID'] ?? 0) ?></td>
              <td class="td-primary"><?= htmlspecialchars((string) ($item['FuelType'] ?? '')) ?></td>
              <td class="td-mono">UGX <?= number_format((float) ($item['UnitPrice'] ?? 0), 0) ?></td>
            </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
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
