<?php
session_start();
include 'db.php'; // Database connection file

// Initialize pagination variables
$limit = 10; // Number of records per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Fetch assignments along with client and phone details with pagination
$query = "
    SELECT a.id, c.name AS client_name, p.brand, p.model, a.down_payment, 
           a.monthly_installment, a.outstanding_balance, a.assign_date
    FROM phone_assignments AS a
    JOIN clients AS c ON a.client_id = c.id
    JOIN stock AS p ON a.phone_id = p.id
    ORDER BY a.assign_date DESC
    LIMIT ? OFFSET ?
";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$assignments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get total count of assignments for pagination
$totalQuery = "SELECT COUNT(*) AS total FROM phone_assignments";
$totalResult = $conn->query($totalQuery);
$totalAssignments = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($totalAssignments / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assignments Report</title>
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
        h2 {
            color: #343a40;
        }
        .alert {
            margin-top: 20px;
        }
        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
            border-color: #545b62;
        }
        .table th, .table td {
            vertical-align: middle;
        }
    </style>
</head>
<body>
<div class="container">
    <a href="dashboard.php" class="btn btn-secondary mb-4">Back to Dashboard</a>

    <h2>Assignments Report</h2>

    <?php if (empty($assignments)): ?>
        <div class="alert alert-warning">No assignments found.</div>
    <?php else: ?>
        <table class="table table-bordered mt-4">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Client</th>
                    <th>Phone Brand & Model</th>
                    <th>Acompte (TND)</th>
                    <th>Versement mensuel (TND)</th>
                    <th>Solde impayé (TND)</th>
                    <th>Date d'affectation</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($assignments as $assignment): ?>
                    <tr>
                        <td><?= htmlspecialchars($assignment['id']); ?></td>
                        <td><?= htmlspecialchars($assignment['client_name']); ?></td>
                        <td><?= htmlspecialchars($assignment['brand'] . " " . $assignment['model']); ?></td>
                        <td><?= htmlspecialchars(number_format($assignment['down_payment'], 2)); ?></td>
                        <td><?= htmlspecialchars(number_format($assignment['monthly_installment'], 2)); ?></td>
                        <td><?= htmlspecialchars(number_format($assignment['outstanding_balance'], 2)); ?></td>
                        <td><?= htmlspecialchars(date("Y-m-d", strtotime($assignment['assign_date']))); ?></td>
                        <td>
                            <?php if ($assignment['outstanding_balance'] > 0): ?>
                                <a href="mark_as_paid.php?id=<?= htmlspecialchars($assignment['id']); ?>" class="btn btn-success btn-sm">Mark as Paid</a>
                            <?php else: ?>
                                <span class="text-success">Paid</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= ($i == $page) ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?= $i; ?>"><?= $i; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>
</body>
</html>
