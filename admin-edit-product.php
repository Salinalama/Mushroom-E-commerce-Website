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

// Get product data
$sql = "SELECT * FROM products WHERE id = $product_id";
$result = $conn->query($sql);

if ($result->num_rows <= 0) {
    header("Location: admin-products.php");
    exit();
}

$product = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $price = floatval($_POST['price']);
    $description = $conn->real_escape_string($_POST['description']);
    $category = $conn->real_escape_string($_POST['category']);
    $stock_quantity = intval($_POST['stock_quantity']);
    $image = $conn->real_escape_string($_POST['image']);
    
    // Use prepared statement for update
    $stmt = $conn->prepare("UPDATE products SET name=?, price=?, description=?, category=?, stock_quantity=?, image=? WHERE id=?");
    $stmt->bind_param("sdssisi", $name, $price, $description, $category, $stock_quantity, $image, $product_id);
    
    if ($stmt->execute()) {
        header("Location: admin-products.php?success=Product updated successfully");
        exit();
    } else {
        $error = "Error updating product: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - Admin</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .admin-container { padding: 120px 20px 50px; background: #fefae0; min-height: 100vh; }
        .admin-form { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group textarea, .form-group select { 
            width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; 
        }
        .btn { background: #bc6c25; color: white; padding: 12px 20px; border: none; border-radius: 5px; cursor: pointer; margin-right: 10px; }
        .btn-secondary { background: #6c757d; }
    </style>
</head>
<body>
    <header>
        <nav>
            <ul>
                <li><a href="admin.php">Dashboard</a></li>
                <li><a href="admin-products.php">Products</a></li>
                <li><a href="admin-orders.php">Orders</a></li>
                <li><a href="admin-users.php">Users</a></li>
            </ul>
            <button class="signIn" onclick="location.href='logout.php';">Logout</button>
        </nav>
    </header>

    <main class="admin-container">
        <div class="admin-form">
            <h2 style="color: #6f1d1b; margin-bottom: 20px;">Edit Product</h2>
            
            <?php if (isset($error)): ?>
                <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label>Product Name</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Price (NPR)</label>
                    <input type="number" name="price" step="0.01" value="<?php echo htmlspecialchars($product['price']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="3" required><?php echo htmlspecialchars($product['description']); ?></textarea>
                </div>

                <div class="form-group">
                    <label>Category</label>
                    <select name="category" required>
                        <option value="Fresh Mushrooms" <?php echo $product['category'] == 'Fresh Mushrooms' ? 'selected' : ''; ?>>Fresh Mushrooms</option>
                        <option value="Dried Mushrooms" <?php echo $product['category'] == 'Dried Mushrooms' ? 'selected' : ''; ?>>Dried Mushrooms</option>
                        <option value="Organic Mushrooms" <?php echo $product['category'] == 'Organic Mushrooms' ? 'selected' : ''; ?>>Organic Mushrooms</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Stock Quantity</label>
                    <input type="number" name="stock_quantity" value="<?php echo htmlspecialchars($product['stock_quantity']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Image Filename</label>
                    <input type="text" name="image" value="<?php echo htmlspecialchars($product['image']); ?>" required placeholder="images/button.jpg">
                </div>

                <div>
                    <button type="submit" class="btn">Update Product</button>
                    <a href="admin-products.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </main>
</body>
</html>