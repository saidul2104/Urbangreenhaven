<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Check if required parameters are present
if (!isset($_POST['consultation_id']) || !isset($_POST['status'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

require_once 'config.php';

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Validate status
    $valid_statuses = ['pending', 'accepted', 'completed', 'cancelled'];
    $status = $_POST['status'];
    
    if (!in_array($status, $valid_statuses)) {
        echo json_encode(['success' => false, 'message' => 'Invalid status']);
        exit;
    }

    // Update consultation status
    $stmt = $pdo->prepare('UPDATE consultation_requests SET status = :status WHERE id = :consultation_id');
    $result = $stmt->execute([
        'status' => $status,
        'consultation_id' => $_POST['consultation_id']
    ]);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Consultation status updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update consultation status']);
    }

} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?> 