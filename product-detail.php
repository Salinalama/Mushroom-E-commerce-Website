<?php
session_start();
include 'db.php';

// Get product ID from URL
$productId = $_GET['id'] ?? 0;
$product = null;

if ($productId) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();
}

if (!$product) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Shroomify</title>
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
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="profile.php">Profile</a></li>
                <?php endif; ?>
            </ul>
            <div style="display: flex; align-items: center; gap: 15px;">
                <form action="search.php" method="GET" style="display: flex; gap: 5px;">
                    <input type="text" name="q" placeholder="Search mushrooms..." 
                           style="padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                    <button type="submit" style="padding: 8px 12px; background: #bc6c25; color: white; border: none; border-radius: 4px;">
                        Search
                    </button>
                </form>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <button class="signIn" onclick="location.href='logout.php';">Logout</button>
                <?php else: ?>
                    <button class="signIn" onclick="location.href='login.php';">Login</button>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <main style="padding: 120px 50px 50px; background-color: #fefae0; min-height: 80vh;">
        <div style="max-width: 1200px; margin: 0 auto;">
            <a href="index.php#products" style="color: #bc6c25; text-decoration: none; margin-bottom: 20px; display: inline-block;">
                ← Back to Products
            </a>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 50px; margin-top: 30px;">
                <div>
                    <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                         style="width: 100%; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                </div>
                
                <div>
                    <h1 style="color: #6f1d1b; margin-bottom: 10px;"><?php echo htmlspecialchars($product['name']); ?></h1>
                    <p style="color: #bc6c25; font-size: 24px; font-weight: bold; margin-bottom: 20px;">
                        NPR <?php echo number_format($product['price'], 2); ?>
                    </p>
                    
                    <div style="margin-bottom: 20px;">
                        <span style="background: #dda15e; color: white; padding: 5px 10px; border-radius: 15px; font-size: 14px;">
                            <?php echo htmlspecialchars($product['category']); ?>
                        </span>
                    </div>
                    
                    <p style="line-height: 1.6; margin-bottom: 30px;"><?php echo htmlspecialchars($product['description']); ?></p>
                    
                    <div style="margin-bottom: 30px;">
                        <strong>Availability:</strong> 
                        <span style="color: <?php echo $product['stock_quantity'] > 0 ? '#388e3c' : '#d32f2f'; ?>">
                            <?php echo $product['stock_quantity'] > 0 ? 'In Stock (' . $product['stock_quantity'] . ' available)' : 'Out of Stock'; ?>
                        </span>
                    </div>
                    
                    <?php if ($product['stock_quantity'] > 0): ?>
                    <form method="POST" action="add-to-cart.php">
                        <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($product['name']); ?>">
                        <input type="hidden" name="price" value="<?php echo $product['price']; ?>">
                        
                        <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 20px;">
                            <label for="quantity" style="font-weight: bold;">Quantity:</label>
                            <input type="number" id="quantity" name="quantity" value="1" min="1" 
                                   max="<?php echo $product['stock_quantity']; ?>" 
                                   style="padding: 8px; width: 80px; border: 1px solid #ccc; border-radius: 4px;">
                        </div>
                        
                        <button type="submit" style="background: #bc6c25; color: white; padding: 12px 30px; 
                            border: none; border-radius: 5px; font-size: 16px; cursor: pointer; width: 100%;">
                            Add to Cart
                        </button>
                    </form>
                    <?php else: ?>
                        <button style="background: #ccc; color: #666; padding: 12px 30px; 
                            border: none; border-radius: 5px; font-size: 16px; width: 100%;" disabled>
                            Out of Stock
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            
            <div style="margin-top: 50px;">
                <h2 style="color: #6f1d1b; margin-bottom: 20px;">Product Details</h2>
                <div style="background: white; padding: 20px; border-radius: 8px;">
                    <p><strong>Category:</strong> <?php echo htmlspecialchars($product['category']); ?></p>
                    <p><strong>Weight:</strong> 200g</p>
                    <p><strong>Storage:</strong> Keep refrigerated at 2-4°C</p>
                    <p><strong>Shelf Life:</strong> 7-10 days when properly stored</p>
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