<?php

require_once __DIR__ . "/../config/helpers.php";
require_once __DIR__ . "/../Model/CategoryModel.php";

require_admin();

$categoryModel = new CategoryModel();

if (!isset($_GET["id"])) {
    redirect("/admin/categories?error=" . urlencode("Category ID is missing"));
}

$id = intval($_GET["id"]);

if ($id <= 0) {
    redirect("/admin/categories?error=" . urlencode("Invalid category ID"));
}

$category = $categoryModel->getCategoryById($id);

if (!$category) {
    redirect("/admin/categories?error=" . urlencode("Category not found"));
}

$childCount = $categoryModel->countChildCategories($id);
$productCount = $categoryModel->countProductsByCategory($id);

if ($childCount > 0) {
    redirect("/admin/categories?error=" . urlencode("Cannot delete this category because it has child categories"));
}

if ($productCount > 0) {
    redirect("/admin/categories?error=" . urlencode("Cannot delete this category because products are using it"));
}

$success = $categoryModel->deleteCategory($id);

if ($success) {
    redirect("/admin/categories?success=" . urlencode("Category deleted successfully"));
} else {
    redirect("/admin/categories?error=" . urlencode("Failed to delete category"));
}

?>
