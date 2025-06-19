<?php
require_once '../config/database.php';

if ($_POST) {
    // Handle settings form submission
    $peak_hours_start = $_POST['peak_hours_start'] ?? '';
    $peak_hours_end = $_POST['peak_hours_end'] ?? '';
    $auto_optimization = isset($_POST['auto_optimization']);
    $solar_priority = isset($_POST['solar_priority']);
    $battery_reserve = $_POST['battery_reserve'] ?? 20;
    
    // Save settings to database
    $success = true;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Energy Settings - SmartEnergy</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/pages.css">
</head>
<body>
    <div class="container">
        <header>
            <div class="app-icon">S</div>
            <h1>Energy Settings</h1>
            <a href="../index.php" class="back-btn">← Back to Dashboard</a>
        </header>

        <div class="page-content">
            <div class="settings-grid">
                <div class="settings-card">
                    <h3>Peak Hours Configuration</h3>
                    <form method="POST" class="settings-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Peak Hours Start</label>
                                <input type="time" name="peak_hours_start" value="17:00">
                            </div>
                            <div class="form-group">
                                <label>Peak Hours End</label>
                                <input type="time" name="peak_hours_end" value="21:00">
                            </div>
                        </div>
                        <button type="submit" class="submit-btn">Save Peak Hours</button>
                    </form>
                </div>

                <div class="settings-card">
                    <h3>Optimization Settings</h3>
                    <form method="POST" class="settings-form">
                        <div class="toggle-group">
                            <label class="toggle-label">
                                <input type="checkbox" name="auto_optimization" checked>
                                <span class="toggle-slider"></span>
                                Auto Optimization
                            </label>
                            <p>Automatically optimize energy usage based on cost and availability</p>
                        </div>
                        
                        <div class="toggle-group">
                            <label class="toggle-label">
                                <input type="checkbox" name="solar_priority" checked>
                                <span class="toggle-slider"></span>
                                Solar Priority
                            </label>
                            <p>Prioritize solar energy over grid power when available</p>
                        </div>
                        
                        <div class="form-group">
                            <label>Battery Reserve (%)</label>
                            <input type="range" name="battery_reserve" min="10" max="50" value="20" class="range-slider">
                            <div class="range-value">20%</div>
                        </div>
                        
                        <button type="submit" class="submit-btn">Save Settings</button>
                    </form>
                </div>

                <div class="settings-card">
                    <h3>Notifications</h3>
                    <div class="notification-list">
                        <div class="notification-item">
                            <span>High energy usage alerts</span>
                            <label class="toggle-label mini">
                                <input type="checkbox" checked>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        <div class="notification-item">
                            <span>Solar generation reports</span>
                            <label class="toggle-label mini">
                                <input type="checkbox" checked>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        <div class="notification-item">
                            <span>Battery low warnings</span>
                            <label class="toggle-label mini">
                                <input type="checkbox">
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
