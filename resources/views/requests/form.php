<?php
/**
 * View: Budget Request Form (Part 1 - Hierarchical Entry)
 */
?>
<div class="animate-fade-in pb-20">
    <!-- Header context -->
    <div class="mb-6 flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-dark-muted mb-2">
                <a href="<?= \App\Core\View::url('/requests') ?>" class="hover:text-white transition-colors flex items-center gap-1">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i> คำขอทั้งหมด
                </a>
                <span>/</span>
                <span class="text-slate-400">สร้างคำขอใหม่</span>
            </div>
            <h1 class="text-3xl font-bold text-white mb-1"><?= htmlspecialchars($requestTitle) ?></h1>
            <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-sm">
                <div class="flex items-center gap-2">
                    <span class="text-dark-muted">ปีงบประมาณ:</span>
                    <span class="text-primary-400 font-semibold"><?= htmlspecialchars($fiscalYear) ?></span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-dark-muted">หน่วยงาน:</span>
                    <span class="text-slate-300"><?= htmlspecialchars($organization['name_th'] ?? '-') ?></span>
                </div>
            </div>
        </div>
        
        <div class="flex items-center gap-3">
            <button type="button" onclick="history.back()" class="btn btn-secondary border-slate-700 bg-slate-800/50">
                ยกเลิก
            </button>
            <button type="button" id="btnSaveDraft" class="btn btn-primary shadow-lg shadow-primary-500/20">
                <i data-lucide="save" class="w-4 h-4 mr-1.5"></i>
                บันทึกร่างคำขอ
            </button>
        </div>
    </div>

    <!-- Main Entry Form -->
    <form id="mainRequestForm" action="<?= \App\Core\View::url('/requests') ?>" method="POST">
        <?= \App\Core\View::csrf() ?>
        <input type="hidden" name="fiscal_year" value="<?= htmlspecialchars($fiscalYear) ?>">
        <input type="hidden" name="org_id" value="<?= htmlspecialchars($orgId) ?>">
        <input type="hidden" name="request_title" value="<?= htmlspecialchars($requestTitle) ?>">

        <!-- Tabs Navigation -->
        <div class="bg-slate-900/50 border border-slate-700 rounded-xl overflow-hidden shadow-xl mb-6">
            <div class="flex border-b border-dark-border overflow-x-auto scrollbar-hide bg-slate-800/30">
                <?php foreach ($budgetTree as $i => $cat): ?>
                <button type="button" 
                        class="tab-btn px-6 py-4 text-sm font-medium transition-all duration-200 border-b-2 whitespace-nowrap <?= $i === 0 ? 'border-primary-500 text-primary-400 bg-primary-500/5' : 'border-transparent text-dark-muted hover:text-slate-300' ?>"
                        data-tab="cat-<?= $cat['id'] ?>">
                    <?= htmlspecialchars($cat['name_th']) ?>
                </button>
                <?php endforeach; ?>
            </div>

            <!-- Tab Contents -->
            <div class="p-0">
                <?php foreach ($budgetTree as $i => $cat): ?>
                <div id="cat-<?= $cat['id'] ?>" class="tab-content <?= $i === 0 ? '' : 'hidden' ?>">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse table-fixed min-w-[800px]">
                            <thead>
                                <tr class="bg-slate-800/50 border-b border-slate-700 text-slate-400 text-xs uppercase tracking-wider">
                                    <th class="py-3 px-6 w-[45%] font-semibold">รายการ</th>
                                    <th class="py-3 px-4 w-[12%] text-center font-semibold text-center">จำนวน</th>
                                    <th class="py-3 px-4 w-[18%] text-center font-semibold text-center">วงเงิน (บาท)</th>
                                    <th class="py-3 px-6 w-[25%] font-semibold">หมายเหตุ</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-dark-border">
                                <?php 
                                    // Fetch hierarchy for this category
                                    $categoryItems = \App\Models\BudgetCategoryItem::getHierarchy($cat['id']);
                                    
                                    function renderItemRow($item, $level = 0) {
                                        $hasChildren = !empty($item['children']);
                                        $paddingClass = "pl-" . (6 + ($level * 6));
                                        $rowClass = $hasChildren ? 'bg-slate-800/10 font-semibold' : 'hover:bg-white/5';
                                        $id = $item['id'];
                                ?>
                                    <tr class="<?= $rowClass ?> group transition-colors" data-level="<?= $level ?>" data-parent="<?= $item['parent_id'] ?>" data-item-id="<?= $id ?>">
                                        <td class="py-3 px-6 <?= $paddingClass ?> relative align-middle">
                                            <div class="flex items-center gap-2">
                                                <?php if ($hasChildren): ?>
                                                    <button type="button" class="collapse-btn text-slate-500 hover:text-white transition-colors">
                                                        <i data-lucide="chevron-down" class="w-4 h-4"></i>
                                                    </button>
                                                <?php endif; ?>
                                                <span class="<?= $hasChildren ? 'text-slate-200' : 'text-slate-400' ?>"><?= htmlspecialchars($item['name']) ?></span>
                                            </div>
                                        </td>
                                        <td class="py-2 px-4 text-center align-middle">
                                            <?php if (!$hasChildren): ?>
                                                <input type="number" 
                                                       name="items[<?= $id ?>][quantity]" 
                                                       value="0" 
                                                       min="0"
                                                       class="w-full bg-slate-800/50 border border-slate-700 rounded-lg px-2 py-1.5 text-center text-white focus:ring-1 focus:ring-primary-500 outline-none transition-all qty-input">
                                            <?php else: ?>
                                                <span class="text-slate-500">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-2 px-4 text-center align-middle">
                                            <?php if (!$hasChildren): ?>
                                                <input type="number" 
                                                       name="items[<?= $id ?>][amount]" 
                                                       value="0.00" 
                                                       step="0.01"
                                                       min="0"
                                                       class="w-full bg-slate-800/50 border border-slate-700 rounded-lg px-2 py-1.5 text-right text-white focus:ring-1 focus:ring-primary-500 outline-none transition-all amount-input">
                                            <?php else: ?>
                                                <input type="text" 
                                                       readonly 
                                                       class="w-full bg-transparent border-none px-2 py-1.5 text-right text-emerald-400 font-bold parent-total-amount" 
                                                       value="0.00">
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-2 px-6 align-middle">
                                            <?php if (!$hasChildren): ?>
                                                <input type="text" 
                                                       name="items[<?= $id ?>][note]" 
                                                       placeholder="ระบุเพิ่มเติม..."
                                                       class="w-full bg-slate-800/50 border border-slate-700 rounded-lg px-3 py-1.5 text-sm text-slate-300 focus:ring-1 focus:ring-primary-500 outline-none transition-all">
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php 
                                        if ($hasChildren) {
                                            foreach ($item['children'] as $child) {
                                                renderItemRow($child, $level + 1);
                                            }
                                        }
                                    }

                                    foreach ($categoryItems as $item) {
                                        renderItemRow($item);
                                    }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Footer Summary Bar -->
            <div class="bg-slate-800 p-6 border-t border-slate-700">
                <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                    <div class="flex items-center gap-8">
                        <div>
                            <p class="text-dark-muted text-xs uppercase tracking-wider mb-1">ยอดรวมวงเงินคำขอทั้งหมด</p>
                            <p class="text-3xl font-bold text-white tracking-tight" id="grandTotalDisplay">0.00 <span class="text-lg font-normal text-slate-500">บาท</span></p>
                        </div>
                    </div>
                    <div class="flex gap-4 w-full md:w-auto">
                         <button type="submit" class="btn btn-primary btn-lg w-full md:w-48 shadow-xl shadow-primary-500/20">
                            <i data-lucide="check-circle-2" class="w-5 h-5 mr-2"></i> บันทึกข้อมูล
                         </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab switching
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const target = btn.dataset.tab;

            tabBtns.forEach(b => {
                b.classList.remove('border-primary-500', 'text-primary-400', 'bg-primary-500/5');
                b.classList.add('border-transparent', 'text-dark-muted');
            });

            tabContents.forEach(c => c.classList.add('hidden'));

            btn.classList.add('border-primary-500', 'text-primary-400', 'bg-primary-500/5');
            btn.classList.remove('border-transparent', 'text-dark-muted');
            document.getElementById(target).classList.remove('hidden');
        });
    });

    // Auto-calculate hierarchy
    const amountInputs = document.querySelectorAll('.amount-input');
    const grandTotalDisplay = document.getElementById('grandTotalDisplay');

    function calculateGrandTotal() {
        let total = 0;
        // Only sum level 0 parents or all inputs? 
        // Actually we only need to sum top-level category totals or all child inputs to avoid double counting.
        // Summing all .amount-input is safest if they are only on leaf nodes.
        const leafInputs = document.querySelectorAll('.amount-input');
        leafInputs.forEach(input => {
            total += parseFloat(input.value) || 0;
        });
        grandTotalDisplay.textContent = total.toLocaleString('th-TH', { 
            minimumFractionDigits: 2, 
            maximumFractionDigits: 2 
        });
    }

    function updateParentTotals() {
        // Find all parent rows (rows with parent-total-amount)
        const parentRows = Array.from(document.querySelectorAll('tr[data-item-id]')).filter(tr => tr.querySelector('.parent-total-amount'));
        
        // Sort parents by level descending to calculate from lowest children up
        parentRows.sort((a, b) => b.dataset.level - a.dataset.level);

        parentRows.forEach(row => {
            const id = row.dataset.itemId;
            const children = document.querySelectorAll(`tr[data-parent="${id}"]`);
            let sum = 0;

            children.forEach(child => {
                // If child is also a parent, get its calculated value
                const childTotalInput = child.querySelector('.parent-total-amount');
                if (childTotalInput) {
                    sum += parseFloat(childTotalInput.value.replace(/,/g, '')) || 0;
                } else {
                    // Child is a leaf, get its input value
                    const amountInput = child.querySelector('.amount-input');
                    if (amountInput) {
                        sum += parseFloat(amountInput.value) || 0;
                    }
                }
            });

            const totalField = row.querySelector('.parent-total-amount');
            if (totalField) {
                totalField.value = sum.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }
        });

        calculateGrandTotal();
    }

    amountInputs.forEach(input => {
        input.addEventListener('input', updateParentTotals);
        // Also handle blur for decimal formatting
        input.addEventListener('blur', function() {
            if (this.value === '') this.value = '0.00';
            else this.value = parseFloat(this.value).toFixed(2);
        });
    });

    // Initial calculation
    updateParentTotals();

    // Collapse/Expand functionality
    document.querySelectorAll('.collapse-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const row = this.closest('tr');
            const id = row.dataset.itemId;
            const icon = this.querySelector('i');
            
            toggleChildren(id, row.classList.contains('collapsed'));
            
            if (row.classList.contains('collapsed')) {
                row.classList.remove('collapsed');
                icon.style.transform = '';
            } else {
                row.classList.add('collapsed');
                icon.style.transform = 'rotate(-90deg)';
            }
        });
    });

    function toggleChildren(parentId, show) {
        const children = document.querySelectorAll(`tr[data-parent="${parentId}"]`);
        children.forEach(child => {
            if (show) {
                child.classList.remove('hidden');
                // If child was NOT collapsed, also show its children
                if (!child.classList.contains('collapsed')) {
                    const childId = child.dataset.itemId;
                    toggleChildren(childId, true);
                }
            } else {
                child.classList.add('hidden');
                // Always hide recursively when hiding parent
                const childId = child.dataset.itemId;
                toggleChildren(childId, false);
            }
        });
    }

    // Lucide initialization for dynamic icons if needed
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
});
</script>
