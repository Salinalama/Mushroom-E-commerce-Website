<?php
session_start();
include 'db.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit();
}

// Get statistics
$totalUsers = $conn->query("SELECT COUNT(*) FROM users")->fetch_row()[0];
$totalOrders = $conn->query("SELECT COUNT(*) FROM orders")->fetch_row()[0];
$totalProducts = $conn->query("SELECT COUNT(*) FROM products")->fetch_row()[0];
$revenue = $conn->query("SELECT SUM(total_amount) FROM orders WHERE status = 'Paid'")->fetch_row()[0] ?? 0;

// Get recent orders
$recentOrders = $conn->query("
    SELECT o.*, u.username 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.order_date DESC 
    LIMIT 5
");

// Get low stock products
$lowStockProducts = $conn->query("
    SELECT * FROM products 
    WHERE stock_quantity <= 10 
    ORDER BY stock_quantity ASC 
    LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Shroomify</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .admin-container {
            padding: 120px 20px 50px;
            background-color: #fefae0;
            min-height: 100vh;
        }
        
        .admin-header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .stat-number {
            font-size: 2.5em;
            font-weight: bold;
            color: #6f1d1b;
            margin: 10px 0;
        }
        
        .admin-section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .admin-nav {
            background: #6f1d1b;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .admin-nav a {
            color: white;
            text-decoration: none;
            margin-right: 20px;
            padding: 8px 15px;
            border-radius: 5px;
            transition: background 0.3s;
        }
        
        .admin-nav a:hover {
            background: #8b2d2b;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        table th, table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        table th {
            background: #f8f8f8;
            font-weight: bold;
        }
        
        .status-pending { color: #ff9800; }
        .status-paid { color: #4caf50; }
        .status-cancelled { color: #f44336; }
    </style>
</head>
<body>
    <header>
        <nav>
            <!-- <ul>
                <li><a href="index.php#main">Home</a></li>
                <li><a href="index.php#products">Products</a></li>
                <li><a href="index.php#about">About</a></li>
                <li><a href="index.php#contact">Contact</a></li>
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
                <h1 style="color: #6f1d1b; margin-bottom: 20px;">Admin Dashboard</h1>
                
                <div class="admin-nav">
                    <a href="admin.php">Dashboard</a>
                    <a href="admin-products.php">Products</a>
                    <a href="admin-orders.php">Orders</a>
                    <a href="admin-users.php">Users</a>
                    <!-- <a href="admin-reports.php">Reports</a> -->
                </div>
            </div>

            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Users</h3>
                    <div class="stat-number"><?php echo $totalUsers; ?></div>
                    <p>Registered customers</p>
                </div>
                
                <div class="stat-card">
                    <h3>Total Orders</h3>
                    <div class="stat-number"><?php echo $totalOrders; ?></div>
                    <p>All-time orders</p>
                </div>
                
                <div class="stat-card">
                    <h3>Total Products</h3>
                    <div class="stat-number"><?php echo $totalProducts; ?></div>
                    <p>Available products</p>
                </div>
                
                <div class="stat-card">
                    <h3>Total Revenue</h3>
                    <div class="stat-number">NPR <?php echo number_format($revenue, 2); ?></div>
                    <p>From completed orders</p>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="admin-section">
                <h2 style="color: #6f1d1b; margin-bottom: 20px;">Recent Orders</h2>
                <?php if ($recentOrders->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                                <!-- <th>Action</th> -->
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($order = $recentOrders->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $order['id']; ?></td>
                                <td><?php echo htmlspecialchars($order['username']); ?></td>
                                <td>NPR <?php echo number_format($order['total_amount'], 2); ?></td>
                                <td>
                                    <span class="status-<?php echo strtolower($order['status']); ?>">
                                        <?php echo $order['status']; ?>
                                    </span>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($order['order_date'])); ?></td>
                                <td>
                                    <a href="admin-order-details.php?id=<?php echo $order['id']; ?>" 
                                       style="color: #bc6c25; text-decoration: none;">
                                        <!-- View -->
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No orders found.</p>
                <?php endif; ?>
            </div>

            <!-- Low Stock Alert -->
           
        </div>
    </main>

    <footer>
        <p>&copy; 2024 Shroomify. All Rights Reserved.</p>
        <p><a href="#">Privacy Policy</a> | <a href="#">Terms of Service</a></p>
    </footer>
</body>
</html>