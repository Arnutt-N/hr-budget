<div class="space-y-6 animate-fade-in">
    <!-- Header with Fiscal Year Selector -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <p class="text-dark-muted text-sm mt-1">สรุปภาพรวมงบประมาณประจำปี</p>
        </div>
        
        <!-- Fiscal Year Selector -->
        <div class="flex items-center gap-2">
            <label class="text-sm text-dark-muted">ปีงบประมาณ พ.ศ.</label>
            <select id="fiscal-year-select" class="input w-24 font-semibold text-center" style="text-align-last: center;" onchange="changeFiscalYear(this.value)">
                <?php foreach ($fiscalYears as $fy): ?>
                <option value="<?= $fy['value'] ?>" <?= $fy['value'] == $fiscalYear ? 'selected' : '' ?>>
                    <?= $fy['value'] ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Allocated -->
        <div class="bg-dark-card border border-dark-border rounded-xl p-6 card-hover">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <p class="text-dark-muted text-sm font-medium">งบประมาณจัดสรร</p>
                    <h3 class="text-2xl font-bold text-blue-400 mt-1">
                        <?= \App\Core\View::number($stats['allocated'] ?? 0, 2) ?>
                    </h3>
                </div>
                <div class="icon-glass text-blue-400">
                    <i data-lucide="wallet" class="w-6 h-6"></i>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="badge badge-blue">
                    <i data-lucide="calendar" class="w-3 h-3 mr-1"></i>
                    ปี <?= $fiscalYear ?>
                </span>
            </div>
        </div>

        <!-- Total Spent -->
        <div class="bg-dark-card border border-dark-border rounded-xl p-6 card-hover">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <p class="text-dark-muted text-sm font-medium">เบิกจ่ายแล้ว</p>
                    <h3 class="text-2xl font-bold text-orange-400 mt-1">
                        <?= \App\Core\View::number($stats['spent'] ?? 0, 2) ?>
                    </h3>
                </div>
                <div class="icon-glass text-orange-400">
                    <i data-lucide="banknote" class="w-6 h-6"></i>
                </div>
            </div>
        </div>

        <!-- Remaining -->
        <div class="bg-dark-card border border-dark-border rounded-xl p-6 card-hover">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <p class="text-dark-muted text-sm font-medium">คงเหลือ</p>
                    <h3 class="text-2xl font-bold text-green-400 mt-1">
                        <?= \App\Core\View::number($stats['remaining'] ?? 0, 2) ?>
                    </h3>
                </div>
                <div class="icon-glass text-green-400">
                    <i data-lucide="piggy-bank" class="w-6 h-6"></i>
                </div>
            </div>
            <div class="text-xs text-dark-muted">
                โอนเข้า: <?= \App\Core\View::number($stats['transfer_in'] ?? 0, 2) ?>
            </div>
        </div>

        <!-- Disbursement Rate -->
        <div class="bg-dark-card border border-dark-border rounded-xl p-6 card-hover">
            <div class="flex justify-between items-start mb-2">
                <div>
                    <p class="text-dark-muted text-sm font-medium">อัตราการเบิกจ่าย</p>
                    <h3 class="text-2xl font-bold text-white mt-1">
                        <?= $stats['spent_percent'] ?? 0 ?>%
                    </h3>
                </div>
                <?php 
                    $rate = $stats['spent_percent'] ?? 0;
                    $rateColor = $rate >= 80 ? 'green' : ($rate >= 50 ? 'orange' : 'red');
                    $rateTextClass = "text-{$rateColor}-400";
                ?>
                <div class="icon-glass <?= $rateTextClass ?>">
                    <i data-lucide="trending-up" class="w-6 h-6"></i>
                </div>
            </div>
            
            <div class="w-full bg-gray-700 rounded-full h-2.5 mt-2">
                <div class="bg-<?= $rateColor ?>-500 h-2.5 rounded-full" style="width: <?= min(100, $rate) ?>%"></div>
            </div>
            <div class="text-xs text-dark-muted mt-2 text-right">เป้าหมาย: 100%</div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Timeline Trend Chart -->
        <div class="lg:col-span-2 bg-dark-card border border-dark-border rounded-xl p-6">
            <h3 class="text-lg font-semibold text-white mb-4">แนวโน้มการเบิกจ่าย (Timeline)</h3>
            <div class="h-64 relative">
                <canvas id="trendChart"></canvas>
            </div>
        </div>

        <!-- Category Donut Chart -->
        <div class="bg-dark-card border border-dark-border rounded-xl p-6">
            <h3 class="text-lg font-semibold text-white mb-4">สัดส่วนตามหมวดหมู่งบประมาณ</h3>
            <div class="h-64 relative flex justify-center">
                <canvas id="categoryDonutChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Quarterly Disbursement Table with KPI -->
    <div class="bg-slate-900/50 backdrop-blur-sm border border-slate-700 rounded-xl shadow-2xl overflow-hidden">
        <!-- Table Header -->
        <div class="px-6 py-4 border-b border-slate-700 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-slate-100 flex items-center gap-2">
                <i data-lucide="table" class="text-primary-500 w-5 h-5"></i>
                ผลการเบิกจ่ายตามไตรมาส
            </h3>
            <a href="<?= \App\Core\View::url('/budgets/list?year=' . $fiscalYear) ?>" class="text-sm text-primary-500 hover:text-primary-400 transition-colors flex items-center gap-1">
                ดูรายละเอียด <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
        </div>
        
        <!-- Table Wrapper -->
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-800/80 backdrop-blur-sm">
                    <tr class="text-xs uppercase font-semibold text-slate-300">
                        <th class="px-6 py-4 text-left">แผนงาน<br><span class="text-slate-500 normal-case text-[10px]">(Plan)</span></th>
                        <th class="px-4 py-4 text-right whitespace-nowrap">งบจัดสรร<br><span class="text-slate-500 normal-case text-[10px]">(Allocated)</span></th>
                        <th class="px-4 py-4 text-right">Q1</th>
                        <th class="px-4 py-4 text-right">Q2</th>
                        <th class="px-4 py-4 text-right">Q3</th>
                        <th class="px-4 py-4 text-right">Q4</th>
                        <th class="px-4 py-4 text-right whitespace-nowrap">รวมเบิกจ่าย<br><span class="text-slate-500 normal-case text-[10px]">(Total Spent)</span></th>
                        <th class="px-4 py-4 text-right whitespace-nowrap">คงเหลือ<br><span class="text-slate-500 normal-case text-[10px]">(Remaining)</span></th>
                        <th class="px-4 py-4 text-center">KPI %</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    <?php if (!empty($quarterlyData)): ?>
                        <?php foreach ($quarterlyData as $row): ?>
                        <?php 
                            $allocated = (float)($row['allocated'] ?? 0);
                            $totalSpent = (float)($row['total_spent'] ?? 0);
                            $remaining = (float)($row['remaining'] ?? 0);
                            
                            $kpiPercent = $allocated > 0 
                                ? round(($totalSpent / $allocated) * 100, 1) 
                                : 0;
                            $kpiClass = $kpiPercent >= 80 ? 'text-emerald-400' : 
                                       ($kpiPercent >= 50 ? 'text-amber-400' : 'text-red-400');
                            $kpiBg = $kpiPercent >= 80 ? 'bg-emerald-500' : 
                                    ($kpiPercent >= 50 ? 'bg-amber-500' : 'bg-red-500');
                        ?>
                        <tr class="hover:bg-slate-800/30 transition-colors">
                            <td class="px-6 py-3.5 font-medium text-slate-200">
                                <div class="flex items-center gap-2">
                                    <i data-lucide="folder" class="text-blue-400 w-4 h-4"></i>
                                    <span><?= htmlspecialchars($row['category_name'] ?? '-') ?></span>
                                </div>
                            </td>
                            <td class="px-4 py-3.5 text-right tabular-nums text-slate-200"><?= \App\Core\View::number($allocated, 2) ?></td>
                            <td class="px-4 py-3.5 text-right tabular-nums text-slate-400"><?= \App\Core\View::number($row['q1'] ?? 0, 2) ?></td>
                            <td class="px-4 py-3.5 text-right tabular-nums text-slate-400"><?= \App\Core\View::number($row['q2'] ?? 0, 2) ?></td>
                            <td class="px-4 py-3.5 text-right tabular-nums text-slate-400"><?= \App\Core\View::number($row['q3'] ?? 0, 2) ?></td>
                            <td class="px-4 py-3.5 text-right tabular-nums text-slate-400"><?= \App\Core\View::number($row['q4'] ?? 0, 2) ?></td>
                            <td class="px-4 py-3.5 text-right tabular-nums text-emerald-400 font-medium"><?= \App\Core\View::number($totalSpent, 2) ?></td>
                            <td class="px-4 py-3.5 text-right tabular-nums text-slate-200"><?= \App\Core\View::number($remaining, 2) ?></td>
                            <td class="px-4 py-3.5 text-center">
                                <div class="inline-flex items-center gap-1.5">
                                    <div class="w-12 h-1.5 bg-slate-700 rounded-full overflow-hidden">
                                        <div class="h-full <?= $kpiBg ?> rounded-full transition-all" style="width: <?= min($kpiPercent, 100) ?>%"></div>
                                    </div>
                                    <span class="<?= $kpiClass ?> text-xs tabular-nums font-medium"><?= $kpiPercent ?>%</span>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="px-6 py-8 text-center text-slate-400">
                                <i data-lucide="folder-open" class="w-12 h-12 mx-auto mb-2 text-slate-600"></i>
                                <p>ยังไม่มีข้อมูลงบประมาณ</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Chart Scripts -->
