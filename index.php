<?php
require_once 'config/database.php';
require_once 'classes/EnergyData.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    $energyData = new EnergyData($db);

    // Get all data with error handling
    $currentUsage = $energyData->getCurrentUsage();
    $solarGenerated = $energyData->getSolarGenerated();
    $batteryStored = $energyData->getBatteryStored();
    $energyCost = $energyData->getEnergyCost();
    $batteryPercentage = $energyData->getBatteryPercentage();
    $topConsumers = $energyData->getTopConsumers();
    $efficiency = $energyData->getEfficiencyScore();

    // Calculate percentage changes
    $usageChange = $energyData->getPercentageChange('usage');
    $solarChange = $energyData->getPercentageChange('solar');
    $costChange = $energyData->getPercentageChange('cost');
    
} catch (Exception $e) {
    // If there's a database error, use default values
    $currentUsage = 2.4;
    $solarGenerated = 18.7;
    $batteryStored = 7.5;
    $energyCost = 127.50;
    $batteryPercentage = 75;
    $topConsumers = ['air_conditioning' => 42, 'water_heater' => 28, 'lighting' => 15, 'kitchen' => 15];
    $efficiency = ['efficiency_score' => 85, 'peak_optimization' => 'Good', 'solar_utilization' => 'Excellent'];
    $usageChange = -12;
    $solarChange = 8;
    $costChange = 5;
    
    // Optionally show error for debugging
    // echo "Database Error: " . $e->getMessage();
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
        
        <header>
            <div class="app-icon">S</div>
            <h1>SmartEnergy</h1>
            <div class="live-indicator">
                <span class="dot"></span>
                Live
            </div>
        </header>

        <!-- Main Stats Cards -->
        <div class="main-stats">
            <div class="stat-card usage">
                <div class="stat-header">
                    <div class="stat-icon usage">⚡</div>
                    <div class="stat-label">Current Usage</div>
                    <div class="stat-period">Live</div>
                </div>
                <div class="stat-value"><?php echo number_format($currentUsage, 1); ?> kW</div>
                <div class="stat-change <?php echo $usageChange < 0 ? 'negative' : 'positive'; ?>">
                    <?php echo ($usageChange < 0 ? '↓' : '↑') . ' ' . abs($usageChange) . '%'; ?> vs yesterday
                </div>
            </div>

            <div class="stat-card solar">
                <div class="stat-header">
                    <div class="stat-icon solar">☀</div>
                    <div class="stat-label">Solar Generated</div>
                    <div class="stat-period">Today</div>
                </div>
                <div class="stat-value"><?php echo number_format($solarGenerated, 1); ?> kWh</div>
                <div class="stat-change <?php echo $solarChange < 0 ? 'negative' : 'positive'; ?>">
                    <?php echo ($solarChange < 0 ? '↓' : '↑') . ' ' . abs($solarChange) . '%'; ?> vs yesterday
                </div>
            </div>

            <div class="stat-card battery">
                <div class="stat-header">
                    <div class="stat-icon battery">🔋</div>
                    <div class="stat-label">Battery Stored</div>
                    <div class="stat-period"><?php echo $batteryPercentage; ?>%</div>
                </div>
                <div class="stat-value"><?php echo number_format($batteryStored, 1); ?> kWh</div>
                <div class="battery-bar">
                    <div class="battery-fill" style="width: <?php echo $batteryPercentage; ?>%"></div>
                </div>
            </div>

            <div class="stat-card cost">
                <div class="stat-header">
                    <div class="stat-icon cost">$</div>
                    <div class="stat-label">Energy Cost</div>
                    <div class="stat-period">This month</div>
                </div>
                <div class="stat-value">$<?php echo number_format($energyCost, 2); ?></div>
                <div class="stat-change <?php echo $costChange < 0 ? 'negative' : 'positive'; ?>">
                    <?php echo ($costChange < 0 ? '↓' : '↑') . ' ' . abs($costChange) . '%'; ?> vs last month
                </div>
            </div>
        </div>

        <!-- Charts and Sources -->
        <div class="dashboard-grid">
            <div class="chart-section">
                <div class="chart-header">
                    <div class="chart-title">Energy Consumption</div>
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

            <div class="sources-section">
                <div class="sources-title">Energy Sources</div>
                <div class="source-legend">
                    <div class="legend-item">
                        <div class="legend-dot solar"></div>
                        Solar 70%
                    </div>
                    <div class="legend-item">
                        <div class="legend-dot grid"></div>
                        Grid 30%
                    </div>
                </div>
                <div class="source-chart-container">
                    <canvas id="sourcesChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Bottom Section -->
        <div class="bottom-section">
            <div class="consumers-card">
                <div class="section-title">Top Energy Consumers</div>
                <div class="consumer-item">
                    <div class="consumer-info">
                        <div class="consumer-icon ac">❄</div>
                        <div class="consumer-name">Air Conditioning</div>
                    </div>
                    <div class="consumer-percent"><?php echo $topConsumers['air_conditioning']; ?>%</div>
                </div>
                <div class="consumer-item">
                    <div class="consumer-info">
                        <div class="consumer-icon heater">🔥</div>
                        <div class="consumer-name">Water Heater</div>
                    </div>
                    <div class="consumer-percent"><?php echo $topConsumers['water_heater']; ?>%</div>
                </div>
                <div class="consumer-item">
                    <div class="consumer-info">
                        <div class="consumer-icon light">💡</div>
                        <div class="consumer-name">Lighting</div>
                    </div>
                    <div class="consumer-percent"><?php echo $topConsumers['lighting']; ?>%</div>
                </div>
                <div class="consumer-item">
                    <div class="consumer-info">
                        <div class="consumer-icon kitchen">🍳</div>
                        <div class="consumer-name">Kitchen</div>
                    </div>
                    <div class="consumer-percent"><?php echo $topConsumers['kitchen']; ?>%</div>
                </div>
            </div>

            <div class="efficiency-card">
                <div class="section-title">Efficiency Score</div>
                <div class="efficiency-score-display">
                    <div class="efficiency-circle">
                        <div class="efficiency-value"><?php echo $efficiency['efficiency_score']; ?>%</div>
                    </div>
                    <div class="efficiency-label">Excellent efficiency rating</div>
                </div>
                <div class="efficiency-details">
                    <div class="efficiency-item">
                        <div class="efficiency-item-label">Peak usage optimization</div>
                        <div class="efficiency-status <?php echo strtolower($efficiency['peak_optimization']); ?>">
                            <?php echo $efficiency['peak_optimization']; ?>
                        </div>
                    </div>
                    <div class="efficiency-item">
                        <div class="efficiency-item-label">Solar utilization</div>
                        <div class="efficiency-status <?php echo strtolower($efficiency['solar_utilization']); ?>">
                            <?php echo $efficiency['solar_utilization']; ?>
                        </div>
                    </div>
                </div>
            </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="js/dashboard.js"></script>
</body>
</html>
