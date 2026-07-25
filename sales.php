<?php
require_once 'auth.php';
require_login();
$page = 'sales';
require 'db.php';

$msg = '';
$msg_type = '';

// ── HANDLE POST ACTIONS ────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ADD ORDER
    if ($action === 'add') {
        $customer_id = (int)($_POST['CustomerID']     ?? 0);
        $product_id  = (int)($_POST['ProductID']      ?? 0);
        $qty         = trim($_POST['QuantityLiters']  ?? '');
        $date        = trim($_POST['OrderDate']        ?? date('Y-m-d H:i:s'));

        if (!$customer_id || !$product_id || $qty === '') {
            $msg = 'Customer, product and quantity are required.';
            $msg_type = 'danger';
        } else {
            $pdo->prepare(
                "INSERT INTO SalesOrders (CustomerID, ProductID, QuantityLiters, OrderDate) VALUES (?, ?, ?, ?)"
            )->execute([$customer_id, $product_id, $qty, $date]);
            header('Location: sales.php?success=added');
            exit;
        }
    }

    // EDIT ORDER
    if ($action === 'edit') {
        $id          = (int)($_POST['OrderID']        ?? 0);
        $customer_id = (int)($_POST['CustomerID']     ?? 0);
        $product_id  = (int)($_POST['ProductID']      ?? 0);
        $qty         = trim($_POST['QuantityLiters']  ?? '');
        $date        = trim($_POST['OrderDate']        ?? '');

        if ($id && $customer_id && $product_id && $qty !== '') {
            $pdo->prepare(
                "UPDATE SalesOrders SET CustomerID=?, ProductID=?, QuantityLiters=?, OrderDate=? WHERE OrderID=?"
            )->execute([$customer_id, $product_id, $qty, $date, $id]);
            header('Location: sales.php?success=updated');
            exit;
        } else {
            $msg = 'All fields are required.';
            $msg_type = 'danger';
        }
    }

    // DELETE ORDER
    if ($action === 'delete') {
        $id = (int)($_POST['OrderID'] ?? 0);
        if ($id) {
            $pdo->prepare("DELETE FROM SalesOrders WHERE OrderID=?")->execute([$id]);
            header('Location: sales.php?success=deleted');
            exit;
        }
    }
}

// ── SUCCESS MESSAGES ──────────────────────────────
if (isset($_GET['success'])) {
    $map = ['added' => 'Order placed successfully.', 'updated' => 'Order updated.', 'deleted' => 'Order deleted.'];
    $msg = $map[$_GET['success']] ?? '';
    $msg_type = 'success';
}

// ── FETCH ALL ORDERS ──────────────────────────────
$orders = $pdo->query(
    "SELECT O.OrderID, O.OrderDate, ROUND(O.QuantityLiters, 2) AS QuantityLiters,
            O.CustomerID, O.ProductID,
            C.CustomerName, P.FuelType, P.UnitPrice,
            ROUND(O.QuantityLiters * P.UnitPrice, 2) AS TotalAmount
     FROM SalesOrders O
     JOIN Customers C ON O.CustomerID = C.CustomerID
     JOIN Products  P ON O.ProductID  = P.ProductID
     ORDER BY O.OrderDate DESC, O.OrderID DESC"
)->fetchAll();

// ── DROPDOWN DATA ─────────────────────────────────
$customers = $pdo->query("SELECT CustomerID, CustomerName FROM Customers ORDER BY CustomerName")->fetchAll();
$products  = $pdo->query("SELECT ProductID, FuelType, UnitPrice FROM Products ORDER BY FuelType")->fetchAll();

