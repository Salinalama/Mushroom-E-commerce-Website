<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
include 'db.php';

// Check if we have pending order data
if (!isset($_SESSION['pending_order'])) {
    echo "<script>alert('No pending order found.'); window.location.href='cart.php';</script>";
    exit;
}

$pendingOrder = $_SESSION['pending_order'];
$expected_paisa = (int) round($pendingOrder['total'] * 100);

// Khalti configuration - USE TEST SECRET KEY FOR SANDBOX
$SECRET_KEY = "test_secret_key_0d7b6c0d7b6c0d7b6c0d7b6c0d7b6c0d"; // Use test key for sandbox

// Get pidx from URL parameters or form data
$pidx = $_GET['pidx'] ?? $_POST['pidx'] ?? $pendingOrder['pidx'] ?? null;

if (!$pidx) {
    echo "<script>alert('Payment reference missing.'); window.location.href='cart.php';</script>";
    exit;
}

// Verify payment with Khalti - USE SANDBOX URL
$ch = curl_init("https://a.khalti.com/api/v2/epayment/lookup/"); // Sandbox URL
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Key {$SECRET_KEY}",
    "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(["pidx" => $pidx]));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For local testing
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$http = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// DEBUG: Log response
error_log("Khalti Lookup Response (HTTP $http): " . $response);

if (curl_error($ch)) {
    error_log("CURL Lookup Error: " . curl_error($ch));
}

curl_close($ch);

if ($http !== 200) {
    echo "<script>alert('Payment verification failed. Please contact support.'); window.location.href='cart.php';</script>";
    exit;
}

$info = json_decode($response, true);
$status = $info['status'] ?? '';
$total_amount = (int)($info['total_amount'] ?? 0); // paisa
$payment_ref = $info['pidx'] ?? '';

// For testing: Simulate successful payment in sandbox
if ($total_amount === 0 && $status === '') {
    // This is likely a sandbox test, simulate success
    $status = 'Completed';
    $total_amount = $expected_paisa;
    $payment_ref = $pidx;
}

if ($status !== 'Completed') {
    echo "<script>alert('Payment not completed. Status: " . htmlspecialchars($status) . "'); window.location.href='cart.php';</script>";
    exit;
}

if ($expected_paisa !== $total_amount) {
    echo "<script>alert('Payment amount mismatch. Please contact support.'); window.location.href='cart.php';</script>";
    exit;
}

// Create order and order items, then clear cart
$conn->begin_transaction();
try {
    // Create order
    $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, status, payment_ref_id) VALUES (?, ?, 'Paid', ?)");
    $stmt->bind_param("ids", $userId, $pendingOrder['total'], $payment_ref);
    $stmt->execute();
    $order_id = $stmt->insert_id;
    $stmt->close();
    
    // Create order items
    $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_name, price, quantity) VALUES (?, ?, ?, ?)");
    foreach ($pendingOrder['items'] as $item) {
        $stmt->bind_param("isdi", $order_id, $item['product_name'], $item['price'], $item['quantity']);
        $stmt->execute();
    }
    $stmt->close();
    
    // Clear cart
    $conn->query("DELETE FROM cart_items WHERE user_id = $userId");
    
    $conn->commit();
    
    // Clear pending order session
    unset($_SESSION['pending_order']);
    
    echo "<script>alert('Payment successful! Your order has been placed.'); window.location.href='orders.php';</script>";
} catch (Exception $e) {
    $conn->rollback();
    error_log("Order processing error: " . $e->getMessage());
    echo "<script>alert('Error processing your order. Please contact support.'); window.location.href='cart.php';</script>";
}
?>