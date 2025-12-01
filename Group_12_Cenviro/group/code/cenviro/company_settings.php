<?php
/*This page allows users to update company information.*/
require_once 'config/auth.php';
$auth->requireLogin();

// Get current user
$user = $auth->getCurrentUser();
if (!$user) {
    $auth->logout();
}

// Get current settings from database
$settings = $companySettings->getSettings();

// Get values for form - use user's account data first, then settings
$company_name = $user['org_name'] ?? ($settings['company_name'] ?? '');
$main_email = $user['email'] ?? ($settings['main_email'] ?? '');
$ceo = $user['name'] ?? ($settings['ceo'] ?? '');
$company_description = $settings['company_description'] ?? '';
$country = $settings['country'] ?? '';
$city = $settings['city'] ?? '';
$full_address = $settings['full_address'] ?? '';
$time_zone = $settings['time_zone'] ?? '';
$support_email = $settings['support_email'] ?? '';
$phone_number = $settings['phone_number'] ?? '';
$website_url = $settings['website_url'] ?? '';

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $data = [
        'company_name' => trim($_POST['company_name'] ?? ''),
        'ceo' => trim($_POST['ceo'] ?? ''),
        'company_description' => trim($_POST['company_description'] ?? ''),
        'country' => trim($_POST['country'] ?? ''),
        'city' => trim($_POST['city'] ?? ''),
        'full_address' => trim($_POST['full_address'] ?? ''),
        'time_zone' => trim($_POST['time_zone'] ?? ''),
        'main_email' => trim($_POST['main_email'] ?? ''),
        'support_email' => trim($_POST['support_email'] ?? ''),
        'phone_number' => trim($_POST['phone_number'] ?? ''),
        'website_url' => trim($_POST['website_url'] ?? '')
    ];
    
    // Don't allow changing company name, email, or CEO - use user's account data
    $data['company_name'] = $user['org_name'];
    $data['main_email'] = $user['email'];
    $data['ceo'] = $user['name'];
    
    // Validate required fields
    if (empty($data['company_name'])) {
        $error_message = 'Company name is required.';
    } else {
        // Update settings in database
        if (!$error_message) {
            if ($companySettings->updateSettings($data)) {
                $success_message = 'Settings updated successfully!';
                // Refresh settings to show updated values
                $settings = $companySettings->getSettings();
                
                // Update all variables - but keep company_name, main_email, and ceo from user account
                $company_name = $user['org_name'] ?? ($settings['company_name'] ?? '');
                $main_email = $user['email'] ?? ($settings['main_email'] ?? '');
                $ceo = $user['name'] ?? ($settings['ceo'] ?? '');
                $company_description = $settings['company_description'] ?? '';
                $country = $settings['country'] ?? '';
                $city = $settings['city'] ?? '';
                $full_address = $settings['full_address'] ?? '';
                $time_zone = $settings['time_zone'] ?? '';
                $support_email = $settings['support_email'] ?? '';
                $phone_number = $settings['phone_number'] ?? '';
                $website_url = $settings['website_url'] ?? '';
            } else {
                $error_message = 'Failed to update settings. Please try again.';
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
    <title>Company Settings - Cenviro</title>
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="dashboard-container">
        <header class="header">
            <div class="header-left">
                <h2><?php echo htmlspecialchars($user['org_name'] ?? 'Organization'); ?></h2>
            </div>
            <div class="header-right">
                <div class="user-info">
                    <span class="material-symbols-outlined">person</span>
                    <span><?php echo htmlspecialchars($user['name']); ?></span>
                </div>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </header>

        <aside class="sidebar">
            <div class="sidebar-logo">
                <img src="logo.png" alt="Cenviro Logo" class="logo-img">
                <h2><a href="dashboard.php">Cenviro</a></h2>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="dashboard.php" class="nav-btn"><span class="material-symbols-outlined">dashboard</span> Dashboard</a></li>
                    <li><a href="data_input.php" class="nav-btn"><span class="material-symbols-outlined">edit_note</span> Data Input</a></li>
                    <li><a href="reports.php" class="nav-btn"><span class="material-symbols-outlined">description</span> Reports</a></li>
                    <li><a href="company_settings.php" class="nav-btn active"><span class="material-symbols-outlined">settings</span> Company Settings</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <div class="main-title">
                <h2>Company Settings</h2>
                <p>Update your company information. This will appear in your ESG reports.</p>
            </div>

            <?php if ($success_message): ?>
            <div class="alert success"><?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>
            <?php if ($error_message): ?>
            <div class="alert error"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <div class="content-section">
                <form method="POST" action="company_settings.php">
                    <h3 class="section-title">Basic Information</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="company_name">Company Name *</label>
                            <input type="text" id="company_name" name="company_name" 
                                   value="<?php echo htmlspecialchars($company_name); ?>" 
                                   placeholder="e.g., Cenviro Limited" readonly style="background-color: #f5f5f5; cursor: not-allowed;">
                            <p class="help-text" style="font-size: 12px; color: #666; margin-top: 5px;">This is your organization name and cannot be changed.</p>
                        </div>

                        <div class="form-group">
                            <label for="ceo">CEO</label>
                            <input type="text" id="ceo" name="ceo" 
                                   value="<?php echo htmlspecialchars($ceo); ?>" 
                                   placeholder="e.g., John Doe" readonly style="background-color: #f5f5f5; cursor: not-allowed;">
                            <p class="help-text" style="font-size: 12px; color: #666; margin-top: 5px;">This is your name and cannot be changed.</p>
                        </div>

                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label for="company_description">Company Description</label>
                            <textarea id="company_description" name="company_description" rows="3" 
                                      placeholder="Brief description of your company (1-3 lines)"><?php echo htmlspecialchars($company_description); ?></textarea>
                        </div>
                    </div>

                    <h3 class="section-title" style="margin-top: 30px;">Location Information</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="country">Country</label>
                            <input type="text" id="country" name="country" 
                                   value="<?php echo htmlspecialchars($country); ?>" 
                                   placeholder="e.g., Ghana">
                        </div>

                        <div class="form-group">
                            <label for="city">City</label>
                            <input type="text" id="city" name="city" 
                                   value="<?php echo htmlspecialchars($city); ?>" 
                                   placeholder="e.g., Accra">
                        </div>

                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label for="full_address">Full Address</label>
                            <textarea id="full_address" name="full_address" rows="2" 
                                      placeholder="Street address, building, etc."><?php echo htmlspecialchars($full_address); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="time_zone">Time Zone</label>
                            <input type="text" id="time_zone" name="time_zone" 
                                   value="<?php echo htmlspecialchars($time_zone); ?>" 
                                   placeholder="e.g., Africa/Accra">
                        </div>
                    </div>

                    <h3 class="section-title" style="margin-top: 30px;">Contact Information</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="main_email">Main Email *</label>
                            <input type="email" id="main_email" name="main_email" 
                                   value="<?php echo htmlspecialchars($main_email); ?>" 
                                   placeholder="contact@company.com" readonly style="background-color: #f5f5f5; cursor: not-allowed;">
                            <p class="help-text" style="font-size: 12px; color: #666; margin-top: 5px;">This is your account email and cannot be changed.</p>
                        </div>

                        <div class="form-group">
                            <label for="support_email">Support Email</label>
                            <input type="email" id="support_email" name="support_email" 
                                   value="<?php echo htmlspecialchars($support_email); ?>" 
                                   placeholder="support@company.com">
                        </div>

                        <div class="form-group">
                            <label for="phone_number">Phone Number</label>
                            <input type="text" id="phone_number" name="phone_number" 
                                   value="<?php echo htmlspecialchars($phone_number); ?>" 
                                   placeholder="+233 XX XXX XXXX">
                        </div>

                        <div class="form-group">
                            <label for="website_url">Website URL</label>
                            <input type="url" id="website_url" name="website_url" 
                                   value="<?php echo htmlspecialchars($website_url); ?>" 
                                   placeholder="https://www.company.com">
                        </div>
                    </div>

                    <div class="action-buttons" style="margin-top: 30px;">
                        <button type="submit" class="btn-submit">Save Settings</button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <footer class="footer">
        <p>&copy; <?php echo date('Y'); ?> Cenviro. All rights reserved. | Generated using Cenviro - ESG KPI Tracking System</p>
    </footer>
</body>
</html>
<?php $database->close(); ?>

