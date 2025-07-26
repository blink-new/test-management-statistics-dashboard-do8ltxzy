<?php
$testId = (int)($_GET['id'] ?? 0);
$test = getTestById($testId);

if (!$test) {
    header("Location: ?page=tests");
    exit;
}

// Handle test submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_test'])) {
    $participantName = sanitizeInput($_POST['participant_name']);
    $participantEmail = sanitizeInput($_POST['participant_email']);
    $answers = $_POST['answers'] ?? [];
    $timeStarted = (int)$_POST['time_started'];
    $timeTaken = time() - $timeStarted;
    
    if (empty($participantName) || empty($participantEmail)) {
        $error = "Name and email are required.";
    } else {
        try {
            $pdo = getDatabase();
            $pdo->beginTransaction();
            
            // Get test questions
            $questions = getTestQuestions($testId);
            $totalQuestions = count($questions);
            $score = 0;
            
            // Create test attempt
            $stmt = $pdo->prepare("INSERT INTO test_attempts (test_id, participant_name, participant_email, total_questions, time_taken, completed_at) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$testId, $participantName, $participantEmail, $totalQuestions, $timeTaken, date('Y-m-d H:i:s')]);
            $attemptId = $pdo->lastInsertId();
            
            // Process answers
            foreach ($questions as $question) {
                $selectedAnswer = $answers[$question['id']] ?? '';
                $isCorrect = ($selectedAnswer === $question['correct_answer']) ? 1 : 0;
                if ($isCorrect) $score++;
                
                $stmt = $pdo->prepare("INSERT INTO test_answers (attempt_id, question_id, selected_answer, is_correct) VALUES (?, ?, ?, ?)");
                $stmt->execute([$attemptId, $question['id'], $selectedAnswer, $isCorrect]);
            }
            
            // Update attempt with score
            $stmt = $pdo->prepare("UPDATE test_attempts SET score = ? WHERE id = ?");
            $stmt->execute([$score, $attemptId]);
            
            $pdo->commit();
            
            // Redirect to results
            header("Location: ?page=results&attempt=" . $attemptId);
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Failed to submit test: " . $e->getMessage();
        }
    }
}

$questions = getTestQuestions($testId);
?>

