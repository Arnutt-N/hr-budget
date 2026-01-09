<?php
/**
 * Disbursement Form - Part 2 (Tailwind Implementation)
 * Based on research/wireframe_disbursement_form_v2.html
 */

// Expense Type Metadata (Mapped by ID for safety)
$typeMeta = [
    1 => ['color' => 'blue', 'icon' => 'users'],         // งบบุคลากร
    2 => ['color' => 'emerald', 'icon' => 'briefcase'],  // งบดำเนินงาน
    3 => ['color' => 'purple', 'icon' => 'building'],    // งบลงทุน
    4 => ['color' => 'rose', 'icon' => 'receipt'],       // งบรายจ่ายอื่น
    5 => ['color' => 'amber', 'icon' => 'heart-handshake'] // งบเงินอุดหนุน
];

$currentTypeId = $expenseType['id'] ?? 1;
$currentTypeName = $expenseType['name_th'] ?? 'งบบุคลากร';
$meta = $typeMeta[$currentTypeId] ?? ['color' => 'slate', 'icon' => 'folder'];
$color = $meta['color'];

// Calculate Totals recursively
$totalAllocated = 0;
$totalDisbursed = 0;
$totalRemaining = 0;
$totalTransfer = 0;
$totalPending = 0;
$totalPo = 0;
$itemCount = 0;
$trackings = $trackings ?? [];

$calculateTotals = function($items) use (&$calculateTotals, $trackings, &$totalAllocated, &$totalDisbursed, &$totalRemaining, &$itemCount, &$totalTransfer, &$totalPending, &$totalPo) {
    foreach ($items as $item) {
        $itemCount++;
        $t = $trackings[$item['id']] ?? [];
        $alloc = (float) ($t['allocated'] ?? 0);
        $trans = (float) ($t['transfer'] ?? 0);
        $disb = (float) ($t['disbursed'] ?? 0);
        $pend = (float) ($t['pending'] ?? 0);
        $po = (float) ($t['po'] ?? 0);
        
        $totalAllocated += $alloc;
        $totalTransfer += $trans;
        $totalDisbursed += $disb;
        $totalPending += $pend;
        $totalPo += $po;
        $totalRemaining += ($alloc + $trans) - ($disb + $pend + $po);

        if (!empty($item['children'])) {
            $calculateTotals($item['children']);
        }
    }
};

foreach ($groups as $group) {
    $calculateTotals($group['items'] ?? []);
}

// Fiscal Year Calendar: ปีงบ 2569 เดือน 10 = ตุลาคม 2568
$fiscalYear = $record['fiscal_year'];
$recordMonth = $record['record_month'];
$calendarYear = ($recordMonth >= 10) ? $fiscalYear - 1 : $fiscalYear;

// Record count for ครั้งที่
$recordCount = $record['record_count'] ?? 0;
$maxRecords = 5;

// Read Only Check
$isReadOnly = !empty($_GET['readonly']);
?>

<style>
/* Hide number input spinners */
input[type="number"]::-webkit-outer-spin-button,
input[type="number"]::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
input[type="number"] {
    -moz-appearance: textfield;
    appearance: textfield;
}
</style>

