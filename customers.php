<?php
require_once 'auth.php';
require_login();
$page = 'customers';
require_once 'database/Database.php';
require_once 'services/CustomerService.php';

$db = Database::getInstance();
$pdo = $db->getPdo();
$customerService = new CustomerService($pdo);

$msg = '';
$msg_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'add') {
            $customerService->createCustomer([
                'CustomerName' => $_POST['CustomerName'] ?? '',
                'Address' => $_POST['Address'] ?? '',
                'CreditLimit' => $_POST['CreditLimit'] ?? 0,
            ]);
            header('Location: customers.php?success=added');
            exit;
        }

        if ($action === 'edit') {
            $id = (int) ($_POST['CustomerID'] ?? 0);
            $customerService->updateCustomer($id, [
                'CustomerName' => $_POST['CustomerName'] ?? '',
                'Address' => $_POST['Address'] ?? '',
                'CreditLimit' => $_POST['CreditLimit'] ?? 0,
            ]);
            header('Location: customers.php?success=updated');
            exit;
        }

        if ($action === 'delete') {
            $id = (int) ($_POST['CustomerID'] ?? 0);
            $customerService->deleteCustomer($id);
            header('Location: customers.php?success=deleted');
            exit;
        }
    } catch (InvalidArgumentException $e) {
        $msg = $e->getMessage();
        $msg_type = 'danger';
    } catch (RuntimeException $e) {
        $msg = $e->getMessage();
        $msg_type = 'danger';
    }
}

if (isset($_GET['success'])) {
    $map = ['added' => 'Customer added successfully.', 'updated' => 'Customer updated.', 'deleted' => 'Customer deleted.'];
    $msg = $map[$_GET['success']] ?? '';
    $msg_type = 'success';
}

$customers = $customerService->getCustomers();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Customers — Oil &amp; Gas Sales DBMS</title>
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
      <h1>Customers</h1>
      <p class="page-sub"><?= count($customers) ?> registered customer<?= count($customers) != 1 ? 's' : '' ?></p>
    </div>
    <div class="topbar-right">
      <button class="btn btn-primary" onclick="openModal('addModal')">+ Add Customer</button>
    </div>
  </header>

  <div class="content">

    <?php if ($msg): ?>
    <div class="alert alert-<?= $msg_type ?>">
      <?= $msg_type === 'success' ? '✓' : '✕' ?> <?= htmlspecialchars($msg) ?>
    </div>
    <?php endif; ?>

    <!-- SEARCH TOOLBAR -->
    <div class="toolbar">
      <div class="search-wrap">
        <span class="search-icon">🔍</span>
        <input type="text" id="searchInput" placeholder="Search customers…" oninput="filterTable()">
      </div>
      <span style="font-size:.8rem;color:var(--text-muted)"><?= count($customers) ?> records</span>
    </div>

    <!-- TABLE -->
    <div class="panel">
      <div class="table-wrap">
        <table id="custTable">
          <thead>
            <tr>
              <th>ID</th>
              <th>Customer Name</th>
              <th>Address</th>
              <th>Credit Limit (UGX)</th>
              <th>Total Spent (UGX)</th>
              <th>Orders</th>
              <th>Credit Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($customers)): ?>
            <tr><td colspan="8">
              <div class="empty-state">
                <div class="empty-icon">👥</div>
                <p>No customers yet. Add your first customer above.</p>
              </div>
            </td></tr>
            <?php else: foreach ($customers as $c):
              $ratio = $c['CreditLimit'] > 0 ? ($c['Total_Spent'] / $c['CreditLimit']) : 0;
              if ($ratio > 0.8) { $badge = 'badge-danger'; $label = 'Over Limit'; }
              elseif ($ratio > 0.5) { $badge = 'badge-warning'; $label = 'Moderate'; }
              else { $badge = 'badge-success'; $label = 'Good'; }
            ?>
            <tr>
              <td class="td-id"><?= $c['CustomerID'] ?></td>
              <td class="td-primary"><?= htmlspecialchars($c['CustomerName']) ?></td>
              <td style="color:var(--text-muted);font-size:.84rem"><?= htmlspecialchars($c['Address'] ?? '—') ?></td>
              <td class="td-mono"><?= number_format($c['CreditLimit'], 2) ?></td>
              <td class="td-mono" style="color:var(--accent)"><?= number_format($c['Total_Spent'], 2) ?></td>
              <td><span class="badge badge-muted"><?= $c['OrderCount'] ?></span></td>
              <td><span class="badge <?= $badge ?>"><?= $label ?></span></td>
              <td>
                <div class="td-actions">
                  <button class="btn btn-edit btn-sm"
                    onclick="openEdit(<?= $c['CustomerID'] ?>, <?= htmlspecialchars(json_encode($c['CustomerName'])) ?>, <?= htmlspecialchars(json_encode($c['Address'] ?? '')) ?>, '<?= $c['CreditLimit'] ?>')">
                    ✎ Edit
                  </button>
                  <button class="btn btn-delete btn-sm"
                    onclick="openDelete(<?= $c['CustomerID'] ?>, <?= htmlspecialchars(json_encode($c['CustomerName'])) ?>)">
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
      <span class="modal-title">➕ Add Customer</span>
      <button class="modal-close" onclick="closeModal('addModal')">✕</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="add">
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Customer Name *</label>
          <input class="form-control" type="text" name="CustomerName" placeholder="e.g. Stabex Mbarara" required>
        </div>
        <div class="form-group">
          <label class="form-label">Address</label>
          <input class="form-control" type="text" name="Address" placeholder="e.g. Mbarara-Masaka Road">
        </div>
        <div class="form-group">
          <label class="form-label">Credit Limit (UGX)</label>
          <input class="form-control" type="number" name="CreditLimit" step="0.01" min="0" placeholder="e.g. 60000000" value="0">
          <p class="form-hint">Maximum credit this customer can use</p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="closeModal('addModal')">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Customer</button>
      </div>
    </form>
  </div>
