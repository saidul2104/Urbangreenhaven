<?php
require_once 'config.php';

// Fetch recent questions with their answers
$stmt = $pdo->query("
    SELECT q.*, 
           COUNT(a.id) as answer_count 
    FROM questions q 
    LEFT JOIN answers a ON q.id = a.question_id 
    GROUP BY q.id 
    ORDER BY q.created_at DESC 
    LIMIT 5
");
$recent_questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Function to get answers for a question
function getAnswers($pdo, $question_id) {
    $stmt = $pdo->prepare("
        SELECT * FROM answers 
        WHERE question_id = ? 
        ORDER BY created_at ASC
    ");
    $stmt->execute([$question_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Q&A - Urban Green Haven</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <!-- Keep the existing navigation -->
    
    <!-- Add this after the navigation -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline"><?php echo $_SESSION['success']; ?></span>
                <?php unset($_SESSION['success']); ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline"><?php echo $_SESSION['error']; ?></span>
                <?php unset($_SESSION['error']); ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Keep the existing content until the Recent Questions section -->
    
    <!-- Replace the Recent Questions section with this: -->
    <div>
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Recent Questions & Answers</h2>
        <div class="space-y-6">
            <?php foreach ($recent_questions as $question): ?>
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800"><?php echo htmlspecialchars($question['question']); ?></h3>
                            <p class="text-sm text-gray-500 mt-1">
                                Asked by <?php echo htmlspecialchars($question['name']); ?> on 
                                <?php echo date('F j, Y', strtotime($question['created_at'])); ?>
                            </p>
                        </div>
                        <span class="bg-primary-100 text-primary-800 text-xs font-medium px-2.5 py-0.5 rounded">
                            <?php echo $question['answer_count']; ?> Answer<?php echo $question['answer_count'] != 1 ? 's' : ''; ?>
                        </span>
                    </div>

                    <!-- Answers -->
                    <?php 
                    $answers = getAnswers($pdo, $question['id']);
                    if ($answers): 
                    ?>
                        <div class="space-y-4 mt-4">
                            <?php foreach ($answers as $answer): ?>
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($answer['answer'])); ?></p>
                                    <p class="text-sm text-gray-500 mt-2">
                                        Answered by <?php echo htmlspecialchars($answer['name']); ?> on 
                                        <?php echo date('F j, Y', strtotime($answer['created_at'])); ?>
                                    </p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Answer Form -->
                    <div class="mt-6">
                        <button onclick="toggleAnswerForm(<?php echo $question['id']; ?>)" 
                                class="text-primary-600 hover:text-primary-700 font-medium">
                            Write an Answer
                        </button>
                        <form id="answerForm<?php echo $question['id']; ?>" 
                              action="answer-process.php" 
                              method="POST" 
                              class="hidden mt-4 space-y-4">
                            <input type="hidden" name="question_id" value="<?php echo $question['id']; ?>">
                            <div>
                                <label for="answerName<?php echo $question['id']; ?>" class="block text-sm font-medium text-gray-700">Your Name*</label>
                                <input type="text" id="answerName<?php echo $question['id']; ?>" name="name" required
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500">
                            </div>
                            <div>
                                <label for="answerEmail<?php echo $question['id']; ?>" class="block text-sm font-medium text-gray-700">Your Email*</label>
                                <input type="email" id="answerEmail<?php echo $question['id']; ?>" name="email" required
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500">
                            </div>
                            <div>
                                <label for="answerText<?php echo $question['id']; ?>" class="block text-sm font-medium text-gray-700">Your Answer*</label>
                                <textarea id="answerText<?php echo $question['id']; ?>" name="answer" rows="4" required
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"></textarea>
                            </div>
                            <button type="submit"
                                class="bg-primary-600 hover:bg-primary-700 text-white font-medium py-2 px-4 rounded-md transition-colors duration-300">
                                Submit Answer
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Keep the existing sidebar and footer -->

    <!-- Add this to your existing scripts section -->
    <script>
        function toggleAnswerForm(questionId) {
            const form = document.getElementById('answerForm' + questionId);
            form.classList.toggle('hidden');
        }
    </script>
</body>
</html> 