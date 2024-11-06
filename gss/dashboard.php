<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }
        .navbar {
            background: linear-gradient(135deg, #343a40, #23272b);
            padding: 10px;
            color: #ffffff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .navbar h1 {
            font-size: 1.8rem;
            font-weight: bold;
            margin: 0;
        }
        .logout-btn {
            font-weight: bold;
            color: #ffffff;
            background-color: transparent;
            border: 2px solid #ffffff;
            padding: 0.4rem 1rem;
            transition: background 0.3s, border-color 0.3s;
            border-radius: 5px;
            text-decoration: none;
        }
        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            border-color: #ffffff;
        }
        .sidenav {
            height: 100%;
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #343a40;
            padding-top: 20px;
            color: #ffffff;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
        }
        .sidenav a {
            padding: 15px 20px;
            text-decoration: none;
            font-size: 16px;
            color: #ffffff;
            display: block;
            transition: background 0.3s;
        }
        .sidenav a:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        .content {
            margin-left: 270px; /* Sidebar width + padding */
            padding: 20px;
        }
        .card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
            border-radius: 10px;
            overflow: hidden;
            position: relative;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
        }
        .card i {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        .card h5 {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        .btn-light {
            font-weight: bold;
            color: #ffffff;
            padding: 0.5rem 1rem;
            background: rgba(255, 255, 255, 0.1);
            transition: background 0.3s;
        }
        .btn-light:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        .bg-info-gradient { background: linear-gradient(135deg, #17a2b8, #138496); }
        .bg-success-gradient { background: linear-gradient(135deg, #28a745, #218838); }
        .bg-primary-gradient { background: linear-gradient(135deg, #007bff, #0069d9); }
        .bg-warning-gradient { background: linear-gradient(135deg, #ffc107, #e0a800); }
        .bg-danger-gradient { background: linear-gradient(135deg, #dc3545, #c82333); }
        .bg-secondary-gradient { background: linear-gradient(135deg, #6c757d, #5a6268); }
        .container {
            margin-top: 100px;
        }
    </style>
</head>
<body>

<!-- Navbar with Logout -->
<div class="navbar fixed-top">
    <h1>Admin Dashboard</h1>
    <a href="logout.php" class="logout-btn">Logout</a>
</div>

<!-- Sidebar Navigation -->
<div class="sidenav">
    <h4>Navigation</h4>
    <a href="#" onclick="toggleCategory('ticket-cart')">Ticket/Cart</a>
    <a href="#" onclick="toggleCategory('phone-stock')">Phone Stock</a>
</div>


<!-- Main Content -->
<div class="content">
    <div id="ticket-cart-section">
        <h2>Assign Phones to Client</h2>
        <div class="row">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card bg-info-gradient text-white text-center p-3">
                    <i class="fas fa-users"></i>
                    <div class="card-body">
                        <h5 class="card-title">Manage Clients</h5>
                        <p class="card-text">Add or remove clients.</p>
                        <a href="clients.php" class="btn btn-light">Manage Clients</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card bg-success-gradient text-white text-center p-3">
                    <i class="fas fa-boxes"></i>
                    <div class="card-body">
                        <h5 class="card-title">Manage Stock</h5>
                        <p class="card-text">Add or remove card stock.</p>
                        <a href="stock.php" class="btn btn-light">Manage Stock</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card bg-primary-gradient text-white text-center p-3">
                    <i class="fas fa-cart-plus"></i>
                    <div class="card-body">
                        <h5 class="card-title">Assign Cart</h5>
                        <p class="card-text">Assign cart to clients.</p>
                        <a href="assign_cart.php" class="btn btn-light">Assign Cart</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card bg-warning-gradient text-white text-center p-3">
                    <i class="fas fa-chart-line"></i>
                    <div class="card-body">
                        <h5 class="card-title">Track Sales</h5>
                        <p class="card-text">View and track all sales.</p>
                        <a href="sales.php" class="btn btn-light">Track Sales</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card bg-danger-gradient text-white text-center p-3">
                    <i class="fas fa-file-alt"></i>
                    <div class="card-body">
                        <h5 class="card-title">Generate Reports</h5>
                        <p class="card-text">Generate sales reports.</p>
                        <a href="reports.php" class="btn btn-light">Generate Reports</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card bg-secondary-gradient text-white text-center p-3">
                    <i class="fas fa-money-check-alt"></i>
                    <div class="card-body">
                        <h5 class="card-title">Debts</h5>
                        <p class="card-text">View client debts reports.</p>
                        <a href="debt.php" class="btn btn-light">Show Debts</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="phone-stock-section" style="display: none;">
        <h2>Manage Phone Stock</h2>
        <div class="row">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card bg-danger-gradient text-white text-center p-3">
                    <i class="fas fa-phone"></i>
                    <div class="card-body">
                        <h5 class="card-title">Manage Phones</h5>
                        <p class="card-text">Add or remove phone stock.</p>
                        <a href="product.php" class="btn btn-light">Manage Phones</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card bg-warning-gradient text-white text-center p-3">
                    <i class="fas fa-phone"></i>
                    <div class="card-body">
                        <h5 class="card-title">Assign Phones</h5>
                        <p class="card-text">Assign phones to clients.</p>
                        <a href="assign_phone.php" class="btn btn-light">Assign Phones</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card bg-success-gradient text-white text-center p-3">
                    <i class="fas fa-file-alt"></i>
                    <div class="card-body">
                        <h5 class="card-title">Phone Reports</h5>
                        <p class="card-text">View reports on phones.</p>
                        <a href="p_reports.php" class="btn btn-light">View Reports</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleCategory(category) {
        const ticketCartSection = document.getElementById('ticket-cart-section');
        const phoneStockSection = document.getElementById('phone-stock-section');
        if (category === 'ticket-cart') {
            ticketCartSection.style.display = 'block';
            phoneStockSection.style.display = 'none';
        } else {
            ticketCartSection.style.display = 'none';
            phoneStockSection.style.display = 'block';
        }
    }

    // Initialize the page with ticket/cart section visible
    window.onload = function() {
        toggleCategory('ticket-cart');
    }
</script>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
