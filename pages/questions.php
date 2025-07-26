<?php
// Handle question creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_question'])) {
    $groupId = (int)$_POST['group_id'];
    $questionText = sanitizeInput($_POST['question_text']);
    $optionA = sanitizeInput($_POST['option_a']);
    $optionB = sanitizeInput($_POST['option_b']);
    $optionC = sanitizeInput($_POST['option_c']);
    $optionD = sanitizeInput($_POST['option_d']);
    $correctAnswer = $_POST['correct_answer'];
    
    if (empty($questionText) || empty($optionA) || empty($optionB) || empty($optionC) || empty($optionD)) {
        $error = "All fields are required.";
    } else {
        try {
            $pdo = getDatabase();
            $stmt = $pdo->prepare("INSERT INTO questions (group_id, question_text, option_a, option_b, option_c, option_d, correct_answer) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$groupId, $questionText, $optionA, $optionB, $optionC, $optionD, $correctAnswer]);
            $success = "Question created successfully!";
        } catch (Exception $e) {
            $error = "Failed to create question: " . $e->getMessage();
        }
    }
}

// Handle group creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_group'])) {
    $groupName = sanitizeInput($_POST['group_name']);
    $groupDescription = sanitizeInput($_POST['group_description']);
    
    if (empty($groupName)) {
        $error = "Group name is required.";
    } else {
        try {
            $pdo = getDatabase();
            $stmt = $pdo->prepare("INSERT INTO question_groups (name, description) VALUES (?, ?)");
            $stmt->execute([$groupName, $groupDescription]);
            $success = "Question group created successfully!";
        } catch (Exception $e) {
            $error = "Failed to create group: " . $e->getMessage();
        }
    }
}

$questions = getAllQuestions();
$groups = getAllQuestionGroups();
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Questions</h1>
            <p class="text-gray-600 mt-1">Manage your question bank</p>
        </div>
        <div class="flex space-x-3">
            <button onclick="showGroupModal()" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                <i data-lucide="folder-plus" class="w-4 h-4 mr-2"></i>
                New Group
            </button>
            <button onclick="showQuestionModal()" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                New Question
            </button>
        </div>
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

    <!-- Filter -->
    <div class="bg-white rounded-lg shadow-sm p-4">
        <div class="flex items-center space-x-4">
            <label for="group_filter" class="text-sm font-medium text-gray-700">Filter by Group:</label>
            <select id="group_filter" onchange="filterQuestions()" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">All Groups</option>
                <?php foreach ($groups as $group): ?>
                    <option value="<?= $group['id'] ?>"><?= htmlspecialchars($group['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <!-- Questions List -->
    <?php if (empty($questions)): ?>
        <div class="bg-white rounded-lg shadow-sm p-12 text-center">
            <i data-lucide="help-circle" class="w-16 h-16 text-gray-400 mx-auto mb-4"></i>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No questions yet</h3>
            <p class="text-gray-600 mb-6">Start building your question bank</p>
            <button onclick="showQuestionModal()" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                Create Question
            </button>
        </div>
    <?php else: ?>
        <div class="space-y-4">
            <?php 
            $currentGroup = '';
            foreach ($questions as $question): 
                if ($currentGroup !== $question['group_name']):
                    if ($currentGroup !== '') echo '</div>';
                    $currentGroup = $question['group_name'];
            ?>
                    <div class="group-section" data-group-id="<?= $question['group_id'] ?>">
                        <h3 class="font-semibold text-gray-900 mb-3 flex items-center">
                            <i data-lucide="folder" class="w-5 h-5 mr-2 text-blue-600"></i>
                            <?= htmlspecialchars($currentGroup ?: 'Uncategorized') ?>
                        </h3>
            <?php endif; ?>
            
                <div class="question-item bg-white rounded-lg shadow-sm border border-gray-200 p-6 ml-6" data-group-id="<?= $question['group_id'] ?>">
                    <div class="flex justify-between items-start mb-4">
                        <h4 class="text-lg font-medium text-gray-900"><?= htmlspecialchars($question['question_text']) ?></h4>
                        <div class="flex items-center space-x-2">
                            <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">
                                Correct: <?= strtoupper($question['correct_answer']) ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div class="flex items-center p-3 rounded-lg <?= $question['correct_answer'] === 'a' ? 'bg-green-50 border border-green-200' : 'bg-gray-50' ?>">
                            <span class="font-medium text-gray-700 mr-2">A)</span>
                            <span class="<?= $question['correct_answer'] === 'a' ? 'text-green-800 font-medium' : 'text-gray-700' ?>">
                                <?= htmlspecialchars($question['option_a']) ?>
                            </span>
                        </div>
                        <div class="flex items-center p-3 rounded-lg <?= $question['correct_answer'] === 'b' ? 'bg-green-50 border border-green-200' : 'bg-gray-50' ?>">
                            <span class="font-medium text-gray-700 mr-2">B)</span>
                            <span class="<?= $question['correct_answer'] === 'b' ? 'text-green-800 font-medium' : 'text-gray-700' ?>">
                                <?= htmlspecialchars($question['option_b']) ?>
                            </span>
                        </div>
                        <div class="flex items-center p-3 rounded-lg <?= $question['correct_answer'] === 'c' ? 'bg-green-50 border border-green-200' : 'bg-gray-50' ?>">
                            <span class="font-medium text-gray-700 mr-2">C)</span>
                            <span class="<?= $question['correct_answer'] === 'c' ? 'text-green-800 font-medium' : 'text-gray-700' ?>">
                                <?= htmlspecialchars($question['option_c']) ?>
                            </span>
                        </div>
                        <div class="flex items-center p-3 rounded-lg <?= $question['correct_answer'] === 'd' ? 'bg-green-50 border border-green-200' : 'bg-gray-50' ?>">
                            <span class="font-medium text-gray-700 mr-2">D)</span>
                            <span class="<?= $question['correct_answer'] === 'd' ? 'text-green-800 font-medium' : 'text-gray-700' ?>">
                                <?= htmlspecialchars($question['option_d']) ?>
                            </span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if ($currentGroup !== '') echo '</div>'; ?>
        </div>
    <?php endif; ?>
