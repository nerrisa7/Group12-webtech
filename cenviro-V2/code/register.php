<?php
require_once 'config/auth.php';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $org_name = mysqli_real_escape_string($conn, $_POST['org_name'] ?? '');
    $name = mysqli_real_escape_string($conn, $_POST['name'] ?? '');
    $email = mysqli_real_escape_string($conn, $_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (!$org_name || !$name || !$email || !$password) {
        $error = 'Please fill in all required fields.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } else {
        $check_query = "SELECT user_id FROM users WHERE email = '$email'";
        $check_result = mysqli_query($conn, $check_query);
        
        if ($check_result && mysqli_num_rows($check_result) > 0) {
            $error = 'Email already registered.';
        } else {
            $org_query = "INSERT INTO organizations (org_name) VALUES ('$org_name')";
            if (mysqli_query($conn, $org_query)) {
                $org_id = mysqli_insert_id($conn);
                
                $hashed_password = md5($password);
                $user_query = "INSERT INTO users (name, email, password, org_id) 
                              VALUES ('$name', '$email', '$hashed_password', '$org_id')";
                
                if (mysqli_query($conn, $user_query)) {
                    if ($user = loginUser($conn, $email, $password)) {
                        header('Location: dashboard.php');
                        exit();
                    }
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
</body>
</html>

