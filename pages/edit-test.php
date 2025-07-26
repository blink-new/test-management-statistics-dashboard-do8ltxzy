<?php
$testId = (int)($_GET['id'] ?? 0);
$test = getTestById($testId);

if (!$test) {
    header("Location: ?page=tests");
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_test'])) {
    $title = sanitizeInput($_POST['title']);
    $description = sanitizeInput($_POST['description']);
    $timeLimit = (int)$_POST['time_limit'];
    $questionIds = $_POST['questions'] ?? [];
    
    if (empty($title)) {
        $error = "Test title is required.";
    } elseif (empty($questionIds)) {
        $error = "Please select at least one question.";
    } else {
        try {
            $pdo = getDatabase();
            $pdo->beginTransaction();
            
            // Update test
            $stmt = $pdo->prepare("UPDATE tests SET title = ?, description = ?, time_limit = ? WHERE id = ?");
            $stmt->execute([$title, $description, $timeLimit, $testId]);
            
            // Delete existing test questions
            $stmt = $pdo->prepare("DELETE FROM test_questions WHERE test_id = ?");
            $stmt->execute([$testId]);
            
            // Insert new test questions
            $stmt = $pdo->prepare("INSERT INTO test_questions (test_id, question_id) VALUES (?, ?)");
            foreach ($questionIds as $questionId) {
                $stmt->execute([$testId, $questionId]);
            }
            
            $pdo->commit();
            header("Location: ?page=tests&updated=1");
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Failed to update test: " . $e->getMessage();
        }
    }
}

$questions = getAllQuestions();
$groups = getAllQuestionGroups();
$testQuestions = getTestQuestions($testId);
$selectedQuestionIds = array_column($testQuestions, 'id');
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center space-x-4">
        <a href="?page=tests" class="p-2 text-gray-600 hover:text-gray-900 transition-colors">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Edit Test</h1>
            <p class="text-gray-600 mt-1">Update test details and questions</p>
        </div>
    </div>

    <?php if (isset($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
            <?= $error ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="space-y-6">
        <!-- Test Details -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Test Details</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Test Title *</label>
                    <input type="text" id="title" name="title" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Enter test title" value="<?= htmlspecialchars($test['title']) ?>">
                </div>
                
                <div>
                    <label for="time_limit" class="block text-sm font-medium text-gray-700 mb-2">Time Limit (minutes)</label>
                    <input type="number" id="time_limit" name="time_limit" min="0" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="0 = No limit" value="<?= $test['time_limit'] ?>">
                </div>
            </div>
            
            <div class="mt-4">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <textarea id="description" name="description" rows="3" 
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                          placeholder="Enter test description"><?= htmlspecialchars($test['description']) ?></textarea>
            </div>
        </div>

        <!-- Question Selection -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold text-gray-900">Select Questions</h2>
                <div class="flex items-center space-x-4">
                    <button type="button" onclick="selectAll()" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        Select All
                    </button>
                    <button type="button" onclick="selectNone()" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        Select None
                    </button>
                </div>
            </div>

            <?php if (empty($questions)): ?>
                <div class="text-center py-8">
                    <i data-lucide="help-circle" class="w-12 h-12 text-gray-400 mx-auto mb-4"></i>
                    <p class="text-gray-600 mb-4">No questions available</p>
                    <a href="?page=questions" class="inline-flex items-center text-blue-600 hover:text-blue-800">
                        <i data-lucide="plus" class="w-4 h-4 mr-1"></i>
                        Add questions first
                    </a>
                </div>
            <?php else: ?>
                <!-- Filter by Group -->
                <div class="mb-4">
                    <label for="group_filter" class="block text-sm font-medium text-gray-700 mb-2">Filter by Group</label>
                    <select id="group_filter" onchange="filterByGroup()" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Groups</option>
                        <?php foreach ($groups as $group): ?>
                            <option value="<?= $group['id'] ?>"><?= htmlspecialchars($group['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Questions List -->
                <div class="space-y-4 max-h-96 overflow-y-auto">
                    <?php 
                    $currentGroup = '';
                    foreach ($questions as $question): 
                        if ($currentGroup !== $question['group_name']):
                            if ($currentGroup !== '') echo '</div>';
                            $currentGroup = $question['group_name'];
                    ?>
                            <div class="group-section" data-group-id="<?= $question['group_id'] ?>">
                                <h3 class="font-medium text-gray-900 mb-2 flex items-center">
                                    <i data-lucide="folder" class="w-4 h-4 mr-2"></i>
                                    <?= htmlspecialchars($currentGroup ?: 'Uncategorized') ?>
                                </h3>
                    <?php endif; ?>
                    
                        <div class="question-item ml-6 p-4 border border-gray-200 rounded-lg" data-group-id="<?= $question['group_id'] ?>">
                            <label class="flex items-start space-x-3 cursor-pointer">
                                <input type="checkbox" name="questions[]" value="<?= $question['id'] ?>" 
                                       class="mt-1 question-checkbox"
                                       <?= in_array($question['id'], $selectedQuestionIds) ? 'checked' : '' ?>>
                                <div class="flex-1">
                                    <p class="font-medium text-gray-900"><?= htmlspecialchars($question['question_text']) ?></p>
                                    <div class="mt-2 grid grid-cols-2 gap-2 text-sm">
                                        <span class="<?= $question['correct_answer'] === 'a' ? 'text-green-600 font-medium' : 'text-gray-600' ?>">
                                            A) <?= htmlspecialchars($question['option_a']) ?>
                                        </span>
                                        <span class="<?= $question['correct_answer'] === 'b' ? 'text-green-600 font-medium' : 'text-gray-600' ?>">
                                            B) <?= htmlspecialchars($question['option_b']) ?>
                                        </span>
                                        <span class="<?= $question['correct_answer'] === 'c' ? 'text-green-600 font-medium' : 'text-gray-600' ?>">
                                            C) <?= htmlspecialchars($question['option_c']) ?>
                                        </span>
                                        <span class="<?= $question['correct_answer'] === 'd' ? 'text-green-600 font-medium' : 'text-gray-600' ?>">
                                            D) <?= htmlspecialchars($question['option_d']) ?>
                                        </span>
                                    </div>
                                </div>
                            </label>
                        </div>
                    <?php endforeach; ?>
                    <?php if ($currentGroup !== '') echo '</div>'; ?>
                </div>

                <div class="mt-4 p-3 bg-blue-50 rounded-lg">
                    <p class="text-sm text-blue-800">
                        <span id="selected-count"><?= count($selectedQuestionIds) ?></span> questions selected
                    </p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end space-x-4">
            <a href="?page=tests" class="px-6 py-2 text-gray-600 hover:text-gray-800 transition-colors">
                Cancel
            </a>
            <button type="submit" name="update_test" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                Update Test
            </button>
        </div>
    </form>
