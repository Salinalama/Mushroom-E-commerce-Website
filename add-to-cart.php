<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include 'db.php'; // DB connection file

    $userId = $_SESSION['user_id'];
    $productName = $_POST['product_name'];
    $price = $_POST['price'];

    // Check if already in cart
    $check = $conn->prepare("SELECT * FROM cart_items WHERE user_id=? AND product_name=?");
    $check->bind_param("is", $userId, $productName);
    $check->execute();
    $result = $check->get_result();

 $quantity = $_POST['quantity'] ?? 1; // Get quantity from form

if ($result->num_rows > 0) {
    $update = $conn->prepare("UPDATE cart_items SET quantity = quantity + ? WHERE user_id = ? AND product_name = ?");
    $update->bind_param("iis", $quantity, $userId, $productName);
    $update->execute();
} else {
    $stmt = $conn->prepare("INSERT INTO cart_items (user_id, product_name, price, quantity) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isii", $userId, $productName, $price, $quantity);
    $stmt->execute();
}
    header("Location: cart.php");
}
?>
