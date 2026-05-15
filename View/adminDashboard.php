<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>

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
            padding: 25px;
            border: 1px solid #ddd;
        }

        h2 {
            text-align: center;
            color: #333;
        }

        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
        }

        .card-row {
            display: flex;
            gap: 20px;
            justify-content: center;
        }

        .card {
            width: 320px;
            border: 1px solid #ccc;
            padding: 20px;
            text-align: center;
            background-color: #fafafa;
        }

        .card h3 {
            color: #222;
            margin-top: 0;
        }

       

        .btn {
            display: inline-block;
            padding: 10px 14px;
            background-color: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 10px;
        }

        .btn-secondary {
            background-color: #007bff;
        }

       
    </style>
</head>
<body>

<div class="container">

    <h2>Admin Dashboard</h2>

    <p class="subtitle">
        Product & Category Management Panel
    </p>

    <div class="card-row">

        <div class="card">
            <h3>Category Management</h3>

           

            <a class="btn" href="categoryList.php">
                Manage Categories
            </a>
        </div>

        <div class="card">
            <h3>Product Management</h3>

            

            <a class="btn btn-secondary" href="productList.php">
                Manage Products
            </a>
        </div>

    </div>



</div>

</body>
</html>
