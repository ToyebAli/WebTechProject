<?php

require_once __DIR__ . "/../config/helpers.php";
require_once __DIR__ . "/../Model/2_ProductModel.php";

require_admin();

$productModel = new ProductModel();

$method = $_SERVER["REQUEST_METHOD"];

if ($method !== "POST" && $method !== "PATCH") {
    json_response([
        "ok" => false,
        "message" => "Invalid request method"
    ], 405);
}

$id = $_GET["id"] ?? "";

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
    json_response([
        "ok" => false,
        "message" => "Invalid product ID"
    ], 422);
}

$product = $productModel->getProductById($id);

if (!$product) {
    json_response([
        "ok" => false,
        "message" => "Product not found"
    ], 404);
}

$newStatus = $productModel->toggleAvailability($id);

if ($newStatus === false) {
    json_response([
        "ok" => false,
        "message" => "Failed to update availability"
    ], 500);
}

json_response([
    "ok" => true,
    "message" => "Availability updated successfully",
    "is_available" => (int)$newStatus
]);
