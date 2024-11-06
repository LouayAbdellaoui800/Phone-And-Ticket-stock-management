<?php
session_start();
include 'db.php'; // Database connection

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Fetch total quantity and total revenue for sales
$salesSql = "SELECT SUM(quantity) AS total_quantity, SUM(price) AS total_revenue FROM sales";
$salesResult = $conn->query($salesSql);

$totalQuantity = 0;
$totalRevenue = 0;
if ($salesResult && $row = $salesResult->fetch_assoc()) {
    $totalQuantity = $row['total_quantity'];
    $totalRevenue = $row['total_revenue'];
}

// Fetch total debt paid and unpaid per client
$debtSql = "
    SELECT 
        Clients.name AS client_name,
        SUM(CASE WHEN debts.status = 'paid' THEN debts.amount_due ELSE 0 END) AS total_paid,
        SUM(CASE WHEN debts.status = 'pending' THEN debts.amount_due ELSE 0 END) AS total_not_paid
    FROM 
        debts
    JOIN 
        Clients ON debts.client_id = Clients.id
    GROUP BY 
        Clients.id
";
$debtResult = $conn->query($debtSql);

$clientDebts = [];
if ($debtResult) {
    $clientDebts = $debtResult->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprehensive Report</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h1 class="text-center">Comprehensive Report</h1>
    
    <!-- Total Sales Overview -->
    <div class="card mt-4">
        <div class="card-header text-white bg-primary">Total Sales Overview</div>
        <div class="card-body">
            <h5>Total Quantity Sold: <?= htmlspecialchars($totalQuantity); ?></h5>
            <h5>Total Revenue: <?= htmlspecialchars(number_format($totalRevenue, 2)); ?> TND</h5>
        </div>
    </div>
    
    <!-- Debt Summary per Client -->
    <div class="card mt-4">
        <div class="card-header text-white bg-primary">Debt Summary per Client</div>
        <div class="card-body">
            <?php if (count($clientDebts) > 0): ?>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Client Name</th>
                            <th>Total Debt Paid (TND)</th>
                            <th>Total Debt Unpaid (TND)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clientDebts as $debt): ?>
                            <tr>
                                <td><?= htmlspecialchars($debt['client_name']); ?></td>
                                <td><?= htmlspecialchars(number_format($debt['total_paid'], 2)); ?></td>
                                <td><?= htmlspecialchars(number_format($debt['total_not_paid'], 2)); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-center">No debt records found.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <a href="dashboard.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
