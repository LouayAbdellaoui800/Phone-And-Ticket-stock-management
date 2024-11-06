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

	$error = "";
	$success = "";
	// Handle ticket or carte assignment
	if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['assign_item'])) {
		$client_id = (int)$_POST['client_id'];
		$item_type = $_POST['item_type']; // 'ticket' or 'carte'
		$carte_type = $_POST['carte_type']; // only for 'carte'
		$quantity = (int)$_POST['quantity'];
		$unit_price = (float)$_POST['price']; // Store unit price entered by the user
		$total_price = $unit_price * $quantity; // Calculate total price
		$credit = isset($_POST['credit_checkbox']) ? (float)$_POST['credit'] : 0; // Credit only if checkbox is checked

		if ($quantity <= 0) {
			$error = 'Quantity must be greater than zero.';
		} elseif ($unit_price <= 0) {
			$error = 'Price must be greater than zero.';
		} elseif ($credit < 0) {
			$error = 'Crédit must be zero or more.';
		} else {
			// Begin a transaction
			$conn->begin_transaction();

			try {
				if ($item_type == 'ticket') {
					// Check stock and price in ticket table
					$stmt = $conn->prepare("SELECT quantity, price FROM ticket WHERE type = ?");
					$stmt->bind_param("s", $carte_type);
					$stmt->execute();
					$result = $stmt->get_result();
					$stock = $result->fetch_assoc();

					if ($stock['quantity'] < $quantity) {
						$error = 'Not enough stock available in ticket.';
					} else {
						// Assign ticket to client
						$stmt = $conn->prepare("INSERT INTO sales (client_id, item_type, carte_type, quantity, price, credit) VALUES (?, 'ticket', ?, ?, ?, ?)");
						$stmt->bind_param("isidd", $client_id, $carte_type, $quantity, $total_price, $credit); // Use total_price here
						$stmt->execute();

						// Update ticket stock
						$new_quantity = $stock['quantity'] - $quantity;
						$stmt = $conn->prepare("UPDATE ticket SET quantity = ? WHERE type = ?");
						$stmt->bind_param("is", $new_quantity, $carte_type);
						$stmt->execute();
						
						// Update ticket price
						$new_price = $stock['price'] - $total_price;
						$stmt = $conn->prepare("UPDATE ticket SET price = ? WHERE type = ?");
						$stmt->bind_param("is", $new_price, $carte_type);
						$stmt->execute();
						$success = "Ticket assigned successfully!";
					}
				} elseif ($item_type == 'carte') {
					// Check stock and price in carte table
					$stmt = $conn->prepare("SELECT quantity, price FROM carte WHERE type = ?");
					$stmt->bind_param("s", $carte_type);
					$stmt->execute();
					$result = $stmt->get_result();
					$stock = $result->fetch_assoc();

					if ($stock['quantity'] < $quantity) {
						$error = 'Not enough stock available in carte.';
					} else {
						// Assign carte to client
						$stmt = $conn->prepare("INSERT INTO sales (client_id, item_type, carte_type, quantity, price, credit) VALUES (?, 'carte', ?, ?, ?, ?)");
						$stmt->bind_param("isidd", $client_id, $carte_type, $quantity, $total_price, $credit); // Use total_price here
						$stmt->execute();

						// Update carte stock
						$new_quantity = $stock['quantity'] - $quantity;
						$stmt = $conn->prepare("UPDATE carte SET quantity = ? WHERE type = ?");
						$stmt->bind_param("is", $new_quantity, $carte_type);
						$stmt->execute();
						
						 // Update carte price
						$new_price = $stock['price'] - $total_price;
						$stmt = $conn->prepare("UPDATE carte SET price = ? WHERE type = ?");
						$stmt->bind_param("is", $new_price, $carte_type);
						$stmt->execute();

						$success = "Carte assigned successfully!";
					}
				}

				// Insert a debt record if credit is checked
				if ($credit > 0) {
					$new_credit=$credit * $quantity;
					$stmt = $conn->prepare("INSERT INTO debts (client_id, amount_due, due_date, status) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 30 DAY), 'pending')");
					$stmt->bind_param("id", $client_id, $new_credit);
					$stmt->execute();
				}

				// Commit the transaction
				$conn->commit();
			} catch (mysqli_sql_exception $e) {
				// Rollback the transaction if something goes wrong
				$conn->rollback();
				$error = "Error occurred: " . $e->getMessage();
			}
		}
	}

	?>
	<!DOCTYPE html>
	<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Assign Ticket or Carte</title>
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
			h1 {
				color: #343a40;
				margin-bottom: 30px;
			}
			.alert {
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
			.form-control {
				border-radius: 5px;
			}
		</style>
	</head>
	<body>
	<div class="container">
		<h1 class="text-center">Assign Ticket or Carte to Client</h1>

		<a href="dashboard.php" class="btn btn-secondary mb-4">Back to Dashboard</a>

		<!-- Display errors or success messages -->
		<?php if (!empty($error)): ?>
			<div class="alert alert-danger"><?= htmlspecialchars($error); ?></div>
		<?php endif; ?>
		<?php if (!empty($success)): ?>
			<div class="alert alert-success"><?= htmlspecialchars($success); ?></div>
		<?php endif; ?>

		<form method="POST" action="" class="mt-4 mb-4">
			<div class="form-row">
				<div class="col-md-3 mb-3">
					<label for="client">Select Client</label>
					<select name="client_id" class="form-control" required>
						<?php foreach ($clients as $client) : ?>
							<option value="<?= $client['id']; ?>"><?= htmlspecialchars($client['name']); ?></option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="col-md-3 mb-3">
					<label for="item_type">Item Type</label>
					<select name="item_type" id="item_type" class="form-control" required>
						<option value="ticket">Ticket</option>
						<option value="carte">Carte</option>
					</select>
				</div>

				<div class="col-md-3 mb-3">
					<label for="carte_type">Type (Ticket/Carte)</label>
					<select name="carte_type" class="form-control" required>
						<option value="telecom">Telecom</option>
						<option value="ooredoo">Ooredoo</option>
						<option value="orange">Orange</option>
					</select>
				</div>

				<div class="col-md-3 mb-3">
					<label for="quantity">Quantity</label>
					<input type="number" name="quantity" class="form-control" placeholder="Quantity" required min="1">
				</div>

				<div class="col-md-3 mb-3">
					<label for="price">Price (TND)</label>
					<input type="number" name="price" class="form-control" placeholder="Price" required min="0" step="0.01">
				</div>

				<div class="col-md-3 mb-3">
					<label for="credit">Crédit (TND)</label>
					<input type="number" name="credit" class="form-control" placeholder="Crédit" min="0" step="0.01">
				</div>

				<div class="col-md-3 mb-3">
					<label for="credit_checkbox">Generate Debt</label>
					<div>
						<input type="checkbox" name="credit_checkbox" id="credit_checkbox">
						<label for="credit_checkbox">Check to generate debt from credit</label>
					</div>
				</div>
			</div>
			<button type="submit" name="assign_item" class="btn btn-primary btn-block">Assign Item</button>
		</form>
	</div>

	<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
	</body>
	</html>