</div>

<!-- New Question Modal -->
<div id="questionModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-2xl w-full mx-4 max-h-screen overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-semibold text-gray-900">Create New Question</h3>
            <button onclick="closeQuestionModal()" class="text-gray-400 hover:text-gray-600">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>
        
        <form method="POST" class="space-y-4">
            <div>
                <label for="group_id" class="block text-sm font-medium text-gray-700 mb-2">Question Group</label>
                <select name="group_id" id="group_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Select a group</option>
                    <?php foreach ($groups as $group): ?>
                        <option value="<?= $group['id'] ?>"><?= htmlspecialchars($group['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label for="question_text" class="block text-sm font-medium text-gray-700 mb-2">Question Text</label>
                <textarea name="question_text" id="question_text" required rows="3" 
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                          placeholder="Enter your question"></textarea>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="option_a" class="block text-sm font-medium text-gray-700 mb-2">Option A</label>
                    <input type="text" name="option_a" id="option_a" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Option A">
                </div>
                <div>
                    <label for="option_b" class="block text-sm font-medium text-gray-700 mb-2">Option B</label>
                    <input type="text" name="option_b" id="option_b" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Option B">
                </div>
                <div>
                    <label for="option_c" class="block text-sm font-medium text-gray-700 mb-2">Option C</label>
                    <input type="text" name="option_c" id="option_c" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Option C">
                </div>
                <div>
                    <label for="option_d" class="block text-sm font-medium text-gray-700 mb-2">Option D</label>
                    <input type="text" name="option_d" id="option_d" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Option D">
                </div>
            </div>
            
            <div>
                <label for="correct_answer" class="block text-sm font-medium text-gray-700 mb-2">Correct Answer</label>
                <select name="correct_answer" id="correct_answer" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Select correct answer</option>
                    <option value="a">Option A</option>
                    <option value="b">Option B</option>
                    <option value="c">Option C</option>
                    <option value="d">Option D</option>
                </select>
            </div>
            
            <div class="flex justify-end space-x-3 pt-4">
                <button type="button" onclick="closeQuestionModal()" class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors">
                    Cancel
                </button>
                <button type="submit" name="create_question" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Create Question
                </button>
            </div>
        </form>
    </div>
</div>

<!-- New Group Modal -->
<div id="groupModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-semibold text-gray-900">Create Question Group</h3>
            <button onclick="closeGroupModal()" class="text-gray-400 hover:text-gray-600">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>
        
        <form method="POST" class="space-y-4">
            <div>
                <label for="group_name" class="block text-sm font-medium text-gray-700 mb-2">Group Name</label>
                <input type="text" name="group_name" id="group_name" required 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="Enter group name">
            </div>
            
            <div>
                <label for="group_description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <textarea name="group_description" id="group_description" rows="3" 
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                          placeholder="Enter group description"></textarea>
            </div>
            
            <div class="flex justify-end space-x-3 pt-4">
                <button type="button" onclick="closeGroupModal()" class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors">
                    Cancel
                </button>
                <button type="submit" name="create_group" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    Create Group
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    lucide.createIcons();
    
    function showQuestionModal() {
        document.getElementById('questionModal').classList.remove('hidden');
        document.getElementById('questionModal').classList.add('flex');
    }
    
    function closeQuestionModal() {
        document.getElementById('questionModal').classList.add('hidden');
        document.getElementById('questionModal').classList.remove('flex');
    }
    
    function showGroupModal() {
        document.getElementById('groupModal').classList.remove('hidden');
        document.getElementById('groupModal').classList.add('flex');
    }
    
    function closeGroupModal() {
        document.getElementById('groupModal').classList.add('hidden');
        document.getElementById('groupModal').classList.remove('flex');
    }
    
    function filterQuestions() {
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
</script>