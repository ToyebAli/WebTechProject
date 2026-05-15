<?php

require_once __DIR__ . "/../Model/ProductModel.php";

$productModel = new ProductModel();
$products = $productModel->getAllProducts();

?>

<!DOCTYPE html>
<html>
<head>
    <title>Product List</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f8;
            margin: 0;
            padding: 20px;
        }

        .container {
            width: 1100px;
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
            display: inline-block;
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
            padding: 9px;
            text-align: center;
            vertical-align: middle;
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

        .product-img {
            width: 70px;
            height: 60px;
            object-fit: cover;
            border: 1px solid #ccc;
        }

        .no-image {
            color: #777;
            font-size: 13px;
        }

        .low-stock {
            background-color: #fff3cd;
        }

        .badge {
            padding: 6px 10px;
            border-radius: 4px;
            color: white;
            font-size: 13px;
            border: none;
            cursor: pointer;
        }

        .badge-in {
            background-color: #28a745;
        }

        .badge-out {
            background-color: #6c757d;
        }

        .rating {
            color: #444;
            font-weight: bold;
        }

        .small-text {
            font-size: 13px;
            color: #666;
        }
    </style>
</head>
<body>

<div class="container">

    <h2>Product Management</h2>

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
    <a class="btn btn-add" href="productForm.php">Add New Product</a>
    <a class="btn btn-add" href="categoryList.php">Manage Categories</a>
</div>

    <?php if (empty($products)) { ?>

        <div class="empty">
            No products found yet.
        </div>

    <?php } else { ?>

        <table>
            <tr>
                <th>ID</th>
                <th>Image</th>
                <th>Product Name</th>
                <th>Category</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Average Rating</th>
                <th>Availability</th>
                <th>Action</th>
            </tr>

            <?php foreach ($products as $product) { ?>

                <?php
                $rowClass = "";

                if ($product["stock_qty"] <= 5) {
                    $rowClass = "low-stock";
                }
                ?>

                <tr class="<?php echo $rowClass; ?>">
                    <td><?php echo htmlspecialchars($product["id"]); ?></td>

                    <td>
                        <?php if (!empty($product["primary_image_path"])) { ?>
                            <img class="product-img"
                                 src="../<?php echo htmlspecialchars($product["primary_image_path"]); ?>"
                                 alt="Product Image">
                        <?php } else { ?>
                            <span class="no-image">No image</span>
                        <?php } ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($product["name"]); ?>
                    </td>

                    <td>
                        <?php
                        if ($product["category_name"] == null) {
                            echo "No Category";
                        } else {
                            echo htmlspecialchars($product["category_name"]);
                        }
                        ?>
                    </td>

                    <td>
                        <?php echo number_format($product["price"], 2); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($product["stock_qty"]); ?>

                        <?php if ($product["stock_qty"] <= 5) { ?>
                            <br>
                            <span class="small-text">Low stock</span>
                        <?php } ?>
                    </td>

                    <td>
                        <?php
                        if ($product["avg_rating"] == null) {
                            echo "No rating";
                        } else {
                            echo "<span class='rating'>" . number_format($product["avg_rating"], 1) . " / 5</span>";
                        }
                        ?>
                    </td>

                    <td>
                        <?php if ($product["is_available"] == 1) { ?>
                            <button class="badge badge-in"
                                    id="availability-<?php echo $product["id"]; ?>"
                                    onclick="toggleAvailability(<?php echo $product["id"]; ?>)">
                                In Stock
                            </button>
                        <?php } else { ?>
                            <button class="badge badge-out"
                                    id="availability-<?php echo $product["id"]; ?>"
                                    onclick="toggleAvailability(<?php echo $product["id"]; ?>)">
                                Out of Stock
                            </button>
                        <?php } ?>
                    </td>

                    <td>
                        <a class="btn btn-edit" href="productForm.php?id=<?php echo $product["id"]; ?>">
                            Edit
                        </a>
                        <a class="btn btn-delete"
                           href="../Controller/productDelete.php?id=<?php echo $product["id"]; ?>"
                           onclick="return confirm('Are you sure you want to delete this product?');">
                            Delete
                        </a>
                    </td>
                </tr>

            <?php } ?>

        </table>

    <?php } ?>

</div>

<script>
function toggleAvailability(productId) {
    var button = document.getElementById("availability-" + productId);

    var xhr = new XMLHttpRequest();

    xhr.open("PATCH", "../Controller/productAvailabilityToggle.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    xhr.onload = function () {
        if (xhr.status === 200) {
            try {
                var response = JSON.parse(xhr.responseText);

                if (response.ok === true) {
                    if (response.is_available == 1) {
                        button.innerHTML = "In Stock";
                        button.className = "badge badge-in";
                    } else {
                        button.innerHTML = "Out of Stock";
                        button.className = "badge badge-out";
                    }
                } else {
                    alert(response.message);
                }
            } catch (e) {
                alert("Invalid server response");
            }
        } else {
            alert("AJAX request failed");
        }
    };

    xhr.send("id=" + encodeURIComponent(productId));
}
</script>

</body>
</html>