<?php

require_once __DIR__ . "/../Model/ProductModel.php";

$productModel = new ProductModel();

if (!isset($_GET["id"])) {
    header("Location: ../View/productList.php?error=" . urlencode("Product ID is missing"));
    exit;
}

$id = intval($_GET["id"]);

if ($id <= 0) {
    header("Location: ../View/productList.php?error=" . urlencode("Invalid product ID"));
    exit;
}

$product = $productModel->getProductById($id);

if (!$product) {
    header("Location: ../View/productList.php?error=" . urlencode("Product not found"));
    exit;
}

$orderItemCount = $productModel->countOrderItemsByProduct($id);

if ($orderItemCount > 0) {
    header("Location: ../View/productList.php?error=" . urlencode("Cannot delete this product because it exists in order items"));
    exit;
}

$success = $productModel->deleteProduct($id);

if ($success) {

    if (!empty($product["primary_image_path"])) {
        $imageFullPath = __DIR__ . "/../" . $product["primary_image_path"];

        if (file_exists($imageFullPath)) {
            unlink($imageFullPath);
        }
    }

    header("Location: ../View/productList.php?success=" . urlencode("Product deleted successfully"));
    exit;
} else {
    header("Location: ../View/productList.php?error=" . urlencode("Failed to delete product"));
    exit;
}

?>
