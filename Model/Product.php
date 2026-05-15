
<?php
class Product {
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

    public function getAllAvailable(?int $categoryId = null): array {
        $sql = 'SELECT p.*, c.name AS category_name,
                       COALESCE(ROUND(AVG(r.rating),1),0) AS avg_rating,
                       COUNT(r.id) AS review_count
                FROM products p
                LEFT JOIN categories c ON c.id = p.category_id
                LEFT JOIN reviews r ON r.product_id = p.id
                WHERE p.is_available = 1';
        $params = [];
        if ($categoryId) { $sql .= ' AND p.category_id = ?'; $params[] = $categoryId; }
        $sql .= ' GROUP BY p.id ORDER BY p.created_at DESC';
        $s = $this->db->prepare($sql);
        $s->execute($params);
        return $s->fetchAll();
    }

    public function search(string $q): array {
        $like = '%' . $q . '%';
        $s = $this->db->prepare(
            'SELECT p.*, c.name AS category_name,
                    COALESCE(ROUND(AVG(r.rating),1),0) AS avg_rating,
                    COUNT(r.id) AS review_count
             FROM products p
             LEFT JOIN categories c ON c.id = p.category_id
             LEFT JOIN reviews r ON r.product_id = p.id
             WHERE p.is_available = 1
               AND (p.name LIKE ? OR p.description LIKE ?)
             GROUP BY p.id ORDER BY p.created_at DESC'
        );
        $s->execute([$like, $like]);
        return $s->fetchAll();
    }

    public function findById(int $id): array|false {
        $s = $this->db->prepare(
            'SELECT p.*, c.name AS category_name,
                    COALESCE(ROUND(AVG(r.rating),1),0) AS avg_rating,
                    COUNT(r.id) AS review_count
             FROM products p
             LEFT JOIN categories c ON c.id = p.category_id
             LEFT JOIN reviews r ON r.product_id = p.id
             WHERE p.id = ? GROUP BY p.id'
        );
        $s->execute([$id]);
        return $s->fetch();
    }

    public function findByIds(array $ids): array {
        if (empty($ids)) return [];
        $ph = implode(',', array_fill(0, count($ids), '?'));
        $s  = $this->db->prepare("SELECT * FROM products WHERE id IN ($ph)");
        $s->execute(array_values($ids));
        $map = [];
        foreach ($s->fetchAll() as $r) $map[$r['id']] = $r;
        return $map;
    }

    public function getCategories(): array {
        return $this->db->query('SELECT * FROM categories ORDER BY name')->fetchAll();
    }

    public function getConnection(): PDO { return $this->db; }
}