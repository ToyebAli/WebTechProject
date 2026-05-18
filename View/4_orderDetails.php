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
<!-- TASK 4 PART START: Customer order detail and review form -->
<div class="wrap page">
  <p style="font-size:var(--sm);margin-bottom:var(--s6)">
    <a href="<?= url('/orders') ?>">&larr; Back to My Orders</a>
  </p>

  <div class="card" style="margin-bottom:var(--s6)">
    <div class="cb">
      <div style="display:flex;justify-content:space-between;gap:var(--s4);flex-wrap:wrap">
        <div>
          <div style="font-size:var(--sm);color:var(--muted)">Order ID</div>
          <h1 style="font-family:var(--font-head);font-size:var(--xl)">#<?= (int)$order['id'] ?></h1>
        </div>
        <div>
          <div style="font-size:var(--sm);color:var(--muted)">Date</div>
          <div><?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></div>
        </div>
        <div>
          <div style="font-size:var(--sm);color:var(--muted)">Status</div>
          <span class="badge <?= $statusClasses[$order['status']] ?? 'b-gray' ?>">
            <?= e($order['status']) ?>
          </span>
        </div>
        <div>
          <div style="font-size:var(--sm);color:var(--muted)">Total</div>
          <div style="font-weight:700">Tk <?= number_format((float)$order['total_amount'], 2) ?></div>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="cb">
      <h2 class="ct">Items Ordered</h2>

      <?php foreach ($order['items'] as $item): ?>
        <div style="display:grid;grid-template-columns:70px 1fr auto;gap:var(--s4);align-items:start;padding:var(--s4) 0;border-bottom:1px solid var(--divider)">
          <?php if (!empty($item['primary_image_path'])): ?>
            <img src="<?= e(product_image_url($item['primary_image_path'])) ?>"
                 alt="<?= e($item['product_name']) ?>"
                 style="width:70px;height:70px;object-fit:cover;border:1px solid var(--border);border-radius:var(--rmd)">
          <?php else: ?>
            <div style="width:70px;height:70px;background:var(--divider);border-radius:var(--rmd);display:flex;align-items:center;justify-content:center;color:var(--muted)">
              No image
            </div>
          <?php endif; ?>

          <div>
            <div style="font-weight:700"><?= e($item['product_name']) ?></div>
            <div style="font-size:var(--sm);color:var(--muted)">
              Quantity: <?= (int)$item['quantity'] ?> |
              Unit price: Tk <?= number_format((float)$item['unit_price'], 2) ?>
            </div>

            <?php if ($order['status'] === 'Delivered'): ?>
              <?php if (!empty($item['review_id'])): ?>
                <p class="review-note" style="margin-top:var(--s3);font-size:var(--sm);color:var(--success)">
                  You already reviewed this product.
                </p>
              <?php else: ?>
                <form class="task4-review-form" data-product-id="<?= (int)$item['product_id'] ?>" style="margin-top:var(--s4)">
                  <label style="display:block;font-weight:600;font-size:var(--sm);margin-bottom:var(--s2)">Leave a Review</label>
                  <div style="display:flex;gap:var(--s3);align-items:center;flex-wrap:wrap;margin-bottom:var(--s3)">
                    <select name="rating" class="fc" style="max-width:140px" required>
                      <option value="">Rating</option>
                      <option value="5">5</option>
                      <option value="4">4</option>
                      <option value="3">3</option>
                      <option value="2">2</option>
                      <option value="1">1</option>
                    </select>
                    <button type="submit" class="btn btn-primary btn-sm">Submit Review</button>
                  </div>
                  <textarea name="review_text" class="fc" rows="3" placeholder="Write your review"></textarea>
                  <div class="task4-review-message" style="font-size:var(--sm);margin-top:var(--s2)"></div>
                </form>
              <?php endif; ?>
            <?php endif; ?>
          </div>

          <div style="font-weight:700;text-align:right">
            Tk <?= number_format((float)$item['unit_price'] * (int)$item['quantity'], 2) ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<script>
const task4ReviewForms = document.querySelectorAll('.task4-review-form');
const task4RouteBase = <?= json_encode(rtrim(url('/'), '/')) ?>;

task4ReviewForms.forEach(function (form) {
  form.addEventListener('submit', function (event) {
    event.preventDefault();
    const messageBox = form.querySelector('.task4-review-message');
    const button = form.querySelector('button[type="submit"]');
    const payload = {
      product_id: Number(form.dataset.productId),
      rating: Number(form.rating.value),
      review_text: form.review_text.value
    };

    button.disabled = true;
    messageBox.textContent = '';

    fetch(task4RouteBase + '/api/reviews', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify(payload)
    })
    .then(function (response) { return response.json(); })
    .then(function (data) {
      if (!data.ok) {
        messageBox.style.color = 'var(--error)';
        messageBox.textContent = data.message || 'Could not add review.';
        return;
      }

      form.innerHTML =
        '<p style="font-size:var(--sm);color:var(--success);margin-bottom:var(--s2)">' +
          data.message +
        '</p>' +
        '<div style="font-size:var(--sm);font-weight:600">Saved rating: ' +
          data.review.rating + ' / 5</div>' +
        (data.review.review_text
          ? '<p style="font-size:var(--sm);color:var(--muted);margin-top:var(--s2)"></p>'
          : '');

      if (data.review.review_text) {
        form.querySelector('p:last-child').textContent = data.review.review_text;
      }
    })
    .catch(function () {
      messageBox.style.color = 'var(--error)';
      messageBox.textContent = 'AJAX request failed.';
    })
    .finally(function () {
      if (document.body.contains(button)) {
        button.disabled = false;
      }
    });
  });
});
</script>
<!-- TASK 4 PART END -->
<?php include __DIR__ . '/footer.php'; ?>
