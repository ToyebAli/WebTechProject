<?php

require_once __DIR__ . "/../config/helpers.php";
require_admin();
start_session_if_needed();

require_once __DIR__ . "/../Model/ProductModel.php";

$productModel = new ProductModel();

$id = "";
$category_id = "";
$name = "";
$description = "";
$price = "";
$stock_qty = "";
$primary_image_path = "";
$is_available = 1;

$pageTitle = "Add New Product";

$errors = [
    "category_id" => "",
    "name" => "",
    "description" => "",
    "price" => "",
    "stock_qty" => "",
    "image" => "",
    "general" => ""
];

$oldInput = [];

if (isset($_SESSION["product_old"])) {
    $oldInput = $_SESSION["product_old"];
    unset($_SESSION["product_old"]);
}

if (isset($_SESSION["product_errors"])) {
    foreach ($_SESSION["product_errors"] as $key => $value) {
        if (array_key_exists($key, $errors)) {
            $errors[$key] = $value;
        }
    }
    unset($_SESSION["product_errors"]);
}

if (isset($_GET["id"])) {
    $id = intval($_GET["id"]);
    $product = $productModel->getProductById($id);

    if ($product) {
        $category_id = $product["category_id"];
        $name = $product["name"];
        $description = $product["description"];
        $price = $product["price"];
        $stock_qty = $product["stock_qty"];
        $primary_image_path = $product["primary_image_path"];
        $is_available = $product["is_available"];
        $pageTitle = "Edit Product";
    } else {
        redirect("/admin/products?error=" . urlencode("Product not found"));
    }
}

if (!empty($oldInput)) {
    if (isset($oldInput["id"])) {
        $id = $oldInput["id"];
    }

    if (isset($oldInput["category_id"])) {
        $category_id = $oldInput["category_id"];
    }

    if (isset($oldInput["name"])) {
        $name = $oldInput["name"];
    }

    if (isset($oldInput["description"])) {
        $description = $oldInput["description"];
    }

    if (isset($oldInput["price"])) {
        $price = $oldInput["price"];
    }

    if (isset($oldInput["stock_qty"])) {
        $stock_qty = $oldInput["stock_qty"];
    }

    if (isset($oldInput["is_available"])) {
        $is_available = $oldInput["is_available"];
    }
}

$categories = $productModel->getCategoriesForDropdown();
include __DIR__ . "/header.php";

?>

<style>
        .admin-page {
            font-family: Arial, sans-serif;
            padding: 20px;
        }

        .container {
            width: 700px;
            margin: auto;
            background-color: white;
            padding: 20px;
            border: 1px solid #ddd;
        }

        h2 {
            text-align: center;
            color: #333;
        }

        label {
            font-weight: bold;
            display: block;
            margin-top: 12px;
        }

        input[type="text"],
        input[type="number"],
        textarea,
        select,
        input[type="file"] {
            width: 100%;
            padding: 9px;
            margin-top: 5px;
            box-sizing: border-box;
        }

        textarea {
            height: 100px;
            resize: vertical;
        }

        .error-text {
            color: red;
            font-size: 14px;
            margin-top: 4px;
        }

        .general-error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }

        .button-row {
            margin-top: 20px;
            text-align: center;
        }

        .btn {
            padding: 9px 14px;
            text-decoration: none;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-save {
            background-color: #28a745;
            color: white;
        }

        .btn-back {
            background-color: #6c757d;
            color: white;
            margin-left: 8px;
        }

        .note {
            color: #666;
            font-size: 14px;
            margin-top: 5px;
        }

        .current-image {
            width: 120px;
            height: 90px;
            object-fit: cover;
            border: 1px solid #ccc;
            margin-top: 8px;
        }

        .no-category-warning {
            background-color: #fff3cd;
            color: #856404;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
    </style>

<div class="admin-page">
<div class="container">

    <h2><?php echo htmlspecialchars($pageTitle); ?></h2>

    <?php if (!empty($errors["general"])) { ?>
        <div class="general-error">
            <?php echo htmlspecialchars($errors["general"]); ?>
        </div>
    <?php } ?>

    <?php if (empty($categories)) { ?>
        <div class="no-category-warning">
            No category found. Please create a category before adding products.
        </div>
    <?php } ?>

    <form method="post" action="<?= $id !== "" ? url('/admin/products/edit') : url('/admin/products/create') ?>" enctype="multipart/form-data">

        <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">

        <label>Product Name</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($name); ?>">

        <?php if (!empty($errors["name"])) { ?>
            <div class="error-text">
                <?php echo htmlspecialchars($errors["name"]); ?>
            </div>
        <?php } ?>

        <label>Description</label>
        <textarea name="description"><?php echo htmlspecialchars($description); ?></textarea>

        <?php if (!empty($errors["description"])) { ?>
            <div class="error-text">
                <?php echo htmlspecialchars($errors["description"]); ?>
            </div>
        <?php } ?>

        <label>Price</label>
        <input type="number" name="price" step="0.01" min="0" value="<?php echo htmlspecialchars($price); ?>">

        <?php if (!empty($errors["price"])) { ?>
            <div class="error-text">
                <?php echo htmlspecialchars($errors["price"]); ?>
            </div>
        <?php } ?>

        <label>Stock Quantity</label>
        <input type="number" name="stock_qty" min="0" value="<?php echo htmlspecialchars($stock_qty); ?>">

        <?php if (!empty($errors["stock_qty"])) { ?>
            <div class="error-text">
                <?php echo htmlspecialchars($errors["stock_qty"]); ?>
            </div>
        <?php } ?>

        <label>Category</label>
        <select name="category_id">
            <option value="">Select Category</option>

            <?php foreach ($categories as $category) { ?>

                <?php
                if ($category["parent_name"] == null) {
                    $categoryText = $category["name"];
                } else {
                    $categoryText = $category["parent_name"] . " - " . $category["name"];
                }
                ?>

                <option value="<?php echo htmlspecialchars($category["id"]); ?>"
                    <?php
                    if ($category_id == $category["id"]) {
                        echo "selected";
                    }
                    ?>
                >
                    <?php echo htmlspecialchars($categoryText); ?>
                </option>

            <?php } ?>
        </select>

        <?php if (!empty($errors["category_id"])) { ?>
            <div class="error-text">
                <?php echo htmlspecialchars($errors["category_id"]); ?>
            </div>
        <?php } ?>

        <label>Availability</label>
        <select name="is_available">
            <option value="1" <?php if ($is_available == 1) echo "selected"; ?>>
                In Stock
            </option>

            <option value="0" <?php if ($is_available == 0) echo "selected"; ?>>
                Out of Stock
            </option>
        </select>

        <label>Primary Image</label>
        <input type="file" name="primary_image">

        <?php if (!empty($errors["image"])) { ?>
            <div class="error-text">
                <?php echo htmlspecialchars($errors["image"]); ?>
            </div>
        <?php } ?>

        <?php if ($id !== "" && !empty($primary_image_path)) { ?>
            <div class="note">
                Current image:
            </div>

            <img class="current-image"
                 src="<?= e(product_image_url($primary_image_path)) ?>"
                 alt="Current Product Image">

            <div class="note">
                Choose a new image only if you want to replace the current image.
            </div>
        <?php } else { ?>
            <div class="note">
                Upload JPEG or PNG image. Maximum size: 3 MB.
            </div>
        <?php } ?>

        <div class="button-row">
            <button type="submit" class="btn btn-save">Save Product</button>

            <a href="<?= url('/admin/products') ?>" class="btn btn-back">Back</a>
        </div>

    </form>

</div>

</div>
<?php include __DIR__ . "/footer.php"; ?>
