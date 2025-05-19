<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

require_once 'config.php';

if (!isset($_POST['order_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing order ID']);
    exit;
}

$order_id = filter_var($_POST['order_id'], FILTER_VALIDATE_INT);

if (!$order_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
    exit;
}

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Start transaction
    $pdo->beginTransaction();

    // Delete order items first (due to foreign key constraint)
    $stmt = $pdo->prepare('DELETE FROM order_items WHERE order_id = :order_id');
    $stmt->execute(['order_id' => $order_id]);

    // Delete the order
    $stmt = $pdo->prepare('DELETE FROM orders WHERE id = :order_id');
    $stmt->execute(['order_id' => $order_id]);

    // Commit transaction
    $pdo->commit();

    echo json_encode(['success' => true, 'message' => 'Order deleted successfully']);
} catch (PDOException $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?> 