<?php
$stats = getTestStatistics();
$performanceData = getTestPerformanceData();

// Get recent test attempts
$pdo = getDatabase();
$stmt = $pdo->query("
    SELECT ta.*, t.title as test_title 
    FROM test_attempts ta 
    JOIN tests t ON ta.test_id = t.id 
    WHERE ta.completed_at IS NOT NULL 
    ORDER BY ta.completed_at DESC 
    LIMIT 10
");
$recentAttempts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get question performance
$stmt = $pdo->query("
    SELECT q.question_text, qg.name as group_name,
           COUNT(ta_ans.id) as total_answers,
           SUM(CASE WHEN ta_ans.is_correct = 1 THEN 1 ELSE 0 END) as correct_answers,
           ROUND(AVG(CASE WHEN ta_ans.is_correct = 1 THEN 100.0 ELSE 0.0 END), 1) as success_rate
    FROM questions q
    LEFT JOIN question_groups qg ON q.group_id = qg.id
    LEFT JOIN test_answers ta_ans ON q.id = ta_ans.question_id
    GROUP BY q.id, q.question_text, qg.name
    HAVING total_answers > 0
    ORDER BY success_rate ASC, total_answers DESC
    LIMIT 10
");
$questionPerformance = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="space-y-6">
    <!-- Header -->
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Statistics</h1>
        <p class="text-gray-600 mt-1">Analyze test performance and insights</p>
    </div>

    <!-- Overview Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow-sm p-6 card-hover">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100">
                    <i data-lucide="file-text" class="w-6 h-6 text-blue-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Tests</p>
                    <p class="text-2xl font-bold text-gray-900"><?= $stats['total_tests'] ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6 card-hover">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100">
                    <i data-lucide="help-circle" class="w-6 h-6 text-green-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Questions</p>
                    <p class="text-2xl font-bold text-gray-900"><?= $stats['total_questions'] ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6 card-hover">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100">
                    <i data-lucide="users" class="w-6 h-6 text-purple-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Attempts</p>
                    <p class="text-2xl font-bold text-gray-900"><?= $stats['total_attempts'] ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6 card-hover">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100">
                    <i data-lucide="trending-up" class="w-6 h-6 text-yellow-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Average Score</p>
                    <p class="text-2xl font-bold text-gray-900"><?= $stats['average_score'] ?? 0 ?>%</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Test Performance Chart -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Test Performance</h2>
        
        <?php if (empty($performanceData)): ?>
            <div class="text-center py-8">
                <i data-lucide="bar-chart" class="w-12 h-12 text-gray-400 mx-auto mb-4"></i>
                <p class="text-gray-600">No test data available yet</p>
            </div>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($performanceData as $data): ?>
                    <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                        <div class="flex-1">
                            <h3 class="font-medium text-gray-900"><?= htmlspecialchars($data['title']) ?></h3>
                            <p class="text-sm text-gray-600"><?= $data['attempts'] ?> attempts</p>
                        </div>
                        <div class="flex items-center space-x-4">
                            <div class="text-right">
                                <p class="text-lg font-semibold text-gray-900"><?= round($data['avg_score'] ?? 0, 1) ?>%</p>
                                <p class="text-xs text-gray-600">Average Score</p>
                            </div>
                            <div class="w-32 bg-gray-200 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full" style="width: <?= min(100, $data['avg_score'] ?? 0) ?>%"></div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Question Performance -->
    <?php if (!empty($questionPerformance)): ?>
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Most Challenging Questions</h2>
            <div class="space-y-4">
                <?php foreach ($questionPerformance as $question): ?>
                    <div class="p-4 border border-gray-200 rounded-lg">
                        <div class="flex justify-between items-start mb-2">
                            <div class="flex-1">
                                <p class="font-medium text-gray-900"><?= htmlspecialchars($question['question_text']) ?></p>
                                <p class="text-sm text-gray-600">
                                    <?= htmlspecialchars($question['group_name'] ?: 'Uncategorized') ?> • 
                                    <?= $question['total_answers'] ?> answers
                                </p>
                            </div>
                            <div class="text-right ml-4">
                                <p class="text-lg font-semibold <?= $question['success_rate'] < 50 ? 'text-red-600' : ($question['success_rate'] < 75 ? 'text-yellow-600' : 'text-green-600') ?>">
                                    <?= $question['success_rate'] ?>%
                                </p>
                                <p class="text-xs text-gray-600">Success Rate</p>
                            </div>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="<?= $question['success_rate'] < 50 ? 'bg-red-500' : ($question['success_rate'] < 75 ? 'bg-yellow-500' : 'bg-green-500') ?> h-2 rounded-full" 
                                 style="width: <?= $question['success_rate'] ?>%"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Recent Test Attempts -->
    <?php if (!empty($recentAttempts)): ?>
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Recent Test Attempts</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Participant</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Test</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Score</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time Taken</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Completed</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($recentAttempts as $attempt): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($attempt['participant_name']) ?></div>
                                        <div class="text-sm text-gray-500"><?= htmlspecialchars($attempt['participant_email']) ?></div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= htmlspecialchars($attempt['test_title']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <span class="text-sm font-medium text-gray-900">
                                            <?= $attempt['score'] ?>/<?= $attempt['total_questions'] ?>
                                        </span>
                                        <span class="ml-2 text-xs text-gray-500">
                                            (<?= round(($attempt['score'] / max(1, $attempt['total_questions'])) * 100, 1) ?>%)
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= formatTime($attempt['time_taken']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= date('M j, Y g:i A', strtotime($attempt['completed_at'])) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    lucide.createIcons();
</script>