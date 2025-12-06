<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';
$userId = $_SESSION['user_id'];

// Get user details (REMOVED created_at since it doesn't exist)
$userStmt = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
$userStmt->bind_param("i", $userId);
$userStmt->execute();
$user = $userStmt->get_result()->fetch_assoc();
$userStmt->close();

// Get user orders
$ordersResult = $conn->query("SELECT * FROM orders WHERE user_id = $userId ORDER BY order_date DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Shroomify</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Signika+Negative:wght@300..700&display=swap" rel="stylesheet">
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
                <li><a href="profile.php" style="color: #ffe8d6;">Profile</a></li>
            </ul>
            <div style="display: flex; align-items: center; gap: 15px;">
                <form action="search.php" method="GET" style="display: flex; gap: 5px;">
                    <input type="text" name="q" placeholder="Search mushrooms..." 
                           style="padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                    <button type="submit" style="padding: 8px 12px; background: #bc6c25; color: white; border: none; border-radius: 4px;">
                        Search
                    </button>
                </form>
                <button class="signIn" onclick="location.href='logout.php';">Logout</button>
            </div>
        </nav>
    </header>

    <main style="padding: 120px 50px 50px; background-color: #fefae0; min-height: 80vh;">
        <div style="max-width: 1000px; margin: 0 auto;">
            <h1 style="color: #6f1d1b; margin-bottom: 40px;">My Profile</h1>
            
            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 40px;">
                <!-- User Info Section -->
                <div style="background: white; padding: 25px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <h2 style="color: #6f1d1b; margin-bottom: 20px;">Account Details</h2>
                    
                    <div style="margin-bottom: 20px;">
                        <label style="font-weight: bold; display: block; margin-bottom: 5px;">Username:</label>
                        <p style="padding: 10px; background: #f8f8f8; border-radius: 5px;"><?php echo htmlspecialchars($user['username']); ?></p>
                    </div>
                    
                    <div style="margin-bottom: 20px;">
                        <label style="font-weight: bold; display: block; margin-bottom: 5px;">Email:</label>
                        <p style="padding: 10px; background: #f8f8f8; border-radius: 5px;"><?php echo htmlspecialchars($user['email']); ?></p>
                    </div>
                    
                    <!-- REMOVED created_at section since column doesn't exist -->
                    
                    <a href="logout.php" style="display: block; text-align: center; background: #d32f2f; color: white; 
                       padding: 10px; border-radius: 5px; text-decoration: none; margin-top: 20px;">
                        Logout
                    </a>
                </div>
                
                <!-- Orders Section -->
                <div style="background: white; padding: 25px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <h2 style="color: #6f1d1b; margin-bottom: 20px;">Order History</h2>
                    
                    <?php if ($ordersResult && $ordersResult->num_rows > 0): ?>
                        <div style="max-height: 500px; overflow-y: auto;">
                            <?php while ($order = $ordersResult->fetch_assoc()): ?>
                                <div style="border: 1px solid #eee; border-radius: 8px; padding: 15px; margin-bottom: 15px;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                        <h3 style="color: #6f1d1b; margin: 0;">Order #<?php echo $order['id']; ?></h3>
                                        <span style="background: #dda15e; color: white; padding: 4px 8px; border-radius: 12px; font-size: 12px;">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </div>
                                    
                                    <p style="margin: 5px 0; color: #666;">
                                        <strong>Date:</strong> <?php echo date('M j, Y g:i A', strtotime($order['order_date'])); ?>
                                    </p>
                                    <p style="margin: 5px 0; color: #666;">
                                        <strong>Total:</strong> NPR <?php echo number_format($order['total_amount'], 2); ?>
                                    </p>
                                    <p style="margin: 5px 0; color: #666;">
                                        <strong>Payment Ref:</strong> <?php echo $order['payment_ref_id'] ?: 'N/A'; ?>
                                    </p>
                                    
                                    <a href="order-details.php?id=<?php echo $order['id']; ?>" 
                                       style="color: #bc6c25; text-decoration: none; font-size: 14px;">
                                        View Order Details →
                                    </a>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <p style="text-align: center; color: #666; padding: 40px 0;">
                            You haven't placed any orders yet.
                            <br>
                            <a href="index.php#products" style="color: #bc6c25;">Start shopping →</a>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <p>&copy; 2024 Shroomify. All Rights Reserved.</p>
        <p><a href="#">Privacy Policy</a> | <a href="#">Terms of Service</a></p>
    </footer>
</body>
</html>