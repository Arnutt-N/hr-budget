<!-- Global Styles adapted from reference -->
<style>
    @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@300;400;500;600;700&display=swap');
    
    :root {
        font-family: 'Noto Sans Thai', sans-serif;
    }

    /* Hide Number Input Spinners */
    input[type="number"]::-webkit-inner-spin-button,
    input[type="number"]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    input[type="number"] {
        -moz-appearance: textfield;
    }

    /* Smooth transitions */
    .transition-all-300 {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    /* Custom Scrollbar */
    ::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }
    ::-webkit-scrollbar-track {
        background: #0f172a; 
    }
    ::-webkit-scrollbar-thumb {
        background: #334155; 
        border-radius: 3px;
    }
    ::-webkit-scrollbar-thumb:hover {
        background: #475569; 
    }
</style>

<!-- Ambient Background -->
<div class="fixed inset-0 pointer-events-none overflow-hidden -z-10">
    <div class="absolute top-0 left-1/4 w-[600px] h-[600px] bg-indigo-900/10 rounded-full blur-[120px]"></div>
    <div class="absolute bottom-0 right-1/4 w-[500px] h-[500px] bg-cyan-900/10 rounded-full blur-[100px]"></div>
</div>

<div class="min-h-screen pb-10">
    <!-- Header Section -->
    <header class="mb-8 border-b border-slate-800/60 pb-6">
        <!-- Row 1: Title (left) and Fiscal Year (right) -->
        <div class="flex items-center justify-between mb-3">
            <h1 class="text-xs font-medium text-white tracking-wide">
                <?= htmlspecialchars($request['request_title']) ?>
            </h1>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-800/50 border border-slate-700/50 backdrop-blur-sm">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                </span>
                <span class="text-xs font-medium text-slate-300 tracking-wide uppercase">ปีงบประมาณ <?= $request['fiscal_year'] ?></span>
            </div>
        </div>
        
        <!-- Row 2: Status and Progress -->
        <div class="flex items-center justify-between">
            <p class="text-xs text-slate-400">
                สถานะ: <span class="text-emerald-400 capitalize"><?= $request['request_status'] ?></span> • 
                สร้างโดย: <?= htmlspecialchars($request['created_by_name'] ?? '-') ?>
            </p>
            <div class="text-right flex items-center gap-1">
                <span class="text-xs text-slate-500 uppercase tracking-widest">ความคืบหน้า</span>
                <span id="progressCount" class="text-xs font-mono font-medium text-white">0</span>
                <span class="text-xs text-slate-500">รายการ</span>
            </div>
        </div>
    </header>

    <?php if ($request['request_status'] === 'draft'): ?>
        <!-- Form Content Area -->
        <div id="loadingIndicator" class="hidden py-20 text-center">
            <i class="ph ph-spinner animate-spin text-4xl text-indigo-500 mb-4"></i>
            <p class="text-slate-400">กำลังโหลดข้อมูล...</p>
        </div>

        <!-- Dynamic Content Container -->
        <div id="itemsContainer" class="space-y-16 animate-fade-in">
            <!-- Items injected here by JS -->
        </div>

        <!-- Static Footer (End of Form) -->
        <div class="mt-12 p-6 rounded-2xl bg-slate-900/50 border border-slate-800 backdrop-blur-sm">
            <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="flex items-center gap-4 w-full md:w-auto">
                    <div class="p-4 rounded-xl bg-indigo-500/10 text-indigo-400 hidden sm:block">
                        <i class="ph ph-money text-3xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500 uppercase tracking-wider mb-1">งบประมาณรวมทั้งสิ้น (Total Budget)</p>
                        <p class="text-3xl md:text-4xl font-bold text-white font-mono tracking-tight flex items-baseline gap-2">
                            <span id="grandTotal"><?= \App\Core\View::currency($request['total_amount']) ?></span>
                            <span class="text-lg text-slate-500 font-sans font-normal">THB</span>
                        </p>
                    </div>
                </div>
                
                <div class="flex gap-4 w-full md:w-auto items-center flex-col sm:flex-row">
                    <div id="saveIndicator" class="hidden px-4 text-sm text-emerald-400 flex items-center gap-2">
                        <i class="ph ph-check-circle"></i> บันทึกแล้ว
                    </div>

                    <div class="flex gap-3 w-full sm:w-auto">
                        <a href="<?= \App\Core\View::url('/requests') ?>" 
                            class="flex-1 sm:flex-none flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-slate-800 border border-slate-700 text-slate-300 font-medium hover:bg-slate-700 hover:text-white transition-all active:scale-95">
                            <i class="ph ph-arrow-left"></i>
                            <span>ยกเลิก</span>
                        </a>
                        <button onclick="window.location.href='<?= \App\Core\View::url('/requests') ?>'"
                            class="flex-1 sm:flex-none flex items-center justify-center gap-2 px-8 py-3 rounded-xl bg-indigo-600 text-white font-bold hover:bg-indigo-500 shadow-lg shadow-indigo-600/25 transition-all active:scale-95">
                            <i class="ph ph-floppy-disk"></i>
                            <span>บันทึก</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

    <?php else: ?>
        <div class="text-center py-20 bg-slate-900/30 rounded-3xl border border-slate-800 border-dashed">
            <div class="w-16 h-16 bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-500">
                <i class="ph ph-lock text-3xl"></i>
            </div>
            <h3 class="text-lg font-medium text-white mb-2">คำขอนี้ไม่ได้อยู่ในสถานะร่าง</h3>
            <p class="text-slate-400 mb-6">ไม่สามารถแก้ไขข้อมูลได้</p>
            <a href="<?= \App\Core\View::url('/requests') ?>" class="btn btn-secondary">
                <i class="ph ph-arrow-left"></i> กลับไปรายการ
            </a>
        </div>
    <?php endif; ?>
