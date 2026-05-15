<?php

header("Content-Type: application/json");

require_once __DIR__ . "/../Model/ProductModel.php";

$productModel = new ProductModel();

$method = $_SERVER["REQUEST_METHOD"];

if ($method !== "POST" && $method !== "PATCH") {
    echo json_encode([
        "ok" => false,
        "message" => "Invalid request method"
    ]);
    exit;
}

$id = "";

if ($method === "POST") {
    if (isset($_POST["id"])) {
        $id = $_POST["id"];
    }
}

if ($method === "PATCH") {
    $rawInput = file_get_contents("php://input");
    parse_str($rawInput, $patchData);

    if (isset($patchData["id"])) {
        $id = $patchData["id"];
    }
}

$id = intval($id);

if ($id <= 0) {
    echo json_encode([
        "ok" => false,
        "message" => "Invalid product ID"
    ]);
    exit;
}

$product = $productModel->getProductById($id);

if (!$product) {
    echo json_encode([
        "ok" => false,
        "message" => "Product not found"
    ]);
    exit;
}

$newStatus = $productModel->toggleAvailability($id);

if ($newStatus === false) {
    echo json_encode([
        "ok" => false,
        "message" => "Failed to update availability"
    ]);
    exit;
}

echo json_encode([
    "ok" => true,
    "message" => "Availability updated successfully",
    "is_available" => (int)$newStatus
]);

?>
