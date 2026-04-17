<?php
/**
 * Admin Fiscal Years Index View
 */
?>
<div class="mb-6 flex items-center justify-between">
    <h2 class="text-2xl font-bold text-white flex items-center gap-3">
        <i data-lucide="calendar-range" class="w-8 h-8 text-primary-500"></i>
        จัดการปีงบประมาณ
    </h2>
    <button type="button" onclick="openCreateModal()" class="btn btn-primary">
        <i data-lucide="circle-plus" class="w-4 h-4"></i>
        เพิ่มปีงบประมาณ
    </button>
</div>

<div class="bg-slate-900/50 backdrop-blur-sm border border-slate-700 rounded-xl shadow-2xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-slate-800/80 backdrop-blur-sm">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">
                        ปีงบประมาณ (พ.ศ.)
                    </th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">
                        ระยะเวลา
                    </th>
                    <th class="px-6 py-4 text-center text-xs font-semibold text-slate-400 uppercase tracking-wider">
                        สถานะ
                    </th>
                    <th class="px-6 py-4 text-right text-xs font-semibold text-slate-400 uppercase tracking-wider">
                        จัดการ
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-dark-border">
                <?php if (empty($years)): ?>
                <tr>
                    <td colspan="4" class="px-6 py-12 text-center text-slate-500">
                        <i data-lucide="calendar-x" class="w-12 h-12 mx-auto mb-3 opacity-50"></i>
                        <p class="text-sm">ไม่พบข้อมูลปีงบประมาณ</p>
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($years as $year): ?>
                    <tr class="hover:bg-white/5 transition-colors <?= $year['is_closed'] ? 'opacity-60' : '' ?>">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <span class="text-lg font-semibold text-white"><?= htmlspecialchars($year['year']) ?></span>
                                <?php if ($year['is_current']): ?>
                                    <span class="badge badge-blue gap-1.5 py-1 px-2.5">
                                        <i data-lucide="star" class="w-3.5 h-3.5 fill-current"></i>
                                        ปัจจุบัน
                                    </span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-slate-300">
                                <i data-lucide="calendar" class="w-4 h-4 inline mr-1"></i>
                                <?= date('d/m/Y', strtotime($year['start_date'])) ?> - 
                                <?= date('d/m/Y', strtotime($year['end_date'])) ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <?php if ($year['is_closed']): ?>
                                <span class="badge badge-amber gap-1.5 py-1 px-2.5">
                                    <i data-lucide="lock" class="w-3.5 h-3.5"></i>
                                    ปิดแล้ว
                                </span>
                            <?php else: ?>
                                <span class="badge badge-green gap-1.5 py-1 px-2.5">
                                    <i data-lucide="unlock" class="w-3.5 h-3.5"></i>
                                    เปิดอยู่
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <!-- Set Current -->
                                <?php if (!$year['is_current']): ?>
                                <form method="POST" action="<?= \App\Core\View::url("/admin/fiscal-years/{$year['id']}/set-current") ?>" class="inline">
                                    <?= \App\Core\View::csrf() ?>
                                    <button type="submit" class="btn-icon btn-ghost-primary" title="ตั้งเป็นปีปัจจุบัน">
                                        <i data-lucide="star" class="w-4 h-4"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                                
                                <!-- Toggle Closed -->
                                <form method="POST" action="<?= \App\Core\View::url("/admin/fiscal-years/{$year['id']}/toggle-closed") ?>" class="inline">
                                    <?= \App\Core\View::csrf() ?>
                                    <button type="submit" class="btn-icon btn-ghost-warning" title="<?= $year['is_closed'] ? 'เปิดปี' : 'ปิดปี' ?>">
                                        <i data-lucide="<?= $year['is_closed'] ? 'unlock' : 'lock' ?>" class="w-4 h-4"></i>
                                    </button>
                                </form>
                                
                                <!-- Edit -->
                                <button type="button" 
                                        onclick="openEditModal(<?= $year['id'] ?>, <?= $year['year'] ?>, <?= $year['is_current'] ? 1 : 0 ?>, <?= $year['is_closed'] ? 1 : 0 ?>)"
                                        class="btn-icon btn-ghost-primary" title="แก้ไข">
                                    <i data-lucide="pencil" class="w-4 h-4"></i>
                                </button>

                                
                                <!-- Delete -->
                                <button type="button" onclick="confirmDelete(<?= $year['id'] ?>)" 
                                        class="btn-icon text-red-400 hover:text-red-300 hover:bg-red-900/20" title="ลบ">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                                <form id="delete-form-<?= $year['id'] ?>" method="POST" 
                                      action="<?= \App\Core\View::url("/admin/fiscal-years/{$year['id']}/delete") ?>" 
                                      style="display: none;">
                                    <?= \App\Core\View::csrf() ?>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Create Modal -->
