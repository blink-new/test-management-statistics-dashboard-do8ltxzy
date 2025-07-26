<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if there are test results to display
if (!isset($_SESSION['test_result'])) {
    header('Location: student-panel.php');
    exit;
}

$result = $_SESSION['test_result'];
$questions = $result['questions'];
$userAnswers = $result['answers'];

// Calculate performance metrics
$scorePercentage = $result['score'];
$correctCount = $result['correct'];
$totalCount = $result['total'];
$timeTaken = $result['time_taken'];

// Determine performance level
$performanceLevel = '';
$performanceColor = '';
$performanceIcon = '';

if ($scorePercentage >= 90) {
    $performanceLevel = 'Excellent';
    $performanceColor = 'text-green-600';
    $performanceIcon = 'trophy';
} elseif ($scorePercentage >= 80) {
    $performanceLevel = 'Very Good';
    $performanceColor = 'text-blue-600';
    $performanceIcon = 'star';
} elseif ($scorePercentage >= 70) {
    $performanceLevel = 'Good';
    $performanceColor = 'text-yellow-600';
    $performanceIcon = 'thumbs-up';
} elseif ($scorePercentage >= 60) {
    $performanceLevel = 'Fair';
    $performanceColor = 'text-orange-600';
    $performanceIcon = 'meh';
} else {
    $performanceLevel = 'Needs Improvement';
    $performanceColor = 'text-red-600';
    $performanceIcon = 'alert-circle';
}

