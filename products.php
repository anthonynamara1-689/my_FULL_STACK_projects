<?php
require_once 'auth.php';
require_login();
$page = 'products';
require 'db.php';

$msg = '';
$msg_type = '';

// ── HANDLE POST ACTIONS ────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ADD PRODUCT
    if ($action === 'add') {
        $fuel  = trim($_POST['FuelType']  ?? '');
        $price = trim($_POST['UnitPrice'] ?? '0');

        if ($fuel === '') {
            $msg = 'Fuel type is required.';
            $msg_type = 'danger';
        } else {
            $pdo->prepare("INSERT INTO Products (FuelType, UnitPrice) VALUES (?, ?)")
                ->execute([$fuel, $price]);
            header('Location: products.php?success=added');
            exit;
        }
    }

    // EDIT PRODUCT
    if ($action === 'edit') {
        $id    = (int)($_POST['ProductID'] ?? 0);
        $fuel  = trim($_POST['FuelType']   ?? '');
        $price = trim($_POST['UnitPrice']  ?? '0');

        if ($id && $fuel !== '') {
            $pdo->prepare("UPDATE Products SET FuelType=?, UnitPrice=? WHERE ProductID=?")
                ->execute([$fuel, $price, $id]);
            header('Location: products.php?success=updated');
            exit;
        } else {
            $msg = 'Fuel type is required.';
            $msg_type = 'danger';
        }
    }

    // DELETE PRODUCT
    if ($action === 'delete') {
        $id = (int)($_POST['ProductID'] ?? 0);
        if ($id) {
            $in_use = $pdo->prepare("SELECT COUNT(*) FROM SalesOrders WHERE ProductID=?");
            $in_use->execute([$id]);
            if ($in_use->fetchColumn() > 0) {
                $msg = 'Cannot delete: this product is referenced in existing sales orders.';
                $msg_type = 'danger';
            } else {
                $pdo->prepare("DELETE FROM Products WHERE ProductID=?")->execute([$id]);
                header('Location: products.php?success=deleted');
                exit;
            }
        }
    }
}

// ── SUCCESS MESSAGES ──────────────────────────────
if (isset($_GET['success'])) {
    $map = ['added' => 'Product added successfully.', 'updated' => 'Product updated.', 'deleted' => 'Product deleted.'];
    $msg = $map[$_GET['success']] ?? '';
    $msg_type = 'success';
}

// ── FETCH PRODUCTS WITH SALES STATS ───────────────
$products = $pdo->query(
    "SELECT P.ProductID, P.FuelType, P.UnitPrice,
            COUNT(O.OrderID) AS OrderCount,
            COALESCE(SUM(O.QuantityLiters), 0) AS TotalLiters,
            COALESCE(SUM(O.QuantityLiters * P.UnitPrice), 0) AS TotalRevenue
     FROM Products P
     LEFT JOIN SalesOrders O ON O.ProductID = P.ProductID
     GROUP BY P.ProductID, P.FuelType, P.UnitPrice
     ORDER BY P.ProductID ASC"
)->fetchAll();

$fuel_types = ['Jet A1 Fuel','Kerosene','LPG Gas (12kg)','Heavy Fuel Oil (HFO)','Engine Oil - 5W30','Diesel','Petrol','Aviation Gasoline'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Products — Oil &amp; Gas Sales DBMS</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main">

  <header class="topbar">
    <button class="mobile-menu-btn" id="mobileMenuBtn">☰</button>
    <div class="topbar-left">
      <h1>Products</h1>
      <p class="page-sub"><?= count($products) ?> fuel product<?= count($products) != 1 ? 's' : '' ?> in inventory</p>
    </div>
    <div class="topbar-right">
      <button class="btn btn-primary" onclick="openModal('addModal')">+ Add Product</button>
    </div>
  </header>

  <div class="content">

    <?php if ($msg): ?>
    <div class="alert alert-<?= $msg_type ?>">
      <?= $msg_type === 'success' ? '✓' : '✕' ?> <?= htmlspecialchars($msg) ?>
    </div>
    <?php endif; ?>

    <!-- PRODUCT STAT CARDS -->
    <?php if (!empty($products)):
      $max_rev = max(array_column($products, 'TotalRevenue')) ?: 1;
    ?>
    <div class="stats-grid" style="grid-template-columns:repeat(auto-fit,minmax(180px,1fr));margin-bottom:1.75rem">
      <?php foreach ($products as $p): ?>
      <div class="stat-card">
        <div>
          <div class="stat-label"><?= htmlspecialchars($p['FuelType']) ?></div>
          <div class="stat-value" style="font-size:1.4rem">
            <?= number_format($p['UnitPrice'], 0) ?> <span style="font-size:.75rem;color:var(--text-muted)">UGX</span>
          </div>
          <div class="stat-sub"><?= number_format($p['TotalLiters'], 0) ?> L sold · <?= $p['OrderCount'] ?> orders</div>
        </div>
        <div class="stat-icon">🛢</div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- TOOLBAR -->
    <div class="toolbar">
      <div class="search-wrap">
        <span class="search-icon">🔍</span>
        <input type="text" id="searchInput" placeholder="Search products…" oninput="filterTable()">
      </div>
      <span style="font-size:.8rem;color:var(--text-muted)"><?= count($products) ?> records</span>
    </div>

    <!-- TABLE -->
    <div class="panel">
      <div class="table-wrap">
        <table id="prodTable">
          <thead>
            <tr>
              <th>ID</th>
              <th>Fuel Type</th>
              <th>Unit Price (UGX)</th>
              <th>Orders</th>
              <th>Liters Sold</th>
              <th>Revenue (UGX)</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($products)): ?>
            <tr><td colspan="7">
              <div class="empty-state">
                <div class="empty-icon">🛢</div>
                <p>No products yet. Add your first fuel product above.</p>
              </div>
            </td></tr>
            <?php else: foreach ($products as $p): ?>
            <tr>
              <td class="td-id"><?= $p['ProductID'] ?></td>
              <td>
                <span class="badge badge-accent"><?= htmlspecialchars($p['FuelType']) ?></span>
              </td>
              <td class="td-mono" style="color:var(--accent);font-weight:600">
                <?= number_format($p['UnitPrice'], 2) ?>
              </td>
              <td><span class="badge badge-muted"><?= $p['OrderCount'] ?></span></td>
              <td class="td-mono"><?= number_format($p['TotalLiters'], 0) ?> L</td>
              <td class="td-mono"><?= number_format($p['TotalRevenue'], 0) ?></td>
              <td>
                <div class="td-actions">
                  <button class="btn btn-edit btn-sm"
                    onclick="openEdit(<?= $p['ProductID'] ?>, <?= htmlspecialchars(json_encode($p['FuelType'])) ?>, '<?= $p['UnitPrice'] ?>')">
                    ✎ Edit
                  </button>
                  <button class="btn btn-delete btn-sm"
                    onclick="openDelete(<?= $p['ProductID'] ?>, <?= htmlspecialchars(json_encode($p['FuelType'])) ?>)">
                    ✕
                  </button>
                </div>
              </td>
            </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div><!-- /content -->
