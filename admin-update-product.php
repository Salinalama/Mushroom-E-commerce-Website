<?php
session_start();
include 'db.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header("Location: login.php");
    exit();
}

// Get product ID from URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id <= 0) {
    header("Location: admin-products.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $price = floatval($_POST['price']);
    $description = $conn->real_escape_string($_POST['description']);
    $category = $conn->real_escape_string($_POST['category']);
    $stock_quantity = intval($_POST['stock_quantity']);
    $image = $conn->real_escape_string($_POST['image']);
    
    // Update product
    $sql = "UPDATE products SET 
            name = '$name',
            price = $price,
            description = '$description',
            category = '$category',
            stock_quantity = $stock_quantity,
            image = '$image'
            WHERE id = $product_id";
    
    if ($conn->query($sql) === TRUE) {
        header("Location: admin-products.php?success=Product updated successfully");
        exit();
    } else {
        header("Location: admin-products.php?error=Error updating product: " . $conn->error);
        exit();
    }
} else {
    header("Location: admin-products.php");
    exit();
}
?>