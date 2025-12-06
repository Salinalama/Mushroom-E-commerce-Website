<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';
$userId = $_SESSION['user_id'];
$result = $conn->query("SELECT * FROM cart_items WHERE user_id = $userId");

$cartItems = [];
$total = 0;

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $cartItems[] = $row;
        $total += $row['price'] * $row['quantity'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shroomify - Your Cart</title>
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
                    <button class="signIn" onclick="location.href='logout.php';">Logout</button>
                </div>
            </nav>
        </header>

        <main class="cart-main">
            <h2>Your Cart</h2>
            <div class="cart-container">
                <?php if (!empty($cartItems)) : ?>
                    <?php foreach ($cartItems as $item) : ?>
                        <div class="cart-item">
                            <div>
                                <strong><?php echo htmlspecialchars($item['product_name']); ?></strong><br>
                                NPR <?php echo $item['price']; ?> x <?php echo $item['quantity']; ?>
                            </div>
                            <a class="remove-link" href="remove-from-cart.php?id=<?php echo $item['id']; ?>">Remove</a>
                        </div>
                    <?php endforeach; ?>

                    <h3 class="cart-total">Total: NPR <?php echo $total; ?></h3>
                    
                    <!-- Payment Options -->
                    <div class="payment-options" style="margin-top: 30px;">
                        <h3 style="color: #6f1d1b; margin-bottom: 20px;">Choose Payment Method:</h3>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <!-- Khalti Payment -->
                            <div style="border: 2px solid #dda15e; border-radius: 10px; padding: 20px; text-align: center;">
                                <div style="font-size: 24px; margin-bottom: 15px;">💳</div>
                                <h4 style="color: #6f1d1b; margin-bottom: 10px;">Pay with Khalti</h4>
                                <p style="color: #666; margin-bottom: 15px; font-size: 14px;">
                                    Secure online payment with Nepal's leading payment gateway
                                </p>
                                <form action="khalti-initiate.php" method="POST">
                                    <button type="submit" style="background: #5C2D91; color: white; padding: 12px 25px; 
                                        border: none; border-radius: 5px; font-size: 16px; cursor: pointer; width: 100%;">
                                        Pay with Khalti
                                    </button>
                                </form>
                            </div>
                            
                            <!-- Cash on Delivery -->
                            <div style="border: 2px solid #dda15e; border-radius: 10px; padding: 20px; text-align: center;">
                                <div style="font-size: 24px; margin-bottom: 15px;">💰</div>
                                <h4 style="color: #6f1d1b; margin-bottom: 10px;">Cash on Delivery</h4>
                                <p style="color: #666; margin-bottom: 15px; font-size: 14px;">
                                    Pay cash when your order is delivered to your doorstep
                                </p>
                                <form action="process-cod.php" method="POST">
                                    <button type="submit" style="background: #bc6c25; color: white; padding: 12px 25px; 
                                        border: none; border-radius: 5px; font-size: 16px; cursor: pointer; width: 100%;">
                                        Order with COD
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                <?php else : ?>
                    <p class="empty-cart-msg">Your cart is empty 🍄</p>
                    <div style="text-align: center; margin-top: 20px;">
                        <a href="index.php#products" style="background: #bc6c25; color: white; padding: 12px 20px; 
                           text-decoration: none; border-radius: 5px; display: inline-block;">
                            Continue Shopping
                        </a>
                    </div>
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