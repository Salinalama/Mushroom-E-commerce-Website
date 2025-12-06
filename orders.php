<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';
$userId = $_SESSION['user_id'];

// Get orders
$ordersResult = $conn->query("SELECT * FROM orders WHERE user_id = $userId ORDER BY order_date DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shroomify - Your Orders</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="home">
        <header>
            <nav>
                <ul>
                    <li><a href="index.php#main">Home</a></li>
                    <li><a href="index.php#products">Products</a></li>
                    <li><a href="index.php#about">About</a></li>
                    <li><a href="index.php#contact">Contact</a></li>
                    <li><a href="cart.php">Cart</a></li>
                    <li><a href="orders.php" class="active">Orders</a></li>
                </ul>
                <button class="signIn" onclick="location.href='logout.php';">Logout</button>
            </nav>
        </header>

        <main class="cart-main">
            <h2>Your Orders</h2>
            <div class="cart-container">
                <?php if ($ordersResult && $ordersResult->num_rows > 0) : ?>
                    <?php while ($order = $ordersResult->fetch_assoc()) : ?>
                        <div class="order-item">
                            <h3>Order #<?php echo $order['id']; ?></h3>
                            <p>Date: <?php echo $order['order_date']; ?></p>
                            <p>Total: NPR <?php echo $order['total_amount']; ?></p>
                            <p>Status: <?php echo $order['status']; ?></p>
                            
                            <h4>Items:</h4>
                            <?php 
                            $orderId = $order['id'];
                            $itemsResult = $conn->query("SELECT * FROM order_items WHERE order_id = $orderId");
                            if ($itemsResult && $itemsResult->num_rows > 0) : 
                            ?>
                                <ul>
                                <?php while ($item = $itemsResult->fetch_assoc()) : ?>
                                    <li><?php echo $item['product_name']; ?> - NPR <?php echo $item['price']; ?> x <?php echo $item['quantity']; ?></li>
                                <?php endwhile; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                <?php else : ?>
                    <p class="empty-cart-msg">You haven't placed any orders yet.</p>
                <?php endif; ?>
            </div>
        </main>

        <footer>
            <p>&copy; 2024 Shroomify. All Rights Reserved.</p>
            <p><a href="#">Privacy Policy</a> | <a href="#">Terms of Service</a></p>
        </footer>
    </div>
</body>
</html>