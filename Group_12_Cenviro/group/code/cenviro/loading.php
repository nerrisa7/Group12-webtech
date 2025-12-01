<?php
/*This page shows a loading screen before redirecting to dashboard.*/
require_once 'config/auth.php';
$auth->requireLogin();

$user = $auth->getCurrentUser();
$firstName = $user ? explode(' ', $user['name'])[0] : 'User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loading Dashboard - Cenviro</title>
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f5f5f5;
            min-height: 100vh;
        }
        .loading-card {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            text-align: center;
            max-width: 420px;
            width: 90%;
        }
        .spinner {
            width: 60px;
            height: 60px;
            border: 6px solid #e3f2fd;
            border-top: 6px solid #2d6a4f;
            border-radius: 50%;
            margin: 30px auto;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .loading-card h2 {
            margin-bottom: 10px;
            color: #2d6a4f;
        }
        .loading-card p {
            color: #666;
        }
    </style>
</head>
<body>
    <div class="loading-card">
        <h2>Hi <?php echo htmlspecialchars($firstName); ?>!</h2>
        <p>We are preparing your ESG dashboard.</p>
        <div class="spinner"></div>
        <p>Please wait a moment...</p>
    </div>

    <script>
        setTimeout(function() {
            window.location.href = 'dashboard.php';
        }, 2000);
    </script>

    <footer class="footer">
        <p>&copy; <?php echo date('Y'); ?> Cenviro. All rights reserved. | Generated using Cenviro - ESG KPI Tracking System</p>
    </footer>
</body>
</html>
<?php $database->close(); ?>

