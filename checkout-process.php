<?php
session_start();
header('Content-Type: application/json');

require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$shipping_address = $_POST['shipping_address'] ?? '';
$phone = $_POST['phone'] ?? '';
$payment_method = $_POST['payment_method'] ?? '';
$transaction_number = $_POST['transaction_number'] ?? '';

if (empty($shipping_address) || empty($phone) || empty($payment_method) || empty($transaction_number)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit;
}

if (!in_array($payment_method, ['Bkash', 'Nagad', 'Rocket'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid payment method.']);
    exit;
}

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Start transaction
    $pdo->beginTransaction();
    
    // Get user information
    $stmt = $pdo->prepare('SELECT name, email FROM users WHERE id = :user_id');
    $stmt->execute(['user_id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get cart items
    $stmt = $pdo->prepare('
        SELECT c.*, p.price, p.name 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = :user_id
    ');
    $stmt->execute(['user_id' => $user_id]);
    $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($cart_items)) {
        throw new Exception('Cart is empty');
    }
    
    // Calculate total amount
    $total_amount = 0;
    foreach ($cart_items as $item) {
        $total_amount += $item['price'] * $item['quantity'];
    }
    
    // Add shipping cost
    $total_amount += 100; // ৳100 shipping cost
    
    // Create order with user information
    $stmt = $pdo->prepare('
        INSERT INTO orders (
            user_id, 
            total_amount, 
            shipping_address, 
            phone_number,
            customer_name,
            customer_email,
            payment_method,
            transaction_number,
            status
        ) VALUES (
            :user_id, 
            :total_amount, 
            :shipping_address,
            :phone_number,
            :customer_name,
            :customer_email,
            :payment_method,
            :transaction_number,
            :status
        )
    ');
    
    $stmt->execute([
        'user_id' => $user_id,
        'total_amount' => $total_amount,
        'shipping_address' => $shipping_address,
        'phone_number' => $phone,
        'customer_name' => $user['name'],
        'customer_email' => $user['email'],
        'payment_method' => $payment_method,
        'transaction_number' => $transaction_number,
        'status' => 'pending'
    ]);
    
    $order_id = $pdo->lastInsertId();
    
    // Create order items
    $stmt = $pdo->prepare('
        INSERT INTO order_items (order_id, product_id, quantity, price) 
        VALUES (:order_id, :product_id, :quantity, :price)
    ');
    
    foreach ($cart_items as $item) {
        $stmt->execute([
            'order_id' => $order_id,
            'product_id' => $item['product_id'],
            'quantity' => $item['quantity'],
            'price' => $item['price']
        ]);
    }
    
    // Clear cart
    $stmt = $pdo->prepare('DELETE FROM cart WHERE user_id = :user_id');
    $stmt->execute(['user_id' => $user_id]);
    
    // Commit transaction
    $pdo->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Order placed successfully',
        'order_id' => $order_id
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    echo json_encode([
        'success' => false, 
        'message' => 'Error placing order: ' . $e->getMessage()
    ]);
}
?> 