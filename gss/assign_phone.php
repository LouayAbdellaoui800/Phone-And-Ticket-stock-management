<?php
session_start();
include 'db.php'; // Database connection file

// Fetch clients and phones data for dropdowns
$clients = $conn->query("SELECT id, name FROM clients")->fetch_all(MYSQLI_ASSOC);
$phones = $conn->query("SELECT id, model, brand, price, quantity FROM stock WHERE quantity > 0")->fetch_all(MYSQLI_ASSOC);

$error = $success = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['assign_phone'])) {
    $client_id = $_POST['client_id'];
    $selected_phones = $_POST['phone_id']; // array of selected phone IDs
    $quantities = $_POST['quantity']; // array of selected quantities
    $down_payment = (float)$_POST['down_payment'];
    $installment_months = (int)$_POST['installment_months'];

    $total_price = 0;
    $outstanding_balance = 0;

    // Calculate total price for selected phones and check stock
    $phones_available = true;
    foreach ($selected_phones as $index => $phone_id) {
        // Fetch phone price and quantity
        $phone_stmt = $conn->prepare("SELECT price, quantity FROM stock WHERE id = ?");
        $phone_stmt->bind_param("i", $phone_id);
        $phone_stmt->execute();
        $phone = $phone_stmt->get_result()->fetch_assoc();

        if ($phone) {
            // Check if requested quantity is available in stock
            $requested_quantity = (int)$quantities[$index];
            if ($requested_quantity > $phone['quantity']) {
                $phones_available = false;
            }
            $total_price += $phone['price'] * $requested_quantity;
        }
    }

    // Calculate outstanding balance and monthly installment
    $outstanding_balance = $total_price - $down_payment;
    $monthly_installment = $outstanding_balance > 0 ? $outstanding_balance / $installment_months : 0; // Based on selected months

    if ($outstanding_balance < 0) {
        $error = "Down payment cannot exceed the total price of the phones.";
    } elseif (!$phones_available) {
        $error = "One or more selected phones are out of stock or requested quantity exceeds available stock.";
    } else {
        // Begin transaction
        $conn->begin_transaction();
        try {
            foreach ($selected_phones as $index => $phone_id) {
                // Fetch the requested quantity
                $requested_quantity = (int)$quantities[$index];
                
                // Insert assignment for each phone
                $stmt = $conn->prepare("INSERT INTO phone_assignments (client_id, phone_id, down_payment, monthly_installment, outstanding_balance, quantity, assign_date) VALUES (?, ?, ?, ?, ?, ?, NOW())");
                $stmt->bind_param("iidddi", $client_id, $phone_id, $down_payment, $monthly_installment, $outstanding_balance, $requested_quantity);

                if (!$stmt->execute()) {
                    throw new Exception("Error assigning phone. Please try again.");
                }

                // Decrement phone stock
                $update_stock = $conn->prepare("UPDATE stock SET quantity = quantity - ? WHERE id = ?");
                $update_stock->bind_param("ii", $requested_quantity, $phone_id);
                $update_stock->execute();
            }
            
            // Commit transaction
            $conn->commit();
            $success = "Phones assigned with payment plan successfully!";
        } catch (Exception $e) {
            // Rollback transaction
            $conn->rollback();
            $error = $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assign Phones with Payment Plan</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script>
        function updateTotalPrice() {
            const selectedPhones = Array.from(document.querySelectorAll('select[name="phone_id[]"] option:checked'));
            const quantities = Array.from(document.querySelectorAll('input[name="quantity[]"]'));
            let totalPrice = 0;

            selectedPhones.forEach((option, index) => {
                const price = parseFloat(option.getAttribute('data-price'));
                const quantity = parseInt(quantities[index].value) || 0; // Default to 0 if empty
                totalPrice += price * quantity;
            });

            document.getElementById('total_price').innerText = totalPrice.toFixed(2);
        }

        function validateForm() {
            const downPayment = parseFloat(document.querySelector('input[name="down_payment"]').value);
            const totalPrice = parseFloat(document.getElementById('total_price').innerText);
            if (downPayment > totalPrice) {
                alert("Down payment cannot exceed the total price.");
                return false;
            }
            return true;
        }
    </script>
</head>
<body>
<div class="container mt-5">
    <a href="dashboard.php" class="btn btn-secondary mb-4">Back to Dashboard</a>

    <h2>Assign Phones to Client</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error); ?></div>
    <?php elseif ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <form method="POST" action="" onsubmit="return validateForm()">
        <div class="form-group">
            <label for="client_id">Select Client</label>
            <select name="client_id" class="form-control" required>
                <?php foreach ($clients as $client): ?>
                    <option value="<?= $client['id']; ?>"><?= htmlspecialchars($client['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="phone_id">Select Phones</label>
            <select name="phone_id[]" class="form-control" multiple required onchange="updateTotalPrice()">
                <?php foreach ($phones as $phone): ?>
                    <option value="<?= $phone['id']; ?>" data-price="<?= $phone['price']; ?>"><?= htmlspecialchars($phone['brand'] . " " . $phone['model'] . " - TND " . $phone['price']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="quantity">Quantities</label>
            <input type="number" name="quantity[]" class="form-control" min="1" value="1" onchange="updateTotalPrice()" required>
        </div>

        <div class="form-group">
            <label for="total_price">Total Price (TND)</label>
            <p id="total_price" class="font-weight-bold">0.00</p>
        </div>

        <div class="form-group">
            <label for="down_payment">Down Payment (TND)</label>
            <input type="number" name="down_payment" class="form-control" min="0" step="0.01" required>
        </div>

        <div class="form-group">
            <label for="installment_months">Installment Plan (Months)</label>
            <select name="installment_months" class="form-control" required>
                <option value="6">6 Months</option>
                <option value="12" selected>12 Months</option>
            </select>
        </div>
        
        <button type="submit" name="assign_phone" class="btn btn-primary">Assign Phones with Payment Plan</button>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
