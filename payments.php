<?php
require_once 'auth.php';
require_login();
$page = 'payments';
require_once 'database/Database.php';
require_once 'services/PaymentService.php';

$db = Database::getInstance();
$pdo = $db->getPdo();
$paymentService = new PaymentService($pdo);

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $paymentService->recordPayment([
            'CustomerID' => $_POST['CustomerID'] ?? 0,
            'Amount' => $_POST['Amount'] ?? 0,
            'PaymentDate' => $_POST['PaymentDate'] ?? date('Y-m-d'),
        ]);
        $message = 'Payment recorded successfully.';
        $messageType = 'success';
    } catch (InvalidArgumentException $e) {
        $message = $e->getMessage();
        $messageType = 'danger';
    } catch (PDOException $e) {
        $message = 'Unable to save payment: ' . $e->getMessage();
        $messageType = 'danger';
    }
}

$payments = $paymentService->getPayments();
$customers = $paymentService->getCustomers();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Payments — Oil &amp; Gas Sales DBMS</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main">
  <header class="topbar">
    <button class="mobile-menu-btn" id="mobileMenuBtn">☰</button>
    <div class="topbar-left">
      <h1>Payments</h1>
      <p class="page-sub">Track collected payments and customer settlements</p>
    </div>
  </header>
  <div class="content">
    <?php if ($message): ?>
    <div class="alert alert-<?= htmlspecialchars($messageType) ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="panel">
      <div class="panel-header">
        <span class="panel-title">💳 Record a Payment</span>
      </div>
      <div class="panel-body">
        <form method="POST" class="form-row">
          <div class="form-group">
            <label class="form-label">Customer</label>
            <select name="CustomerID" class="form-control" required>
              <option value="">Select customer</option>
              <?php foreach ($customers as $customer): ?>
              <option value="<?= (int) ($customer['CustomerID'] ?? 0) ?>"><?= htmlspecialchars((string) ($customer['CustomerName'] ?? '')) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Amount (UGX)</label>
            <input class="form-control" type="number" name="Amount" min="1" step="0.01" required>
          </div>
          <div class="form-group">
            <label class="form-label">Payment Date</label>
            <input class="form-control" type="date" name="PaymentDate" value="<?= date('Y-m-d') ?>">
          </div>
          <div class="form-group" style="display:flex;align-items:flex-end">
            <button class="btn btn-primary" type="submit">Save Payment</button>
          </div>
        </form>
      </div>
    </div>

    <div class="panel">
      <div class="panel-header">
        <span class="panel-title">📋 Payment History</span>
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr><th>ID</th><th>Customer</th><th>Amount</th><th>Date</th></tr>
          </thead>
          <tbody>
            <?php if (empty($payments)): ?>
            <tr><td colspan="4"><div class="empty-state"><p>No payments recorded yet.</p></div></td></tr>
            <?php else: foreach ($payments as $payment): ?>
            <tr>
              <td class="td-id">#<?= (int) ($payment['PaymentID'] ?? 0) ?></td>
              <td class="td-primary"><?= htmlspecialchars((string) ($payment['CustomerName'] ?? $payment['CustomerID'] ?? '')) ?></td>
              <td class="td-mono">UGX <?= number_format((float) ($payment['Amount'] ?? 0), 0) ?></td>
              <td class="td-mono"><?= htmlspecialchars((string) ($payment['PaymentDate'] ?? '')) ?></td>
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
