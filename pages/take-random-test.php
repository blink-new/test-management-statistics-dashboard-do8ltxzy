<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

$db = getDatabase();

// Handle POST request to start random test
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['section'])) {
    $section = $_POST['section'];
    
    // Get 40 random questions from the selected section
    $stmt = $db->prepare("
        SELECT * FROM questions 
        WHERE question_group = ? 
        ORDER BY RANDOM() 
        LIMIT 40
    ");
    $stmt->execute([$section]);
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($questions) < 40) {
        die("Not enough questions in this section. Need 40, found " . count($questions));
    }
    
    // Store questions in session for the test
    $_SESSION['random_test'] = [
        'section' => $section,
        'questions' => $questions,
        'start_time' => time(),
        'time_limit' => 60 * 60, // 60 minutes
        'current_question' => 0,
        'answers' => []
    ];
    
    // Redirect to test interface
    header('Location: take-random-test.php');
    exit;
}

// Check if there's an active random test
if (!isset($_SESSION['random_test'])) {
    header('Location: student-panel.php');
    exit;
}

$test = $_SESSION['random_test'];
$currentQuestionIndex = $test['current_question'];
$totalQuestions = count($test['questions']);

// Handle answer submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['answer'])) {
    $answer = $_POST['answer'];
    $questionId = $test['questions'][$currentQuestionIndex]['id'];
    
    // Store the answer
    $_SESSION['random_test']['answers'][$questionId] = $answer;
    
    // Move to next question or finish test
    if ($currentQuestionIndex + 1 < $totalQuestions) {
        $_SESSION['random_test']['current_question']++;
        header('Location: take-random-test.php');
        exit;
    } else {
        // Test completed, calculate results
        $correct = 0;
        $total = count($test['questions']);
        
        foreach ($test['questions'] as $question) {
            $userAnswer = $_SESSION['random_test']['answers'][$question['id']] ?? '';
            if ($userAnswer === $question['correct_answer']) {
                $correct++;
            }
        }
        
        $score = ($correct / $total) * 100;
        $timeTaken = time() - $test['start_time'];
        
        // Save result to database
        $studentId = $_SESSION['student_id'] ?? 'demo_student';
        $stmt = $db->prepare("
            INSERT INTO test_results (test_id, student_id, score, correct_answers, total_questions, time_taken, completed_at)
            VALUES (?, ?, ?, ?, ?, ?, datetime('now'))
        ");
        $stmt->execute([
            'random_' . $test['section'],
            $studentId,
            $score,
            $correct,
            $total,
            $timeTaken
        ]);
        
        // Store results for display
        $_SESSION['test_result'] = [
            'section' => $test['section'],
            'score' => $score,
            'correct' => $correct,
            'total' => $total,
            'time_taken' => $timeTaken,
            'questions' => $test['questions'],
            'answers' => $test['answers']
        ];
        
        // Clear the test session
        unset($_SESSION['random_test']);
        
        // Redirect to results
        header('Location: random-test-results.php');
        exit;
    }
}

// Check time limit
$timeElapsed = time() - $test['start_time'];
$timeRemaining = $test['time_limit'] - $timeElapsed;

if ($timeRemaining <= 0) {
    // Time's up, auto-submit
    header('Location: take-random-test.php');
    $_POST['answer'] = ''; // Submit empty answer
}

