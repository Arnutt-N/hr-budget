<?php
/**
 * Budget Tracking - Disbursement Records List
 * Layout: main
 */

$thaiMonths = ['', 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 
              'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
?>

<div class="space-y-6 animate-fade-in">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-white">รายการบันทึกการเบิกจ่าย</h1>
            <p class="text-dark-muted text-sm mt-1">จัดการข้อมูลการเบิกจ่ายงบประมาณ</p>
        </div>
        <button id="openCreateModal" class="btn btn-primary">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span class="hidden sm:inline">สร้างรายการใหม่</span>
        </button>
    </div>

    <!-- Table Card -->
    <div class="bg-dark-card border border-dark-border rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table w-full whitespace-nowrap">
                <thead>
                    <tr>
                        <th class="w-10 text-center">#</th>
                        <th>ปีงบประมาณ</th>
                        <th>ประจำเดือน</th>
                        <th class="text-center">ครั้งที่</th>
                        <th>กอง</th>
                        <th class="text-center w-48">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($sessions)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-12 text-dark-muted">
                            <i data-lucide="folder-open" class="w-12 h-12 mx-auto mb-3 text-dark-muted"></i>
                            <p class="text-lg">ยังไม่มีรายการเบิกจ่าย</p>
                            <p class="text-sm mt-2">คลิกปุ่ม "สร้างรายการใหม่" เพื่อเริ่มต้น</p>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($sessions as $i => $session): ?>
                        <tr class="hover:bg-dark-border/20 transition-colors">
                            <td class="text-center text-dark-muted"><?= $i + 1 ?></td>
                            <td class="font-medium text-white"><?= $session['fiscal_year'] ?></td>
                            <td>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                    <?= $thaiMonths[$session['record_month']] ?? $session['record_month'] ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-dark-border text-dark-muted">
                                    <?= $session['sequence_number'] ?? 1 ?>
                                </span>
                            </td>
                            <td class="text-white">
                                <?= htmlspecialchars($session['organization_name'] ?? '-') ?>
                            </td>
                            <td class="text-center">
                                <div class="flex justify-center gap-1">
                                    <!-- เรียกดู -->
                                    <a href="<?= \App\Core\View::url('/budgets/tracking/activities?session_id=' . $session['id']) ?>&readonly=1" 
                                       class="btn btn-icon btn-ghost-primary"
                                       title="เรียกดู">
                                        <i data-lucide="eye" class="w-5 h-5"></i>
                                    </a>
                                    <!-- แก้ไข -->
                                    <a href="<?= \App\Core\View::url('/budgets/tracking/activities?session_id=' . $session['id']) ?>" 
                                       class="btn btn-icon btn-ghost-warning"
                                       title="แก้ไข">
                                        <i data-lucide="square-pen" class="w-5 h-5"></i>
                                    </a>
                                    <!-- ลบ -->
                                    <button type="button" 
                                            onclick="confirmDelete(<?= $session['id'] ?>)"
                                            class="btn btn-icon text-red-400 hover:text-red-300 hover:bg-red-400/10"
                                            title="ลบ">
                                        <i data-lucide="trash-2" class="w-5 h-5"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bottom Navigation -->
    <div class="flex justify-start mt-6">
        <a href="<?= \App\Core\View::url('/budgets/list') ?>" class="btn btn-secondary">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>กลับ</span>
        </a>
    </div>
</div>

<!-- Create Session Modal -->
<div id="createModal" class="fixed inset-0 z-50 overflow-y-auto hidden">
    <!-- Backdrop -->
    <div id="modalBackdrop" class="fixed inset-0 bg-black/60 backdrop-blur-sm opacity-0 transition-opacity duration-200"></div>
    
    <!-- Modal Panel -->
    <div class="flex min-h-full items-center justify-center p-4">
        <div id="modalPanel" class="relative w-full max-w-md bg-dark-card border border-dark-border rounded-xl shadow-2xl opacity-0 scale-95 transition-all duration-200">
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between p-4 border-b border-dark-border">
                <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                    <i data-lucide="calendar-plus" class="w-5 h-5 text-primary-500"></i>
                    สร้างรายการเบิกจ่ายใหม่
                </h3>
                <button id="closeModalBtn" class="p-1 rounded hover:bg-dark-border text-dark-muted hover:text-white transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            
            <!-- Modal Body -->
            <form action="<?= \App\Core\View::url('/budgets/tracking/store-session') ?>" method="POST" class="p-6 space-y-5">
                <?= \App\Core\View::csrf() ?>
                
                <div>
                    <label class="block text-sm font-medium text-white mb-2">ปีงบประมาณ</label>
                    <select name="fiscal_year" class="input w-full" required>
                        <?php 
                        // Default to 2569 (year with plans data) instead of currentYear
                        $defaultYear = 2569;
                        foreach ($fiscalYears ?? [] as $year): 
                        ?>
                            <option value="<?= $year['value'] ?? $year ?>" <?= ($year['value'] ?? $year) == $defaultYear ? 'selected' : '' ?>>
                                <?= $year['label'] ?? $year ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-white mb-2">ประจำเดือน</label>
                    <select name="month" class="input w-full" required>
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                            <option value="<?= $m ?>" <?= $m == date('n') ? 'selected' : '' ?>>
                                <?= $thaiMonths[$m] ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <?php if (!empty($isAdmin)): ?>
                <!-- Admin View: มี 2 dropdown - กรม และ กอง -->
                <div>
                    <label class="block text-sm font-medium text-white mb-2 flex items-center gap-1">
                        <i data-lucide="landmark" class="w-4 h-4"></i>กรม
                    </label>
                    <select id="department_select" class="input w-full">
                        <option value="">-- เลือกกรม --</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?= $dept['id'] ?>"><?= htmlspecialchars($dept['name_th'] ?? $dept['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-white mb-2 flex items-center gap-1">
                        <i data-lucide="building-2" class="w-4 h-4"></i>กอง
                    </label>
                    <select name="organization_id" id="organization_select" class="input w-full" required disabled>
                        <option value="">-- กรุณาเลือกกรมก่อน --</option>
                        <?php foreach ($divisions as $div): ?>
                            <option value="<?= $div['id'] ?>" data-parent-id="<?= $div['parent_id'] ?>">
                                <?= htmlspecialchars($div['name_th'] ?? $div['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <script>
                document.getElementById('department_select').addEventListener('change', function() {
                    const deptId = this.value;
                    const orgSelect = document.getElementById('organization_select');
                    const options = orgSelect.querySelectorAll('option[data-parent-id]');
                    
                    if (!deptId) {
                        orgSelect.disabled = true;
                        orgSelect.value = '';
                        // Reset options
                        options.forEach(opt => opt.hidden = true);
                        orgSelect.options[0].text = '-- กรุณาเลือกกรมก่อน --';
                        return;
                    }
                    
                    let count = 0;
                    options.forEach(opt => {
                        if (opt.dataset.parentId == deptId) {
                            opt.hidden = false;
                            count++;
                        } else {
                            opt.hidden = true;
                        }
                    });
                    
                    orgSelect.disabled = false;
                    orgSelect.value = '';
                    orgSelect.options[0].text = count > 0 ? '-- เลือกกอง --' : '-- ไม่มีกองในกรมนี้ --';
                });
                </script>
                
                <?php else: ?>
                <!-- User View: แสดงกองแบบ Read-only -->
                <div>
                    <label class="block text-sm font-medium text-white mb-2 flex items-center gap-1">
                        <i data-lucide="building-2" class="w-4 h-4"></i>กอง (ของคุณ)
                    </label>
                    <div class="w-full px-4 py-2.5 bg-dark-border/50 border border-dark-border rounded-lg text-dark-muted">
                        <?= htmlspecialchars($userOrgName ?? 'หน่วยงานของคุณ') ?>
                        <span class="text-xs text-dark-muted/60 ml-2">(ไม่สามารถเปลี่ยนได้)</span>
                    </div>
                    <input type="hidden" name="organization_id" value="<?= $userOrgId ?? 0 ?>">
                </div>
                <?php endif; ?>
                
                <div class="bg-blue-500/10 border border-blue-500/20 rounded-lg p-4">
                    <div class="flex items-start gap-3">
                        <i data-lucide="info" class="w-5 h-5 text-blue-400 mt-0.5"></i>
                        <div class="text-sm text-blue-300">
                            <?php if (!empty($isAdmin)): ?>
                            ในฐานะผู้ดูแลระบบ คุณสามารถเลือกกองที่ต้องการบันทึกได้
                            <?php else: ?>
                            ระบบจะสร้างรายการสำหรับกองของคุณ
                            <?php endif; ?>
                            <br>โดยใช้วันที่ปัจจุบันเป็นวันที่บันทึก
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="flex gap-3 pt-2">
                    <button type="button" id="cancelModalBtn" class="btn btn-secondary">
                        ยกเลิก
                    </button>
                    <button type="submit" class="btn btn-primary flex-1">
                        ดำเนินการต่อ
                        <i data-lucide="arrow-right" class="w-4 h-4 ml-1"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function() {
    const modal = document.getElementById('createModal');
    const backdrop = document.getElementById('modalBackdrop');
    const panel = document.getElementById('modalPanel');
    const openBtn = document.getElementById('openCreateModal');
    const closeBtn = document.getElementById('closeModalBtn');
    const cancelBtn = document.getElementById('cancelModalBtn');

    function openModal() {
        modal.classList.remove('hidden');
        // Trigger animation after display change
        requestAnimationFrame(() => {
            backdrop.classList.remove('opacity-0');
            backdrop.classList.add('opacity-100');
            panel.classList.remove('opacity-0', 'scale-95');
            panel.classList.add('opacity-100', 'scale-100');
        });
    }

    function closeModal() {
        backdrop.classList.remove('opacity-100');
        backdrop.classList.add('opacity-0');
        panel.classList.remove('opacity-100', 'scale-100');
        panel.classList.add('opacity-0', 'scale-95');
        // Wait for animation to complete before hiding
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 200);
    }

    // Event Listeners
    openBtn.addEventListener('click', openModal);
    closeBtn.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', closeModal);
    
    // Close on backdrop click
    backdrop.addEventListener('click', closeModal);
    
    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeModal();
        }
    });
})();

// Delete confirmation
function confirmDelete(sessionId) {
    if (confirm('คุณต้องการลบรายการนี้หรือไม่?\n\nการลบจะลบข้อมูลการเบิกจ่ายทั้งหมดที่เกี่ยวข้อง')) {
        // Create and submit a form to delete
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= \App\Core\View::url("/budgets/tracking/") ?>' + sessionId + '/delete';
        
        // Add CSRF token
        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = '<?= \App\Core\View::csrfToken() ?>';
        form.appendChild(csrf);
        
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
