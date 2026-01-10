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
                <span class="text-lg font-semibold text-primary-400">
                    <?= isset($request['request_status']) && $request['request_status'] === 'draft' ? 'ร่างคำขอ (Draft)' : ($request['request_status'] ?? 'Draft') ?>
                </span>
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
                    data-tab-target="content-<?= $tab['id'] ?>">
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
                $categoryItems = \App\Models\BudgetCategoryItem::getRootItemsForTab($tabName);
                
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
                                foreach ($items as $item): 
                                    $hasChildren = !empty($item['children']);
                                    $itemId = $item['id'];
                                    
                                    // Retrieve saved values
                                    $savedItem = $savedItems[$itemId] ?? [];
                                    $qtyVal = $savedItem['quantity'] ?? 0;
                                    $unitPriceVal = $savedItem['unit_price'] ?? 0;
                                    
                                    // Logic: If Qty=0 and UnitPrice>0, it means the Amount was stored in unit_price (Direct Amount)
                                    // If Qty>0, then UnitPrice is the actual Unit Price.
                                    $displayQty = $qtyVal > 0 ? $qtyVal : '';
                                    
                                    if ($qtyVal > 0) {
                                        $displayUnitPrice = $unitPriceVal;
                                        $displayAmount = $qtyVal * $unitPriceVal;
                                    } else {
                                        // Direct Amount Mode
                                        $displayUnitPrice = ''; // Hide Unit Price if it was holding the Amount
                                        $displayAmount = $unitPriceVal; // Show the stored value as Amount
                                    }
                                    
                                    // Format if > 0
                                    $displayUnitPrice = ($displayUnitPrice > 0) ? $displayUnitPrice : '';
                                    $displayAmount = ($displayAmount > 0) ? $displayAmount : '';
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
                                    <div style="padding-left: <?= $paddingLeft ?>" class="flex items-center <?= $rowClass ?>">
                                        <?php if ($hasChildren): ?>
                                            <button type="button" class="toggle-children mr-2 text-slate-500 hover:text-slate-300" data-target="<?= $itemId ?>">
                                                <i data-lucide="chevron-down" class="w-4 h-4"></i>
                                            </button>
                                        <?php endif; ?>
                                        <?= htmlspecialchars($item['name'] ?? $item['name_th'] ?? '') ?>
                                    </div>
                                </td>
                                <td class="p-1 text-center">
                                    <?php if (!$hasChildren): ?>
                                        <input type="number" step="1" min="0" 
                                               class="w-full px-2 py-1 bg-slate-700/50 border border-transparent hover:border-slate-600 focus:border-primary-500 rounded text-center text-slate-100 text-sm focus:outline-none focus:ring-1 focus:ring-primary-500/50 transition-all inp-quantity"
                                               name="items[<?= $itemId ?>][quantity]" value="<?= $displayQty ?>" placeholder="0">
                                    <?php else: ?>
                                        <input type="text" disabled readonly
                                               class="w-full px-2 py-1 bg-slate-700/30 border border-transparent rounded text-center text-slate-300 text-sm disabled:opacity-100 disabled:cursor-default inp-parent-qty"
                                               value="" placeholder="-">
                                    <?php endif; ?>
                                </td>
                                <td class="p-1">
                                    <?php if (!$hasChildren): ?>
                                        <input type="number" step="0.01" min="0" 
                                               class="w-full px-2 py-1 bg-slate-700/50 border border-transparent hover:border-slate-600 focus:border-primary-500 rounded text-right text-slate-100 text-sm focus:outline-none focus:ring-1 focus:ring-primary-500/50 transition-all inp-unit-price"
                                               name="items[<?= $itemId ?>][unit_price]" value="<?= $displayUnitPrice ?>" placeholder="0.00">
                                    <?php else: ?>
                                        <input type="text" disabled readonly
                                               class="w-full px-2 py-1 bg-slate-700/30 border border-transparent rounded text-right text-slate-300 text-sm disabled:opacity-100 disabled:cursor-default inp-parent-price"
                                               value="" placeholder="-">
                                    <?php endif; ?>
                                </td>
                                <td class="p-1">
                                    <input type="number" step="0.01" min="0" <?= $hasChildren ? 'disabled' : '' ?>
                                           class="w-full px-2 py-1 bg-slate-700/50 border border-transparent hover:border-slate-600 focus:border-primary-500 rounded text-right font-medium text-sm focus:outline-none focus:ring-1 focus:ring-primary-500/50 transition-all inp-amount <?= $hasChildren ? 'text-amber-400 disabled:opacity-100 disabled:cursor-default' : 'text-orange-400' ?>"
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
                    </table>
                </div>
            </div>
            
            <?php endforeach; ?>

            <!-- Grand Total Footer -->
            <div class="bg-slate-700/60 px-6 py-4 border-t border-slate-600">
                <div class="flex items-center justify-between">
                    <span class="text-lg font-bold text-slate-200">รวมทั้งสิ้น (Total)</span>
                    <div class="flex items-center gap-8 text-sm">
                        <div class="text-center">
                            <div class="text-slate-400 text-xs">วงเงินทั้งหมด</div>
                            <div class="text-2xl font-bold text-amber-400" id="grand-total">0.00</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Footer -->
            <div class="px-6 py-4 bg-slate-800/30 border-t border-slate-700 flex justify-between gap-3">
                <a href="<?= \App\Core\View::url('/requests') ?>" 
                   class="px-5 py-2.5 bg-slate-700 text-slate-200 rounded-lg font-medium border border-slate-600 hover:bg-slate-600 transition-colors flex items-center gap-2">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i> กลับหน้าหลัก
                </a>
                
                <div class="flex gap-3">
                    <button type="button" id="clearFormBtn"
                       class="px-5 py-2.5 bg-slate-600 text-slate-200 rounded-lg font-medium border border-slate-500 hover:bg-slate-500 transition-colors flex items-center gap-2">
                        <i data-lucide="eraser" class="w-4 h-4"></i> ล้างข้อมูล
                    </button>
                    <button type="submit" class="px-6 py-2.5 bg-primary-600 text-white rounded-lg font-medium shadow-lg shadow-primary-900/30 hover:bg-primary-500 transition-colors flex items-center gap-2">
                        <i data-lucide="save" class="w-4 h-4"></i> บันทึกคำขอ
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('mainForm');
    
    // Tab Switching
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('data-tab-target');
            const targetContent = document.getElementById(targetId);
            
            if (!targetContent) {
                console.error('Target content not found:', targetId);
                return;
            }
            
            // Update tab buttons
            document.querySelectorAll('.tab-btn').forEach(b => {
                b.className = b.className.replace(/border-b-\[3px\] border-\w+-\d+ text-\w+-\d+ bg-gradient.*?transparent/g, '');
                if (!b.className.includes('text-slate-400')) {
                    b.className += ' text-slate-400 hover:bg-slate-800/50 hover:text-slate-200';
                }
            });
            
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(c => {
                c.classList.add('hidden');
                c.style.display = 'none'; // Ensure display none
            });
            
            // Show active button style
            this.className = this.className.replace('text-slate-400 hover:bg-slate-800/50 hover:text-slate-200', '');
            const color = this.querySelector('i').dataset.lucide === 'users' ? 'blue' :
                         this.querySelector('i').dataset.lucide === 'briefcase' ? 'emerald' :
                         this.querySelector('i').dataset.lucide === 'building' ? 'purple' :
                         this.querySelector('i').dataset.lucide === 'heart-handshake' ? 'amber' : 'rose';
            this.className += ` border-b-[3px] border-${color}-400 text-${color}-300 bg-gradient-to-t from-${color}-900/10 to-transparent`;
            
            // Show target content
            targetContent.classList.remove('hidden');
            targetContent.style.display = 'block'; // Force display block
        });
    });
    
    // Calculation Logic
    function formatNumber(num) {
        return num.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    function getVal(el) {
        return parseFloat(el?.value || 0);
    }

    function updateRow(tr) {
        if (!tr) return;
        
        const qty = getVal(tr.querySelector('.inp-quantity'));
        const unitPrice = getVal(tr.querySelector('.inp-unit-price'));
        const amountInput = tr.querySelector('.inp-amount');
        
        if (amountInput && !amountInput.disabled) {
            // Only auto-calculate if unit_price is provided and Qty > 0
            if (unitPrice > 0 && qty > 0) {
                // Auto-calculate: Quantity × Unit Price
                const calculated = qty * unitPrice;
                amountInput.value = calculated > 0 ? calculated.toFixed(2) : '';
            }
            // If unit_price is empty/0, preserve user's manual amount input (Direct Mode)
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
            parentAmountInput.value = sumAmount.toFixed(2);
        }
        
        // Update parent qty input
        const parentQtyInput = parentRow.querySelector('.inp-parent-qty');
        if (parentQtyInput) {
            parentQtyInput.value = Math.round(sumQty);
        }
        
        // Update parent price input
        const parentPriceInput = parentRow.querySelector('.inp-parent-price');
        if (parentPriceInput) {
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
        // Update each tab summary
        let grandTotal = 0;
        
        document.querySelectorAll('.tab-summary').forEach(summary => {
            const tabId = summary.dataset.tab;
            let total = 0;
            
            document.querySelectorAll(`tr[data-category="${tabId}"]:not(.parent-row)`).forEach(row => {
                total += getVal(row.querySelector('.inp-amount'));
            });
            
            summary.textContent = formatNumber(total);
            grandTotal += total;
            
            // Update badge
            const badge = document.querySelector(`.tab-total-badge[data-tab="${tabId}"]`);
            if (badge) badge.textContent = formatNumber(total);
        });
        
        document.getElementById('grand-total').textContent = formatNumber(grandTotal);
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
    }

    // Clear Form Button Handler
    const clearBtn = document.getElementById('clearFormBtn');
    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            if (confirm('ต้องการล้างข้อมูลทั้งหมดใช่หรือไม่?')) {
                // Clear all input fields
                document.querySelectorAll('.inp-quantity, .inp-unit-price, .inp-amount:not(:disabled)').forEach(input => {
                    input.value = '';
                });
                
                // Clear parent amounts
                document.querySelectorAll('.inp-amount:disabled').forEach(input => {
                    input.value = '';
                });
                
                // Reset parent inputs
                document.querySelectorAll('.inp-parent-qty, .inp-parent-price').forEach(input => {
                    input.value = '0';
                });
                
                // Update all totals
                updateAllParentTotals();
                updateTabTotals();
            }
        });
    }

    // Collapse/Expand Logic
    document.querySelectorAll('.toggle-children').forEach(btn => {
        btn.dataset.expanded = 'false';
        
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const targetId = this.dataset.target;
            const isExpanded = this.dataset.expanded === 'true';
            const newState = !isExpanded;
            
            this.dataset.expanded = newState;
            
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

    // Initialize Icons and Calculation on Load
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
        
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
            
            // Initial Calculation to populate totals from loaded values
            updateAllParentTotals();
            updateTabTotals();
        }, 50);
    }
});
</script>
