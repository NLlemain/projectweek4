<?php
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../classes/EnergyData.php';

$period = $_GET['period'] ?? 'week';

$database = new Database();
$db = $database->getConnection();
$energyData = new EnergyData($db);

$chartData = getChartData($period, $db);

echo json_encode($chartData);

function getChartData($period, $db) {
    try {
        switch ($period) {
            case 'day':
                $stmt = $db->query("SELECT 
                    HOUR(tijdstip) as hour,
                    AVG(stroomverbruik_woning) as avg_usage,
                    AVG(zonnepaneelspanning * zonnepaneelstroom) as avg_solar
                    FROM energy_data 
                    WHERE tijdstip >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                    GROUP BY HOUR(tijdstip)
                    ORDER BY hour");
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $labels = [];
                $usage_data = [];
                $solar_data = [];
                
                foreach ($data as $row) {
                    $labels[] = sprintf('%02d:00', $row['hour']);
                    $usage_data[] = round($row['avg_usage'], 3);
                    $solar_data[] = round($row['avg_solar'], 3);
                }
                
                return [
                    'labels' => $labels,
                    'usage_data' => $usage_data,
                    'solar_data' => $solar_data,
                    'title' => 'Last 24 Hours'
                ];
                
            case 'week':
                $stmt = $db->query("SELECT 
                    DATE(tijdstip) as date,
                    AVG(stroomverbruik_woning) as avg_usage,
                    AVG(zonnepaneelspanning * zonnepaneelstroom) as avg_solar
                    FROM energy_data 
                    WHERE tijdstip >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                    GROUP BY DATE(tijdstip)
                    ORDER BY date");
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $labels = [];
                $usage_data = [];
                $solar_data = [];
                
                foreach ($data as $row) {
                    $labels[] = date('D', strtotime($row['date']));
                    $usage_data[] = round($row['avg_usage'], 3);
                    $solar_data[] = round($row['avg_solar'], 3);
                }
                
                return [
                    'labels' => $labels,
                    'usage_data' => $usage_data,
                    'solar_data' => $solar_data,
                    'title' => 'Last 7 Days'
                ];
                
            case 'month':
                $stmt = $db->query("SELECT 
                    WEEK(tijdstip) as week,
                    AVG(stroomverbruik_woning) as avg_usage,
                    AVG(zonnepaneelspanning * zonnepaneelstroom) as avg_solar
                    FROM energy_data 
                    WHERE tijdstip >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    GROUP BY WEEK(tijdstip)
                    ORDER BY week");
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $labels = [];
                $usage_data = [];
                $solar_data = [];
                
                foreach ($data as $i => $row) {
                    $labels[] = 'Week ' . ($i + 1);
                    $usage_data[] = round($row['avg_usage'], 3);
                    $solar_data[] = round($row['avg_solar'], 3);
                }
                
                return [
                    'labels' => $labels,
                    'usage_data' => $usage_data,
                    'solar_data' => $solar_data,
                    'title' => 'Last Month'
                ];
        }
    } catch (PDOException $e) {
        // Return empty data on error
        return [
            'labels' => [],
            'usage_data' => [],
            'solar_data' => [],
            'title' => 'No Data Available'
        ];
    }
}
?>
