<?php
require_once '../config/database.php';
require_once '../classes/EnergyData.php';

if (!isset($_GET['type']) || !isset($_GET['format'])) {
    die('Invalid parameters');
}

$database = new Database();
$db = $database->getConnection();
$energyData = new EnergyData($db);

$reportType = $_GET['type'];
$format = $_GET['format'];
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';

// Generate report data
$reportData = generateReportData($energyData, $reportType, $dateFrom, $dateTo);

switch ($format) {
    case 'pdf':
        generatePDF($reportData, $reportType);
        break;
    case 'excel':
        generateExcel($reportData, $reportType);
        break;
    case 'csv':
        generateCSV($reportData, $reportType);
        break;
}

function generateReportData($energyData, $type, $dateFrom = '', $dateTo = '') {
    $data = [
        'title' => ucfirst($type) . ' Energy Report',
        'generated_date' => date('Y-m-d H:i:s'),
        'period' => $type,
        'current_usage' => $energyData->getCurrentUsage(),
        'solar_generated' => $energyData->getSolarGenerated(),
        'battery_stored' => $energyData->getBatteryStored(),
        'energy_cost' => $energyData->getEnergyCost(),
        'top_consumers' => $energyData->getTopConsumers(),
        'efficiency' => $energyData->getEfficiencyScore()
    ];
    
    return $data;
}

function generatePDF($data, $type) {
    // Create PDF content
    $content = "SMARTENERGY REPORT - " . strtoupper($type) . "\n\n";
    $content .= "Generated: " . date('F j, Y \a\t g:i A') . "\n\n";
    $content .= "ENERGY SUMMARY\n";
    $content .= "==============\n";
    $content .= "Current Usage: " . $data['current_usage'] . " kW\n";
    $content .= "Solar Generated: " . $data['solar_generated'] . " kWh\n";
    $content .= "Battery Stored: " . $data['battery_stored'] . " kWh\n";
    $content .= "Energy Cost: $" . $data['energy_cost'] . "\n\n";
    
    $content .= "TOP ENERGY CONSUMERS\n";
    $content .= "===================\n";
    foreach ($data['top_consumers'] as $device => $percentage) {
        $device_name = ucfirst(str_replace('_', ' ', $device));
        $content .= "{$device_name}: {$percentage}%\n";
    }
    
    $content .= "\nEFFICIENCY METRICS\n";
    $content .= "==================\n";
    $content .= "Overall Score: " . $data['efficiency']['efficiency_score'] . "%\n";
    $content .= "Peak Optimization: " . $data['efficiency']['peak_optimization'] . "\n";
    $content .= "Solar Utilization: " . $data['efficiency']['solar_utilization'] . "\n";
    
    // Generate actual PDF
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="energy-report-' . $type . '-' . date('Y-m-d') . '.pdf"');
    header('Cache-Control: no-cache, must-revalidate');
    
    // Simple PDF generation
    $pdf_content = "%PDF-1.4
1 0 obj
<< /Type /Catalog /Pages 2 0 R >>
endobj

2 0 obj
<< /Type /Pages /Kids [3 0 R] /Count 1 >>
endobj

3 0 obj
<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>
endobj

4 0 obj
<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>
endobj

5 0 obj
<< /Length ";
    
    $stream = "BT /F1 14 Tf 50 750 Td (SMARTENERGY REPORT - " . strtoupper($type) . ") Tj 0 -30 Td /F1 10 Tf";
    
    $y_position = -15;
    $lines = explode("\n", $content);
    
    foreach ($lines as $line) {
        if (strlen($line) > 0) {
            $clean_line = str_replace(['(', ')', '\\'], ['\\(', '\\)', '\\\\'], $line);
            $stream .= " 0 {$y_position} Td ({$clean_line}) Tj";
            $y_position = -15;
        } else {
            $y_position = -10;
        }
    }
    
    $stream .= " ET";
    
    $stream_length = strlen($stream);
    $pdf_content .= $stream_length . " >>
stream
" . $stream . "
endstream
endobj

xref
0 6
0000000000 65535 f 
0000000009 00000 n 
0000000058 00000 n 
0000000115 00000 n 
0000000244 00000 n 
0000000317 00000 n 
trailer
<< /Size 6 /Root 1 0 R >>
startxref
" . (400 + $stream_length) . "
%%EOF";
    
    echo $pdf_content;
}

function generateExcel($data, $type) {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="energy-report-' . $type . '-' . date('Y-m-d') . '.xls"');
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    
    echo "<html><body>";
    echo "<h2>Energy Report - " . ucfirst($type) . "</h2>";
    echo "<p>Generated: " . date('F j, Y \a\t g:i A') . "</p>";
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f0f0f0;'><th colspan='2'>Energy Summary</th></tr>";
    echo "<tr><td>Current Usage</td><td>" . $data['current_usage'] . " kW</td></tr>";
    echo "<tr><td>Solar Generated</td><td>" . $data['solar_generated'] . " kWh</td></tr>";
    echo "<tr><td>Battery Stored</td><td>" . $data['battery_stored'] . " kWh</td></tr>";
    echo "<tr><td>Energy Cost</td><td>$" . $data['energy_cost'] . "</td></tr>";
    echo "<tr style='background-color: #f0f0f0;'><th colspan='2'>Top Consumers</th></tr>";
    foreach ($data['top_consumers'] as $device => $percentage) {
        echo "<tr><td>" . ucfirst(str_replace('_', ' ', $device)) . "</td><td>" . $percentage . "%</td></tr>";
    }
    echo "<tr style='background-color: #f0f0f0;'><th colspan='2'>Efficiency</th></tr>";
    echo "<tr><td>Overall Score</td><td>" . $data['efficiency']['efficiency_score'] . "%</td></tr>";
    echo "<tr><td>Peak Optimization</td><td>" . $data['efficiency']['peak_optimization'] . "</td></tr>";
    echo "<tr><td>Solar Utilization</td><td>" . $data['efficiency']['solar_utilization'] . "</td></tr>";
    echo "</table>";
    echo "</body></html>";
}

function generateCSV($data, $type) {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="energy-report-' . $type . '-' . date('Y-m-d') . '.csv"');
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    
    $output = fopen('php://output', 'w');
    
    // Add BOM for UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    fputcsv($output, ['Energy Report - ' . ucfirst($type)]);
    fputcsv($output, ['Generated: ' . date('F j, Y \a\t g:i A')]);
    fputcsv($output, []);
    fputcsv($output, ['Energy Summary']);
    fputcsv($output, ['Metric', 'Value']);
    fputcsv($output, ['Current Usage', $data['current_usage'] . ' kW']);
    fputcsv($output, ['Solar Generated', $data['solar_generated'] . ' kWh']);
    fputcsv($output, ['Battery Stored', $data['battery_stored'] . ' kWh']);
    fputcsv($output, ['Energy Cost', '$' . $data['energy_cost']]);
    fputcsv($output, []);
    fputcsv($output, ['Top Energy Consumers']);
    fputcsv($output, ['Device', 'Percentage']);
    foreach ($data['top_consumers'] as $device => $percentage) {
        fputcsv($output, [ucfirst(str_replace('_', ' ', $device)), $percentage . '%']);
    }
    fputcsv($output, []);
    fputcsv($output, ['Efficiency Metrics']);
    fputcsv($output, ['Overall Score', $data['efficiency']['efficiency_score'] . '%']);
    fputcsv($output, ['Peak Optimization', $data['efficiency']['peak_optimization']]);
    fputcsv($output, ['Solar Utilization', $data['efficiency']['solar_utilization']]);
    
    fclose($output);
}
?>