// ── ORDER STATS ───────────────────────────────────
$total_revenue  = array_sum(array_column($orders, 'TotalAmount'));
$total_liters   = array_sum(array_column($orders, 'QuantityLiters'));
$large_orders   = array_filter($orders, fn($o) => $o['QuantityLiters'] >= 10000);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sales Orders — Oil &amp; Gas Sales DBMS</title>
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
      <h1>Sales Orders</h1>
      <p class="page-sub"><?= count($orders) ?> order<?= count($orders) != 1 ? 's' : '' ?> total</p>
    </div>
    <div class="topbar-right">
      <button class="btn btn-primary" onclick="openModal('addModal')">+ New Order</button>
    </div>
  </header>

  <div class="content">

    <?php if ($msg): ?>
    <div class="alert alert-<?= $msg_type ?>">
      <?= $msg_type === 'success' ? '✓' : '✕' ?> <?= htmlspecialchars($msg) ?>
    </div>
    <?php endif; ?>

    <!-- STATS -->
    <div class="stats-grid" style="margin-bottom:1.75rem">
      <div class="stat-card highlight">
        <div>
          <div class="stat-label">Total Revenue</div>
          <div class="stat-value">UGX <?= number_format($total_revenue, 0) ?></div>
          <div class="stat-sub">From all orders</div>
        </div>
        <div class="stat-icon">💰</div>
      </div>
      <div class="stat-card">
        <div>
          <div class="stat-label">Total Volume</div>
          <div class="stat-value"><?= number_format($total_liters, 0) ?></div>
          <div class="stat-sub">Litres dispatched</div>
        </div>
        <div class="stat-icon">⛽</div>
      </div>
      <div class="stat-card">
        <div>
          <div class="stat-label">Total Orders</div>
          <div class="stat-value"><?= count($orders) ?></div>
          <div class="stat-sub">All time</div>
        </div>
        <div class="stat-icon">📋</div>
      </div>
      <div class="stat-card">
        <div>
          <div class="stat-label">Large Orders</div>
          <div class="stat-value"><?= count($large_orders) ?></div>
          <div class="stat-sub">≥ 10,000 litres</div>
        </div>
        <div class="stat-icon">🚚</div>
      </div>
    </div>

    <!-- TOOLBAR -->
    <div class="toolbar">
      <div class="search-wrap">
        <span class="search-icon">🔍</span>
        <input type="text" id="searchInput" placeholder="Search orders…" oninput="filterTable()">
      </div>
      <div style="display:flex;gap:.5rem;align-items:center">
        <select id="filterFuel" class="form-control" style="width:auto;font-size:.8rem;padding:.4rem .75rem" onchange="filterTable()">
          <option value="">All Fuel Types</option>
          <?php foreach ($products as $p): ?>
          <option value="<?= htmlspecialchars($p['FuelType']) ?>"><?= htmlspecialchars($p['FuelType']) ?></option>
          <?php endforeach; ?>
        </select>
        <span style="font-size:.8rem;color:var(--text-muted)"><?= count($orders) ?> records</span>
      </div>
    </div>

    <!-- TABLE -->
    <div class="panel">
      <div class="table-wrap">
        <table id="salesTable">
          <thead>
            <tr>
              <th>Order ID</th>
              <th>Date</th>
              <th>Customer</th>
              <th>Fuel Type</th>
              <th>Qty (Litres)</th>
              <th>Unit Price</th>
              <th>Total (UGX)</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($orders)): ?>
            <tr><td colspan="8">
              <div class="empty-state">
                <div class="empty-icon">📭</div>
                <p>No orders yet. Create your first sales order above.</p>
              </div>
            </td></tr>
            <?php else: foreach ($orders as $o):
              $is_large = $o['QuantityLiters'] >= 10000;
            ?>
            <tr data-fuel="<?= htmlspecialchars($o['FuelType']) ?>">
              <td class="td-id">#<?= $o['OrderID'] ?></td>
              <td style="font-size:.83rem;color:var(--text-muted)">
                <?= date('d M Y', strtotime($o['OrderDate'])) ?><br>
                <span style="font-size:.75rem"><?= date('H:i', strtotime($o['OrderDate'])) ?></span>
              </td>
              <td class="td-primary"><?= htmlspecialchars($o['CustomerName']) ?></td>
              <td><span class="badge badge-accent"><?= htmlspecialchars($o['FuelType']) ?></span></td>
              <td class="td-mono">
                <?= number_format($o['QuantityLiters'], 0) ?> L
                <?php if ($is_large): ?>
                <span class="badge badge-warning" style="margin-left:4px;font-size:.6rem">BULK</span>
                <?php endif; ?>
              </td>
              <td class="td-mono" style="font-size:.83rem"><?= number_format($o['UnitPrice'], 2) ?></td>
              <td class="td-mono" style="color:var(--accent);font-weight:600">
                <?= number_format($o['TotalAmount'], 0) ?>
              </td>
              <td>
                <div class="td-actions">
                  <button class="btn btn-edit btn-sm" onclick="openEdit(
                    <?= $o['OrderID'] ?>,
                    <?= $o['CustomerID'] ?>,
                    <?= $o['ProductID'] ?>,
                    '<?= $o['QuantityLiters'] ?>',
                    '<?= date('Y-m-d\TH:i', strtotime($o['OrderDate'])) ?>'
                  )">✎ Edit</button>
                  <button class="btn btn-delete btn-sm"
                    onclick="openDelete(<?= $o['OrderID'] ?>, '#<?= $o['OrderID'] ?> — <?= addslashes($o['CustomerName']) ?>')">
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