<script>
    window.chartData = {
        trend: <?= json_encode($trend ?? []) ?>,
        category: {
            labels: <?= json_encode(array_column($budgetByCategory ?? [], 'category_name')) ?>,
            values: <?= json_encode(array_map('floatval', array_column($budgetByCategory ?? [], 'allocated'))) ?>
        }
    };
    
    function changeFiscalYear(year) {
        window.location.href = '<?= \App\Core\View::url('/dashboard') ?>?year=' + year;
    }

    // Timeline Trend Chart (Line) - Reference Style
    const trendCtx = document.getElementById('trendChart')?.getContext('2d');
    if (trendCtx) {
        // Calculate target line (cumulative target based on allocated budget)
        const allocated = <?= $stats['allocated'] ?? 0 ?>;
        const monthlyTarget = allocated / 12;
        const targetData = Array.from({length: 12}, (_, i) => monthlyTarget * (i + 1));
        
        // Simple Gradient for fill
        const gradientActual = trendCtx.createLinearGradient(0, 0, 0, 300);
        gradientActual.addColorStop(0, 'rgba(14, 165, 233, 0.3)'); // Sky 500
        gradientActual.addColorStop(1, 'rgba(14, 165, 233, 0.05)');
        
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: ['ต.ค.', 'พ.ย.', 'ธ.ค.', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.'],
                datasets: [
                    {
                        label: 'เบิกจ่ายจริง',
                        data: window.chartData?.trend || [],
                        borderColor: '#0ea5e9', // Sky 500 (Solid Blue)
                        backgroundColor: gradientActual,
                        fill: true,
                        tension: 0.4,
                        borderWidth: 3,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: '#0ea5e9',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2
                    },
                    {
                        label: 'เป้าหมายสะสม',
                        data: targetData,
                        borderColor: '#94a3b8', // Slate 400
                        backgroundColor: 'transparent',
                        borderDash: [6, 4],
                        fill: false,
                        tension: 0, // Straight line for target
                        borderWidth: 2,
                        pointRadius: 0,
                        pointHoverRadius: 5,
                        pointBackgroundColor: '#94a3b8',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom',
                        labels: {
                            color: '#cbd5e1',
                            font: { 
                                size: 12, 
                                family: 'Noto Sans Thai, sans-serif'
                            },
                            padding: 20,
                            usePointStyle: true,
                            boxWidth: 60, // Increased length slightly as requested
                            generateLabels: function(chart) {
                                const labels = Chart.defaults.plugins.legend.labels.generateLabels(chart);
                                labels.forEach(label => {
                                    const dataset = chart.data.datasets[label.datasetIndex];
                                    label.pointStyle = 'line';
                                    label.lineWidth = 3; // Make legend line slightly thicker to match chart
                                    label.strokeStyle = dataset.borderColor; // Explicitly set color
                                    
                                    // Apply dash style if exists in dataset
                                    if (dataset.borderDash) {
                                        label.lineDash = dataset.borderDash;
                                    }
                                });
                                return labels;
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.95)',
                        titleColor: '#f1f5f9',
                        bodyColor: '#cbd5e1',
                        borderColor: '#334155',
                        borderWidth: 1,
                        padding: 12,
                        cornerRadius: 8,
                        titleFont: { size: 14, family: 'Noto Sans Thai, sans-serif' },
                        bodyFont: { size: 13, family: 'Noto Sans Thai, sans-serif' },
                        displayColors: true,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += new Intl.NumberFormat('th-TH', {
                                        style: 'decimal',
                                        maximumFractionDigits: 0
                                    }).format(context.parsed.y);
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { 
                            display: false,
                            drawBorder: false
                        },
                        ticks: { color: '#94a3b8', font: { size: 11 } }
                    },
                    y: {
                        grid: { 
                            color: 'rgba(51, 65, 85, 0.2)',
                            drawBorder: false,
                            borderDash: [5, 5] // Make horizontal lines dashed for cleaner look
                        },
                        ticks: {
                            color: '#94a3b8',
                            font: { size: 11 },
                            callback: function(value) {
                                return new Intl.NumberFormat('th-TH', {
                                    notation: 'compact',
                                    compactDisplay: 'short'
                                }).format(value);
                            }
                        },
                        beginAtZero: true
                    }
                }
            }
        });
    }

    // Category Donut Chart
    const donutCtx = document.getElementById('categoryDonutChart')?.getContext('2d');
    if (donutCtx) {
        const fullLabels = window.chartData?.category?.labels || [];
        const shortLabels = fullLabels.map(label => label.length > 20 ? label.substring(0, 20) + '...' : label);
        
        new Chart(donutCtx, {
            type: 'doughnut',
            data: {
                labels: shortLabels,
                datasets: [{
                    data: window.chartData?.category?.values || [],
                    backgroundColor: [
                        '#3b82f6', '#22c55e', '#f97316', '#ef4444', 
                        '#8b5cf6', '#06b6d4', '#eab308', '#ec4899'
                    ],
                    borderColor: '#1e1e2e',
                    borderWidth: 2,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            color: '#9ca3af',
                            font: { size: 12, family: 'Noto Sans Thai, sans-serif' },
                            padding: 15,
                            usePointStyle: true,
                            boxWidth: 8
                        }
                    },
                    tooltip: {
                        backgroundColor: '#1e1e2e',
                        titleColor: '#ffffff',
                        bodyColor: '#cbd5e1',
                        borderColor: '#374151',
                        borderWidth: 1,
                        padding: 12,
                        titleFont: { size: 14, family: 'Noto Sans Thai, sans-serif' },
                        bodyFont: { size: 13, family: 'Noto Sans Thai, sans-serif' },
                        callbacks: {
                            title: function(context) {
                                return fullLabels[context[0].dataIndex];
                            },
                            label: function(context) {
                                const value = context.parsed || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                return ' ' + 
                                       new Intl.NumberFormat('th-TH', {
                                           style: 'decimal',
                                           minimumFractionDigits: 0
                                       }).format(value) + 
                                       ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    }
</script>
