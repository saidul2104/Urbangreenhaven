<?php
require_once 'config.php';

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create questions table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS questions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            question TEXT NOT NULL,
            created_at DATETIME NOT NULL,
            INDEX (created_at)
        )
    ");

    // Create answers table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS answers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            question_id INT NOT NULL,
            name VARCHAR(255) NOT NULL,
            answer TEXT NOT NULL,
            created_at DATETIME NOT NULL,
            FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
            INDEX (question_id),
            INDEX (created_at)
        )
    ");

    echo "Q&A tables created successfully!";
} catch (PDOException $e) {
    die("Error creating tables: " . $e->getMessage());
}
?> 