<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Fetch current stock for both ticket and carte
$stmt_ticket = $conn->query("SELECT * FROM ticket");
$ticket_stock = $stmt_ticket->fetch_all(MYSQLI_ASSOC);

$stmt_carte = $conn->query("SELECT * FROM carte");
$carte_stock = $stmt_carte->fetch_all(MYSQLI_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $item_type = $_POST['item_type']; // 'ticket' or 'carte'
    $carte_type = $_POST['carte_type'];
    $quantity = (int)$_POST['quantity'];
    $price_per_item = (float)$_POST['price_per_item']; // Manually entered price per item
    $action = $_POST['action'];

    // Validate inputs
    if ($quantity <= 0 || $price_per_item <= 0) {
        echo "<script>alert('Quantity and Price must be greater than zero.');</script>";
    } else {
        try {
            $table_name = ($item_type === 'ticket') ? 'ticket' : 'carte';

            // Prepare SQL statement based on action
            if ($action === 'add') {
                $stmt = $conn->prepare("SELECT quantity, price FROM $table_name WHERE type = ?");
                $stmt->bind_param("s", $carte_type);
                $stmt->execute();
                $stmt->bind_result($existing_quantity, $existing_price);
                $stmt->fetch();
                $stmt->close();

                if ($existing_quantity !== null) {
                    $new_quantity = $existing_quantity + $quantity;
                    $new_total_price = $existing_price + ($price_per_item * $quantity);

                    $stmt = $conn->prepare("UPDATE $table_name SET quantity = ?, price = ? WHERE type = ?");
                    $stmt->bind_param("iis", $new_quantity, $new_total_price, $carte_type);
                } else {
					$total_price = $price_per_item * $quantity;
                    $stmt = $conn->prepare("INSERT INTO $table_name (type, quantity, price) VALUES (?, ?, ?)");
                    $stmt->bind_param("sii", $carte_type, $quantity, $total_price);
                }

                if (!$stmt->execute()) {
                    echo "<script>alert('Error: " . htmlspecialchars($stmt->error) . "');</script>";
                } else {
                    echo "<script>alert('Stock added successfully!');</script>";
                }
                $stmt->close();
            }

            header('Location: stock.php');
            exit();
        } catch (mysqli_sql_exception $e) {
            echo "<script>alert('Error updating stock: " . htmlspecialchars($e->getMessage()) . "');</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Stock</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f4f7fa;
            font-family: Arial, sans-serif;
        }
        .container {
            margin-top: 30px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        h1, h2 {
            color: #343a40;
        }
        h1 {
            font-size: 2rem;
            margin-bottom: 20px;
        }
        .table {
            margin-top: 20px;
        }
        .thead-dark th {
            background-color: #343a40;
            color: white;
        }
        .btn-success {
            background-color: #28a745;
            border: none;
        }
        .btn-success:hover {
            background-color: #218838;
        }
        .image-cell img {
            width: 40px;
            height: 40px;
            margin-left: 10px;
            border-radius: 5px;
        }
        .no-image {
            color: #6c757d;
            font-style: italic;
        }
        .alert {
            margin-top: 20px;
        }
        .form-control {
            border-radius: 5px;
        }
        .btn-secondary {
            border-radius: 5px;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h1 class="text-center">Manage Stock</h1>
    <a href="dashboard.php" class="btn btn-secondary mb-4">Back to Dashboard</a>

    <form method="POST" action="" class="mt-4 mb-4">
        <div class="form-row align-items-end">
            <div class="col-md-2 mb-3">
                <label for="item_type">Select Type</label>
                <select name="item_type" class="form-control" required>
                    <option value="ticket">Ticket</option>
                    <option value="carte">Carte</option>
                </select>
            </div>
            <div class="col-md-2 mb-3">
                <label for="carte_type">Carte/Ticket Type</label>
                <select name="carte_type" class="form-control" required>
                    <option value="telecom">Telecom</option>
                    <option value="ooredoo">Ooredoo</option>
                    <option value="orange">Orange</option>
                </select>
            </div>
            <div class="col-md-2 mb-3">
                <label for="quantity">Quantity</label>
                <input type="number" name="quantity" class="form-control" placeholder="Quantity" required min="1">
            </div>
            <div class="col-md-2 mb-3">
                <label for="price_per_item">Price Per Item</label>
                <input type="number" step="0.01" name="price_per_item" class="form-control" placeholder="Price per item" required min="0">
            </div>
            <div class="col-md-4 mb-3">
                <button type="submit" name="action" value="add" class="btn btn-success btn-block">Add Stock</button>
            </div>
        </div>
    </form>

    <?php if (isset($message)): ?>
        <div class="alert alert-info"><?= htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <h2 class="mt-5">Current Stock (Ticket)</h2>
    <table class="table table-bordered">
        <thead class="thead-dark">
            <tr>
                <th>Type</th>
                <th>Quantity</th>
                <th>Total Price</th>
                <th>Image</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($ticket_stock as $item) : ?>
                <tr>
                    <td><?= htmlspecialchars($item['type']); ?></td>
                    <td><?= htmlspecialchars($item['quantity']); ?></td>
                    <td><?= htmlspecialchars($item['price'].' TND'); ?></td>
                    <td class="image-cell">
                        <?php
                        // Determine the image based on carte_type
                        $image = '';
                        switch ($item['type']) {
                            case 'telecom':
                                $image = 'tt.png';
                                break;
                            case 'ooredoo':
                                $image = 'Ooredoo.png';
                                break;
                            case 'orange':
                                $image = 'Orange.png';
                                break;
                        }

                        // Display the image if it exists
                        if ($image && file_exists("assets/images/$image")) {
                            echo '<img src="assets/images/' . $image . '" alt="' . htmlspecialchars($item['type']) . '">';
                        } else {
                            echo '<span class="no-image">No Image</span>';
                        }
                        ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2 class="mt-5">Current Stock (Carte)</h2>
    <table class="table table-bordered">
        <thead class="thead-dark">
            <tr>
                <th>Type</th>
                <th>Quantity</th>
                <th>Total Price</th>
                <th>Image</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($carte_stock as $item) : ?>
                <tr>
                    <td><?= htmlspecialchars($item['type']); ?></td>
                    <td><?= htmlspecialchars($item['quantity']); ?></td>
                    <td><?= htmlspecialchars($item['price'].' TND'); ?></td>
                    <td class="image-cell">
                        <?php
                        // Determine the image based on carte_type
                        $image = '';
                        switch ($item['type']) {
                            case 'telecom':
                                $image = 'tt.png';
                                break;
                            case 'ooredoo':
                                $image = 'Ooredoo.png';
                                break;
                            case 'orange':
                                $image = 'Orange.png';
                                break;
                        }

                        // Display the image if it exists
                        if ($image && file_exists("assets/images/$image")) {
                            echo '<img src="assets/images/' . $image . '" alt="' . htmlspecialchars($item['type']) . '">';
                        } else {
                            echo '<span class="no-image">No Image</span>';
                        }
                        ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
