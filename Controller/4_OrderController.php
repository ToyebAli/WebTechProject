<?php

require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../Model/4_OrderModel.php';

// TASK 4 PART START: Order management controller
class Task4OrderController
{
    private Task4OrderModel $model;
    private array $allowedStatuses = ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];

    public function __construct()
    {
        $this->model = new Task4OrderModel();
    }

    public function myOrders(): void
    {
        session_guard();
        $orders = $this->model->getOrdersForUser((int)$_SESSION['user_id']);
        $pageTitle = 'My Orders';
        include __DIR__ . '/../View/4_myOrders.php';
    }

    public function show(): void
    {
        session_guard();
        $orderId = (int)($_GET['id'] ?? 0);
        $order = $this->model->getOrderForUserWithItems($orderId, (int)$_SESSION['user_id']);

        if (!$order) {
            http_response_code(404);
            include __DIR__ . '/../View/404.php';
            return;
        }

        $pageTitle = 'Order #' . $order['id'];
        include __DIR__ . '/../View/4_orderDetails.php';
    }

    public function adminOrders(): void
    {
        require_admin();
        $status = trim($_GET['status'] ?? '');
        $fromDate = trim($_GET['from_date'] ?? '');
        $toDate = trim($_GET['to_date'] ?? '');

        if (!in_array($status, $this->allowedStatuses, true)) {
            $status = '';
        }

        $orders = $this->model->getAllOrders($status, $fromDate, $toDate);
        $pageTitle = 'Order Management';
        include __DIR__ . '/../View/4_adminOrders.php';
    }

    public function apiUpdateStatus(int $orderId): void
    {
        require_admin();

        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'PUT') {
            json_response(['ok' => false, 'message' => 'PUT method required.'], 405);
        }

        $body = json_decode(file_get_contents('php://input'), true);
        $newStatus = trim((string)($body['status'] ?? ''));

        if (!in_array($newStatus, $this->allowedStatuses, true)) {
            json_response(['ok' => false, 'message' => 'Invalid order status.'], 422);
        }

        $currentStatus = $this->model->findStatusById($orderId);
        if ($currentStatus === false) {
            json_response(['ok' => false, 'message' => 'Order not found.'], 404);
        }

        if (!$this->isValidStatusChange($currentStatus, $newStatus)) {
            json_response(['ok' => false, 'message' => 'Invalid status change.'], 422);
        }

        $this->model->updateStatus($orderId, $newStatus);
        json_response(['ok' => true]);
    }

    private function isValidStatusChange(string $currentStatus, string $newStatus): bool
    {
        if ($currentStatus === $newStatus) {
            return true;
        }

        if ($newStatus === 'Cancelled') {
            return true;
        }

        $nextStatus = [
            'Pending' => 'Processing',
            'Processing' => 'Shipped',
            'Shipped' => 'Delivered',
        ];

        return isset($nextStatus[$currentStatus]) && $nextStatus[$currentStatus] === $newStatus;
    }
}
// TASK 4 PART END