</div>

<script>
const requestId = <?= $request['id'] ?>;
const itemsContainer = document.getElementById('itemsContainer');
const loadingIndicator = document.getElementById('loadingIndicator');
const saveIndicator = document.getElementById('saveIndicator');
const grandTotalEl = document.getElementById('grandTotal');
const progressCountEl = document.getElementById('progressCount');

// Currency formatter
const formatCurrency = (amount) => {
    return new Intl.NumberFormat('th-TH', { 
        style: 'decimal', 
        minimumFractionDigits: 2, 
        maximumFractionDigits: 2 
    }).format(amount);
};

// Initial Load: Fetch ALL categories
document.addEventListener('DOMContentLoaded', () => {
    fetchAllCategories();
});

async function fetchAllCategories() {
    itemsContainer.innerHTML = '';
    loadingIndicator.classList.remove('hidden');

    // Get all categories available
    const categories = <?= json_encode($budgetCategories ?? []) ?>;
    
    if (categories.length === 0) {
        itemsContainer.innerHTML = `<div class="text-center text-slate-500">ไม่พบหมวดงบประมาณ</div>`;
        loadingIndicator.classList.add('hidden');
        return;
    }

    try {
        // Fetch all categories in parallel
        const promises = categories.map(cat => 
            fetch(`<?= \App\Core\View::url('') ?>/requests/${requestId}/items/category?category_id=${cat.id}`)
                .then(res => res.json())
                .then(items => ({ category: cat, items: items }))
        );

        const results = await Promise.all(promises);
        
        loadingIndicator.classList.add('hidden');
        
        let totalItemsFilled = 0;

        results.forEach(({ category, items }) => {
            if (items.length > 0) {
                renderCategorySection(category, items);
                // Count filled items
                items.forEach(i => {
                    if (!i.is_header && (i.quantity > 0 || i.unit_price > 0)) {
                        totalItemsFilled++;
                    }
                });
            }
        });
        
        updateProgress(totalItemsFilled);

    } catch (error) {
        console.error(error);
        itemsContainer.innerHTML = `<div class="p-6 text-center text-red-400">เกิดข้อผิดพลาดในการโหลดข้อมูล</div>`;
    }
}

