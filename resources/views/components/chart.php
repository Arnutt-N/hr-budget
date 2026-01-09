<?php
/**
 * Chart Component
 * 
 * Props:
 * - id: Unique ID for the canvas (required)
 * - type: line, bar, doughnut, pie (default: line)
 * - data: Array of data following Chart.js structure ['labels' => [], 'datasets' => []]
 * - height: Height CSS value (default: 300px)
 * - title: Chart Title (optional, renders a header)
 * - footer: Footer content (optional)
 * - class: Additional classes
 */

$id = $id ?? 'chart-' . uniqid();
$type = $type ?? 'line';
$data = $data ?? ['labels' => [], 'datasets' => []];
$height = $height ?? '300px';
$title = $title ?? '';
$footer = $footer ?? '';
$class = $class ?? '';

// Ensure Chart.js defaults are applied via JS, but we can structure the HTML
?>

<div class="bg-dark-card border border-dark-border rounded-xl p-6 shadow-xl <?= $class ?>">
    <?php if ($title): ?>
    <div class="mb-6">
        <h3 class="text-lg font-semibold text-slate-100 flex items-center gap-2">
            <!-- Icon logic can be added here if needed -->
            <?= htmlspecialchars($title) ?>
        </h3>
    </div>
    <?php endif; ?>

    <div style="height: <?= $height ?>; width: 100%; position: relative;">
        <canvas id="<?= $id ?>"></canvas>
    </div>

    <?php if ($footer): ?>
    <div class="mt-4 pt-4 border-t border-dark-border">
        <?= $footer ?>
    </div>
    <?php endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('<?= $id ?>').getContext('2d');
        const chartType = '<?= $type ?>';
        const chartData = <?= json_encode($data) ?>;
        
        // Common Options (Dark Theme + Glassmorphism)
        const commonOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: chartType === 'doughnut' || chartType === 'pie' ? 'right' : 'top',
                    align: 'end',
                    labels: {
                        color: '#94a3b8', // slate-400
                        font: { family: "'Noto Sans Thai', sans-serif", size: 12 },
                        usePointStyle: true,
                        boxWidth: 8,
                        padding: 15
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(15, 23, 42, 0.95)', // dark-900 with opacity
                    titleColor: '#f8fafc',
                    bodyColor: '#cbd5e1',
                    borderColor: '#334155', // slate-700
                    borderWidth: 1,
                    padding: 12,
                    cornerRadius: 8,
                    titleFont: { family: "'Noto Sans Thai', sans-serif", size: 14 },
                    bodyFont: { family: "'Noto Sans Thai', sans-serif", size: 13 },
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += new Intl.NumberFormat('th-TH').format(context.parsed.y);
                            } else if (context.parsed !== null) {
                                // For Pie/Doughnut
                                label += new Intl.NumberFormat('th-TH').format(context.parsed);
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                x: {
                    display: chartType !== 'doughnut' && chartType !== 'pie',
                    grid: { display: false, drawBorder: false },
                    ticks: { color: '#94a3b8', font: { family: "'Noto Sans Thai', sans-serif" } }
                },
                y: {
                    display: chartType !== 'doughnut' && chartType !== 'pie',
                    grid: { color: 'rgba(51, 65, 85, 0.2)', borderDash: [4, 4], drawBorder: false },
                    ticks: { 
                        color: '#94a3b8',
                        font: { family: "'Noto Sans Thai', sans-serif" },
                        callback: function(value) {
                             if (value >= 1000000) return (value/1000000).toFixed(1) + 'M';
                             if (value >= 1000) return (value/1000).toFixed(0) + 'k';
                             return value;
                        }
                    },
                    beginAtZero: true
                }
            }
        };
        
        // Merge Gradient Logic if it's a line chart
        if (chartType === 'line') {
             // Create a simple default gradient for the first dataset
             const gradient = ctx.createLinearGradient(0, 0, 0, 400);
             gradient.addColorStop(0, 'rgba(14, 165, 233, 0.3)'); // sky-500
             gradient.addColorStop(1, 'rgba(14, 165, 233, 0.0)');
             
             if (chartData.datasets && chartData.datasets[0] && !chartData.datasets[0].backgroundColor) {
                 chartData.datasets[0].backgroundColor = gradient;
                 chartData.datasets[0].fill = true;
             }
        }

        new Chart(ctx, {
            type: chartType,
            data: chartData,
            options: commonOptions
        });
    });
</script>
