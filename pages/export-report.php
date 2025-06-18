<?php
require_once '../config/database.php';
require_once '../classes/EnergyData.php';

if ($_POST && isset($_POST['generate_report'])) {
    $report_type = $_POST['report_type'] ?? 'monthly';
    $date_from = $_POST['date_from'] ?? '';
    $date_to = $_POST['date_to'] ?? '';
    $format = $_POST['format'] ?? 'pdf';
    
    // Generate report logic here
    $report_generated = true;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export Report - SmartEnergy</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/pages.css">
</head>
<body>
    <div class="container">
        <header>
            <div class="app-icon">S</div>
            <h1>Export Report</h1>
            <a href="../index.php" class="back-btn">← Back to Dashboard</a>
        </header>

        <div class="page-content">
            <div class="export-grid">
                <div class="export-form-card">
                    <h3>Generate Report</h3>
                    <form method="POST" class="export-form">
                        <div class="form-group">
                            <label>Report Type</label>
                            <select name="report_type" required>
                                <option value="daily">Daily Report</option>
                                <option value="weekly">Weekly Report</option>
                                <option value="monthly" selected>Monthly Report</option>
                                <option value="yearly">Yearly Report</option>
                                <option value="custom">Custom Range</option>
                            </select>
                        </div>
                        
                        <div class="form-row" id="date-range" style="display: none;">
                            <div class="form-group">
                                <label>From Date</label>
                                <input type="date" name="date_from">
                            </div>
                            <div class="form-group">
                                <label>To Date</label>
                                <input type="date" name="date_to">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Export Format</label>
                            <div class="format-options">
                                <label><input type="radio" name="format" value="pdf" checked> PDF</label>
                                <label><input type="radio" name="format" value="excel"> Excel</label>
                                <label><input type="radio" name="format" value="csv"> CSV</label>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Include Data</label>
                            <div class="data-options">
                                <label><input type="checkbox" name="include[]" value="usage" checked> Energy Usage</label>
                                <label><input type="checkbox" name="include[]" value="solar" checked> Solar Generation</label>
                                <label><input type="checkbox" name="include[]" value="cost" checked> Cost Analysis</label>
                                <label><input type="checkbox" name="include[]" value="efficiency" checked> Efficiency Metrics</label>
                            </div>
                        </div>
                        
                        <button type="submit" name="generate_report" class="submit-btn">Generate Report</button>
                    </form>
                </div>

                <div class="recent-reports-card">
                    <h3>Recent Reports</h3>
                    <div class="report-list">
                        <div class="report-item">
                            <div class="report-info">
                                <div class="report-name">Monthly Report - November 2024</div>
                                <div class="report-date">Generated: Nov 30, 2024</div>
                            </div>
                            <a href="../api/generate-report.php?type=monthly&format=pdf" class="download-btn">Download PDF</a>
                        </div>
                        <div class="report-item">
                            <div class="report-info">
                                <div class="report-name">Weekly Report - Week 47</div>
                                <div class="report-date">Generated: Nov 24, 2024</div>
                            </div>
                            <a href="../api/generate-report.php?type=weekly&format=excel" class="download-btn">Download Excel</a>
                        </div>
                        <div class="report-item">
                            <div class="report-info">
                                <div class="report-name">Daily Report - November 15</div>
                                <div class="report-date">Generated: Nov 15, 2024</div>
                            </div>
                            <a href="../api/generate-report.php?type=daily&format=csv" class="download-btn">Download CSV</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.querySelector('select[name="report_type"]').addEventListener('change', function() {
            const dateRange = document.getElementById('date-range');
            if (this.value === 'custom') {
                dateRange.style.display = 'flex';
            } else {
                dateRange.style.display = 'none';
            }
        });

        document.querySelector('.export-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const reportType = formData.get('report_type');
            const format = formData.get('format');
            const dateFrom = formData.get('date_from');
            const dateTo = formData.get('date_to');
            
            let url = `../api/generate-report.php?type=${reportType}&format=${format}`;
            if (dateFrom) url += `&date_from=${dateFrom}`;
            if (dateTo) url += `&date_to=${dateTo}`;
            
            // Show loading message
            const submitBtn = this.querySelector('.submit-btn');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = 'Generating...';
            submitBtn.disabled = true;
            
            // Create download link
            const link = document.createElement('a');
            link.href = url;
            link.download = `energy-report-${reportType}-${new Date().toISOString().split('T')[0]}.${format === 'excel' ? 'xls' : format}`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            // Reset button
            setTimeout(() => {
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            }, 2000);
        });
    </script>
</body>
</html>
            }, 2000);
        });
    </script>
</body>
</html>