function updateProgress(count) {
    if (progressCountEl) {
        progressCountEl.textContent = count;
    }
}

// ---------------------------------------------------------
// NEW RENDER LOGIC (Card/Section Based)
// ---------------------------------------------------------

function renderCategorySection(category, items) {
    // Top Level Category Wrapper
    const categoryWrapper = document.createElement('div');
    categoryWrapper.className = 'category-section animate-fade-in';
    
    // Icon Logic
    const iconMap = {
        'บุคลากร': 'ph-users',
        'ดำเนินงาน': 'ph-briefcase',
        'ลงทุน': 'ph-buildings',
        'อุดหนุน': 'ph-hand-coins',
        'รายจ่ายอื่น': 'ph-dots-three-circle'
    };
    let mainIcon = 'ph-files';
    const catName = category.name_th || category.name || '';
    for (const key in iconMap) {
        if (catName.includes(key)) {
            mainIcon = iconMap[key];
            break;
        }
    }

    // Header for the whole Category
    categoryWrapper.innerHTML = `
        <div class="flex items-start gap-4 mb-6 px-2 border-b border-slate-800/50 pb-4">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-600 to-violet-700 flex items-center justify-center shadow-lg shadow-indigo-500/20 ring-1 ring-white/10 shrink-0">
                <i class="ph ${mainIcon} text-white text-2xl"></i>
            </div>
            <div>
                <h2 class="text-xl font-bold text-white tracking-tight">${category.name_th || category.name}</h2>
                <p class="text-sm text-slate-400 mt-1">${category.description || 'รายละเอียดรายการงบประมาณ'}</p>
            </div>
        </div>
        <div class="grid gap-4" id="cat-content-${category.id}"></div>
    `;
    
    itemsContainer.appendChild(categoryWrapper);
    const contentContainer = categoryWrapper.querySelector(`#cat-content-${category.id}`);

    // Now process items into sections (Level 1)
    let currentSectionBody = null; // Container for Level 1 header
    let looseItemsContainer = null; // Container for items without a level 1 header

    items.forEach(item => {
        // Skip root if it matches category name (Level 0)
        // Usually items include Level 0 as the category header itself in some seeds.
        if (item.level === 0) return;

        // Level 1: Collapsible Card
        if (item.level === 1 && item.is_header) {
            // Determine Icon for Section
            const secIconMap = {
                'เงินเดือน': 'ph-money',
                'เงินประจำตำแหน่ง': 'ph-briefcase',
                'ค่าตอบแทนรายเดือน': 'ph-coins',
                'เงินเพิ่มพิเศษ': 'ph-star',
                'ค่าจ้างประจำ': 'ph-user-check',
                'ค่าตอบแทนพนักงานราชการ': 'ph-user-gear',
                'ค่าตอบแทน': 'ph-credit-card',
                'ค่าใช้สอย': 'ph-wrench',
                'วัสดุ': 'ph-package'
            };
            
            let secIcon = 'ph-cards';
            for (const key in secIconMap) {
                if (item.item_name.includes(key)) {
                    secIcon = secIconMap[key];
                    break;
                }
            }
            
            // Count fields in this section (look ahead in items array)
            const sectionItemId = item.id;
            let fieldCount = 0;
            let foundSection = false;
            for (const it of items) {
                if (it.id === sectionItemId) {
                    foundSection = true;
                    continue;
                }
                if (foundSection) {
                    if (it.level === 1 && it.is_header) break; // Next section
                    if (!it.is_header && it.level === 2) fieldCount++;
                }
            }
            
            const sectionDiv = document.createElement('div');
            const isExpanded = item.item_name.includes('เงินเดือน');
            
            sectionDiv.className = `border rounded-2xl overflow-hidden transition-all duration-300 ${isExpanded ? 'border-indigo-500/30 bg-[#0f172a]/40 shadow-lg shadow-indigo-900/10' : 'border-slate-800 bg-[#0f172a]/20 hover:border-slate-700'}`;
            sectionDiv.setAttribute('data-section-id', sectionItemId);
            
            sectionDiv.innerHTML = `
                <button type="button" class="w-full flex items-center justify-between p-4 transition-colors ${isExpanded ? 'bg-indigo-500/5' : 'hover:bg-slate-800/50'}" onclick="toggleSection(this)">
                    <div class="flex items-center gap-4">
                        <div class="p-2 rounded-lg transition-colors ${isExpanded ? 'bg-indigo-500/20 text-indigo-300' : 'bg-slate-800 text-slate-400'}">
                            <i class="ph ${secIcon} text-lg"></i>
                        </div>
                        <div class="text-left">
                            <h3 class="font-semibold ${isExpanded ? 'text-indigo-200' : 'text-slate-300'}">${item.item_name}</h3>
                            <p class="text-xs text-slate-500">${fieldCount} รายการ</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="hidden sm:block text-right section-subtotal" data-section="${sectionItemId}">
                            <span class="block text-[10px] text-slate-500 uppercase">Subtotal</span>
                            <span class="text-sm font-mono font-medium text-emerald-400 subtotal-value">0.00</span>
                        </div>
                        <i class="ph ph-caret-down text-slate-500 transition-transform duration-300 ${isExpanded ? 'rotate-180' : ''}"></i>
                    </div>
                </button>
                <div class="section-content transition-all duration-300 ease-in-out overflow-hidden" style="${isExpanded ? 'max-height: 2000px; opacity: 1;' : 'max-height: 0px; opacity: 0;'}">
                    <div class="p-4 space-y-3 border-t border-slate-800/50" id="section-body-${item.id}">
                    </div>
                </div>
            `;
            contentContainer.appendChild(sectionDiv);
            currentSectionBody = sectionDiv.querySelector(`#section-body-${item.id}`);
            return;
        }

        // Handle items
        let targetContainer = currentSectionBody;
        
        // If no current section (items appearing before first Level 1 header), create a default container
        if (!targetContainer) {
            if (!looseItemsContainer) {
                looseItemsContainer = document.createElement('div');
                looseItemsContainer.className = 'border rounded-2xl overflow-hidden border-slate-800 bg-[#0f172a]/20 mb-4 p-4 space-y-3';
                contentContainer.appendChild(looseItemsContainer);
            }
            targetContainer = looseItemsContainer;
        }

        if (item.is_header) {
            // Level 2 Header
             const subHeader = document.createElement('div');
            subHeader.className = "pt-2 pb-1 text-xs uppercase tracking-wider text-indigo-400 font-bold flex items-center gap-2";
            subHeader.innerHTML = `<span>${item.item_name}</span> <div class="h-px bg-indigo-500/20 flex-1"></div>`;
            targetContainer.appendChild(subHeader);
        } else {
             // Leaf Item
            const fieldWrapper = document.createElement('div');
            fieldWrapper.className = `group rounded-xl transition-all duration-300 border bg-[#1e293b]/30 border-slate-700/50 hover:border-indigo-500/30 hover:bg-[#1e293b]/50 p-4`;
            
            const quantity = item.quantity || '';
            const unitPrice = item.unit_price || '';
            const total = (quantity && unitPrice) ? (quantity * unitPrice) : 0;
            const remark = item.remark || '';
            const requiresQty = item.requires_quantity;

            fieldWrapper.innerHTML = `
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-1 h-4 rounded-full bg-indigo-500"></div>
                    <label class="text-sm font-medium text-slate-200">${item.item_name}</label>
                    ${item.default_unit ? `<span class="text-xs text-slate-500 bg-slate-800 px-2 py-0.5 rounded-md border border-slate-700">${item.default_unit}</span>` : ''}
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                    <!-- Quantity -->
                    <div class="md:col-span-2 space-y-1.5">
                        <label class="text-[10px] uppercase tracking-wider text-slate-500 font-semibold">อัตรา (คน/หน่วย)</label>
                        <input type="number" 
                            class="w-full bg-[#0f172a] border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:border-cyan-400 focus:ring-cyan-400/20 transition-all text-right font-mono"
                            placeholder="-"
                            value="${quantity}"
                            data-id="${item.id}"
                            data-field="quantity"
                            ${!requiresQty ? 'disabled' : ''}
                        >
                    </div>

                    <!-- Unit Price -->
                    <div class="md:col-span-3 space-y-1.5">
                         <label class="text-[10px] uppercase tracking-wider text-slate-500 font-semibold">ราคาต่อหน่วย (บาท)</label>
                         <input type="number" 
                            class="w-full bg-[#0f172a] border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:border-emerald-400 focus:ring-emerald-400/20 transition-all text-right font-mono"
                            placeholder="0.00"
                            step="0.01"
                            value="${unitPrice}"
                            data-id="${item.id}"
                            data-field="unit_price"
                        >
                    </div>

                    <!-- Total (Editable) -->
                    <div class="md:col-span-3 space-y-1.5">
                        <label class="text-[10px] uppercase tracking-wider text-slate-500 font-semibold">วงเงินรวม (บาท)</label>
                        <input type="number" 
                            class="w-full bg-[#0f172a] border border-slate-700 rounded-lg px-3 py-2 text-sm text-indigo-300 font-bold focus:border-indigo-400 focus:ring-indigo-400/20 transition-all text-right font-mono row-total"
                            placeholder="0.00"
                            step="0.01"
                            value="${total || ''}"
                            data-id="${item.id}"
                            data-field="total_amount"
                        >
                    </div>

                    <!-- Remark -->
                    <div class="md:col-span-4 space-y-1.5">
                        <label class="text-[10px] uppercase tracking-wider text-slate-500 font-semibold">หมายเหตุ</label>
                        <div class="relative">
                            <input type="text" 
                                class="w-full bg-[#0f172a] border border-slate-700 rounded-lg pl-8 pr-3 py-2 text-sm text-slate-300 focus:border-indigo-400 focus:ring-indigo-400/20 transition-all placeholder-slate-700"
                                placeholder="..."
                                value="${remark}"
                                data-id="${item.id}"
                                data-field="remark"
                            >
                            <i class="ph ph-note-pencil absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-600"></i>
                        </div>
                    </div>
                </div>
            `;
            targetContainer.appendChild(fieldWrapper);
        }
    });

    attachListeners();
    
    // Update subtotals after initial render
    setTimeout(() => updateSectionSubtotals(), 100);
}

