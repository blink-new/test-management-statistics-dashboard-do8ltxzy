<?php
// Helper functions

function getAllTests() {
    $pdo = getDatabase();
    $stmt = $pdo->query("
        SELECT t.*, 
               COUNT(tq.question_id) as question_count,
               COUNT(ta.id) as attempt_count
        FROM tests t 
        LEFT JOIN test_questions tq ON t.id = tq.test_id 
        LEFT JOIN test_attempts ta ON t.id = ta.test_id
        GROUP BY t.id 
        ORDER BY t.created_at DESC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTestById($id) {
    $pdo = getDatabase();
    $stmt = $pdo->prepare("SELECT * FROM tests WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getAllQuestions() {
    $pdo = getDatabase();
    $stmt = $pdo->query("
        SELECT q.*, qg.name as group_name 
        FROM questions q 
        LEFT JOIN question_groups qg ON q.group_id = qg.id 
        ORDER BY qg.name, q.id
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAllQuestionGroups() {
    $pdo = getDatabase();
    $stmt = $pdo->query("SELECT * FROM question_groups ORDER BY name");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTestQuestions($testId) {
    $pdo = getDatabase();
    $stmt = $pdo->prepare("
        SELECT q.*, qg.name as group_name 
        FROM questions q 
        JOIN test_questions tq ON q.id = tq.question_id 
        LEFT JOIN question_groups qg ON q.group_id = qg.id 
        WHERE tq.test_id = ? 
        ORDER BY tq.id
    ");
    $stmt->execute([$testId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTestStatistics() {
    $pdo = getDatabase();
    
    // Get overall statistics
    $stats = [];
    
    // Total tests
    $stmt = $pdo->query("SELECT COUNT(*) FROM tests");
    $stats['total_tests'] = $stmt->fetchColumn();
    
    // Total questions
    $stmt = $pdo->query("SELECT COUNT(*) FROM questions");
    $stats['total_questions'] = $stmt->fetchColumn();
    
    // Total attempts
    $stmt = $pdo->query("SELECT COUNT(*) FROM test_attempts WHERE completed_at IS NOT NULL");
    $stats['total_attempts'] = $stmt->fetchColumn();
    
    // Average score
    $stmt = $pdo->query("SELECT AVG(CAST(score AS FLOAT) / CAST(total_questions AS FLOAT) * 100) FROM test_attempts WHERE completed_at IS NOT NULL AND total_questions > 0");
    $stats['average_score'] = round($stmt->fetchColumn(), 1);
    
    return $stats;
}

function getTestPerformanceData() {
    $pdo = getDatabase();
    $stmt = $pdo->query("
        SELECT t.title, 
               COUNT(ta.id) as attempts,
               AVG(CAST(ta.score AS FLOAT) / CAST(ta.total_questions AS FLOAT) * 100) as avg_score
        FROM tests t 
        LEFT JOIN test_attempts ta ON t.id = ta.test_id AND ta.completed_at IS NOT NULL
        GROUP BY t.id, t.title
        ORDER BY t.title
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function createTest($title, $description, $timeLimit, $questionIds) {
    $pdo = getDatabase();
    
    try {
        $pdo->beginTransaction();
        
        // Insert test
        $stmt = $pdo->prepare("INSERT INTO tests (title, description, time_limit) VALUES (?, ?, ?)");
        $stmt->execute([$title, $description, $timeLimit]);
        $testId = $pdo->lastInsertId();
        
        // Insert test questions
        $stmt = $pdo->prepare("INSERT INTO test_questions (test_id, question_id) VALUES (?, ?)");
        foreach ($questionIds as $questionId) {
            $stmt->execute([$testId, $questionId]);
        }
        
        $pdo->commit();
        return $testId;
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function deleteTest($id) {
    $pdo = getDatabase();
    
    try {
        $pdo->beginTransaction();
        
        // Delete test answers
        $pdo->prepare("DELETE FROM test_answers WHERE attempt_id IN (SELECT id FROM test_attempts WHERE test_id = ?)")->execute([$id]);
        
        // Delete test attempts
        $pdo->prepare("DELETE FROM test_attempts WHERE test_id = ?")->execute([$id]);
        
        // Delete test questions
        $pdo->prepare("DELETE FROM test_questions WHERE test_id = ?")->execute([$id]);
        
        // Delete test
        $pdo->prepare("DELETE FROM tests WHERE id = ?")->execute([$id]);
        
        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        return false;
    }
}

function formatTime($seconds) {
    if ($seconds < 60) {
        return $seconds . 's';
    } elseif ($seconds < 3600) {
        return floor($seconds / 60) . 'm ' . ($seconds % 60) . 's';
    } else {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;
        return $hours . 'h ' . $minutes . 'm ' . $secs . 's';
    }
}

function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Initialize student session if not exists
function initStudentSession() {
    if (!isset($_SESSION['student_id'])) {
        $_SESSION['student_id'] = 'demo_student_' . uniqid();
    }
}

// Call this function at the start of student pages
initStudentSession();
?>