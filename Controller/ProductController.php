<?php
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../models/Product.php';

class ProductController {
    private Product $model;
    public function __construct() { $this->model = new Product(); }

    public function index(): void {
        session_guard();
        $categoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : null;
        $products   = $this->model->getAllAvailable($categoryId);
        $categories = $this->model->getCategories();
        $pageTitle  = 'Shop';
        include __DIR__ . '/../views/shop/index.php';
    }

    public function show(): void {
        session_guard();
        $id      = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $product = $this->model->findById($id);
        if (!$product) { http_response_code(404); include __DIR__ . '/../views/errors/404.php'; return; }
        $pageTitle = e($product['name']);
        include __DIR__ . '/../views/shop/show.php';
    }

    public function apiSearch(): void {
        session_guard();
        $q = trim($_GET['q'] ?? '');
        $results = $q !== '' ? $this->model->search($q) : $this->model->getAllAvailable();
        json_response(['ok' => true, 'products' => $results]);
    }

    public function apiFilter(): void {
        session_guard();
        $catId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : null;
        json_response(['ok' => true, 'products' => $this->model->getAllAvailable($catId ?: null)]);
    }

    public function apiReviews(): void {
        session_guard();
        $id  = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $pdo = $this->model->getConnection();
        $s   = $pdo->prepare(
            'SELECT r.*, u.name AS reviewer_name
             FROM reviews r JOIN users u ON u.id = r.user_id
             WHERE r.product_id = ? ORDER BY r.created_at DESC'
        );
        $s->execute([$id]);
        json_response(['ok' => true, 'reviews' => $s->fetchAll()]);
    }
}