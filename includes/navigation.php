<?php
$modules = [
    ['label' => 'Dashboard', 'page' => 'dashboard', 'url' => 'index.php', 'icon' => '▦'],
    ['label' => 'Sales', 'page' => 'sales', 'url' => 'sales.php', 'icon' => '📋'],
    ['label' => 'Payments', 'page' => 'payments', 'url' => 'payments.php', 'icon' => '💳'],
    ['label' => 'Customers', 'page' => 'customers', 'url' => 'customers.php', 'icon' => '👥'],
    ['label' => 'Products', 'page' => 'products', 'url' => 'products.php', 'icon' => '🛢'],
    ['label' => 'Inventory', 'page' => 'inventory', 'url' => 'inventory.php', 'icon' => '📦'],
    ['label' => 'Reports', 'page' => 'reports', 'url' => 'reports.php', 'icon' => '📈'],
    ['label' => 'Users', 'page' => 'users', 'url' => 'admin_users.php', 'icon' => '👤'],
    ['label' => 'Settings', 'page' => 'settings', 'url' => 'settings.php', 'icon' => '⚙️'],
];
?>

<div class="sidebar-section">
  <span class="sidebar-label">Modules</span>
  <?php foreach ($modules as $module): ?>
    <?php $active = ($page ?? '') === $module['page']; ?>
    <a href="<?= htmlspecialchars($module['url']) ?>" class="nav-item <?= $active ? 'active' : '' ?>">
      <span class="nav-icon"><?= htmlspecialchars($module['icon']) ?></span> <?= htmlspecialchars($module['label']) ?>
    </a>
  <?php endforeach; ?>
</div>