// Format time taken
$hours = floor($timeTaken / 3600);
$minutes = floor(($timeTaken % 3600) / 60);
$seconds = $timeTaken % 60;
$timeFormatted = '';
if ($hours > 0) {
    $timeFormatted = sprintf('%dh %dm %ds', $hours, $minutes, $seconds);
} elseif ($minutes > 0) {
    $timeFormatted = sprintf('%dm %ds', $minutes, $seconds);
} else {
    $timeFormatted = sprintf('%ds', $seconds);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Results - <?= htmlspecialchars($result['section']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <style>
        .result-card {
            animation: slideUp 0.6s ease-out;
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .score-circle {
            background: conic-gradient(#3b82f6 <?= $scorePercentage * 3.6 ?>deg, #e5e7eb 0deg);
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div class="flex items-center space-x-4">
                    <div class="bg-green-100 p-2 rounded-lg">
                        <i data-lucide="check-circle" class="w-8 h-8 text-green-600"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Test Completed!</h1>
                        <p class="text-gray-600"><?= htmlspecialchars($result['section']) ?> Practice Test Results</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="student-panel.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                        <i data-lucide="arrow-left" class="w-4 h-4 inline mr-2"></i>
                        Back to Panel
                    </a>
                </div>
            </div>
        </div>
    </header>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Results Summary -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
            <!-- Score Card -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm p-8 text-center result-card">
                    <div class="relative w-32 h-32 mx-auto mb-6">
                        <div class="score-circle w-32 h-32 rounded-full flex items-center justify-center">
                            <div class="bg-white w-24 h-24 rounded-full flex items-center justify-center">
                                <div class="text-center">
                                    <div class="text-3xl font-bold text-gray-900"><?= number_format($scorePercentage, 0) ?>%</div>
                                    <div class="text-xs text-gray-600">Score</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <h3 class="text-xl font-semibold <?= $performanceColor ?> mb-2">
                        <i data-lucide="<?= $performanceIcon ?>" class="w-5 h-5 inline mr-2"></i>
                        <?= $performanceLevel ?>
                    </h3>
                    <p class="text-gray-600"><?= $correctCount ?> out of <?= $totalCount ?> questions correct</p>
                </div>
            </div>

            <!-- Statistics -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm p-8 result-card">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">Performance Statistics</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                        <div class="text-center">
                            <div class="bg-blue-100 p-3 rounded-lg mb-3 mx-auto w-fit">
                                <i data-lucide="target" class="w-6 h-6 text-blue-600"></i>
                            </div>
                            <div class="text-2xl font-bold text-gray-900"><?= $correctCount ?></div>
                            <div class="text-sm text-gray-600">Correct</div>
                        </div>
                        <div class="text-center">
                            <div class="bg-red-100 p-3 rounded-lg mb-3 mx-auto w-fit">
                                <i data-lucide="x" class="w-6 h-6 text-red-600"></i>
                            </div>
                            <div class="text-2xl font-bold text-gray-900"><?= $totalCount - $correctCount ?></div>
                            <div class="text-sm text-gray-600">Incorrect</div>
                        </div>
                        <div class="text-center">
                            <div class="bg-purple-100 p-3 rounded-lg mb-3 mx-auto w-fit">
                                <i data-lucide="clock" class="w-6 h-6 text-purple-600"></i>
                            </div>
                            <div class="text-2xl font-bold text-gray-900"><?= $timeFormatted ?></div>
                            <div class="text-sm text-gray-600">Time Taken</div>
                        </div>
                        <div class="text-center">
                            <div class="bg-green-100 p-3 rounded-lg mb-3 mx-auto w-fit">
                                <i data-lucide="zap" class="w-6 h-6 text-green-600"></i>
                            </div>
                            <div class="text-2xl font-bold text-gray-900"><?= number_format($timeTaken / $totalCount, 1) ?>s</div>
                            <div class="text-sm text-gray-600">Avg/Question</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-8">
            <div class="flex flex-wrap gap-4 justify-center">
                <a href="student-panel.php" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors flex items-center space-x-2">
                    <i data-lucide="repeat" class="w-4 h-4"></i>
                    <span>Take Another Test</span>
                </a>
                <button onclick="showDetailedReview()" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg font-medium transition-colors flex items-center space-x-2">
                    <i data-lucide="eye" class="w-4 h-4"></i>
                    <span>Review Answers</span>
                </button>
                <button onclick="window.print()" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium transition-colors flex items-center space-x-2">
                    <i data-lucide="printer" class="w-4 h-4"></i>
                    <span>Print Results</span>
                </button>
            </div>
        </div>

        <!-- Detailed Review (Hidden by default) -->
        <div id="detailedReview" class="hidden">
            <div class="bg-white rounded-xl shadow-sm">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-xl font-semibold text-gray-900">Detailed Answer Review</h3>
                    <p class="text-gray-600 mt-1">Review all questions and see the correct answers</p>
                </div>
                <div class="p-6">
                    <div class="space-y-8">
                        <?php foreach ($questions as $index => $question): ?>
                        <?php 
                        $userAnswer = $userAnswers[$question['id']] ?? '';
                        $correctAnswer = $question['correct_answer'];
                        $isCorrect = $userAnswer === $correctAnswer;
                        $options = ['A', 'B', 'C', 'D'];
                        $answers = [
                            $question['option_a'],
                            $question['option_b'], 
                            $question['option_c'],
                            $question['option_d']
                        ];
                        ?>
                        <div class="border border-gray-200 rounded-lg p-6">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-start space-x-4">
                                    <div class="<?= $isCorrect ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' ?> rounded-full w-8 h-8 flex items-center justify-center font-semibold text-sm">
                                        <?= $index + 1 ?>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="text-lg font-medium text-gray-900 mb-2">
                                            <?= htmlspecialchars($question['question_text']) ?>
                                        </h4>
                                        <p class="text-sm text-gray-500">
                                            <i data-lucide="tag" class="w-4 h-4 inline mr-1"></i>
                                            <?= htmlspecialchars($question['question_group']) ?>
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <?php if ($isCorrect): ?>
                                    <i data-lucide="check-circle" class="w-6 h-6 text-green-600"></i>
                                    <span class="text-green-600 font-medium">Correct</span>
                                    <?php else: ?>
                                    <i data-lucide="x-circle" class="w-6 h-6 text-red-600"></i>
                                    <span class="text-red-600 font-medium">Incorrect</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <?php for ($i = 0; $i < 4; $i++): ?>
                                <?php 
                                $optionLetter = $options[$i];
                                $isUserAnswer = $userAnswer === $optionLetter;
                                $isCorrectAnswer = $correctAnswer === $optionLetter;
                                
                                $optionClass = 'border-gray-200 bg-white';
                                if ($isCorrectAnswer) {
                                    $optionClass = 'border-green-300 bg-green-50';
                                } elseif ($isUserAnswer && !$isCorrect) {
                                    $optionClass = 'border-red-300 bg-red-50';
                                }
                                ?>
                                <div class="border-2 <?= $optionClass ?> rounded-lg p-3">
                                    <div class="flex items-center space-x-3">
                                        <div class="flex items-center space-x-2">
                                            <?php if ($isCorrectAnswer): ?>
                                            <i data-lucide="check" class="w-4 h-4 text-green-600"></i>
                                            <?php elseif ($isUserAnswer && !$isCorrect): ?>
                                            <i data-lucide="x" class="w-4 h-4 text-red-600"></i>
                                            <?php endif; ?>
                                            <div class="bg-gray-100 text-gray-600 rounded-full w-6 h-6 flex items-center justify-center font-semibold text-xs">
                                                <?= $optionLetter ?>
                                            </div>
                                        </div>
                                        <span class="text-gray-900 text-sm"><?= htmlspecialchars($answers[$i]) ?></span>
                                    </div>
                                </div>
                                <?php endfor; ?>
                            </div>

                            <?php if (!$isCorrect): ?>
                            <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                <div class="flex items-start space-x-2">
                                    <i data-lucide="info" class="w-4 h-4 text-blue-600 mt-0.5"></i>
                                    <div class="text-sm text-blue-800">
                                        <span class="font-medium">Your answer:</span> <?= $userAnswer ? $userAnswer . ' - ' . htmlspecialchars($answers[array_search($userAnswer, $options)]) : 'No answer selected' ?>
                                        <br>
                                        <span class="font-medium">Correct answer:</span> <?= $correctAnswer ?> - <?= htmlspecialchars($answers[array_search($correctAnswer, $options)]) ?>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Initialize Lucide icons
        lucide.createIcons();

        function showDetailedReview() {
            const reviewSection = document.getElementById('detailedReview');
            reviewSection.classList.toggle('hidden');
            
            // Scroll to review section
            if (!reviewSection.classList.contains('hidden')) {
                reviewSection.scrollIntoView({ behavior: 'smooth' });
            }
        }

        // Clear test result from session after viewing
        <?php unset($_SESSION['test_result']); ?>
    </script>
</body>
</html>