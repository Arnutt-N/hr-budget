<?php
    $isEdit = $action === 'edit';
    $formUrl = $isEdit ? \App\Core\View::url("/budgets/{$budget['id']}") : \App\Core\View::url('/budgets');
    $pageTitle = $isEdit ? 'แก้ไขงบประมาณ' : 'เพิ่มงบประมาณ';
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
    ::-webkit-scrollbar { width: 6px; height: 6px; }
    ::-webkit-scrollbar-track { background: #0f172a; }
    ::-webkit-scrollbar-thumb { background: #334155; border-radius: 3px; }
    ::-webkit-scrollbar-thumb:hover { background: #475569; }
</style>

<!-- Ambient Background -->
<div class="fixed inset-0 pointer-events-none overflow-hidden -z-10">
    <div class="absolute top-0 left-1/4 w-[600px] h-[600px] bg-indigo-900/10 rounded-full blur-[120px]"></div>
    <div class="absolute bottom-0 right-1/4 w-[500px] h-[500px] bg-cyan-900/10 rounded-full blur-[100px]"></div>
</div>

<div class="max-w-4xl mx-auto px-4 pb-20 animate-fade-in">
    <!-- Header -->
    <header class="mb-10 border-b border-slate-800/60 pb-8 flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
            <a href="<?= \App\Core\View::url('/budgets/list?year=' . $fiscalYear) ?>" class="inline-flex items-center text-slate-400 hover:text-white mb-4 transition-colors text-sm">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
                กลับไปรายการงบประมาณ
            </a>
            <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-violet-600 rounded-2xl flex items-center justify-center shadow-lg shadow-indigo-500/20 ring-1 ring-white/10">
                <i data-lucide="<?= $isEdit ? 'square-pen' : 'plus-circle' ?>" class="w-8 h-8 text-white"></i>
            </div>
            <div class="flex items-center gap-3 mb-2">
                <h1 class="text-3xl font-bold text-white tracking-tight"><?= $pageTitle ?></h1>
            </div>
            <p class="text-slate-400 max-w-lg">กรอกข้อมูลรายการงบประมาณ ผลเบิกจ่าย และสถานะคงเหลือ</p>
        </div>
        
        <!-- Status Badge (Only for Edit) -->
        <?php if ($isEdit): ?>
        <div class="px-4 py-2 rounded-full bg-slate-800/50 border border-slate-700/50 backdrop-blur-sm">
            <span class="text-xs font-medium text-slate-300 uppercase tracking-widest">สถานะ: </span>
            <span class="text-sm font-bold text-emerald-400 ml-1 uppercase"><?= $budget['status'] ?? 'Draft' ?></span>
        </div>
        <?php endif; ?>
    </header>

    <form method="POST" action="<?= $formUrl ?>" id="budget-form">
        <?= \App\Core\View::csrf() ?>
        
        <div class="space-y-8">
            
            <!-- Section 1: Basic Info -->
            <div class="border border-slate-800 bg-[#0f172a]/20 rounded-2xl overflow-hidden backdrop-blur-sm">
                <div class="p-4 border-b border-slate-800/50 bg-slate-800/30 flex items-center gap-3">
                    <i data-lucide="info" class="w-5 h-5 text-indigo-400"></i>
                    <h2 class="font-semibold text-slate-200">ข้อมูลพื้นฐาน</h2>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Fiscal Year -->
                    <div class="space-y-2">
                        <label class="text-xs uppercase tracking-wider text-slate-500 font-semibold">
                            ปีงบประมาณ <span class="text-red-400">*</span>
                        </label>
                        <div class="relative">
                            <select name="fiscal_year" id="fiscal_year" class="w-full bg-[#0f172a] border border-slate-700 rounded-xl pl-10 pr-4 py-3 text-sm text-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500/50 transition-all appearance-none" required>
                                <?php foreach ($fiscalYears as $fy): ?>
                                <option value="<?= $fy['value'] ?>" 
                                    <?= ($budget['fiscal_year'] ?? $fiscalYear) == $fy['value'] ? 'selected' : '' ?>
                                    <?= $fy['is_closed'] ? 'disabled' : '' ?>>
                                    <?= htmlspecialchars($fy['label']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <i data-lucide="calendar" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500 w-5 h-5"></i>
                            <i data-lucide="chevron-down" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-500 pointer-events-none w-4 h-4"></i>
                        </div>
                    </div>

                    <!-- Category -->
                    <div class="space-y-2">
                        <label class="text-xs uppercase tracking-wider text-slate-500 font-semibold">
                            หมวดหมู่งบประมาณ <span class="text-red-400">*</span>
                        </label>
                        <div class="relative">
                            <select name="category_id" id="category_id" class="w-full bg-[#0f172a] border border-slate-700 rounded-xl pl-10 pr-4 py-3 text-sm text-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500/50 transition-all appearance-none" required>
                                <option value="">-- เลือกหมวดหมู่ --</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= ($budget['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?> (<?= $cat['code'] ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <i data-lucide="tag" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500 w-5 h-5"></i>
                            <i data-lucide="chevron-down" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-500 pointer-events-none w-4 h-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 2: Budget Allocation -->
            <div class="border border-slate-800 bg-[#0f172a]/20 rounded-2xl overflow-hidden backdrop-blur-sm">
                <div class="p-4 border-b border-slate-800/50 bg-slate-800/30 flex items-center gap-3">
                    <i data-lucide="coins" class="w-5 h-5 text-emerald-400"></i>
                    <h2 class="font-semibold text-slate-200">การจัดสรรงบประมาณ</h2>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Allocated Amount -->
                    <div class="space-y-2">
                        <label class="text-xs uppercase tracking-wider text-slate-500 font-semibold">
                            งบประมาณจัดสรร (บาท) <span class="text-red-400">*</span>
                        </label>
                        <div class="relative group">
                            <input type="number" name="allocated_amount" id="allocated_amount" 
                                class="w-full bg-[#0f172a] border border-slate-700 rounded-xl pl-10 pr-4 py-3 text-white font-mono placeholder-slate-700 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/50 transition-all"
                                step="0.01" min="0" value="<?= $budget['allocated_amount'] ?? 0 ?>" placeholder="0.00" required>
                            <i data-lucide="banknote" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-emerald-500/70 group-focus-within:text-emerald-500 transition-colors w-5 h-5"></i>
                        </div>
                    </div>

                    <!-- Transfer Allocation -->
                    <div class="space-y-2">
                        <label class="text-xs uppercase tracking-wider text-slate-500 font-semibold">
                            โอนเปลี่ยนแปลง (+/-)
                        </label>
                        <div class="relative group">
                            <input type="number" name="transfer_allocation" id="transfer_allocation" 
                                class="w-full bg-[#0f172a] border border-slate-700 rounded-xl pl-10 pr-4 py-3 text-white font-mono placeholder-slate-700 focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500/50 transition-all"
                                step="0.01" value="<?= $budget['transfer_allocation'] ?? 0 ?>" placeholder="0.00">
                            <i data-lucide="arrow-left-right" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-yellow-500/70 group-focus-within:text-yellow-500 transition-colors w-5 h-5"></i>
                        </div>
                        <p class="text-[10px] text-slate-500">*ค่าบวก = ขอลด/โอนออก, ค่าลบ = ขอเพิ่ม/รับโอน</p>
                    </div>
                </div>
            </div>

            <!-- Section 3: Monthly Records -->
            <div class="border border-slate-800 bg-[#0f172a]/20 rounded-2xl overflow-hidden backdrop-blur-sm">
                <div class="p-4 border-b border-slate-800/50 bg-slate-800/30 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <i data-lucide="trending-up" class="w-5 h-5 text-cyan-400"></i>
                        <h2 class="font-semibold text-slate-200">บันทึกผลการเบิกจ่าย (ประจำเดือน)</h2>
                    </div>
                    <div class="text-xs text-slate-400 bg-slate-900/50 px-3 py-1 rounded-full border border-slate-700">
                        <?= date('d M Y') ?>
                    </div>
                </div>
                
                <div class="p-6">
                    <!-- Date & Period Row -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div class="space-y-2">
                            <label class="text-xs uppercase tracking-wider text-slate-500 font-semibold">วันที่บันทึก</label>
                            <input type="date" name="record_date" id="record_date" 
                                class="w-full bg-[#0f172a] border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:border-cyan-500 transition-all"
                                value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs uppercase tracking-wider text-slate-500 font-semibold">ช่วงเวลา</label>
                            <div class="relative">
                                <select name="record_period" class="w-full bg-[#0f172a] border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:border-cyan-500 appearance-none">
                                    <option value="beginning">ต้นเดือน (1-10)</option>
                                    <option value="mid">กลางเดือน (11-20)</option>
                                    <option value="end">สิ้นเดือน (21-สิ้นเดือน)</option>
                                </select>
                                <i data-lucide="chevron-down" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-500 pointer-events-none w-4 h-4"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Financial Inputs -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <!-- Spent Amount -->
                        <div class="space-y-2">
                            <label class="text-xs uppercase tracking-wider text-slate-500 font-semibold">เบิกจ่ายจริง</label>
                            <div class="relative group">
                                <input type="number" name="spent_amount" id="spent_amount" 
                                    class="w-full bg-[#0f172a] border border-slate-700 rounded-xl pl-10 pr-4 py-3 text-white font-mono placeholder-slate-700 focus:border-orange-500 focus:ring-1 focus:ring-orange-500/50 transition-all"
                                    step="0.01" min="0" value="<?= $budget['current_spent'] ?? ($budget['spent_amount'] ?? 0) ?>" placeholder="0.00">
                                <i data-lucide="wallet" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-orange-500/70 w-5 h-5"></i>
                            </div>
                        </div>

                        <!-- Request Amount -->
                        <div class="space-y-2">
                            <label class="text-xs uppercase tracking-wider text-slate-500 font-semibold">ขออนุมัติวงเงิน</label>
                            <div class="relative group">
                                <input type="number" name="request_amount" id="request_amount" 
                                    class="w-full bg-[#0f172a] border border-slate-700 rounded-xl pl-10 pr-4 py-3 text-white font-mono placeholder-slate-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500/50 transition-all"
                                    step="0.01" min="0" value="<?= $budget['request_amount'] ?? 0 ?>" placeholder="0.00">
                                <i data-lucide="file-text" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-blue-500/70 w-5 h-5"></i>
                            </div>
                        </div>

                        <!-- PO Amount -->
                        <div class="space-y-2">
                            <label class="text-xs uppercase tracking-wider text-slate-500 font-semibold">ก่อหนี้ผูกพัน (PO)</label>
                            <div class="relative group">
                                <input type="number" name="po_amount" id="po_amount" 
                                    class="w-full bg-[#0f172a] border border-slate-700 rounded-xl pl-10 pr-4 py-3 text-white font-mono placeholder-slate-700 focus:border-purple-500 focus:ring-1 focus:ring-purple-500/50 transition-all"
                                    step="0.01" min="0" value="<?= $budget['po_amount'] ?? 0 ?>" placeholder="0.00">
                                <i data-lucide="receipt" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-purple-500/70 w-5 h-5"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Summary Card -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-slate-900/50 rounded-xl p-5 border border-slate-800">
                        <div class="flex flex-col">
                            <span class="text-xs text-slate-500 uppercase tracking-wider">ใช้ไปแล้วทั้งสิ้น (Committed)</span>
                            <span class="text-2xl font-mono font-bold text-slate-200 mt-1" id="total-committed-display">0.00</span>
                        </div>
                        <div class="flex flex-col md:text-right">
                             <span class="text-xs text-slate-500 uppercase tracking-wider">คงเหลือสุทธิ (Net Remaining)</span>
                             <span class="text-2xl font-mono font-bold text-emerald-400 mt-1" id="net-remaining-display">0.00</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Section 4: Extra Info -->
            <div class="border border-slate-800 bg-[#0f172a]/20 rounded-2xl overflow-hidden backdrop-blur-sm">
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                         <label class="text-xs uppercase tracking-wider text-slate-500 font-semibold">สถานะรายการ</label>
                         <select name="status" id="status" class="w-full bg-[#0f172a] border border-slate-700 rounded-xl px-4 py-3 text-sm text-white focus:border-indigo-500 appearance-none">
                            <option value="draft" <?= ($budget['status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>>ร่าง (Draft)</option>
                            <option value="submitted" <?= ($budget['status'] ?? '') === 'submitted' ? 'selected' : '' ?>>รออนุมัติ (Submitted)</option>
                            <option value="approved" <?= ($budget['status'] ?? '') === 'approved' ? 'selected' : '' ?>>อนุมัติ (Approved)</option>
                            <option value="rejected" <?= ($budget['status'] ?? '') === 'rejected' ? 'selected' : '' ?>>ปฏิเสธ (Rejected)</option>
                        </select>
                    </div>
                    
                    <div class="space-y-2">
                        <label class="text-xs uppercase tracking-wider text-slate-500 font-semibold">หมายเหตุเพิ่มเติม</label>
                        <textarea name="notes" id="notes" rows="2" 
                            class="w-full bg-[#0f172a] border border-slate-700 rounded-xl px-4 py-3 text-sm text-slate-300 focus:border-indigo-500 placeholder-slate-700"
                            placeholder="ระบุหมายเหตุ (ถ้ามี)"><?= htmlspecialchars($budget['notes'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row justify-end gap-4 pt-4">
                <a href="<?= \App\Core\View::url('/budgets/list?year=' . $fiscalYear) ?>" 
                    class="px-8 py-3 rounded-xl border border-slate-700 text-slate-400 hover:text-white hover:bg-slate-800 transition-colors text-center font-medium">
                    ยกเลิก
                </a>
                <button type="submit" 
                    class="px-8 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold shadow-lg shadow-indigo-600/20 transition-all active:scale-95 flex items-center justify-center gap-2">
                    <i data-lucide="save" class="w-5 h-5"></i>
                    <span><?= $isEdit ? 'บันทึกการแก้ไข' : 'บันทึกรายการ' ?></span>
                </button>
            </div>
            
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Utils
    const formatCurrency = (amount) => {
        return new Intl.NumberFormat('th-TH', { 
            style: 'decimal', 
            minimumFractionDigits: 2, 
            maximumFractionDigits: 2 
        }).format(amount);
    };

    // Auto-calculate budget stats
    const inputs = document.querySelectorAll('#allocated_amount, #transfer_allocation, #spent_amount, #request_amount, #po_amount');
    inputs.forEach(el => el.addEventListener('input', calculateStats));
    
    function calculateStats() {
        const allocated = parseFloat(document.getElementById('allocated_amount').value) || 0;
        const transferAlloc = parseFloat(document.getElementById('transfer_allocation').value) || 0;
        const spent = parseFloat(document.getElementById('spent_amount').value) || 0;
        const request = parseFloat(document.getElementById('request_amount').value) || 0;
        const po = parseFloat(document.getElementById('po_amount').value) || 0;
        
        // Formulas
        const remainingAfterTransfer = allocated - transferAlloc;
        const totalCommitted = spent + request + po;
        const netRemaining = remainingAfterTransfer - totalCommitted;
        
        document.getElementById('total-committed-display').textContent = formatCurrency(totalCommitted);
        
        const netDisplay = document.getElementById('net-remaining-display');
        netDisplay.textContent = formatCurrency(netRemaining);
        
        if (netRemaining < 0) {
            netDisplay.classList.remove('text-emerald-400');
            netDisplay.classList.add('text-red-400');
        } else {
            netDisplay.classList.remove('text-red-400');
            netDisplay.classList.add('text-emerald-400');
        }
    }
    
    // Initial calculation
    calculateStats();
    
    // Form validation
    document.getElementById('budget-form').addEventListener('submit', function(e) {
        const allocated = parseFloat(document.getElementById('allocated_amount').value) || 0;
        if (allocated < 0) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'ข้อมูลไม่ถูกต้อง',
                text: 'งบประมาณจัดสรรต้องมากกว่าหรือเท่ากับ 0',
                background: '#0f172a',
                color: '#f1f5f9',
                confirmButtonColor: '#4f46e5'
            });
        }
    });
</script>
