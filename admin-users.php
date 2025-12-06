<?php
session_start();
include 'db.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header("Location: login.php");
    exit();
}

// Handle make admin/remove admin actions
if (isset($_GET['action']) && isset($_GET['user_id'])) {
    $user_id = (int)$_GET['user_id'];
    $action = $_GET['action'];
    
    if ($action === 'make_admin') {
        $conn->query("UPDATE users SET is_admin = 1 WHERE id = $user_id");
    } 
    elseif ($action === 'remove_admin') {
        // Prevent removing admin from yourself
        if ($user_id != $_SESSION['user_id']) {
            $conn->query("UPDATE users SET is_admin = 0 WHERE id = $user_id");
        }
    }
    header("Location: admin-users.php");
    exit();
}

// Handle user deletion
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    
    // Prevent deleting yourself
    if ($delete_id != $_SESSION['user_id']) {
        $conn->query("DELETE FROM users WHERE id = $delete_id");
    }
    header("Location: admin-users.php");
    exit();
}

// Get all users (without created_at since it doesn't exist)
$users = $conn->query("SELECT id, username, email, is_admin FROM users ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Admin</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .admin-container { padding: 120px 20px 50px; background: #fefae0; min-height: 100vh; }
        .admin-header { background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; }
        .admin-nav { background: #6f1d1b; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .admin-nav a { color: white; text-decoration: none; margin-right: 20px; padding: 8px 15px; border-radius: 5px; }
        .admin-section { background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f8f8; }
        .btn { padding: 5px 10px; border: none; border-radius: 3px; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-sm { font-size: 12px; }
        .btn-success { background: #28a745; color: white; }
        .btn-warning { background: #ffc107; color: black; }
        .btn-danger { background: #dc3545; color: white; }
        .admin-badge { background: #6f1d1b; color: white; padding: 3px 8px; border-radius: 12px; font-size: 12px; }
        .current-user { background: #f8f9fa; }
    </style>
</head>
<body>
    <header>
        <nav>
            <!-- <ul>
                <li><a href="admin.php">Dashboard</a></li>
                <li><a href="admin-products.php">Products</a></li>
                <li><a href="admin-orders.php">Orders</a></li>
                <li><a href="admin-users.php" style="color: #ffe8d6;">Users</a></li>
            </ul> -->
            <button class="signIn" onclick="location.href='logout.php';">Logout</button>
        </nav>
    </header>

    <main class="admin-container">
        <div style="max-width: 1200px; margin: 0 auto;">
            <div class="admin-header">
                <h1 style="color: #6f1d1b;">User Management</h1>
                <div class="admin-nav">
                    <a href="admin.php">Dashboard</a>
                    <a href="admin-products.php">Products</a>
                    <a href="admin-orders.php">Orders</a>
                    <a href="admin-users.php">Users</a>
                </div>
            </div>

            <!-- Users List -->
            <div class="admin-section">
                <h2>All Users (<?php echo $users->num_rows; ?>)</h2>
                
                <?php if ($users->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($user = $users->fetch_assoc()): ?>
                            <tr class="<?php echo $user['id'] == $_SESSION['user_id'] ? 'current-user' : ''; ?>">
                                <td><?php echo $user['id']; ?></td>
                                <td>
                                    <?php echo htmlspecialchars($user['username']); ?>
                                    <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                        <span class="admin-badge">You</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <?php if ($user['is_admin'] == 1): ?>
                                        <span style="color: #6f1d1b; font-weight: bold;">Admin</span>
                                    <?php else: ?>
                                        Customer
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <?php if ($user['is_admin'] == 0): ?>
                                            <a href="admin-users.php?action=make_admin&user_id=<?php echo $user['id']; ?>" 
                                               class="btn btn-sm btn-success"
                                               onclick="return confirm('Make <?php echo htmlspecialchars($user['username']); ?> an admin?')">
                                                Make Admin
                                            </a>
                                        <?php else: ?>
                                            <a href="admin-users.php?action=remove_admin&user_id=<?php echo $user['id']; ?>" 
                                               class="btn btn-sm btn-warning"
                                               onclick="return confirm('Remove admin privileges from <?php echo htmlspecialchars($user['username']); ?>?')">
                                                Remove Admin
                                            </a>
                                        <?php endif; ?>
                                        
                                        <a href="admin-users.php?delete_id=<?php echo $user['id']; ?>" 
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('Are you sure you want to delete <?php echo htmlspecialchars($user['username']); ?>? This cannot be undone!')">
                                            Delete
                                        </a>
                                    <?php else: ?>
                                        <span style="color: #999; font-size: 12px;">Current session</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No users found.</p>
                <?php endif; ?>
            </div>

            <!-- User Statistics -->
            <div class="admin-section">
                <h2>User Statistics</h2>
                <?php
                $total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
                $total_admins = $conn->query("SELECT COUNT(*) as count FROM users WHERE is_admin = 1")->fetch_assoc()['count'];
                $total_customers = $total_users - $total_admins;
                ?>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                    <div style="background: #e9ecef; padding: 15px; border-radius: 5px; text-align: center;">
                        <h3 style="margin: 0; color: #6f1d1b;"><?php echo $total_users; ?></h3>
                        <p style="margin: 5px 0 0 0;">Total Users</p>
                    </div>
                    
                    <div style="background: #d4edda; padding: 15px; border-radius: 5px; text-align: center;">
                        <h3 style="margin: 0; color: #155724;"><?php echo $total_admins; ?></h3>
                        <p style="margin: 5px 0 0 0;">Administrators</p>
                    </div>
                    
                    <div style="background: #cce5ff; padding: 15px; border-radius: 5px; text-align: center;">
                        <h3 style="margin: 0; color: #004085;"><?php echo $total_customers; ?></h3>
                        <p style="margin: 5px 0 0 0;">Customers</p>
                    </div>
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