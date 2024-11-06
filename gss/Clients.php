<?php
session_start();
include 'db.php'; // Include your database connection

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Fetch current clients
$stmt = $conn->query("SELECT * FROM Clients");
$clients = $stmt->fetch_all(MYSQLI_ASSOC);

// Handle add/update client form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $client_name = $_POST['name'];
    $client_phone = $_POST['phone'];
    $client_id = isset($_POST['client_id']) ? (int)$_POST['client_id'] : null; // ID is null for new clients

    if (empty($client_name) || empty($client_phone)) {
        echo "<script>alert('All fields are required.');</script>";
    } else {
        try {
            if ($client_id) {
                // Update existing client
                $stmt = $conn->prepare("UPDATE Clients SET name = ?, phone = ? WHERE id = ?");
                $stmt->bind_param("ssi", $client_name, $client_phone, $client_id);
            } else {
                // Insert new client
                $stmt = $conn->prepare("INSERT INTO Clients (name, phone) VALUES (?, ?)");
                $stmt->bind_param("ss", $client_name, $client_phone);
            }
            $stmt->execute();
            header('Location: clients.php'); // Redirect to the same page after submission
            exit();
        } catch (mysqli_sql_exception $e) {
            echo "<script>alert('Error: " . htmlspecialchars($e->getMessage()) . "');</script>";
        }
    }
}

// Handle delete client action
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM Clients WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    header('Location: clients.php'); // Redirect after deletion
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Clients</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-top: 50px;
        }
        h1, h2 {
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
        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }
        .btn-danger:hover {
            background-color: #c82333;
            border-color: #bd2130;
        }
        .btn-warning {
            background-color: #ffc107;
            border-color: #ffc107;
        }
        .btn-warning:hover {
            background-color: #e0a800;
            border-color: #d39e00;
        }
        .table {
            margin-top: 20px;
        }
        .thead-dark th {
            background-color: #343a40;
            color: white;
        }
        .table-bordered th, .table-bordered td {
            border: 1px solid #dee2e6;
        }
        .form-control {
            border-radius: 5px;
        }
    </style>
</head>
<body>
<div class="container">
    <h1 class="text-center">Manage Clients</h1>

    <a href="dashboard.php" class="btn btn-secondary mb-4">Back to Dashboard</a>

    <form method="POST" action="" class="mt-4 mb-4">
        <div class="form-row">
            <div class="col-md-4 mb-3">
                <label for="client_name">Client Name</label>
                <input type="text" name="name" class="form-control" placeholder="Client Name" required>
            </div>
            <div class="col-md-4 mb-3">
                <label for="client_phone">Client Phone</label>
                <input type="text" name="phone" class="form-control" placeholder="Client Phone" required>
            </div>
            <div class="col-md-4 mb-3">
                <button type="submit" class="btn btn-primary btn-block">Add/Update Client</button>
            </div>
        </div>
        <input type="hidden" name="client_id" value="">
    </form>

    <h2 class="mt-5">Current Clients</h2>
    <table class="table table-bordered">
        <thead class="thead-dark">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Phone</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($clients as $client) : ?>
                <tr>
                    <td><?= htmlspecialchars($client['id']); ?></td>
                    <td><?= htmlspecialchars($client['name']); ?></td>
                    <td><?= htmlspecialchars($client['phone']); ?></td>
                    <td>
                        <a href="?delete_id=<?= $client['id']; ?>" class="btn btn-danger btn-sm">Delete</a>
                        <button class="btn btn-warning btn-sm" onclick="editClient(<?= htmlspecialchars(json_encode($client)); ?>)">Edit</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
function editClient(client) {
    document.querySelector('input[name="name"]').value = client.name;
    document.querySelector('input[name="phone"]').value = client.phone;
    document.querySelector('input[name="client_id"]').value = client.id;
}
</script>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
