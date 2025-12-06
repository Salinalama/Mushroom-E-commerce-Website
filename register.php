<?php
include 'db.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $error = "Email already registered.";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $password);
        $stmt->execute();
        header("Location: login.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register - Shroomify</title>
    <link rel="stylesheet" href="style.css">
</head>
<body style="padding: 100px 20px;">
    <h2 style="text-align:center;">Create Account</h2>
    <form method="post" style="max-width:400px; margin:auto; background:#fff; padding:30px; border-radius:10px;">
        <?php if ($error): ?>
            <p style="color:red;"><?php echo $error; ?></p>
        <?php endif; ?>
        <input type="text" name="username" placeholder="Username" required style="width:100%; padding:10px; margin-bottom:15px;">
        <input type="email" name="email" placeholder="Email" required style="width:100%; padding:10px; margin-bottom:15px;">
        <input type="password" name="password" placeholder="Password" required style="width:100%; padding:10px; margin-bottom:15px;">
        <button type="submit" style="width:100%; background:#bc6c25; color:#fff; padding:10px;">Register</button>
        <p style="text-align:center; margin-top:15px;">Already have an account? <a href="login.php">Login</a></p>
    </form>
</body>
</html>
