<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

if (!isset($_GET['id'])) {
    die("Invalid request.");
}

$sale_id = (int)$_GET['id'];

// Fetch the sale record to populate the form fields
$stmt = $conn->prepare("SELECT * FROM CartAssignments WHERE id = ?");
$stmt->bind_param("i", $sale_id);
$stmt->execute();
$sale = $stmt->get_result()->fetch_assoc();

if (!$sale) {
    die("Sale not found.");
}

$error = "";
$success = "";

// Handle form submission for updating the sale record
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quantity = (int)$_POST['quantity'];
    $credit = (float)$_POST['credit'];

    if ($quantity <= 0) {
        $error = 'Quantity must be greater than zero.';
    } elseif ($credit < 0) {
        $error = 'Crédit must be zero or more.';
    } else {
        $stmt = $conn->prepare("UPDATE CartAssignments SET quantity = ?, credit = ? WHERE id = ?");
        $stmt->bind_param("idi", $quantity, $credit, $sale_id);
        
        if ($stmt->execute()) {
            $success = "Sale updated successfully!";
        } else {
            $error = "Error updating sale.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modify Sale</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h1 class="text-center">Modify Sale</h1>
    <a href="sales.php" class="btn btn-secondary mb-4">Back to Sales</a>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error); ?></div>
    <?php elseif ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="quantity">Quantity</label>
            <input type="number" name="quantity" class="form-control" value="<?= htmlspecialchars($sale['quantity']); ?>" required min="1">
        </div>

        <div class="form-group">
            <label for="credit">Crédit (TND)</label>
            <input type="number" name="credit" class="form-control" value="<?= htmlspecialchars($sale['credit']); ?>" required min="0" step="0.01">
        </div>

        <button type="submit" class="btn btn-primary">Save Changes</button>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
