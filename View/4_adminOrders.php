<?php
if (!function_exists('e')) require_once __DIR__ . '/../config/helpers.php';
include __DIR__ . '/header.php';
$statuses = ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];
function task4_status_class(string $status): string
{
    return [
        'Pending' => 'b-yellow',
        'Processing' => 'b-blue',
        'Shipped' => 'b-orange',
        'Delivered' => 'b-green',
        'Cancelled' => 'b-red',
    ][$status] ?? 'b-gray';
}
?>
<!-- TASK 4 PART START: Admin order management -->
<div class="wrap page">
  <div class="ph">
    <h1>Order Management</h1>
  </div>

  <div class="card" style="margin-bottom:var(--s6)">
    <div class="cb">
      <form method="GET" action="<?= url('/admin/orders') ?>" style="display:flex;gap:var(--s4);flex-wrap:wrap;align-items:end">
        <div class="fg" style="margin-bottom:0">
          <label for="status">Status</label>
          <select id="status" name="status" class="fc">
            <option value="">All</option>
            <?php foreach ($statuses as $itemStatus): ?>
              <option value="<?= e($itemStatus) ?>" <?= $status === $itemStatus ? 'selected' : '' ?>>
                <?= e($itemStatus) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="fg" style="margin-bottom:0">
          <label for="from_date">From date</label>
          <input id="from_date" type="date" name="from_date" class="fc" value="<?= e($fromDate) ?>">
        </div>
        <div class="fg" style="margin-bottom:0">
          <label for="to_date">To date</label>
          <input id="to_date" type="date" name="to_date" class="fc" value="<?= e($toDate) ?>">
        </div>
        <button class="btn btn-primary" type="submit">Filter</button>
        <a href="<?= url('/admin/orders') ?>" class="btn btn-sec">Clear</a>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="cb" style="overflow-x:auto">
      <?php if (empty($orders)): ?>
        <div class="empty">
          <h3>No orders found</h3>
        </div>
      <?php else: ?>
        <table>
          <thead>
            <tr>
              <th>Order ID</th>
              <th>Customer</th>
              <th>Date</th>
              <th>Total</th>
              <th>Current Status</th>
              <th>Change Status</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($orders as $order): ?>
              <tr>
                <td>#<?= (int)$order['id'] ?></td>
                <td>
                  <div><?= e($order['customer_name']) ?></div>
                  <div style="font-size:var(--xs);color:var(--muted)"><?= e($order['customer_email']) ?></div>
                </td>
                <td><?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></td>
                <td>Tk <?= number_format((float)$order['total_amount'], 2) ?></td>
                <td>
                  <span id="task4-status-badge-<?= (int)$order['id'] ?>"
                        class="badge <?= task4_status_class($order['status']) ?>">
                    <?= e($order['status']) ?>
                  </span>
                </td>
                <td>
                  <select class="fc task4-status-select"
                          data-order-id="<?= (int)$order['id'] ?>"
                          data-current-status="<?= e($order['status']) ?>">
                    <?php foreach ($statuses as $itemStatus): ?>
                      <option value="<?= e($itemStatus) ?>" <?= $order['status'] === $itemStatus ? 'selected' : '' ?>>
                        <?= e($itemStatus) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
const task4AdminRouteBase = <?= json_encode(rtrim(url('/'), '/')) ?>;
const task4StatusClasses = {
  Pending: 'badge b-yellow',
  Processing: 'badge b-blue',
  Shipped: 'badge b-orange',
  Delivered: 'badge b-green',
  Cancelled: 'badge b-red'
};

document.querySelectorAll('.task4-status-select').forEach(function (select) {
  select.addEventListener('change', function () {
    const orderId = select.dataset.orderId;
    const oldStatus = select.dataset.currentStatus;
    const newStatus = select.value;
    const badge = document.getElementById('task4-status-badge-' + orderId);

    fetch(task4AdminRouteBase + '/api/orders/' + orderId, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify({status: newStatus})
    })
    .then(function (response) { return response.json(); })
    .then(function (data) {
      if (!data.ok) {
        alert(data.message || 'Could not update status.');
        select.value = oldStatus;
        return;
      }

      select.dataset.currentStatus = newStatus;
      badge.textContent = newStatus;
      badge.className = task4StatusClasses[newStatus] || 'badge b-gray';
    })
    .catch(function () {
      alert('AJAX request failed.');
      select.value = oldStatus;
    });
  });
});
</script>
<!-- TASK 4 PART END -->
<?php include __DIR__ . '/footer.php'; ?>

