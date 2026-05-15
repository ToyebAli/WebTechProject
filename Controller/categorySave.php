<?php

session_start();

require_once __DIR__ . "/../Model/CategoryModel.php";

$categoryModel = new CategoryModel();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../View/categoryList.php");
    exit;
}

$id = "";
$name = "";
$parent_id = "";

if (isset($_POST["id"])) {
    $id = trim($_POST["id"]);
}

if (isset($_POST["name"])) {
    $name = trim($_POST["name"]);
}

if (isset($_POST["parent_id"])) {
    $parent_id = trim($_POST["parent_id"]);
}

function redirectCategoryFormWithErrors($id, $oldInput, $errors)
{
    $_SESSION["category_old"] = $oldInput;
    $_SESSION["category_errors"] = $errors;

    if ($id !== "") {
        header("Location: ../View/categoryForm.php?id=" . urlencode($id));
    } else {
        header("Location: ../View/categoryForm.php");
    }

    exit;
}

$oldInput = [
    "id" => $id,
    "name" => $name,
    "parent_id" => $parent_id
];

$errors = [];
$parent_id_for_save = null;

if ($name === "") {
    $errors["name"] = "Category name is required";
}

if ($parent_id === "") {
    $parent_id_for_save = null;
} else if (!ctype_digit($parent_id)) {
    $errors["parent_id"] = "Parent category must be valid";
} else {
    $parent_id_for_save = intval($parent_id);

    if ($id !== "" && intval($id) === $parent_id_for_save) {
        $errors["parent_id"] = "A category cannot be its own parent";
    } else {
        $parentCategory = $categoryModel->getCategoryById($parent_id_for_save);

        if (!$parentCategory) {
            $errors["parent_id"] = "Selected parent category does not exist";
        } else if ($parentCategory["parent_id"] !== null) {
            $errors["parent_id"] = "Parent category must be a main category";
        }
    }

    if ($id !== "" && !isset($errors["parent_id"])) {
        $childCount = $categoryModel->countChildCategories(intval($id));

        if ($childCount > 0) {
            $errors["parent_id"] = "A category with child categories cannot have a parent category";
        }
    }
}

if (!empty($errors)) {
    redirectCategoryFormWithErrors($id, $oldInput, $errors);
}

if ($id === "") {
    $success = $categoryModel->addCategory($name, $parent_id_for_save);

    if ($success) {
        header("Location: ../View/categoryList.php?success=" . urlencode("Category added successfully"));
        exit;
    } else {
        $errors["general"] = "Failed to add category";
        redirectCategoryFormWithErrors($id, $oldInput, $errors);
    }
} else {
    $id = intval($id);

    $existingCategory = $categoryModel->getCategoryById($id);

    if (!$existingCategory) {
        header("Location: ../View/categoryList.php?error=" . urlencode("Category not found"));
        exit;
    }

    $success = $categoryModel->updateCategory($id, $name, $parent_id_for_save);

    if ($success) {
        header("Location: ../View/categoryList.php?success=" . urlencode("Category updated successfully"));
        exit;
    } else {
        $errors["general"] = "Failed to update category";
        redirectCategoryFormWithErrors($id, $oldInput, $errors);
    }
}

?>
