<?php
if (!function_exists('e')) require_once __DIR__ . '/../../config/helpers.php';
include __DIR__ . '/../layouts/header.php';
$inCart = $_SESSION['cart'][$product['id']] ?? 0;
$stock  = (int)$product['stock_qty'];
$avg    = (float)($product['avg_rating'] ?? 0);
?>
<div class="wrap page">
  <p style="font-size:var(--sm);color:var(--muted);margin-bottom:var(--s6)">
    <a href="/products">&larr; Back to Shop</a>
    <?php if ($product['category_name']): ?>
      &nbsp;›&nbsp; <?= e($product['category_name']) ?>
    <?php endif; ?>
  </p>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--s10);align-items:start">

    <!-- Image -->
    <div>
      <?php if ($product['primary_image_path']): ?>
        <img src="/public/uploads/products/<?= e($product['primary_image_path']) ?>"
             alt="<?= e($product['name']) ?>"
             style="width:100%;border-radius:var(--rxl);box-shadow:var(--sh-md);
                    border:1px solid var(--border)" loading="lazy" width="600" height="500">
      <?php else: ?>
        <div style="width:100%;aspect-ratio:4/3;background:var(--divider);
                    border-radius:var(--rxl);display:flex;align-items:center;
                    justify-content:center;color:var(--faint)">
          <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
            <rect x="3" y="3" width="18" height="18" rx="2"/>
            <circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>
          </svg>
        </div>
      <?php endif; ?>
    </div>

    <!-- Details -->
    <div style="display:flex;flex-direction:column;gap:var(--s5)">
      <?php if ($product['category_name']): ?>
        <div><span class="badge b-blue"><?= e($product['category_name']) ?></span></div>
      <?php endif; ?>

      <h1 style="font-family:var(--font-head);font-size:var(--2xl);font-weight:700;line-height:1.2">
        <?= e($product['name']) ?>
      </h1>

      <div style="display:flex;align-items:center;gap:var(--s2)">
        <span style="color:#d97706;font-size:var(--lg)">
          <?php for($i=1;$i<=5;$i++) echo $i<=round($avg)?'★':'☆'; ?>
        </span>
        <span style="color:var(--muted);font-size:var(--sm)">
          <?= $avg>0?number_format($avg,1).' out of 5':'No ratings yet' ?>
          (<?= (int)$product['review_count'] ?> review<?= $product['review_count']!=1?'s':'' ?>)
        </span>
      </div>

      <div style="font-size:var(--2xl);font-weight:800;color:var(--primary)">
        ৳<?= number_format((float)$product['price'],2) ?>
      </div>

      <?php if ($stock===0): ?>
        <span class="badge b-red" style="width:fit-content;padding:4px 14px">✕ Out of Stock</span>
      <?php elseif ($stock<=5): ?>
        <span class="badge b-orange" style="width:fit-content;padding:4px 14px">⚠ Only <?= $stock ?> left</span>
      <?php else: ?>
        <span class="badge b-green" style="width:fit-content;padding:4px 14px">✓ In Stock (<?= $stock ?>)</span>
      <?php endif; ?>

      <?php if ($product['description']): ?>
        <p style="color:var(--muted);line-height:1.7"><?= nl2br(e($product['description'])) ?></p>
      <?php endif; ?>

      <?php if ($stock > 0): ?>
        <div style="display:flex;align-items:center;gap:var(--s4);flex-wrap:wrap">
          <div class="qty-ctrl" id="qty-ctrl" style="<?= $inCart>0?'':'display:none' ?>">
            <button onclick="adjustCart(<?=(int)$product['id']?>,'dec')">−</button>
            <span id="qty-disp"><?= $inCart ?></span>
            <button onclick="adjustCart(<?=(int)$product['id']?>,'inc')">+</button>
          </div>
          <button id="add-btn" class="btn btn-primary btn-lg"
                  onclick="addToCartDetail(<?=(int)$product['id']?>,this)">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
              <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
            </svg>
            <?= $inCart>0?"Add More ({$inCart} in cart)":'Add to Cart' ?>
          </button>
          <a href="/cart" class="btn btn-sec btn-lg">View Cart</a>
        </div>
      <?php else: ?>
        <button class="btn btn-lg" disabled
                style="background:var(--divider);color:var(--muted);cursor:not-allowed">
          Out of Stock
        </button>
      <?php endif; ?>
    </div>
  </div>

  <!-- Reviews -->
  <div style="margin-top:var(--s12)">
    <h2 style="font-family:var(--font-head);font-size:var(--xl);font-weight:700;
               border-top:1px solid var(--divider);padding-top:var(--s8);
               margin-bottom:var(--s6)">Customer Reviews</h2>
    <div id="reviews-list">
      <div style="color:var(--muted);font-size:var(--sm);padding:var(--s8) 0">Loading reviews…</div>
    </div>
  </div>
