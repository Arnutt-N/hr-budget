<?php
/**
 * View: Budget Request Form (Hierarchical Entry - Based on Wireframe Part 2)
 * Adapted from budgets/tracking/form.php logic
 */

// Ensure savedItems is always an array
$savedItems = $savedItems ?? [];

// Expense Type Metadata (Mapped by Category ID)
$typeMeta = [];
$tabMap = [];
foreach ($budgetTree as $cat) {
    // Assign colors based on category name pattern
    $color = 'slate';
    $icon = 'folder';
    $name = $cat['name_th'] ?? '';
    
    if (strpos($name, 'บุคลากร') !== false) {
        $color = 'blue';
        $icon = 'users';
    } elseif (strpos($name, 'ดำเนินงาน') !== false) {
        $color = 'emerald';
        $icon = 'briefcase';
    } elseif (strpos($name, 'ลงทุน') !== false) {
        $color = 'purple';
        $icon = 'building';
    } elseif (strpos($name, 'อุดหนุน') !== false) {
        $color = 'amber';
        $icon = 'heart-handshake';
    } elseif (strpos($name, 'รายจ่ายอื่น') !== false) {
        $color = 'rose';
        $icon = 'receipt';
    }
    
    $typeMeta[$cat['id']] = ['color' => $color, 'icon' => $icon];
    $tabMap[$cat['id']] = $cat;
}

// Ensure all 5 tabs exist (add placeholders if missing from database)
$requiredTabs = [
    ['id' => 'personnel', 'name_th' => 'งบบุคลากร', 'pattern' => 'บุคลากร'],
    ['id' => 'operating', 'name_th' => 'งบดำเนินงาน', 'pattern' => 'ดำเนินงาน'],
    ['id' => 'investment', 'name_th' => 'งบลงทุน', 'pattern' => 'ลงทุน'],
    ['id' => 'subsidy', 'name_th' => 'งบเงินอุดหนุน', 'pattern' => 'อุดหนุน'],
    ['id' => 'other', 'name_th' => 'งบรายจ่ายอื่น', 'pattern' => 'รายจ่ายอื่น']
];

// Build complete tab list
$allTabs = [];
foreach ($requiredTabs as $reqTab) {
    // Check if this tab exists in database data
    $found = false;
    foreach ($budgetTree as $dbTab) {
        $dbName = $dbTab['name_th'] ?? '';
        if (strpos($dbName, $reqTab['pattern']) !== false) {
            $allTabs[] = $dbTab;
            $found = true;
            break;
        }
    }
    // If not found, add placeholder
    if (!$found) {
        $placeholderId = 'placeholder_' . $reqTab['id'];
        $allTabs[] = [
            'id' => $placeholderId,
            'name_th' => $reqTab['name_th'],
            'is_placeholder' => true
        ];
        // Add to typeMeta
        $colors = [
            'งบลงทุน' => ['color' => 'purple', 'icon' => 'building'],
            'งบเงินอุดหนุน' => ['color' => 'amber', 'icon' => 'heart-handshake'],
            'งบรายจ่ายอื่น' => ['color' => 'rose', 'icon' => 'receipt']
        ];
        $typeMeta[$placeholderId] = $colors[$reqTab['name_th']] ?? ['color' => 'slate', 'icon' => 'folder'];
    }
}

// Replace budgetTree with complete list
$budgetTree = $allTabs;

// Get active tab (default to first)
$activeTabId = $budgetTree[0]['id'] ?? 1;