</div>

<script>
    lucide.createIcons();
    
    function updateSelectedCount() {
        const checked = document.querySelectorAll('.question-checkbox:checked').length;
        document.getElementById('selected-count').textContent = checked;
    }
    
    function selectAll() {
        const visibleCheckboxes = document.querySelectorAll('.question-item:not([style*="display: none"]) .question-checkbox');
        visibleCheckboxes.forEach(cb => cb.checked = true);
        updateSelectedCount();
    }
    
    function selectNone() {
        const visibleCheckboxes = document.querySelectorAll('.question-item:not([style*="display: none"]) .question-checkbox');
        visibleCheckboxes.forEach(cb => cb.checked = false);
        updateSelectedCount();
    }
    
    function filterByGroup() {
        const groupId = document.getElementById('group_filter').value;
        const questionItems = document.querySelectorAll('.question-item');
        const groupSections = document.querySelectorAll('.group-section');
        
        if (groupId === '') {
            // Show all
            questionItems.forEach(item => item.style.display = 'block');
            groupSections.forEach(section => section.style.display = 'block');
        } else {
            // Hide all first
            questionItems.forEach(item => item.style.display = 'none');
            groupSections.forEach(section => section.style.display = 'none');
            
            // Show selected group
            const targetItems = document.querySelectorAll(`[data-group-id="${groupId}"]`);
            targetItems.forEach(item => item.style.display = 'block');
        }
    }
    
    // Add event listeners
    document.addEventListener('DOMContentLoaded', function() {
        const checkboxes = document.querySelectorAll('.question-checkbox');
        checkboxes.forEach(cb => {
            cb.addEventListener('change', updateSelectedCount);
        });
        updateSelectedCount();
    });
</script>