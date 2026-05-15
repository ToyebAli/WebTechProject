<?php

session_start();

require_once __DIR__ . "/../Model/CategoryModel.php";

$categoryModel = new CategoryModel();

$id = "";
$name = "";
$parent_id = "";
$pageTitle = "Add New Category";

$errors = [
    "name" => "",
    "parent_id" => "",
    "general" => ""
];

$oldInput = [];

if (isset($_SESSION["category_old"])) {
    $oldInput = $_SESSION["category_old"];
    unset($_SESSION["category_old"]);
}

if (isset($_SESSION["category_errors"])) {
    foreach ($_SESSION["category_errors"] as $key => $value) {
        if (array_key_exists($key, $errors)) {
            $errors[$key] = $value;
        }
    }
    unset($_SESSION["category_errors"]);
}

if (isset($_GET["id"])) {
    $id = intval($_GET["id"]);
    $category = $categoryModel->getCategoryById($id);

    if ($category) {
        $name = $category["name"];
        $parent_id = $category["parent_id"];
        $pageTitle = "Edit Category";
    } else {
        header("Location: categoryList.php?error=Category not found");
        exit;
    }
}

if (isset($_GET["error_name"])) {
    $errors["name"] = $_GET["error_name"];
}

if (isset($_GET["error"])) {
    $errors["general"] = $_GET["error"];
}

if (!empty($oldInput)) {
    if (isset($oldInput["id"])) {
        $id = $oldInput["id"];
    }

    if (isset($oldInput["name"])) {
        $name = $oldInput["name"];
    }

    if (isset($oldInput["parent_id"])) {
        $parent_id = $oldInput["parent_id"];
    }
}

$parentCategories = $categoryModel->getParentCategories();

?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($pageTitle); ?></title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f8;
            margin: 0;
            padding: 20px;
        }

        .container {
            width: 600px;
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
        select {
            width: 100%;
            padding: 9px;
            margin-top: 5px;
            box-sizing: border-box;
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
    </style>
</head>
<body>

<div class="container">

    <h2><?php echo htmlspecialchars($pageTitle); ?></h2>

    <?php if (!empty($errors["general"])) { ?>
        <div class="general-error">
            <?php echo htmlspecialchars($errors["general"]); ?>
        </div>
    <?php } ?>

    <form method="post" action="../Controller/categorySave.php">

        <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">

        <label>Category Name</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($name); ?>">

        <?php if (!empty($errors["name"])) { ?>
            <div class="error-text">
                <?php echo htmlspecialchars($errors["name"]); ?>
            </div>
        <?php } ?>

        <label>Parent Category</label>
        <select name="parent_id">
            <option value="">No Parent Category</option>

            <?php foreach ($parentCategories as $parentCategory) { ?>

                <?php
                if ($id != "" && $parentCategory["id"] == $id) {
                    continue;
                }
                ?>

                <option value="<?php echo htmlspecialchars($parentCategory["id"]); ?>"
                    <?php
                    if ($parent_id == $parentCategory["id"]) {
                        echo "selected";
                    }
                    ?>
                >
                    <?php echo htmlspecialchars($parentCategory["name"]); ?>
                </option>

            <?php } ?>
        </select>

        <?php if (!empty($errors["parent_id"])) { ?>
            <div class="error-text">
                <?php echo htmlspecialchars($errors["parent_id"]); ?>
            </div>
        <?php } ?>

        <div class="note">
            Leave parent category empty if this is a main category.
        </div>

        <div class="button-row">
            <button type="submit" class="btn btn-save">Save Category</button>

            <a href="categoryList.php" class="btn btn-back">Back</a>
        </div>

    </form>

</div>

</body>
</html>
