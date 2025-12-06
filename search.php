<?php
session_start();
include 'db.php';

$searchQuery = $_GET['q'] ?? '';
$products = [];

if (!empty($searchQuery)) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE name LIKE ? OR description LIKE ? OR category LIKE ?");
    $searchTerm = "%$searchQuery%";
    $stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - Shroomify</title>
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
                           value="<?php echo htmlspecialchars($searchQuery); ?>"
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
            <h1 style="color: #6f1d1b; margin-bottom: 30px;">
                Search Results for "<?php echo htmlspecialchars($searchQuery); ?>"
            </h1>
            
            <?php if (empty($searchQuery)): ?>
                <p>Please enter a search term.</p>
            <?php elseif (empty($products)): ?>
                <p>No products found matching your search.</p>
            <?php else: ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 30px;">
                    <?php foreach ($products as $product): ?>
                        <div style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                            <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>"
                                 style="width: 100%; height: 200px; object-fit: cover; border-radius: 8px; margin-bottom: 15px;">
                            
                            <h3 style="color: #6f1d1b; margin-bottom: 10px;"><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p style="color: #bc6c25; font-weight: bold; margin-bottom: 10px;">NPR <?php echo number_format($product['price'], 2); ?></p>
                            
                            <a href="product-detail.php?id=<?php echo $product['id']; ?>" 
                               style="display: inline-block; background: #dda15e; color: white; padding: 8px 15px; 
                                      text-decoration: none; border-radius: 5px; margin-top: 10px;">
                                View Details
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <p>&copy; 2024 Shroomify. All Rights Reserved.</p>
        <p><a href="#">Privacy Policy</a> | <a href="#">Terms of Service</a></p>
    </footer>
</body>
</html>