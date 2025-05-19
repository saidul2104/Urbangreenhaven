<?php
header('Content-Type: application/json');
require_once 'config.php';

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Handle GET request to fetch questions
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_questions') {
        $stmt = $pdo->query('
            SELECT q.*, 
            GROUP_CONCAT(
                JSON_OBJECT(
                    "id", a.id,
                    "answer", a.answer,
                    "name", a.name,
                    "created_at", a.created_at
                )
            ) as answers
            FROM questions q
            LEFT JOIN answers a ON q.id = a.question_id
            GROUP BY q.id
            ORDER BY q.created_at DESC
            LIMIT 10
        ');
        
        $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Process answers JSON
        foreach ($questions as &$question) {
            $question['answers'] = $question['answers'] ? json_decode('[' . $question['answers'] . ']', true) : [];
        }
        
        echo json_encode(['success' => true, 'questions' => $questions]);
        exit;
    }

    // Handle POST request to submit question or answer
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Submit new question
        if (isset($_POST['question'])) {
            // Validate input
            $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $question = filter_input(INPUT_POST, 'question', FILTER_SANITIZE_STRING);

            if (!$name || !$email || !$question) {
                echo json_encode(['success' => false, 'message' => 'All fields are required.']);
                exit;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo json_encode(['success' => false, 'message' => 'Invalid email format.']);
                exit;
            }

            $stmt = $pdo->prepare('
                INSERT INTO questions (name, email, question, created_at)
                VALUES (:name, :email, :question, NOW())
            ');
            
            $stmt->execute([
                'name' => $name,
                'email' => $email,
                'question' => $question
            ]);
            
            $questionId = $pdo->lastInsertId();
            
            // Fetch the newly created question
            $stmt = $pdo->prepare('SELECT * FROM questions WHERE id = ?');
            $stmt->execute([$questionId]);
            $question = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'question' => $question]);
            exit;
        }
        
        // Submit new answer
        if (isset($_POST['answer']) && isset($_POST['question_id'])) {
            // Validate input
            $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
            $answer = filter_input(INPUT_POST, 'answer', FILTER_SANITIZE_STRING);
            $question_id = filter_input(INPUT_POST, 'question_id', FILTER_VALIDATE_INT);

            if (!$name || !$answer || !$question_id) {
                echo json_encode(['success' => false, 'message' => 'All fields are required.']);
                exit;
            }

            $stmt = $pdo->prepare('
                INSERT INTO answers (question_id, name, answer, created_at)
                VALUES (:question_id, :name, :answer, NOW())
            ');
            
            $stmt->execute([
                'question_id' => $question_id,
                'name' => $name,
                'answer' => $answer
            ]);
            
            $answerId = $pdo->lastInsertId();
            
            // Fetch the newly created answer
            $stmt = $pdo->prepare('SELECT * FROM answers WHERE id = ?');
            $stmt->execute([$answerId]);
            $answer = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'answer' => $answer]);
            exit;
        }
    }

    echo json_encode(['success' => false, 'message' => 'Invalid request']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>