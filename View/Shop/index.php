<?php
if (!function_exists('e')) require_once __DIR__ . '/../../config/helpers.php';
include __DIR__ . '/../layouts/header.php';
?>
<div class="wrap page">
  <div style="display:flex;align-items:center;justify-content:space-between;
              flex-wrap:wrap;gap:var(--s4);margin-bottom:var(--s8)">
    <div class="ph" style="margin-bottom:0"><h1>Shop</h1></div>
    <a href="/cart" class="btn btn-sec">
      <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
      </svg>
      Cart
      <span id="cart-badge" class="nav-badge"
        style="<?= empty($_SESSION['cart'])?'display:none':'' ?>">
        <?= array_sum($_SESSION['cart'] ?? []) ?>
      </span>
    </a>
  </div>

  <!-- Filter bar -->
  <div style="display:flex;gap:var(--s4);flex-wrap:wrap;align-items:center;
              margin-bottom:var(--s6);padding:var(--s4);background:var(--surface);
              border:1px solid var(--border);border-radius:var(--rlg)">
    <div style="flex:1;min-width:220px;position:relative">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
           style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--muted)">
        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
      </svg>
      <input type="text" id="search-input" class="fc"
             placeholder="Search products…" style="padding-left:2.4rem"
             autocomplete="off" aria-label="Search products">
    </div>
    <div style="min-width:200px">
      <select id="category-filter" class="fc" aria-label="Filter by category">
        <option value="">All Categories</option>
        <?php foreach ($categories as $cat): ?>
          <option value="<?= (int)$cat['id'] ?>"><?= e($cat['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <button id="clear-btn" class="btn btn-sec btn-sm" type="button">Clear</button>
  </div>

  <!-- Skeleton (shown during AJAX) -->
  <div id="skeleton" style="display:none;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:var(--s6)">
    <?php for($i=0;$i<8;$i++): ?>
      <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--rxl);overflow:hidden">
        <div class="sk" style="height:200px;border-radius:0"></div>
        <div style="padding:var(--s5)">
          <div class="sk" style="height:14px;width:70%;margin-bottom:8px"></div>
          <div class="sk" style="height:14px;width:45%"></div>
        </div>
      </div>
    <?php endfor; ?>
  </div>

  <!-- Product grid -->
  <div id="product-grid" style="display:grid;
       grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:var(--s6)">
    <?php include __DIR__ . '/_product_cards.php'; ?>
  </div>

  <!-- Empty state -->
  <div id="empty-state" style="display:none">
    <div class="empty">
      <svg width="52" height="52" viewBox="0 0 24 24" fill="none" stroke="currentColor"
           stroke-width="1.4" style="color:var(--faint)">
        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
        <line x1="8" y1="11" x2="14" y2="11"/>
      </svg>
      <h3>No products found</h3>
      <p>Try a different keyword or category.</p>
      <button onclick="clearFilters()" class="btn btn-primary">Show All</button>
    </div>
  </div>
</div>

<style>
@keyframes shimmer{0%{background-position:-200% 0}100%{background-position:200% 0}}
.sk{background:linear-gradient(90deg,#f3f0ec 25%,#e6e4df 50%,#f3f0ec 75%);
  background-size:200% 100%;animation:shimmer 1.4s ease-in-out infinite;border-radius:4px}
.prod-card{background:var(--surface);border:1px solid var(--border);border-radius:var(--rxl);
  overflow:hidden;box-shadow:var(--sh-sm);display:flex;flex-direction:column;
  transition:box-shadow 180ms,transform 180ms}
.prod-card:hover{box-shadow:var(--sh-md);transform:translateY(-2px)}
.prod-img{width:100%;height:200px;object-fit:cover;background:var(--divider)}
.prod-img-ph{width:100%;height:200px;background:var(--divider);display:flex;
  align-items:center;justify-content:center;color:var(--faint)}
.prod-body{padding:var(--s5);flex:1;display:flex;flex-direction:column;gap:var(--s2)}
.prod-footer{padding:var(--s4) var(--s5);border-top:1px solid var(--divider)}
.stars{color:#d97706;font-size:var(--xs)}
</style>

<script>
let timer = null;
const grid   = document.getElementById('product-grid');
const empty  = document.getElementById('empty-state');
const skel   = document.getElementById('skeleton');
const badge  = document.getElementById('cart-badge');

function showSkel(on) {
  skel.style.display  = on ? 'grid' : 'none';
  grid.style.display  = on ? 'none' : 'grid';
  empty.style.display = 'none';
}

function stars(avg, cnt) {
  let s = '';
  for (let i=1;i<=5;i++) s += i<=Math.round(avg)?'★':'☆';
  return `<span class="stars">${s}</span>
          <span style="font-size:var(--xs);color:var(--muted)">${avg>0?parseFloat(avg).toFixed(1):'No'} (${cnt})</span>`;
}

function cardHtml(p) {
  const img = p.primary_image_path
    ? `<img src="/public/uploads/products/${p.primary_image_path}" alt="${p.name}" class="prod-img" loading="lazy" width="240" height="200">`
    : `<div class="prod-img-ph"><svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg></div>`;
  const stk = parseInt(p.stock_qty);
  const stockBadge = stk === 0 ? '<span class="badge b-red">Out of Stock</span>'
    : stk <= 5 ? `<span style="color:var(--warning);font-size:var(--xs);font-weight:600">⚠ Only ${stk} left</span>` : '';
  const btn = stk > 0
    ? `<button class="btn btn-primary btn-full" onclick="addToCart(${p.id},this)">
         <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
         Add to Cart
       </button>`
    : `<button class="btn btn-full" disabled style="background:var(--divider);color:var(--muted);cursor:not-allowed">Out of Stock</button>`;
  return `<div class="prod-card">
    <a href="/products/show?id=${p.id}" style="text-decoration:none">${img}</a>
    <div class="prod-body">
      <div style="font-size:var(--xs);color:var(--muted)">${p.category_name||'Uncategorised'}</div>
      <a href="/products/show?id=${p.id}" style="font-weight:700;color:var(--text);text-decoration:none">${p.name}</a>
      <div>${stars(p.avg_rating, p.review_count)}</div>
      <div style="font-size:var(--lg);font-weight:700;color:var(--primary);margin-top:auto">৳${parseFloat(p.price).toFixed(2)}</div>
      ${stockBadge}
    </div>
    <div class="prod-footer">${btn}</div>
  </div>`;
}

function render(products) {
  showSkel(false);
  if (!products.length) { grid.innerHTML=''; empty.style.display='block'; return; }
  empty.style.display = 'none';
  grid.innerHTML = products.map(cardHtml).join('');
}

function load(url) {
  showSkel(true);
  fetch(url, {headers:{'X-Requested-With':'XMLHttpRequest'}})
    .then(r=>r.json()).then(d=>render(d.ok?d.products:[])).catch(()=>showSkel(false));
}

document.getElementById('search-input').addEventListener('input', function() {
  clearTimeout(timer);
  timer = setTimeout(() => {
    document.getElementById('category-filter').value = '';
    load('/api/products/search?q=' + encodeURIComponent(this.value.trim()));
  }, 280);
});

document.getElementById('category-filter').addEventListener('change', function() {
  document.getElementById('search-input').value = '';
  load('/api/products' + (this.value ? '?category_id=' + this.value : ''));
});

function clearFilters() {
  document.getElementById('search-input').value = '';
  document.getElementById('category-filter').value = '';
  load('/api/products');
}
document.getElementById('clear-btn').addEventListener('click', clearFilters);

function addToCart(pid, btn) {
  btn.disabled = true;
  const fd = new FormData(); fd.append('product_id', pid);
  fetch('/api/cart/add', {method:'POST',body:fd,headers:{'X-Requested-With':'XMLHttpRequest'}})
    .then(r=>r.json()).then(d=>{
      if (d.ok) {
        btn.textContent = '✓ Added!';
        btn.style.background = 'var(--success)';
        if (badge) { badge.textContent = d.cart_count; badge.style.display = 'inline'; }
        setTimeout(()=>{ btn.disabled=false; btn.style.background=''; btn.innerHTML='<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg> Add to Cart'; }, 1400);
      } else {
        btn.textContent = '⚠ ' + (d.message||'Error');
        btn.style.background = 'var(--error)';
        setTimeout(()=>{ btn.disabled=false; btn.style.background=''; btn.textContent='Add to Cart'; }, 2000);
      }
    }).catch(()=>{ btn.disabled=false; });
}
</script>
<?php include __DIR__ . '/../layouts/footer.php'; ?>