</div>

<!-- ───── EDIT MODAL ───── -->
<div class="modal-overlay" id="editModal">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">✎ Edit Customer</span>
      <button class="modal-close" onclick="closeModal('editModal')">✕</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="edit">
      <input type="hidden" name="CustomerID" id="editID">
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Customer Name *</label>
          <input class="form-control" type="text" name="CustomerName" id="editName" required>
        </div>
        <div class="form-group">
          <label class="form-label">Address</label>
          <input class="form-control" type="text" name="Address" id="editAddress">
        </div>
        <div class="form-group">
          <label class="form-label">Credit Limit (UGX)</label>
          <input class="form-control" type="number" name="CreditLimit" id="editLimit" step="0.01" min="0">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="closeModal('editModal')">Cancel</button>
        <button type="submit" class="btn btn-primary">Update Customer</button>
      </div>
    </form>
  </div>
</div>

<!-- ───── DELETE MODAL ───── -->
<div class="modal-overlay" id="deleteModal">
  <div class="modal" style="max-width:400px">
    <div class="modal-header" style="border-bottom-color:rgba(217,64,64,.3)">
      <span class="modal-title" style="color:var(--danger)">⚠ Delete Customer</span>
      <button class="modal-close" onclick="closeModal('deleteModal')">✕</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="delete">
      <input type="hidden" name="CustomerID" id="deleteID">
      <div class="modal-body">
        <p style="font-size:.9rem;line-height:1.65">
          Are you sure you want to delete <strong id="deleteName" style="color:var(--text)"></strong>?<br>
          <span style="color:var(--text-muted);font-size:.82rem">This cannot be undone. Customers with orders cannot be deleted.</span>
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

// Close on backdrop click
document.querySelectorAll('.modal-overlay').forEach(el => {
  el.addEventListener('click', e => { if (e.target === el) el.classList.remove('open'); });
});

function openEdit(id, name, address, limit) {
  document.getElementById('editID').value      = id;
  document.getElementById('editName').value    = name;
  document.getElementById('editAddress').value = address;
  document.getElementById('editLimit').value   = limit;
  openModal('editModal');
}

function openDelete(id, name) {
  document.getElementById('deleteID').value  = id;
  document.getElementById('deleteName').textContent = name;
  openModal('deleteModal');
}

function filterTable() {
  const val = document.getElementById('searchInput').value.toLowerCase();
  document.querySelectorAll('#custTable tbody tr').forEach(row => {
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
