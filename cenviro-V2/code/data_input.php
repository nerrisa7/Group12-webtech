<?php
require_once 'config/auth.php';
requireLogin();

$user = getCurrentUser($conn);
if (!$user) {
    logoutUser();
}

$message = '';
$message_type = '';

function getKpiId($conn, $name_pattern, $category) {
    $pattern = mysqli_real_escape_string($conn, $name_pattern);
    $category = mysqli_real_escape_string($conn, $category);
    $query = "SELECT kpi_id FROM esg_kpis WHERE category = '$category' AND (kpi_name LIKE '%$pattern%') LIMIT 1";
    $result = mysqli_query($conn, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['kpi_id'];
    }
    return null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $category = mysqli_real_escape_string($conn, $_POST['category'] ?? '');
    $period_date = mysqli_real_escape_string($conn, $_POST['period_date'] ?? date('Y-m-d'));
    
    if ($category) {
        $saved = false;
        
        if ($category === 'environmental') {
            $kpi_map = [
                'kpi_electricity_kwh' => 'Energy Consumption',
                'kpi_fuel_liters' => 'Energy Consumption',
                'kpi_water_m3' => 'Water Usage',
                'kpi_waste_kg' => 'Waste Generated',
                'kpi_recycled_kg' => 'Recycling Rate',
                'kpi_scope1_tco2e' => 'Carbon Emissions',
                'kpi_scope2_tco2e' => 'Carbon Emissions'
            ];
            
            foreach ($kpi_map as $field => $kpi_pattern) {
                if (isset($_POST[$field]) && $_POST[$field] !== '') {
                    $value = floatval($_POST[$field]);
                    $kpi_id = getKpiId($conn, $kpi_pattern, 'environmental');
                    if ($kpi_id) {
                        $value = mysqli_real_escape_string($conn, $value);
                        $insert = "INSERT INTO esg_data (user_id, kpi_id, category, value, period_date) 
                                  VALUES ('$user_id', '$kpi_id', 'environmental', '$value', '$period_date')
                                  ON DUPLICATE KEY UPDATE value = '$value', updated_at = NOW()";
                        mysqli_query($conn, $insert);
                        $saved = true;
                    }
                }
            }
        } elseif ($category === 'social') {
            $kpi_map = [
                'kpi_total_employees' => 'Employee Satisfaction',
                'kpi_female_pct' => 'Diversity Index',
                'kpi_training_hours' => 'Training Hours',
                'kpi_incidents' => 'Safety Incidents',
                'kpi_community_hours' => 'Community Engagement'
            ];
            
            foreach ($kpi_map as $field => $kpi_pattern) {
                if (isset($_POST[$field]) && $_POST[$field] !== '') {
                    $value = floatval($_POST[$field]);
                    $kpi_id = getKpiId($conn, $kpi_pattern, 'social');
                    if ($kpi_id) {
                        $value = mysqli_real_escape_string($conn, $value);
                        $insert = "INSERT INTO esg_data (user_id, kpi_id, category, value, period_date) 
                                  VALUES ('$user_id', '$kpi_id', 'social', '$value', '$period_date')
                                  ON DUPLICATE KEY UPDATE value = '$value', updated_at = NOW()";
                        mysqli_query($conn, $insert);
                        $saved = true;
                    }
                }
            }
        } elseif ($category === 'governance') {
            $kpi_map = [
                'kpi_board_meetings' => 'Board Diversity',
                'kpi_independent_directors_pct' => 'Board Diversity',
                'kpi_code_of_conduct' => 'Ethics Training',
                'kpi_anti_corruption_training' => 'Ethics Training'
            ];
            
            foreach ($kpi_map as $field => $kpi_pattern) {
                if (isset($_POST[$field]) && $_POST[$field] !== '') {
                    $value = floatval($_POST[$field]);
                    $kpi_id = getKpiId($conn, $kpi_pattern, 'governance');
                    if ($kpi_id) {
                        $value = mysqli_real_escape_string($conn, $value);
                        $insert = "INSERT INTO esg_data (user_id, kpi_id, category, value, period_date) 
                                  VALUES ('$user_id', '$kpi_id', 'governance', '$value', '$period_date')
                                  ON DUPLICATE KEY UPDATE value = '$value', updated_at = NOW()";
                        mysqli_query($conn, $insert);
                        $saved = true;
                    }
                }
            }
        }
        
        if ($saved) {
            $message = 'Data saved successfully!';
            $message_type = 'success';
        } else {
            $message = 'No data was saved. Please fill in at least one field.';
            $message_type = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Input - Cenviro</title>
    <link rel="stylesheet" href="styles.css">
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
                    <li><a href="data_input.php" class="nav-btn active"><span class="material-symbols-outlined">edit_note</span> Data Input</a></li>
                    <li><a href="reports.php" class="nav-btn"><span class="material-symbols-outlined">description</span> Reports</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <div class="main-title">
                <h2>ESG Data Input</h2>
            </div>

            <?php if ($message): ?>
            <div class="alert <?php echo $message_type; ?>"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <div class="content-section">
                <div class="tabs">
                    <button class="tab-btn active" onclick="openTab(event, 'environmental')">
                        <span class="material-symbols-outlined tab-icon">eco</span>
                        Environmental
                    </button>
                    <button class="tab-btn" onclick="openTab(event, 'social')">
                        <span class="material-symbols-outlined tab-icon">groups</span>
                        Social
                    </button>
                    <button class="tab-btn" onclick="openTab(event, 'governance')">
                        <span class="material-symbols-outlined tab-icon">gavel</span>
                        Governance
                    </button>
                </div>

                <div id="environmental" class="tab-content active">
                    <h3 class="section-title">Environmental KPIs</h3>
                    <form method="POST" action="data_input.php">
                        <input type="hidden" name="category" value="environmental">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="period_date_env">Period Date *</label>
                                <input type="date" id="period_date_env" name="period_date" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                        </div>

                        <div class="form-grid">
                            <div class="form-group">
                                <label for="kpi_electricity_kwh">Electricity Consumption (kWh)</label>
                                <input type="number" step="0.01" id="kpi_electricity_kwh" name="kpi_electricity_kwh" placeholder="e.g., 12500">
                                <p class="help-text">Total electricity used in the period</p>
                            </div>

                            <div class="form-group">
                                <label for="kpi_fuel_liters">Fuel Consumption (Liters)</label>
                                <input type="number" step="0.01" id="kpi_fuel_liters" name="kpi_fuel_liters" placeholder="e.g., 850">
                                <p class="help-text">Diesel, petrol, or other fuels</p>
                            </div>

                            <div class="form-group">
                                <label for="kpi_water_m3">Water Usage (m³)</label>
                                <input type="number" step="0.01" id="kpi_water_m3" name="kpi_water_m3" placeholder="e.g., 450">
                                <p class="help-text">Total water consumption</p>
                            </div>

                            <div class="form-group">
                                <label for="kpi_waste_kg">Total Waste (kg)</label>
                                <input type="number" step="0.01" id="kpi_waste_kg" name="kpi_waste_kg" placeholder="e.g., 2200">
                                <p class="help-text">Total waste generated</p>
                            </div>

                            <div class="form-group">
                                <label for="kpi_recycled_kg">Recycled Waste (kg)</label>
                                <input type="number" step="0.01" id="kpi_recycled_kg" name="kpi_recycled_kg" placeholder="e.g., 1650">
                                <p class="help-text">Amount of waste recycled</p>
                            </div>

                            <div class="form-group">
                                <label for="kpi_scope1_tco2e">Scope 1 Emissions (tCO₂e)</label>
                                <input type="number" step="0.01" id="kpi_scope1_tco2e" name="kpi_scope1_tco2e" placeholder="e.g., 28.5">
                                <p class="help-text">Direct emissions from owned sources</p>
                            </div>

                            <div class="form-group">
                                <label for="kpi_scope2_tco2e">Scope 2 Emissions (tCO₂e)</label>
                                <input type="number" step="0.01" id="kpi_scope2_tco2e" name="kpi_scope2_tco2e" placeholder="e.g., 35.2">
                                <p class="help-text">Indirect emissions from purchased energy</p>
                            </div>
                        </div>

                        <div class="action-buttons">
                            <button type="submit" class="btn-submit">Submit Data</button>
                        </div>
                    </form>
                </div>

                <div id="social" class="tab-content">
                    <h3 class="section-title">Social KPIs</h3>
                    <form method="POST" action="data_input.php">
                        <input type="hidden" name="category" value="social">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="period_date_social">Period Date *</label>
                                <input type="date" id="period_date_social" name="period_date" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                        </div>

                        <div class="form-grid">
                            <div class="form-group">
                                <label for="kpi_total_employees">Total Employees</label>
                                <input type="number" id="kpi_total_employees" name="kpi_total_employees" placeholder="e.g., 45">
                                <p class="help-text">Total number of employees</p>
                            </div>

                            <div class="form-group">
                                <label for="kpi_female_pct">Female Employees (%)</label>
                                <input type="number" step="0.1" id="kpi_female_pct" name="kpi_female_pct" placeholder="e.g., 42.5">
                                <p class="help-text">Percentage of female employees</p>
                            </div>

                            <div class="form-group">
                                <label for="kpi_training_hours">Training Hours</label>
                                <input type="number" step="0.01" id="kpi_training_hours" name="kpi_training_hours" placeholder="e.g., 320">
                                <p class="help-text">Total employee training hours</p>
                            </div>

                            <div class="form-group">
                                <label for="kpi_incidents">Safety Incidents</label>
                                <input type="number" id="kpi_incidents" name="kpi_incidents" placeholder="e.g., 0">
                                <p class="help-text">Number of workplace safety incidents</p>
                            </div>

                            <div class="form-group">
                                <label for="kpi_community_hours">Community Service Hours</label>
                                <input type="number" step="0.01" id="kpi_community_hours" name="kpi_community_hours" placeholder="e.g., 85">
                                <p class="help-text">Hours spent on community initiatives</p>
                            </div>
                        </div>

                        <div class="action-buttons">
                            <button type="submit" class="btn-submit">Submit Data</button>
                        </div>
                    </form>
                </div>

                <div id="governance" class="tab-content">
                    <h3 class="section-title">Governance KPIs</h3>
                    <form method="POST" action="data_input.php">
                        <input type="hidden" name="category" value="governance">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="period_date_gov">Period Date *</label>
                                <input type="date" id="period_date_gov" name="period_date" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                        </div>

                        <div class="form-grid">
                            <div class="form-group">
                                <label for="kpi_board_meetings">Board Meetings</label>
                                <input type="number" id="kpi_board_meetings" name="kpi_board_meetings" placeholder="e.g., 2">
                                <p class="help-text">Number of board meetings held</p>
                            </div>

                            <div class="form-group">
                                <label for="kpi_independent_directors_pct">Independent Directors (%)</label>
                                <input type="number" step="0.1" id="kpi_independent_directors_pct" name="kpi_independent_directors_pct" placeholder="e.g., 40">
                                <p class="help-text">Percentage of independent board members</p>
                            </div>

                            <div class="form-group">
                                <label for="kpi_code_of_conduct">Code of Conduct</label>
                                <select id="kpi_code_of_conduct" name="kpi_code_of_conduct">
                                    <option value="">Select</option>
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </select>
                                <p class="help-text">Organization has a code of conduct</p>
                            </div>

                            <div class="form-group">
                                <label for="kpi_anti_corruption_training">Anti-Corruption Training</label>
                                <select id="kpi_anti_corruption_training" name="kpi_anti_corruption_training">
                                    <option value="">Select</option>
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </select>
                                <p class="help-text">Employees received anti-corruption training</p>
                            </div>
                        </div>

                        <div class="action-buttons">
                            <button type="submit" class="btn-submit">Submit Data</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        function openTab(evt, tabName) {
            var tabs = document.getElementsByClassName("tab-content");
            for (var i = 0; i < tabs.length; i++) {
                tabs[i].classList.remove("active");
            }
            var btns = document.getElementsByClassName("tab-btn");
            for (var i = 0; i < btns.length; i++) {
                btns[i].classList.remove("active");
            }
            document.getElementById(tabName).classList.add("active");
            evt.currentTarget.classList.add("active");
        }
    </script>
</body>
</html>
<?php mysqli_close($conn); ?>

