<?php
// Database connection
$servername = "localhost";  // Use your own values
$username_db = "root";      // Your MySQL username
$password_db = "";          // Your MySQL password (if any)
$dbname = "perso"; // Your database name

// Create connection
$conn = new mysqli($servername, $username_db, $password_db, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
