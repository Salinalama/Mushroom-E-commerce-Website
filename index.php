<?php session_start(); ?>
<!DOCTYPE html>
<html>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Signika+Negative:wght@300..700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">

    <title>Shroomify</title>
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
        <?php if (isset($_SESSION['user_id'])): ?>
            <button class="signIn" onclick="location.href='logout.php';">Logout</button>
        <?php else: ?>
            <button class="signIn" onclick="location.href='login.php';">Login</button>
        <?php endif; ?>
    </div>
</nav>
        </header>

        <div id="main" class="main">
            <div class="main2">
                <h1><pre>Welcome 
to
shroomify</pre></h1>
            </div>
            <img src="mushroom.png" alt="Mushroom Image">
        </div>
    </div>

    <section id="products" class="products" style="padding: 50px; background-color: #fefae0;">
        <h2 style="text-align: center; font-size: 32px; color: #6f1d1b; margin-bottom: 30px;">Our Mushrooms</h2>
        <div id="product-list" style="display: flex; gap: 30px; flex-wrap: wrap; justify-content: center;"></div>
    </section>
    
    <div id="product-list" style="display: flex; gap: 30px; flex-wrap: wrap; justify-content: center;">
    <?php
    include 'db.php';
    // Fetch products from database instead of hardcoded array
    $dbProducts = $conn->query("SELECT * FROM products LIMIT 8");
    while ($product = $dbProducts->fetch_assoc()):
    ?>
        <div style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); width: 250px;">
            <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                 alt="<?php echo htmlspecialchars($product['name']); ?>"
                 style="width: 100%; height: 200px; object-fit: cover; border-radius: 8px; margin-bottom: 15px;">
            
            <h3 style="color: #6f1d1b; margin-bottom: 10px;"><?php echo htmlspecialchars($product['name']); ?></h3>
            <p style="color: #bc6c25; font-weight: bold; margin-bottom: 15px;">NPR <?php echo number_format($product['price'], 2); ?></p>
            
            <a href="product-detail.php?id=<?php echo $product['id']; ?>" 
               style="display: inline-block; background: #dda15e; color: white; padding: 8px 15px; 
                      text-decoration: none; border-radius: 5px; margin-right: 10px;">
                View Details
            </a>
            
            <form method="POST" action="add-to-cart.php" style="display: inline;">
                <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($product['name']); ?>">
                <input type="hidden" name="price" value="<?php echo $product['price']; ?>">
                <button type="submit" style="background: #bc6c25; color: white; padding: 8px 15px; 
                       border: none; border-radius: 5px; cursor: pointer;">
                    Add to Cart
                </button>
            </form>
        </div>
    <?php endwhile; ?>
</div>

    <div id="about" class="about" style="padding: 50px; background-color: #e6ccb2; text-align: center;">
        <h2 style="color: #6f1d1b;">About Shroomify</h2>
        <p style="max-width: 800px; margin: auto; font-size: 18px;">
            Shroomify is your one-stop destination for fresh, organic, and nutrient-packed mushrooms. We specialize in
            delivering top-quality mushroom varieties that not only add flavor to your meals but also boost your health.
            Whether you're a foodie, a health enthusiast, or just curious about mushrooms — Shroomify is here to make your
            mushroom journey exciting and wholesome. 🍄
        </p>
    </div>

    <section id="contact" style="background-color: #fefae0; padding: 50px 20px;">
        <h2 style="text-align: center; font-size: 32px; color: #6f1d1b; margin-bottom: 30px;">Contact Us</h2>
        <div style="max-width: 800px; margin: auto;">
            <form style="display: flex; flex-direction: column; gap: 20px;">
                <input type="email" placeholder="Your Email" required style="padding: 15px; font-size: 16px; border: 1px solid #ccc; border-radius: 8px;">
                <textarea rows="5" placeholder="Your Message" required style="padding: 15px; font-size: 16px; border: 1px solid #ccc; border-radius: 8px;"></textarea>
                <button type="submit" style="background-color: #bc6c25; color: #fff; padding: 12px 20px; border: none; border-radius: 8px; font-size: 16px; cursor: pointer;">
                    Send Message
                </button>
            </form>
            <div style="margin-top: 40px; text-align: center; font-size: 16px;">
                <p>📞 Phone: 9841227779</p>
                <p>📧 Email: contact@shroomify.com</p>
                <p>📍 Location: Shroomify HQ, Gokarna, Nepal</p>
            </div>
        </div>
    </section>

    <footer>
        <p>&copy; 2024 Shroomify. All Rights Reserved.</p>
        <p><a href="#">Privacy Policy</a> | <a href="#">Terms of Service</a></p>
    </footer>

    <!-- <script>
    const products = [
        { name: 'Button Mushrooms (200g)', price: 100, image: 'button.jpg' },
        { name: 'Oyster Mushrooms (200g)', price: 120, image: 'oyster.jpg' },
        { name: 'Enoki Mushrooms (200g)', price: 140, image: 'enoki.jpeg' },
        { name: 'Shiitake Mushrooms (200g)', price: 150, image: 'shiitake.jpg' },
        { name: 'Dry Oyster Mushrooms (200g)', price: 180, image: 'dry-oyster.png' },
        { name: 'Dry Shiitake Mushrooms (200g)', price: 200, image: 'dry-shiitake.jpg' },
        { name: 'Dry Enoki Mushrooms (200g)', price: 190, image: 'dry-enoki.jpg' },
        { name: 'Dry Button Mushrooms (200g)', price: 170, image: 'dry-button.webp' },
    ];

    const productList = document.getElementById('product-list');

    products.forEach((product) => {
        const productDiv = document.createElement('div');
        productDiv.classList.add('product-card');
        productDiv.innerHTML = `
            <img src="${product.image}" alt="${product.name}" style="width: 180px; height: 180px; object-fit: cover; border-radius: 10px;">
            <h3 style="color:#bc6c25;">${product.name}</h3>
            <p style="margin: 10px 0;">NPR ${product.price}</p>
            <form method="POST" action="add-to-cart.php">
                <input type="hidden" name="product_name" value="${product.name}">
                <input type="hidden" name="price" value="${product.price}">
                <button type="submit" class="add-to-cart">Add to Cart</button>
            </form>
        `;
        productList.appendChild(productDiv);
    });
</script> -->


</body>
</html>
