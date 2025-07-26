<?php
// Handle test deletion
if (isset($_POST['delete_test'])) {
    $testId = (int)$_POST['test_id'];
    if (deleteTest($testId)) {
        $success = "Test deleted successfully!";
    } else {
        $error = "Failed to delete test.";
    }
}

$tests = getAllTests();
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Tests</h1>
            <p class="text-gray-600 mt-1">Manage all your tests</p>
        </div>
        <a href="?page=create-test" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
            <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
            Create Test
        </a>
    </div>

    <?php if (isset($success)): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg">
            <?= $success ?>
        </div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
            <?= $error ?>
        </div>
    <?php endif; ?>

    <!-- Tests Grid -->
    <?php if (empty($tests)): ?>
        <div class="bg-white rounded-lg shadow-sm p-12 text-center">
            <i data-lucide="file-text" class="w-16 h-16 text-gray-400 mx-auto mb-4"></i>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No tests yet</h3>
            <p class="text-gray-600 mb-6">Get started by creating your first test</p>
            <a href="?page=create-test" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                Create Test
            </a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($tests as $test): ?>
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 card-hover">
                    <div class="p-6">
                        <div class="flex items-start justify-between mb-4">
                            <div class="p-2 bg-blue-100 rounded-lg">
                                <i data-lucide="file-text" class="w-6 h-6 text-blue-600"></i>
                            </div>
                            <div class="flex items-center space-x-1">
                                <button onclick="deleteTest(<?= $test['id'] ?>)" class="p-1 text-gray-400 hover:text-red-600 transition-colors">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>
                        
                        <h3 class="text-lg font-semibold text-gray-900 mb-2"><?= htmlspecialchars($test['title']) ?></h3>
                        
                        <?php if ($test['description']): ?>
                            <p class="text-gray-600 text-sm mb-4 line-clamp-2"><?= htmlspecialchars($test['description']) ?></p>
                        <?php endif; ?>
                        
                        <div class="space-y-2 mb-4">
                            <div class="flex items-center text-sm text-gray-600">
                                <i data-lucide="help-circle" class="w-4 h-4 mr-2"></i>
                                <?= $test['question_count'] ?> questions
                            </div>
                            <div class="flex items-center text-sm text-gray-600">
                                <i data-lucide="users" class="w-4 h-4 mr-2"></i>
                                <?= $test['attempt_count'] ?> attempts
                            </div>
                            <?php if ($test['time_limit'] > 0): ?>
                                <div class="flex items-center text-sm text-gray-600">
                                    <i data-lucide="clock" class="w-4 h-4 mr-2"></i>
                                    <?= formatTime($test['time_limit'] * 60) ?> limit
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="flex items-center space-x-2">
                            <a href="?page=take-test&id=<?= $test['id'] ?>" class="flex-1 text-center px-3 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm font-medium">
                                Take Test
                            </a>
                            <a href="?page=edit-test&id=<?= $test['id'] ?>" class="flex-1 text-center px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                                Edit
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Delete Test</h3>
        <p class="text-gray-600 mb-6">Are you sure you want to delete this test? This action cannot be undone.</p>
        <div class="flex justify-end space-x-3">
            <button onclick="closeDeleteModal()" class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors">
                Cancel
            </button>
            <form method="POST" class="inline">
                <input type="hidden" name="test_id" id="deleteTestId">
                <button type="submit" name="delete_test" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                    Delete
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    lucide.createIcons();
    
    function deleteTest(testId) {
        document.getElementById('deleteTestId').value = testId;
        document.getElementById('deleteModal').classList.remove('hidden');
        document.getElementById('deleteModal').classList.add('flex');
    }
    
    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
        document.getElementById('deleteModal').classList.remove('flex');
    }
</script>