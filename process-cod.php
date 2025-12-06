<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';
$userId = $_SESSION['user_id'];

// Get cart items and total
$result = $conn->query("SELECT * FROM cart_items WHERE user_id = $userId");
$cartItems = [];
$total = 0;

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $cartItems[] = $row;
        $total += $row['price'] * $row['quantity'];
    }
}

if (empty($cartItems)) {
    echo "<script>alert('Your cart is empty.'); window.location.href='cart.php';</script>";
    exit;
}

// Create COD order
$conn->begin_transaction();
try {
    // Create order with COD status
    $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, status, payment_method, payment_ref_id) 
                           VALUES (?, ?, 'Pending', 'Cash on Delivery', 'COD-".uniqid()."')");
    $stmt->bind_param("id", $userId, $total);
    $stmt->execute();
    $order_id = $stmt->insert_id;
    $stmt->close();
    
    // Create order items
    $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_name, price, quantity) VALUES (?, ?, ?, ?)");
    foreach ($cartItems as $item) {
        $stmt->bind_param("isdi", $order_id, $item['product_name'], $item['price'], $item['quantity']);
        $stmt->execute();
    }
    $stmt->close();
    
    // Clear cart
    $conn->query("DELETE FROM cart_items WHERE user_id = $userId");
    
    $conn->commit();
    
    // Success message
    echo "<script>
        alert('Order placed successfully! Your order #$order_id will be delivered soon. Please keep cash ready.');
        window.location.href='order-success.php?id=$order_id';
    </script>";
    
} catch (Exception $e) {
    $conn->rollback();
    echo "<script>
        alert('Error processing your order. Please try again.');
        window.location.href='cart.php';
    </script>";
}
?>