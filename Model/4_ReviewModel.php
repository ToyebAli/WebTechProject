<?php

require_once __DIR__ . '/DatabaseConnection.php';

// TASK 4 PART START: Product review model
class Task4ReviewModel
{
    private DatabaseConnection $db;

    public function __construct()
    {
        $this->db = new DatabaseConnection();
    }

    public function canUserReviewDeliveredProduct(int $userId, int $productId): bool
    {
        $connection = $this->db->openConnection();
        $sql = 'SELECT COUNT(*) AS total
                FROM orders o
                JOIN order_items oi ON oi.order_id = o.id
                WHERE o.user_id = ?
                  AND oi.product_id = ?
                  AND o.status = \'Delivered\'';
        $stmt = $connection->prepare($sql);
        $stmt->bind_param('ii', $userId, $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        $this->db->closeConnection($connection);

        return ((int)($row['total'] ?? 0)) > 0;
    }

    public function findUserReview(int $userId, int $productId): array|false
    {
        $connection = $this->db->openConnection();
        $sql = 'SELECT id, product_id, user_id, rating, review_text, created_at
                FROM reviews
                WHERE user_id = ? AND product_id = ?
                LIMIT 1';
        $stmt = $connection->prepare($sql);
        $stmt->bind_param('ii', $userId, $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        $review = $result->fetch_assoc();
        $stmt->close();
        $this->db->closeConnection($connection);

        return $review ?: false;
    }

    public function addReview(int $productId, int $userId, int $rating, string $reviewText): array|false
    {
        $connection = $this->db->openConnection();
        $sql = 'INSERT INTO reviews (product_id, user_id, rating, review_text)
                VALUES (?, ?, ?, ?)';
        $stmt = $connection->prepare($sql);
        $stmt->bind_param('iiis', $productId, $userId, $rating, $reviewText);
        $success = $stmt->execute();
        $stmt->close();
        $this->db->closeConnection($connection);

        if (!$success) {
            return false;
        }

        return $this->findUserReview($userId, $productId);
    }

    public function getProductAverage(int $productId): float
    {
        $connection = $this->db->openConnection();
        $sql = 'SELECT COALESCE(ROUND(AVG(rating), 1), 0) AS average
                FROM reviews
                WHERE product_id = ?';
        $stmt = $connection->prepare($sql);
        $stmt->bind_param('i', $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        $this->db->closeConnection($connection);

        return (float)($row['average'] ?? 0);
    }

    public function getReviewsForProduct(int $productId): array
    {
        $connection = $this->db->openConnection();
        $sql = 'SELECT
                    r.id,
                    r.product_id,
                    r.user_id,
                    r.rating,
                    r.review_text,
                    r.created_at,
                    u.name AS reviewer_name
                FROM reviews r
                JOIN users u ON u.id = r.user_id
                WHERE r.product_id = ?
                ORDER BY r.created_at DESC, r.id DESC';
        $stmt = $connection->prepare($sql);
        $stmt->bind_param('i', $productId);
        $stmt->execute();
        $result = $stmt->get_result();

        $reviews = [];
        while ($row = $result->fetch_assoc()) {
            $reviews[] = $row;
        }

        $stmt->close();
        $this->db->closeConnection($connection);
        return $reviews;
    }
}
// TASK 4 PART END

