<?php
session_start();
include 'db.php'; // Include your database connection

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Search functionality
$searchQuery = '';
if (isset($_GET['search'])) {
    $searchQuery = trim($_GET['search']);
}

// Fetch debt data, filtering by client name if a search query exists
$sql = "
    SELECT 
        Debts.*, 
        Clients.name AS client_name 
    FROM 
        Debts 
    JOIN 
        Clients 
    ON 
        Debts.client_id = Clients.id 
";

if ($searchQuery) {
    $sql .= " WHERE Clients.name LIKE ?";
}

$sql .= " ORDER BY due_date ASC"; // Ensure we always order the results

$stmt = $conn->prepare($sql);

if ($searchQuery) {
    $searchTerm = '%' . $searchQuery . '%';
    $stmt->bind_param("s", $searchTerm);
}

$stmt->execute();
$debts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debts</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container {
            max-width: 1200px;
        }
        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .card-header {
            background-color: #007bff;
            color: white;
            text-align: center;
            font-size: 1.5rem;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
        }
        .table th {
            background-color: #343a40;
            color: white;
            text-align: center;
        }
        .table td {
            text-align: center;
        }
        .btn-warning {
            background-color: #ffc107;
            border: none;
        }
        .btn-warning:hover {
            background-color: #e0a800;
        }
        .no-debts-message {
            font-weight: bold;
            color: #6c757d;
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <a href="dashboard.php" class="btn btn-secondary mb-4">Back to Dashboard</a>
    
    <h1 class="text-center">Client Debts</h1>
    
    <div class="card">
        <div class="card-header">Debts Overview</div>
        <div class="card-body">
            <!-- Search Form -->
            <form method="GET" action="debt.php" class="mb-4">
                <div class="form-row">
                    <div class="col-md-10">
                        <input type="text" name="search" class="form-control" placeholder="Search by Client Name" value="<?= htmlspecialchars($searchQuery); ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-block">Search</button>
                    </div>
                </div>
            </form>

            <!-- Debts Table -->
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Client</th>
                        <th>Amount Due</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($debts) > 0): ?>
                        <?php foreach ($debts as $debt) : 
                            // Set row color based on debt status
                            $rowClass = '';
                            if ($debt['status'] == 'paid') {
                                $rowClass = 'table-success';
                            } elseif ($debt['status'] == 'pending') {
                                $rowClass = 'table-warning';
                            } elseif ($debt['status'] == 'overdue') {
                                $rowClass = 'table-danger';
                            }
                        ?>
                            <tr class="<?= $rowClass; ?>">
                                <td><?= htmlspecialchars($debt['client_name']); ?></td>
                                <td><?= htmlspecialchars($debt['amount_due']); ?> TND</td>
                                <td><?= htmlspecialchars($debt['due_date']); ?></td>
                                <td><?= htmlspecialchars($debt['status']); ?></td>
                                <td>
                                    <a href="modify_debt.php?id=<?= $debt['id']; ?>" class="btn btn-warning btn-sm">Modify</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center no-debts-message">No debts found for the search term: <?= htmlspecialchars($searchQuery); ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
