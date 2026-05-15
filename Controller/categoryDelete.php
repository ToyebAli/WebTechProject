<?php

require_once __DIR__ . "/../Model/CategoryModel.php";

$categoryModel = new CategoryModel();

if (!isset($_GET["id"])) {
    header("Location: ../View/categoryList.php?error=" . urlencode("Category ID is missing"));
    exit;
}

$id = intval($_GET["id"]);

if ($id <= 0) {
    header("Location: ../View/categoryList.php?error=" . urlencode("Invalid category ID"));
    exit;
}

$category = $categoryModel->getCategoryById($id);

if (!$category) {
    header("Location: ../View/categoryList.php?error=" . urlencode("Category not found"));
    exit;
}

$childCount = $categoryModel->countChildCategories($id);
$productCount = $categoryModel->countProductsByCategory($id);

if ($childCount > 0) {
    header("Location: ../View/categoryList.php?error=" . urlencode("Cannot delete this category because it has child categories"));
    exit;
}

if ($productCount > 0) {
    header("Location: ../View/categoryList.php?error=" . urlencode("Cannot delete this category because products are using it"));
    exit;
}

$success = $categoryModel->deleteCategory($id);

if ($success) {
    header("Location: ../View/categoryList.php?success=" . urlencode("Category deleted successfully"));
    exit;
} else {
    header("Location: ../View/categoryList.php?error=" . urlencode("Failed to delete category"));
    exit;
}

?>