function toggleSection(btn) {
    const content = btn.nextElementSibling;
    const icon = btn.querySelector('.ph-caret-down');
    
    // Check if open (using max-height or opacity)
    const isOpen = content.style.maxHeight !== '0px';

    if (!isOpen) {
        content.style.maxHeight = '2000px';
        content.style.opacity = '1';
        icon.style.transform = 'rotate(180deg)';
        btn.classList.add('bg-indigo-500/5');
        // also add text highlights if needed, but css handles group hover or parent class?
        // simple background toggle is enough for now
    } else {
        content.style.maxHeight = '0px';
        content.style.opacity = '0';
        icon.style.transform = 'rotate(0deg)';
        btn.classList.remove('bg-indigo-500/5');
    }
}

function attachListeners() {
    document.querySelectorAll('input[data-field]').forEach(input => {
        // Prevent duplicate listeners
        if (input.hasAttribute('data-listening')) return;
        input.setAttribute('data-listening', 'true');
        
        input.addEventListener('input', handleInput);
        input.addEventListener('blur', handleBlur);
        input.addEventListener('focus', e => e.target.select());
    });
}

function handleInput(e) {
    const input = e.target;
    if (input.dataset.field === 'remark') return;

    const wrapper = input.closest('.grid');
    const qtyInput = wrapper.querySelector('[data-field="quantity"]');
    const priceInput = wrapper.querySelector('[data-field="unit_price"]');
    const totalEl = wrapper.querySelector('.row-total');

    let qty = parseFloat(qtyInput.value) || 0;
    if (qtyInput.disabled) qty = 1;
    const price = parseFloat(priceInput.value) || 0;
    
    totalEl.textContent = formatCurrency(qty * price);
    
    // Update section subtotals
    updateSectionSubtotals();
}

