<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

echo "<h2>Payment Gateway (Placeholder)</h2>";
echo "<p>Feature coming soon. Thank you for shopping with Shroomify!</p>";
echo "<a href='index.html'>Return to Home</a>";
?>
