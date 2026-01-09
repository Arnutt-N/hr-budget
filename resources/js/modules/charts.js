/**
 * Charts Module
 * 
 * Chart.js initialization and helpers
 */

import Chart from 'chart.js/auto';

// Chart.js Default Configuration
Chart.defaults.color = '#94a3b8';
Chart.defaults.font.family = '"Noto Sans Thai", sans-serif';

/**
 * Create a line chart for spending trends
 */
export function createTrendChart(canvasId, data) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return null;
    
    return new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.labels || [],
            datasets: [
                {
                    label: 'เป้าหมายสะสม',
                    data: data.target || [],
                    borderColor: '#64748b',
                    borderDash: [5, 5],
                    tension: 0.4,
                    fill: false
                },
                {
                    label: 'เบิกจ่ายจริง',
                    data: data.actual || [],
                    borderColor: '#0ea5e9',
                    backgroundColor: 'rgba(14, 165, 233, 0.1)',
                    fill: true,
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { 
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 20
                    }
                }
            },
            scales: {
                y: {
                    grid: { color: '#334155' },
                    beginAtZero: true,
                    ticks: {
                        callback: (value) => value + '%'
                    }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });
}

/**
 * Create a doughnut chart for category breakdown
 */
export function createCategoryChart(canvasId, data) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return null;
    
    return new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: data.labels || [],
            datasets: [{
                data: data.values || [],
                backgroundColor: [
                    '#3b82f6',
                    '#8b5cf6', 
                    '#10b981',
                    '#f59e0b',
                    '#ef4444'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 15
                    }
                }
            },
            cutout: '70%'
        }
    });
}

/**
 * Create a bar chart
 */
export function createBarChart(canvasId, data) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return null;
    
    return new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.labels || [],
            datasets: [{
                label: data.label || 'จำนวน',
                data: data.values || [],
                backgroundColor: '#0ea5e9',
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    grid: { color: '#334155' },
                    beginAtZero: true
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });
}

// Export Chart class for direct usage
export { Chart };

// Initialize charts on page load
document.addEventListener('DOMContentLoaded', () => {
    initDashboardCharts();
});

/**
 * Initialize dashboard charts
 */
function initDashboardCharts() {
    const trendCanvas = document.getElementById('trendChart');
    const categoryCanvas = document.getElementById('categoryChart');
    
    if (trendCanvas) {
        // Fetch chart data from API
        fetch('/api/dashboard/chart-data')
            .then(res => res.json())
            .then(data => {
                createTrendChart('trendChart', {
                    labels: data.labels,
                    target: [8, 16, 25, 33, 41, 50, 58, 66, 75, 83, 91, 100],
                    actual: data.data
                });
            })
            .catch(() => {
                // Use mock data if API fails
                createTrendChart('trendChart', {
                    labels: ['ต.ค.', 'พ.ย.', 'ธ.ค.', 'ม.ค.', 'ก.พ.', 'มี.ค.'],
                    target: [8, 16, 25, 33, 41, 50],
                    actual: [5, 12, 28, 35, null, null]
                });
            });
    }
    
    if (categoryCanvas) {
        // Get data from page if available
        const chartData = window.chartData?.category;
        
        if (chartData) {
            createCategoryChart('categoryChart', chartData);
        } else {
            // Use mock data
            createCategoryChart('categoryChart', {
                labels: ['งบบุคลากร', 'งบดำเนินงาน', 'งบลงทุน'],
                values: [65, 25, 10]
            });
        }
    }
}
