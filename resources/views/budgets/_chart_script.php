<script>
// Chart Data from PHP
const chartData = <?= json_encode($chartData ?? []) ?>;

// If no data, hide chart
if (chartData.length === 0) {
    document.querySelector('#orgChart').parentElement.parentElement.style.display = 'none';
} else {
    // Prepare data for Chart.js
    const labels = chartData.map(d => d.org_name || 'ไม่ระบุ');
    const allocatedData = chartData.map(d => parseFloat(d.allocated) || 0);
    const disbursedData = chartData.map(d => parseFloat(d.disbursed) || 0);
    
    // Create chart
    const ctx = document.getElementById('orgChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'งบจัดสรร',
                    data: allocatedData,
                    backgroundColor: 'rgba(56, 189, 248, 0.5)',
                    borderColor: 'rgba(56, 189, 248, 1)',
                    borderWidth: 1
                },
                {
                    label: 'เบิกจ่าย',
                    data: disbursedData,
                    backgroundColor: 'rgba(74, 222, 128, 0.5)',
                    borderColor: 'rgba(74, 222, 128, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return (value / 1000000).toFixed(1) + 'M';
                        },
                        color: '#94a3b8'
                    },
                    grid: {
                        color: '#334155'
                    }
                },
                x: {
                    ticks: {
                        color: '#94a3b8'
                    },
                    grid: {
                        color: '#334155'
                    }
                }
            },
            plugins: {
                legend: {
                    labels: {
                        color: '#f1f5f9'
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            label += new Intl.NumberFormat('th-TH', { style: 'currency', currency: 'THB' }).format(context.parsed.y);
                            return label;
                        }
                    }
                }
            }
        }
    });
}
</script>
</div>
