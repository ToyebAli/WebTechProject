<?php
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../models/Product.php';

class CartController {
    private Product $model;
    public function __construct() { $this->model = new Product(); }

    public function index(): void {
        session_guard();
        $cart      = $_SESSION['cart'] ?? [];
        $products  = $this->model->findByIds(array_keys($cart));
        $pageTitle = 'Your Cart';
        include __DIR__ . '/../views/cart/index.php';
    }

    public function apiAdd(): void {
        session_guard();
        $pid     = (int)($_POST['product_id'] ?? 0);
        if (!$pid) json_response(['ok'=>false,'message'=>'Invalid product.'], 422);
        $product = $this->model->findById($pid);
        if (!$product || !$product['is_available'])
            json_response(['ok'=>false,'message'=>'Product not available.'], 422);
        $_SESSION['cart'] = $_SESSION['cart'] ?? [];
        $current  = $_SESSION['cart'][$pid] ?? 0;
        $newQty   = min($current + 1, (int)$product['stock_qty']);
        if ($newQty < 1) json_response(['ok'=>false,'message'=>'Out of stock.'], 422);
        $_SESSION['cart'][$pid] = $newQty;
        json_response(['ok'=>true,'cart_count'=>array_sum($_SESSION['cart']),'qty'=>$newQty]);
    }

    public function apiUpdate(): void {
        session_guard();
        $pid    = (int)($_POST['product_id'] ?? 0);
        $action = $_POST['action'] ?? '';
        if (!$pid || !in_array($action, ['inc','dec']))
            json_response(['ok'=>false,'message'=>'Bad request.'], 422);
        $product = $this->model->findById($pid);
        if (!$product) json_response(['ok'=>false,'message'=>'Not found.'], 404);
        $current = $_SESSION['cart'][$pid] ?? 0;
        $current = $action === 'inc'
            ? min($current + 1, (int)$product['stock_qty'])
            : max($current - 1, 0);
        if ($current === 0) unset($_SESSION['cart'][$pid]);
        else $_SESSION['cart'][$pid] = $current;
        $grandTotal = 0;
        $cart = $_SESSION['cart'] ?? [];
        if (!empty($cart)) {
            $allP = $this->model->findByIds(array_keys($cart));
            foreach ($cart as $id => $qty) $grandTotal += ($allP[$id]['price'] ?? 0) * $qty;
        }
        json_response([
            'ok'          => true,
            'qty'         => $current,
            'line_total'  => number_format($current * (float)$product['price'], 2),
            'grand_total' => number_format($grandTotal, 2),
            'cart_count'  => array_sum($cart),
        ]);
    }
    
    public function apiRemove(): void {
        session_guard();
        $pid = (int)($_POST['product_id'] ?? 0);
        unset($_SESSION['cart'][$pid]);
        $grandTotal = 0;
        $cart = $_SESSION['cart'] ?? [];
        if (!empty($cart)) {
            $allP = $this->model->findByIds(array_keys($cart));
            foreach ($cart as $id => $qty) $grandTotal += ($allP[$id]['price'] ?? 0) * $qty;
        }
        json_response(['ok'=>true,'grand_total'=>number_format($grandTotal,2),'cart_count'=>array_sum($cart)]);
    }
}