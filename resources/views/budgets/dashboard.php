<div class="space-y-6 animate-fade-in">
    <!-- Header with Fiscal Year Selector -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">ผลการเบิกจ่ายงบประมาณ</h1>
            <p class="text-dark-muted text-sm mt-1">ภาพรวมการเบิกจ่ายงบประมาณประจำปี</p>
        </div>
        
        <!-- Fiscal Year Selector -->
        <div class="flex items-center gap-3">
            <label class="text-sm text-dark-muted">ปีงบประมาณ:</label>
            <select id="fiscal-year-select" class="input w-40" onchange="changeFiscalYear(this.value)">
                <?php foreach ($fiscalYears as $fy): ?>
                <option value="<?= $fy['value'] ?>" <?= $fy['value'] == $fiscalYear ? 'selected' : '' ?>>
                    <?= htmlspecialchars($fy['label']) ?>
                </option>
                <?php endforeach; ?>
            </select>
            
            <a href="<?= \App\Core\View::url('/budgets/list?year=' . $fiscalYear) ?>" class="btn btn-secondary">
                <i data-lucide="list" class="w-4 h-4"></i>
                <span class="hidden sm:inline">รายการ</span>
            </a>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Allocated -->
        <div class="bg-dark-card border border-dark-border rounded-xl p-6 card-hover">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <p class="text-dark-muted text-sm font-medium">งบประมาณจัดสรร</p>
                    <h3 class="text-2xl font-bold text-white mt-1">
                        <?= \App\Core\View::currency($stats['total_allocated'] ?? 0) ?>
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
                        <?= \App\Core\View::currency($stats['total_spent'] ?? 0) ?>
                    </h3>
                </div>
                <div class="icon-glass text-orange-400">
                    <i data-lucide="banknote" class="w-6 h-6"></i>
                </div>
            </div>
            <div class="w-full progress">
                <div class="progress-bar bg-orange-500" style="width: <?= min(100, $stats['spent_percent'] ?? 0) ?>%"></div>
            </div>
            <div class="text-xs text-dark-muted mt-2"><?= $stats['spent_percent'] ?? 0 ?>% ของงบทั้งหมด</div>
        </div>

        <!-- Remaining -->
        <div class="bg-dark-card border border-dark-border rounded-xl p-6 card-hover">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <p class="text-dark-muted text-sm font-medium">คงเหลือ</p>
                    <h3 class="text-2xl font-bold text-green-400 mt-1">
                        <?= \App\Core\View::currency($stats['total_remaining'] ?? 0) ?>
                    </h3>
                </div>
                <div class="icon-glass text-green-400">
                    <i data-lucide="piggy-bank" class="w-6 h-6"></i>
                </div>
            </div>
            <div class="text-xs text-dark-muted">
                โอนเข้า: <?= \App\Core\View::currency($stats['total_transfer_in'] ?? 0) ?>
            </div>
        </div>

        <!-- Disbursement Rate -->
        <div class="bg-dark-card border border-dark-border rounded-xl p-6 card-hover">
            <div class="flex justify-between items-start mb-4">
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
            <?php if ($rate >= 80): ?>
                <span class="badge badge-green">ดีมาก</span>
            <?php elseif ($rate >= 50): ?>
                <span class="badge badge-orange">ปานกลาง</span>
            <?php else: ?>
                <span class="badge badge-red">ต่ำ</span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Trend Chart -->
        <div class="lg:col-span-2 bg-dark-card border border-dark-border rounded-xl p-6">
            <h3 class="text-lg font-semibold text-white mb-4">แนวโน้มการเบิกจ่าย (Timeline)</h3>
            <div class="h-64 relative">
                <canvas id="trendChart"></canvas>
            </div>
        </div>

        <!-- Category Chart -->
        <div class="bg-dark-card border border-dark-border rounded-xl p-6">
            <h3 class="text-lg font-semibold text-white mb-4">สัดส่วนตามหมวดหมู่</h3>
            <div class="h-64 relative flex justify-center">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Budget by Category Table -->
    <div class="bg-dark-card border border-dark-border rounded-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-dark-border flex justify-between items-center">
            <h3 class="text-lg font-semibold text-white">งบประมาณตามหมวดหมู่</h3>
            <a href="<?= \App\Core\View::url('/budgets/list?year=' . $fiscalYear) ?>" class="text-sm text-primary-500 hover:text-primary-400">
                ดูรายละเอียด <i data-lucide="arrow-right" class="w-4 h-4 inline ml-1"></i>
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>หมวดหมู่</th>
                        <th class="text-right">งบจัดสรร</th>
                        <th class="text-right">เบิกจ่ายแล้ว</th>
                        <th class="text-right">คงเหลือ</th>
                        <th class="w-32">ความคืบหน้า</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($byCategory)): ?>
                        <?php foreach ($byCategory as $cat): ?>
                        <?php 
                            $allocated = (float) $cat['allocated'];
                            $spent = (float) $cat['spent'];
                            $remaining = (float) $cat['remaining'];
                            $percent = $allocated > 0 ? ($spent / $allocated) * 100 : 0;
                            $progressClass = $percent > 80 ? 'bg-red-500' : ($percent > 50 ? 'bg-orange-500' : 'bg-green-500');
                        ?>
                        <tr>
                            <td class="font-medium"><?= htmlspecialchars($cat['category_name'] ?? '-') ?></td>
                            <td class="text-right"><?= \App\Core\View::currency($allocated) ?></td>
                            <td class="text-right text-dark-muted"><?= \App\Core\View::currency($spent) ?></td>
                            <td class="text-right text-green-400"><?= \App\Core\View::currency($remaining) ?></td>
                            <td>
                                <div class="progress">
                                    <div class="progress-bar <?= $progressClass ?>" style="width: <?= min(100, round($percent)) ?>%"></div>
                                </div>
                                <div class="text-xs text-right mt-1 text-dark-muted"><?= round($percent, 1) ?>%</div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-8 text-dark-muted">
                                <i data-lucide="folder-open" class="w-12 h-12 mx-auto mb-2 text-dark-muted"></i>
                                <p>ยังไม่มีข้อมูลงบประมาณ</p>
                                <a href="<?= \App\Core\View::url('/budgets/create?year=' . $fiscalYear) ?>" class="btn btn-primary mt-4">
                                    <i data-lucide="plus" class="w-4 h-4 mr-1"></i> เพิ่มงบประมาณ
                                </a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Chart Data & Scripts -->
<script>
    window.chartData = {
        trend: <?= json_encode($trend) ?>,
        category: {
            labels: <?= json_encode(array_column($byCategory ?? [], 'category_name')) ?>,
            values: <?= json_encode(array_map('floatval', array_column($byCategory ?? [], 'allocated'))) ?>
        }
    };
    
    function changeFiscalYear(year) {
        window.location.href = '<?= \App\Core\View::url('/budgets') ?>?year=' + year;
    }
</script>
