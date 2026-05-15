<?php
if (!function_exists('e')) require_once __DIR__ . '/../../config/helpers.php';
include __DIR__ . '/../layouts/header.php';
$total = 0;
foreach ($cart as $pid => $qty)
    if (isset($products[$pid])) $total += $products[$pid]['price'] * $qty;
?>
<div class="wrap page">
  <div class="ph">
    <h1>Your Cart</h1>
    <span style="color:var(--muted);font-size:var(--sm)">
      <?= array_sum($cart) ?> item<?= array_sum($cart)!=1?'s':'' ?>
    </span>
  </div>

  <?php if (empty($cart)): ?>
    <div class="empty" style="min-height:50vh">
      <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor"
           stroke-width="1.2" style="color:var(--faint)">
        <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
      </svg>
      <h3>Your cart is empty</h3>
      <p>Browse the shop and add items to get started.</p>
      <a href="/products" class="btn btn-primary">Browse Products</a>
    </div>
  <?php else: ?>
    <div style="display:grid;grid-template-columns:1fr 340px;gap:var(--s8);align-items:start">

      <!-- Items -->
      <div id="cart-items">
        <?php foreach ($cart as $pid => $qty):
          if (!isset($products[$pid])) continue;
          $p    = $products[$pid];
          $line = (float)$p['price'] * $qty;
        ?>
        <div id="row-<?= (int)$pid ?>" class="c-row">
          <?php if ($p['primary_image_path']): ?>
            <img src="/public/uploads/products/<?= e($p['primary_image_path']) ?>"
                 alt="<?= e($p['name']) ?>"
                 style="width:88px;height:88px;object-fit:cover;border-radius:var(--rmd);
                        border:1px solid var(--border);flex-shrink:0"
                 loading="lazy" width="88" height="88">
          <?php else: ?>
            <div style="width:88px;height:88px;background:var(--divider);
                        border-radius:var(--rmd);flex-shrink:0;display:flex;
                        align-items:center;justify-content:center;color:var(--faint)">
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4">
                <rect x="3" y="3" width="18" height="18" rx="2"/>
                <circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>
              </svg>
            </div>
          <?php endif; ?>

          <div style="flex:1;min-width:0">
            <a href="/products/show?id=<?= (int)$pid ?>"
               style="font-weight:700;color:var(--text);text-decoration:none;
                      display:block;margin-bottom:var(--s1)"><?= e($p['name']) ?></a>
            <div style="font-size:var(--sm);color:var(--muted);margin-bottom:var(--s3)">
              ৳<?= number_format((float)$p['price'],2) ?> each
            </div>
            <div style="display:flex;align-items:center;gap:var(--s4)">
              <div class="qty-ctrl">
                <button onclick="updateCart(<?=(int)$pid?>,'dec')" aria-label="Decrease">−</button>
                <span id="qty-<?= (int)$pid ?>"><?= (int)$qty ?></span>
                <button onclick="updateCart(<?=(int)$pid?>,'inc')" aria-label="Increase">+</button>
              </div>
              <button onclick="removeItem(<?=(int)$pid?>)"
                      class="btn btn-sm" style="color:var(--error)">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                  <polyline points="3 6 5 6 21 6"/>
                  <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                  <path d="M10 11v6"/><path d="M14 11v6"/>
                </svg>
                Remove
              </button>
            </div>
          </div>

          <div id="line-<?= (int)$pid ?>"
               style="font-weight:700;font-size:var(--lg);text-align:right;
                      min-width:90px;white-space:nowrap">
            ৳<?= number_format($line,2) ?>
          </div>
        </div>
        <hr class="fdiv" style="margin:0">
        <?php endforeach; ?>
      </div>

      <!-- Summary -->
      <div class="card" style="position:sticky;top:80px">
        <div class="cb">
          <h2 style="font-size:var(--lg);font-weight:700;margin-bottom:var(--s5)">Summary</h2>
          <div style="display:flex;justify-content:space-between;font-size:var(--sm);
                      color:var(--muted);margin-bottom:var(--s3)">
            <span>Subtotal</span>
            <span id="subtotal">৳<?= number_format($total,2) ?></span>
          </div>
          <div style="display:flex;justify-content:space-between;font-size:var(--sm);
                      color:var(--muted);margin-bottom:var(--s4)">
            <span>Shipping</span><span>Free</span>
          </div>
          <hr class="fdiv">
          <div style="display:flex;justify-content:space-between;font-weight:800;
                      font-size:var(--xl);margin-top:var(--s4);margin-bottom:var(--s6)">
            <span>Total</span>
            <span id="grand-total" style="color:var(--primary)">৳<?= number_format($total,2) ?></span>
          </div>
          <a href="/checkout" class="btn btn-primary btn-full btn-lg">
            Proceed to Checkout
          </a>
          <a href="/products" class="btn btn-sec btn-full" style="margin-top:var(--s3)">
            Continue Shopping
          </a>
        </div>
      </div>
    </div>
  <?php endif; ?>
</div>

<style>
.c-row{display:flex;align-items:flex-start;gap:var(--s5);padding:var(--s6) 0;background:var(--surface)}
.qty-ctrl{display:flex;align-items:center;border:1.5px solid var(--border);border-radius:var(--rmd);overflow:hidden}
.qty-ctrl button{width:34px;height:38px;font-size:var(--lg);font-weight:700;
  background:var(--surface);border:none;cursor:pointer;transition:background var(--tr)}
.qty-ctrl button:hover{background:var(--surface2)}
.qty-ctrl span{min-width:36px;text-align:center;font-weight:700}
</style>

<script>
const badge = document.getElementById('cart-badge');

function updateCart(pid, action) {
  const fd = new FormData(); fd.append('product_id',pid); fd.append('action',action);
  fetch('/api/cart/update',{method:'POST',body:fd,headers:{'X-Requested-With':'XMLHttpRequest'}})
    .then(r=>r.json()).then(d=>{
      if (!d.ok) { alert(d.message||'Error'); return; }
      if (d.qty === 0) {
        const row = document.getElementById('row-'+pid);
        row?.nextElementSibling?.remove();
        row?.remove();
      } else {
        document.getElementById('qty-'+pid).textContent = d.qty;
        document.getElementById('line-'+pid).textContent = '৳'+d.line_total;
      }
      document.getElementById('grand-total').textContent = '৳'+d.grand_total;
      document.getElementById('subtotal').textContent    = '৳'+d.grand_total;
      if (badge) badge.textContent = d.cart_count;
      if (d.cart_count === 0) location.reload();
    }).catch(console.error);
}

function removeItem(pid) {
  const fd = new FormData(); fd.append('product_id',pid);
  fetch('/api/cart/remove',{method:'POST',body:fd,headers:{'X-Requested-With':'XMLHttpRequest'}})
    .then(r=>r.json()).then(d=>{
      if (!d.ok) { alert(d.message||'Error'); return; }
      const row = document.getElementById('row-'+pid);
      row?.nextElementSibling?.remove();
      row?.remove();
      document.getElementById('grand-total').textContent = '৳'+d.grand_total;
      document.getElementById('subtotal').textContent    = '৳'+d.grand_total;
      if (badge) badge.textContent = d.cart_count;
      if (d.cart_count === 0) location.reload();
    }).catch(console.error);
}
</script>
<?php include __DIR__ . '/../layouts/footer.php'; ?>