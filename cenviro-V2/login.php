<?php
require_once 'config/auth.php';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($email && $password) {
        if ($user = loginUser($conn, $email, $password)) {
            header('Location: dashboard.php');
            exit();
        } else {
            $error = 'Invalid email or password.';
        }
    } else {
        $error = 'Please fill in all fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Cenviro</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="auth-container">
        <aside class="auth-left">
            <section>
                <img src="logo.png" alt="Cenviro Logo" class="auth-logo">
                <h2><a href="index.php">Cenviro</a></h2>
                <p>ESG KPI Tracking for SMEs</p>
            </section>
        </aside>
        
        <main class="auth-right">
            <section class="auth-form-section">
                <div class="form-title">
                    <h4>Welcome Back!</h4>
                    <p>Log in to track your ESG performance</p>
                </div>
                
                <?php if ($error): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <form method="POST" action="login.php">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" placeholder="your.email@company.com" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="••••••••" required>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn-primary">Log In</button>
                        <a href="register.php" class="btn-secondary">Don't have an account? Sign Up</a>
                    </div>
                </form>
                
                <div class="demo-info">
                    <p>Demo Account:<br>
                    <strong>Email:</strong> demo@cenviro.com<br>
                    <strong>Password:</strong> Demo@123</p>
                </div>
            </section>
        </main>
    </div>
</body>
</html>

