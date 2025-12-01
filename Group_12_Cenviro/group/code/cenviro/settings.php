<?php
/*This page allows users to update their company information.*/
require_once 'config/auth.php';
$auth->requireLogin();

// Get current user
$user = $auth->getCurrentUser();
if (!$user) {
    $auth->logout();
}

$user_id = $_SESSION['user_id'];
$org_id = $user['org_id'];
$success_message = '';
$error_message = '';

// Get current organization info
$organization_data = $organization->getOrganizationById($org_id);


// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $org_name = trim($_POST['org_name'] ?? '');
    $company_info = trim($_POST['company_info'] ?? '');
    
    if (!$org_name) {
        $error_message = 'Organization name is required.';
    } else {
        // Handle logo upload
        $logo_path = null;
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/uploads/logos/';
            
            // Create uploads directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_extension = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (in_array($file_extension, $allowed_extensions)) {
                $new_filename = 'logo_' . $org_id . '_' . time() . '.' . $file_extension;
                $upload_path = $upload_dir . $new_filename;
                
                if (move_uploaded_file($_FILES['logo']['tmp_name'], $upload_path)) {
                    // Delete old logo if exists
                    if ($organization_data && $organization_data['logo_path']) {
                        $old_logo = __DIR__ . '/' . $organization_data['logo_path'];
                        if (file_exists($old_logo)) {
                            unlink($old_logo);
                        }
                    }
                    $logo_path = 'uploads/logos/' . $new_filename;
                } else {
                    $error_message = 'Failed to upload logo.';
                }
            } else {
                $error_message = 'Invalid file type. Please upload JPG, PNG, or GIF.';
            }
        }
        
        // Update organization
        if (!$error_message) {
            if ($organization->updateOrganization($org_id, $org_name, $logo_path, $company_info)) {
                $success_message = 'Settings updated successfully!';
                // Refresh organization data
                $organization_data = $organization->getOrganizationById($org_id);
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
    <title>Settings - Cenviro</title>
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
                    <li><a href="settings.php" class="nav-btn active"><span class="material-symbols-outlined">settings</span> Settings</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <div class="main-title">
                <h2>Company Settings</h2>
            </div>

            <?php if ($success_message): ?>
            <div class="alert success"><?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>
            <?php if ($error_message): ?>
            <div class="alert error"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <div class="content-section">
                <form method="POST" action="settings.php" enctype="multipart/form-data">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="org_name">Organization Name *</label>
                            <input type="text" id="org_name" name="org_name" 
                                   value="<?php echo htmlspecialchars($organization_data['org_name'] ?? ''); ?>" 
                                   placeholder="Your Company Name" required>
                        </div>

                        <div class="form-group">
                            <label for="logo">Company Logo</label>
                            <input type="file" id="logo" name="logo" accept="image/jpeg,image/png,image/gif">
                            <p class="help-text">Upload JPG, PNG, or GIF (max 5MB)</p>
                            <?php if ($organization_data && $organization_data['logo_path']): ?>
                            <div style="margin-top: 10px;">
                                <p>Current logo:</p>
                                <img src="<?php echo htmlspecialchars($organization_data['logo_path']); ?>" 
                                     alt="Company Logo" 
                                     style="max-width: 200px; max-height: 200px; border: 1px solid #ddd; padding: 5px; border-radius: 5px;">
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label for="company_info">Company Information</label>
                            <textarea id="company_info" name="company_info" rows="5" 
                                      placeholder="Enter information about your company. This will appear on your ESG reports."><?php echo htmlspecialchars($organization_data['company_info'] ?? ''); ?></textarea>
                            <p class="help-text">This information will be included in your downloaded reports.</p>
                        </div>
                    </div>

                    <div class="action-buttons">
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