<!-- ───── ADD ORDER MODAL ───── -->
<div class="modal-overlay" id="addModal">
  <div class="modal modal-lg">
    <div class="modal-header">
      <span class="modal-title">➕ New Sales Order</span>
      <button class="modal-close" onclick="closeModal('addModal')">✕</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="add">
      <div class="modal-body">
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Customer *</label>
            <select class="form-control" name="CustomerID" required>
              <option value="">— Select customer —</option>
              <?php foreach ($customers as $c): ?>
              <option value="<?= $c['CustomerID'] ?>"><?= htmlspecialchars($c['CustomerName']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Product / Fuel *</label>
            <select class="form-control" name="ProductID" id="addProduct" onchange="updatePrice(this, 'addPriceHint')" required>
              <option value="">— Select product —</option>
              <?php foreach ($products as $p): ?>
              <option value="<?= $p['ProductID'] ?>" data-price="<?= $p['UnitPrice'] ?>">
                <?= htmlspecialchars($p['FuelType']) ?> — UGX <?= number_format($p['UnitPrice'], 2) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Quantity (Litres) *</label>
            <input class="form-control" type="number" name="QuantityLiters" id="addQty"
                   step="0.01" min="0.01" placeholder="e.g. 5000" oninput="calcTotal('addProduct','addQty','addTotal')" required>
            <p class="form-hint" id="addPriceHint">Select a product to see unit price</p>
          </div>
          <div class="form-group">
            <label class="form-label">Order Date &amp; Time</label>
            <input class="form-control" type="datetime-local" name="OrderDate"
                   value="<?= date('Y-m-d\TH:i') ?>">
          </div>
        </div>
        <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:1rem;margin-top:.5rem">
          <div style="font-size:.7rem;letter-spacing:.1em;text-transform:uppercase;color:var(--text-muted);margin-bottom:.25rem">Estimated Total</div>
          <div id="addTotal" style="font-family:'Source Sans Pro',sans-serif;font-size:1.5rem;font-weight:700;color:var(--accent)">UGX 0</div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="closeModal('addModal')">Cancel</button>
        <button type="submit" class="btn btn-primary">Place Order</button>
      </div>
    </form>
  </div>
</div>

<!-- ───── EDIT ORDER MODAL ───── -->
<div class="modal-overlay" id="editModal">
  <div class="modal modal-lg">
    <div class="modal-header">
      <span class="modal-title">✎ Edit Order</span>
      <button class="modal-close" onclick="closeModal('editModal')">✕</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="edit">
      <input type="hidden" name="OrderID" id="editOrderID">
      <div class="modal-body">
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Customer *</label>
            <select class="form-control" name="CustomerID" id="editCustomer" required>
              <option value="">— Select customer —</option>
              <?php foreach ($customers as $c): ?>
              <option value="<?= $c['CustomerID'] ?>"><?= htmlspecialchars($c['CustomerName']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Product / Fuel *</label>
            <select class="form-control" name="ProductID" id="editProduct" onchange="updatePrice(this,'editPriceHint')" required>
              <option value="">— Select product —</option>
              <?php foreach ($products as $p): ?>
              <option value="<?= $p['ProductID'] ?>" data-price="<?= $p['UnitPrice'] ?>">
                <?= htmlspecialchars($p['FuelType']) ?> — UGX <?= number_format($p['UnitPrice'], 2) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Quantity (Litres) *</label>
            <input class="form-control" type="number" name="QuantityLiters" id="editQty"
                   step="0.01" min="0.01" oninput="calcTotal('editProduct','editQty','editTotal')" required>
            <p class="form-hint" id="editPriceHint"></p>
          </div>
          <div class="form-group">
            <label class="form-label">Order Date &amp; Time</label>
            <input class="form-control" type="datetime-local" name="OrderDate" id="editDate">
          </div>
        </div>
        <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:1rem;margin-top:.5rem">
          <div style="font-size:.7rem;letter-spacing:.1em;text-transform:uppercase;color:var(--text-muted);margin-bottom:.25rem">Estimated Total</div>
          <div id="editTotal" style="font-family:'Source Sans Pro',sans-serif;font-size:1.5rem;font-weight:700;color:var(--accent)">UGX 0</div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="closeModal('editModal')">Cancel</button>
        <button type="submit" class="btn btn-primary">Update Order</button>
      </div>
    </form>
  </div>
</div>

<!-- ───── DELETE MODAL ───── -->
<div class="modal-overlay" id="deleteModal">
  <div class="modal" style="max-width:400px">
    <div class="modal-header" style="border-bottom-color:rgba(217,64,64,.3)">
      <span class="modal-title" style="color:var(--danger)">⚠ Delete Order</span>
      <button class="modal-close" onclick="closeModal('deleteModal')">✕</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="delete">
      <input type="hidden" name="OrderID" id="deleteID">
      <div class="modal-body">
        <p style="font-size:.9rem;line-height:1.65">
          Delete order <strong id="deleteName" style="color:var(--text)"></strong>?<br>
          <span style="color:var(--text-muted);font-size:.82rem">This action cannot be undone.</span>
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
// Products data for live price calculation
const productsData = <?= json_encode(array_column($products, null, 'ProductID')) ?>;

function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

document.querySelectorAll('.modal-overlay').forEach(el => {
  el.addEventListener('click', e => { if (e.target === el) el.classList.remove('open'); });
});

function updatePrice(selectEl, hintId) {
  const opt   = selectEl.selectedOptions[0];
  const price = opt ? opt.dataset.price : null;
  const hint  = document.getElementById(hintId);
  if (price && hint) hint.textContent = `Unit price: UGX ${parseFloat(price).toLocaleString()}`;
}

function calcTotal(productSelId, qtyId, totalId) {
  const sel   = document.getElementById(productSelId);
  const qty   = parseFloat(document.getElementById(qtyId).value) || 0;
  const opt   = sel ? sel.selectedOptions[0] : null;
  const price = opt ? parseFloat(opt.dataset.price) || 0 : 0;
  const total = qty * price;
  document.getElementById(totalId).textContent = 'UGX ' + total.toLocaleString('en-UG', {maximumFractionDigits: 0});
}

function openEdit(orderId, customerId, productId, qty, dateVal) {
  document.getElementById('editOrderID').value        = orderId;
  document.getElementById('editCustomer').value       = customerId;
  document.getElementById('editProduct').value        = productId;
  document.getElementById('editQty').value            = qty;
  document.getElementById('editDate').value           = dateVal;

  // Update price hint & total
  const sel = document.getElementById('editProduct');
  updatePrice(sel, 'editPriceHint');
  calcTotal('editProduct', 'editQty', 'editTotal');

  openModal('editModal');
}

function openDelete(id, label) {
  document.getElementById('deleteID').value         = id;
  document.getElementById('deleteName').textContent = label;
  openModal('deleteModal');
}

function filterTable() {
  const text = document.getElementById('searchInput').value.toLowerCase();
  const fuel = document.getElementById('filterFuel').value.toLowerCase();
  document.querySelectorAll('#salesTable tbody tr').forEach(row => {
    const matchText = row.textContent.toLowerCase().includes(text);
    const matchFuel = !fuel || (row.dataset.fuel || '').toLowerCase() === fuel;
    row.style.display = (matchText && matchFuel) ? '' : 'none';
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
