<?php
require_once '../config/database.php';
require_once '../classes/EnergyData.php';

if ($_POST) {
    // Handle schedule form submission
    $device = $_POST['device'] ?? '';
    $start_time = $_POST['start_time'] ?? '';
    $end_time = $_POST['end_time'] ?? '';
    $days = $_POST['days'] ?? [];
    
    // Save to database (you can create a schedules table)
    $success = true; // Placeholder
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Usage - SmartEnergy</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/pages.css">
</head>
<body>
    <div class="container">
        <header>
            <div class="app-icon">S</div>
            <h1>Schedule Usage</h1>
            <a href="../index.php" class="back-btn">← Back to Dashboard</a>
        </header>

        <div class="page-content">
            <div class="schedule-form-card">
                <h3>Create New Schedule</h3>
                <form method="POST" class="schedule-form">
                    <div class="form-group">
                        <label>Device</label>
                        <select name="device" required>
                            <option value="">Select Device</option>
                            <option value="air_conditioning">Air Conditioning</option>
                            <option value="water_heater">Water Heater</option>
                            <option value="lighting">Lighting</option>
                            <option value="kitchen">Kitchen Appliances</option>
                        </select>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Start Time</label>
                            <input type="time" name="start_time" required>
                        </div>
                        <div class="form-group">
                            <label>End Time</label>
                            <input type="time" name="end_time" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Days</label>
                        <div class="days-selector">
                            <label><input type="checkbox" name="days[]" value="monday"> Mon</label>
                            <label><input type="checkbox" name="days[]" value="tuesday"> Tue</label>
                            <label><input type="checkbox" name="days[]" value="wednesday"> Wed</label>
                            <label><input type="checkbox" name="days[]" value="thursday"> Thu</label>
                            <label><input type="checkbox" name="days[]" value="friday"> Fri</label>
                            <label><input type="checkbox" name="days[]" value="saturday"> Sat</label>
                            <label><input type="checkbox" name="days[]" value="sunday"> Sun</label>
                        </div>
                    </div>
                    
                    <button type="submit" class="submit-btn">Create Schedule</button>
                </form>
            </div>

            <div class="schedules-list-card">
                <h3>Active Schedules</h3>
                <div class="schedule-item">
                    <div class="schedule-device">🔥 Water Heater</div>
                    <div class="schedule-time">06:00 - 08:00</div>
                    <div class="schedule-days">Mon, Tue, Wed, Thu, Fri</div>
                    <button class="delete-btn">Delete</button>
                </div>
                <div class="schedule-item">
                    <div class="schedule-device">💡 Lighting</div>
                    <div class="schedule-time">18:00 - 23:00</div>
                    <div class="schedule-days">Daily</div>
                    <button class="delete-btn">Delete</button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
