<?php

require_once __DIR__ . "/DatabaseConnection.php";

class ProductModel
{
    private $db;

    public function __construct()
    {
        $this->db = new DatabaseConnection();
    }

    public function getAllProducts()
    {
        $connection = $this->db->openConnection();

        $sql = "SELECT 
                    p.id,
                    p.category_id,
                    p.name,
                    p.description,
                    p.price,
                    p.stock_qty,
                    p.primary_image_path,
                    p.is_available,
                    p.created_at,
                    c.name AS category_name,
                    (
                        SELECT AVG(r.rating)
                        FROM reviews r
                        WHERE r.product_id = p.id
                    ) AS avg_rating
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                ORDER BY p.id DESC";

        $stmt = $connection->prepare($sql);
        $stmt->execute();

        $result = $stmt->get_result();

        $products = [];

        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }

        $stmt->close();
        $this->db->closeConnection($connection);

        return $products;
    }

    public function getProductById($id)
    {
        $connection = $this->db->openConnection();

        $sql = "SELECT 
                    id,
                    category_id,
                    name,
                    description,
                    price,
                    stock_qty,
                    primary_image_path,
                    is_available,
                    created_at
                FROM products
                WHERE id = ?";

        $stmt = $connection->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $result = $stmt->get_result();
        $product = $result->fetch_assoc();

        $stmt->close();
        $this->db->closeConnection($connection);

        return $product;
    }

    public function getCategoriesForDropdown()
    {
        $connection = $this->db->openConnection();

        $sql = "SELECT 
                    c.id,
                    c.name,
                    c.parent_id,
                    p.name AS parent_name
                FROM categories c
                LEFT JOIN categories p ON c.parent_id = p.id
                ORDER BY 
                    CASE 
                        WHEN c.parent_id IS NULL THEN c.id
                        ELSE c.parent_id
                    END,
                    c.parent_id,
                    c.name ASC";

        $stmt = $connection->prepare($sql);
        $stmt->execute();

        $result = $stmt->get_result();

        $categories = [];

        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }

        $stmt->close();
        $this->db->closeConnection($connection);

        return $categories;
    }

    public function addProduct($category_id, $name, $description, $price, $stock_qty, $primary_image_path, $is_available)
    {
        $connection = $this->db->openConnection();

        $sql = "INSERT INTO products
                    (category_id, name, description, price, stock_qty, primary_image_path, is_available)
                VALUES
                    (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $connection->prepare($sql);

        $stmt->bind_param(
            "issdisi",
            $category_id,
            $name,
            $description,
            $price,
            $stock_qty,
            $primary_image_path,
            $is_available
        );

        $success = $stmt->execute();

        $stmt->close();
        $this->db->closeConnection($connection);

        return $success;
    }

    public function updateProductWithoutImage($id, $category_id, $name, $description, $price, $stock_qty, $is_available)
    {
        $connection = $this->db->openConnection();

        $sql = "UPDATE products
                SET category_id = ?,
                    name = ?,
                    description = ?,
                    price = ?,
                    stock_qty = ?,
                    is_available = ?
                WHERE id = ?";

        $stmt = $connection->prepare($sql);

        $stmt->bind_param(
            "issdiii",
            $category_id,
            $name,
            $description,
            $price,
            $stock_qty,
            $is_available,
            $id
        );

        $success = $stmt->execute();

        $stmt->close();
        $this->db->closeConnection($connection);

        return $success;
    }

    public function updateProductWithImage($id, $category_id, $name, $description, $price, $stock_qty, $primary_image_path, $is_available)
    {
        $connection = $this->db->openConnection();

        $sql = "UPDATE products
                SET category_id = ?,
                    name = ?,
                    description = ?,
                    price = ?,
                    stock_qty = ?,
                    primary_image_path = ?,
                    is_available = ?
                WHERE id = ?";

        $stmt = $connection->prepare($sql);

        $stmt->bind_param(
            "issdisii",
            $category_id,
            $name,
            $description,
            $price,
            $stock_qty,
            $primary_image_path,
            $is_available,
            $id
        );

        $success = $stmt->execute();

        $stmt->close();
        $this->db->closeConnection($connection);

        return $success;
    }

    public function countOrderItemsByProduct($id)
    {
        $connection = $this->db->openConnection();

        $sql = "SELECT COUNT(*) AS total
                FROM order_items
                WHERE product_id = ?";

        $stmt = $connection->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        $stmt->close();
        $this->db->closeConnection($connection);

        return $row["total"];
    }

    public function canDeleteProduct($id)
    {
        $orderItemCount = $this->countOrderItemsByProduct($id);

        if ($orderItemCount > 0) {
            return false;
        }

        return true;
    }

    public function deleteProduct($id)
    {
        if (!$this->canDeleteProduct($id)) {
            return false;
        }

        $connection = $this->db->openConnection();

        $sql = "DELETE FROM products
                WHERE id = ?";

        $stmt = $connection->prepare($sql);
        $stmt->bind_param("i", $id);

        $success = $stmt->execute();

        $stmt->close();
        $this->db->closeConnection($connection);

        return $success;
    }

    public function toggleAvailability($id)
    {
        $connection = $this->db->openConnection();

        $product = $this->getProductById($id);

        if (!$product) {
            $this->db->closeConnection($connection);
            return false;
        }

        if ($product["is_available"] == 1) {
            $newStatus = 0;
        } else {
            $newStatus = 1;
        }

        $sql = "UPDATE products
                SET is_available = ?
                WHERE id = ?";

        $stmt = $connection->prepare($sql);
        $stmt->bind_param("ii", $newStatus, $id);

        $success = $stmt->execute();

        $stmt->close();
        $this->db->closeConnection($connection);

        if ($success) {
            return $newStatus;
        }

        return false;
    }
}

?>
