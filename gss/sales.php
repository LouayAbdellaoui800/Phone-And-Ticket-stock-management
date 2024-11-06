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

// Fetch sales data (cart assignments), filtering by client name if a search query exists
$sql = "
    SELECT 
        sales.*, 
        Clients.name AS client_name 
    FROM 
        sales 
    JOIN 
        Clients 
    ON 
        sales.client_id = Clients.id 
";

if ($searchQuery) {
    $sql .= " WHERE Clients.name LIKE ?";
}

$sql .= " ORDER BY assignment_date DESC"; // Ensure we always order the results

$stmt = $conn->prepare($sql);

if ($searchQuery) {
    $searchTerm = '%' . $searchQuery . '%';
    $stmt->bind_param("s", $searchTerm);
}

$stmt->execute();
$sales = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales</title>
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
        .badge {
            font-size: 1rem;
            padding: 0.5em 1em;
        }
        .badge-telecom { background-color: #007bff; } /* Blue */
        .badge-ooredoo { background-color: #dc3545; } /* Red */
        .badge-orange { background-color: #fd7e14; } /* Orange */
        .btn-warning {
            background-color: #ffc107;
            border: none;
        }
        .btn-warning:hover {
            background-color: #e0a800;
        }
        .search-input {
            border-radius: 25px;
        }
        .search-button {
            border-radius: 25px;
            width: 100%;
        }
        .no-sales-message {
            font-weight: bold;
            color: #6c757d;
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <a href="dashboard.php" class="btn btn-secondary mb-4">Back to Dashboard</a>
    <div class="card">
        <div class="card-header">Sales</div>
        <div class="card-body">
            <!-- Search Form -->
            <form method="GET" action="sales.php" class="mb-4">
                <div class="form-row">
                    <div class="col-md-10">
                        <input type="text" name="search" class="form-control search-input" placeholder="Search by Client Name" value="<?= htmlspecialchars($searchQuery); ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary search-button">Search</button>
                    </div>
                </div>
            </form>

            <!-- Sales Table -->
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Client</th>
                        <th>Carte/Ticket Subtype</th>
                        <th>Carte/Ticket Type</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Crédit</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($sales) > 0): ?>
                        <?php foreach ($sales as $sale) : ?>
                            <tr>
                                <td><?= htmlspecialchars($sale['client_name']); ?></td>
                                <td><?= htmlspecialchars($sale['carte_type']); ?></td>
                                <td>
                                    <?php if ($sale['item_type'] === 'telecom') : ?>
                                        <span class="badge badge-telecom">Telecom</span>
                                    <?php elseif ($sale['item_type'] === 'ooredoo') : ?>
                                        <span class="badge badge-ooredoo">Ooredoo</span>
                                    <?php elseif ($sale['item_type'] === 'orange') : ?>
                                        <span class="badge badge-orange">Orange</span>
                                    <?php else : ?>
                                        <?= htmlspecialchars($sale['item_type']); ?>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($sale['quantity']); ?></td>
                                <td><?= htmlspecialchars($sale['price']);?></td>
                                <td><?= htmlspecialchars($sale['credit']); ?></td>
                                <td><?= htmlspecialchars($sale['assignment_date']); ?></td>
                                <td>
                                    <a href="modify_sale.php?id=<?= $sale['id']; ?>" class="btn btn-warning btn-sm">Modify</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center no-sales-message">No sales found for the search term: <?= htmlspecialchars($searchQuery); ?></td>
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
