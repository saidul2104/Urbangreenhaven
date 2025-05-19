<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Check if required parameter is present
if (!isset($_POST['consultation_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing consultation ID']);
    exit;
}

require_once 'config.php';

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Delete consultation request
    $stmt = $pdo->prepare('DELETE FROM consultation_requests WHERE id = :consultation_id');
    $result = $stmt->execute([
        'consultation_id' => $_POST['consultation_id']
    ]);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Consultation request deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete consultation request']);
    }

} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?> 