$currentQuestion = $test['questions'][$currentQuestionIndex];
$progress = (($currentQuestionIndex + 1) / $totalQuestions) * 100;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Random Test - <?= htmlspecialchars($test['section']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <style>
        .progress-bar {
            transition: width 0.3s ease;
        }
        .question-card {
            animation: slideIn 0.3s ease-out;
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(20px); }
            to { opacity: 1; transform: translateX(0); }
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Header with Timer and Progress -->
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-4">
                    <div class="bg-blue-100 p-2 rounded-lg">
                        <i data-lucide="clock" class="w-6 h-6 text-blue-600"></i>
                    </div>
                    <div>
                        <h1 class="text-lg font-semibold text-gray-900"><?= htmlspecialchars($test['section']) ?> Practice Test</h1>
                        <p class="text-sm text-gray-600">Question <?= $currentQuestionIndex + 1 ?> of <?= $totalQuestions ?></p>
                    </div>
                </div>
                
                <div class="flex items-center space-x-6">
                    <!-- Timer -->
                    <div class="text-center">
                        <div id="timer" class="text-2xl font-bold text-gray-900"></div>
                        <p class="text-xs text-gray-600">Time Remaining</p>
                    </div>
                    
                    <!-- Progress -->
                    <div class="w-32">
                        <div class="flex justify-between text-xs text-gray-600 mb-1">
                            <span>Progress</span>
                            <span><?= number_format($progress, 0) ?>%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full progress-bar" style="width: <?= $progress ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Question Card -->
        <div class="bg-white rounded-xl shadow-sm question-card">
            <div class="p-8">
                <!-- Question -->
                <div class="mb-8">
                    <div class="flex items-start space-x-4">
                        <div class="bg-blue-100 text-blue-600 rounded-full w-8 h-8 flex items-center justify-center font-semibold text-sm">
                            <?= $currentQuestionIndex + 1 ?>
                        </div>
                        <div class="flex-1">
                            <h2 class="text-xl font-medium text-gray-900 leading-relaxed">
                                <?= htmlspecialchars($currentQuestion['question_text']) ?>
                            </h2>
                            <p class="text-sm text-gray-500 mt-2">
                                <i data-lucide="tag" class="w-4 h-4 inline mr-1"></i>
                                <?= htmlspecialchars($currentQuestion['question_group']) ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Answer Options -->
                <form method="POST" class="space-y-4">
                    <?php 
                    $options = ['A', 'B', 'C', 'D'];
                    $answers = [
                        $currentQuestion['option_a'],
                        $currentQuestion['option_b'], 
                        $currentQuestion['option_c'],
                        $currentQuestion['option_d']
                    ];
                    ?>
                    
                    <?php for ($i = 0; $i < 4; $i++): ?>
                    <label class="block cursor-pointer">
                        <div class="border-2 border-gray-200 rounded-lg p-4 hover:border-blue-300 hover:bg-blue-50 transition-all duration-200 group">
                            <div class="flex items-center space-x-4">
                                <input type="radio" name="answer" value="<?= $options[$i] ?>" 
                                       class="w-5 h-5 text-blue-600 border-gray-300 focus:ring-blue-500" required>
                                <div class="flex items-center space-x-3">
                                    <div class="bg-gray-100 group-hover:bg-blue-100 text-gray-600 group-hover:text-blue-600 rounded-full w-8 h-8 flex items-center justify-center font-semibold text-sm transition-colors">
                                        <?= $options[$i] ?>
                                    </div>
                                    <span class="text-gray-900 font-medium"><?= htmlspecialchars($answers[$i]) ?></span>
                                </div>
                            </div>
                        </div>
                    </label>
                    <?php endfor; ?>

                    <!-- Navigation Buttons -->
                    <div class="flex justify-between items-center pt-8 border-t border-gray-200">
                        <div class="text-sm text-gray-600">
                            <?php if ($currentQuestionIndex > 0): ?>
                            <span class="text-green-600 font-medium"><?= $currentQuestionIndex ?></span> answered, 
                            <?php endif; ?>
                            <span class="text-blue-600 font-medium"><?= $totalQuestions - $currentQuestionIndex - 1 ?></span> remaining
                        </div>
                        
                        <div class="flex space-x-4">
                            <?php if ($currentQuestionIndex < $totalQuestions - 1): ?>
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors flex items-center space-x-2">
                                <span>Next Question</span>
                                <i data-lucide="arrow-right" class="w-4 h-4"></i>
                            </button>
                            <?php else: ?>
                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium transition-colors flex items-center space-x-2">
                                <i data-lucide="check" class="w-4 h-4"></i>
                                <span>Finish Test</span>
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Quick Navigation -->
        <div class="mt-8 bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-sm font-medium text-gray-900 mb-4">Quick Navigation</h3>
            <div class="grid grid-cols-10 gap-2">
                <?php for ($i = 0; $i < $totalQuestions; $i++): ?>
                <div class="w-8 h-8 rounded flex items-center justify-center text-xs font-medium
                    <?php if ($i < $currentQuestionIndex): ?>
                        bg-green-100 text-green-600
                    <?php elseif ($i === $currentQuestionIndex): ?>
                        bg-blue-600 text-white
                    <?php else: ?>
                        bg-gray-100 text-gray-600
                    <?php endif; ?>">
                    <?= $i + 1 ?>
                </div>
                <?php endfor; ?>
            </div>
            <div class="flex items-center justify-center space-x-6 mt-4 text-xs text-gray-600">
                <div class="flex items-center space-x-2">
                    <div class="w-3 h-3 bg-green-100 rounded"></div>
                    <span>Answered</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-3 h-3 bg-blue-600 rounded"></div>
                    <span>Current</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-3 h-3 bg-gray-100 rounded"></div>
                    <span>Remaining</span>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Initialize Lucide icons
        lucide.createIcons();

        // Timer functionality
        let timeRemaining = <?= $timeRemaining ?>;
        
        function updateTimer() {
            if (timeRemaining <= 0) {
                document.getElementById('timer').textContent = '00:00';
                // Auto-submit the form
                document.querySelector('form').submit();
                return;
            }
            
            const hours = Math.floor(timeRemaining / 3600);
            const minutes = Math.floor((timeRemaining % 3600) / 60);
            const seconds = timeRemaining % 60;
            
            let display = '';
            if (hours > 0) {
                display = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            } else {
                display = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            }
            
            document.getElementById('timer').textContent = display;
            
            // Change color when time is running low
            const timerElement = document.getElementById('timer');
            if (timeRemaining < 300) { // Less than 5 minutes
                timerElement.classList.add('text-red-600');
                timerElement.classList.remove('text-gray-900');
            } else if (timeRemaining < 600) { // Less than 10 minutes
                timerElement.classList.add('text-yellow-600');
                timerElement.classList.remove('text-gray-900');
            }
            
            timeRemaining--;
        }
        
        // Update timer every second
        updateTimer();
        setInterval(updateTimer, 1000);

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.key >= '1' && e.key <= '4') {
                const options = ['A', 'B', 'C', 'D'];
                const optionIndex = parseInt(e.key) - 1;
                const radio = document.querySelector(`input[value="${options[optionIndex]}"]`);
                if (radio) {
                    radio.checked = true;
                }
            } else if (e.key === 'Enter' && e.ctrlKey) {
                // Ctrl+Enter to submit
                const checkedRadio = document.querySelector('input[name="answer"]:checked');
                if (checkedRadio) {
                    document.querySelector('form').submit();
                }
            }
        });

        // Prevent accidental page refresh
        window.addEventListener('beforeunload', function(e) {
            e.preventDefault();
            e.returnValue = '';
        });
    </script>
</body>
</html>