</div>

<style>
.qty-ctrl{display:flex;align-items:center;border:1.5px solid var(--border);
  border-radius:var(--rmd);overflow:hidden}
.qty-ctrl button{width:36px;height:44px;font-size:var(--lg);font-weight:700;
  color:var(--text);background:var(--surface);border:none;cursor:pointer;
  transition:background var(--tr)}
.qty-ctrl button:hover{background:var(--surface2)}
.qty-ctrl span{min-width:40px;text-align:center;font-weight:700;font-size:var(--base)}
.review-card{padding:var(--s5);border:1px solid var(--border);border-radius:var(--rlg);
  background:var(--surface);margin-bottom:var(--s4)}
</style>

<script>
const badge = document.getElementById('cart-badge');

fetch('/api/products/<?= (int)$product['id'] ?>/reviews',
      {headers:{'X-Requested-With':'XMLHttpRequest'}})
  .then(r=>r.json())
  .then(d=>{
    const box = document.getElementById('reviews-list');
    if (!d.ok || !d.reviews.length) {
      box.innerHTML = '<p style="color:var(--muted);font-size:var(--sm)">No reviews yet. Be the first after receiving your order!</p>';
      return;
    }
    box.innerHTML = d.reviews.map(rv=>{
      const st = [1,2,3,4,5].map(i=>i<=rv.rating?'★':'☆').join('');
      const dt = new Date(rv.created_at).toLocaleDateString('en-GB',{day:'numeric',month:'short',year:'numeric'});
      return `<div class="review-card">
        <div style="display:flex;align-items:center;gap:var(--s3);margin-bottom:var(--s3)">
          <div style="width:34px;height:34px;border-radius:50%;background:var(--primary);
               display:flex;align-items:center;justify-content:center;color:#fff;
               font-weight:700;font-size:var(--sm);flex-shrink:0">
            ${(rv.reviewer_name||'?')[0].toUpperCase()}
          </div>
          <div>
            <div style="font-weight:600;font-size:var(--sm)">${rv.reviewer_name||'Customer'}</div>
            <div style="font-size:var(--xs);color:var(--muted)">${dt}</div>
          </div>
          <span style="margin-left:auto;color:#d97706">${st}</span>
        </div>
        ${rv.review_text?`<p style="font-size:var(--sm);color:var(--muted);margin:0">${rv.review_text}</p>`:''}
      </div>`;
    }).join('');
  }).catch(()=>{});

function addToCartDetail(pid, btn) {
  btn.disabled = true;
  const fd = new FormData(); fd.append('product_id', pid);
  fetch('/api/cart/add',{method:'POST',body:fd,headers:{'X-Requested-With':'XMLHttpRequest'}})
    .then(r=>r.json()).then(d=>{
      btn.disabled = false;
      if (d.ok) {
        if (badge) { badge.textContent=d.cart_count; badge.style.display='inline'; }
        document.getElementById('qty-ctrl').style.display = 'flex';
        document.getElementById('qty-disp').textContent   = d.qty;
        btn.innerHTML = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg> Add More (${d.qty} in cart)`;
        btn.style.background = 'var(--success)';
        setTimeout(()=>btn.style.background='', 1400);
      } else {
        btn.innerHTML = '⚠ '+(d.message||'Error');
        btn.style.background = 'var(--error)';
        setTimeout(()=>{ btn.style.background=''; btn.textContent='Add to Cart'; },2000);
      }
    }).catch(()=>{ btn.disabled=false; });
}

function adjustCart(pid, action) {
  const fd = new FormData(); fd.append('product_id',pid); fd.append('action',action);
  fetch('/api/cart/update',{method:'POST',body:fd,headers:{'X-Requested-With':'XMLHttpRequest'}})
    .then(r=>r.json()).then(d=>{
      if (!d.ok) return;
      document.getElementById('qty-disp').textContent = d.qty;
      if (badge) badge.textContent = d.cart_count;
      if (d.qty === 0) {
        document.getElementById('qty-ctrl').style.display = 'none';
        document.getElementById('add-btn').innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg> Add to Cart';
      }
    }).catch(()=>{});
}
</script>
<?php include __DIR__ . '/../layouts/footer.php'; ?>