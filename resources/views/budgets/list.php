<?php
// Layout: main
?>

<div class="space-y-6 animate-fade-in">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <p class="text-dark-muted text-sm mt-1">รายการเบิกจ่ายงบประมาณประจำปี</p>
        </div>
        <a href="<?= \App\Core\View::url('/budgets/tracking?year=' . $fiscalYear) ?>" class="btn btn-primary">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span class="hidden sm:inline">บันทึกข้อมูล</span>
        </a>
    </div>

    <!-- Filter Card -->
    <div class="bg-dark-card border border-dark-border rounded-xl p-4">
        <form method="GET" action="<?= \App\Core\View::url('/budgets/list') ?>" class="flex flex-wrap gap-4 items-end">
            
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
                    <option value="<?= $plan['name_th'] ?? $plan['plan_name'] ?? '' ?>" <?= ($filters['plan_name'] ?? '') == ($plan['name_th'] ?? $plan['plan_name'] ?? '') ? 'selected' : '' ?>>
                        <?= htmlspecialchars($plan['name_th'] ?? $plan['plan_name'] ?? '-') ?>
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
            
            <!-- ข้อมูล ณ วันที่ -->
            <div class="w-40">
                <label class="block text-xs text-dark-muted mb-1">ข้อมูล ณ วันที่</label>
                <select name="record_date" class="input w-full">
                    <option value="">ทั้งหมด</option>
                    <?php 
                    $latestDate = !empty($recordDates) ? $recordDates[0]['record_date'] : null;
                    $selectedDate = $filters['record_date'] ?? $latestDate;
                    foreach ($recordDates ?? [] as $date): 
                    ?>
                    <option value="<?= $date['record_date'] ?>" <?= $selectedDate == $date['record_date'] ? 'selected' : '' ?>>
                        <?= \App\Core\View::date($date['record_date'], 'd/m/Y') ?>
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
                    <i data-lucide="search" class="w-4 h-4 mr-1"></i> ค้นหา
                </button>
            </div>
            
            <!-- ปุ่มล้างค่า -->
            <div>
                <a href="<?= \App\Core\View::url('/budgets/list?year=' . $fiscalYear) ?>" class="btn btn-secondary px-3 h-[41px] flex items-center justify-center" title="ล้างค่า">
                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <!-- 1. งบจัดสรร -->
        <div class="bg-dark-card border border-dark-border rounded-lg p-4">
            <p class="text-dark-muted text-sm">งบจัดสรร</p>
            <p class="text-xl font-bold text-blue-400"><?= \App\Core\View::number($stats['total_allocated'] ?? 0, 2) ?></p>
        </div>

        <!-- 2. โอน จัดสรร/เบิกแทน/เปลี่ยนแปลง -->
        <div class="bg-dark-card border border-dark-border rounded-lg p-4">
            <p class="text-dark-muted text-sm">โอน จัดสรร/เบิกแทน/เปลี่ยนแปลง</p>
            <?php 
                $transfer = (float)($stats['transfer_change_amount'] ?? 0);
                $color = $transfer >= 0 ? 'text-green-400' : 'text-red-400';
            ?>
            <p class="text-xl font-bold <?= $color ?>">
                <?= $transfer >= 0 ? '+' : '' ?><?= \App\Core\View::number($transfer, 2) ?>
            </p>
        </div>

        <!-- 3. งบสุทธิ -->
        <div class="bg-dark-card border border-dark-border rounded-lg p-4 group relative">
            <p class="text-dark-muted text-sm">งบสุทธิ</p>
            <p class="text-xl font-bold text-violet-400"><?= \App\Core\View::number($stats['total_net_budget'] ?? 0, 2) ?></p>
            
            <!-- Tooltip -->
            <div class="absolute bottom-full left-0 mb-2 w-full invisible group-hover:visible opacity-0 group-hover:opacity-100 transition-all duration-200 z-10 px-2 pointer-events-none">
                <div class="bg-[#1e1e2d] border border-gray-700 rounded-lg shadow-xl p-3 text-xs w-full">
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-gray-400">งบจัดสรร</span>
                        <span class="text-white"><?= \App\Core\View::number($stats['total_allocated'] ?? 0, 2) ?></span>
                    </div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-gray-400">โอน +/-</span>
                        <span class="<?= $transfer >= 0 ? 'text-green-400' : 'text-red-400' ?>">
                            <?= $transfer >= 0 ? '+' : '' ?><?= \App\Core\View::number($transfer, 2) ?>
                        </span>
                    </div>
                    <div class="pt-2 border-t border-gray-700 flex justify-between items-center font-bold">
                        <span class="text-white">งบสุทธิ</span>
                        <span class="text-violet-400"><?= \App\Core\View::number($stats['total_net_budget'] ?? 0, 2) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. รวมเบิกจ่าย -->
        <div class="bg-dark-card border border-dark-border rounded-lg p-4 group relative">
            <p class="text-dark-muted text-sm">รวมเบิกจ่าย</p>
            <p class="text-xl font-bold text-orange-400"><?= \App\Core\View::number($stats['total_spending'] ?? 0, 2) ?></p>
            
            <!-- Tooltip -->
            <div class="absolute bottom-full left-0 mb-2 w-full invisible group-hover:visible opacity-0 group-hover:opacity-100 transition-all duration-200 z-10 px-2 pointer-events-none">
                <div class="bg-[#1e1e2d] border border-gray-700 rounded-lg shadow-xl p-3 text-xs w-full">
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-gray-400">เบิกจ่าย</span>
                        <span class="text-white"><?= \App\Core\View::number($stats['total_disbursed'] ?? 0, 2) ?></span>
                    </div>
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-gray-400">ขออนุมัติ</span>
                        <span class="text-white"><?= \App\Core\View::number($stats['total_request'] ?? 0, 2) ?></span>
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

        <!-- 5. คงเหลือ -->
        <div class="bg-dark-card border border-dark-border rounded-lg p-4">
            <p class="text-dark-muted text-sm">คงเหลือ</p>
            <p class="text-xl font-bold text-green-400"><?= \App\Core\View::number($stats['total_balance'] ?? 0, 2) ?></p>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-slate-900/50 backdrop-blur-sm border border-slate-700 rounded-xl shadow-2xl overflow-hidden">
        <!-- Table Header -->
        <div class="px-6 py-4 border-b border-slate-700 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-slate-100 flex items-center gap-2">
                <i data-lucide="table" class="text-primary-500 w-5 h-5"></i>
                รายละเอียดการเบิกจ่าย
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-800/80 backdrop-blur-sm">
                    <tr class="text-xs uppercase font-semibold text-slate-300">
                        <th class="px-6 py-4 text-left">ผลผลิต/โครงการ</th>
                        <th class="px-4 py-4 text-right">งบจัดสรร</th>
                        <th class="px-4 py-4 text-right text-green-400">โอน +/-</th>
                        <th class="px-4 py-4 text-right text-violet-400">งบสุทธิ</th>
                        <th class="px-4 py-4 text-right">เบิกจ่าย</th>
                        <th class="px-4 py-4 text-right">ขออนุมัติ</th>
                        <th class="px-4 py-4 text-right">PO</th>
                        <th class="px-4 py-4 text-right text-orange-400">รวมเบิกจ่าย</th>
                        <th class="px-4 py-4 text-right text-green-400">คงเหลือ</th>
                        <th class="px-4 py-4 text-center">% เบิก (no PO)</th>
                        <th class="px-4 py-4 text-center">% เบิก (PO)</th>
                        <th class="px-4 py-4 text-center w-20">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    <?php if (!empty($budgets)): ?>
                        <?php foreach ($budgets as $i => $budget): ?>
                        <?php 
                            // Calculate values
                            $allocated = (float)($budget['budget_allocated_amount'] ?? 0);
                            $transfer = (float)($budget['transfer_change_amount'] ?? 0);
                            $netBudget = (float)($budget['budget_net_balance'] ?? 0);
                            
                            $spent = (float)($budget['disbursed_amount'] ?? 0);
                            $request = (float)($budget['request_amount'] ?? 0);
                            $po = (float)($budget['po_pending_amount'] ?? 0);
                            
                            $totalSpent = $spent + $request + $po;
                            $remaining = (float)($budget['balance_amount'] ?? ($netBudget - $totalSpent));
                            
                            $percentNoPO = $netBudget > 0 ? ($spent / $netBudget * 100) : 0;
                            $percentWithPO = $netBudget > 0 ? (($spent + $po) / $netBudget * 100) : 0;
                            
                            $rowId = 'row-' . $i;
                        ?>
                        <tr class="bg-dark-bg hover:bg-white/5 transition-colors group cursor-pointer" onclick="document.getElementById('<?= $rowId ?>').classList.toggle('hidden')">
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-2">
                                    <i data-lucide="chevron-right" class="w-4 h-4 text-gray-400 transition-transform group-hover:text-white" style="transition: transform 0.2s" onclick="this.style.transform = this.style.transform === 'rotate(90deg)' ? 'rotate(0deg)' : 'rotate(90deg)'"></i>
                                    <div>
                                        <div class="font-medium text-white text-[15px]"><?= htmlspecialchars($budget['project_name'] ?? '-') ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-right font-medium py-3 px-2 text-white/90">
                                <?= number_format($allocated, 2) ?>
                            </td>
                            <td class="text-right py-3 px-2 <?= $transfer >= 0 ? 'text-green-400' : 'text-red-400' ?>">
                                <?= $transfer >= 0 ? '+' : '' ?><?= number_format($transfer, 2) ?>
                            </td>
                            <td class="text-right text-violet-400 font-medium py-3 px-2">
                                <?= number_format($netBudget, 2) ?>
                            </td>
                            <td class="text-right py-3 px-2 text-white/90">
                                <?= number_format($spent, 2) ?>
                            </td>
                            <td class="text-right py-3 px-2 text-white/90">
                                <?= number_format($request, 2) ?>
                            </td>
                            <td class="text-right py-3 px-2 text-white/90">
                                <?= number_format($po, 2) ?>
                            </td>
                            <td class="text-right text-orange-400 font-medium py-3 px-2">
                                <?= number_format($totalSpent, 2) ?>
                            </td>
                            <td class="text-right text-green-400 font-bold py-3 px-2">
                                <?= number_format($remaining, 2) ?>
                            </td>
                            <td class="text-center py-3 px-2">
                                <span class="<?= $percentNoPO >= 80 ? 'text-green-400' : 'text-gray-400' ?>">
                                    <?= number_format($percentNoPO, 2) ?>%
                                </span>
                            </td>
                            <td class="text-center py-3 px-2">
                                <span class="<?= $percentWithPO >= 80 ? 'text-green-400' : 'text-gray-400' ?>">
                                    <?= number_format($percentWithPO, 2) ?>%
                                </span>
                            </td>
                            <td class="text-center py-3 px-2">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="<?= \App\Core\View::url('/budgets/tracking?year=' . $fiscalYear . '&org_id=' . ($budget['org_id'] ?? '')) ?>" 
                                       class="btn btn-icon btn-ghost-primary" 
                                       title="เรียกดู">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </a>
                                    <a href="<?= \App\Core\View::url('/budgets/tracking?year=' . $fiscalYear . '&org_id=' . ($budget['org_id'] ?? '')) ?>" 
                                       class="btn btn-icon btn-ghost-warning" 
                                       title="แก้ไข">
                                        <i data-lucide="square-pen" class="w-4 h-4"></i>
                                    </a>
                                    <button onclick="confirmDelete(<?= $budget['datasource_row'] ?? 0 ?>, '<?= htmlspecialchars(($budget['item_name'] ?? 'รายการ')) ?>')" 
                                            class="btn btn-icon text-red-400 hover:text-red-300 hover:bg-red-400/10"
                                            title="ลบ">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <!-- Collapsible Detail Row -->
                        <tr id="<?= $rowId ?>" class="hidden bg-slate-700/10 hover:bg-slate-700/20 transition-colors">
                            <!-- Column 1: Activity Name (Indented) -->
                            <td class="py-3 px-4 relative">
                                <div class="flex items-center gap-2 pl-6">
                                    <span class="text-slate-200 text-sm"><?= htmlspecialchars($budget['activity_name'] ?? '-') ?></span>
                                </div>
                            </td>
                            <!-- Financial Columns (Same as parent) -->
                            <td class="text-right font-medium py-3 px-2 text-white/90 text-sm"><?= number_format($allocated, 2) ?></td>
                            <td class="text-right py-3 px-2 text-sm <?= $transfer >= 0 ? 'text-green-400' : 'text-red-400' ?>"><?= number_format($transfer, 2) ?></td>
                            <td class="text-right text-violet-400 font-medium py-3 px-2 text-sm"><?= number_format($netBudget, 2) ?></td>
                            <td class="text-right text-white/90 py-3 px-2 text-sm"><?= number_format($spent, 2) ?></td>
                            <td class="text-right text-white/90 py-3 px-2 text-sm"><?= number_format($request, 2) ?></td>
                            <td class="text-right text-white/90 py-3 px-2 text-sm"><?= number_format($po, 2) ?></td>
                            <td class="text-right text-orange-400 font-medium py-3 px-2 text-sm"><?= number_format($totalSpent, 2) ?></td>
                            <td class="text-right text-green-400 font-bold py-3 px-2 text-sm"><?= number_format($remaining, 2) ?></td>
                            <!-- KPI Columns -->
                            <td class="text-center py-3 px-2 text-sm">
                                <span class="<?= $percentNoPO >= 80 ? 'text-green-400' : 'text-gray-400' ?>"><?= number_format($percentNoPO, 2) ?>%</span>
                            </td>
                            <td class="text-center py-3 px-2 text-sm">
                                <span class="<?= $percentWithPO >= 80 ? 'text-green-400' : 'text-gray-400' ?>"><?= number_format($percentWithPO, 2) ?>%</span>
                            </td>
                            <!-- Action Column -->
                            <td class="text-center text-gray-600 py-3 px-2"></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="13" class="text-center py-12 text-dark-muted">
                                <i data-lucide="folder-open" class="w-12 h-12 mx-auto mb-3 text-dark-muted"></i>
                                <p class="text-lg">ยังไม่มีข้อมูลเบิกจ่าย</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($pagination['total'] > 1): ?>
        <div class="px-4 py-3 border-t border-dark-border flex justify-between items-center">
            <p class="text-xs text-dark-muted">
                แสดง <?= $pagination['current'] * $pagination['perPage'] - $pagination['perPage'] + 1 ?> 
                ถึง <?= min($pagination['current'] * $pagination['perPage'], $pagination['totalRecords']) ?> 
                จาก <?= $pagination['totalRecords'] ?> รายการ
            </p>
            <div class="flex gap-1">
                <?php if ($pagination['current'] > 1): ?>
                <a href="<?= \App\Core\View::url('/budgets/list?page=' . ($pagination['current'] - 1) . '&year=' . $fiscalYear) ?>" class="px-3 py-1 bg-dark-bg border border-dark-border rounded hover:bg-dark-border text-sm">ก่อนหน้า</a>
                <?php endif; ?>
                
                <?php if ($pagination['current'] < $pagination['total']): ?>
                <a href="<?= \App\Core\View::url('/budgets/list?page=' . ($pagination['current'] + 1) . '&year=' . $fiscalYear) ?>" class="px-3 py-1 bg-dark-bg border border-dark-border rounded hover:bg-dark-border text-sm">ถัดไป</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
function confirmDelete(id, name) {
    if (confirm('คุณต้องการลบรายการ "' + name + '" ใช่หรือไม่?')) {
        // Implement delete logic here via API or Form
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= \App\Core\View::url('/budgets/delete/') ?>' + id;
        form.innerHTML = '<?= \App\Core\View::csrf() ?><input type="hidden" name="_method" value="DELETE">';
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
