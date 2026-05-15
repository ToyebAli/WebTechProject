<?php
if (!function_exists('e')) require_once __DIR__ . '/../../config/helpers.php';
include __DIR__ . '/../layouts/header.php';
$total = 0;
$cart  = $_SESSION['cart'] ?? [];
foreach ($cart as $pid => $qty)
    if (isset($products[$pid])) $total += $products[$pid]['price'] * $qty;
?>
<div class="wrap page">
  <div class="ph"><h1>Checkout</h1></div>

  <?php if (!empty($errors)): ?>
    <div class="alert alert-err" style="max-width:860px;margin-bottom:var(--s6)" role="alert">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/>
        <line x1="12" y1="16" x2="12.01" y2="16"/>
      </svg>
      <?= e(array_values($errors)[0]) ?>
    </div>
  <?php endif; ?>

  <form method="POST" action="/checkout" novalidate>
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
    <div style="display:grid;grid-template-columns:1fr 360px;gap:var(--s8);align-items:start">

      <div style="display:flex;flex-direction:column;gap:var(--s6)">

        <!-- Shipping -->
        <div class="card"><div class="cb">
          <h2 style="font-size:var(--lg);font-weight:700;margin-bottom:var(--s5)">Shipping Address</h2>

          <?php if (!empty($addresses)): ?>
            <?php foreach ($addresses as $i => $addr): if (!$addr) continue; ?>
              <label class="addr-opt">
                <input type="radio" name="saved_address" value="<?= e($addr) ?>"
                       onchange="pickAddr(false)" <?= $i===0?'checked':'' ?>>
                <div style="flex:1">
                  <div style="font-weight:600;font-size:var(--sm)"><?= e($addr) ?></div>
                  <div style="font-size:var(--xs);color:var(--muted)">Saved address <?= $i+1 ?></div>
                </div>
              </label>
            <?php endforeach; ?>
            <label class="addr-opt">
              <input type="radio" name="saved_address" value="" onchange="pickAddr(true)">
              <div style="font-weight:600;font-size:var(--sm)">Use a new address</div>
            </label>
            <div id="new-addr" style="display:none;margin-top:var(--s3)">
              <textarea name="shipping_address" id="new-ta" class="fc" rows="3"
                placeholder="House, Road, Area, City…" disabled></textarea>
            </div>
          <?php else: ?>
            <div class="fg">
              <label for="sa">Delivery Address <span style="color:var(--error)">*</span></label>
              <textarea id="sa" name="shipping_address"
                class="fc <?= !empty($errors['address'])?'err':'' ?>"
                rows="3" placeholder="House, Road, Area, City…"><?= e($_POST['shipping_address']??'') ?></textarea>
              <?php if (!empty($errors['address'])): ?>
                <div class="fe">&#9888; <?= e($errors['address']) ?></div>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </div></div>

        <!-- Payment -->
        <div class="card"><div class="cb">
          <h2 style="font-size:var(--lg);font-weight:700;margin-bottom:var(--s5)">Payment Method</h2>
          <?php if (!empty($errors['payment'])): ?>
            <div class="fe" style="margin-bottom:var(--s3)">&#9888; <?= e($errors['payment']) ?></div>
          <?php endif; ?>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--s4)">
            <label class="pay-opt" id="pay-cash">
              <input type="radio" name="payment_method" value="Cash" checked
                     onchange="hlPay('cash','card')">
              <span style="font-size:1.5rem">💵</span>
              <div>
                <div style="font-weight:700;font-size:var(--sm)">Cash on Delivery</div>
                <div style="font-size:var(--xs);color:var(--muted)">Pay when delivered</div>
              </div>
            </label>
            <label class="pay-opt" id="pay-card">
              <input type="radio" name="payment_method" value="Card"
                     onchange="hlPay('card','cash')">
              <span style="font-size:1.5rem">💳</span>
              <div>
                <div style="font-weight:700;font-size:var(--sm)">Pay by Card</div>
                <div style="font-size:var(--xs);color:var(--muted)">Secure payment</div>
              </div>
            </label>
          </div>
        </div></div>
      </div>

      <!-- Sidebar -->
      <div class="card" style="position:sticky;top:80px"><div class="cb">
        <h2 style="font-size:var(--lg);font-weight:700;margin-bottom:var(--s5)">Summary</h2>
        <?php foreach ($cart as $pid => $qty):
          if (!isset($products[$pid])) continue;
          $p = $products[$pid]; ?>
          <div style="display:flex;justify-content:space-between;font-size:var(--sm);
                      margin-bottom:var(--s3)">
            <span><?= e($p['name']) ?> <span style="color:var(--muted)">×<?= (int)$qty ?></span></span>
            <span style="font-weight:600">৳<?= number_format((float)$p['price']*$qty,2) ?></span>
          </div>
        <?php endforeach; ?>
        <hr class="fdiv">
        <div style="display:flex;justify-content:space-between;font-weight:800;
                    font-size:var(--xl);margin-top:var(--s4);margin-bottom:var(--s6)">
          <span>Total</span>
          <span style="color:var(--primary)">৳<?= number_format($total,2) ?></span>
        </div>
        <button type="submit" class="btn btn-primary btn-full btn-lg">
          Place Order
        </button>
        <a href="/cart" class="btn btn-sec btn-full" style="margin-top:var(--s3)">
          &larr; Back to Cart
        </a>
      </div></div>
    </div>
  </form>
</div>

<style>
.addr-opt{display:flex;align-items:center;gap:var(--s4);padding:var(--s4);
  border:1.5px solid var(--border);border-radius:var(--rmd);cursor:pointer;
  margin-bottom:var(--s3);transition:border-color var(--tr)}
.addr-opt:has(input:checked){border-color:var(--primary);background:#e8f4f4}
.addr-opt input{accent-color:var(--primary)}
.pay-opt{display:flex;align-items:center;gap:var(--s4);padding:var(--s5);
  border:1.5px solid var(--border);border-radius:var(--rlg);cursor:pointer;
  transition:border-color var(--tr)}
.pay-opt:has(input:checked){border-color:var(--primary);background:#e8f4f4}
.pay-opt input{accent-color:var(--primary)}
</style>
<script>
document.addEventListener('DOMContentLoaded',()=>hlPay('cash','card'));
function hlPay(a,b){
  document.getElementById('pay-'+a).style.borderColor='var(--primary)';
  document.getElementById('pay-'+b).style.borderColor='';
}
function pickAddr(isNew){
  const box=document.getElementById('new-addr');
  const ta=document.getElementById('new-ta');
  if(isNew){box.style.display='block';ta.disabled=false;ta.focus();}
  else{box.style.display='none';ta.disabled=true;}
}
</script>
<?php include __DIR__ . '/../layouts/footer.php'; ?>