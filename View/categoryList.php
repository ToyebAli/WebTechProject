<?php

require_once __DIR__ . "/../Model/CategoryModel.php";

$categoryModel = new CategoryModel();
$categories = $categoryModel->getAllCategories();

?>

<!DOCTYPE html>
<html>
<head>
    <title>Category List</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f8;
            margin: 0;
            padding: 20px;
        }

        .container {
            width: 900px;
            margin: auto;
            background-color: white;
            padding: 20px;
            border: 1px solid #ddd;
        }

        h2 {
            text-align: center;
            color: #333;
        }

        .top-bar {
    margin-bottom: 15px;
    display: flex;
    justify-content: flex-end;
    gap: 8px;
}
.btn-dashboard {
    background-color: #17a2b8;
    color: white;
}
        .btn {
            padding: 8px 12px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
            border: none;
            cursor: pointer;
        }

        .btn-add {
            background-color: #28a745;
            color: white;
        }

        .btn-edit {
            background-color: #007bff;
            color: white;
        }

        .btn-delete {
            background-color: #dc3545;
            color: white;
        }

        .message {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
        }

        table th,
        table td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
        }

        table th {
            background-color: #333;
            color: white;
        }

        .empty {
            text-align: center;
            color: #777;
            padding: 20px;
        }
    </style>
</head>
<body>

<div class="container">

    <h2>Category Management</h2>

    <?php
    if (isset($_GET["success"])) {
        echo "<div class='message success'>" . htmlspecialchars($_GET["success"]) . "</div>";
    }

    if (isset($_GET["error"])) {
        echo "<div class='message error'>" . htmlspecialchars($_GET["error"]) . "</div>";
    }
    ?>

   <div class="top-bar">
    <a class="btn btn-dashboard" href="adminDashboard.php">Dashboard</a>
    <a class="btn btn-add" href="categoryForm.php">Add New Category</a>
</div>

    <?php if (empty($categories)) { ?>

        <div class="empty">
            No categories found yet.
        </div>

    <?php } else { ?>

        <table>
            <tr>
                <th>ID</th>
                <th>Category Name</th>
                <th>Parent Category</th>
                <th>Action</th>
            </tr>

            <?php foreach ($categories as $category) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($category["id"]); ?></td>

                    <td><?php echo htmlspecialchars($category["name"]); ?></td>

                    <td>
                        <?php
                        if ($category["parent_name"] == null) {
                            echo "None";
                        } else {
                            echo htmlspecialchars($category["parent_name"]);
                        }
                        ?>
                    </td>

                    <td>
                        <a class="btn btn-edit" href="categoryForm.php?id=<?php echo $category["id"]; ?>">
                            Edit
                        </a>
                        <a class="btn btn-delete"
                           href="../Controller/categoryDelete.php?id=<?php echo $category["id"]; ?>"
                           onclick="return confirm('Are you sure you want to delete this category?');">
                            Delete
                        </a>

                    </td>
                </tr>
            <?php } ?>

        </table>

    <?php } ?>

</div>

</body>
</html>