<div id="createModal" class="modal-overlay hidden">
    <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm transition-opacity modal-backdrop"></div>
    
    <div class="fixed inset-0 overflow-y-auto z-50">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="modal-card relative bg-slate-800/95 backdrop-blur-md rounded-2xl shadow-2xl border border-slate-700/50 max-w-lg w-full transform transition-all p-0 overflow-hidden">
                
                <!-- Header -->
                <div class="flex items-center justify-between px-8 py-6">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-primary-500/10 flex items-center justify-center">
                            <i data-lucide="calendar-plus" class="w-5 h-5 text-primary-400"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-white">เพิ่มปีงบประมาณ</h3>
                    </div>
                    <button type="button" onclick="closeCreateModal()" class="text-slate-400 hover:text-slate-200 transition-colors">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <!-- Form Body -->
                <form id="createForm" method="POST" action="<?= \App\Core\View::url('/admin/fiscal-years/store') ?>">
                    <?= \App\Core\View::csrf() ?>
                    
                    <div class="px-8 py-6 space-y-4">
                        <!-- Year Input -->
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">
                                ปีงบประมาณ (พ.ศ.) <span class="text-red-400">*</span>
                            </label>
                            <input type="number" 
                                   name="year" 
                                   id="modalYearInput"
                                   class="input w-full bg-slate-900/50"
                                   placeholder="เช่น 2570"
                                   min="2500"
                                   max="2600"
                                   required>
                            <p id="modalDateHint" class="mt-1 text-xs text-slate-500">
                                <i data-lucide="info" class="w-3 h-3 inline mr-1"></i>
                                ช่วงเวลา: 1 ต.ค. XXXX - 30 ก.ย. XXXX (คำนวณอัตโนมัติ)
                            </p>
                        </div>

                        <!-- Checkboxes -->
                        <div class="space-y-3">
                            <label class="flex items-center gap-3 p-3 rounded-lg bg-slate-900/50 border border-slate-700/50 cursor-pointer hover:bg-slate-800/70 transition-colors">
                                <input type="checkbox" 
                                       name="is_current" 
                                       value="1"
                                       class="w-4 h-4 rounded border-slate-600 bg-slate-900 text-primary-600 focus:ring-primary-500 focus:ring-offset-slate-900">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 text-sm">
                                        <i data-lucide="star" class="w-4 h-4 text-blue-400"></i>
                                        <span class="font-medium text-white">ตั้งเป็นปีปัจจุบัน</span>
                                    </div>
                                </div>
                            </label>

                            <label class="flex items-center gap-3 p-3 rounded-lg bg-slate-900/50 border border-slate-700/50 cursor-pointer hover:bg-slate-800/70 transition-colors">
                                <input type="checkbox" 
                                       name="is_closed" 
                                       value="1"
                                       class="w-4 h-4 rounded border-slate-600 bg-slate-900 text-amber-600 focus:ring-amber-500 focus:ring-offset-slate-900">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 text-sm">
                                        <i data-lucide="lock" class="w-4 h-4 text-amber-400"></i>
                                        <span class="font-medium text-white">ปิดปีงบประมาณ</span>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="flex gap-3 px-8 py-6">
                        <button type="button" onclick="closeCreateModal()" class="flex-1 btn btn-secondary">
                            <i data-lucide="x" class="w-4 h-4"></i>
                            ยกเลิก
                        </button>
                        <button type="submit" class="flex-1 btn btn-primary">
                            <i data-lucide="save" class="w-4 h-4"></i>
                            บันทึก
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal-overlay hidden">
    <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm transition-opacity modal-backdrop"></div>
    
    <div class="fixed inset-0 overflow-y-auto z-50">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="modal-card relative bg-slate-800/95 backdrop-blur-md rounded-2xl shadow-2xl border border-slate-700/50 max-w-lg w-full transform transition-all p-0 overflow-hidden">
                
                <!-- Header -->
                <div class="flex items-center justify-between px-8 py-6">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-amber-500/10 flex items-center justify-center">
                            <i data-lucide="pencil" class="w-5 h-5 text-amber-400"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-white">แก้ไขปีงบประมาณ</h3>
                    </div>
                    <button type="button" onclick="closeEditModal()" class="text-slate-400 hover:text-slate-200 transition-colors">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <!-- Form Body -->
                <form id="editForm" method="POST">
                    <?= \App\Core\View::csrf() ?>
                    <input type="hidden" id="editYearId" name="id">
                    
                    <div class="px-8 py-6 space-y-4">
                        <!-- Year Input -->
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">
                                ปีงบประมาณ (พ.ศ.) <span class="text-red-400">*</span>
                            </label>
                            <input type="number" 
                                   name="year" 
                                   id="editYearInput"
                                   class="input w-full bg-slate-900/50"
                                   placeholder="เช่น 2570"
                                   min="2500"
                                   max="2600"
                                   required>
                            <p id="editDateHint" class="mt-1 text-xs text-slate-500">
                                <i data-lucide="info" class="w-3 h-3 inline mr-1"></i>
                                ช่วงเวลา: 1 ต.ค. XXXX - 30 ก.ย. XXXX (คำนวณอัตโนมัติ)
                            </p>
                        </div>

                        <!-- Checkboxes -->
                        <div class="space-y-3">
                            <label class="flex items-center gap-3 p-3 rounded-lg bg-slate-900/50 border border-slate-700/50 cursor-pointer hover:bg-slate-800/70 transition-colors">
                                <input type="checkbox" 
                                       name="is_current" 
                                       id="editIsCurrent"
                                       value="1"
                                       class="w-4 h-4 rounded border-slate-600 bg-slate-900 text-primary-600 focus:ring-primary-500 focus:ring-offset-slate-900">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 text-sm">
                                        <i data-lucide="star" class="w-4 h-4 text-blue-400"></i>
                                        <span class="font-medium text-white">ตั้งเป็นปีปัจจุบัน</span>
                                    </div>
                                </div>
                            </label>

                            <label class="flex items-center gap-3 p-3 rounded-lg bg-slate-900/50 border border-slate-700/50 cursor-pointer hover:bg-slate-800/70 transition-colors">
                                <input type="checkbox" 
                                       name="is_closed" 
                                       id="editIsClosed"
                                       value="1"
                                       class="w-4 h-4 rounded border-slate-600 bg-slate-900 text-amber-600 focus:ring-amber-500 focus:ring-offset-slate-900">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 text-sm">
                                        <i data-lucide="lock" class="w-4 h-4 text-amber-400"></i>
                                        <span class="font-medium text-white">ปิดปีงบประมาณ</span>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="flex gap-3 px-8 py-6">
                        <button type="button" onclick="closeEditModal()" class="flex-1 btn btn-secondary">
                            <i data-lucide="x" class="w-4 h-4"></i>
                            ยกเลิก
                        </button>
                        <button type="submit" class="flex-1 btn btn-primary">
                            <i data-lucide="save" class="w-4 h-4"></i>
                            บันทึกการแก้ไข
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

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
    color: #f59e0b; /* amber-500 */
}

