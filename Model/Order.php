<?php
class Order {
    private PDO $db;
    public function __construct() {
        static $pdo = null;
        if ($pdo === null) {
            $pdo = new PDO(
                'mysql:host=127.0.0.1;dbname=ecommerce_store;charset=utf8mb4',
                'root', '',
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                 PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                 PDO::ATTR_EMULATE_PREPARES => false]
            );
        }
        $this->db = $pdo;
    }

    public function place(int $userId, string $address, string $payment,
                          array $cartItems, array $productMap): int {
        $total = 0;
        foreach ($cartItems as $pid => $qty)
            $total += $productMap[$pid]['price'] * $qty;

        $this->db->beginTransaction();
        try {
            foreach ($cartItems as $pid => $qty) {
                $p = $productMap[$pid];
                if ($p['stock_qty'] < $qty || !$p['is_available'])
                    throw new RuntimeException("Sorry, \"{$p['name']}\" no longer has enough stock.");
            }
            $s = $this->db->prepare(
                'INSERT INTO orders (user_id,shipping_address,payment_method,total_amount,status)
                 VALUES (?,?,?,?,?)'
            );
            $s->execute([$userId, $address, $payment, $total, 'Pending']);
            $orderId = (int)$this->db->lastInsertId();
            $si = $this->db->prepare(
                'INSERT INTO order_items (order_id,product_id,quantity,unit_price) VALUES (?,?,?,?)'
            );
            $sd = $this->db->prepare('UPDATE products SET stock_qty = stock_qty - ? WHERE id = ?');
            foreach ($cartItems as $pid => $qty) {
                $si->execute([$orderId, $pid, $qty, $productMap[$pid]['price']]);
                $sd->execute([$qty, $pid]);
            }
            $this->db->commit();
            return $orderId;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function findWithItems(int $orderId): array|false {
        $s = $this->db->prepare('SELECT * FROM orders WHERE id = ?');
        $s->execute([$orderId]);
        $order = $s->fetch();
        if (!$order) return false;
        $si = $this->db->prepare(
            'SELECT oi.*, p.name AS product_name, p.primary_image_path
             FROM order_items oi JOIN products p ON p.id = oi.product_id
             WHERE oi.order_id = ?'
        );
        $si->execute([$orderId]);
        $order['items'] = $si->fetchAll();
        return $order;
    }
}