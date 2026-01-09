    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
        <div>
            <p class="text-dark-muted text-sm">รายการคำของบประมาณแผนงานบุคลากรภาครัฐ</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-dark-muted text-sm">ปีงบประมาณ พ.ศ.</span>
            <select onchange="window.location.href='?year=' + this.value" class="input py-2 px-3 text-sm rounded-lg border border-slate-700 bg-slate-800 text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none cursor-pointer">
                <?php foreach ($fiscalYears as $year): ?>
                <option value="<?= $year['year'] ?>" <?= $year['year'] == $fiscalYear ? 'selected' : '' ?>>
                    <?= $year['year'] ?>
                </option>
                <?php endforeach; ?>
            </select>
            
            <a href="<?= \App\Core\View::url('/requests/create') ?>" class="btn btn-primary">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span class="hidden sm:inline">สร้างคำขอ</span>
            </a>
        </div>
    </div>

    <!-- Requests Table -->
    <div class="bg-slate-900/50 backdrop-blur-sm border border-slate-700 rounded-xl overflow-hidden shadow-2xl">
        <div class="px-6 py-4 border-b border-dark-border flex items-center gap-3 bg-slate-800/30">
            <div class="w-8 h-8 rounded-lg bg-primary-500/10 flex items-center justify-center text-primary-400">
                <i data-lucide="table" class="w-4 h-4"></i>
            </div>
            <h3 class="font-semibold text-lg text-white">คำของบประมาณแผนงานบุคลากรภาครัฐ</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="table w-full whitespace-nowrap">
                <thead class="bg-slate-800/80 backdrop-blur-sm">
                    <tr class="text-slate-300 border-b border-dark-border">
                        <th style="text-align:center" class="w-16 py-4 font-semibold">ลำดับ</th>
                        <th style="text-align:center" class="py-4 font-semibold">แบบคำขอ</th>
                        <th style="text-align:center" class="py-4 font-semibold">กอง</th>
                        <th style="text-align:center" class="py-4 font-semibold">ยอดรวม</th>
                        <th style="text-align:center" class="py-4 font-semibold">วันที่บันทึก</th>
                        <th style="text-align:center" class="w-32 py-4 font-semibold">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark-border">
                    <?php if (!empty($requests)): ?>
                        <?php foreach ($requests as $i => $req): ?>
                        <tr class="bg-dark-bg hover:bg-white/5 transition-colors group">
                            <td style="text-align:center" class="w-16 py-4 text-dark-muted font-medium align-middle"><?= $i + 1 + ($pagination['current'] - 1) * $pagination['perPage'] ?></td>
                            <td style="text-align:center" class="py-4 align-middle">
                                <div class="font-medium text-white group-hover:text-primary-400 transition-colors"><?= htmlspecialchars($req['request_title']) ?></div>
                            </td>
                            <td style="text-align:center" class="py-4 text-dark-muted align-middle">
                                <?= htmlspecialchars($req['created_by_name'] ?? '-') ?>
                            </td>
                            <td style="text-align:center" class="py-4 font-medium text-emerald-400 align-middle">
                                <?= number_format($req['total_amount'], 2) ?>
                            </td>
                            <td style="text-align:center" class="py-4 text-dark-muted text-sm align-middle">
                                <?php 
                                    $date = strtotime($req['created_at']);
                                    echo date('d/m/', $date) . (date('Y', $date) + 543);
                                ?>
                            </td>
                            <td style="text-align:center" class="w-32 py-4 align-middle">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="<?= \App\Core\View::url('/requests/' . $req['id']) ?>" class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-sky-400 transition-colors" title="เรียกดู">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </a>
                                    <a href="<?= \App\Core\View::url('/requests/' . $req['id']) ?>" class="w-8 h-8 flex items-center justify-center text-amber-500 hover:text-amber-400 transition-colors" title="แก้ไข">
                                        <i data-lucide="square-pen" class="w-4 h-4"></i>
                                    </a>
                                    <?php if (\App\Core\Auth::hasRole('admin')): ?>
                                    <form action="<?= \App\Core\View::url('/requests/' . $req['id'] . '/delete') ?>" method="POST" class="w-8 h-8 flex items-center justify-center m-0 p-0">
                                        <?= \App\Core\View::csrf() ?>
                                        <button type="button" class="w-full h-full flex items-center justify-center text-rose-500 hover:text-rose-400 transition-colors btn-delete" title="ลบคำขอ">
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
                            <td colspan="6" class="text-center py-16 text-dark-muted">
                                <div class="bg-dark-border/30 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i data-lucide="files" class="w-8 h-8 text-slate-500"></i>
                                </div>
                                <p class="text-lg font-medium text-slate-400">ยังไม่มีคำของบประมาณ</p>
                                <p class="text-sm mt-1 text-slate-600">กดปุ่ม "สร้างคำขอ" เพื่อเริ่มรายการใหม่</p>
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
