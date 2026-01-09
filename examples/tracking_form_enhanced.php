<?php
// Determine Mode
$fiscalYear = $fiscalYear ?? date('Y') + 543;
$formUrl = \App\Core\View::url('/budgets/tracking/save');

// --- Icon Helper (Lucide Style from Example) ---
if (!function_exists('getIcon')) {
    function getIcon($name, $size = 18, $className = "") {
        $paths = [
            'Users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" /><circle cx="9" cy="7" r="4" /><path d="M22 21v-2a4 4 0 0 0-3-3.87" /><path d="M16 3.13a4 4 0 0 1 0 7.75" />',
            'Banknote' => '<rect width="20" height="12" x="2" y="6" rx="2" /><circle cx="12" cy="12" r="2" /><path d="M6 12h.01M18 12h.01" />',
            'Briefcase' => '<rect width="20" height="14" x="2" y="7" rx="2" ry="2" /><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16" />',
            'Activity' => '<path d="M22 12h-4l-3 9L9 3l-3 9H2" />',
            'ChevronDown' => '<path d="m6 9 6 6 6-6" />',
            'Save' => '<path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" /><polyline points="17 21 17 13 7 13 7 21" /><polyline points="7 3 7 8 15 8" />',
            'CreditCard' => '<rect width="20" height="14" x="2" y="5" rx="2" /><line x1="2" x2="22" y1="10" y2="10" />',
            'Wrench' => '<path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z" />',
            'Coins' => '<circle cx="8" cy="8" r="6" /><path d="M18.09 10.37A6 6 0 1 1 10.34 18" /><path d="M7 6h1v4" /><path d="m16.71 13.88.7 .71-2.82 2.82" />',
            'Wallet' => '<path d="M21 12V7H5a2 2 0 0 1 0-4h14v4" /><path d="M3 5v14a2 2 0 0 0 2 2h16v-5" /><path d="M18 12a2 2 0 0 0 0 4h4v-4Z" />',
            'AlertCircle' => '<circle cx="12" cy="12" r="10" /><line x1="12" x2="12" y1="8" y2="12" /><line x1="12" x2="12.01" y1="16" y2="16" />',
            'UserCog' => '<circle cx="18" cy="15" r="3" /><circle cx="9" cy="7" r="4" /><path d="M10 15H6a4 4 0 0 0-4 4v2" /><path d="m21.7 16.4.9-.9" /><path d="m15.2 13.9-.9-.9" /><path d="m16.6 18.7.3-1.2" /><path d="m20.4 14.3.3-1.2" /><path d="m19.6 13.5-1.2.3" /><path d="m16.8 17.3-1.2.3" />',
            'Spinner' => '<path d="M21 12a9 9 0 1 1-6.219-8.56" />',
            'CheckCircle' => '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>',
            'ArrowLeft' => '<path d="m12 19-7-7 7-7"/><path d="M19 12H5"/>'
        ];

        $path = $paths[$name] ?? '';
        return '<svg xmlns="http://www.w3.org/2000/svg" width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="'.$className.'">'.$path.'</svg>';
    }
}
?>

