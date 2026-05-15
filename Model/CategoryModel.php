<?php

require_once __DIR__ . "/DatabaseConnection.php";

class CategoryModel
{
    private $db;

    public function __construct()
    {
        $this->db = new DatabaseConnection();
    }

    public function getAllCategories()
    {
        $connection = $this->db->openConnection();

        $sql = "SELECT 
                    c.id,
                    c.name,
                    c.parent_id,
                    p.name AS parent_name
                FROM categories c
                LEFT JOIN categories p ON c.parent_id = p.id
                ORDER BY c.id DESC";

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

    public function getParentCategories()
    {
        $connection = $this->db->openConnection();

        $sql = "SELECT id, name 
                FROM categories 
                WHERE parent_id IS NULL 
                ORDER BY name ASC";

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

    public function getCategoryById($id)
    {
        $connection = $this->db->openConnection();

        $sql = "SELECT id, name, parent_id 
                FROM categories 
                WHERE id = ?";

        $stmt = $connection->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $result = $stmt->get_result();
        $category = $result->fetch_assoc();

        $stmt->close();
        $this->db->closeConnection($connection);

        return $category;
    }

    public function addCategory($name, $parent_id)
    {
        $connection = $this->db->openConnection();

        if ($parent_id === "" || $parent_id === null) {
            $parent_id = null;
        }

        $sql = "INSERT INTO categories (name, parent_id) 
                VALUES (?, ?)";

        $stmt = $connection->prepare($sql);
        $stmt->bind_param("si", $name, $parent_id);

        $success = $stmt->execute();

        $stmt->close();
        $this->db->closeConnection($connection);

        return $success;
    }

    public function updateCategory($id, $name, $parent_id)
    {
        $connection = $this->db->openConnection();

        if ($parent_id === "" || $parent_id === null) {
            $parent_id = null;
        }

        $sql = "UPDATE categories 
                SET name = ?, parent_id = ? 
                WHERE id = ?";

        $stmt = $connection->prepare($sql);
        $stmt->bind_param("sii", $name, $parent_id, $id);

        $success = $stmt->execute();

        $stmt->close();
        $this->db->closeConnection($connection);

        return $success;
    }

    public function countChildCategories($id)
    {
        $connection = $this->db->openConnection();

        $sql = "SELECT COUNT(*) AS total 
                FROM categories 
                WHERE parent_id = ?";

        $stmt = $connection->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        $stmt->close();
        $this->db->closeConnection($connection);

        return $row["total"];
    }

    public function countProductsByCategory($id)
    {
        $connection = $this->db->openConnection();

        $sql = "SELECT COUNT(*) AS total 
                FROM products 
                WHERE category_id = ?";

        $stmt = $connection->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        $stmt->close();
        $this->db->closeConnection($connection);

        return $row["total"];
    }

    public function canDeleteCategory($id)
    {
        $childCount = $this->countChildCategories($id);
        $productCount = $this->countProductsByCategory($id);

        if ($childCount > 0 || $productCount > 0) {
            return false;
        }

        return true;
    }

    public function deleteCategory($id)
    {
        if (!$this->canDeleteCategory($id)) {
            return false;
        }

        $connection = $this->db->openConnection();

        $sql = "DELETE FROM categories 
                WHERE id = ?";

        $stmt = $connection->prepare($sql);
        $stmt->bind_param("i", $id);

        $success = $stmt->execute();

        $stmt->close();
        $this->db->closeConnection($connection);

        return $success;
    }
}

?>
