<?php
session_start();
include 'db.php'; // Include your database connection

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$error = "";
$success = "";

// Fetch all debts to display for selection
$debtQuery = $conn->query("SELECT debts.id, Clients.name AS client_name, debts.amount_due, debts.due_date, debts.status FROM debts JOIN Clients ON debts.client_id = Clients.id ORDER BY debts.due_date DESC");
$debts = $debtQuery->fetch_all(MYSQLI_ASSOC);

// Handle form submission for updating a debt record
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_debt'])) {
    $debt_id = (int)$_POST['debt_id'];
    $amount_due = (float)$_POST['amount_due'];
    $due_date = $_POST['due_date'];
    $status = $_POST['status'];

    if ($amount_due < 0) {
        $error = 'Amount due must be zero or more.';
    } elseif (empty($due_date)) {
        $error = 'Due date is required.';
    } else {
        // Update debt in the database
        $stmt = $conn->prepare("UPDATE debts SET amount_due = ?, due_date = ?, status = ? WHERE id = ?");
        $stmt->bind_param("dssi", $amount_due, $due_date, $status, $debt_id);
        if ($stmt->execute()) {
            $success = "Debt updated successfully!";
        } else {
            $error = "Error updating debt: " . $stmt->error;
        }
    }
}

// Load specific debt details if a debt is selected
$debtDetails = null;
if (isset($_GET['debt_id'])) {
    $debt_id = (int)$_GET['debt_id'];
    $stmt = $conn->prepare("SELECT * FROM debts WHERE id = ?");
    $stmt->bind_param("i", $debt_id);
    $stmt->execute();
    $debtDetails = $stmt->get_result()->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modify Debt</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h1 class="text-center">Modify Debt</h1>

    <a href="dashboard.php" class="btn btn-secondary mb-4">Back to Dashboard</a>

    <!-- Display errors or success messages -->
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <!-- Select Debt to Modify -->
    <form method="GET" action="modifier_debt.php" class="mb-4">
        <div class="form-group">
            <label for="debt_id">Select Debt</label>
            <select name="debt_id" class="form-control" required onchange="this.form.submit()">
                <option value="">Choose a debt to modify</option>
                <?php foreach ($debts as $debt) : ?>
                    <option value="<?= $debt['id']; ?>" <?= (isset($_GET['debt_id']) && $_GET['debt_id'] == $debt['id']) ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($debt['client_name']) . " - Due: " . $debt['due_date'] . " - Status: " . $debt['status']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>

    <!-- Display Selected Debt Details for Editing -->
    <?php if ($debtDetails): ?>
        <form method="POST" action="modifier_debt.php">
            <input type="hidden" name="debt_id" value="<?= htmlspecialchars($debtDetails['id']); ?>">

            <div class="form-group">
                <label for="amount_due">Amount Due (TND)</label>
                <input type="number" name="amount_due" class="form-control" value="<?= htmlspecialchars($debtDetails['amount_due']); ?>" required min="0" step="0.01">
            </div>

            <div class="form-group">
                <label for="due_date">Due Date</label>
                <input type="date" name="due_date" class="form-control" value="<?= htmlspecialchars($debtDetails['due_date']); ?>" required>
            </div>

            <div class="form-group">
                <label for="status">Status</label>
                <select name="status" class="form-control" required>
                    <option value="pending" <?= $debtDetails['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="paid" <?= $debtDetails['status'] == 'paid' ? 'selected' : ''; ?>>Paid</option>
                    <option value="overdue" <?= $debtDetails['status'] == 'overdue' ? 'selected' : ''; ?>>Overdue</option>
                </select>
            </div>

            <button type="submit" name="update_debt" class="btn btn-primary">Update Debt</button>
        </form>
    <?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