// Calculate and update all section subtotals
function updateSectionSubtotals() {
    document.querySelectorAll('.section-subtotal').forEach(subtotalEl => {
        const sectionId = subtotalEl.dataset.section;
        const sectionContainer = document.querySelector(`#section-body-${sectionId}`);
        if (!sectionContainer) return;
        
        let total = 0;
        sectionContainer.querySelectorAll('.row-total').forEach(rowTotal => {
            const val = parseFloat(rowTotal.textContent.replace(/,/g, '')) || 0;
            total += val;
        });
        
        const subtotalValue = subtotalEl.querySelector('.subtotal-value');
        if (subtotalValue) {
            subtotalValue.textContent = formatCurrency(total);
        }
    });
}

async function handleBlur(e) {
    const input = e.target;
    const wrapper = input.closest('.grid');
    const id = input.dataset.id;
    
    const qtyInput = wrapper.querySelector('[data-field="quantity"]');
    const priceInput = wrapper.querySelector('[data-field="unit_price"]');
    const totalInput = wrapper.querySelector('[data-field="total_amount"]');
    const remarkInput = wrapper.querySelector('[data-field="remark"]');

    // Show mini saving indicator
    saveIndicator.classList.remove('hidden');
    saveIndicator.classList.add('animate-pulse');
    saveIndicator.innerHTML = '<i class="ph ph-spinner animate-spin"></i> กำลังบันทึก...';

    const payload = {
        category_item_id: id,
        quantity: qtyInput ? qtyInput.value : '',
        unit_price: priceInput ? priceInput.value : '',
        total_amount: totalInput ? totalInput.value : '',
        remark: remarkInput ? remarkInput.value : ''
    };

    try {
        const res = await fetch(`<?= \App\Core\View::url('') ?>/requests/${requestId}/items/update`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(payload)
        });
        
        let data;
        const contentType = res.headers.get("content-type");
        if (contentType && contentType.indexOf("application/json") !== -1) {
            data = await res.json();
        } else {
            throw new Error("Invalid response format from server");
        }
        
        if (!res.ok) {
            throw new Error(data.message || 'Server returned an error');
        }
        
        if (data.success) {
            grandTotalEl.textContent = formatCurrency(data.total_amount);
            saveIndicator.classList.remove('animate-pulse');
            saveIndicator.innerHTML = '<i class="ph ph-check-circle text-emerald-400"></i> บันทึกแล้ว';
            
            // Approximate count logic for visual feedback
             let totalItemsFilled = 0;
             const wrappers = document.querySelectorAll('.grid'); // Each item grid
             wrappers.forEach(w => {
                 const q = w.querySelector('[data-field="quantity"]');
                 const p = w.querySelector('[data-field="unit_price"]');
                 /* Logic for counting filled items */
                 if (q && p && (parseFloat(q.value) > 0 || parseFloat(p.value) > 0)) {
                     totalItemsFilled++;
                 }
                 // Consider manual total too
                 const t = w.querySelector('[data-field="total_amount"]');
                 if (t && parseFloat(t.value) > 0) {
                     // Check if not already counted (simple logic)
                     if (!(q && p && (parseFloat(q.value) > 0 || parseFloat(p.value) > 0))) {
                         totalItemsFilled++;
                     }
                 }
             });
             updateProgress(totalItemsFilled);

            setTimeout(() => saveIndicator.classList.add('hidden'), 2000);
        } else {
            throw new Error(data.message || 'Save failed');
        }
    } catch (err) {
        console.error('Save Error:', err);
        saveIndicator.classList.remove('animate-pulse');
        saveIndicator.innerHTML = `<i class="ph ph-warning text-red-500"></i> <span class="text-red-400 text-xs">${err.message || 'บันทึกไม่สำเร็จ'}</span>`;
        // Keep error visible longer
        setTimeout(() => saveIndicator.classList.add('hidden'), 5000);
    }
}
</script>
