<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Simple routing
$page = $_GET['page'] ?? 'dashboard';
$allowed_pages = ['dashboard', 'tests', 'create-test', 'edit-test', 'questions', 'statistics', 'take-test', 'results'];

if (!in_array($page, $allowed_pages)) {
    $page = 'dashboard';
}

// Initialize database
initDatabase();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Management Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .gradient-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .card-hover { transition: all 0.3s ease; }
        .card-hover:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Navigation -->
    <nav class="gradient-bg shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <h1 class="text-white text-xl font-bold">Test Management</h1>
                    </div>
                    <div class="hidden md:block ml-10">
                        <div class="flex items-baseline space-x-4">
                            <a href="?page=dashboard" class="<?= $page === 'dashboard' ? 'bg-white bg-opacity-20' : '' ?> text-white hover:bg-white hover:bg-opacity-20 px-3 py-2 rounded-md text-sm font-medium transition-colors">
                                Dashboard
                            </a>
                            <a href="?page=tests" class="<?= $page === 'tests' ? 'bg-white bg-opacity-20' : '' ?> text-white hover:bg-white hover:bg-opacity-20 px-3 py-2 rounded-md text-sm font-medium transition-colors">
                                Tests
                            </a>
                            <a href="?page=questions" class="<?= $page === 'questions' ? 'bg-white bg-opacity-20' : '' ?> text-white hover:bg-white hover:bg-opacity-20 px-3 py-2 rounded-md text-sm font-medium transition-colors">
                                Questions
                            </a>
                            <a href="?page=statistics" class="<?= $page === 'statistics' ? 'bg-white bg-opacity-20' : '' ?> text-white hover:bg-white hover:bg-opacity-20 px-3 py-2 rounded-md text-sm font-medium transition-colors">
                                Statistics
                            </a>
                            <a href="pages/student-panel.php" class="text-white hover:bg-white hover:bg-opacity-20 px-3 py-2 rounded-md text-sm font-medium transition-colors">
                                Student Panel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <?php
        switch ($page) {
            case 'dashboard':
                include 'pages/dashboard.php';
                break;
            case 'tests':
                include 'pages/tests.php';
                break;
            case 'create-test':
                include 'pages/create-test.php';
                break;
            case 'edit-test':
                include 'pages/edit-test.php';
                break;
            case 'questions':
                include 'pages/questions.php';
                break;
            case 'statistics':
                include 'pages/statistics.php';
                break;
            case 'take-test':
                include 'pages/take-test.php';
                break;
            case 'results':
                include 'pages/results.php';
                break;
            default:
                include 'pages/dashboard.php';
        }
        ?>
    </main>

    <script src="assets/js/main.js"></script>
</body>
</html>