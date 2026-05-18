<?php

require_once __DIR__ . '/DatabaseConnection.php';

// TASK 4 PART START: Order management model
class Task4OrderModel
{
    private DatabaseConnection $db;

    public function __construct()
    {
        $this->db = new DatabaseConnection();
    }

    public function getOrdersForUser(int $userId): array
    {
        $connection = $this->db->openConnection();
        $sql = 'SELECT id, created_at, total_amount, status
                FROM orders
                WHERE user_id = ?
                ORDER BY created_at DESC, id DESC';
        $stmt = $connection->prepare($sql);
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }

        $stmt->close();
        $this->db->closeConnection($connection);
        return $orders;
    }

    public function getOrderForUserWithItems(int $orderId, int $userId): array|false
    {
        $connection = $this->db->openConnection();

        $orderSql = 'SELECT *
                     FROM orders
                     WHERE id = ? AND user_id = ?
                     LIMIT 1';
        $orderStmt = $connection->prepare($orderSql);
        $orderStmt->bind_param('ii', $orderId, $userId);
        $orderStmt->execute();
        $orderResult = $orderStmt->get_result();
        $order = $orderResult->fetch_assoc();
        $orderStmt->close();

        if (!$order) {
            $this->db->closeConnection($connection);
            return false;
        }

        $itemSql = 'SELECT
                        oi.product_id,
                        oi.quantity,
                        oi.unit_price,
                        p.name AS product_name,
                        p.primary_image_path,
                        r.id AS review_id,
                        r.rating AS review_rating,
                        r.review_text
                    FROM order_items oi
                    JOIN products p ON p.id = oi.product_id
                    LEFT JOIN reviews r
                        ON r.product_id = oi.product_id
                       AND r.user_id = ?
                    WHERE oi.order_id = ?
                    ORDER BY oi.id ASC';
        $itemStmt = $connection->prepare($itemSql);
        $itemStmt->bind_param('ii', $userId, $orderId);
        $itemStmt->execute();
        $itemResult = $itemStmt->get_result();

        $items = [];
        while ($row = $itemResult->fetch_assoc()) {
            $items[] = $row;
        }

        $itemStmt->close();
        $this->db->closeConnection($connection);
        $order['items'] = $items;
        return $order;
    }

    public function getAllOrders(string $status = '', string $fromDate = '', string $toDate = ''): array
    {
        $connection = $this->db->openConnection();
        $sql = 'SELECT
                    o.id,
                    o.user_id,
                    o.created_at,
                    o.total_amount,
                    o.status,
                    u.name AS customer_name,
                    u.email AS customer_email
                FROM orders o
                JOIN users u ON u.id = o.user_id
                WHERE (? = \'\' OR o.status = ?)
                  AND (? = \'\' OR DATE(o.created_at) >= ?)
                  AND (? = \'\' OR DATE(o.created_at) <= ?)
                ORDER BY o.created_at DESC, o.id DESC';
        $stmt = $connection->prepare($sql);
        $stmt->bind_param('ssssss', $status, $status, $fromDate, $fromDate, $toDate, $toDate);
        $stmt->execute();
        $result = $stmt->get_result();

        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }

        $stmt->close();
        $this->db->closeConnection($connection);
        return $orders;
    }

    public function findStatusById(int $orderId): string|false
    {
        $connection = $this->db->openConnection();
        $sql = 'SELECT status FROM orders WHERE id = ? LIMIT 1';
        $stmt = $connection->prepare($sql);
        $stmt->bind_param('i', $orderId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        $this->db->closeConnection($connection);

        return $row['status'] ?? false;
    }

    public function updateStatus(int $orderId, string $status): bool
    {
        $connection = $this->db->openConnection();
        $sql = 'UPDATE orders SET status = ? WHERE id = ?';
        $stmt = $connection->prepare($sql);
        $stmt->bind_param('si', $status, $orderId);
        $success = $stmt->execute();
        $stmt->close();
        $this->db->closeConnection($connection);
        return $success;
    }
}
// TASK 4 PART END

