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
                    <div class="flex items-center gap-2">
                        <i data-lucide="<?= $tMeta['icon'] ?>" class="w-4 h-4"></i>
                        <?= $tName ?>
                    </div>
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
                    <table class="w-full text-sm table-fixed">
                        <thead>
                            <tr class="bg-slate-800/60 text-slate-400 text-xs uppercase tracking-wider border-b border-slate-700/50">
                                <th class="px-4 py-3 text-left w-[25%]">รายการ</th>
                                <th class="px-4 py-3 text-center w-[8%]">จำนวน</th>
                                <th class="px-4 py-3 text-right w-[12%]">งบจัดสรร</th>
                                <th class="px-4 py-3 text-right w-[10%]">โอน +/-</th>
                                <th class="px-4 py-3 text-right w-[12%] text-warning-400">เบิกจ่าย</th>
                                <th class="px-4 py-3 text-right w-[10%]">ขออนุมัติ</th>
                                <th class="px-4 py-3 text-right w-[8%]">PO</th>
                                <th class="px-4 py-3 text-right w-[15%] <?= $iconClass ?>">คงเหลือ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700/30">
                            <?php foreach ($groups as $group): 
                                $groupId = "group-" . $group['id'];
                            ?>
                                <!-- Group Header (as Super Parent) -->
                                <tr class="hover:bg-slate-800/30 transition-colors parent-row cursor-pointer group-header-row" 
                                    data-id="<?= $groupId ?>" 
                                    data-has-children="1" 
                                    data-parent=""
                                    data-category="<?= $group['id'] ?>"
                                    onclick="document.querySelector('.toggle-children[data-target=\'<?= $groupId ?>\']')?.click()">
                                    
                                    <td class="py-3 pr-4 pl-4 font-semibold text-slate-200 truncate" title="<?= htmlspecialchars($group['name_th']) ?>">
                                        <div class="flex items-center gap-2 truncate">
                                            <!-- Toggle Button with Chevron -->
                                            <button type="button" class="toggle-children text-slate-500 hover:text-slate-300 transition-transform duration-200 flex-shrink-0" 
                                                    data-target="<?= $groupId ?>" 
                                                    data-expanded="false" 
                                                    onclick="event.stopPropagation()">
                                                <i data-lucide="chevron-down" class="w-4 h-4" style="transform: rotate(-90deg);"></i>
                                            </button>
                                            
                                            <!-- Folder Icon (Visual only) -->
                                            <i data-lucide="folder" class="w-4 h-4 <?= $iconClass ?> flex-shrink-0"></i>
                                            
                                            <span class="truncate"><?= $group['name_th'] ?></span>
                                        </div>
                                    </td>
                                    
                                    <!-- Group Aggregates -->
                                    <td class="px-4 py-2">
                                        <div class="text-center font-bold text-slate-300 val-quantity">0.00</div>
                                        <input type="hidden" class="inp-quantity" value="0">
                                    </td>
                                    <td class="px-4 py-2">
                                        <div class="text-right font-bold text-slate-100 val-allocated">0.00</div>
                                        <input type="hidden" class="inp-allocated" value="0">
                                    </td>
                                    <td class="px-4 py-2">
                                        <div class="text-right font-bold text-cyan-400 val-transfer">0.00</div>
                                        <input type="hidden" class="inp-transfer" value="0">
                                    </td>
                                    <td class="px-4 py-2">
                                        <div class="text-right font-bold text-orange-400 val-disbursed">0.00</div>
                                        <input type="hidden" class="inp-disbursed" value="0">
                                    </td>
                                    <td class="px-4 py-2">
                                        <div class="text-right font-bold text-slate-400 val-pending">0.00</div>
                                        <input type="hidden" class="inp-pending" value="0">
                                    </td>
                                    <td class="px-4 py-2">
                                        <div class="text-right font-bold text-slate-400 val-po">0.00</div>
                                        <input type="hidden" class="inp-po" value="0">
                                    </td>
                                    <td class="px-4 py-2 text-right font-bold <?= $iconClass ?> cell-remaining">
                                        0.00
                                    </td>
                                </tr>

                                <!-- Recursive Items Rendering -->
                                <?php 
                                $renderItems = function($items, $level, $parentId) use (&$renderItems, $trackings, $iconClass, $isReadOnly) {
                                    foreach ($items as $item): 
                                        $t = $trackings[$item['id']] ?? [];
                                        $alloc = (float)($t['allocated'] ?? 0);
                                        $trans = (float)($t['transfer'] ?? 0);
                                        $disb = (float)($t['disbursed'] ?? 0);
                                        $pend = (float)($t['pending'] ?? 0);
                                        $po = (float)($t['po'] ?? 0);
                                        $qty = (float)($t['quantity'] ?? 0); 
                                        $price = (float)($t['unit_price'] ?? 0);
                                        
                                        $rem = ($alloc + $trans) - ($disb + $pend + $po);
                                        
                                        $paddingLeft = ($level * 16) + 32 . 'px'; 
                                        
                                        $hasChildren = !empty($item['children']);
                                        $rowClass = $hasChildren ? "font-medium text-slate-200" : "text-slate-300";
                                        $itemId = $item['id'];
                                        
                                        // Default visibility: HIDDEN (Collapsed by default)
                                        $rowStyle = 'display: none;'; 
                                        
                                        $isParentRow = $hasChildren ? 'parent-row' : '';
                                ?>
                                <tr class="hover:bg-slate-800/30 transition-colors item-row <?= $isParentRow ?>" 
                                    style="<?= $rowStyle ?>" 
                                    data-id="<?= $itemId ?>" 
                                    data-parent="<?= $parentId ?>" 
                                    data-has-children="<?= $hasChildren ? '1' : '0' ?>" 
                                    data-category="<?= $item['expense_group_id'] ?? '' ?>">
                                    
                                    <td class="py-2 pr-4 truncate">
                                        <div style="padding-left: <?= $paddingLeft ?>" class="flex items-center <?= $rowClass ?> truncate" title="<?= htmlspecialchars($item['name_th']) ?>">
                                            <?php if ($hasChildren): ?>
                                                <button type="button" class="toggle-children mr-2 text-slate-500 hover:text-slate-300 flex-shrink-0" data-target="<?= $itemId ?>">
                                                    <i data-lucide="chevron-down" class="w-4 h-4"></i>
                                                </button>
                                            <?php else: ?>
                                                <!-- Spacer for visual alignment of leaves under parents -->
                                                <i data-lucide="chevron-right" class="w-4 h-4 mr-2 opacity-0 flex-shrink-0"></i>
                                            <?php endif; ?>
                                            <span class="truncate"><?= $item['name_th'] ?></span>
                                        </div>
                                    </td>
                                    
                                    <!-- Quantity -->
                                    <td class="px-4 py-2">
                                        <?php if ($hasChildren): ?>
                                            <div class="text-center font-bold text-slate-300 val-quantity"><?= number_format($qty, 2) ?></div>
                                            <input type="hidden" class="inp-quantity" name="items[<?= $itemId ?>][quantity]" value="<?= $qty ?>">
                                        <?php else: ?>
                                            <input type="text" inputmode="decimal" <?= $isReadOnly ? 'disabled' : '' ?> 
                                               class="w-full px-2 py-1 bg-slate-700/50 border border-transparent hover:border-slate-600 focus:border-primary-500 rounded text-center text-slate-300 text-sm focus:outline-none focus:ring-1 focus:ring-primary-500/50 transition-all inp-quantity disabled:opacity-50 disabled:cursor-not-allowed"
                                               name="items[<?= $itemId ?>][quantity]" value="<?= $qty > 0 ? $qty : '0.00' ?>" placeholder="0.00">
                                        <?php endif; ?>
                                        <input type="hidden" class="inp-unit-price" name="items[<?= $itemId ?>][unit_price]" value="<?= $price ?>">
                                    </td>
                                    
                                    <!-- Allocated -->
                                    <td class="px-4 py-2">
                                        <?php if ($hasChildren): ?>
                                            <div class="text-right font-bold text-slate-100 val-allocated"><?= number_format($alloc, 2) ?></div>
                                            <input type="hidden" class="inp-allocated" name="items[<?= $itemId ?>][allocated]" value="<?= $alloc ?>">
                                        <?php else: ?>
                                            <input type="text" inputmode="decimal" <?= $isReadOnly ? 'disabled' : '' ?> 
                                               class="w-full px-2 py-1 bg-slate-700/50 border border-transparent hover:border-slate-600 focus:border-primary-500 rounded text-right text-slate-100 text-sm focus:outline-none focus:ring-1 focus:ring-primary-500/50 transition-all inp-allocated disabled:opacity-50 disabled:cursor-not-allowed"
                                               name="items[<?= $itemId ?>][allocated]" value="<?= $alloc > 0 ? $alloc : '0.00' ?>" placeholder="0.00">
                                        <?php endif; ?>
                                    </td>
                                    
                                    <!-- Transfer -->
                                    <td class="px-4 py-2">
                                        <?php if ($hasChildren): ?>
                                            <div class="text-right font-bold text-cyan-400 val-transfer"><?= number_format($trans, 2) ?></div>
                                            <input type="hidden" class="inp-transfer" name="items[<?= $itemId ?>][transfer]" value="<?= $trans ?>">
                                        <?php else: ?>
                                            <input type="text" inputmode="decimal" <?= $isReadOnly ? 'disabled' : '' ?> 
                                               class="w-full px-2 py-1 bg-slate-700/50 border border-transparent hover:border-slate-600 focus:border-primary-500 rounded text-right text-cyan-400 text-sm focus:outline-none focus:ring-1 focus:ring-primary-500/50 transition-all inp-transfer disabled:opacity-50 disabled:cursor-not-allowed"
                                               name="items[<?= $itemId ?>][transfer]" value="<?= $trans != 0 ? $trans : '0.00' ?>" placeholder="0.00">
                                        <?php endif; ?>
                                    </td>
                                    
                                    <!-- Disbursed -->
                                    <td class="px-4 py-2">
                                        <?php if ($hasChildren): ?>
                                            <div class="text-right font-bold text-orange-400 val-disbursed"><?= number_format($disb, 2) ?></div>
                                            <input type="hidden" class="inp-disbursed" name="items[<?= $itemId ?>][disbursed]" value="<?= $disb ?>">
                                        <?php else: ?>
                                            <input type="text" inputmode="decimal" <?= $isReadOnly ? 'disabled' : '' ?> 
                                               class="w-full px-2 py-1 bg-slate-700/50 border border-transparent hover:border-slate-600 focus:border-primary-500 rounded text-right text-orange-400 font-medium text-sm focus:outline-none focus:ring-1 focus:ring-primary-500/50 transition-all inp-disbursed disabled:opacity-50 disabled:cursor-not-allowed"
                                               name="items[<?= $itemId ?>][disbursed]" value="<?= $disb > 0 ? $disb : '0.00' ?>" placeholder="0.00">
                                        <?php endif; ?>
                                    </td>
                                    
                                    <!-- Pending -->
                                    <td class="px-4 py-2">
                                        <?php if ($hasChildren): ?>
                                            <div class="text-right font-bold text-slate-400 val-pending"><?= number_format($pend, 2) ?></div>
                                            <input type="hidden" class="inp-pending" name="items[<?= $itemId ?>][pending]" value="<?= $pend ?>">
                                        <?php else: ?>
                                            <input type="text" inputmode="decimal" <?= $isReadOnly ? 'disabled' : '' ?> 
                                               class="w-full px-2 py-1 bg-slate-700/50 border border-transparent hover:border-slate-600 focus:border-primary-500 rounded text-right text-slate-400 text-sm focus:outline-none focus:ring-1 focus:ring-primary-500/50 transition-all inp-pending disabled:opacity-50 disabled:cursor-not-allowed"
                                               name="items[<?= $itemId ?>][pending]" value="<?= $pend > 0 ? $pend : '0.00' ?>" placeholder="0.00">
                                        <?php endif; ?>
                                    </td>
                                    
                                    <!-- PO -->
                                    <td class="px-4 py-2">
                                        <?php if ($hasChildren): ?>
                                            <div class="text-right font-bold text-slate-400 val-po"><?= number_format($po, 2) ?></div>
                                            <input type="hidden" class="inp-po" name="items[<?= $itemId ?>][po]" value="<?= $po ?>">
                                        <?php else: ?>
                                            <input type="text" inputmode="decimal" <?= $isReadOnly ? 'disabled' : '' ?> 
                                               class="w-full px-2 py-1 bg-slate-700/50 border border-transparent hover:border-slate-600 focus:border-primary-500 rounded text-right text-slate-400 text-sm focus:outline-none focus:ring-1 focus:ring-primary-500/50 transition-all inp-po disabled:opacity-50 disabled:cursor-not-allowed"
                                               name="items[<?= $itemId ?>][po]" value="<?= $po > 0 ? $po : '0.00' ?>" placeholder="0.00">
                                        <?php endif; ?>
                                    </td>
                                    
                                    <td class="px-4 py-2 text-right font-bold <?= $iconClass ?> cell-remaining">
                                        <?= number_format($rem, 2) ?>
                                    </td>
                                </tr>
                                <?php
                                    if ($hasChildren) {
                                        $renderItems($item['children'], $level + 1, $itemId);
                                    }
                                    endforeach;
                                };

                                // Pass Group ID as the Parent for top level items
                                $renderItems($group['items'] ?? [], 0, $groupId);
                                ?>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="bg-slate-900/80 border-t-2 border-slate-600">
                            <tr class="font-bold text-slate-200">
                                <td class="px-4 py-3 text-center">รวมทั้งสิ้น (Total)</td>
                                <td class="px-4 py-3 text-center" id="footer-quantity">0.00</td>
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
                    <div class="flex gap-3">
                        <button type="button" class="btn-clear-tab px-5 py-2.5 bg-slate-600 text-slate-200 rounded-lg font-medium hover:bg-slate-500 transition-colors flex items-center gap-2">
                             <i data-lucide="rotate-ccw" class="w-4 h-4"></i> ล้างค่า
                        </button>
                        <button type="submit" class="px-6 py-2.5 bg-primary-600 text-white rounded-lg font-medium shadow-lg shadow-primary-900/30 hover:bg-primary-500 transition-colors flex items-center gap-2">
                            <i data-lucide="save" class="w-4 h-4"></i> บันทึกข้อมูล
                        </button>
                    </div>
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
            
            // Calculate Amount if Price & Qty exist (Optional: if user wants auto-calc amount)
            // Note: The requirement didn't explicitly ask for Qty * Price = Amount logic for this form, 
            // but usually tracking forms carry over amounts. For now, we respect the manual inputs
            // but if this was a request form, we would calc. Here, we just sum up.
            
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
            
            // Return values for parent aggregation
            return {
                qty: getVal(tr.querySelector('.inp-quantity')),
                price: getVal(tr.querySelector('.inp-unit-price')),
                alloc, trans, disb, pend, po
            };
        }

        function updateGlobals() {
            let tQty = 0, tAlloc = 0, tTrans = 0, tDisb = 0, tPend = 0, tPo = 0;
            const tabTotals = {}; 

            // Initialize tab totals
            document.querySelectorAll('.tab-link').forEach(link => {
                tabTotals[link.dataset.tabId] = 0;
            });

            // Iterate all rows to calculate globals and per-tab totals
            document.querySelectorAll('tr.item-row:not(.parent-row)').forEach(tr => {
                const qty = getVal(tr.querySelector('.inp-quantity'));
                const alloc = getVal(tr.querySelector('.inp-allocated'));
                const trans = getVal(tr.querySelector('.inp-transfer'));
                const disb = getVal(tr.querySelector('.inp-disbursed'));
                const pend = getVal(tr.querySelector('.inp-pending'));
                const po = getVal(tr.querySelector('.inp-po'));
                
                // For global totals
                tQty += qty;
                tAlloc += alloc;
                tTrans += trans;
                tDisb += disb;
                tPend += pend;
                tPo += po;
                
                // For Tab Totals (Sum of Allocated? Or Disbursed? Usually Allocated or Remaining)
                // Let's sum 'Remaining' or 'Allocated'? Requirement says "Show total". 
                // Let's show TOTAL ALLOCATED for each tab as primary indicator.
                // Or maybe Disbursed? Let's go with Disbursed (Orange) as it's a tracking form.
                // Wait, usually users want to see "How much money is in this tab".
                // Let's show "Allocated" (Ngob)
                if (tr.dataset.category) {
                    // Start with 0 if undefined
                    if (!tabTotals[tr.dataset.category]) tabTotals[tr.dataset.category] = 0;
                    tabTotals[tr.dataset.category] += alloc; 
                }
            });

            const tRem = (tAlloc + tTrans) - (tDisb + tPend + tPo);

            const setTxt = (id, val) => {
                const el = document.getElementById(id);
                if(el) el.textContent = formatNumber(val);
            };

            setTxt('summary-allocated', tAlloc);
            setTxt('summary-disbursed', tDisb);
            setTxt('summary-remaining', tRem);
            
            setTxt('footer-quantity', tQty);
            setTxt('footer-allocated', tAlloc);
            setTxt('footer-transfer', tTrans);
            setTxt('footer-disbursed', tDisb);
            setTxt('footer-pending', tPend);
            setTxt('footer-po', tPo);
            setTxt('footer-remaining', tRem);
            
            // Update Tab Badges (Removed)
            /* Object.keys(tabTotals).forEach(tabId => {
               const badge = document.querySelector(`.tab-total-badge[data-tab-id="${tabId}"]`);
               if (badge) {
                   badge.textContent = formatNumber(tabTotals[tabId]);
               }
            }); */
        }

        // Update Parent Row Totals from Children
        function updateParentTotals(parentId) {
            const parentRow = document.querySelector(`tr.parent-row[data-id="${parentId}"]`);
            if (!parentRow) return;
            
            // Find all immediate children
            const children = document.querySelectorAll(`tr[data-parent="${parentId}"]`);
            
            let sQty=0, sPrice=0, sAlloc=0, sTrans=0, sDisb=0, sPend=0, sPo=0;
            let childCount = 0;
            
            children.forEach(childRow => {
                childCount++;
                const isChildParent = childRow.classList.contains('parent-row');
                
                if (isChildParent) {
                    // For parents, values are in inputs (disabled)
                     sQty += parseFloat(childRow.querySelector('.inp-quantity')?.value.replace(/,/g, '') || 0);
                     // Price is usually NOT summed for parents, but Average? Or Sum?
                     // Requirement: "Show calculated totals". Summing Unit Price makes no sense usually.
                     // But if it's a category sum, maybe just keep Logic 0 or same?
                     // Let's SUM for now as requested "Parent... show number total"
                     // Actually summing unit price is wrong. Let's leave Average or Blank.
                     // But for robustness, let's sum Amount fields (Alloc, Disb, etc.)
                     
                     sAlloc += parseFloat(childRow.querySelector('.inp-allocated')?.value || 0);
                     sTrans += parseFloat(childRow.querySelector('.inp-transfer')?.value || 0);
                     sDisb += parseFloat(childRow.querySelector('.inp-disbursed')?.value || 0);
                     sPend += parseFloat(childRow.querySelector('.inp-pending')?.value || 0);
                     sPo += parseFloat(childRow.querySelector('.inp-po')?.value || 0);
                } else {
                    sQty += getVal(childRow.querySelector('.inp-quantity'));
                    // sPrice += getVal(childRow.querySelector('.inp-unit-price')); // Don't sum price
                    
                    sAlloc += getVal(childRow.querySelector('.inp-allocated'));
                    sTrans += getVal(childRow.querySelector('.inp-transfer'));
                    sDisb += getVal(childRow.querySelector('.inp-disbursed'));
                    sPend += getVal(childRow.querySelector('.inp-pending'));
                    sPo += getVal(childRow.querySelector('.inp-po'));
                }
            });
            
            // Set values to parent inputs
            const setVal = (cls, val) => {
                const inp = parentRow.querySelector(cls);
                if(inp) inp.value = val;
                
                // Also update the text display for parent rows
                const displayClass = cls.replace('inp-', 'val-');
                const displayEl = parentRow.querySelector(displayClass);
                if (displayEl) {
                    displayEl.textContent = formatNumber(val);
                }
            };
            
            setVal('.inp-quantity', sQty);
            // setVal('.inp-unit-price', sPrice); 
            setVal('.inp-allocated', sAlloc);
            setVal('.inp-transfer', sTrans);
            setVal('.inp-disbursed', sDisb);
            setVal('.inp-pending', sPend);
            setVal('.inp-po', sPo);
            
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
                    isDirty = true;
                }
            });
            
            // Scroll Prevention
            form.addEventListener('wheel', function(e) {
                if (e.target.matches('input[type="number"]')) {
                    e.preventDefault();
                    e.target.blur();
                }
            }, { passive: false });
        }
        
        // Initial Calc
        updateAllParentTotals();
        updateGlobals(); // Run once to set tab totals
        
        // Clear Button Logic
        const btnClear = document.querySelector('.btn-clear-tab');
        if(btnClear) {
            btnClear.addEventListener('click', function(e) {
                e.preventDefault();
                if(!confirm('ต้องการล้างค่าข้อมูลทั้งหมดในแท็บนี้ใช่หรือไม่?')) return;
                
                // Clear only inputs in this tab
                document.querySelectorAll('tr.item-row:not(.parent-row) input').forEach(inp => {
                    if (inp.type === 'number' || inp.type === 'text') {
                        inp.value = '';
                    }
                });
                
                // Trigger updates
                document.querySelectorAll('tr.item-row:not(.parent-row)').forEach(tr => updateRow(tr));
                updateAllParentTotals();
                updateGlobals();
                isDirty = true;
            });
        }
        
        // Dirty Flag & JSON ... (Same as before)
        let isDirty = false;
        window.addEventListener('beforeunload', (e) => { 
            if(isDirty) { e.preventDefault(); e.returnValue = ''; } 
        });
        
        function prepareJSON() {
            const data = {};
            document.querySelectorAll('tr.item-row:not(.parent-row)').forEach(row => {
                const id = row.dataset.id;
                data[id] = {
                    quantity: getVal(row.querySelector('.inp-quantity')),
                    unit_price: getVal(row.querySelector('.inp-unit-price')),
                    allocated: getVal(row.querySelector('.inp-allocated')),
                    transfer: getVal(row.querySelector('.inp-transfer')),
                    disbursed: getVal(row.querySelector('.inp-disbursed')),
                    pending: getVal(row.querySelector('.inp-pending')),
                    po: getVal(row.querySelector('.inp-po'))
                };
            });
            
            let jsonInput = document.getElementById('items_json');
            if (!jsonInput) {
                jsonInput = document.createElement('input');
                jsonInput.type = 'hidden';
                jsonInput.id = 'items_json';
                jsonInput.name = 'items_json';
                form.appendChild(jsonInput);
            }
            jsonInput.value = JSON.stringify(data);
        }

        if (form) {
            form.addEventListener('submit', function(e) {
                prepareJSON();
                isDirty = false;
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
