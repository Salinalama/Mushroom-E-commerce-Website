<?php
session_start();
include 'db.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit();
}

// Handle order status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $orderId = intval($_POST['order_id']);
    $newStatus = $_POST['status'];
    
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $newStatus, $orderId);
    $stmt->execute();
    
    header("Location: admin-orders.php?success=Order status updated");
    exit();
}

// Get all orders with user information
$orders = $conn->query("
    SELECT o.*, u.username, u.email 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.order_date DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management - Admin</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Reuse the same styles from admin-products.php */
        .admin-container { padding: 120px 20px 50px; background-color: #fefae0; min-height: 100vh; }
        .admin-header { background: white; padding: 20px; border-radius: 10px; margin-bottom: 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .admin-section { background: white; padding: 25px; border-radius: 10px; margin-bottom: 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .admin-nav { background: #6f1d1b; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .admin-nav a { color: white; text-decoration: none; margin-right: 20px; padding: 8px 15px; border-radius: 5px; transition: background 0.3s; }
        .admin-nav a:hover { background: #8b2d2b; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        table th, table td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        table th { background: #f8f8f8; font-weight: bold; }
        
        .status-pending { color: #ff9800; }
        .status-processing { color: #2196f3; }
        .status-shipped { color: #673ab7; }
        .status-delivered { color: #4caf50; }
        .status-cancelled { color: #f44336; }
        
        .btn { background: #bc6c25; color: white; padding: 8px 15px; border: none; border-radius: 5px; cursor: pointer; }
        .btn:hover { background: #a55a1f; }
    </style>
</head>
<body>
    <header>
        <nav>
            <!-- <ul>
                <li><a href="index.php#main">Home</a></li>
                <li><a href="index.php#products">Products</a></li>
                <li><a href="admin.php">Admin Panel</a></li>
            </ul> -->
            <div style="display: flex; align-items: center; gap: 15px;">
                <span style="color: #ffe8d6;">Welcome, Admin!</span>
                <button class="signIn" onclick="location.href='logout.php';">Logout</button>
            </div>
        </nav>
    </header>

    <main class="admin-container">
        <div style="max-width: 1400px; margin: 0 auto;">
            <div class="admin-header">
                <h1 style="color: #6f1d1b; margin-bottom: 20px;">Order Management</h1>
                
                <div class="admin-nav">
                    <a href="admin.php">Dashboard</a>
                    <a href="admin-products.php">Products</a>
                    <a href="admin-orders.php">Orders</a>
                    <a href="admin-users.php">Users</a>
                </div>
            </div>

            <!-- Orders List -->
            <div class="admin-section">
                <h2 style="color: #6f1d1b; margin-bottom: 20px;">All Orders</h2>
                
                <?php if ($orders->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Email</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Payment Method</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($order = $orders->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $order['id']; ?></td>
                                <td><?php echo htmlspecialchars($order['username']); ?></td>
                                <td><?php echo htmlspecialchars($order['email']); ?></td>
                                <td>NPR <?php echo number_format($order['total_amount'], 2); ?></td>
                                <td>
                                    <span class="status-<?php echo strtolower($order['status']); ?>">
                                        <?php echo $order['status']; ?>
                                    </span>
                                </td>
                                <td><?php echo $order['payment_method'] ?? 'N/A'; ?></td>
                                <td><?php echo date('M j, Y g:i A', strtotime($order['order_date'])); ?></td>
                                <td>
                                    <a href="admin-order-details.php?id=<?php echo $order['id']; ?>" 
                                       style="color: #bc6c25; text-decoration: none; margin-right: 10px;">
                                        <!-- View Details -->
                                    </a>
                                    <form method="POST" action="" style="display: inline;">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <select name="status" style="padding: 5px; margin-right: 5px;">
                                            <option value="Pending" <?php echo $order['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="Processing" <?php echo $order['status'] == 'Processing' ? 'selected' : ''; ?>>Processing</option>
                                            <option value="Shipped" <?php echo $order['status'] == 'Shipped' ? 'selected' : ''; ?>>Shipped</option>
                                            <option value="Delivered" <?php echo $order['status'] == 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
                                            <option value="Cancelled" <?php echo $order['status'] == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                        <button type="submit" name="update_status" class="btn">Update</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No orders found.</p>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer>
        <p>&copy; 2024 Shroomify. All Rights Reserved.</p>
        <p><a href="#">Privacy Policy</a> | <a href="#">Terms of Service</a></p>
    </footer>
</body>
</html>