<div class="max-w-4xl mx-auto space-y-6">
    <!-- Test Header -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900"><?= htmlspecialchars($test['title']) ?></h1>
                <?php if ($test['description']): ?>
                    <p class="text-gray-600 mt-2"><?= htmlspecialchars($test['description']) ?></p>
                <?php endif; ?>
            </div>
            <?php if ($test['time_limit'] > 0): ?>
                <div class="text-right">
                    <div class="text-2xl font-bold text-red-600" id="timer">
                        <?= sprintf('%02d:%02d', $test['time_limit'], 0) ?>
                    </div>
                    <p class="text-sm text-gray-600">Time Remaining</p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="flex items-center space-x-6 text-sm text-gray-600">
            <div class="flex items-center">
                <i data-lucide="help-circle" class="w-4 h-4 mr-1"></i>
                <?= count($questions) ?> questions
            </div>
            <?php if ($test['time_limit'] > 0): ?>
                <div class="flex items-center">
                    <i data-lucide="clock" class="w-4 h-4 mr-1"></i>
                    <?= $test['time_limit'] ?> minutes
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (isset($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
            <?= $error ?>
        </div>
    <?php endif; ?>

    <?php if (empty($questions)): ?>
        <div class="bg-white rounded-lg shadow-sm p-12 text-center">
            <i data-lucide="alert-circle" class="w-16 h-16 text-gray-400 mx-auto mb-4"></i>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No questions available</h3>
            <p class="text-gray-600 mb-6">This test doesn't have any questions yet.</p>
            <a href="?page=tests" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
                Back to Tests
            </a>
        </div>
    <?php else: ?>
        <!-- Participant Info Form -->
        <div id="participantForm" class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Before you start</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="participant_name" class="block text-sm font-medium text-gray-700 mb-2">Your Name *</label>
                    <input type="text" id="participant_name" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Enter your full name">
                </div>
                <div>
                    <label for="participant_email" class="block text-sm font-medium text-gray-700 mb-2">Your Email *</label>
                    <input type="email" id="participant_email" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Enter your email">
                </div>
            </div>
            <div class="mt-6">
                <button onclick="startTest()" class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium">
                    Start Test
                </button>
            </div>
        </div>

        <!-- Test Form -->
        <form method="POST" id="testForm" class="hidden">
            <input type="hidden" name="participant_name" id="form_participant_name">
            <input type="hidden" name="participant_email" id="form_participant_email">
            <input type="hidden" name="time_started" id="time_started" value="<?= time() ?>">
            
            <!-- Progress Bar -->
            <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm font-medium text-gray-700">Progress</span>
                    <span class="text-sm text-gray-600" id="progress-text">0 of <?= count($questions) ?> answered</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" id="progress-bar" style="width: 0%"></div>
                </div>
            </div>

            <!-- Questions -->
            <div class="space-y-6">
                <?php foreach ($questions as $index => $question): ?>
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="flex items-start justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-900">
                                Question <?= $index + 1 ?>
                            </h3>
                            <?php if ($question['group_name']): ?>
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">
                                    <?= htmlspecialchars($question['group_name']) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <p class="text-gray-900 mb-6"><?= htmlspecialchars($question['question_text']) ?></p>
                        
                        <div class="space-y-3">
                            <?php foreach (['a', 'b', 'c', 'd'] as $option): ?>
                                <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors">
                                    <input type="radio" name="answers[<?= $question['id'] ?>]" value="<?= $option ?>" 
                                           class="mr-3 question-radio" data-question="<?= $question['id'] ?>"
                                           onchange="updateProgress()">
                                    <span class="font-medium text-gray-700 mr-2"><?= strtoupper($option) ?>)</span>
                                    <span class="text-gray-900"><?= htmlspecialchars($question['option_' . $option]) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Submit Button -->
            <div class="bg-white rounded-lg shadow-sm p-6 mt-6">
                <div class="flex justify-between items-center">
                    <div class="text-sm text-gray-600">
                        Make sure you've answered all questions before submitting.
                    </div>
                    <button type="submit" name="submit_test" id="submit-btn" disabled
                            class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium disabled:bg-gray-400 disabled:cursor-not-allowed">
                        Submit Test
                    </button>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>

<script>
    lucide.createIcons();
    
    let timeLimit = <?= $test['time_limit'] * 60 ?>;
    let timeRemaining = timeLimit;
    let timerInterval;
    
    function startTest() {
        const name = document.getElementById('participant_name').value.trim();
        const email = document.getElementById('participant_email').value.trim();
        
        if (!name || !email) {
            alert('Please enter your name and email');
            return;
        }
        
        // Transfer participant info to form
        document.getElementById('form_participant_name').value = name;
        document.getElementById('form_participant_email').value = email;
        document.getElementById('time_started').value = Math.floor(Date.now() / 1000);
        
        // Hide participant form and show test
        document.getElementById('participantForm').classList.add('hidden');
        document.getElementById('testForm').classList.remove('hidden');
        
        // Start timer if time limit is set
        if (timeLimit > 0) {
            startTimer();
        }
        
        // Scroll to top
        window.scrollTo(0, 0);
    }
    
    function startTimer() {
        timerInterval = setInterval(function() {
            timeRemaining--;
            
            const minutes = Math.floor(timeRemaining / 60);
            const seconds = timeRemaining % 60;
            
            document.getElementById('timer').textContent = 
                String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
            
            // Change color when time is running out
            if (timeRemaining <= 300) { // 5 minutes
                document.getElementById('timer').className = 'text-2xl font-bold text-red-600';
            } else if (timeRemaining <= 600) { // 10 minutes
                document.getElementById('timer').className = 'text-2xl font-bold text-yellow-600';
            }
            
            // Auto-submit when time is up
            if (timeRemaining <= 0) {
                clearInterval(timerInterval);
                alert('Time is up! The test will be submitted automatically.');
                document.getElementById('testForm').submit();
            }
        }, 1000);
    }
    
    function updateProgress() {
        const totalQuestions = <?= count($questions) ?>;
        const answeredQuestions = document.querySelectorAll('.question-radio:checked').length;
        const percentage = (answeredQuestions / totalQuestions) * 100;
        
        document.getElementById('progress-bar').style.width = percentage + '%';
        document.getElementById('progress-text').textContent = answeredQuestions + ' of ' + totalQuestions + ' answered';
        
        // Enable submit button when all questions are answered
        const submitBtn = document.getElementById('submit-btn');
        if (answeredQuestions === totalQuestions) {
            submitBtn.disabled = false;
            submitBtn.classList.remove('disabled:bg-gray-400', 'disabled:cursor-not-allowed');
        } else {
            submitBtn.disabled = true;
            submitBtn.classList.add('disabled:bg-gray-400', 'disabled:cursor-not-allowed');
        }
    }
    
    // Prevent accidental page refresh
    window.addEventListener('beforeunload', function(e) {
        if (!document.getElementById('testForm').classList.contains('hidden')) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
</script>