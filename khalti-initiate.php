<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';

$userId = $_SESSION['user_id'];
$result = $conn->query("SELECT * FROM cart_items WHERE user_id = $userId");

$total_rs = 0.0;
$cartItems = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $cartItems[] = $row;
        $total_rs += ((float)$row['price']) * ((int)$row['quantity']);
    }
}

if ($total_rs <= 0) {
    echo "<script>alert('Your cart is empty.'); window.location.href='cart.php';</script>";
    exit;
}

// Get customer info
$cstmt = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
$cstmt->bind_param("i", $userId);
$cstmt->execute();
$customer = $cstmt->get_result()->fetch_assoc();
$cstmt->close();

// Khalti configuration
$SECRET_KEY = "2d98cb006e8142e5a357c9945fb0107b"; // Replace with your actual key
$BASE_URL = "http://localhost/myproject/"; // Update with your actual base URL
$RETURN_URL = $BASE_URL . "khalti-verify.php";
$WEBSITE_URL = $BASE_URL;

$amount_paisa = (int) round($total_rs * 100);

$payload = [
    "return_url" => $RETURN_URL,
    "website_url" => $WEBSITE_URL,
    "amount" => $amount_paisa,
    "purchase_order_id" => uniqid("ORDER_"),
    "purchase_order_name" => "Shroomify Order",
    "customer_info" => [
        "name" => $customer['username'] ?? "Customer",
        "email" => $customer['email'] ?? "customer@example.com",
        "phone" => "9800000000" // You might want to collect phone numbers in registration
    ]
];

// Initiate payment with Khalti
$ch = curl_init("https://dev.khalti.com/api/v2/epayment/initiate/");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Key {$SECRET_KEY}",
    "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

$response = curl_exec($ch);
$http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http !== 200) {
    echo "<pre>Payment initiation failed (HTTP $http)\n" . htmlspecialchars($response) . "</pre>";
    exit;
}

$data = json_decode($response, true);
if (!empty($data["payment_url"])) {
    header("Location: " . $data["payment_url"]);
    exit;
}

echo "<pre>Unexpected response from Khalti:\n" . htmlspecialchars($response) . "</pre>";
?>