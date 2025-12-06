<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';
$userId = $_SESSION['user_id'];
$orderId = $_GET['id'] ?? 0;

// Get order details
$orderStmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$orderStmt->bind_param("ii", $orderId, $userId);
$orderStmt->execute();
$order = $orderStmt->get_result()->fetch_assoc();
$orderStmt->close();

if (!$order) {
    header("Location: profile.php");
    exit();
}

// Get order items
$itemsStmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
$itemsStmt->bind_param("i", $orderId);
$itemsStmt->execute();
$items = $itemsStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$itemsStmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Details - Shroomify</title>
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

    <main style="padding: 100px 20px;">
        <div style="max-width: 800px; margin: auto; background: white; padding: 30px; border-radius: 10px;">
            <h2>Order #<?php echo $order['id']; ?> Details</h2>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 20px 0;">
                <div>
                    <h3>Order Information</h3>
                    <p><strong>Date:</strong> <?php echo date('F j, Y g:i A', strtotime($order['order_date'])); ?></p>
                    <p><strong>Status:</strong> <?php echo ucfirst($order['status']); ?></p>
                    <p><strong>Total:</strong> NPR <?php echo number_format($order['total_amount'], 2); ?></p>
                </div>
                
                <div>
                    <h3>Payment Information</h3>
                    <p><strong>Payment Reference:</strong> <?php echo $order['payment_ref_id'] ?: 'N/A'; ?></p>
                </div>
            </div>
            
            <h3>Order Items</h3>
            <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
                <thead>
                    <tr style="background: #f8f8f8;">
                        <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Product</th>
                        <th style="padding: 12px; text-align: right; border-bottom: 2px solid #ddd;">Price</th>
                        <th style="padding: 12px; text-align: center; border-bottom: 2px solid #ddd;">Quantity</th>
                        <th style="padding: 12px; text-align: right; border-bottom: 2px solid #ddd;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td style="padding: 12px; border-bottom: 1px solid #ddd;"><?php echo htmlspecialchars($item['product_name']); ?></td>
                        <td style="padding: 12px; text-align: right; border-bottom: 1px solid #ddd;">NPR <?php echo number_format($item['price'], 2); ?></td>
                        <td style="padding: 12px; text-align: center; border-bottom: 1px solid #ddd;"><?php echo $item['quantity']; ?></td>
                        <td style="padding: 12px; text-align: right; border-bottom: 1px solid #ddd;">NPR <?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" style="padding: 12px; text-align: right; font-weight: bold;">Grand Total:</td>
                        <td style="padding: 12px; text-align: right; font-weight: bold;">NPR <?php echo number_format($order['total_amount'], 2); ?></td>
                    </tr>
                </tfoot>
            </table>
            
            <a href="profile.php" style="display: inline-block; background: #bc6c25; color: white; 
               padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-top: 20px;">
                ← Back to Profile
            </a>
        </div>
    </main>
</body>
</html>