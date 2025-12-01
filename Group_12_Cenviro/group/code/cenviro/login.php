<?php
/*This page handles user login using OOP classes.*/
require_once 'config/auth.php';

/*Check if user is already logged in*/
if ($auth->isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$success = '';

// Check if user just registered
if (isset($_GET['registered']) && $_GET['registered'] == '1') {
    $success = 'Registration successful! Please log in with your credentials.';
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validate input
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } elseif (!preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $email)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } else {
        // Try to login using the Auth class
        if ($user = $auth->login($email, $password)) {
            header('Location: loading.php');
            exit();
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Cenviro</title>
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
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
                
                <?php if ($success): ?>
                <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>
                
                <form method="POST" action="login.php" id="loginForm">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" placeholder="your.email@company.com" 
                               pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}" 
                               title="Please enter a valid email address" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="••••••••" 
                               minlength="6" title="Password must be at least 6 characters" required>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn-primary">Log In</button>
                        <a href="register.php" class="btn-secondary">Don't have an account? Sign Up</a>
                    </div>
                </form>
                
                <script>
                document.getElementById('loginForm').addEventListener('submit', function(e) {
                    const email = document.getElementById('email').value;
                    const password = document.getElementById('password').value;
                    const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
                    
                    if (!emailRegex.test(email)) {
                        e.preventDefault();
                        alert('Please enter a valid email address.');
                        return false;
                    }
                    
                    if (password.length < 8) {
                        e.preventDefault();
                        alert('Password must be at least 6 characters long.');
                        return false;
                    }
                });
                </script>
                
                <div class="demo-info">
                    <p>Demo Account:<br>
                    <strong>Email:</strong> demo@cenviro.com<br>
                    <strong>Password:</strong> Demo@123</p>
                </div>
            </section>
        </main>
    </div>

    <footer class="footer">
        <p>&copy; <?php echo date('Y'); ?> Cenviro. All rights reserved. | Generated using Cenviro - ESG KPI Tracking System</p>
    </footer>
</body>
</html>

