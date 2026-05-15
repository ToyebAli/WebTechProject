<?php
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/User.php';

class CheckoutController {
    private Product $productModel;
    private Order   $orderModel;
    private User    $userModel;

    public function __construct() {
        $this->productModel = new Product();
        $this->orderModel   = new Order();
        $this->userModel    = new User();
    }

    public function form(): void {
        session_guard();
        $cart = $_SESSION['cart'] ?? [];
        if (empty($cart)) { redirect('/cart'); }
        $products  = $this->productModel->findByIds(array_keys($cart));
        $user      = $this->userModel->findById((int)$_SESSION['user_id']);
        $addresses = json_decode($user['shipping_addresses'] ?? '[]', true) ?? [];
        $errors    = $_SESSION['checkout_errors'] ?? [];
        unset($_SESSION['checkout_errors']);
        $pageTitle = 'Checkout';
        include __DIR__ . '/../views/checkout/form.php';
    }

    public function place(): void {
        session_guard();
        csrf_verify();
        $cart = $_SESSION['cart'] ?? [];
        if (empty($cart)) { redirect('/cart'); }

        $address = trim($_POST['shipping_address'] ?? '');
        if ($address === '' && !empty($_POST['saved_address']))
            $address = trim($_POST['saved_address']);

        $payment = $_POST['payment_method'] ?? '';
        $errors  = [];
        if ($address === '') $errors['address'] = 'Please enter or select a shipping address.';
        if (!in_array($payment, ['Cash','Card'])) $errors['payment'] = 'Please select a payment method.';
        if (!empty($errors)) { $_SESSION['checkout_errors'] = $errors; redirect('/checkout'); }

        $products = $this->productModel->findByIds(array_keys($cart));
        foreach ($cart as $pid => $qty) {
            if (!isset($products[$pid])) {
                $_SESSION['checkout_errors'] = ['stock' => 'A product in your cart no longer exists.'];
                redirect('/checkout');
            }
            if (!$products[$pid]['is_available'] || $products[$pid]['stock_qty'] < $qty) {
                $_SESSION['checkout_errors'] = ['stock' => "\"{$products[$pid]['name']}\" does not have enough stock."];
                redirect('/checkout');
            }
        }

        try {
            $orderId = $this->orderModel->place(
                (int)$_SESSION['user_id'], $address, $payment, $cart, $products
            );
        } catch (RuntimeException $e) {
            $_SESSION['checkout_errors'] = ['stock' => $e->getMessage()];
            redirect('/checkout');
        }

        unset($_SESSION['cart']);
        redirect('/checkout/confirm?order_id=' . $orderId);
    }

    public function confirm(): void {
        session_guard();
        $orderId = (int)($_GET['order_id'] ?? 0);
        $order   = $this->orderModel->findWithItems($orderId);
        if (!$order || (int)$order['user_id'] !== (int)$_SESSION['user_id']) {
            http_response_code(403); die('Access denied.');
        }
        $pageTitle = 'Order Confirmed';
        include __DIR__ . '/../views/checkout/confirm.php';
    }
}