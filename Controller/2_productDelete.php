<?php

require_once __DIR__ . "/../config/helpers.php";
require_once __DIR__ . "/../Model/2_ProductModel.php";

require_admin();

$productModel = new ProductModel();

if (!isset($_GET["id"])) {
    redirect("/admin/products?error=" . urlencode("Product ID is missing"));
}

$id = intval($_GET["id"]);

if ($id <= 0) {
    redirect("/admin/products?error=" . urlencode("Invalid product ID"));
}

$product = $productModel->getProductById($id);

if (!$product) {
    redirect("/admin/products?error=" . urlencode("Product not found"));
}

$orderItemCount = $productModel->countOrderItemsByProduct($id);

if ($orderItemCount > 0) {
    redirect("/admin/products?error=" . urlencode("Cannot delete this product because it exists in order items"));
}

$success = $productModel->deleteProduct($id);

if ($success) {

    if (!empty($product["primary_image_path"])) {
        $imageFullPath = __DIR__ . "/../" . str_replace("public/", "Public/", $product["primary_image_path"]);

        if (file_exists($imageFullPath)) {
            unlink($imageFullPath);
        }
    }

    redirect("/admin/products?success=" . urlencode("Product deleted successfully"));
} else {
    redirect("/admin/products?error=" . urlencode("Failed to delete product"));
}

?>
