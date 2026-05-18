<?php
if (!function_exists('e')) require_once __DIR__ . '/../config/helpers.php';
include __DIR__ . '/header.php';
$statusClasses = [
    'Pending' => 'b-yellow',
    'Processing' => 'b-blue',
    'Shipped' => 'b-orange',
    'Delivered' => 'b-green',
    'Cancelled' => 'b-red',
];
?>
<!-- TASK 4 PART START: Customer order history -->
<div class="wrap page">
  <div class="ph">
    <h1>My Orders</h1>
  </div>

  <?php if (empty($orders)): ?>
    <div class="card">
      <div class="empty">
        <h3>No orders yet</h3>
        <p>Your placed orders will appear here after checkout.</p>
        <a href="<?= url('/products') ?>" class="btn btn-primary">Browse Products</a>
      </div>
    </div>
  <?php else: ?>
    <div style="display:flex;flex-direction:column;gap:var(--s4)">
      <?php foreach ($orders as $order): ?>
        <div class="card">
          <div class="cb" style="display:flex;align-items:center;justify-content:space-between;gap:var(--s4);flex-wrap:wrap">
            <div>
              <div style="font-size:var(--sm);color:var(--muted)">Order ID</div>
              <div style="font-size:var(--lg);font-weight:700">#<?= (int)$order['id'] ?></div>
            </div>
            <div>
              <div style="font-size:var(--sm);color:var(--muted)">Date</div>
              <div><?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></div>
            </div>
            <div>
              <div style="font-size:var(--sm);color:var(--muted)">Total</div>
              <div style="font-weight:700">Tk <?= number_format((float)$order['total_amount'], 2) ?></div>
            </div>
            <div>
              <span class="badge <?= $statusClasses[$order['status']] ?? 'b-gray' ?>">
                <?= e($order['status']) ?>
              </span>
            </div>
            <a href="<?= url('/orders/show?id=' . (int)$order['id']) ?>" class="btn btn-sec">Open Details</a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
<!-- TASK 4 PART END -->
<?php include __DIR__ . '/footer.php'; ?>

