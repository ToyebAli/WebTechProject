<?php
$cart = $_SESSION['cart'] ?? [];
foreach ($products as $p):
    $stock  = (int)$p['stock_qty'];
    $inCart = $cart[$p['id']] ?? 0;
    $avg    = (float)($p['avg_rating'] ?? 0);
?>
<div class="prod-card">
  <a href="/products/show?id=<?= (int)$p['id'] ?>" style="text-decoration:none">
    <?php if ($p['primary_image_path']): ?>
      <img src="/public/uploads/products/<?= e($p['primary_image_path']) ?>"
           alt="<?= e($p['name']) ?>" class="prod-img"
           loading="lazy" width="240" height="200">
    <?php else: ?>
      <div class="prod-img-ph">
        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4">
          <rect x="3" y="3" width="18" height="18" rx="2"/>
          <circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>
        </svg>
      </div>
    <?php endif; ?>
  </a>
  <div class="prod-body">
    <div style="font-size:var(--xs);color:var(--muted)"><?= e($p['category_name'] ?? 'Uncategorised') ?></div>
    <a href="/products/show?id=<?= (int)$p['id'] ?>"
       style="font-weight:700;color:var(--text);text-decoration:none"><?= e($p['name']) ?></a>
    <div>
      <span class="stars">
        <?php for($i=1;$i<=5;$i++) echo $i<=round($avg)?'★':'☆'; ?>
      </span>
      <span style="font-size:var(--xs);color:var(--muted)">
        <?= $avg>0?number_format($avg,1):'No' ?> (<?= (int)($p['review_count']??0) ?>)
      </span>
    </div>
    <div style="font-size:var(--lg);font-weight:700;color:var(--primary);margin-top:auto">
      ৳<?= number_format((float)$p['price'],2) ?>
    </div>
    <?php if ($stock===0): ?>
      <span class="badge b-red">Out of Stock</span>
    <?php elseif ($stock<=5): ?>
      <span style="color:var(--warning);font-size:var(--xs);font-weight:600">⚠ Only <?= $stock ?> left</span>
    <?php endif; ?>
  </div>
  <div class="prod-footer">
    <?php if ($stock > 0): ?>
      <button class="btn btn-primary btn-full" onclick="addToCart(<?= (int)$p['id'] ?>, this)">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
          <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
          <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
        </svg>
        <?= $inCart > 0 ? "In Cart ({$inCart})" : 'Add to Cart' ?>
      </button>
    <?php else: ?>
      <button class="btn btn-full" disabled
              style="background:var(--divider);color:var(--muted);cursor:not-allowed">
        Out of Stock
      </button>
    <?php endif; ?>
  </div>
</div>
<?php endforeach; ?>