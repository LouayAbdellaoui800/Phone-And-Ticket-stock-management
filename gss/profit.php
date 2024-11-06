<?php
session_start();
include 'db.php'; // Include your database connection

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Fetch total revenue from sales
$sqlRevenue = "
    SELECT SUM(price) AS total_revenue 
    FROM sales
";
$resultRevenue = $conn->query($sqlRevenue);
$totalRevenue = $resultRevenue->fetch_assoc()['total_revenue'] ?? 0;

// Fetch total cost from the items sold (Assuming you have a column for cost in either ticket or carte tables)
$sqlCost = "
    SELECT 
        SUM(CASE WHEN item_type = 'ticket' THEN (SELECT price FROM ticket WHERE type = sales.carte_type) * quantity 
                 WHEN item_type = 'carte' THEN (SELECT price FROM carte WHERE type = sales.carte_type) * quantity 
            END) AS total_cost 
    FROM 
        sales
";
$resultCost = $conn->query($sqlCost);
$totalCost = $resultCost->fetch_assoc()['total_cost'] ?? 0;

// Calculate profit
$totalProfit = $totalRevenue - $totalCost;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profit Report</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>

<div class="container mt-5">
    <h1 class="text-center">Profit Report</h1>
    
    <a href="dashboard.php" class="btn btn-secondary mb-4">Back to Dashboard</a> <!-- Back to Dashboard Button -->
    
    <div class="card">
        <div class="card-header">Profit Details</div>
        <div class="card-body text-center">
            <h3>Total Revenue: TND <?= number_format($totalRevenue); ?></h3>
            <h3>Total Cost: TND <?= number_format($totalCost); ?></h3>
            <h2 class="text-success">Total Profit: TND <?= number_format($totalProfit); ?></h2>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
