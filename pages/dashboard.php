<?php
$stats = getTestStatistics();
$recentTests = array_slice(getAllTests(), 0, 5);
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Dashboard</h1>
        <p class="text-gray-600">Welcome to the Test Management System</p>
    </div>

    <!-- Statistics Cards -->
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

    <!-- Quick Actions -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Quick Actions</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="?page=create-test" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <i data-lucide="plus" class="w-5 h-5 text-blue-600"></i>
                </div>
                <div class="ml-3">
                    <p class="font-medium text-gray-900">Create New Test</p>
                    <p class="text-sm text-gray-600">Build a new test with questions</p>
                </div>
            </a>

            <a href="?page=questions" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                <div class="p-2 bg-green-100 rounded-lg">
                    <i data-lucide="edit" class="w-5 h-5 text-green-600"></i>
                </div>
                <div class="ml-3">
                    <p class="font-medium text-gray-900">Manage Questions</p>
                    <p class="text-sm text-gray-600">Add or edit questions</p>
                </div>
            </a>

            <a href="?page=statistics" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                <div class="p-2 bg-purple-100 rounded-lg">
                    <i data-lucide="bar-chart" class="w-5 h-5 text-purple-600"></i>
                </div>
                <div class="ml-3">
                    <p class="font-medium text-gray-900">View Statistics</p>
                    <p class="text-sm text-gray-600">Analyze test performance</p>
                </div>
            </a>
        </div>
    </div>

    <!-- Recent Tests -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold text-gray-900">Recent Tests</h2>
            <a href="?page=tests" class="text-blue-600 hover:text-blue-800 text-sm font-medium">View All</a>
        </div>
        
        <?php if (empty($recentTests)): ?>
            <div class="text-center py-8">
                <i data-lucide="file-text" class="w-12 h-12 text-gray-400 mx-auto mb-4"></i>
                <p class="text-gray-600">No tests created yet</p>
                <a href="?page=create-test" class="inline-flex items-center mt-2 text-blue-600 hover:text-blue-800">
                    <i data-lucide="plus" class="w-4 h-4 mr-1"></i>
                    Create your first test
                </a>
            </div>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($recentTests as $test): ?>
                    <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                        <div class="flex items-center">
                            <div class="p-2 bg-blue-100 rounded-lg">
                                <i data-lucide="file-text" class="w-5 h-5 text-blue-600"></i>
                            </div>
                            <div class="ml-3">
                                <p class="font-medium text-gray-900"><?= htmlspecialchars($test['title']) ?></p>
                                <p class="text-sm text-gray-600">
                                    <?= $test['question_count'] ?> questions • <?= $test['attempt_count'] ?> attempts
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <a href="?page=take-test&id=<?= $test['id'] ?>" class="text-green-600 hover:text-green-800 text-sm font-medium">
                                Take Test
                            </a>
                            <a href="?page=edit-test&id=<?= $test['id'] ?>" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                Edit
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    // Initialize Lucide icons
    lucide.createIcons();
</script>