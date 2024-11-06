<?php
include('db.php');
// Fetch product data based on ID from URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM stock WHERE id = ?");
    $stmt->bind_param("i", $id); // Use bind_param for MySQLi
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
}

// Handle form submission for editing a product
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $model = $_POST['model'];
    $brand = $_POST['brand'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];

    // Handle image upload if a new image is provided
    $target_file = $product['image_path']; // keep existing image path
    if (!empty($_FILES["image"]["name"])) {
        $target_dir = "assets/images/";
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);
    }

    // Update product in the database
    $stmt = $conn->prepare("UPDATE stock SET model = ?, brand = ?, price = ?, quantity = ?, image_path = ? WHERE id = ?");
    $stmt->execute([$model, $brand, $price, $quantity, $target_file, $id]);

    // Redirect to the product management page
    header("Location: product.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }
        .container {
            max-width: 600px;
            margin-top: 50px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }
        h1 {
            color: #343a40;
            margin-bottom: 20px;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }
        .form-control, .form-control-file {
            border-radius: 5px;
        }
        .form-group img {
            display: block;
            max-width: 100px;
            height: auto;
            margin-top: 10px;
        }
    </style>
</head>
<body>

<div class="container">
    <h1 class="text-center">Edit Product</h1>
	    <a href="dashboard.php" class="btn btn-secondary mb-4">Back to Dashboard</a>

    <form action="edit_product.php?id=<?php echo $id; ?>" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="model">Model</label>
            <input type="text" class="form-control" id="model" name="model" value="<?php echo htmlspecialchars($product['model']); ?>" required>
        </div>
        <div class="form-group">
            <label for="brand">Brand</label>
            <input type="text" class="form-control" id="brand" name="brand" value="<?php echo htmlspecialchars($product['brand']); ?>" required>
        </div>
        <div class="form-group">
            <label for="price">Price</label>
            <input type="number" class="form-control" id="price" name="price" value="<?php echo htmlspecialchars($product['price']); ?>" required step="0.01">
        </div>
        <div class="form-group">
            <label for="quantity">Quantity</label>
            <input type="number" class="form-control" id="quantity" name="quantity" value="<?php echo htmlspecialchars($product['quantity']); ?>" required>
        </div>
        <div class="form-group">
            <label for="image">Product Image</label>
            <input type="file" class="form-control-file" id="image" name="image" accept="image/*">
            <img src="<?php echo htmlspecialchars($product['image_path']); ?>" alt="<?php echo htmlspecialchars($product['model']); ?>">
        </div>
        <button type="submit" class="btn btn-primary btn-block">Update Product</button>
    </form>
</div>

</body>
</html>
