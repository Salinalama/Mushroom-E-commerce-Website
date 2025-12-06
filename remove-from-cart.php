<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';
$id = $_GET['id'];
$userId = $_SESSION['user_id'];

$conn->query("DELETE FROM cart_items WHERE id = $id AND user_id = $userId");
header("Location: cart.php");
?>
