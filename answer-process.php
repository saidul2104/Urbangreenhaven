<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $question_id = filter_input(INPUT_POST, 'question_id', FILTER_VALIDATE_INT);
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $answer = filter_input(INPUT_POST, 'answer', FILTER_SANITIZE_STRING);

    if (!$question_id || !$name || !$email || !$answer) {
        $_SESSION['error'] = "All fields are required.";
        header('Location: qa.php');
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format.";
        header('Location: qa.php');
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO answers (question_id, name, email, answer) VALUES (?, ?, ?, ?)");
        $stmt->execute([$question_id, $name, $email, $answer]);
        
        $_SESSION['success'] = "Your answer has been submitted successfully.";
        header('Location: qa.php');
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = "An error occurred. Please try again later.";
        header('Location: qa.php');
        exit;
    }
} else {
    header('Location: qa.php');
    exit;
} 