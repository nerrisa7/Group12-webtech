<?php
/*This page handles user registration using OOP classes.*/
require_once 'config/auth.php';

// Check if user is already logged in
if ($auth->isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $org_name = trim($_POST['org_name'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate input
    if (!$org_name || !$name || !$email || !$password) {
        $error = 'Please fill in all required fields.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } else {
        // Check if email already exists using User class
        if ($user->emailExists($email)) {
            $error = 'Email already registered.';
        } else {
            // Create organization using Organization class
            $org_id = $organization->createOrganization($org_name);
                
            if ($org_id) {
                // Create user using User class
                $new_user_id = $user->createUser($name, $email, $password, $org_id);
                
                if ($new_user_id) {
                    // Registration successful - redirect to login page
                    header('Location: login.php?registered=1');
                    exit();
                } else {
                    $error = 'Registration failed. Please try again.';
                }
            } else {
                $error = 'Failed to create organization. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Cenviro</title>
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
                    <h4>Create An Account</h4>
                    <p>Start tracking your ESG performance</p>
                </div>
                
                <?php if ($error): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <form method="POST" action="register.php">
                    <div class="form-group">
                        <label for="org_name">Organization Name</label>
                        <input type="text" id="org_name" name="org_name" placeholder="Your Company Name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="name">Your Full Name</label>
                        <input type="text" id="name" name="name" placeholder="John Doe" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" placeholder="your.email@company.com" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Create Password</label>
                        <input type="password" id="password" name="password" placeholder="••••••••" minlength="8" required>
                        <ul class="password-requirements">
                            <li>At least 8 characters long</li>
                        </ul>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="••••••••" required>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn-primary">Sign Up</button>
                        <a href="login.php" class="btn-secondary">Already have an account? Login</a>
                    </div>
                </form>
            </section>
        </main>
    </div>

    <footer class="footer">
        <p>&copy; <?php echo date('Y'); ?> Cenviro. All rights reserved. | Generated using Cenviro - ESG KPI Tracking System</p>
    </footer>
</body>
</html>

