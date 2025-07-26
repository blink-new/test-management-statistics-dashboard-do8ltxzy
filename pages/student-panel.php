<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

$db = getDatabase();

// Get all available question groups/sections
$stmt = $db->prepare("SELECT DISTINCT question_group FROM questions ORDER BY question_group");
$stmt->execute();
$sections = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Get section statistics
$sectionStats = [];
foreach ($sections as $section) {
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM questions WHERE question_group = ?");
    $stmt->execute([$section]);
    $sectionStats[$section] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
}

// Get recent test results for this student (using session for demo)
$studentId = $_SESSION['student_id'] ?? 'demo_student';
$stmt = $db->prepare("
    SELECT tr.*, t.title, t.description 
    FROM test_results tr 
    JOIN tests t ON tr.test_id = t.id 
    WHERE tr.student_id = ? 
    ORDER BY tr.completed_at DESC 
    LIMIT 5
");
$stmt->execute([$studentId]);
$recentResults = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate overall statistics
$stmt = $db->prepare("
    SELECT 
        COUNT(*) as total_tests,
        AVG(score) as avg_score,
        MAX(score) as best_score
    FROM test_results 
    WHERE student_id = ?
");
$stmt->execute([$studentId]);
$overallStats = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Panel - Test Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Header -->
    <header class="gradient-bg text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div class="flex items-center space-x-4">
                    <div class="bg-white/20 p-2 rounded-lg">
                        <i data-lucide="graduation-cap" class="w-8 h-8"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold">Student Panel</h1>
                        <p class="text-white/80">Welcome back, Student!</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="../index.php" class="bg-white/20 hover:bg-white/30 px-4 py-2 rounded-lg transition-colors">
                        <i data-lucide="home" class="w-5 h-5 inline mr-2"></i>
                        Back to Main
                    </a>
                </div>
            </div>
        </div>
    </header>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm p-6 card-hover">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Tests Completed</p>
                        <p class="text-3xl font-bold text-gray-900"><?= $overallStats['total_tests'] ?? 0 ?></p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-lg">
                        <i data-lucide="check-circle" class="w-6 h-6 text-blue-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 card-hover">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Average Score</p>
                        <p class="text-3xl font-bold text-gray-900"><?= number_format($overallStats['avg_score'] ?? 0, 1) ?>%</p>
                    </div>
                    <div class="bg-green-100 p-3 rounded-lg">
                        <i data-lucide="trending-up" class="w-6 h-6 text-green-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 card-hover">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Best Score</p>
                        <p class="text-3xl font-bold text-gray-900"><?= number_format($overallStats['best_score'] ?? 0, 1) ?>%</p>
                    </div>
                    <div class="bg-purple-100 p-3 rounded-lg">
                        <i data-lucide="trophy" class="w-6 h-6 text-purple-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Available Sections -->
            <div class="bg-white rounded-xl shadow-sm">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-900 flex items-center">
                        <i data-lucide="book-open" class="w-5 h-5 mr-2 text-blue-600"></i>
                        Take Practice Test
                    </h2>
                    <p class="text-gray-600 mt-1">Choose a section to practice with 40 random questions</p>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <?php foreach ($sections as $section): ?>
                        <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition-colors">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="bg-blue-100 p-2 rounded-lg">
                                        <i data-lucide="<?= getSectionIcon($section) ?>" class="w-5 h-5 text-blue-600"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-medium text-gray-900"><?= htmlspecialchars($section) ?></h3>
                                        <p class="text-sm text-gray-600"><?= $sectionStats[$section] ?> questions available</p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <?php if ($sectionStats[$section] >= 40): ?>
                                    <button onclick="startRandomTest('<?= htmlspecialchars($section) ?>')" 
                                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                        <i data-lucide="play" class="w-4 h-4 inline mr-1"></i>
                                        Start Test
                                    </button>
                                    <?php else: ?>
                                    <span class="text-sm text-gray-500 bg-gray-100 px-3 py-1 rounded-lg">
                                        Need <?= 40 - $sectionStats[$section] ?> more questions
                                    </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Recent Results -->
            <div class="bg-white rounded-xl shadow-sm">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-900 flex items-center">
                        <i data-lucide="clock" class="w-5 h-5 mr-2 text-green-600"></i>
                        Recent Results
                    </h2>
                    <p class="text-gray-600 mt-1">Your latest test performances</p>
                </div>
                <div class="p-6">
                    <?php if (empty($recentResults)): ?>
                    <div class="text-center py-8">
                        <i data-lucide="clipboard" class="w-12 h-12 text-gray-400 mx-auto mb-4"></i>
                        <p class="text-gray-600">No test results yet</p>
                        <p class="text-sm text-gray-500">Take your first practice test to see results here</p>
                    </div>
                    <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($recentResults as $result): ?>
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="font-medium text-gray-900"><?= htmlspecialchars($result['title']) ?></h3>
                                    <p class="text-sm text-gray-600"><?= date('M j, Y g:i A', strtotime($result['completed_at'])) ?></p>
                                </div>
                                <div class="text-right">
                                    <div class="text-2xl font-bold <?= $result['score'] >= 70 ? 'text-green-600' : ($result['score'] >= 50 ? 'text-yellow-600' : 'text-red-600') ?>">
                                        <?= number_format($result['score'], 1) ?>%
                                    </div>
                                    <p class="text-sm text-gray-600"><?= $result['correct_answers'] ?>/<?= $result['total_questions'] ?> correct</p>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Random Test Modal -->
    <div id="randomTestModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Start Random Test</h3>
                <p class="text-gray-600 mb-6">You're about to start a practice test with 40 random questions from the <span id="selectedSection" class="font-medium"></span> section.</p>
                
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <div class="flex items-start space-x-3">
                        <i data-lucide="info" class="w-5 h-5 text-blue-600 mt-0.5"></i>
                        <div class="text-sm text-blue-800">
                            <p class="font-medium mb-1">Test Details:</p>
                            <ul class="space-y-1">
                                <li>• 40 random questions</li>
                                <li>• 60 minutes time limit</li>
                                <li>• Multiple choice format</li>
                                <li>• Results shown immediately</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="flex space-x-3">
                    <button onclick="closeRandomTestModal()" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 py-2 px-4 rounded-lg font-medium transition-colors">
                        Cancel
                    </button>
                    <button onclick="confirmStartTest()" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg font-medium transition-colors">
                        Start Test
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Initialize Lucide icons
        lucide.createIcons();

        let selectedSectionForTest = '';

        function startRandomTest(section) {
            selectedSectionForTest = section;
            document.getElementById('selectedSection').textContent = section;
            document.getElementById('randomTestModal').classList.remove('hidden');
            document.getElementById('randomTestModal').classList.add('flex');
        }

        function closeRandomTestModal() {
            document.getElementById('randomTestModal').classList.add('hidden');
            document.getElementById('randomTestModal').classList.remove('flex');
        }

        function confirmStartTest() {
            // Create a form to submit the random test request
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'take-random-test.php';
            
            const sectionInput = document.createElement('input');
            sectionInput.type = 'hidden';
            sectionInput.name = 'section';
            sectionInput.value = selectedSectionForTest;
            
            form.appendChild(sectionInput);
            document.body.appendChild(form);
            form.submit();
        }

        // Close modal when clicking outside
        document.getElementById('randomTestModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeRandomTestModal();
            }
        });
    </script>
</body>
</html>

<?php
function getSectionIcon($section) {
    $icons = [
        'Mathematics' => 'calculator',
        'Science' => 'flask',
        'History' => 'scroll',
        'Programming' => 'code',
        'English' => 'book',
        'Geography' => 'globe',
        'Physics' => 'atom',
        'Chemistry' => 'beaker'
    ];
    return $icons[$section] ?? 'book-open';
}
?>