/* Hide number input arrows */
input[type=number]::-webkit-inner-spin-button, 
input[type=number]::-webkit-outer-spin-button { 
    -webkit-appearance: none; 
    margin: 0; 
}
input[type=number] {
    -moz-appearance: textfield;
}
</style>

<script>
function confirmDelete(id) {
    if (confirm('คุณต้องการลบปีงบประมาณนี้ใช่หรือไม่?\n\n⚠️ การลบจะส่งผลต่อข้อมูลที่เกี่ยวข้อง')) {
        document.getElementById('delete-form-' + id).submit();
    }
}

function openCreateModal() {
    const modal = document.getElementById('createModal');
    modal.classList.remove('hidden');
    setTimeout(() => {
        modal.classList.add('show');
        document.getElementById('modalYearInput').focus();
    }, 10);
    lucide.createIcons();
}

function closeCreateModal() {
    const modal = document.getElementById('createModal');
    modal.classList.remove('show');
    modal.classList.add('hide');
    setTimeout(() => {
        modal.classList.add('hidden');
        modal.classList.remove('hide');
        document.getElementById('createForm').reset();
    }, 200);
}

function openEditModal(id, year, isCurrent, isClosed) {
    const modal = document.getElementById('editModal');
    const form = document.getElementById('editForm');
    
    // Set form action
    form.action = '<?= \App\Core\View::url('/admin/fiscal-years') ?>/' + id + '/update';
    
    // Fill form data
    document.getElementById('editYearId').value = id;
    document.getElementById('editYearInput').value = year;
    document.getElementById('editIsCurrent').checked = isCurrent === 1;
    document.getElementById('editIsClosed').checked = isClosed === 1;
    
    // Update date hint
    updateDateHint('editYearInput', 'editDateHint');
    
    // Show modal
    modal.classList.remove('hidden');
    setTimeout(() => {
        modal.classList.add('show');
        document.getElementById('editYearInput').focus();
    }, 10);
    lucide.createIcons();
}