</div><!-- /main -->

<!-- ───── ADD MODAL ───── -->
<div class="modal-overlay" id="addModal">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">➕ Add Product</span>
      <button class="modal-close" onclick="closeModal('addModal')">✕</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="add">
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Fuel Type *</label>
          <input class="form-control" type="text" name="FuelType"
                 list="fuelSuggestions" placeholder="e.g. Jet A1 Fuel" required>
          <datalist id="fuelSuggestions">
            <?php foreach ($fuel_types as $ft): ?>
            <option value="<?= htmlspecialchars($ft) ?>">
            <?php endforeach; ?>
          </datalist>
          <p class="form-hint">Type or choose from common oil &amp; gas products</p>
        </div>
        <div class="form-group">
          <label class="form-label">Unit Price (UGX per litre / unit)</label>
          <input class="form-control" type="number" name="UnitPrice"
                 step="0.01" min="0" placeholder="e.g. 6500.00" value="">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="closeModal('addModal')">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Product</button>
      </div>
    </form>
  </div>
</div>

<!-- ───── EDIT MODAL ───── -->
<div class="modal-overlay" id="editModal">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">✎ Edit Product</span>
      <button class="modal-close" onclick="closeModal('editModal')">✕</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="edit">
      <input type="hidden" name="ProductID" id="editID">
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Fuel Type *</label>
          <input class="form-control" type="text" name="FuelType" id="editFuel"
                 list="fuelSuggestions2" required>
          <datalist id="fuelSuggestions2">
            <?php foreach ($fuel_types as $ft): ?>
            <option value="<?= htmlspecialchars($ft) ?>">
            <?php endforeach; ?>
          </datalist>
        </div>
        <div class="form-group">
          <label class="form-label">Unit Price (UGX)</label>
          <input class="form-control" type="number" name="UnitPrice" id="editPrice"
                 step="0.01" min="0">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="closeModal('editModal')">Cancel</button>
        <button type="submit" class="btn btn-primary">Update Product</button>
      </div>
    </form>
  </div>
</div>

<!-- ───── DELETE MODAL ───── -->
<div class="modal-overlay" id="deleteModal">
  <div class="modal" style="max-width:400px">
    <div class="modal-header" style="border-bottom-color:rgba(217,64,64,.3)">
      <span class="modal-title" style="color:var(--danger)">⚠ Delete Product</span>
      <button class="modal-close" onclick="closeModal('deleteModal')">✕</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="delete">
      <input type="hidden" name="ProductID" id="deleteID">
      <div class="modal-body">
        <p style="font-size:.9rem;line-height:1.65">
          Delete <strong id="deleteName" style="color:var(--text)"></strong>?<br>
          <span style="color:var(--text-muted);font-size:.82rem">Products with sales orders cannot be deleted.</span>
        </p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="closeModal('deleteModal')">Cancel</button>
        <button type="submit" class="btn btn-sm" style="background:var(--danger);color:#fff;border:none">Delete</button>
      </div>
    </form>
  </div>
</div>

<script>
function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

document.querySelectorAll('.modal-overlay').forEach(el => {
  el.addEventListener('click', e => { if (e.target === el) el.classList.remove('open'); });
});

function openEdit(id, fuel, price) {
  document.getElementById('editID').value    = id;
  document.getElementById('editFuel').value  = fuel;
  document.getElementById('editPrice').value = price;
  openModal('editModal');
}

function openDelete(id, name) {
  document.getElementById('deleteID').value         = id;
  document.getElementById('deleteName').textContent = name;
  openModal('deleteModal');
}

function filterTable() {
  const val = document.getElementById('searchInput').value.toLowerCase();
  document.querySelectorAll('#prodTable tbody tr').forEach(row => {
    row.style.display = row.textContent.toLowerCase().includes(val) ? '' : 'none';
  });
}

// Mobile menu toggle
document.addEventListener('DOMContentLoaded', function() {
  const mobileMenuBtn = document.getElementById('mobileMenuBtn');
  const sidebar = document.querySelector('.sidebar');
  
  if (mobileMenuBtn) {
    mobileMenuBtn.addEventListener('click', function(e) {
      e.stopPropagation();
      sidebar.classList.toggle('mobile-open');
    });
    
    document.querySelectorAll('.nav-item').forEach(item => {
      item.addEventListener('click', function() {
        sidebar.classList.remove('mobile-open');
      });
    });
    
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
