<?php
/**
 * Admin Fiscal Years Form (Create/Edit)
 */
$isEdit = ($mode ?? 'create') === 'edit';
$yearData = $year ?? null;
?>
<div class="max-w-2xl mx-auto">
    <!-- Header -->
    <div class="mb-6 flex items-center gap-4">
        <a href="<?= \App\Core\View::url('/admin/fiscal-years') ?>" 
           class="btn-icon btn-ghost-primary">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <h2 class="text-2xl font-bold text-white">
            <?= $isEdit ? 'แก้ไขปีงบประมาณ' : 'เพิ่มปีงบประมาณ' ?>
        </h2>
    </div>

    <!-- Form Card -->
    <div class="bg-dark-card border border-dark-border rounded-xl shadow-lg p-8">
        <form method="POST" action="<?= \App\Core\View::url($isEdit && is_array($yearData) ? "/admin/fiscal-years/{$yearData['id']}/update" : '/admin/fiscal-years/store') ?>">
            <?= \App\Core\View::csrf() ?>
            
            <!-- Year Input -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-slate-300 mb-2">
                    ปีงบประมาณ (พ.ศ.) <span class="text-red-400">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i data-lucide="calendar" class="w-5 h-5 text-slate-500"></i>
                    </div>
                    <input type="number" 
                           name="year" 
                           value="<?= htmlspecialchars(is_array($yearData) ? ($yearData['year'] ?? '') : ($_SESSION['form_data']['year'] ?? '')) ?>"
                           class="input input-icon w-full"
                           placeholder="เช่น 2570"
                           min="2500"
                           max="2600"
                           required>
                </div>
                <p class="mt-1 text-xs text-slate-500">
                    <i data-lucide="info" class="w-3 h-3 inline mr-1"></i>
                    ช่วงเวลา: 1 ต.ค. <?= ($yearData['year'] ?? 0) ? ($yearData['year'] - 544) : 'XXXX' ?> - 30 ก.ย. <?= ($yearData['year'] ?? 0) ? ($yearData['year'] - 543) : 'XXXX' ?> (คำนวณอัตโนมัติ)
                </p>
            </div>

            <!-- Options -->
            <div class="mb-6 space-y-4">
                <!-- Is Current -->
                <label class="flex items-center gap-3 p-4 rounded-lg bg-slate-900/50 border border-slate-700/50 cursor-pointer hover:bg-slate-800/70 transition-colors">
                    <input type="checkbox" 
                           name="is_current" 
                           value="1"
                           <?= ($yearData['is_current'] ?? false) ? 'checked' : '' ?>
                           class="w-5 h-5 rounded border-slate-600 bg-slate-900 text-primary-600 focus:ring-primary-500 focus:ring-offset-slate-900">
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <i data-lucide="star" class="w-4 h-4 text-amber-400"></i>
                            <span class="font-medium text-white">ตั้งเป็นปีงบประมาณปัจจุบัน</span>
                        </div>
                        <p class="text-xs text-slate-400 mt-1">ปีปัจจุบันจะถูกใช้เป็นค่าเริ่มต้นในระบบ</p>
                    </div>
                </label>

                <!-- Is Closed -->
                <label class="flex items-center gap-3 p-4 rounded-lg bg-slate-900/50 border border-slate-700/50 cursor-pointer hover:bg-slate-800/70 transition-colors">
                    <input type="checkbox" 
                           name="is_closed" 
                           value="1"
                           <?= ($yearData['is_closed'] ?? false) ? 'checked' : '' ?>
                           class="w-5 h-5 rounded border-slate-600 bg-slate-900 text-red-600 focus:ring-red-500 focus:ring-offset-slate-900">
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <i data-lucide="lock" class="w-4 h-4 text-red-400"></i>
                            <span class="font-medium text-white">ปิดปีงบประมาณ</span>
                        </div>
                        <p class="text-xs text-slate-400 mt-1">ปิดการแก้ไขข้อมูลสำหรับปีงบประมาณนี้</p>
                    </div>
                </label>
            </div>

            <!-- Info Box -->
            <div class="mb-6 p-4 bg-blue-900/20 border border-blue-800/50 rounded-lg">
                <div class="flex gap-3">
                    <i data-lucide="info" class="w-5 h-5 text-blue-400 flex-shrink-0 mt-0.5"></i>
                    <div class="text-sm text-blue-300">
                        <p class="font-medium mb-1">หมายเหตุ</p>
                        <ul class="list-disc list-inside space-y-1 text-blue-300/80">
                            <li>ระบบจะคำนวณวันที่เริ่มต้นและสิ้นสุดอัตโนมัติ</li>
                            <li>ปีงบประมาณไทย: เริ่ม 1 ตุลาคม สิ้นสุด 30 กันยายน</li>
                            <li>สามารถมีปีปัจจุบันได้เพียง 1 ปีเท่านั้น</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3">
                <a href="<?= \App\Core\View::url('/admin/fiscal-years') ?>" class="btn btn-secondary">
                    <i data-lucide="x" class="w-4 h-4"></i>
                    ยกเลิก
                </a>
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <?= $isEdit ? 'บันทึกการแก้ไข' : 'เพิ่มปีงบประมาณ' ?>
                </button>
            </div>
        </form>
    </div>
</div>

<?php
// Clear form data after display
unset($_SESSION['form_data']);
?>

<style>
/* Force dark background for checkboxes */
input[type="checkbox"] {
    background-color: #0f172a !important; /* slate-900 */
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    width: 1.25rem;
    height: 1.25rem;
    border: 1px solid #475569;
    border-radius: 0.25rem;
    cursor: pointer;
    position: relative;
}

input[type="checkbox"]:checked {
    background-color: #0f172a !important;
}

input[type="checkbox"]:checked::before {
    content: '✓';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 1rem;
    font-weight: bold;
}

input[name="is_current"]:checked::before {
    color: #2563eb; /* primary blue */
}

input[name="is_closed"]:checked::before {
    color: #dc2626; /* red */
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    lucide.createIcons();
    
    // Auto-update date range hint
    const yearInput = document.querySelector('input[name="year"]');
    const dateHint = yearInput.closest('.mb-6').querySelector('.text-xs');
    
    yearInput.addEventListener('input', () => {
        const yearBE = parseInt(yearInput.value);
        if (yearBE >= 2500 && yearBE <= 2600) {
            const startYear = yearBE - 544;
            const endYear = yearBE - 543;
            dateHint.innerHTML = `<i data-lucide="info" class="w-3 h-3 inline mr-1"></i> ช่วงเวลา: 1 ต.ค. ${startYear} - 30 ก.ย. ${endYear} (คำนวณอัตโนมัติ)`;
            lucide.createIcons();
        }
    });
});
</script>