<!-- Global Styles & Animations -->
<style>
    @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@300;400;500;600;700&display=swap');
    
    :root { font-family: 'Noto Sans Thai', sans-serif; }

    /* Hide Number Input Spinners */
    input[type="number"]::-webkit-inner-spin-button, input[type="number"]::-webkit-outer-spin-button {
        -webkit-appearance: none; margin: 0;
    }
    input[type="number"] { -moz-appearance: textfield; }

    /* Custom Scrollbar */
    ::-webkit-scrollbar { width: 8px; height: 8px; }
    ::-webkit-scrollbar-track { background: #0f172a; }
    ::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
    ::-webkit-scrollbar-thumb:hover { background: #475569; }

    .glass-card {
        background: rgba(30, 41, 59, 0.4);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.05);
    }
    .glass-card:hover {
        background: rgba(30, 41, 59, 0.6);
        border-color: rgba(99, 102, 241, 0.2);
    }
</style>

<!-- Ambient Background -->
<div class="fixed inset-0 pointer-events-none overflow-hidden -z-10 bg-[#020617]">
    <div class="absolute top-[-10%] left-[-10%] w-[500px] h-[500px] bg-indigo-900/10 rounded-full blur-[120px]"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-[500px] h-[500px] bg-cyan-900/10 rounded-full blur-[100px]"></div>
    <div class="absolute top-0 left-0 w-full h-full opacity-20 brightness-100 contrast-150" style="background-image: url('https://grainy-gradients.vercel.app/noise.svg');"></div>
</div>

<div class="max-w-4xl mx-auto px-4 pb-32 pt-8 animate-fade-in relative z-10">
    
    <!-- Header -->
    <header class="mb-10 text-center">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-800/50 border border-slate-700/50 mb-4 backdrop-blur-sm">
            <span class="relative flex h-2 w-2">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
            </span>
            <span class="text-xs font-medium text-slate-300 tracking-wide uppercase">Fiscal Year <?= $fiscalYear ?></span>
        </div>
        <h1 class="text-3xl font-bold text-white tracking-tight mb-2">Smart Budget Tracking</h1>
        <p class="text-slate-400">บันทึกและติดตามสถานะงบประมาณรายจ่ายประจำปี</p>
    </header>

    <form id="tracking-form" class="space-y-12">
        <input type="hidden" id="fiscal_year" value="<?= $fiscalYear ?>">

        <?php foreach ($categories as $category): ?>
        <?php 
            // Skip empty categories
            if (empty($category['items'])) continue;
            
            // Determine Icon based on category name
            $iconName = 'Activity';
            $catName = $category['name'] ?? $category['name_th'] ?? '';
            if (strpos($catName, 'บุคลากร') !== false) $iconName = 'Users';
            elseif (strpos($catName, 'ดำเนินงาน') !== false) $iconName = 'Activity';
            elseif (strpos($catName, 'ลงทุน') !== false) $iconName = 'Coins'; // Or Buildings if available, but Coins matches logic
            elseif (strpos($catName, 'อุดหนุน') !== false) $iconName = 'Banknote';
        ?>
        <div class="space-y-4 category-group">
            <div class="flex items-center gap-3 mb-6 px-2">
                <div class="p-2 rounded-xl bg-gradient-to-br from-indigo-600 to-violet-700 shadow-lg shadow-indigo-500/20 text-white">
                     <?= getIcon($iconName, 24) ?>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-white"><?= htmlspecialchars($catName) ?></h2>
                    <p class="text-xs text-slate-500"><?= htmlspecialchars($category['description'] ?? 'รายละเอียดรายการงบประมาณ') ?></p>
                </div>
            </div>

            <div class="space-y-4">
                <?php 
                // --- ROBUST GROUPING LOGIC ---
                // 1. Group items into sections
                $sections = [];
                $currentSec = null;
                $looseItems = [];

                foreach ($category['items'] as $item) {
                     // Check if item describes a Section Header
                     $isHeader = !empty($item['is_header']);
                     $level = (int) ($item['level'] ?? 0);

                     // Treat Level 1 + isHeader as New Section
                     if ($isHeader && $level == 1) {
                         if ($currentSec) {
                             $sections[] = $currentSec;
                         }
                         $currentSec = [
                             'header' => $item,
                             'items' => []
                         ];
                     } else {
                         // It causes duplication to add loose items if they are already in a section
                         // But if no section is active, they are loose.
                         if ($currentSec) {
                             $currentSec['items'][] = $item;
                         } else {
                             $looseItems[] = $item;
                         }
                     }
                }
                // Add last section
                if ($currentSec) {
                    $sections[] = $currentSec;
                }

                // 2. Render Logic
                
                // 2.1 Render Loose Items
                if (!empty($looseItems)) {
                    $looseHeader = [
                        'id' => 'loose_' . $category['id'], 
                        'item_name' => 'รายการทั่วไป (' . htmlspecialchars($catName) . ')',
                        'is_loose' => true
                    ];
                    renderTrackingSection($looseHeader, $looseItems, $trackings);
                }

                // 2.2 Render Sections
                foreach ($sections as $sec) {
                    renderTrackingSection($sec['header'], $sec['items'], $trackings);
                }

                ?>
            </div>
        </div>
        <?php endforeach; ?>
    </form>

    <!-- Sticky Footer -->
    <div class="fixed bottom-6 left-1/2 -translate-x-1/2 w-[calc(100%-2rem)] max-w-[54rem] bg-[#0f172a] rounded-2xl p-4 flex items-center justify-between shadow-2xl z-50 border border-slate-800/60 ring-1 ring-white/5">
        <div class="flex items-center gap-4">
            <div class="p-3 rounded-xl bg-indigo-600/20 text-indigo-400">
                <?= getIcon('Wallet', 28) ?>
            </div>
            <div>
                <p class="text-[11px] text-slate-400 uppercase tracking-widest mb-0.5">งบประมาณรวมทั้งสิ้น (TOTAL BUDGET)</p>
                <div class="flex items-baseline gap-2">
                    <p class="text-3xl font-bold text-white font-mono tracking-tight" id="grand-total-remaining">0.00</p>
                    <span class="text-xl font-bold text-white">฿</span>
                    <span class="text-sm text-slate-500 font-medium">THB</span>
                </div>
            </div>
        </div>
        
        <div class="flex items-center gap-3">
             <div id="save-status" class="text-xs hidden mr-2">
                 <span class="text-emerald-400 flex items-center gap-1">
                     <?= getIcon('CheckCircle', 14) ?> บันทึกแล้ว
                 </span>
             </div>
             
             <a href="<?= \App\Core\View::url('/budgets/list') ?>" class="group flex items-center gap-2 px-6 py-3 rounded-xl font-medium text-slate-300 bg-slate-800/80 hover:bg-slate-800 border border-slate-700 transition-all hover:text-white">
                 <?= getIcon('ArrowLeft', 16, 'group-hover:-translate-x-0.5 transition-transform') ?>
                 <span>ยกเลิก</span>
             </a>
             
             <button type="button" id="btn-save" class="bg-indigo-600 hover:bg-indigo-500 text-white px-8 py-3 rounded-xl font-bold shadow-lg shadow-indigo-600/20 transition-all flex items-center gap-2 active:scale-95">
                <?= getIcon('Save', 18) ?>
                <span class="hidden sm:inline">บันทึก</span>
            </button>
        </div>
    </div>

</div>

<?php
// Function Definition
function renderTrackingSection($header, $items, $trackings) {
    if (empty($items) && empty($header['is_loose']) && empty($header['active'])) return; // Modified slightly to allow forcing render? No, keep standard check
    
    // Only return if REALLY empty (no items and not forced)
    // But Example shows empty sections sometimes? No, usually hidden.
    if (empty($items)) return;

    $sectionId = $header['id'];
    $title = $header['item_name'] ?? 'รายการทั่วไป';
    
    // Icon logic
    $secIcon = 'Coins'; // Default
    if (strpos($title, 'เงินเดือน') !== false) $secIcon = 'Banknote';
    elseif (strpos($title, 'ประจำตำแหน่ง') !== false) $secIcon = 'Briefcase';
    elseif (strpos($title, 'ค่าจ้าง') !== false) $secIcon = 'Users';
    elseif (strpos($title, 'ค่าตอบแทน') !== false) $secIcon = 'Wallet';
    elseif (strpos($title, 'เงินเพิ่ม') !== false) $secIcon = 'AlertCircle';
    elseif (strpos($title, 'พนักงานราชการ') !== false) $secIcon = 'UserCog';
    elseif (strpos($title, 'ค่าใช้สอย') !== false) $secIcon = 'Wrench';
    ?>
    
    <div class="border rounded-2xl overflow-hidden transition-all duration-300 border-slate-800 bg-[#1e293b]/10 hover:border-slate-700 section-wrapper" data-section-id="<?= $sectionId ?>">
        <button type="button" class="w-full flex items-center justify-between p-4 transition-colors hover:bg-slate-800/50 section-toggle">
            <div class="flex items-center gap-4">
                <div class="p-2.5 rounded-xl bg-slate-800 text-slate-400 transition-colors icon-wrapper">
                    <?= getIcon($secIcon, 20) ?>
                </div>
                <div class="text-left">
                    <h3 class="font-semibold text-slate-300 transition-colors title-text"><?= htmlspecialchars($title) ?></h3>
                    <p class="text-xs text-slate-500"><?= count($items) ?> รายการ</p>
                </div>
            </div>
            
            <div class="flex items-center gap-4">
                <div class="text-right hidden sm:block">
                    <div class="text-[10px] text-slate-500 uppercase">Remaining</div>
                    <div class="text-sm font-mono font-bold text-emerald-400 section-remaining">0.00</div>
                </div>
                <span class="text-slate-500 transition-transform duration-300 arrow-icon">
                    <?= getIcon('ChevronDown', 18) ?>
                </span>
            </div>
        </button>
        
        <div class="transition-all duration-300 ease-in-out overflow-hidden max-h-0 opacity-0 section-content">
            <div class="p-4 space-y-4 border-t border-slate-800/50">
                <?php foreach ($items as $item): 
                    $t = $trackings[$item['id']] ?? [];
                    $tData = [
                        'allocated' => (float)($t['allocated'] ?? 0),
                        'transfer' => (float)($t['transfer'] ?? 0),
                        'disbursed' => (float)($t['disbursed'] ?? 0),
                        'pending' => (float)($t['pending'] ?? 0),
                        'po' => (float)($t['po'] ?? 0),
                    ];
                ?>
                <div class="glass-card rounded-xl p-5 tracking-card" data-item-id="<?= $item['id'] ?>" data-tracking='<?= json_encode($tData) ?>'>
                    <!-- Header -->
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 gap-2">
                        <h4 class="text-white font-medium flex items-center gap-2 text-sm">
                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                            <?= htmlspecialchars($item['item_name']) ?>
                        </h4>
                        <div class="px-3 py-1 rounded-full text-xs font-bold font-mono tracking-wide bg-slate-800 border border-slate-700 item-balance-badge text-slate-300">
                            Balance: 0.00
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <!-- Budget Zone -->
                        <div class="bg-[#0f172a]/60 rounded-lg p-3 border border-indigo-500/10 relative overflow-hidden group">
                            <div class="absolute top-0 right-0 w-16 h-16 bg-indigo-500/5 rounded-full -mr-8 -mt-8 blur-xl group-hover:bg-indigo-500/10 transition-all"></div>
                            <div class="flex items-center gap-2 mb-2 text-indigo-300 text-xs uppercase tracking-wider font-semibold">
                                 <?= getIcon('Wallet', 12) ?> งบประมาณสุทธิ
                            </div>
                            <div class="grid grid-cols-2 gap-3 mb-2">
                                <div class="col-span-1">
                                    <label class="text-[10px] text-slate-500 block mb-1">งบจัดสรร</label>
                                    <input type="number" step="0.01" class="w-full bg-slate-800/50 border border-slate-700 rounded px-2 py-1.5 text-right text-sm text-white focus:border-indigo-500 focus:outline-none transition-colors inp-allocated" value="<?= $tData['allocated'] ?>" placeholder="0.00">
                                </div>
                                <div class="col-span-1">
                                    <label class="text-[10px] text-slate-500 block mb-1">โอน (+/-)</label>
                                    <input type="number" step="0.01" class="w-full bg-slate-800/50 border border-slate-700 rounded px-2 py-1.5 text-right text-sm text-cyan-300 focus:border-cyan-500 focus:outline-none transition-colors inp-transfer" value="<?= $tData['transfer'] ?>" placeholder="0.00">
                                </div>
                            </div>
                            <div class="flex justify-between items-center pt-2 border-t border-indigo-500/10">
                                <span class="text-[10px] text-slate-500">รวมงบสุทธิ</span>
                                <span class="text-sm font-bold font-mono text-indigo-300 val-total-budget">0.00</span>
                            </div>
                        </div>

                        <!-- Usage Zone -->
                        <div class="bg-[#0f172a]/60 rounded-lg p-3 border border-orange-500/10 relative overflow-hidden group">
                            <div class="absolute top-0 right-0 w-16 h-16 bg-orange-500/5 rounded-full -mr-8 -mt-8 blur-xl group-hover:bg-orange-500/10 transition-all"></div>
                            <div class="flex items-center gap-2 mb-2 text-orange-300 text-xs uppercase tracking-wider font-semibold">
                                <?= getIcon('CreditCard', 12) ?> การเบิกจ่าย
                            </div>
                            <div class="grid grid-cols-3 gap-2 mb-2">
                                <div>
                                    <label class="text-[10px] text-slate-500 block mb-1">เบิกจ่าย</label>
                                    <input type="number" step="0.01" class="w-full bg-slate-800/50 border border-slate-700 rounded px-2 py-1.5 text-right text-xs text-orange-200 focus:border-orange-500 focus:outline-none inp-disbursed" value="<?= $tData['disbursed'] ?>" placeholder="-">
                                </div>
                                <div>
                                    <label class="text-[10px] text-slate-500 block mb-1">รออนุมัติ</label>
                                    <input type="number" step="0.01" class="w-full bg-slate-800/50 border border-slate-700 rounded px-2 py-1.5 text-right text-xs text-yellow-200 focus:border-yellow-500 focus:outline-none inp-pending" value="<?= $tData['pending'] ?>" placeholder="-">
                                </div>
                                <div>
                                    <label class="text-[10px] text-slate-500 block mb-1">PO</label>
                                    <input type="number" step="0.01" class="w-full bg-slate-800/50 border border-slate-700 rounded px-2 py-1.5 text-right text-xs text-blue-200 focus:border-blue-500 focus:outline-none inp-po" value="<?= $tData['po'] ?>" placeholder="-">
                                </div>
                            </div>
                            <div class="flex justify-between items-center pt-2 border-t border-orange-500/10">
                                <span class="text-[10px] text-slate-500">รวมใช้ไป</span>
                                <span class="text-sm font-bold font-mono text-orange-300 val-total-used">0.00</span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php } ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    // 1. Accordion Logic
    document.querySelectorAll('.section-toggle').forEach(btn => {
        btn.addEventListener('click', () => {
            const wrapper = btn.closest('.section-wrapper');
            const content = wrapper.querySelector('.section-content');
            const arrow = wrapper.querySelector('.arrow-icon');
            const iconWrapper = wrapper.querySelector('.icon-wrapper');
            const titleText = wrapper.querySelector('.title-text');
            
            const isOpen = content.style.maxHeight && content.style.maxHeight !== '0px';
            
            if (isOpen) {
                content.style.maxHeight = '0px';
                content.style.opacity = '0';
                arrow.style.transform = 'rotate(0deg)';
                btn.classList.remove('bg-indigo-500/5');
                iconWrapper.classList.remove('bg-indigo-500/20', 'text-indigo-300');
                iconWrapper.classList.add('bg-slate-800', 'text-slate-400');
                titleText.classList.remove('text-indigo-200');
                titleText.classList.add('text-slate-300');
            } else {
                content.style.maxHeight = '5000px';
                content.style.opacity = '1';
                arrow.style.transform = 'rotate(180deg)';
                btn.classList.add('bg-indigo-500/5');
                iconWrapper.classList.remove('bg-slate-800', 'text-slate-400');
                iconWrapper.classList.add('bg-indigo-500/20', 'text-indigo-300');
                titleText.classList.remove('text-slate-300');
                titleText.classList.add('text-indigo-200');
            }
        });
    });

    // 2. Calculation Logic
    const formatMoney = (n) => Number(n).toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    const updateCard = (card) => {
        const getVal = (cls) => parseFloat(card.querySelector(`.${cls}`)?.value) || 0;
        
        const alloc = getVal('inp-allocated');
        const trans = getVal('inp-transfer');
        const disb = getVal('inp-disbursed');
        const pend = getVal('inp-pending');
        const po = getVal('inp-po');
        
        const totalBudget = alloc + trans;
        const totalUsed = disb + pend + po;
        const remaining = totalBudget - totalUsed;
        
        const totalBudgetEl = card.querySelector('.val-total-budget');
        const totalUsedEl = card.querySelector('.val-total-used');
        if (totalBudgetEl) totalBudgetEl.textContent = formatMoney(totalBudget);
        if (totalUsedEl) totalUsedEl.textContent = formatMoney(totalUsed);
        
        const badge = card.querySelector('.item-balance-badge');
        if (badge) {
            badge.textContent = `Balance: ${formatMoney(remaining)}`;
            badge.className = `px-3 py-1 rounded-full text-xs font-bold font-mono tracking-wide border item-balance-badge ${remaining < 0 ? 'bg-red-500/10 text-red-400 border-red-500/20' : 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20'}`;
        }
        
        return remaining;
    };

    const updateAll = () => {
        let grandTotal = 0;
        
        document.querySelectorAll('.section-wrapper').forEach(section => {
            let sectionTotal = 0;
            section.querySelectorAll('.tracking-card').forEach(card => {
                sectionTotal += updateCard(card);
            });
            
            const secRem = section.querySelector('.section-remaining');
            if (secRem) {
                secRem.textContent = formatMoney(sectionTotal);
                secRem.className = `text-sm font-mono font-bold section-remaining ${sectionTotal < 0 ? 'text-red-400' : 'text-emerald-400'}`;
            }
            
            grandTotal += sectionTotal;
        });
        
        document.getElementById('grand-total-remaining').textContent = formatMoney(grandTotal);
    };

    document.querySelectorAll('input[type="number"]').forEach(inp => {
        inp.addEventListener('input', updateAll);
    });

    updateAll();

    // 3. Save Logic
    document.getElementById('btn-save').addEventListener('click', async () => {
        const btn = document.getElementById('btn-save');
        const status = document.getElementById('save-status');
        const originalText = btn.innerHTML;
        
        btn.disabled = true;
        btn.innerHTML = '<?= getIcon("Spinner", 18, "animate-spin") ?> Saving...';
        
        const payload = {
            fiscalYear: document.getElementById('fiscal_year').value,
            items: {}
        };
        
        document.querySelectorAll('.tracking-card').forEach(card => {
            const id = card.dataset.itemId;
            const getVal = (cls) => parseFloat(card.querySelector(`.${cls}`)?.value) || 0;
            
            payload.items[id] = {
                allocated: getVal('inp-allocated'),
                transfer: getVal('inp-transfer'),
                disbursed: getVal('inp-disbursed'),
                pending: getVal('inp-pending'),
                po: getVal('inp-po')
            };
        });
        
        try {
            const res = await fetch('<?= \App\Core\View::url('/budgets/tracking/save') ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            
            const data = await res.json();
            
            if (data.success) {
                status.innerHTML = '<span class="text-emerald-400 flex items-center gap-1"><?= getIcon("CheckCircle", 14) ?> Saved</span>';
                status.classList.remove('hidden');
                setTimeout(() => status.classList.add('hidden'), 3000);
            } else {
                throw new Error(data.message);
            }
        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Save Failed',
                text: err.message,
                background: '#0f172a',
                color: '#f1f5f9'
            });
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    });
});
</script>
