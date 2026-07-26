<?php
require_once 'auth.php';
require_login();
$page = 'dashboard';
require_once 'database/Database.php';
require_once 'services/DashboardService.php';

$db = Database::getInstance();
$pdo = $db->getPdo();
$dashboardService = new DashboardService($pdo);

$stats = $dashboardService->getDashboardStats();
$recent_orders = $dashboardService->getRecentOrders();
$top_customers = $dashboardService->getTopCustomers();
$fuel_sales = $dashboardService->getFuelSales();
$over_limit = $dashboardService->getCreditAlertCustomers();

$max_spend = count($top_customers) > 0 ? $top_customers[0]['Total_Spent'] : 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard — Oil &amp; Gas Sales DBMS</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main">

  <!-- TOPBAR -->
  <header class="topbar">
    <button class="mobile-menu-btn" id="mobileMenuBtn">☰</button>
    <div class="topbar-left">
      <h1>Dashboard</h1>
      <p class="page-sub">Overview of sales, products &amp; customers</p>
    </div>
    <!-- topbar-right removed on dashboard per request -->
  </header>

  <div class="content">

    <!-- CREDIT LIMIT ALERT -->
    <?php if (count($over_limit) > 0): ?>
    <div class="alert alert-danger">
      ⚠&nbsp; <strong><?= count($over_limit) ?> customer<?= count($over_limit) > 1 ? 's' : '' ?></strong>
      <?= count($over_limit) > 1 ? 'have' : 'has' ?> exceeded 80% of their credit limit —
      <?= implode(', ', array_column($over_limit, 'CustomerName')) ?>
    </div>
    <?php endif; ?>

    <!-- KPI STATS -->
    <div class="stats-grid">
      <div class="stat-card highlight">
        <div>
          <div class="stat-label">Revenue</div>
          <div class="stat-value">UGX <?= number_format($stats['revenue'], 0) ?></div>
          <div class="stat-sub"><?= number_format($stats['liters'], 0) ?> liters sold</div>
        </div>
        <div class="stat-icon">💰</div>
      </div>
      <div class="stat-card">
        <div>
          <div class="stat-label">Sales This Month</div>
          <div class="stat-value"><?= $stats['orders'] ?></div>
          <div class="stat-sub">Orders captured</div>
        </div>
        <div class="stat-icon">📋</div>
      </div>
      <div class="stat-card">
        <div>
          <div class="stat-label">Customers</div>
          <div class="stat-value"><?= $stats['customers'] ?></div>
          <div class="stat-sub">Registered accounts</div>
        </div>
        <div class="stat-icon">👥</div>
      </div>
      <div class="stat-card">
        <div>
          <div class="stat-label">Products</div>
          <div class="stat-value"><?= $stats['products'] ?></div>
          <div class="stat-sub">Fuel types available</div>
        </div>
        <div class="stat-icon">🛢</div>
      </div>
    </div>

    <div class="panel">
      <div class="panel-header">
        <span class="panel-title">📊 Business Snapshot</span>
      </div>
      <div class="panel-body">
        <p style="color:var(--text-muted)">This dashboard is now powered by reusable services and is ready for future modules such as payments, inventory, and accounting.</p>
      </div>
    </div>

    <!-- ROW: RECENT ORDERS + FUEL BREAKDOWN -->
    <div class="grid-2">

      <!-- RECENT ORDERS -->
      <div class="panel">
        <div class="panel-header">
          <span class="panel-title">⚡ Recent Orders</span>
          <a href="sales.php" class="btn btn-ghost btn-sm">View All →</a>
        </div>
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>#</th>
                <th>Customer</th>
                <th>Fuel Type</th>
                <th>Liters</th>
                <th>Amount (UGX)</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($recent_orders)): ?>
              <tr><td colspan="5">
                <div class="empty-state"><div class="empty-icon">📭</div><p>No orders yet</p></div>
              </td></tr>
              <?php else: foreach ($recent_orders as $o): ?>
              <tr>
                <td class="td-id">#<?= $o['OrderID'] ?></td>
                <td class="td-primary"><?= htmlspecialchars($o['CustomerName']) ?></td>
                <td><span class="badge badge-accent"><?= htmlspecialchars($o['FuelType']) ?></span></td>
                <td class="td-mono"><?= number_format($o['QuantityLiters'], 0) ?> L</td>
                <td class="td-mono" style="color:var(--accent)"><?= number_format($o['TotalAmount'], 0) ?></td>
              </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- FUEL TYPE BREAKDOWN -->
      <div class="panel">
        <div class="panel-header">
          <span class="panel-title">🛢 Sales by Fuel Type</span>
        </div>
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>Fuel Type</th>
                <th>Liters Sold</th>
                <th>Orders</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($fuel_sales)): ?>
              <tr><td colspan="3"><div class="empty-state"><div class="empty-icon">📊</div><p>No data</p></div></td></tr>
              <?php else: foreach ($fuel_sales as $f): ?>
              <tr>
                <td class="td-primary"><?= htmlspecialchars($f['FuelType']) ?></td>
                <td class="td-mono"><?= number_format($f['Total_Liters_Sold'], 0) ?> L</td>
                <td><span class="badge badge-muted"><?= $f['Number_of_Orders'] ?></span></td>
              </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div><!-- /grid-2 -->

    <!-- TOP CUSTOMERS + CREDIT LIMIT TABLE -->
    <div class="grid-2">

      <!-- TOP CUSTOMERS -->
      <div class="panel">
        <div class="panel-header">
          <span class="panel-title">🏆 Top Customers by Spend</span>
        </div>
        <div class="panel-body">
          <?php if (empty($top_customers)): ?>
          <div class="empty-state"><div class="empty-icon">👥</div><p>No customer data</p></div>
          <?php else: foreach ($top_customers as $i => $c):
            $pct = $max_spend > 0 ? ($c['Total_Spent'] / $max_spend * 100) : 0;
          ?>
          <div style="margin-bottom:1.125rem">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:5px">
              <span style="font-size:.875rem;font-weight:500">
                <span style="color:var(--text-muted);font-size:.75rem;margin-right:.4rem"><?= $i+1 ?>.</span>
                <?= htmlspecialchars($c['CustomerName']) ?>
              </span>
              <span style="font-family:'Courier New',monospace;font-size:.8rem;color:var(--accent)">
                UGX <?= number_format($c['Total_Spent'], 0) ?>
              </span>
            </div>
            <div class="credit-bar-track">
              <div class="credit-bar-fill"
                   style="width:<?= round($pct) ?>%;background:var(--accent);opacity:<?= 1 - ($i * 0.15) ?>">
              </div>
            </div>
            <span class="credit-pct"><?= $c['Orders'] ?> order<?= $c['Orders'] != 1 ? 's' : '' ?></span>
          </div>
          <?php endforeach; endif; ?>
        </div>
      </div>

      <!-- CREDIT LIMIT STATUS -->
      <div class="panel">
        <div class="panel-header">
          <span class="panel-title">⚠ Credit Limit Status</span>
          <a href="customers.php" class="btn btn-ghost btn-sm">Manage →</a>
        </div>
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>Customer</th>
                <th>Credit Limit</th>
                <th>Current Debt</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $all_credit = $pdo->query(
                  "SELECT CustomerName, CreditLimit,
                          (SELECT COALESCE(SUM(O.QuantityLiters * P.UnitPrice),0)
                           FROM SalesOrders O
                           JOIN Products P ON O.ProductID = P.ProductID
                           WHERE O.CustomerID = Customers.CustomerID) AS Current_Debt
                   FROM Customers"
              )->fetchAll();
              if (empty($all_credit)):
              ?>
              <tr><td colspan="4"><div class="empty-state"><p>No customers</p></div></td></tr>
              <?php else: foreach ($all_credit as $c):
                $ratio = $c['CreditLimit'] > 0 ? ($c['Current_Debt'] / $c['CreditLimit']) : 0;
                if ($ratio > 0.8) { $badge = 'badge-danger'; $label = 'Over 80%'; }
                elseif ($ratio > 0.5) { $badge = 'badge-warning'; $label = 'Moderate'; }
                else { $badge = 'badge-success'; $label = 'Good'; }
              ?>
              <tr>
                <td class="td-primary" style="font-size:.84rem"><?= htmlspecialchars($c['CustomerName']) ?></td>
                <td class="td-mono" style="font-size:.8rem"><?= number_format($c['CreditLimit'], 0) ?></td>
                <td class="td-mono" style="font-size:.8rem"><?= number_format($c['Current_Debt'], 0) ?></td>
                <td><span class="badge <?= $badge ?>"><?= $label ?></span></td>
              </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div><!-- /grid-2 -->

  </div><!-- /content -->
</div><!-- /main -->

<script>
// Mobile menu toggle
document.addEventListener('DOMContentLoaded', function() {
  const mobileMenuBtn = document.getElementById('mobileMenuBtn');
  const sidebar = document.querySelector('.sidebar');
  
  if (mobileMenuBtn) {
    mobileMenuBtn.addEventListener('click', function(e) {
      e.stopPropagation();
      sidebar.classList.toggle('mobile-open');
    });
    
    // Close menu when clicking on a nav item
    document.querySelectorAll('.nav-item').forEach(item => {
      item.addEventListener('click', function() {
        sidebar.classList.remove('mobile-open');
      });
    });
    
    // Close menu when clicking outside
    document.addEventListener('click', function(e) {
      if (!sidebar.contains(e.target) && !mobileMenuBtn.contains(e.target)) {
        sidebar.classList.remove('mobile-open');
      }
    });
  }
});
</script>

</body>
</html>
