<?php
// Include your database logic file
require 'db.php'; // Ensure this connects using MySQLi
require 'reports.php'; // Include the logic to fetch reports
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Reports</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>

<div class="container mt-5">
    <a href="dashboard.php" class="btn btn-secondary mb-4">Back to Dashboard</a> 

    <h1 class="text-center">Client Reports</h1>
    
    <!-- Search Form -->
    <form method="GET" action="view_report.php" class="mb-4">
        <div class="form-row">
            <div class="col-md-10">
                <input type="text" name="search" class="form-control" placeholder="Search by Client Name" value="<?= htmlspecialchars($searchQuery); ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-block">Search</button>
            </div>
        </div>
    </form>

    <!-- Reports Table -->
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>Client</th>
                <th>Total Sales (TND)</th>
                <th>Total Debts (TND)</th>
                <th>Outstanding Amount (TND)</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($reports) > 0): ?>
                <?php foreach ($reports as $report) : ?>
                    <tr>
                        <td><?= htmlspecialchars($report['client_name']); ?></td>
                        <td><?= htmlspecialchars(number_format($report['total_sales'], 2)); ?></td>
                        <td><?= htmlspecialchars(number_format($report['total_debts'], 2)); ?></td>
                        <td><?= htmlspecialchars(number_format($report['outstanding_amount'], 2)); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="text-center">No reports found for the search term: <?= htmlspecialchars($searchQuery); ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
