<?php
require_once 'config.php';

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $email = 'admin@urbangreenhaven.com';
    $name = 'Admin User';
    $phone = '0000000000';
    $password = 'Admin123!';
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $is_admin = 1;

    // Check if admin user already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingUser) {
        echo "Admin user already exists with email: $email";
    } else {
        $insertStmt = $pdo->prepare("INSERT INTO users (name, email, phone, password_hash, is_admin, created_at) VALUES (:name, :email, :phone, :password_hash, :is_admin, NOW())");
        $insertStmt->execute([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'password_hash' => $password_hash,
            'is_admin' => $is_admin
        ]);
        echo "Admin user created successfully. Email: $email, Password: $password";
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>