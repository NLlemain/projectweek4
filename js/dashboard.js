let energyChart;
let sourcesChart;

document.addEventListener('DOMContentLoaded', function() {
    // Initialize charts
    initializeCharts();
    
    // Handle period switching
    const periodButtons = document.querySelectorAll('.chart-period');
    periodButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all buttons
            periodButtons.forEach(btn => btn.classList.remove('active'));
            // Add active class to clicked button
            this.classList.add('active');
            
            const period = this.dataset.period;
            updateChartData(period);
        });
    });

    // Auto-refresh data every 30 seconds
    setInterval(function() {
        const activePeriod = document.querySelector('.chart-period.active').dataset.period || 'week';
        updateChartData(activePeriod);
    }, 30000);
});

function initializeCharts() {
    // Energy Consumption Chart
    const consumptionCtx = document.getElementById('energyChart');
    if (consumptionCtx) {
        energyChart = new Chart(consumptionCtx, {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Energy Usage (kWh)',
                    data: [12.5, 11.8, 13.2, 10.9, 14.1, 15.3, 13.7],
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }, {
                    label: 'Solar Generated (kWh)',
                    data: [18.2, 16.8, 19.1, 15.2, 20.3, 22.1, 19.8],
                    borderColor: '#f59e0b',
                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#f1f5f9'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom'
                    }
                }
            }
        });
    }

    // Energy Sources Doughnut Chart
    const sourcesCtx = document.getElementById('sourcesChart');
    if (sourcesCtx) {
        sourcesChart = new Chart(sourcesCtx, {
            type: 'doughnut',
            data: {
                labels: ['Solar', 'Grid'],
                datasets: [{
                    data: [70, 30],
                    backgroundColor: ['#f59e0b', '#6366f1'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }
}

async function updateChartData(period = 'week') {
    try {
        const response = await fetch(`api/chart-data.php?period=${period}`);
        const data = await response.json();
        
        if (energyChart) {
            energyChart.data.labels = data.labels;
            energyChart.data.datasets[0].data = data.usage_data;
            energyChart.data.datasets[1].data = data.solar_data;
            energyChart.update();
        }
        
        // Update chart title
        const chartTitle = document.querySelector('.chart-title');
        if (chartTitle) {
            chartTitle.textContent = `Energy Consumption - ${data.title}`;
        }
        
    } catch (error) {
        console.error('Error updating chart data:', error);
    }
}