<div class="animate-fade-in">
    <!-- Main Card -->
    <div class="bg-slate-900/50 backdrop-blur-sm border border-slate-700 rounded-xl shadow-2xl overflow-hidden">
        
        <!-- Header with Context Info -->
        <div class="bg-gradient-to-r from-primary-900/30 to-slate-800/30 px-6 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-slate-100 flex items-center gap-2">
                        <i data-lucide="file-text" class="w-5 h-5 text-primary-400"></i>
                        บันทึกเบิกจ่ายงบประมาณ
                    </h3>
                    <p class="text-sm text-slate-400 mt-1">
                        <span class="text-blue-400"><?= $record['plan_name'] ?></span> →
                        <span class="text-emerald-400"><?= $record['project_name'] ?></span> →
                        <span class="text-amber-400"><?= $record['activity_name'] ?></span>
                    </p>
                </div>
                <a href="<?= BASE_URL ?>/budgets/tracking/activities?session_id=<?= $record['session_id'] ?>" 
                   class="px-3 py-1.5 text-slate-400 hover:text-slate-200 transition-colors flex items-center gap-1">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i> กลับ
                </a>
            </div>
        </div>

        <!-- Recording Info Bar -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 px-6 py-4 bg-slate-800/50 border-b border-slate-700">
            <div>
                <label class="block text-sm font-medium text-slate-400 mb-1">ปีงบประมาณ</label>
                <span class="text-lg font-semibold text-slate-100"><?= $fiscalYear ?></span>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-400 mb-1">เดือน</label>
                <span class="text-lg font-semibold text-slate-100">
                    <?= \App\Helpers\DateHelper::thaiMonth($recordMonth) ?> <?= $calendarYear ?>
                </span>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-2">
                    วันที่บันทึก (พ.ศ.) 
                </label><input type="date" value="<?= $record['record_date'] ?>" name="record_date"
                    class="thai-datepicker px-3 py-1.5 bg-slate-700 border border-slate-600 rounded text-slate-100 text-sm focus:ring-2 focus:ring-primary-500 w-full max-w-[150px]">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-400 mb-1">ครั้งที่</label>
                <span class="text-lg font-semibold text-primary-400"><?= $recordCount ?> / <?= $maxRecords ?></span>
            </div>
        </div>

        <!-- Tab Navigation -->
        <div class="border-b border-slate-700">
            <nav class="flex overflow-x-auto" id="expense-tabs">
                <?php 
                    // Custom Sort: Personnel(1), Operating(2), Investment(3), Subsidy(5), Other(4)
                    $tabOrder = [1, 2, 3, 5, 4];
                    $sortedTabs = [];
                    $tabMap = array_column($tabs, null, 'id');
                    
                    foreach ($tabOrder as $tid) {
                        if (isset($tabMap[$tid])) $sortedTabs[] = $tabMap[$tid];
                    }
                    // Add remaining
                    foreach ($tabMap as $tid => $tab) {
                        if (!in_array($tid, $tabOrder)) $sortedTabs[] = $tab;
                    }
                    
                    foreach ($sortedTabs as $tab): 

                    $tName = $tab['name_th'];
                    $tMeta = $typeMeta[$tab['id']] ?? ['color'=>'slate', 'icon'=>'circle'];
                    $isActive = ($tab['id'] == $activeTypeId);
                    $tColor = $tMeta['color'];
                    
                    // Explicit tab classes for Tailwind JIT
                    $tabClasses = [
                        'blue' => [
                            'active' => 'border-b-[3px] border-blue-400 text-blue-300 bg-gradient-to-t from-blue-900/10 to-transparent',
                            'inactive' => 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-200'
                        ],
                        'emerald' => [
                            'active' => 'border-b-[3px] border-emerald-400 text-emerald-300 bg-gradient-to-t from-emerald-900/10 to-transparent',
                            'inactive' => 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-200'
                        ],
                        'rose' => [
                            'active' => 'border-b-[3px] border-rose-400 text-rose-300 bg-gradient-to-t from-rose-900/10 to-transparent',
                            'inactive' => 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-200'
                        ],
                        'purple' => [
                            'active' => 'border-b-[3px] border-purple-400 text-purple-300 bg-gradient-to-t from-purple-900/10 to-transparent',
                            'inactive' => 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-200'
                        ],
                        'amber' => [
                            'active' => 'border-b-[3px] border-amber-400 text-amber-300 bg-gradient-to-t from-amber-900/10 to-transparent',
                            'inactive' => 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-200'
                        ]
                    ];
                    
                    $activeClass = $isActive 
                        ? ($tabClasses[$tColor]['active'] ?? 'border-b-[3px] border-slate-400 text-slate-300')
                        : ($tabClasses[$tColor]['inactive'] ?? 'text-slate-400');
                ?>
                <a href="<?= BASE_URL ?>/budgets/tracking/<?= $record['id'] ?>/form?type_id=<?= $tab['id'] ?>"
                    class="tab-link flex-1 min-w-max px-6 py-4 text-sm font-semibold flex items-center justify-center gap-2 transition-colors <?= $activeClass ?>"
                    data-tab-id="<?= $tab['id'] ?>">
                    <i data-lucide="<?= $tMeta['icon'] ?>" class="w-4 h-4"></i>
                    <?= $tName ?>
                </a>
                <?php endforeach; ?>
            </nav>
        </div>

        <!-- Tab Content -->
        <div class="p-6">
            
            <!-- Summary Bar -->
            <?php
                $bgClass = "bg-slate-800";
                $borderClass = "border-slate-700";
                $textClass = "text-slate-300";
                $iconClass = "text-slate-400";
                
                $colorStyles = [
                    'blue' => ['bg' => 'bg-blue-900/20', 'border' => 'border-blue-800/30', 'text' => 'text-blue-300', 'icon' => 'text-blue-400'],
                    'emerald' => ['bg' => 'bg-emerald-900/20', 'border' => 'border-emerald-800/30', 'text' => 'text-emerald-300', 'icon' => 'text-emerald-400'],
                    'rose' => ['bg' => 'bg-rose-900/20', 'border' => 'border-rose-800/30', 'text' => 'text-rose-300', 'icon' => 'text-rose-400'],
                    'purple' => ['bg' => 'bg-purple-900/20', 'border' => 'border-purple-800/30', 'text' => 'text-purple-300', 'icon' => 'text-purple-400'],
                    'amber' => ['bg' => 'bg-amber-900/20', 'border' => 'border-amber-800/30', 'text' => 'text-amber-300', 'icon' => 'text-amber-400']
                ];
                
                if (isset($colorStyles[$color])) {
                    $bgClass = $colorStyles[$color]['bg'];
                    $borderClass = $colorStyles[$color]['border'];
                    $textClass = $colorStyles[$color]['text'];
                    $iconClass = $colorStyles[$color]['icon'];
                }
            ?>
            <div class="flex items-center justify-between mb-4 p-4 <?= $bgClass ?> border <?= $borderClass ?> rounded-lg">
                <div class="flex items-center gap-3">
                    <i data-lucide="<?= $meta['icon'] ?>" class="w-6 h-6 <?= $iconClass ?>"></i>
                    <span class="text-lg font-semibold <?= $textClass ?>"><?= $currentTypeName ?></span>
                    <span class="text-xs text-slate-500">(<?= count($groups) ?> หมวด)</span>
                </div>
                <div class="flex items-center gap-6 text-sm">
                    <span class="text-slate-400">งบจัดสรร: <span id="summary-allocated" class="text-slate-100 font-semibold"><?= number_format($totalAllocated, 2) ?></span></span>
                    <span class="text-slate-400">เบิกจ่าย: <span id="summary-disbursed" class="text-orange-400 font-semibold"><?= number_format($totalDisbursed, 2) ?></span></span>
                    <span class="text-slate-400">คงเหลือ: <span id="summary-remaining" class="<?= $iconClass ?> font-bold"><?= number_format($totalRemaining, 2) ?></span></span>
                </div>
            </div>

            <!-- Form -->
            <form action="<?= BASE_URL ?>/budgets/tracking/<?= $record['id'] ?>/save" method="POST" id="mainForm">
                <input type="hidden" name="type_id" value="<?= $activeTypeId ?>">

                <div class="overflow-x-auto rounded-lg border border-slate-700">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-800/60 text-slate-400 text-xs uppercase tracking-wider border-b border-slate-700/50">
                                <th class="px-4 py-3 text-left w-[30%]">รายการ</th>
                                <th class="px-4 py-3 text-center w-20">จำนวน</th>
                                <th class="px-4 py-3 text-right w-28">งบจัดสรร</th>
                                <th class="px-4 py-3 text-right w-24">โอน +/-</th>
                                <th class="px-4 py-3 text-right w-28 text-warning-400">เบิกจ่าย</th>
                                <th class="px-4 py-3 text-right w-24">ขออนุมัติ</th>
                                <th class="px-4 py-3 text-right w-24">PO</th>
                                <th class="px-4 py-3 text-right w-28 <?= $iconClass ?>">คงเหลือ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700/30">
                            <?php foreach ($groups as $group): ?>
                                <!-- Group Header -->
                                <tr class="bg-slate-700/20">
                                    <td colspan="8" class="px-4 py-2 font-medium <?= $textClass ?>">
                                        <i data-lucide="folder-open" class="w-4 h-4 inline mr-2 opacity-70"></i>
                                        <?= $group['name_th'] ?>
                                    </td>
                                </tr>

                                <!-- Recursive Items Rendering -->
                                <?php 
                                $renderItems = function($items, $level) use (&$renderItems, $trackings, $iconClass, $isReadOnly) {
                                    foreach ($items as $item): 
                                        $t = $trackings[$item['id']] ?? [];
                                        $alloc = (float)($t['allocated'] ?? 0);
                                        $trans = (float)($t['transfer'] ?? 0);
                                        $disb = (float)($t['disbursed'] ?? 0);
                                        $pend = (float)($t['pending'] ?? 0);
                                        $po = (float)($t['po'] ?? 0);
                                        $rem = ($alloc + $trans) - ($disb + $pend + $po);
                                        
                                        // Indentation (16px per level)
                                        $paddingLeft = ($level * 16) + 16 . 'px'; 
                                        
                                        $hasChildren = !empty($item['children']);
                                        $rowClass = $hasChildren ? "font-medium text-slate-200" : "text-slate-300";
                                        $itemId = $item['id'];
                                        // Default Collapse: Hide if level > 0
                                        $rowStyle = ($level > 0) ? 'display: none;' : '';
                                        $isParentRow = $hasChildren ? 'parent-row' : '';
                                ?>
                                <tr class="hover:bg-slate-800/30 transition-colors item-row <?= $isParentRow ?>" style="<?= $rowStyle ?>" data-id="<?= $itemId ?>" data-parent="<?= $item['parent_id'] ?? '' ?>" data-has-children="<?= $hasChildren ? '1' : '0' ?>">
                                    <td class="py-2 pr-4">
                                        <div style="padding-left: <?= $paddingLeft ?>" class="flex items-center <?= $rowClass ?>">
                                            <?php if ($hasChildren): ?>
                                                <button type="button" class="toggle-children mr-2 text-slate-500 hover:text-slate-300" data-target="<?= $itemId ?>">
                                                    <i data-lucide="chevron-down" class="w-4 h-4"></i>
                                                </button>
                                            <?php endif; ?>
                                            <?= $item['name_th'] ?>
                                        </div>
                                    </td>
                                    <td class="px-2 py-2 text-center text-slate-500">-</td>
                                    
                                    <!-- Inputs -->
                                    <td class="p-1">
                                        <input type="number" step="0.01" <?= $isReadOnly || $hasChildren ? 'disabled' : '' ?> 
                                               class="w-full px-2 py-1 bg-slate-700/50 border border-transparent hover:border-slate-600 focus:border-primary-500 rounded text-right text-slate-100 text-sm focus:outline-none focus:ring-1 focus:ring-primary-500/50 transition-all inp-allocated disabled:opacity-50 disabled:cursor-not-allowed"
                                               name="items[<?= $itemId ?>][allocated]" value="<?= $alloc ?: '' ?>" placeholder="0">
                                    </td>
                                    <td class="p-1">
                                        <input type="number" step="0.01" <?= $isReadOnly || $hasChildren ? 'disabled' : '' ?> 
                                               class="w-full px-2 py-1 bg-slate-700/50 border border-transparent hover:border-slate-600 focus:border-primary-500 rounded text-right text-cyan-400 text-sm focus:outline-none focus:ring-1 focus:ring-primary-500/50 transition-all inp-transfer disabled:opacity-50 disabled:cursor-not-allowed"
                                               name="items[<?= $itemId ?>][transfer]" value="<?= $trans ?: '' ?>" placeholder="0">
                                    </td>
                                    <td class="p-1">
                                        <input type="number" step="0.01" <?= $isReadOnly || $hasChildren ? 'disabled' : '' ?> 
                                               class="w-full px-2 py-1 bg-slate-700/50 border border-transparent hover:border-slate-600 focus:border-primary-500 rounded text-right text-orange-400 font-medium text-sm focus:outline-none focus:ring-1 focus:ring-primary-500/50 transition-all inp-disbursed disabled:opacity-50 disabled:cursor-not-allowed"
                                               name="items[<?= $itemId ?>][disbursed]" value="<?= $disb ?: '' ?>" placeholder="0">
                                    </td>
                                    <td class="p-1">
                                        <input type="number" step="0.01" <?= $isReadOnly || $hasChildren ? 'disabled' : '' ?> 
                                               class="w-full px-2 py-1 bg-slate-700/50 border border-transparent hover:border-slate-600 focus:border-primary-500 rounded text-right text-slate-400 text-sm focus:outline-none focus:ring-1 focus:ring-primary-500/50 transition-all inp-pending disabled:opacity-50 disabled:cursor-not-allowed"
                                               name="items[<?= $itemId ?>][pending]" value="<?= $pend ?: '' ?>" placeholder="0">
                                    </td>
                                    <td class="p-1">
                                        <input type="number" step="0.01" <?= $isReadOnly || $hasChildren ? 'disabled' : '' ?> 
                                               class="w-full px-2 py-1 bg-slate-700/50 border border-transparent hover:border-slate-600 focus:border-primary-500 rounded text-right text-slate-400 text-sm focus:outline-none focus:ring-1 focus:ring-primary-500/50 transition-all inp-po disabled:opacity-50 disabled:cursor-not-allowed"
                                               name="items[<?= $itemId ?>][po]" value="<?= $po ?: '' ?>" placeholder="0">
                                    </td>
                                    
                                    <td class="px-4 py-2 text-right font-bold <?= $iconClass ?> cell-remaining">
                                        <?= number_format($rem, 2) ?>
                                    </td>
                                </tr>
                                <?php
                                    if ($hasChildren) {
                                        $renderItems($item['children'], $level + 1);
                                    }
                                    endforeach;
                                };

                                $renderItems($group['items'] ?? [], 0);
                                ?>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="border-t border-slate-600 bg-slate-700/20">
                            <tr class="font-bold text-slate-200">
                                <td colspan="2" class="px-4 py-3 text-center">รวมทั้งสิ้น (Total)</td>
                                <td class="px-4 py-3 text-right" id="footer-allocated"><?= number_format($totalAllocated, 2) ?></td>
                                <td class="px-4 py-3 text-right text-cyan-400" id="footer-transfer"><?= number_format($totalTransfer, 2) ?></td>
                                <td class="px-4 py-3 text-right text-orange-400" id="footer-disbursed"><?= number_format($totalDisbursed, 2) ?></td>
                                <td class="px-4 py-3 text-right text-slate-400" id="footer-pending"><?= number_format($totalPending, 2) ?></td>
                                <td class="px-4 py-3 text-right text-slate-400" id="footer-po"><?= number_format($totalPo, 2) ?></td>
                                <td class="px-4 py-3 text-right <?= $iconClass ?>" id="footer-remaining"><?= number_format($totalRemaining, 2) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Action Footer -->
                <div class="mt-6 flex justify-between gap-3">
                    <a href="<?= BASE_URL ?>/budgets/tracking/activities?session_id=<?= $record['session_id'] ?>" 
                       class="px-5 py-2.5 bg-slate-700 text-slate-200 rounded-lg font-medium border border-slate-600 hover:bg-slate-600 transition-colors flex items-center gap-2">
                        <i data-lucide="<?= $isReadOnly ? 'arrow-left' : 'arrow-left' ?>" class="w-4 h-4"></i> กลับ
                    </a>
                    
                    <?php if (!$isReadOnly): ?>
                    <button type="submit" class="px-6 py-2.5 bg-primary-600 text-white rounded-lg font-medium shadow-lg shadow-primary-900/30 hover:bg-primary-500 transition-colors flex items-center gap-2">
                        <i data-lucide="save" class="w-4 h-4"></i> บันทึกข้อมูล
                    </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('mainForm');
        
        // 1. Calculation Logic
        // --------------------
        function formatNumber(num) {
            return num.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }

        function getVal(el) {
            return parseFloat(el?.value || 0);
        }

        function updateRow(tr) {
            if (!tr) return;
            
            const alloc = getVal(tr.querySelector('.inp-allocated'));
            const trans = getVal(tr.querySelector('.inp-transfer'));
            const disb = getVal(tr.querySelector('.inp-disbursed'));
            const pend = getVal(tr.querySelector('.inp-pending'));
            const po = getVal(tr.querySelector('.inp-po'));
            
            const rem = (alloc + trans) - (disb + pend + po);
            
            const remCell = tr.querySelector('.cell-remaining');
            if(remCell) {
                remCell.textContent = formatNumber(rem);
                if (rem < 0) remCell.classList.add('text-red-400');
                else remCell.classList.remove('text-red-400');
            }
        }

        function updateGlobals() {
            let tAlloc = 0, tTrans = 0, tDisb = 0, tPend = 0, tPo = 0;
            
            // Only sum rows that are NOT parents (leaf nodes) to avoid double counting
            document.querySelectorAll('tr.item-row:not(.parent-row)').forEach(tr => {
                tAlloc += getVal(tr.querySelector('.inp-allocated'));
                tTrans += getVal(tr.querySelector('.inp-transfer'));
                tDisb += getVal(tr.querySelector('.inp-disbursed'));
                tPend += getVal(tr.querySelector('.inp-pending'));
                tPo += getVal(tr.querySelector('.inp-po'));
            });

            const tRem = (tAlloc + tTrans) - (tDisb + tPend + tPo);

            const setTxt = (id, val) => {
                const el = document.getElementById(id);
                if(el) el.textContent = formatNumber(val);
            };

            setTxt('summary-allocated', tAlloc);
            setTxt('summary-disbursed', tDisb);
            setTxt('summary-remaining', tRem);
            
            setTxt('footer-allocated', tAlloc);
            setTxt('footer-transfer', tTrans);
            setTxt('footer-disbursed', tDisb);
            setTxt('footer-pending', tPend);
            setTxt('footer-po', tPo);
            setTxt('footer-remaining', tRem);
        }

        // Update Parent Row Totals from Children
        function updateParentTotals(parentId) {
            const parentRow = document.querySelector(`tr.parent-row[data-id="${parentId}"]`);
            if (!parentRow) return;
            
            // Find all immediate children (not grandchildren)
            const children = document.querySelectorAll(`tr[data-parent="${parentId}"]`);
            
            let sumAlloc = 0, sumTrans = 0, sumDisb = 0, sumPend = 0, sumPo = 0;
            
            children.forEach(childRow => {
                // If child is also a parent, get its calculated values
                const isChildParent = childRow.classList.contains('parent-row');
                
                if (isChildParent) {
                    // Get calculated values from the cells (not inputs, since they're disabled)
                    const childAlloc = parseFloat(childRow.querySelector('.inp-allocated')?.value || 0);
                    const childTrans = parseFloat(childRow.querySelector('.inp-transfer')?.value || 0);
                    const childDisb = parseFloat(childRow.querySelector('.inp-disbursed')?.value || 0);
                    const childPend = parseFloat(childRow.querySelector('.inp-pending')?.value || 0);
                    const childPo = parseFloat(childRow.querySelector('.inp-po')?.value || 0);
                    
                    sumAlloc += childAlloc;
                    sumTrans += childTrans;
                    sumDisb += childDisb;
                    sumPend += childPend;
                    sumPo += childPo;
                } else {
                    // Leaf node - get from inputs
                    sumAlloc += getVal(childRow.querySelector('.inp-allocated'));
                    sumTrans += getVal(childRow.querySelector('.inp-transfer'));
                    sumDisb += getVal(childRow.querySelector('.inp-disbursed'));
                    sumPend += getVal(childRow.querySelector('.inp-pending'));
                    sumPo += getVal(childRow.querySelector('.inp-po'));
                }
            });
            
            // Update parent's disabled inputs with calculated values
            const parentAllocInput = parentRow.querySelector('.inp-allocated');
            const parentTransInput = parentRow.querySelector('.inp-transfer');
            const parentDisbInput = parentRow.querySelector('.inp-disbursed');
            const parentPendInput = parentRow.querySelector('.inp-pending');
            const parentPoInput = parentRow.querySelector('.inp-po');
            
            if (parentAllocInput) parentAllocInput.value = sumAlloc;
            if (parentTransInput) parentTransInput.value = sumTrans;
            if (parentDisbInput) parentDisbInput.value = sumDisb;
            if (parentPendInput) parentPendInput.value = sumPend;
            if (parentPoInput) parentPoInput.value = sumPo;
            
            // Update parent's remaining cell
            updateRow(parentRow);
        }
        
        // Recursively update all parent totals from bottom up
        function updateAllParentTotals() {
            // Get all parent rows sorted by depth (deepest first)
            const parentRows = Array.from(document.querySelectorAll('tr.parent-row'));
            
            // Calculate depth for each parent (count ancestors)
            const rowDepths = parentRows.map(row => {
                let depth = 0;
                let parentId = row.dataset.parent;
                while (parentId) {
                    depth++;
                    const parent = document.querySelector(`tr[data-id="${parentId}"]`);
                    parentId = parent?.dataset.parent;
                }
                return { row, depth };
            });
            
            // Sort by depth descending (deepest first)
            rowDepths.sort((a, b) => b.depth - a.depth);
            
            // Update from bottom up
            rowDepths.forEach(({row}) => {
                updateParentTotals(row.dataset.id);
            });
        }

        // Input change handler
        if(form) {
            form.addEventListener('input', function(e) {
                if (e.target.matches('input[type="number"]')) {
                    updateRow(e.target.closest('tr'));
                    updateAllParentTotals();
                    updateGlobals();
                    isDirty = true; // Mark as dirty when data changes
                }
            });
        }
        
        // Initialize parent totals on page load
        updateAllParentTotals();
        
        // 3. Warning Modal for Unsaved Changes
        // -------------------------------------
        let isDirty = false;
        
        // Browser warning when closing/refreshing
        window.addEventListener('beforeunload', (e) => {
            if (isDirty) {
                e.preventDefault();
                e.returnValue = ''; // Chrome requires returnValue to be set
            }
        });
        
        // Custom warning when clicking tab links
        document.querySelectorAll('.tab-link').forEach(link => {
            link.addEventListener('click', function(e) {
                if (isDirty && this.dataset.tabId != <?= $activeTypeId ?>) {
                    if (!confirm('คุณมีข้อมูลที่ยังไม่ได้บันทึก\n\nต้องการออกจากหน้านี้โดยไม่บันทึกหรือไม่?')) {
                        e.preventDefault();
                    }
                }
            });
        });
        
        // Reset dirty flag after successful save
        if (form) {
            form.addEventListener('submit', function() {
                isDirty = false; // Allow navigation after save
            });
        }

        // 2. Collapse/Expand Logic
        // ------------------------
        document.querySelectorAll('.toggle-children').forEach(btn => {
            // Set initial state (Default Collapsed)
            btn.dataset.expanded = 'false';
            
            // Note: Icon transition is set via CSS class or inline style dynamically below
            // We wait for Lucide to render icons first or use generic selector

            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation(); // Prevent row click issues if any
                
                const targetId = this.dataset.target;
                const isExpanded = this.dataset.expanded === 'true';
                const newState = !isExpanded;
                
                // Update State
                this.dataset.expanded = newState;
                
                // Rotate Icon
                // Lucide renders <svg> replacing <i>
                const icon = this.querySelector('svg') || this.querySelector('i');
                if (icon) {
                    icon.style.transition = 'transform 0.2s ease-in-out';
                    icon.style.transform = newState ? 'rotate(0deg)' : 'rotate(-90deg)';
                }
                
                toggleChildRows(targetId, newState);
            });
        });

        function toggleChildRows(parentId, show) {
            document.querySelectorAll(`tr[data-parent="${parentId}"]`).forEach(row => {
                row.style.display = show ? '' : 'none';
                const childId = row.dataset.id;
                
                // Logic:
                // If Hide -> Hide all descendants recursively.
                // If Show -> Show direct children. IF child was expanded, recurse.
                
                if (!show) {
                    if (row.dataset.hasChildren === '1') {
                        toggleChildRows(childId, false);
                    }
                } else {
                    if (row.dataset.hasChildren === '1') {
                        const btn = row.querySelector('.toggle-children');
                        // Default to true if not set (for safety), but dataset string check is explicit
                        const isChildExpanded = btn && (btn.dataset.expanded === 'true' || btn.dataset.expanded === undefined);
                        
                        if (isChildExpanded) {
                            toggleChildRows(childId, true);
                        }
                    }
                }
            });
        }

        // 3. Initialize Icons at the END
        // ------------------------------
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
            
            // Add transition style to icons AFTER render
            // setTimeout to ensure Lucide has replaced elements
            setTimeout(() => {
                document.querySelectorAll('.toggle-children').forEach(btn => {
                    const svg = btn.querySelector('svg');
                    if (svg) {
                        svg.style.transition = 'transform 0.2s ease-in-out';
                        if (btn.dataset.expanded === 'false') {
                            svg.style.transform = 'rotate(-90deg)';
                        }
                    }
                });
            }, 50);
        }
    });
</script>
