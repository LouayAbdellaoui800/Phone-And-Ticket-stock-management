<?php
include('db.php');
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $model = $_POST['model'];
    $brand = $_POST['brand'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];

    // Handle image upload
    $target_dir = "assets/images/";
    $target_file = $target_dir . basename($_FILES["image"]["name"]);
    
    // Check if the image was uploaded successfully
    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        // Insert product into the database
        $stmt = $conn->prepare("INSERT INTO stock (model, brand, price, quantity, image_path) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdis", $model, $brand, $price, $quantity, $target_file);
        $stmt->execute();

        $_SESSION['message'] = "Product added successfully!";
    } else {
        $_SESSION['message'] = "Error uploading image.";
    }

    // Redirect to avoid resubmission
    header("Location: product.php");
    exit();
}

// Initialize the search variable
$search = isset($_GET['search']) ? $_GET['search'] : '';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }
        .container {
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
        .alert {
            margin-top: 20px;
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
        .table th, .table td {
            vertical-align: middle;
        }
    </style>
</head>
<body>

<div class="container">
    <h1 class="text-center">Product Management</h1>
    <a href="dashboard.php" class="btn btn-secondary mb-4">Back to Dashboard</a>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-info">
            <?= $_SESSION['message']; unset($_SESSION['message']); ?>
        </div>
    <?php endif; ?>

    <!-- Form to add new products -->
    <form action="product.php" method="POST" enctype="multipart/form-data" class="mt-4">
        <div class="form-group">
            <label for="model">Model</label>
            <input type="text" class="form-control" id="model" name="model" required>
        </div>
        <div class="form-group">
            <label for="brand">Brand</label>
            <input type="text" class="form-control" id="brand" name="brand" required>
        </div>
        <div class="form-group">
            <label for="price">Price (TND)</label>
            <input type="number" class="form-control" id="price" name="price" required step="0.01">
        </div>
        <div class="form-group">
            <label for="quantity">Quantity</label>
            <input type="number" class="form-control" id="quantity" name="quantity" required>
        </div>
        <div class="form-group">
            <label for="image">Product Image</label>
            <input type="file" class="form-control-file" id="image" name="image" accept="image/*" >
        </div>
        <button type="submit" class="btn btn-primary">Add Product</button>
    </form>

    <hr>

    <!-- Search Form -->
    <form method="GET" class="mb-3">
        <div class="form-row">
            <div class="col-md-8">
                <input type="text" class="form-control" name="search" placeholder="Search by model or brand" value="<?= htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary btn-block">Search</button>
            </div>
        </div>
    </form>

    <!-- Table to list products -->
    <h2 class="mt-4">Existing Products</h2>
    <table class="table table-bordered table-hover mt-3">
        <thead class="thead-dark">
            <tr>
                <th>ID</th>
                <th>Model</th>
                <th>Brand</th>
                <th>Price (TND)</th>
                <th>Quantity</th>
                <th>Image</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Pagination setup
            $search = '%' . $search . '%';
            $limit = 10;
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $offset = ($page - 1) * $limit;

            // Prepare and execute the main product query
            $query = "SELECT * FROM stock WHERE model LIKE ? OR brand LIKE ? LIMIT ? OFFSET ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssii", $search, $search, $limit, $offset);
            $stmt->execute();
            $result = $stmt->get_result();

            // Fetch products as an associative array
            $products = [];
            while ($product = $result->fetch_assoc()) {
                $products[] = $product;
            }

            // Get total product count for pagination
            $countQuery = "SELECT COUNT(*) as total FROM stock WHERE model LIKE ? OR brand LIKE ?";
            $countStmt = $conn->prepare($countQuery);
            $countStmt->bind_param("ss", $search, $search);
            $countStmt->execute();
            $countResult = $countStmt->get_result();
            $totalProducts = $countResult->fetch_assoc()['total'];
            $totalPages = ceil($totalProducts / $limit);

            foreach ($products as $product): ?>
                <tr>
                    <td><?= $product['id'] ?></td>
                    <td><?= htmlspecialchars($product['model']) ?></td>
                    <td><?= htmlspecialchars($product['brand']) ?></td>
                    <td><?= number_format($product['price'], 2) ?></td>
                    <td><?= $product['quantity'] ?></td>
                    <td><img src="<?= $product['image_path'] ?>" alt="<?= htmlspecialchars($product['model']) ?>" style="width: 100px;"></td>
                    <td>
                        <a href="edit_product.php?id=<?= $product['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                        <a href="#" class="btn btn-danger btn-sm" onclick="confirmDelete(<?= $product['id'] ?>)">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <nav aria-label="Page navigation">
        <ul class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= ($i == $page) ? 'active' : ''; ?>">
                    <a class="page-link" href="product.php?page=<?= $i; ?>&search=<?= htmlspecialchars($search); ?>"><?= $i; ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
</div>

<script>
function confirmDelete(id) {
    if (confirm("Are you sure you want to delete this product?")) {
        window.location.href = 'delete_product.php?id=' + id;
    }
}
</script>
</body>
</html>
