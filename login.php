<?php
session_start();
include 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, password, is_admin FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($userId, $hashedPassword, $isAdmin);
        $stmt->fetch();

        if (password_verify($password, $hashedPassword)) {
            $_SESSION['user_id'] = $userId;
            $_SESSION['is_admin'] = $isAdmin; // Store admin status in session
            
            // Redirect based on admin status
            if ($isAdmin == 1) {
                header("Location: admin.php"); // Redirect admin to admin panel
            } else {
                header("Location: index.php"); // Redirect regular user to homepage
            }
            exit();
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "No user found with that email.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Shroomify</title>
    <link rel="stylesheet" href="style.css">
</head>
<body style="padding: 100px 20px;">
    <h2 style="text-align:center;">Login to Shroomify</h2>
    <form method="post" style="max-width: 400px; margin:auto; background:#fff; padding:30px; border-radius:10px;">
        <?php if ($error): ?>
            <p style="color:red;"><?php echo $error; ?></p>
        <?php endif; ?>
        <div>
            <label>Email</label>
            <input type="email" name="email" required style="width:100%; padding:10px; margin-bottom:15px;">
        </div>
        <div>
            <label>Password</label>
            <input type="password" name="password" required style="width:100%; padding:10px; margin-bottom:15px;">
        </div>
        <button type="submit" style="width:100%; background:#bc6c25; color:#fff; padding:10px;">Login</button>
        <p style="text-align:center; margin-top:15px;">Don't have an account? <a href="register.php">Register</a></p>
    </form>
</body>
</html>
