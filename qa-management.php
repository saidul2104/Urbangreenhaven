<?php
require_once '../config.php';
require_once 'auth.php';

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['id'])) {
        $id = $_POST['id'];
        $action = $_POST['action'];
        $type = $_POST['type']; // 'question' or 'answer'
        
        try {
            if ($type === 'question') {
                $stmt = $pdo->prepare("UPDATE questions SET status = ? WHERE id = ?");
            } else {
                $stmt = $pdo->prepare("UPDATE answers SET status = ? WHERE id = ?");
            }
            $stmt->execute([$action, $id]);
            $_SESSION['success'] = "Status updated successfully.";
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error updating status.";
        }
        header('Location: qa-management.php');
        exit;
    }
}

// Fetch pending questions
$stmt = $pdo->query("
    SELECT q.*, COUNT(a.id) as answer_count 
    FROM questions q 
    LEFT JOIN answers a ON q.id = a.question_id 
    WHERE q.status = 'pending' 
    GROUP BY q.id 
    ORDER BY q.created_at DESC
");
$pending_questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch pending answers
$stmt = $pdo->query("
    SELECT a.*, q.question 
    FROM answers a 
    JOIN questions q ON a.question_id = q.id 
    WHERE a.status = 'pending' 
    ORDER BY a.created_at DESC
");
$pending_answers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Q&A Management - Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <?php include 'admin-header.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold mb-8">Q&A Management</h1>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo $_SESSION['success']; ?></span>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo $_SESSION['error']; ?></span>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <!-- Pending Questions -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-xl font-semibold mb-4">Pending Questions</h2>
            <?php if (empty($pending_questions)): ?>
                <p class="text-gray-500">No pending questions.</p>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($pending_questions as $question): ?>
                        <div class="border rounded-lg p-4">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="font-medium"><?php echo htmlspecialchars($question['question']); ?></p>
                                    <p class="text-sm text-gray-500 mt-1">
                                        Asked by <?php echo htmlspecialchars($question['name']); ?> 
                                        (<?php echo htmlspecialchars($question['email']); ?>) on 
                                        <?php echo date('F j, Y', strtotime($question['created_at'])); ?>
                                    </p>
                                </div>
                                <div class="flex space-x-2">
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="id" value="<?php echo $question['id']; ?>">
                                        <input type="hidden" name="type" value="question">
                                        <input type="hidden" name="action" value="approved">
                                        <button type="submit" class="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600">
                                            Approve
                                        </button>
                                    </form>
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="id" value="<?php echo $question['id']; ?>">
                                        <input type="hidden" name="type" value="question">
                                        <input type="hidden" name="action" value="rejected">
                                        <button type="submit" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">
                                            Reject
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Pending Answers -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Pending Answers</h2>
            <?php if (empty($pending_answers)): ?>
                <p class="text-gray-500">No pending answers.</p>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($pending_answers as $answer): ?>
                        <div class="border rounded-lg p-4">
                            <div class="mb-2">
                                <p class="text-sm text-gray-500">For question: <?php echo htmlspecialchars($answer['question']); ?></p>
                            </div>
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="font-medium"><?php echo htmlspecialchars($answer['answer']); ?></p>
                                    <p class="text-sm text-gray-500 mt-1">
                                        Answered by <?php echo htmlspecialchars($answer['name']); ?> 
                                        (<?php echo htmlspecialchars($answer['email']); ?>) on 
                                        <?php echo date('F j, Y', strtotime($answer['created_at'])); ?>
                                    </p>
                                </div>
                                <div class="flex space-x-2">
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="id" value="<?php echo $answer['id']; ?>">
                                        <input type="hidden" name="type" value="answer">
                                        <input type="hidden" name="action" value="approved">
                                        <button type="submit" class="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600">
                                            Approve
                                        </button>
                                    </form>
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="id" value="<?php echo $answer['id']; ?>">
                                        <input type="hidden" name="type" value="answer">
                                        <input type="hidden" name="action" value="rejected">
                                        <button type="submit" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">
                                            Reject
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 