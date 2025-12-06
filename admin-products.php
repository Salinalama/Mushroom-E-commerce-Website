<?php
session_start();
include 'db.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header("Location: login.php");
    exit();
}

// Handle all product operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_product'])) {
        // Add product
        $stmt = $conn->prepare("INSERT INTO products (name, price, description, category, stock_quantity, image) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sdssis", $_POST['name'], $_POST['price'], $_POST['description'], $_POST['category'], $_POST['stock_quantity'], $_POST['image']);
        $stmt->execute();
    }
    elseif (isset($_POST['edit_product'])) {
        // Edit product
        $stmt = $conn->prepare("UPDATE products SET name=?, price=?, description=?, category=?, stock_quantity=?, image=? WHERE id=?");
        $stmt->bind_param("sdssisi", $_POST['name'], $_POST['price'], $_POST['description'], $_POST['category'], $_POST['stock_quantity'], $_POST['image'], $_POST['product_id']);
        $stmt->execute();
    }
    header("Location: admin-products.php");
    exit();
}

// Handle delete
if (isset($_GET['delete_id'])) {
    $stmt = $conn->prepare("DELETE FROM products WHERE id=?");
    $stmt->bind_param("i", $_GET['delete_id']);
    $stmt->execute();
    header("Location: admin-products.php");
    exit();
}

// Get all products
$products = $conn->query("SELECT * FROM products ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - Admin</title>
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
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group textarea, .form-group select { 
            width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; 
        }
        .btn { background: #bc6c25; color: white; padding: 10px 15px; border: none; border-radius: 5px; cursor: pointer; }
        .btn-sm { padding: 5px 10px; font-size: 12px; }
        .btn-danger { background: #dc3545; }
    </style>
</head>
<body>
    <header>
        <nav>
            <!-- <ul>
                <li><a href="admin.php">Dashboard</a></li>
                <li><a href="admin-products.php">Products</a></li>
                <li><a href="admin-orders.php">Orders</a></li>
                <li><a href="admin-users.php">Users</a></li>
            </ul> -->
            <button class="signIn" onclick="location.href='logout.php';">Logout</button>
        </nav>
    </header>

    <main class="admin-container">
        <div style="max-width: 1200px; margin: 0 auto;">
            <div class="admin-header">
                <h1 style="color: #6f1d1b;">Product Management</h1>
                <div class="admin-nav">
                    <a href="admin.php">Dashboard</a>
                    <a href="admin-products.php">Products</a>
                    <a href="admin-orders.php">Orders</a>
                    <a href="admin-users.php">Users</a>
                </div>
            </div>

            <!-- Add Product Form -->
            <div class="admin-section">
                <h2>Add New Product</h2>
                <form method="POST">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label>Product Name</label>
                            <input type="text" name="name" required>
                        </div>
                        <div class="form-group">
                            <label>Price (NPR)</label>
                            <input type="number" name="price" step="0.01" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" rows="2" required></textarea>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label>Category</label>
                            <select name="category" required>
                                <option value="Fresh Mushrooms">Fresh Mushrooms</option>
                                <option value="Dried Mushrooms">Dried Mushrooms</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Stock Quantity</label>
                            <input type="number" name="stock_quantity" required>
                        </div>
                        <div class="form-group">
                            <label>Image Filename</label>
                            <input type="text" name="image" required placeholder="button.jpg">
                        </div>
                    </div>
                    <button type="submit" name="add_product" class="btn">Add Product</button>
                </form>
            </div>

            <!-- Products List -->
            <div class="admin-section">
                <h2>All Products (<?php echo $products->num_rows; ?>)</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Price</th>
                            <th>Category</th>
                            <th>Stock</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($product = $products->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $product['id']; ?></td>
                            <td><?php echo $product['name']; ?></td>
                            <td>NPR <?php echo $product['price']; ?></td>
                            <td><?php echo $product['category']; ?></td>
                            <td><?php echo $product['stock_quantity']; ?></td>
                            <td>
                                <!-- Edit Button -->
                                <a href="admin-edit-product.php?id=<?php echo $product['id']; ?>" 
                                   class="btn btn-sm" style="background: #bc6c25; color: white; text-decoration: none; padding: 5px 10px; border-radius: 3px;">
                                    Edit
                                </a>
                                
                                <!-- Delete Button -->
                                <a href="admin-products.php?delete_id=<?php echo $product['id']; ?>" 
                                   class="btn btn-sm btn-danger" style="background: #dc3545; color: white; text-decoration: none; padding: 5px 10px; border-radius: 3px;"
                                   onclick="return confirm('Are you sure you want to delete <?php echo addslashes($product['name']); ?>?')">
                                    Delete
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>