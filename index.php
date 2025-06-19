<?php
require_once 'config/database.php';
require_once 'classes/EnergyData.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    $energyData = new EnergyData($db);

    // Get all real data from database
    $currentUsage = $energyData->getCurrentUsage();
    $solarVoltage = $energyData->getSolarVoltage();
    $solarCurrent = $energyData->getSolarCurrent();
    $solarPower = $energyData->getSolarPower();
    $batteryLevel = $energyData->getBatteryLevel();
    $hydrogenProduction = $energyData->getHydrogenProduction();
    $hydrogenStorage = $energyData->getHydrogenStorage();
    $temperatureData = $energyData->getTemperatureData();
    $environmentalData = $energyData->getEnvironmentalData();
    $latestReadings = $energyData->getLatestReadings();

    // Calculate percentage changes
    $usageChange = $energyData->getPercentageChange('usage');
    $solarChange = $energyData->getPercentageChange('solar');
    $batteryChange = $energyData->getPercentageChange('battery');
    $hydrogenChange = $energyData->getPercentageChange('hydrogen');
    
} catch (Exception $e) {
    $error_message = "Database connection failed: " . $e->getMessage();
    // Set all values to 0 on error
    $currentUsage = $solarVoltage = $solarCurrent = $solarPower = 0;
    $batteryLevel = $hydrogenProduction = $hydrogenStorage = 0;
    $temperatureData = ['outdoor' => 0, 'indoor' => 0];
    $environmentalData = ['pressure' => 0, 'humidity' => 0, 'co2' => 0];
    $latestReadings = [];
    $usageChange = $solarChange = $batteryChange = $hydrogenChange = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartEnergy Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <?php if (isset($error_message)): ?>
        <div class="error-message">
            <?php echo $error_message; ?>
        </div>
        <?php endif; ?>
        
        <header>
            <div class="app-icon">S</div>
            <h1>SmartEnergy Dashboard</h1>
            <div class="live-indicator">
                <span class="dot"></span>
                Live Data
            </div>
            <button class="edit-mode-btn" onclick="toggleEditMode()">
                <span class="edit-icon">✏️</span>
                <span class="edit-text">Edit</span>
            </button>
        </header>

        <!-- Edit Mode Controls (hidden by default) -->
        <div class="edit-controls" id="editControls" style="display: none;">
            <div class="edit-toolbar">
                <div class="toolbar-section">
                    <h3>Customize Dashboard</h3>
                </div>
                <div class="toolbar-section">
                    <button class="toolbar-btn" onclick="resetLayout()">Reset Layout</button>
                    <button class="toolbar-btn" onclick="saveChanges()">Save Changes</button>
                    <button class="toolbar-btn cancel" onclick="toggleEditMode()">Cancel</button>
                </div>
            </div>
        </div>

        <!-- Main Energy Stats -->
        <div class="main-stats editable-container" data-container="main-stats">
            <div class="stat-card usage editable-element" data-element="usage-card">
                <div class="edit-handle" style="display: none;">
                    <span class="drag-icon">⋮⋮</span>
                    <div class="element-controls">
                        <button class="control-btn edit-btn" onclick="openAdvancedEdit(this)" title="Advanced Edit">✏️</button>
                        <button class="control-btn color-btn" onclick="openColorPicker(this)" title="Quick Colors">🎨</button>
                        <button class="control-btn remove-btn" onclick="removeElement(this)" title="Delete">🗑️</button>
                    </div>
                </div>
                <div class="stat-header">
                    <div class="stat-icon usage">⚡</div>
                    <div class="stat-label">Stroomverbruik Woning</div>
                    <div class="stat-period">Current</div>
                </div>
                <div class="stat-value"><?php echo number_format($currentUsage, 3); ?> kW</div>
                <div class="stat-change <?php echo $usageChange < 0 ? 'negative' : 'positive'; ?>">
                    <?php echo ($usageChange < 0 ? '↓' : '↑') . ' ' . abs($usageChange) . '%'; ?> vs previous
                </div>
            </div>

            <div class="stat-card solar editable-element" data-element="solar-card">
                <div class="edit-handle" style="display: none;">
                    <span class="drag-icon">⋮⋮</span>
                    <div class="element-controls">
                        <button class="control-btn edit-btn" onclick="openAdvancedEdit(this)" title="Advanced Edit">✏️</button>
                        <button class="control-btn color-btn" onclick="openColorPicker(this)" title="Quick Colors">🎨</button>
                        <button class="control-btn remove-btn" onclick="removeElement(this)" title="Delete">🗑️</button>
                    </div>
                </div>
                <div class="stat-header">
                    <div class="stat-icon solar">☀</div>
                    <div class="stat-label">Zonnepaneel Vermogen</div>
                    <div class="stat-period">Current</div>
                </div>
                <div class="stat-value"><?php echo number_format($solarPower, 2); ?> W</div>
                <div class="stat-subtitle">
                    <?php echo number_format($solarVoltage, 2); ?>V × <?php echo number_format($solarCurrent, 3); ?>A
                </div>
                <div class="stat-change <?php echo $solarChange < 0 ? 'negative' : 'positive'; ?>">
                    <?php echo ($solarChange < 0 ? '↓' : '↑') . ' ' . abs($solarChange) . '%'; ?> vs previous
                </div>
            </div>

            <div class="stat-card battery editable-element" data-element="battery-card">
                <div class="edit-handle" style="display: none;">
                    <span class="drag-icon">⋮⋮</span>
                    <div class="element-controls">
                        <button class="control-btn edit-btn" onclick="openAdvancedEdit(this)" title="Advanced Edit">✏️</button>
                        <button class="control-btn color-btn" onclick="openColorPicker(this)" title="Quick Colors">🎨</button>
                        <button class="control-btn remove-btn" onclick="removeElement(this)" title="Delete">🗑️</button>
                    </div>
                </div>
                <div class="stat-header">
                    <div class="stat-icon battery">🔋</div>
                    <div class="stat-label">Accu Niveau</div>
                    <div class="stat-period">Current</div>
                </div>
                <div class="stat-value"><?php echo number_format($batteryLevel, 2); ?>%</div>
                <div class="battery-bar">
                    <div class="battery-fill" style="width: <?php echo min(100, max(0, $batteryLevel)); ?>%"></div>
                </div>
                <div class="stat-change <?php echo $batteryChange < 0 ? 'negative' : 'positive'; ?>">
                    <?php echo ($batteryChange < 0 ? '↓' : '↑') . ' ' . abs($batteryChange) . '%'; ?> vs previous
                </div>
            </div>

            <div class="stat-card hydrogen editable-element" data-element="hydrogen-card">
                <div class="edit-handle" style="display: none;">
                    <span class="drag-icon">⋮⋮</span>
                    <div class="element-controls">
                        <button class="control-btn edit-btn" onclick="openAdvancedEdit(this)" title="Advanced Edit">✏️</button>
                        <button class="control-btn color-btn" onclick="openColorPicker(this)" title="Quick Colors">🎨</button>
                        <button class="control-btn remove-btn" onclick="removeElement(this)" title="Delete">🗑️</button>
                    </div>
                </div>
                <div class="stat-header">
                    <div class="stat-icon hydrogen">💧</div>
                    <div class="stat-label">Waterstof Productie</div>
                    <div class="stat-period">Current</div>
                </div>
                <div class="stat-value"><?php echo number_format($hydrogenProduction, 4); ?></div>
                <div class="stat-change <?php echo $hydrogenChange < 0 ? 'negative' : 'positive'; ?>">
                    <?php echo ($hydrogenChange < 0 ? '↓' : '↑') . ' ' . abs($hydrogenChange) . '%'; ?> vs previous
                </div>
            </div>  
        </div>

        <!-- Charts and Real-time Data -->
        <div class="dashboard-grid editable-container" data-container="dashboard-grid">
            <div class="chart-section editable-element" data-element="chart-section">
                <div class="edit-handle" style="display: none;">
                    <span class="drag-icon">⋮⋮</span>
                    <div class="element-controls">
                        <button class="control-btn color-btn" onclick="openColorPicker(this)">🎨</button>
                        <button class="control-btn remove-btn" onclick="removeElement(this)">🗑️</button>
                    </div>
                </div>
                <div class="chart-header">
                    <div class="chart-title">Energy Monitoring</div>
                    <div class="chart-controls">
                        <button class="chart-period active" data-period="day">Last Day</button>
                        <button class="chart-period" data-period="week">Last 7 Days</button>
                        <button class="chart-period" data-period="month">Last Month</button>
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="energyChart"></canvas>
                </div>
            </div>

            <div class="sources-section editable-element" data-element="sources-section">
                <div class="edit-handle" style="display: none;">
                    <span class="drag-icon">⋮⋮</span>
                    <div class="element-controls">
                        <button class="control-btn color-btn" onclick="openColorPicker(this)">🎨</button>
                        <button class="control-btn remove-btn" onclick="removeElement(this)">🗑️</button>
                    </div>
                </div>
                <div class="sources-title">System Status</div>
                <div class="status-grid">
                    <div class="status-item">
                        <span class="status-label">Power Usage</span>
                        <span class="status-value"><?php echo number_format($currentUsage, 3); ?> kW</span>
                    </div>
                    <div class="status-item">
                        <span class="status-label">Solar Output</span>
                        <span class="status-value"><?php echo number_format($solarPower, 2); ?> W</span>
                    </div>
                    <div class="status-item">
                        <span class="status-label">Battery Level</span>
                        <span class="status-value"><?php echo number_format($batteryLevel, 1); ?>%</span>
                    </div>
                    <div class="status-item">
                        <span class="status-label">H2 Production</span>
                        <span class="status-value"><?php echo number_format($hydrogenProduction, 4); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Real-time Data Table -->
        <div class="bottom-section editable-container" data-container="bottom-section">
            <div class="data-table-card editable-element" data-element="data-table">
                <div class="edit-handle" style="display: none;">
                    <span class="drag-icon">⋮⋮</span>
                    <div class="element-controls">
                        <button class="control-btn color-btn" onclick="openColorPicker(this)">🎨</button>
                        <button class="control-btn remove-btn" onclick="removeElement(this)">🗑️</button>
                    </div>
                </div>
                <div class="section-title">Latest Sensor Readings</div>
                <div class="data-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Parameter</th>
                                <th>Current Value</th>
                                <th>Unit</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Stroomverbruik Woning</td>
                                <td><?php echo number_format($currentUsage, 3); ?></td>
                                <td>kW</td>
                                <td>
                                    <span class="sensor-status-badge <?php echo $currentUsage > 0 ? 'active' : 'inactive'; ?>">
                                        <span class="status-indicator <?php echo $currentUsage > 0 ? 'active' : 'inactive'; ?>"></span>
                                        <?php echo $currentUsage > 0 ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td>Zonnepaneel Spanning</td>
                                <td><?php echo number_format($solarVoltage, 2); ?></td>
                                <td>V</td>
                                <td>
                                    <span class="sensor-status-badge <?php echo $solarVoltage > 0 ? 'active' : 'inactive'; ?>">
                                        <span class="status-indicator <?php echo $solarVoltage > 0 ? 'active' : 'inactive'; ?>"></span>
                                        <?php echo $solarVoltage > 0 ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td>Zonnepaneel Stroom</td>
                                <td><?php echo number_format($solarCurrent, 3); ?></td>
                                <td>A</td>
                                <td>
                                    <span class="sensor-status-badge <?php echo $solarCurrent > 0 ? 'active' : 'inactive'; ?>">
                                        <span class="status-indicator <?php echo $solarCurrent > 0 ? 'active' : 'inactive'; ?>"></span>
                                        <?php echo $solarCurrent > 0 ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td>Accu Niveau</td>
                                <td><?php echo number_format($batteryLevel, 2); ?></td>
                                <td>%</td>
                                <td>
                                    <span class="sensor-status-badge <?php echo $batteryLevel > 20 ? 'active' : 'warning'; ?>">
                                        <span class="status-indicator <?php echo $batteryLevel > 20 ? 'active' : 'warning'; ?>"></span>
                                        <?php echo $batteryLevel > 20 ? 'Good' : 'Low'; ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td>Waterstof Productie</td>
                                <td><?php echo number_format($hydrogenProduction, 4); ?></td>
                                <td>-</td>
                                <td>
                                    <span class="sensor-status-badge <?php echo $hydrogenProduction > 0 ? 'active' : 'inactive'; ?>">
                                        <span class="status-indicator <?php echo $hydrogenProduction > 0 ? 'active' : 'inactive'; ?>"></span>
                                        <?php echo $hydrogenProduction > 0 ? 'Producing' : 'Standby'; ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td>Waterstof Opslag</td>
                                <td><?php echo number_format($hydrogenStorage, 1); ?></td>
                                <td>%</td>
                                <td>
                                    <span class="sensor-status-badge <?php echo $hydrogenStorage > 10 ? 'active' : 'warning'; ?>">
                                        <span class="status-indicator <?php echo $hydrogenStorage > 10 ? 'active' : 'warning'; ?>"></span>
                                        <?php echo $hydrogenStorage > 10 ? 'Good' : 'Low'; ?>
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="environmental-card editable-element" data-element="environmental-card">
                <div class="edit-handle" style="display: none;">
                    <span class="drag-icon">⋮⋮</span>
                    <div class="element-controls">
                        <button class="control-btn color-btn" onclick="openColorPicker(this)">🎨</button>
                        <button class="control-btn remove-btn" onclick="removeElement(this)">🗑️</button>
                    </div>
                </div>
                <div class="section-title">Environmental Conditions</div>
                <div class="env-grid">
                    <div class="env-item">
                        <div class="env-icon">🌡️</div>
                        <div class="env-data">
                            <div class="env-label">Outdoor Temperature</div>
                            <div class="env-value"><?php echo number_format($temperatureData['outdoor'], 1); ?>°C</div>
                        </div>
                    </div>
                    <div class="env-item">
                        <div class="env-icon">🏠</div>
                        <div class="env-data">
                            <div class="env-label">Indoor Temperature</div>
                            <div class="env-value"><?php echo number_format($temperatureData['indoor'], 1); ?>°C</div>
                        </div>
                    </div>
                    <div class="env-item">
                        <div class="env-icon">📊</div>
                        <div class="env-data">
                            <div class="env-label">Air Pressure</div>
                            <div class="env-value"><?php echo number_format($environmentalData['pressure'], 1); ?> hPa</div>
                        </div>
                    </div>
                    <div class="env-item">
                        <div class="env-icon">💧</div>
                        <div class="env-data">
                            <div class="env-label">Humidity</div>
                            <div class="env-value"><?php echo number_format($environmentalData['humidity'], 1); ?>%</div>
                        </div>
                    </div>
                    <div class="env-item">
                        <div class="env-icon">🌫️</div>
                        <div class="env-data">
                            <div class="env-label">CO2 Concentration</div>
                            <div class="env-value"><?php echo number_format($environmentalData['co2'], 0); ?> ppm</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="actions-card editable-element" data-element="actions-card">
                <div class="edit-handle" style="display: none;">
                    <span class="drag-icon">⋮⋮</span>
                    <div class="element-controls">
                        <button class="control-btn color-btn" onclick="openColorPicker(this)">🎨</button>
                        <button class="control-btn remove-btn" onclick="removeElement(this)">🗑️</button>
                    </div>
                </div>
                <div class="section-title">Quick Actions</div>
                <div class="action-buttons">
                    <a href="pages/schedule-usage.php" class="action-btn">
                        <div style="display: flex; align-items: center;">
                            <div class="action-btn-icon"></div>
                            Schedule Usage
                        </div>
                        <div class="action-btn-arrow">›</div>
                    </a>
                    <a href="pages/energy-settings.php" class="action-btn">
                        <div style="display: flex; align-items: center;">
                            <div class="action-btn-icon"></div>
                            Energy Settings
                        </div>
                        <div class="action-btn-arrow">›</div>
                    </a>
                    <a href="pages/export-report.php" class="action-btn">
                        <div style="display: flex; align-items: center;">
                            <div class="action-btn-icon"></div>
                            Export Report
                        </div>
                        <div class="action-btn-arrow">›</div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Color Picker Modal -->
    <div class="color-picker-modal" id="colorPickerModal" style="display: none;">
        <div class="color-picker-content">
            <h3>Customize Colors</h3>
            <div class="color-options">
                <div class="color-section">
                    <label for="bgColorPicker">Background Color:</label>
                    <input type="color" id="bgColorPicker" value="#ffffff">
                </div>
                <div class="color-section">
                    <label for="borderColorPicker">Border Color:</label>
                    <input type="color" id="borderColorPicker" value="#3b82f6">
                </div>
                <div class="color-section">
                    <label for="textColorPicker">Text Color:</label>
                    <input type="color" id="textColorPicker" value="#1e293b">
                </div>
            </div>
            <div class="color-picker-actions">
                <button class="apply-colors-btn" onclick="applyColors()">Apply</button>
                <button class="cancel-colors-btn" onclick="closeColorPicker()">Cancel</button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="js/dashboard.js"></script>
    <script src="js/edit-mode.js"></script>
</body>
</html>
