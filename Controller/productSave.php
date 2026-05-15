<?php

session_start();

require_once __DIR__ . "/../Model/ProductModel.php";

$productModel = new ProductModel();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../View/productList.php");
    exit;
}

$id = isset($_POST["id"]) ? trim($_POST["id"]) : "";
$category_id = isset($_POST["category_id"]) ? trim($_POST["category_id"]) : "";
$name = isset($_POST["name"]) ? trim($_POST["name"]) : "";
$description = isset($_POST["description"]) ? trim($_POST["description"]) : "";
$price = isset($_POST["price"]) ? trim($_POST["price"]) : "";
$stock_qty = isset($_POST["stock_qty"]) ? trim($_POST["stock_qty"]) : "";
$is_available = isset($_POST["is_available"]) ? intval($_POST["is_available"]) : 1;

function redirectProductFormWithErrors($id, $oldInput, $errors)
{
    $_SESSION["product_old"] = $oldInput;
    $_SESSION["product_errors"] = $errors;

    if ($id !== "") {
        header("Location: ../View/productForm.php?id=" . urlencode($id));
    } else {
        header("Location: ../View/productForm.php");
    }

    exit;
}

$oldInput = [
    "id" => $id,
    "category_id" => $category_id,
    "name" => $name,
    "description" => $description,
    "price" => $price,
    "stock_qty" => $stock_qty,
    "is_available" => $is_available
];

$errors = [];

if ($name === "") {
    $errors["name"] = "Product name is required";
}

if ($description === "") {
    $errors["description"] = "Description is required";
}

if ($category_id === "") {
    $errors["category_id"] = "Category is required";
}

if ($price === "") {
    $errors["price"] = "Price is required";
} else if (!is_numeric($price)) {
    $errors["price"] = "Price must be a number";
} else if (floatval($price) <= 0) {
    $errors["price"] = "Price must be positive";
}

if ($stock_qty === "") {
    $errors["stock_qty"] = "Stock quantity is required";
} else if (filter_var($stock_qty, FILTER_VALIDATE_INT) === false) {
    $errors["stock_qty"] = "Stock quantity must be a whole number";
} else if (intval($stock_qty) < 0) {
    $errors["stock_qty"] = "Stock quantity cannot be negative";
}

if (!empty($errors)) {
    redirectProductFormWithErrors($id, $oldInput, $errors);
}

$category_id = intval($category_id);
$price = floatval($price);
$stock_qty = intval($stock_qty);

if ($is_available !== 0 && $is_available !== 1) {
    $is_available = 1;
}

$categories = $productModel->getCategoriesForDropdown();
$categoryExists = false;

foreach ($categories as $category) {
    if (intval($category["id"]) === $category_id) {
        $categoryExists = true;
        break;
    }
}

if (!$categoryExists) {
    $errors["category_id"] = "Selected category does not exist";
    redirectProductFormWithErrors($id, $oldInput, $errors);
}

$uploadFolder = __DIR__ . "/../public/uploads/products/";
$allowedMimeTypes = ["image/jpeg", "image/png"];
$maxFileSize = 3 * 1024 * 1024;

if (!is_dir($uploadFolder)) {
    mkdir($uploadFolder, 0777, true);
}

$newImagePath = "";

$imageSelected = false;

if (isset($_FILES["primary_image"]) && $_FILES["primary_image"]["error"] !== UPLOAD_ERR_NO_FILE) {
    $imageSelected = true;
}

if ($id === "" && !$imageSelected) {
    $errors["image"] = "Product image is required";
    redirectProductFormWithErrors($id, $oldInput, $errors);
}

if ($imageSelected) {
    if ($_FILES["primary_image"]["error"] !== UPLOAD_ERR_OK) {
        $errors["image"] = "Image upload failed";
        redirectProductFormWithErrors($id, $oldInput, $errors);
    }

    if ($_FILES["primary_image"]["size"] > $maxFileSize) {
        $errors["image"] = "Image size must be 3 MB or less";
        redirectProductFormWithErrors($id, $oldInput, $errors);
    }

    $tmpName = $_FILES["primary_image"]["tmp_name"];

    $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($fileInfo, $tmpName);
    finfo_close($fileInfo);

    if (!in_array($mimeType, $allowedMimeTypes)) {
        $errors["image"] = "Only JPEG and PNG images are allowed";
        redirectProductFormWithErrors($id, $oldInput, $errors);
    }

    if ($mimeType === "image/jpeg") {
        $extension = "jpg";
    } else {
        $extension = "png";
    }

    $newFileName = "product_" . time() . "_" . rand(1000, 9999) . "." . $extension;
    $destination = $uploadFolder . $newFileName;

    if (!move_uploaded_file($tmpName, $destination)) {
        $errors["image"] = "Failed to save uploaded image";
        redirectProductFormWithErrors($id, $oldInput, $errors);
    }

    $newImagePath = "public/uploads/products/" . $newFileName;
}

if ($id === "") {
    $success = $productModel->addProduct(
        $category_id,
        $name,
        $description,
        $price,
        $stock_qty,
        $newImagePath,
        $is_available
    );

    if ($success) {
        header("Location: ../View/productList.php?success=" . urlencode("Product added successfully"));
        exit;
    } else {
        $errors["general"] = "Failed to add product";
        redirectProductFormWithErrors($id, $oldInput, $errors);
    }
} else {
    $id = intval($id);

    $existingProduct = $productModel->getProductById($id);

    if (!$existingProduct) {
        header("Location: ../View/productList.php?error=" . urlencode("Product not found"));
        exit;
    }

    if ($newImagePath !== "") {
        $success = $productModel->updateProductWithImage(
            $id,
            $category_id,
            $name,
            $description,
            $price,
            $stock_qty,
            $newImagePath,
            $is_available
        );
    } else {
        $success = $productModel->updateProductWithoutImage(
            $id,
            $category_id,
            $name,
            $description,
            $price,
            $stock_qty,
            $is_available
        );
    }

    if ($success) {
        header("Location: ../View/productList.php?success=" . urlencode("Product updated successfully"));
        exit;
    } else {
        $errors["general"] = "Failed to update product";
        redirectProductFormWithErrors($id, $oldInput, $errors);
    }
}

?>
