<div class="space-y-6 animate-fade-in">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <p class="text-dark-muted text-sm mt-1">ผลการเบิกจ่ายงบประมาณ</p>
        </div>
        
        <!-- Actions -->
        <div class="flex items-center gap-3">
            <a href="<?= \App\Core\View::url('/execution/export?' . http_build_query(['year' => $fiscalYear] + array_filter($filters))) ?>" class="btn btn-secondary">
                <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                <span class="hidden sm:inline">ส่งออก Excel</span>
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-dark-card border border-dark-border rounded-xl p-4">
        <form method="GET" action="<?= \App\Core\View::url('/execution') ?>" class="flex flex-wrap gap-4 items-end">
            
            <!-- ปีงบประมาณ -->
            <div class="w-28">
                <label class="block text-xs text-dark-muted mb-1">ปีงบประมาณ</label>
                <select name="year" class="input w-full text-center" onchange="this.form.submit()">
                    <?php foreach ($fiscalYears as $fy): ?>
                    <option value="<?= $fy['value'] ?>" <?= $fy['value'] == $fiscalYear ? 'selected' : '' ?>>
                        <?= $fy['value'] ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- แผนงาน -->
            <div class="flex-1 min-w-[180px]">
                <label class="block text-xs text-dark-muted mb-1">แผนงาน</label>
                <select name="plan" class="input w-full">
                    <option value="">ทุกแผนงาน</option>
                    <?php foreach ($plans ?? [] as $plan): ?>
                    <option value="<?= $plan['plan_name'] ?>" <?= ($filters['plan_name'] ?? '') == $plan['plan_name'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($plan['plan_name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- หน่วยงาน -->
            <div class="flex-1 min-w-[180px]">
                <label class="block text-xs text-dark-muted mb-1">หน่วยงาน</label>
                <select name="org" class="input w-full">
                    <option value="">ทุกหน่วยงาน</option>
                    <?php foreach ($organizations ?? [] as $org): ?>
                    <option value="<?= $org['id'] ?>" <?= ($filters['org_id'] ?? '') == $org['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($org['name_th']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- ค้นหา -->
            <div class="flex-1 min-w-[240px]">
                <label class="block text-xs text-dark-muted mb-1">ค้นหา</label>
                <input type="text" name="search" class="input w-full" placeholder="พิมพ์คำค้นหา..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
            </div>
            
            <!-- ปุ่มค้นหา -->
            <div>
                <button type="submit" class="btn btn-primary px-4">
                    <i data-lucide="search" class="w-4 h-4"></i> ค้นหา
                </button>
            </div>
            
            <!-- ปุ่มล้างค่า -->
            <div>
                <a href="<?= \App\Core\View::url('/execution?year=' . $fiscalYear) ?>" class="btn btn-secondary px-3 h-[41px] flex items-center justify-center" title="ล้างค่า">
                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Card 1: Budget Allocation -->
        <div class="bg-dark-card border border-dark-border rounded-xl p-6 card-hover group relative">
            <div class="flex justify-between items-start mb-2">
                <div>
                    <p class="text-dark-muted text-sm font-medium">งบจัดสรร</p>
                    <h3 class="text-2xl font-bold text-blue-400 mt-1">
                        <?= \App\Core\View::number($stats['total_allocated'] ?? 0, 2) ?>
                    </h3>
                </div>
                <div class="p-2 bg-blue-500/10 rounded-lg text-blue-500">
                    <i data-lucide="wallet" class="w-6 h-6"></i>
                </div>
            </div>
            
            <!-- Footer with Tooltip -->
            <div class="pt-3 border-t border-dark-border flex items-center justify-between text-xs cursor-pointer">
                <span class="text-dark-muted">
                    <span class="<?= ($stats['transfer_change_amount'] ?? 0) >= 0 ? 'text-green-400' : 'text-red-400' ?>">
                        โอน <?= ($stats['transfer_change_amount'] ?? 0) >= 0 ? '+' : '' ?><?= \App\Core\View::currencyShort($stats['transfer_change_amount'] ?? 0) ?>
                    </span>
                </span>
                <i data-lucide="info" class="w-4 h-4 text-dark-muted hover:text-white transition-colors"></i>
            </div>

            <!-- Tooltip (Tailwind group-hover) -->
            <div class="absolute bottom-full left-0 mb-2 w-full invisible group-hover:visible opacity-0 group-hover:opacity-100 transition-all duration-200 z-10 px-2 pointer-events-none">
                <div class="bg-[#1e1e2d] border border-gray-700 rounded-lg shadow-xl p-3 text-xs w-full">
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-gray-400">งบจัดสรร</span>
                        <span class="text-white"><?= \App\Core\View::number($stats['total_budget_act'] ?? 0, 2) ?></span>
                    </div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-gray-400">โอน +/-</span>
                        <span class="<?= ($stats['transfer_change_amount'] ?? 0) >= 0 ? 'text-green-400' : 'text-red-400' ?>">
                            <?= ($stats['transfer_change_amount'] ?? 0) >= 0 ? '+' : '' ?><?= \App\Core\View::number($stats['transfer_change_amount'] ?? 0, 2) ?>
                        </span>
                    </div>
                    <div class="pt-2 border-t border-gray-700 flex justify-between items-center font-bold">
                        <span class="text-white">งบสุทธิ</span>
                        <span class="text-blue-400"><?= \App\Core\View::number($stats['total_allocated'] ?? 0, 2) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2: Total Spent -->
        <div class="bg-dark-card border border-dark-border rounded-xl p-6 card-hover group relative">
            <div class="flex justify-between items-start mb-2">
                <div>
                    <p class="text-dark-muted text-sm font-medium">รวมเบิกจ่าย</p>
                    <h3 class="text-2xl font-bold text-orange-400 mt-1">
                        <?= \App\Core\View::number($stats['total_spending'] ?? 0, 2) ?>
                    </h3>
                </div>
                <div class="p-2 bg-orange-500/10 rounded-lg text-orange-500">
                    <i data-lucide="banknote" class="w-6 h-6"></i>
                </div>
            </div>

             <!-- Footer with Tooltip -->
             <div class="pt-3 border-t border-dark-border flex items-center justify-between text-xs cursor-pointer">
                <span class="text-dark-muted flex gap-3">
                    <span class="text-orange-400">เบิก <?= \App\Core\View::currencyShort($stats['total_disbursed'] ?? 0) ?></span> 
                    <span class="text-yellow-400">ขอ 0.00</span> 
                    <span class="text-blue-400">PO <?= \App\Core\View::currencyShort($stats['total_po'] ?? 0) ?></span>
                </span>
                <i data-lucide="info" class="w-4 h-4 text-dark-muted hover:text-white transition-colors"></i>
            </div>

            <!-- Tooltip -->
            <div class="absolute bottom-full left-0 mb-2 w-full invisible group-hover:visible opacity-0 group-hover:opacity-100 transition-all duration-200 z-10 px-2 pointer-events-none">
                <div class="bg-[#1e1e2d] border border-gray-700 rounded-lg shadow-xl p-3 text-xs w-full">
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-gray-400">เบิกจ่าย</span>
                        <span class="text-white"><?= \App\Core\View::number($stats['total_disbursed'] ?? 0, 2) ?></span>
                    </div>
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-gray-400">ขออนุมัติ</span>
                         <span class="text-white">0.00</span>
                    </div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-gray-400">PO ผูกพัน</span>
                        <span class="text-white"><?= \App\Core\View::number($stats['total_po'] ?? 0, 2) ?></span>
                    </div>
                    <div class="pt-2 border-t border-gray-700 flex justify-between items-center font-bold">
                        <span class="text-white">รวมเบิกจ่าย</span>
                        <span class="text-orange-400"><?= \App\Core\View::number($stats['total_spending'] ?? 0, 2) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 3: Remaining -->
        <div class="bg-dark-card border border-dark-border rounded-xl p-6 card-hover">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-dark-muted text-sm font-medium">คงเหลือ</p>
                    <h3 class="text-2xl font-bold text-green-400 mt-1">
                        <?= \App\Core\View::number($stats['total_balance'] ?? ($stats['total_allocated'] - $stats['total_spending']), 2) ?>
                    </h3>
                </div>
                <div class="p-2 bg-green-500/10 rounded-lg text-green-500">
                    <i data-lucide="piggy-bank" class="w-6 h-6"></i>
                </div>
            </div>
        </div>

        <!-- Card 4: Disbursement Rate -->
        <div class="bg-dark-card border border-dark-border rounded-xl p-6 card-hover">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <p class="text-dark-muted text-sm font-medium">อัตราการเบิกจ่าย</p>
                    <h3 class="text-2xl font-bold text-white mt-1">
                        <?= number_format($stats['percent_spending'] ?? 0, 2) ?>%
                    </h3>
                </div>
                <?php 
                    $rate = $stats['percent_spending'] ?? 0;
                    $rateColor = $rate >= 80 ? 'green' : ($rate >= 50 ? 'orange' : 'red');
                ?>
                <div class="p-2 bg-<?= $rateColor ?>-500/10 rounded-lg text-<?= $rateColor ?>-500">
                    <i data-lucide="trending-up" class="w-6 h-6"></i>
                </div>
            </div>
            
            <!-- Progress Bar -->
            <div class="w-full bg-gray-700 rounded-full h-2.5 mt-4">
                <div class="bg-<?= $rateColor ?>-500 h-2.5 rounded-full" style="width: <?= min($rate, 100) ?>%"></div>
            </div>
            <div class="text-xs text-dark-muted mt-2 text-right">เป้าหมาย: 100%</div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Organization Chart -->
        <div class="bg-dark-card border border-dark-border rounded-xl p-6">
            <h3 class="text-lg font-semibold text-white mb-4">งบประมาณตามหน่วยงาน</h3>
            <div class="h-64 relative">
                <canvas id="orgChart"></canvas>
            </div>
        </div>

        <!-- Category Chart -->
        <!-- Category Chart -->
        <div class="bg-dark-card border border-dark-border rounded-xl p-6">
            <h3 class="text-lg font-semibold text-white mb-4">สัดส่วนตามผลผลิต/โครงการ</h3>
            <div class="h-64 relative flex justify-center">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Detail Table -->
    <div class="bg-slate-900/50 backdrop-blur-sm border border-slate-700 rounded-xl shadow-2xl overflow-hidden">
        <!-- Table Header -->
        <div class="px-6 py-4 border-b border-slate-700 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-slate-100 flex items-center gap-2">
                <i data-lucide="table" class="text-primary-500 w-5 h-5"></i>
                รายละเอียดการเบิกจ่าย
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
                        <th class="px-6 py-4 text-left">ผลผลิต/โครงการ<br><span class="text-slate-500 normal-case text-[10px]">(Project)</span></th>
                        <th class="px-4 py-4 text-right whitespace-nowrap">งบจัดสรร<br><span class="text-slate-500 normal-case text-[10px]">(Allocated)</span></th>
                        <th class="px-4 py-4 text-right whitespace-nowrap">งบสุทธิ<br><span class="text-slate-500 normal-case text-[10px]">(Net)</span></th>
                        <th class="px-4 py-4 text-right">Q1</th>
                        <th class="px-4 py-4 text-right">Q2</th>
                        <th class="px-4 py-4 text-right">Q3</th>
                        <th class="px-4 py-4 text-right">Q4</th>
                        <th class="px-4 py-4 text-right whitespace-nowrap">รวมเบิกจ่าย<br><span class="text-slate-500 normal-case text-[10px]">(Total)</span></th>
                        <th class="px-4 py-4 text-right whitespace-nowrap">คงเหลือ<br><span class="text-slate-500 normal-case text-[10px]">(Remaining)</span></th>
                        <th class="px-4 py-4 text-center">KPI %</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    <?php if (!empty($budgetData)): ?>
                        <?php foreach ($budgetData as $item): ?>
                        <?php 
                            $allocated = (float) ($item['allocated'] ?? 0);
                            $transfer = (float) ($item['transfer'] ?? 0);
                            $net = $allocated + $transfer;
                            $q1 = (float) ($item['q1'] ?? 0);
                            $q2 = (float) ($item['q2'] ?? 0);
                            $q3 = (float) ($item['q3'] ?? 0);
                            $q4 = (float) ($item['q4'] ?? 0);
                            $spent = (float) ($item['total_spending_amount'] ?? 0);
                            $balance = (float) ($item['balance_amount'] ?? 0);
                            $kpi = (float) ($item['percent_disburse_incl_po'] ?? 0);
                            
                            $pid = $item['project_id'];
                            $rowClass = 'project-' . $pid;
                        ?>
                        <!-- Project Row -->
                        <tr class="hover:bg-slate-800/30 transition-colors group cursor-pointer" onclick="document.querySelectorAll('.<?= $rowClass ?>').forEach(el => el.classList.toggle('hidden')); this.querySelector('.chevron').classList.toggle('rotate-90')">
                            <td class="px-6 py-3.5 font-medium text-slate-200">
                                <div class="flex items-center gap-2">
                                    <i data-lucide="chevron-right" class="chevron w-4 h-4 text-slate-400 transition-transform duration-200"></i>
                                    <div>
                                        <div class="text-slate-100"><?= htmlspecialchars($item['item_name'] ?? '-') ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-right px-4 py-3.5 text-slate-200 font-medium tabular-nums"><?= \App\Core\View::currencyShort($allocated) ?></td>
                            <td class="text-right px-4 py-3.5 text-violet-400 font-medium tabular-nums"><?= \App\Core\View::currencyShort($net) ?></td>
                            <td class="text-right px-4 py-3.5 text-slate-400 tabular-nums"><?= \App\Core\View::currencyShort($q1) ?></td>
                            <td class="text-right px-4 py-3.5 text-slate-400 tabular-nums"><?= \App\Core\View::currencyShort($q2) ?></td>
                            <td class="text-right px-4 py-3.5 text-slate-400 tabular-nums"><?= \App\Core\View::currencyShort($q3) ?></td>
                            <td class="text-right px-4 py-3.5 text-slate-400 tabular-nums"><?= \App\Core\View::currencyShort($q4) ?></td>
                            <td class="text-right px-4 py-3.5 text-emerald-400 font-medium tabular-nums"><?= \App\Core\View::currencyShort($spent) ?></td>
                            <td class="text-right px-4 py-3.5 text-slate-200 font-medium tabular-nums"><?= \App\Core\View::currencyShort($balance) ?></td>
                            <td class="text-center px-4 py-3.5">
                                <?php 
                                    $kpiClass = $kpi >= 80 ? 'text-emerald-400' : ($kpi >= 50 ? 'text-amber-400' : 'text-red-400');
                                    $kpiBg = $kpi >= 80 ? 'bg-emerald-500' : ($kpi >= 50 ? 'bg-amber-500' : 'bg-red-500');
                                ?>
                                <div class="inline-flex items-center gap-1.5">
                                    <div class="w-12 h-1.5 bg-slate-700 rounded-full overflow-hidden">
                                        <div class="h-full <?= $kpiBg ?> rounded-full transition-all" style="width: <?= min($kpi, 100) ?>%"></div>
                                    </div>
                                    <span class="<?= $kpiClass ?> text-xs tabular-nums font-medium"><?= number_format($kpi, 1) ?>%</span>
                                </div>
                            </td>
                        </tr>
                        
                        <!-- Activity Rows (Hidden) -->
                        <?php foreach ($item['activities'] ?? [] as $act): ?>
                        <tr class="<?= $rowClass ?> hidden bg-slate-700/10 hover:bg-slate-700/20 transition-colors">
                            <td class="px-6 py-2.5 pl-12 text-sm">
                                <div class="flex items-center gap-2 text-slate-200">
                                    <span><?= htmlspecialchars($act['activity_name'] ?? '-') ?></span>
                                </div>
                            </td>
                            <td class="text-right px-4 py-2.5 text-slate-200 text-sm tabular-nums"><?= \App\Core\View::currencyShort($act['allocated'] ?? 0) ?></td>
                            <td class="text-right px-4 py-2.5 text-violet-400 font-medium text-sm tabular-nums"><?= \App\Core\View::currencyShort($act['net_budget'] ?? 0) ?></td>
                            <td class="text-right px-4 py-2.5 text-slate-400 text-sm tabular-nums"><?= \App\Core\View::currencyShort($act['q1'] ?? 0) ?></td>
                            <td class="text-right px-4 py-2.5 text-slate-400 text-sm tabular-nums"><?= \App\Core\View::currencyShort($act['q2'] ?? 0) ?></td>
                            <td class="text-right px-4 py-2.5 text-slate-400 text-sm tabular-nums"><?= \App\Core\View::currencyShort($act['q3'] ?? 0) ?></td>
                            <td class="text-right px-4 py-2.5 text-slate-400 text-sm tabular-nums"><?= \App\Core\View::currencyShort($act['q4'] ?? 0) ?></td>
                            <td class="text-right px-4 py-2.5 text-emerald-400 font-medium text-sm tabular-nums"><?= \App\Core\View::currencyShort($act['total_spending'] ?? 0) ?></td>
                            <td class="text-right px-4 py-2.5 text-slate-200 font-medium text-sm tabular-nums"><?= \App\Core\View::currencyShort($act['balance'] ?? 0) ?></td>
                            <td class="text-center px-4 py-2.5">
                                <?php 
                                    $actNet = (float)($act['net_budget'] ?? 0);
                                    $actSpent = (float)($act['total_spending'] ?? 0);
                                    // Calculate KPI % (Spent / Net * 100)
                                    $actKpi = $actNet > 0 ? ($actSpent / $actNet * 100) : 0;
                                    
                                    $actKpiClass = $actKpi >= 80 ? 'text-emerald-400' : ($actKpi >= 50 ? 'text-amber-400' : 'text-red-400');
                                    $actKpiBg = $actKpi >= 80 ? 'bg-emerald-500' : ($actKpi >= 50 ? 'bg-amber-500' : 'bg-red-500');
                                ?>
                                <div class="inline-flex items-center gap-1.5">
                                    <div class="w-12 h-1.5 bg-slate-700/50 rounded-full overflow-hidden">
                                        <div class="h-full <?= $actKpiBg ?> rounded-full transition-all" style="width: <?= min($actKpi, 100) ?>%"></div>
                                    </div>
                                    <span class="<?= $actKpiClass ?> text-xs tabular-nums font-medium"><?= number_format($actKpi, 1) ?>%</span>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center py-8 text-dark-muted">
                                <i data-lucide="files" class="w-8 h-8 mb-2 mx-auto"></i>
                                <p>ไม่พบข้อมูล</p>
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
        org: <?= json_encode($orgChartData ?? []) ?>,
        category: <?= json_encode($categoryChartData ?? ['labels' => [], 'values' => []]) ?>
    };

    document.addEventListener('DOMContentLoaded', function() {
        // --- Organization Chart (Horizontal Bar) ---
        const orgCtx = document.getElementById('orgChart').getContext('2d');
        
        // Create Gradient
        const gradient = orgCtx.createLinearGradient(0, 0, 400, 0);
        gradient.addColorStop(0, '#38bdf8'); // sky-400
        gradient.addColorStop(1, '#0ea5e9'); // sky-500

        new Chart(orgCtx, {
            type: 'bar',
            data: {
                labels: window.chartData.org.labels,
                datasets: [{
                    label: 'งบประมาณ (บาท)',
                    data: window.chartData.org.values,
                    backgroundColor: gradient,
                    borderRadius: 4,
                    barPercentage: 0.6,
                    categoryPercentage: 0.8
                }]
            },
            options: {
                indexAxis: 'y', // Horizontal Bar
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        titleColor: '#f1f5f9',
                        bodyColor: '#cbd5e1',
                        borderColor: '#334155',
                        borderWidth: 1,
                        padding: 10,
                        callbacks: {
                            label: function(context) {
                                let value = context.raw;
                                return ' ' + new Intl.NumberFormat('th-TH').format(value) + ' บาท';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { color: '#334155', drawBorder: false },
                        ticks: { 
                            color: '#94a3b8',
                            callback: function(value) {
                                return (value / 1000000).toFixed(1) + 'M';
                            }
                        }
                    },
                    y: {
                        grid: { display: false, drawBorder: false },
                        ticks: { color: '#f1f5f9', font: { family: 'Noto Sans Thai' } }
                    }
                }
            }
        });

        // --- Category Chart (Doughnut) ---
        const catCtx = document.getElementById('categoryChart').getContext('2d');
        const catColors = ['#3b82f6', '#8b5cf6', '#ec4899', '#f59e0b', '#10b981', '#64748b']; // Blue, Violet, Pink, Amber, Emerald, Gray

        new Chart(catCtx, {
            type: 'doughnut',
            data: {
                labels: window.chartData.category.labels,
                datasets: [{
                    data: window.chartData.category.values,
                    backgroundColor: catColors,
                    borderColor: '#1e293b', // Match card bg for clean separation
                    borderWidth: 2,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'right',
                        labels: { color: '#cbd5e1', font: { family: 'Noto Sans Thai', size: 12 }, boxWidth: 12, padding: 15 }
                    },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        titleColor: '#f1f5f9',
                        bodyColor: '#cbd5e1',
                        borderColor: '#334155',
                        borderWidth: 1,
                        padding: 10,
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                let value = context.raw;
                                let total = context.chart._metasets[context.datasetIndex].total;
                                let percentage = ((value / total) * 100).toFixed(1) + '%';
                                return ' ' + label + ': ' + new Intl.NumberFormat('th-TH').format(value) + ' (' + percentage + ')';
                            }
                        }
                    }
                }
            }
        });
    });
</script>