function closeEditModal() {
    const modal = document.getElementById('editModal');
    modal.classList.remove('show');
    modal.classList.add('hide');
    setTimeout(() => {
        modal.classList.add('hidden');
        modal.classList.remove('hide');
        document.getElementById('editForm').reset();
    }, 200);
}

function updateDateHint(inputId, hintId) {
    const yearInput = document.getElementById(inputId);
    const dateHint = document.getElementById(hintId);
    const yearBE = parseInt(yearInput.value);
    
    if (yearBE >= 2500 && yearBE <= 2600) {
        const startYear = yearBE - 544;
        const endYear = yearBE - 543;
        dateHint.innerHTML = `<i data-lucide="info" class="w-3 h-3 inline mr-1"></i> ช่วงเวลา: 1 ต.ค. ${startYear} - 30 ก.ย. ${endYear} (คำนวณอัตโนมัติ)`;
        lucide.createIcons();
    }
}

// Prevent mouse scroll on number inputs
function preventMouseScroll() {
    const numberInputs = document.querySelectorAll('input[type="number"]');
    numberInputs.forEach(input => {
        input.addEventListener('wheel', (e) => {
            e.preventDefault();
            input.blur(); // Remove focus to prevent accidental changes
        }, { passive: false });
        
        // Prevent up/down arrow keys from changing value
        input.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowUp' || e.key === 'ArrowDown') {
                e.preventDefault();
            }
        });
    });
}

// Auto-update date hints
document.addEventListener('DOMContentLoaded', () => {
    lucide.createIcons();
    preventMouseScroll();
    
    // Create modal year input
    const createYearInput = document.getElementById('modalYearInput');
    if (createYearInput) {
        createYearInput.addEventListener('input', () => {
            updateDateHint('modalYearInput', 'modalDateHint');
        });
    }
    
    // Edit modal year input
    const editYearInput = document.getElementById('editYearInput');
    if (editYearInput) {
        editYearInput.addEventListener('input', () => {
            updateDateHint('editYearInput', 'editDateHint');
        });
    }
    
    // Close modals on backdrop click
    ['createModal', 'editModal'].forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.addEventListener('click', (e) => {
                if (e.target === modal || e.target.classList.contains('modal-backdrop')) {
                    if (modalId === 'createModal') closeCreateModal();
                    if (modalId === 'editModal') closeEditModal();
                }
            });
        }
    });
});
</script>
