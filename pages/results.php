<?php
$attemptId = (int)($_GET['attempt'] ?? 0);

if (!$attemptId) {
    header("Location: ?page=tests");
    exit;
}

// Get attempt details
$pdo = getDatabase();
$stmt = $pdo->prepare("
    SELECT ta.*, t.title as test_title, t.description as test_description
    FROM test_attempts ta 
    JOIN tests t ON ta.test_id = t.id 
    WHERE ta.id = ?
");
$stmt->execute([$attemptId]);
$attempt = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$attempt) {
    header("Location: ?page=tests");
    exit;
}

// Get detailed answers
$stmt = $pdo->prepare("
    SELECT ta_ans.*, q.question_text, q.option_a, q.option_b, q.option_c, q.option_d, q.correct_answer, qg.name as group_name
    FROM test_answers ta_ans
    JOIN questions q ON ta_ans.question_id = q.id
    LEFT JOIN question_groups qg ON q.group_id = qg.id
    WHERE ta_ans.attempt_id = ?
    ORDER BY ta_ans.id
");
$stmt->execute([$attemptId]);
$answers = $stmt->fetchAll(PDO::FETCH_ASSOC);

$percentage = $attempt['total_questions'] > 0 ? round(($attempt['score'] / $attempt['total_questions']) * 100, 1) : 0;
?>

<div class="max-w-4xl mx-auto space-y-6">
    <!-- Results Header -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="text-center">
            <div class="mb-4">
                <?php if ($percentage >= 80): ?>
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="check-circle" class="w-8 h-8 text-green-600"></i>
                    </div>
                    <h1 class="text-3xl font-bold text-green-600">Excellent!</h1>
                <?php elseif ($percentage >= 60): ?>
                    <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="star" class="w-8 h-8 text-yellow-600"></i>
                    </div>
                    <h1 class="text-3xl font-bold text-yellow-600">Good Job!</h1>
                <?php else: ?>
                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="x-circle" class="w-8 h-8 text-red-600"></i>
                    </div>
                    <h1 class="text-3xl font-bold text-red-600">Keep Trying!</h1>
                <?php endif; ?>
            </div>
            
            <h2 class="text-xl text-gray-900 mb-2"><?= htmlspecialchars($attempt['test_title']) ?></h2>
            <p class="text-gray-600">Completed by <?= htmlspecialchars($attempt['participant_name']) ?></p>
        </div>
    </div>

    <!-- Score Summary -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow-sm p-6 text-center">
            <div class="text-3xl font-bold text-blue-600 mb-2"><?= $attempt['score'] ?></div>
            <p class="text-sm text-gray-600">Correct Answers</p>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm p-6 text-center">
            <div class="text-3xl font-bold text-gray-900 mb-2"><?= $attempt['total_questions'] ?></div>
            <p class="text-sm text-gray-600">Total Questions</p>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm p-6 text-center">
            <div class="text-3xl font-bold <?= $percentage >= 80 ? 'text-green-600' : ($percentage >= 60 ? 'text-yellow-600' : 'text-red-600') ?> mb-2">
                <?= $percentage ?>%
            </div>
            <p class="text-sm text-gray-600">Score</p>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm p-6 text-center">
            <div class="text-3xl font-bold text-purple-600 mb-2"><?= formatTime($attempt['time_taken']) ?></div>
            <p class="text-sm text-gray-600">Time Taken</p>
        </div>
    </div>

    <!-- Score Visualization -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Performance Breakdown</h3>
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-gray-700">Correct Answers</span>
                <span class="text-sm text-gray-600"><?= $attempt['score'] ?> out of <?= $attempt['total_questions'] ?></span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-4">
                <div class="<?= $percentage >= 80 ? 'bg-green-500' : ($percentage >= 60 ? 'bg-yellow-500' : 'bg-red-500') ?> h-4 rounded-full transition-all duration-500" 
                     style="width: <?= $percentage ?>%"></div>
            </div>
            <div class="flex justify-between text-xs text-gray-500">
                <span>0%</span>
                <span>50%</span>
                <span>100%</span>
            </div>
        </div>
    </div>

    <!-- Detailed Review -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Question Review</h3>
            <div class="flex items-center space-x-4 text-sm">
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                    <span class="text-gray-600">Correct</span>
                </div>
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-red-500 rounded-full mr-2"></div>
                    <span class="text-gray-600">Incorrect</span>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <?php foreach ($answers as $index => $answer): ?>
                <div class="border border-gray-200 rounded-lg p-6 <?= $answer['is_correct'] ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200' ?>">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center mr-3 <?= $answer['is_correct'] ? 'bg-green-500' : 'bg-red-500' ?>">
                                <span class="text-white font-medium text-sm"><?= $index + 1 ?></span>
                            </div>
                            <div>
                                <h4 class="font-medium text-gray-900">Question <?= $index + 1 ?></h4>
                                <?php if ($answer['group_name']): ?>
                                    <span class="text-xs text-gray-500"><?= htmlspecialchars($answer['group_name']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="flex items-center">
                            <?php if ($answer['is_correct']): ?>
                                <i data-lucide="check-circle" class="w-5 h-5 text-green-600"></i>
                            <?php else: ?>
                                <i data-lucide="x-circle" class="w-5 h-5 text-red-600"></i>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <p class="text-gray-900 mb-4"><?= htmlspecialchars($answer['question_text']) ?></p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <?php foreach (['a', 'b', 'c', 'd'] as $option): ?>
                            <?php 
                            $isSelected = ($answer['selected_answer'] === $option);
                            $isCorrect = ($answer['correct_answer'] === $option);
                            $classes = 'flex items-center p-3 rounded-lg border ';
                            
                            if ($isCorrect) {
                                $classes .= 'bg-green-100 border-green-300 text-green-800';
                            } elseif ($isSelected && !$isCorrect) {
                                $classes .= 'bg-red-100 border-red-300 text-red-800';
                            } else {
                                $classes .= 'bg-gray-50 border-gray-200 text-gray-700';
                            }
                            ?>
                            <div class="<?= $classes ?>">
                                <span class="font-medium mr-2"><?= strtoupper($option) ?>)</span>
                                <span class="flex-1"><?= htmlspecialchars($answer['option_' . $option]) ?></span>
                                <?php if ($isSelected): ?>
                                    <i data-lucide="user" class="w-4 h-4 ml-2" title="Your answer"></i>
                                <?php endif; ?>
                                <?php if ($isCorrect): ?>
                                    <i data-lucide="check" class="w-4 h-4 ml-2" title="Correct answer"></i>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if (!$answer['is_correct']): ?>
                        <div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                            <p class="text-sm text-blue-800">
                                <strong>Correct answer:</strong> <?= strtoupper($answer['correct_answer']) ?>) 
                                <?= htmlspecialchars($answer['option_' . $answer['correct_answer']]) ?>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Actions -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex justify-center space-x-4">
            <a href="?page=take-test&id=<?= $attempt['test_id'] ?>" class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <i data-lucide="refresh-cw" class="w-4 h-4 mr-2"></i>
                Retake Test
            </a>
            <a href="?page=tests" class="inline-flex items-center px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
                Back to Tests
            </a>
        </div>
    </div>

    <!-- Test Details -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Test Details</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div>
                <span class="font-medium text-gray-700">Participant:</span>
                <span class="text-gray-900 ml-2"><?= htmlspecialchars($attempt['participant_name']) ?></span>
            </div>
            <div>
                <span class="font-medium text-gray-700">Email:</span>
                <span class="text-gray-900 ml-2"><?= htmlspecialchars($attempt['participant_email']) ?></span>
            </div>
            <div>
                <span class="font-medium text-gray-700">Started:</span>
                <span class="text-gray-900 ml-2"><?= date('M j, Y g:i A', strtotime($attempt['started_at'])) ?></span>
            </div>
            <div>
                <span class="font-medium text-gray-700">Completed:</span>
                <span class="text-gray-900 ml-2"><?= date('M j, Y g:i A', strtotime($attempt['completed_at'])) ?></span>
            </div>
        </div>
    </div>
</div>

<script>
    lucide.createIcons();
</script>