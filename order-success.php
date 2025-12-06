<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';
$orderId = $_GET['id'] ?? 0;
$userId = $_SESSION['user_id'];

// Get order details
$orderStmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$orderStmt->bind_param("ii", $orderId, $userId);
$orderStmt->execute();
$order = $orderStmt->get_result()->fetch_assoc();
$orderStmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Success - Shroomify</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <nav>
            <ul>
                <li><a href="index.php#main">Home</a></li>
                <li><a href="index.php#products">Products</a></li>
                <li><a href="index.php#about">About</a></li>
                <li><a href="index.php#contact">Contact</a></li>
                <li><a href="cart.php">Cart</a></li>
                <li><a href="profile.php">Profile</a></li>
            </ul>
        </nav>
    </header>

    <main style="padding: 100px 20px; background-color: #fefae0; min-height: 80vh;">
        <div style="max-width: 600px; margin: auto; background: white; padding: 40px; border-radius: 15px; text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
            <div style="font-size: 80px; margin-bottom: 20px;">✅</div>
            <h1 style="color: #6f1d1b; margin-bottom: 20px;">Order Placed Successfully!</h1>
            
            <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; margin-bottom: 30px;">
                <h2 style="color: #bc6c25; margin-bottom: 15px;">Order #<?php echo $order['id']; ?></h2>
                <p><strong>Status:</strong> <span style="color: #dda15e;"><?php echo ucfirst($order['status']); ?></span></p>
                <p><strong>Payment Method:</strong> <?php echo $order['payment_method']; ?></p>
                <p><strong>Total Amount:</strong> NPR <?php echo number_format($order['total_amount'], 2); ?></p>
                <p><strong>Order Date:</strong> <?php echo date('F j, Y g:i A', strtotime($order['order_date'])); ?></p>
            </div>
            
            <p style="color: #666; margin-bottom: 30px; line-height: 1.6;">
                <?php if ($order['payment_method'] === 'Cash on Delivery'): ?>
                    Your order will be delivered soon. Please keep <strong>NPR <?php echo number_format($order['total_amount'], 2); ?></strong> cash ready for payment upon delivery.
                <?php else: ?>
                    Thank you for your payment. Your order is being processed.
                <?php endif; ?>
            </p>
            
            <div style="display: flex; gap: 15px; justify-content: center;">
                <a href="profile.php" style="background: #bc6c25; color: white; padding: 12px 25px; 
                   text-decoration: none; border-radius: 5px; display: inline-block;">
                    View Orders
                </a>
                <a href="index.php#products" style="background: #dda15e; color: white; padding: 12px 25px; 
                   text-decoration: none; border-radius: 5px; display: inline-block;">
                    Continue Shopping
                </a>
            </div>
        </div>
    </main>

    <footer>
        <p>&copy; 2024 Shroomify. All Rights Reserved.</p>
        <p><a href="#">Privacy Policy</a> | <a href="#">Terms of Service</a></p>
    </footer>
</body>
</html>