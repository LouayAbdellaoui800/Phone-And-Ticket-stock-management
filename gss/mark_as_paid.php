<?php
session_start();
include 'db.php'; // Database connection file

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php'); // Redirect to login if not logged in
    exit();
}

// Check if assignment ID is provided
if (isset($_GET['id'])) {
    $assignment_id = (int)$_GET['id'];

    // Update the assignment to mark it as paid
    $stmt = $conn->prepare("UPDATE phone_assignments SET outstanding_balance = 0 WHERE id = ?");
    $stmt->bind_param("i", $assignment_id);

    if ($stmt->execute()) {
        $success_message = "Payment completed successfully!";
    } else {
        $error_message = "Error occurred while marking payment as completed.";
    }
} else {
    $error_message = "Invalid assignment ID.";
}

// Fetch updated assignments to display the report
$query = "
    SELECT a.id, c.name AS client_name, p.brand, p.model, a.down_payment, 
           a.monthly_installment, a.outstanding_balance, a.assign_date
    FROM phone_assignments AS a
    JOIN clients AS c ON a.client_id = c.id
    JOIN stock AS p ON a.phone_id = p.id
    ORDER BY a.assign_date DESC
";
$assignments = $conn->query($query)->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Mark Payment as Completed</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2>Assignments Report</h2>

    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success_message); ?></div>
    <?php endif; ?>
    
    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <?php if (empty($assignments)): ?>
        <div class="alert alert-warning">No assignments found.</div>
    <?php else: ?>
        <table class="table table-bordered mt-4">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Client Name</th>
                    <th>Phone Brand & Model</th>
                    <th>Down Payment (TND)</th>
                    <th>Monthly Installment (TND)</th>
                    <th>Outstanding Balance (TND)</th>
                    <th>Assignment Date</th>
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
    <?php endif; ?>
</div>
</body>
</html>