// Determine form action
$formAction = ($action === 'create') 
    ? \App\Core\View::url('/requests') 
    : \App\Core\View::url('/requests/' . $requestId . '/update');
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
        <div class="bg-gradient-to-r from-primary-900/30 to-slate-800/30 px-6 py-4 border-b border-slate-700">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-slate-100 flex items-center gap-2">
                        <i data-lucide="file-text" class="w-5 h-5 text-primary-400"></i>
                        บันทึกคำของบประมาณ
                    </h3>
                    <p class="text-sm text-slate-400 mt-1">
                        <span class="text-blue-400">แผนงานบุคลากรภาครัฐ</span> →
                        <span class="text-emerald-400">รายการค่าใช้จ่ายบุคลากรภาครัฐ</span> →
                        <span class="text-amber-400">รายการค่าใช้จ่ายบุคลากรภาครัฐ</span>
                    </p>
                </div>
                <a href="<?= \App\Core\View::url('/requests') ?>" 
                   class="px-3 py-1.5 text-slate-400 hover:text-slate-200 transition-colors flex items-center gap-1">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i> กลับ
                </a>
            </div>
        </div>

        <!-- Recording Info Bar -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 px-6 py-4 bg-slate-800/50 border-b border-slate-700">
            <div>
                <label class="block text-sm font-medium text-slate-400 mb-1">ปีงบประมาณ</label>
                <span class="text-lg font-semibold text-slate-100"><?= htmlspecialchars($fiscalYear) ?></span>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-400 mb-1">หน่วยงาน</label>
                <span class="text-lg font-semibold text-slate-100"><?= htmlspecialchars($organization['name_th'] ?? '-') ?></span>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-400 mb-1">สถานะ</label>
                <?php
                $statusLabels = [
                    'draft' => ['text' => 'ร่างคำขอ (Draft)', 'class' => 'text-slate-400'],
                    'saved' => ['text' => 'บันทึกแล้ว', 'class' => 'text-blue-400'],
                    'confirmed' => ['text' => '✓ รายการที่เลือก', 'class' => 'text-green-400'],
                    'pending' => ['text' => 'รอดำเนินการ', 'class' => 'text-amber-400'],
                    'approved' => ['text' => 'อนุมัติแล้ว', 'class' => 'text-emerald-400']
                ];
                $currentStatus = $request['request_status'] ?? 'draft';
                $config = $statusLabels[$currentStatus] ?? $statusLabels['draft'];
                ?>
                <span class="text-lg font-semibold <?= $config['class'] ?>"><?= $config['text'] ?></span>
            </div>
        </div>

        <!-- Tab Navigation -->
        <div class="border-b border-slate-700">
            <nav class="flex overflow-x-auto" id="expense-tabs">
                <?php 
                foreach ($budgetTree as $i => $tab): 
                    $tMeta = $typeMeta[$tab['id']] ?? ['color' => 'slate', 'icon' => 'circle'];
                    $isActive = ($i === 0);
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
                <button type="button"
                    class="tab-btn flex-1 min-w-max px-6 py-4 text-sm font-semibold flex items-center justify-center gap-2 transition-colors <?= $activeClass ?>"
                    data-tab-id="<?= $tab['id'] ?>"
                    data-tab-target="content-<?= $tab['id'] ?>"
                    data-color="<?= $tColor ?>">
                    <i data-lucide="<?= $tMeta['icon'] ?>" class="w-4 h-4"></i>
                    <?= htmlspecialchars($tab['name_th']) ?>
                    <span class="ml-2 px-2 py-0.5 rounded text-xs bg-slate-800 text-slate-500 tab-total-badge" data-tab="<?= $tab['id'] ?>">0.00</span>
                </button>
                <?php endforeach; ?>
            </nav>
        </div>

        <!-- Form -->
        <form action="<?= $formAction ?>" method="POST" id="mainForm">
            <?= \App\Core\View::csrf() ?>
            <input type="hidden" name="fiscal_year" value="<?= htmlspecialchars($fiscalYear) ?>">
            <input type="hidden" name="org_id" value="<?= htmlspecialchars($orgId) ?>">
            <input type="hidden" name="request_title" value="<?= htmlspecialchars($requestTitle) ?>">

            <!-- Tab Contents -->
            <?php foreach ($budgetTree as $i => $category): 
                $isActiveTab = ($i === 0);
                $catId = $category['id'];
                $meta = $typeMeta[$catId] ?? ['color' => 'slate', 'icon' => 'folder'];
                $color = $meta['color'];
                
                // Get tab name for fetching items
                $tabName = $category['name_th'] ?? '';
                
                // Fetch hierarchy from budget_category_items using tab name pattern
                // User Request: Filter by Organization to remove unrelated items
                // Redo: Pass saved item IDs so they are not filtered out even if not in whitelist
                $categoryItems = \App\Models\BudgetCategoryItem::getRootItemsForTab($tabName, $orgId, array_keys($savedItems));
                
                // Color styles
                $colorStyles = [
                    'blue' => ['bg' => 'bg-blue-900/20', 'border' => 'border-blue-800/30', 'text' => 'text-blue-300', 'icon' => 'text-blue-400'],
                    'emerald' => ['bg' => 'bg-emerald-900/20', 'border' => 'border-emerald-800/30', 'text' => 'text-emerald-300', 'icon' => 'text-emerald-400'],
                    'rose' => ['bg' => 'bg-rose-900/20', 'border' => 'border-rose-800/30', 'text' => 'text-rose-300', 'icon' => 'text-rose-400'],
                    'purple' => ['bg' => 'bg-purple-900/20', 'border' => 'border-purple-800/30', 'text' => 'text-purple-300', 'icon' => 'text-purple-400'],
                    'amber' => ['bg' => 'bg-amber-900/20', 'border' => 'border-amber-800/30', 'text' => 'text-amber-300', 'icon' => 'text-amber-400']
                ];
                
                $bgClass = $colorStyles[$color]['bg'] ?? 'bg-slate-800';
                $borderClass = $colorStyles[$color]['border'] ?? 'border-slate-700';
                $textClass = $colorStyles[$color]['text'] ?? 'text-slate-300';
                $iconClass = $colorStyles[$color]['icon'] ?? 'text-slate-400';
            ?>
            
            <div id="content-<?= $catId ?>" class="tab-content p-6 <?= $isActiveTab ? '' : 'hidden' ?>">
                
                <!-- Summary Bar -->
                <div class="flex items-center justify-between mb-4 p-4 <?= $bgClass ?> border <?= $borderClass ?> rounded-lg">
                    <div class="flex items-center gap-3">
                        <i data-lucide="<?= $meta['icon'] ?>" class="w-6 h-6 <?= $iconClass ?>"></i>
                        <span class="text-lg font-semibold <?= $textClass ?>"><?= htmlspecialchars($category['name_th']) ?></span>
                    </div>
                    <div class="flex items-center gap-6 text-sm">
                        <span class="text-slate-400">วงเงินรวม: <span class="text-slate-100 font-semibold tab-summary" data-tab="<?= $catId ?>">0.00</span> บาท</span>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="overflow-x-auto rounded-lg border border-slate-700">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-800/60 text-slate-400 text-xs uppercase tracking-wider border-b border-slate-700/50">
                                <th class="px-4 py-3 text-left w-[40%]">รายการ</th>
                                <th class="px-4 py-3 text-center w-[12%]">จำนวน (คน)</th>
                                <th class="px-4 py-3 text-right w-[15%]">ราคาต่อหน่วย</th>
                                <th class="px-4 py-3 text-right w-[15%] <?= $iconClass ?>">วงเงิน (บาท)</th>
                                <th class="px-4 py-3 text-left w-[18%]">หมายเหตุ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700/30">
                            <?php 
                            // Recursive rendering function
                            $renderItems = function($items, $level) use (&$renderItems, $iconClass, $catId, $savedItems) {
                                // If no items, show empty message
                                if (empty($items) && $level === 0) {
                                    echo '<tr><td colspan="5" class="py-8 text-center text-slate-500 italic">ไม่พบรายการในหมวดนี้</td></tr>';
                                    return;
                                }

                                foreach ($items as $item): 
                                    $hasChildren = !empty($item['children']);
                                    $itemId = $item['id'];
                                    
                                    // Retrieve saved values
                                    $savedItem = $savedItems[$itemId] ?? [];
                                    $qtyVal = $savedItem['quantity'] ?? 0;
                                    $unitPriceVal = $savedItem['unit_price'] ?? 0;
                                    $amountVal = $savedItem['amount'] ?? 0; // New: use separate amount column
                                    
                                    // Display values directly from DB
                                    $displayQty = $qtyVal > 0 ? $qtyVal : '';
                                    $displayUnitPrice = $unitPriceVal > 0 ? $unitPriceVal : '';
                                    $displayAmount = $amountVal > 0 ? $amountVal : '';
                                    $noteVal = $savedItem['remark'] ?? '';

                                    $paddingLeft = ($level * 16) + 16 . 'px';
                                    $rowClass = $hasChildren ? "font-medium text-slate-200" : "text-slate-300";
                                    $rowStyle = ($level > 0) ? 'display: none;' : '';
                                    $isParentRow = $hasChildren ? 'parent-row' : '';
                            ?>
                            <tr class="hover:bg-slate-800/30 transition-colors item-row <?= $isParentRow ?>" 
                                style="<?= $rowStyle ?>" 
                                data-id="<?= $itemId ?>" 
                                data-parent="<?= $item['parent_id'] ?? '' ?>" 
                                data-has-children="<?= $hasChildren ? '1' : '0' ?>"
                                data-category="<?= $catId ?>">
                                <td class="py-2 pr-4">
                                    <div style="padding-left: <?= $paddingLeft ?>" class="flex items-center gap-2 <?= $rowClass ?>">
                                        <?php if ($hasChildren): ?>
                                            <button type="button" class="toggle-children text-slate-500 hover:text-slate-300" data-target="<?= $itemId ?>">
                                                <i data-lucide="chevron-down" class="w-4 h-4"></i>
                                            </button>
                                        <?php endif; ?>
                                        
                                        <!-- User Request: Show Folder Icon for Groups -->
                                        <?php if (!empty($item['icon'])): ?>
                                            <i data-lucide="<?= $item['icon'] ?>" class="w-4 h-4 <?= $iconClass ?>"></i>
                                        <?php endif; ?>
                                        
                                        <?= htmlspecialchars($item['name'] ?? $item['name_th'] ?? '') ?>
                                    </div>
                                </td>
                                <td class="p-1 text-center">
                                    <?php if (!$hasChildren): ?>
                                        <input type="text" inputmode="numeric" 
                                               class="w-full px-2 py-1 bg-slate-700/50 border border-transparent hover:border-slate-600 focus:border-primary-500 rounded text-center text-slate-100 text-sm focus:outline-none focus:ring-1 focus:ring-primary-500/50 transition-all inp-quantity"
                                               name="items[<?= $itemId ?>][quantity]" value="<?= $displayQty ?>" placeholder="0">
                                    <?php else: ?>
                                        <input type="text" disabled readonly
                                               class="w-full px-2 py-1 bg-slate-700/30 border border-transparent rounded text-center text-slate-300 text-sm disabled:opacity-100 disabled:cursor-not-allowed inp-parent-qty"
                                               value="" placeholder="-">
                                    <?php endif; ?>
                                </td>
                                <td class="p-1">
                                    <?php if (!$hasChildren): ?>
                                        <input type="text" inputmode="decimal" 
                                               class="w-full px-2 py-1 bg-slate-700/50 border border-transparent hover:border-slate-600 focus:border-primary-500 rounded text-right text-slate-100 text-sm focus:outline-none focus:ring-1 focus:ring-primary-500/50 transition-all inp-unit-price"
                                               name="items[<?= $itemId ?>][unit_price]" value="<?= $displayUnitPrice ?>" placeholder="0.00">
                                    <?php else: ?>
                                        <input type="text" disabled readonly
                                               class="w-full px-2 py-1 bg-slate-700/30 border border-transparent rounded text-right text-slate-300 text-sm disabled:opacity-100 disabled:cursor-not-allowed inp-parent-price"
                                               value="" placeholder="-">
                                    <?php endif; ?>
                                </td>
                                <td class="p-1">
                                    <input type="text" inputmode="decimal" <?= $hasChildren ? 'disabled' : '' ?>
                                           class="w-full px-2 py-1 bg-slate-700/50 border border-transparent hover:border-slate-600 focus:border-primary-500 rounded text-right font-medium text-sm focus:outline-none focus:ring-1 focus:ring-primary-500/50 transition-all inp-amount <?= $hasChildren ? 'text-amber-400 disabled:opacity-100 disabled:cursor-not-allowed' : 'text-orange-400' ?>"
                                           name="items[<?= $itemId ?>][amount]" value="<?= $displayAmount ?>" placeholder="0.00">
                                </td>
                                <td class="p-1">
                                    <?php if (!$hasChildren): ?>
                                        <input type="text"
                                               class="w-full px-2 py-1 bg-slate-700/50 border border-transparent hover:border-slate-600 focus:border-primary-500 rounded text-slate-300 text-sm focus:outline-none focus:ring-1 focus:ring-primary-500/50 transition-all"
                                               name="items[<?= $itemId ?>][note]" value="<?= htmlspecialchars($noteVal) ?>" placeholder="...">
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php
                                if ($hasChildren) {
                                    $renderItems($item['children'], $level + 1);
                                }
                                endforeach;
                            };

                            $renderItems($categoryItems, 0);
                            ?>
                        </tbody>
                        <tfoot>
                            <tr class="bg-slate-900/80 border-t-2 border-slate-600">
                                <td class="px-4 py-3 text-center font-bold text-slate-200">รวมทั้งสิ้น (Total)</td>
                                <td class="px-4 py-3 text-center font-bold text-amber-400" id="footer-qty-<?= $catId ?>">0</td>
                                <td class="px-4 py-3"></td>
                                <td class="px-4 py-3 text-right font-bold text-orange-400" id="footer-amount-<?= $catId ?>">0.00</td>
                                <td class="px-4 py-3"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Action Buttons (Inside Container) -->
                <div class="flex justify-between items-center gap-3 mt-6">
                    <a href="<?= \App\Core\View::url('/requests') ?>" 
                       class="px-5 py-2.5 bg-slate-700 text-slate-200 rounded-lg font-medium hover:bg-slate-600 transition-colors flex items-center gap-2">
                        <i data-lucide="arrow-left" class="w-4 h-4"></i> กลับ
                    </a>
                    
                    <?php if (empty($readonly)): ?>
                    <div class="flex gap-3">
                        <button type="button" class="btn-clear-form px-5 py-2.5 bg-slate-600 text-slate-200 rounded-lg font-medium hover:bg-slate-500 transition-colors flex items-center gap-2">
                            <i data-lucide="rotate-ccw" class="w-4 h-4"></i> ล้างค่า
                        </button>
                        
                        <?php 
                        $currentStatus = $request['request_status'] ?? 'draft';
                        if ($currentStatus === 'confirmed' || $currentStatus === 'approved' || $currentStatus === 'pending'): 
                        ?>
                            <div class="flex items-center gap-3">
                                <span class="text-green-400 text-sm font-medium flex items-center gap-1">
                                    <i data-lucide="check" class="w-4 h-4"></i> ยืนยันแล้ว
                                </span>
                                <?php if (\App\Core\Auth::hasRole('admin') || true): ?>
                                   <a href="<?= \App\Core\View::url('/requests/' . $requestId . '/revoke') ?>" 
                                      class="px-5 py-2.5 bg-amber-600/20 text-amber-400 rounded-lg font-medium hover:bg-amber-600/30 transition-colors flex items-center gap-2">
                                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i> ยกเลิกการยืนยัน
                                </a>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <button type="submit" class="btn-save px-6 py-2.5 bg-sky-600 text-white rounded-lg font-medium shadow-lg hover:bg-sky-500 transition-colors flex items-center gap-2">
                                <i data-lucide="save" class="w-4 h-4"></i> บันทึก
                            </button>

                            <button type="button" class="btn-confirm-selection px-6 py-2.5 bg-emerald-600 text-white rounded-lg font-medium shadow-lg shadow-emerald-900/30 hover:bg-emerald-500 transition-colors flex items-center gap-2">
                                <i data-lucide="check-circle" class="w-4 h-4"></i> ยืนยัน
                            </button>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php endforeach; ?>

            <!-- Action Footer Removed (Moved inside tabs) -->
            <!-- Hidden JSON input for large forms -->
            <input type="hidden" name="items_json" id="items_json">
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('mainForm');
    
    // Tab Switching
    const tabStyles = {
        'blue': 'border-b-[3px] border-blue-400 text-blue-300 bg-gradient-to-t from-blue-900/10 to-transparent',
        'emerald': 'border-b-[3px] border-emerald-400 text-emerald-300 bg-gradient-to-t from-emerald-900/10 to-transparent',
        'purple': 'border-b-[3px] border-purple-400 text-purple-300 bg-gradient-to-t from-purple-900/10 to-transparent',
        'amber': 'border-b-[3px] border-amber-400 text-amber-300 bg-gradient-to-t from-amber-900/10 to-transparent',
        'rose': 'border-b-[3px] border-rose-400 text-rose-300 bg-gradient-to-t from-rose-900/10 to-transparent',
        'inactive': 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-200'
    };

    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('data-tab-target');
            const targetContent = document.getElementById(targetId);
            
            if (!targetContent) {
                console.error('Target content not found:', targetId);
                return;
            }
            
            // 1. Reset ALL tabs to inactive state
            document.querySelectorAll('.tab-btn').forEach(b => {
                b.className = 'tab-btn flex-1 min-w-max px-6 py-4 text-sm font-semibold flex items-center justify-center gap-2 transition-colors ' + tabStyles.inactive;
            });
            
            // 2. Hide ALL contents
            document.querySelectorAll('.tab-content').forEach(c => {
                c.classList.add('hidden');
                c.style.display = 'none'; 
            });
            
            // 3. Set Current Tab Active
            const color = this.dataset.color || 'blue'; // Use robust data attribute
            const activeStyle = tabStyles[color];
            
            this.className = 'tab-btn flex-1 min-w-max px-6 py-4 text-sm font-semibold flex items-center justify-center gap-2 transition-colors ' + activeStyle;
            
            // 4. Show Target Content
            targetContent.classList.remove('hidden');
            targetContent.style.display = 'block';

            // 5. Auto-save: Prepare JSON before switching tabs (prevents data loss)
            if (typeof prepareJSON === 'function') {
                prepareJSON();
            }

            // 6. Update Totals to ensure bottom footer reflects active tab
            updateTabTotals();
        });
    });
    
    // Calculation Logic
    function formatNumber(num) {
        return num.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }
    
    // Format input value with commas (for display)
    function formatInputWithCommas(value) {
        if (value === null || value === undefined || value === '') return '';
        // Convert to string, strip commas
        let strVal = String(value).replace(/,/g, '');
        const num = parseFloat(strVal);
        if (isNaN(num)) return '';
        // Always 2 decimal places
        return num.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }
    
    // Get numeric value from input (removes commas)
    function getVal(el) {
        if (!el) return 0;
        let strVal = String(el.value).replace(/,/g, '');
        // Validation: Prevent negative numbers
        return Math.max(0, parseFloat(strVal) || 0);
    }
    
    // Format input on blur
    function formatOnBlur(input) {
        if (!input || input.disabled) return;
        
        const originalVal = input.value.trim();
        if (originalVal === '') return; // Leave empty as empty
        
        const numVal = getVal(input);
        
        // Safety Clean: If getVal returns 0, ONLY format if original input was actually "0" or "0.00"
        // This prevents "abc" or formatting errors from becoming "0.00"
        if (numVal === 0) {
             if (originalVal === '0' || originalVal === '0.00') {
                 input.value = '0.00';
             }
             // Else: leave it alone (e.g. user typed text or had error)
             return;
        }
        
        // Normal case: > 0 or < 0
        input.value = formatInputWithCommas(numVal);
    }

    function updateRow(tr) {
        if (!tr) return;
        
        // Parse current values
        const qty = getVal(tr.querySelector('.inp-quantity'));
        const unitPrice = getVal(tr.querySelector('.inp-unit-price'));
        const amountInput = tr.querySelector('.inp-amount');
        
        // Auto-calculate only if valid inputs exist for BOTH
        if (amountInput && !amountInput.disabled) {
            if (qty > 0 && unitPrice > 0) {
                const calculated = qty * unitPrice;
                amountInput.value = formatInputWithCommas(calculated);
            }
        }
    }

    function updateParentTotals(parentId) {
        const parentRow = document.querySelector(`tr.parent-row[data-id="${parentId}"]`);
        if (!parentRow) return;
        
        const children = document.querySelectorAll(`tr[data-parent="${parentId}"]`);
        let sumAmount = 0;
        let sumQty = 0;
        let sumPrice = 0;
        
        children.forEach(childRow => {
            const isChildParent = childRow.classList.contains('parent-row');
            const amountInput = childRow.querySelector('.inp-amount');
            const qtyInput = childRow.querySelector('.inp-quantity');
            const priceInput = childRow.querySelector('.inp-unit-price');
            
            sumAmount += getVal(amountInput);
            
            // Sum qty and price from child rows
            if (qtyInput) {
                sumQty += getVal(qtyInput);
            } else {
                // Child is also a parent - get from disabled input
                const qtyParentInput = childRow.querySelector('.inp-parent-qty');
                if (qtyParentInput && qtyParentInput.value) {
                    sumQty += parseFloat(qtyParentInput.value.replace(/,/g, '')) || 0;
                }
            }
            
            if (priceInput) {
                sumPrice += getVal(priceInput);
            } else {
                const priceParentInput = childRow.querySelector('.inp-parent-price');
                if (priceParentInput && priceParentInput.value) {
                    sumPrice += parseFloat(priceParentInput.value.replace(/,/g, '')) || 0;
                }
            }
        });
        
        // Update parent row displays
        const parentAmountInput = parentRow.querySelector('.inp-amount');
        if (parentAmountInput) {
            // FIX: Use formatNumber to include commas
            parentAmountInput.value = formatNumber(sumAmount);
        }
        
        // Update parent qty input
        const parentQtyInput = parentRow.querySelector('.inp-parent-qty');
        if (parentQtyInput) {
            // FIX: Use toLocaleString for commas in quantity
            parentQtyInput.value = sumQty.toLocaleString('en-US');
        }
        
        // Update parent price input
        const parentPriceInput = parentRow.querySelector('.inp-parent-price');
        if (parentPriceInput) {
            // Already uses formatNumber, good
            parentPriceInput.value = formatNumber(sumPrice);
        }
    }
    
    function updateAllParentTotals() {
        const parentRows = Array.from(document.querySelectorAll('tr.parent-row'));
        
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
        
        rowDepths.sort((a, b) => b.depth - a.depth);
        rowDepths.forEach(({row}) => {
            updateParentTotals(row.dataset.id);
        });
    }

    function updateTabTotals() {
        let activeTabTotal = 0;
        
        document.querySelectorAll('.tab-summary').forEach(summary => {
            const tabId = summary.dataset.tab;
            let currentTabTotal = 0;
            let currentTabQty = 0;
            
            const container = document.getElementById(`content-${tabId}`);
            if (container) {
                // Selector: Sum ALL leaf item amounts and quantities in this tab
                const leafRows = container.querySelectorAll('tr.item-row:not(.parent-row)');
                
                leafRows.forEach(row => {
                    const amtInput = row.querySelector('.inp-amount');
                    const qtyInput = row.querySelector('.inp-quantity');
                    if (amtInput) currentTabTotal += getVal(amtInput);
                    if (qtyInput) currentTabQty += getVal(qtyInput);
                });

                // Update tfoot fields for each tab
                const fQty = document.getElementById(`footer-qty-${tabId}`);
                const fAmount = document.getElementById(`footer-amount-${tabId}`);
                if (fQty) fQty.textContent = currentTabQty.toLocaleString('en-US');
                if (fAmount) fAmount.textContent = formatNumber(currentTabTotal);

                // Check if this tab is currently visible
                if (container.offsetParent !== null || !container.classList.contains('hidden')) {
                    activeTabTotal = currentTabTotal;
                }
            }
            
            summary.textContent = formatNumber(currentTabTotal);
            
            // Update badge
            const badge = document.querySelector(`.tab-total-badge[data-tab="${tabId}"]`);
            if (badge) badge.textContent = formatNumber(currentTabTotal);
        });
        
        // Update any remaining total displays if they exist
        const grandTotalEl = document.getElementById('grand-total');

        if (grandTotalEl) grandTotalEl.textContent = formatNumber(activeTabTotal);
        
        // Update badge
        document.querySelectorAll('.tab-total-badge').forEach(badge => {
            const tId = badge.dataset.tab;
            const summary = document.querySelector(`.tab-summary[data-tab="${tId}"]`);
            if (summary) badge.textContent = summary.textContent;
        });
    }

    // Input change handlers
    if (form) {
        form.addEventListener('input', function(e) {
            if (e.target.matches('.inp-quantity, .inp-unit-price')) {
                updateRow(e.target.closest('tr'));
                updateAllParentTotals();
                updateTabTotals();
            } else if (e.target.matches('.inp-amount') && !e.target.disabled) {
                updateAllParentTotals();
                updateTabTotals();
            }
        });
        
        // Format numbers with commas on blur
        form.addEventListener('blur', function(e) {
            // Re-enabled .inp-amount now that it is type="text"
            if (e.target.matches('.inp-quantity, .inp-unit-price, .inp-amount')) {
                formatOnBlur(e.target);
            }
        }, true); // Use capture phase for blur
        
        // Handle Form Submit preventing max_input_vars limit
        // Handle Form Submit preventing max_input_vars limit
        const prepareJSON = function(e) {
            try {
                const items = {};
                let count = 0;
                document.querySelectorAll('tr.item-row').forEach(row => {
                    const id = row.dataset.id;
                    const qtyInput = row.querySelector('.inp-quantity');
                    const priceInput = row.querySelector('.inp-unit-price');
                    const amountInput = row.querySelector('.inp-amount');
                    const noteInput = row.querySelector('input[name*="[note]"]');
                    
                    if (amountInput && !amountInput.disabled) {
                       // Strip commas before sending to server
                       items[id] = {
                           quantity: qtyInput ? String(qtyInput.value).replace(/,/g, '') : 0,
                           unit_price: priceInput ? String(priceInput.value).replace(/,/g, '') : 0,
                           amount: String(amountInput.value).replace(/,/g, ''),
                           note: noteInput ? noteInput.value : ''
                       };
                       count++;
                    }
                });
                
                const hidden = document.getElementById('items_json');
                if (hidden) {
                    const json = JSON.stringify(items);
                    hidden.value = json;
                    // Removed alerts to prevent race conditions during form submission
                } else {
                    console.error("Critical Error: items_json input missing!");
                    e.preventDefault();
                }
            } catch (err) {
                alert("JS Error: " + err.message);
                e.preventDefault();
            }
        };

        form.addEventListener('submit', prepareJSON);
        
        // Also attach to all Save Buttons (multiple instances in tabs)
        document.querySelectorAll('.btn-save').forEach(btn => {
            btn.addEventListener('click', prepareJSON);
        });
    }

    // Clear Form Button Handler (Using Custom Modal)
    document.querySelectorAll('.btn-clear-form').forEach(clearBtn => {
        clearBtn.addEventListener('click', function() {
            // Robustly find active tab
            const activeTabContent = Array.from(document.querySelectorAll('.tab-content')).find(el => el.offsetParent !== null || !el.classList.contains('hidden'));
            const tabName = activeTabContent ? (activeTabContent.querySelector('.text-lg')?.textContent?.trim() || 'ปัจจุบัน') : 'ปัจจุบัน';
            
            // Get Tab Total
            const tabTotal = activeTabContent ? (activeTabContent.querySelector('.tab-summary')?.textContent || '0.00') : '0.00';

            // Use Custom Modal for Clear confirmation
            if (typeof Modal !== 'undefined') {
                Modal.show({
                    title: `ล้างค่า "${tabName}"?`,
                    message: 'ข้อมูลในแท็บนี้จะถูกลบออก (ยังไม่บันทึก)',
                    total: tabTotal,
                    variant: 'warning',
                    buttonText: 'ล้างค่า',
                    onConfirm: function() {
                        if (activeTabContent) {
                            activeTabContent.querySelectorAll('.inp-quantity, .inp-unit-price, .inp-amount:not(:disabled)').forEach(input => {
                                input.value = '';
                                input.dispatchEvent(new Event('input', { bubbles: true }));
                            });
                            updateTabTotals(); 
                        }
                    }
                });
            } else {
                // Fallback to native confirm
                if (confirm('ต้องการล้างค่าในแท็บปัจจุบันใช่หรือไม่?')) {
                     const activeTabContent = document.querySelector('.tab-content:not(.hidden)');
                     if (activeTabContent) {
                         activeTabContent.querySelectorAll('.inp-quantity, .inp-unit-price, .inp-amount:not(:disabled)').forEach(input => {
                             input.value = '';
                             input.dispatchEvent(new Event('input', { bubbles: true }));
                         });
                         updateTabTotals(); 
                     }
                }
            }
        });
    });

    // Confirm Selection Modal Handler (Using Custom Modal)
    document.querySelectorAll('.btn-confirm-selection').forEach(confirmBtn => {
        confirmBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Get active tab total
            const activeTabContent = Array.from(document.querySelectorAll('.tab-content')).find(el => el.offsetParent !== null || !el.classList.contains('hidden'));
            const grandTotal = activeTabContent ? (activeTabContent.querySelector('.tab-summary')?.textContent || '0.00') : '0.00';
            
            // Show Custom Modal
            if (typeof Modal !== 'undefined') {
                Modal.show({
                    title: 'ยืนยันการเลือกรายการนี้?',
                    message: 'รายการที่ยืนยันแล้วจะถูกล็อค ห้ามแก้ไขจนกว่าจะยกเลิกการยืนยัน',
                    total: grandTotal,
                    variant: 'confirm',
                    buttonText: 'ยืนยันการเลือก',
                    onConfirm: function() {
                        // Create and submit a form to the confirm route
                        const f = document.createElement('form');
                        f.method = 'POST';
                        f.action = '<?= \App\Core\View::url("/requests/" . $requestId . "/confirm") ?>';
                        
                        const csrfInput = document.createElement('input');
                        csrfInput.type = 'hidden';
                        csrfInput.name = 'csrf_token';
                        csrfInput.value = '<?= $_SESSION["csrf_token"] ?? "" ?>';
                        
                        f.appendChild(csrfInput);
                        document.body.appendChild(f);
                        f.submit();
                    }
                });
            } else {
                // Fallback if modal.js not loaded
                if(confirm('ยืนยันการเลือกรายการนี้? (Total: ' + grandTotal + ')')) {
                     const f = document.createElement('form');
                        f.method = 'POST';
                        f.action = '<?= \App\Core\View::url("/requests/" . $requestId . "/confirm") ?>';
                        document.body.appendChild(f);
                        f.submit();
                }
            }
        });
    });

    // Collapse/Expand Logic
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.toggle-children');
        if (!btn) return;
        
        e.preventDefault();
        e.stopPropagation();
        
        const targetId = btn.dataset.target;
        const isExpanded = btn.dataset.expanded === 'true';
        const newState = !isExpanded;
        
        btn.dataset.expanded = newState;
        
        const icon = btn.querySelector('svg') || btn.querySelector('i');
        if (icon) {
            icon.style.transition = 'transform 0.2s ease-in-out';
            icon.style.transform = newState ? 'rotate(0deg)' : 'rotate(-90deg)';
        }
        
        toggleChildRows(targetId, newState);
    });

    function toggleChildRows(parentId, show) {
        document.querySelectorAll(`tr[data-parent="${parentId}"]`).forEach(row => {
            row.style.display = show ? '' : 'none';
            const childId = row.dataset.id;
            
            if (!show) {
                if (row.dataset.hasChildren === '1') {
                    toggleChildRows(childId, false);
                }
            } else {
                if (row.dataset.hasChildren === '1') {
                    const btn = row.querySelector('.toggle-children');
                    const isChildExpanded = btn && btn.dataset.expanded === 'true';
                    
                    if (isChildExpanded) {
                        toggleChildRows(childId, true);
                    }
                }
            }
        });
    }

    // Prevent mouse wheel from changing number input values
    document.addEventListener('wheel', function(e) {
        const activeEl = document.activeElement;
        // Check for number type OR text inputs with numeric classes
        if (activeEl && (
            activeEl.type === 'number' || 
            activeEl.classList.contains('inp-quantity') ||
            activeEl.classList.contains('inp-unit-price') ||
            activeEl.classList.contains('inp-amount')
        )) {
            e.preventDefault();
            activeEl.blur();
        }
    }, { passive: false });

    // Initialize Icons and Calculation on Load
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
        
        setTimeout(() => {
            document.querySelectorAll('.toggle-children').forEach(btn => {
                // Initialize state based on HTML or force false
                if (!btn.dataset.expanded) btn.dataset.expanded = 'false';

                const svg = btn.querySelector('svg') || btn.querySelector('i');
                if (svg) {
                    svg.style.transition = 'transform 0.2s ease-in-out';
                    if (btn.dataset.expanded === 'false') {
                        svg.style.transform = 'rotate(-90deg)';
                    } else {
                        svg.style.transform = 'rotate(0deg)';
                    }
                }
            });
            
            // Initial Calculation to populate totals from loaded values
            updateAllParentTotals();
            updateTabTotals();
            
            // Format existing values with commas on page load
            document.querySelectorAll('.inp-quantity, .inp-unit-price, .inp-amount').forEach(input => {
                formatOnBlur(input);
            });
        }, 50);
    }
});
</script>
