<?php
if (!function_exists('e')) require_once __DIR__ . '/../../config/helpers.php';
include __DIR__ . '/../layouts/header.php';
$sc=['Pending'=>'b-yellow','Processing'=>'b-blue','Shipped'=>'b-orange',
     'Delivered'=>'b-green','Cancelled'=>'b-red'];
?>
<div class="wrap page">
  <div style="max-width:640px;margin:0 auto;text-align:center;margin-bottom:var(--s10)">
    <div style="width:80px;height:80px;background:#eef5e9;border-radius:50%;
                display:flex;align-items:center;justify-content:center;
                font-size:2.5rem;margin:0 auto var(--s5)">✅</div>
    <h1 style="font-family:var(--font-head);font-size:var(--2xl);font-weight:800;margin-bottom:var(--s3)">
      Order Placed!
    </h1>
    <p style="color:var(--muted)">
      Thank you! Order <strong>#<?= (int)$order['id'] ?></strong> is now being processed.
    </p>
  </div>

  <div style="max-width:640px;margin:0 auto;display:flex;flex-direction:column;gap:var(--s6)">
    <div class="card"><div class="cb">
      <h2 style="font-size:var(--lg);font-weight:700;margin-bottom:var(--s5)">Order Details</h2>
      <dl style="display:grid;grid-template-columns:auto 1fr;gap:var(--s3) var(--s6);font-size:var(--sm)">
        <dt style="color:var(--muted)">Order ID</dt>
        <dd style="font-weight:700;font-family:monospace">#<?= (int)$order['id'] ?></dd>
        <dt style="color:var(--muted)">Date</dt>
        <dd><?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></dd>
        <dt style="color:var(--muted)">Status</dt>
        <dd><span class="badge <?= $sc[$order['status']]??'b-gray' ?>"><?= e($order['status']) ?></span></dd>
        <dt style="color:var(--muted)">Payment</dt>
        <dd><?= e($order['payment_method']) ?></dd>
        <dt style="color:var(--muted)">Ship to</dt>
        <dd><?= e($order['shipping_address']) ?></dd>
      </dl>
    </div></div>

    <div class="card"><div class="cb">
      <h2 style="font-size:var(--lg);font-weight:700;margin-bottom:var(--s5)">Items Ordered</h2>
      <?php foreach ($order['items'] as $item): ?>
        <div style="display:flex;align-items:center;gap:var(--s4);
                    padding:var(--s4) 0;border-bottom:1px solid var(--divider)">
          <?php if ($item['primary_image_path']): ?>
            <img src="/public/uploads/products/<?= e($item['primary_image_path']) ?>"
                 alt="<?= e($item['product_name']) ?>"
                 style="width:54px;height:54px;object-fit:cover;border-radius:var(--rmd);
                        border:1px solid var(--border);flex-shrink:0"
                 loading="lazy" width="54" height="54">
          <?php else: ?>
            <div style="width:54px;height:54px;background:var(--divider);border-radius:var(--rmd);
                        display:flex;align-items:center;justify-content:center;
                        color:var(--faint);flex-shrink:0">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4">
                <rect x="3" y="3" width="18" height="18" rx="2"/>
                <circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>
              </svg>
            </div>
          <?php endif; ?>
          <div style="flex:1">
            <div style="font-weight:600;font-size:var(--sm)"><?= e($item['product_name']) ?></div>
            <div style="font-size:var(--xs);color:var(--muted)">
              Qty: <?= (int)$item['quantity'] ?> × ৳<?= number_format((float)$item['unit_price'],2) ?>
            </div>
          </div>
          <div style="font-weight:700">
            ৳<?= number_format((float)$item['unit_price']*$item['quantity'],2) ?>
          </div>
        </div>
      <?php endforeach; ?>
      <div style="display:flex;justify-content:space-between;font-weight:800;
                  font-size:var(--xl);padding-top:var(--s5)">
        <span>Total</span>
        <span style="color:var(--primary)">৳<?= number_format((float)$order['total_amount'],2) ?></span>
      </div>
    </div></div>

    <div style="display:flex;gap:var(--s4)">
      <a href="/orders" class="btn btn-primary">View My Orders</a>
      <a href="/products" class="btn btn-sec">Continue Shopping</a>
    </div>
  </div>
</div>
<script>
(function(){
  const c=['#01696f','#437a22','#d97706','#1d4ed8'];
  for(let i=0;i<60;i++){
    const el=document.createElement('div');
    el.style.cssText='position:fixed;top:-10px;left:'+Math.random()*100+'vw;width:'+(6+Math.random()*6)+'px;height:'+(6+Math.random()*6)+'px;background:'+c[i%c.length]+';border-radius:'+(Math.random()>.5?'50%':'3px')+';pointer-events:none;z-index:9999;animation:fall '+(1.5+Math.random()*2)+'s linear '+(Math.random()*1.2)+'s forwards';
    document.body.appendChild(el); setTimeout(()=>el.remove(),4200);
  }
  const s=document.createElement('style');
  s.textContent='@keyframes fall{to{transform:translateY(105vh) rotate(720deg);opacity:0}}';
  document.head.appendChild(s);
})();
</script>
<?php include __DIR__ . '/../layouts/footer.php'; ?>