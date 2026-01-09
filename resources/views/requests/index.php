<div class="space-y-6 animate-fade-in">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <p class="text-dark-muted text-sm">จัดการคำขออนุมัติงบประมาณประจำปี <?= $fiscalYear ?></p>
        </div>
        <div class="flex gap-3">
             <div class="relative">
                <select onchange="window.location.href='?year=' + this.value" class="input pl-10 pr-8 py-2 text-sm max-w-[140px]">
                    <?php foreach ($fiscalYears as $year): ?>
                    <option value="<?= $year['year'] ?>" <?= $year['year'] == $fiscalYear ? 'selected' : '' ?>>
                        ปี <?= $year['year'] ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <i data-lucide="calendar" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-dark-muted"></i>
            </div>
            
            <a href="<?= \App\Core\View::url('/requests/create') ?>" class="btn btn-primary">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span class="hidden sm:inline">สร้างคำขอ</span>
            </a>
        </div>
    </div>

    <!-- Requests Table -->
    <div class="bg-dark-card border border-dark-border rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table w-full whitespace-nowrap">
                <thead>
                    <tr>
                        <th class="w-10">#</th>
                        <th>หัวข้อคำขอ</th>
                        <th>ผู้ขอ</th>
                        <th class="text-right">ยอดรวม</th>
                        <th>วันที่บันทึก</th>
                        <th class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($requests)): ?>
                        <?php foreach ($requests as $i => $req): ?>
                        <tr class="hover:bg-dark-border/20 transition-colors">
                            <td class="text-dark-muted"><?= $i + 1 + ($pagination['current'] - 1) * $pagination['perPage'] ?></td>
                            <td>
                                <div class="font-medium text-white"><?= htmlspecialchars($req['request_title']) ?></div>
                            </td>
                            <td class="text-dark-muted">
                                <?= htmlspecialchars($req['created_by_name'] ?? '-') ?>
                            </td>
                            <td class="text-right font-medium">
                                <?= \App\Core\View::currency($req['total_amount']) ?>
                            </td>
                            <td class="text-dark-muted text-sm">
                                <?= date('d/m/Y', strtotime($req['created_at'])) ?>
                            </td>
                            <td class="text-center">
                                <div class="flex items-center justify-center gap-0.5">
                                    <a href="<?= \App\Core\View::url('/requests/' . $req['id']) ?>" class="btn btn-icon btn-ghost-primary" title="บันทึกข้อมูล">
                                        <i data-lucide="square-pen" class="w-4 h-4"></i>
                                    </a>
                                    <?php if (\App\Core\Auth::hasRole('admin')): ?>
                                    <form action="<?= \App\Core\View::url('/requests/' . $req['id'] . '/delete') ?>" method="POST" class="delete-form inline-flex m-0 p-0">
                                        <?= \App\Core\View::csrf() ?>
                                        <button type="button" class="btn btn-icon text-red-400 hover:text-red-300 hover:bg-red-400/10 btn-delete" title="ลบคำขอ">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-12 text-dark-muted">
                                <i data-lucide="files" class="w-10 h-10 mx-auto mb-3 text-dark-muted"></i>
                                ยังไม่มีคำของบประมาณ
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 z-50 hidden">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black/80 backdrop-blur-sm transition-opacity opacity-0" id="modalBackdrop"></div>
    
    <!-- Modal -->
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-dark-card border border-slate-600/50 rounded-xl p-6 w-full max-w-sm transform scale-95 opacity-0 transition-all duration-200 shadow-2xl shadow-black/50" id="modalContent">
            <div class="text-center">
                <div class="w-12 h-12 rounded-full bg-red-500/10 text-red-500 flex items-center justify-center mx-auto mb-4 border border-red-500/20">
                    <i data-lucide="trash-2" class="w-6 h-6"></i>
                </div>
                <h3 class="text-lg font-bold text-white mb-2">ยืนยันการลบ</h3>
                <p class="text-dark-muted text-sm mb-6">คุณแน่ใจหรือไม่ว่าต้องการลบคำขอนี้? <br>การกระทำนี้ไม่สามารถย้อนกลับได้</p>
                
                <div class="flex gap-3 justify-center">
                    <button type="button" id="cancelDelete" class="btn btn-secondary w-full hover:bg-slate-700">ยกเลิก</button>
                    <button type="button" id="confirmDelete" class="btn bg-red-500/10 border border-red-500/50 text-red-400 hover:bg-red-500/20 hover:text-red-300 w-full shadow-lg shadow-red-900/20 backdrop-blur-md transition-all">ลบคำขอ</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const deleteModal = document.getElementById('deleteModal');
    const modalBackdrop = document.getElementById('modalBackdrop');
    const modalContent = document.getElementById('modalContent');
    const cancelBtn = document.getElementById('cancelDelete');
    const confirmBtn = document.getElementById('confirmDelete');
    let currentForm = null;

    // Show modal
    function showModal(form) {
        currentForm = form;
        deleteModal.classList.remove('hidden');
        // Small delay for transition
        requestAnimationFrame(() => {
            modalBackdrop.classList.remove('opacity-0');
            modalContent.classList.remove('scale-95', 'opacity-0');
            modalContent.classList.add('scale-100', 'opacity-100');
        });
    }

    // Hide modal
    function hideModal() {
        modalBackdrop.classList.add('opacity-0');
        modalContent.classList.remove('scale-100', 'opacity-100');
        modalContent.classList.add('scale-95', 'opacity-0');
        
        setTimeout(() => {
            deleteModal.classList.add('hidden');
            currentForm = null;
        }, 200);
    }

    // Bind click events to delete buttons
    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const form = this.closest('form');
            showModal(form);
        });
    });

    // Confirm delete
    confirmBtn.addEventListener('click', function() {
        if (currentForm) {
            currentForm.submit();
        }
    });

    // Cancel / Close
    cancelBtn.addEventListener('click', hideModal);
    modalBackdrop.addEventListener('click', hideModal);
    
    // Esc key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !deleteModal.classList.contains('hidden')) {
            hideModal();
        }
    });
});
</script>
