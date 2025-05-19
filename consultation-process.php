<?php
session_start();
header('Content-Type: application/json');

require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$user_id = $_SESSION['user_id'] ?? null;

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$address = trim($_POST['address'] ?? '');
$consultation_type = trim($_POST['consultation_type'] ?? '');
$consultation_price = trim($_POST['consultation_price'] ?? '');
$consultation_date = trim($_POST['consultation_date'] ?? '');
$consultation_time = trim($_POST['consultation_time'] ?? '');
$notes = trim($_POST['notes'] ?? '');
$payment_method = trim($_POST['payment_method'] ?? '');
$transaction_number = trim($_POST['transaction_number'] ?? '');
$terms_agreed = isset($_POST['terms']) ? true : false;

// Basic validation
if (!$name || !$email || !$phone || !$address || !$consultation_type || !$consultation_price || !$consultation_date || !$consultation_time || !$payment_method || !$terms_agreed) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields and agree to the terms.']);
    exit;
}

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare('INSERT INTO consultation_requests (user_id, name, email, phone, address, consultation_type, consultation_price, consultation_date, consultation_time, notes, payment_method, transaction_number, terms_agreed) VALUES (:user_id, :name, :email, :phone, :address, :consultation_type, :consultation_price, :consultation_date, :consultation_time, :notes, :payment_method, :transaction_number, :terms_agreed)');
    $stmt->execute([
        'user_id' => $user_id,
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'address' => $address,
        'consultation_type' => $consultation_type,
        'consultation_price' => $consultation_price,
        'consultation_date' => $consultation_date,
        'consultation_time' => $consultation_time,
        'notes' => $notes,
        'payment_method' => $payment_method,
        'transaction_number' => $transaction_number,
        'terms_agreed' => $terms_agreed ? 1 : 0
    ]);

    echo json_encode(['success' => true, 'message' => 'Consultation request submitted successfully.']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>