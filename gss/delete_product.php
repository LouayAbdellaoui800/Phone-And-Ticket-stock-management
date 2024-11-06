<?php
 include('db.php');
// Check if ID is provided
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Delete product from the database
    $stmt = $conn->prepare("DELETE FROM stock WHERE id = ?");
    $stmt->execute([$id]);

    // Redirect back to the product management page
    header("Location: product.php");
    exit();
}
?>
