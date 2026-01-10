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
            
            <button type="button" id="btnCreateRequest" class="btn btn-primary">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span class="hidden sm:inline">สร้างคำขอ</span>
            </button>
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
                                <?= htmlspecialchars($req['org_name'] ?? '-') ?>
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

<!-- Create Request Modal -->
<div id="createRequestModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black/60 backdrop-blur-[4px] transition-opacity opacity-0" id="createModalBackdrop"></div>
    
    <!-- Modal Panel -->
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative transform overflow-hidden rounded-xl bg-slate-900/80 border border-slate-700 text-left shadow-2xl transition-all w-full max-w-lg scale-95 opacity-0" id="createModalContent">
                
                <!-- Header -->
                <div class="flex items-center justify-between p-4 border-b border-slate-700">
                    <h3 class="text-lg font-semibold text-white flex items-center gap-2" id="modal-title">
                        <i data-lucide="calendar-plus" class="w-5 h-5 text-primary-400"></i>
                        สร้างรายการเบิกจ่ายใหม่
                    </h3>
                    <button type="button" id="btnCloseIcon" class="p-1 rounded hover:bg-slate-700 text-slate-400 hover:text-white transition-colors">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <!-- Form -->
                <form action="<?= \App\Core\View::url('/requests/create') ?>" method="GET" id="formCreateRequest">
                    <div class="p-6 space-y-5">
                        
                        <!-- Fiscal Year -->
                        <div>
                            <label class="block text-sm font-medium text-white mb-2">ปีงบประมาณ <span class="text-rose-500">*</span></label>
                            <select name="fiscal_year" required 
                                class="w-full px-4 py-2.5 bg-slate-800 border border-slate-600 rounded-lg text-white appearance-none transition-all duration-200 focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20">
                                <?php foreach ($fiscalYears as $year): ?>
                                <option value="<?= $year['year'] ?>" <?= $year['year'] == $fiscalYear ? 'selected' : '' ?>>
                                    <?= $year['year'] ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <?php if (\App\Core\Auth::hasRole('admin')): ?>
                            <?php 
                                // Segregate Departments and Divisions for Cascade Logic
                                $departments = array_filter($organizations, function($org) {
                                    return $org['level'] == 1; // Assuming Level 1 is Department
                                });
                                $divisions = array_filter($organizations, function($org) {
                                    return $org['level'] > 1; // Level 2+ are divisions
                                });
                            ?>
                            
                            <!-- Ministry/Dept (Grom) -->
                            <div>
                                <label class="block text-sm font-medium text-white mb-2">
                                    <i data-lucide="landmark" class="w-4 h-4 inline mr-1"></i>กรม <span class="text-rose-500">*</span>
                                </label>
                                <select id="dept_select" class="w-full px-4 py-2.5 bg-slate-800 border border-slate-600 rounded-lg text-white appearance-none transition-all duration-200 focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20">
                                    <option value="">-- เลือกกรม --</option>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?= $dept['id'] ?>"><?= htmlspecialchars($dept['name_th']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Division (Kong) - Cascading -->
                            <div>
                                <label class="block text-sm font-medium text-white mb-2">
                                    <i data-lucide="building-2" class="w-4 h-4 inline mr-1"></i>กอง/หน่วยงาน <span class="text-rose-500">*</span>
                                </label>
                                <select name="org_id" id="org_select" required disabled
                                    class="w-full px-4 py-2.5 bg-slate-800 border border-slate-600 rounded-lg text-white appearance-none transition-all duration-200 focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <option value="">-- กรุณาเลือกกรมก่อน --</option>
                                    <?php foreach ($divisions as $div): ?>
                                        <option value="<?= $div['id'] ?>" data-parent-id="<?= $div['parent_id'] ?>" hidden>
                                            <?= htmlspecialchars($div['name_th']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <script>
                                document.getElementById('dept_select').addEventListener('change', function() {
                                    const deptId = this.value;
                                    const orgSelect = document.getElementById('org_select');
                                    const options = orgSelect.querySelectorAll('option[data-parent-id]');
                                    
                                    if (!deptId) {
                                        orgSelect.disabled = true;
                                        orgSelect.value = '';
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
                                    orgSelect.options[0].text = count > 0 ? '-- เลือกหน่วยงาน --' : '-- ไม่มีหน่วยงานในกรมนี้ --';
                                });
                            </script>

                        <?php else: ?>
                            <!-- Fixed Org for User -->
                            <?php 
                                $userOrgId = \App\Core\Auth::user()['org_id'];
                                $userOrgName = '-';
                                foreach($organizations as $org) {
                                    if($org['id'] == $userOrgId) {
                                        $userOrgName = $org['name_th'];
                                        break;
                                    }
                                }
                            ?>
                            <input type="hidden" name="org_id" value="<?= $userOrgId ?>">
                            <div>
                                <label class="block text-sm font-medium text-white mb-2">
                                    <i data-lucide="building-2" class="w-4 h-4 inline mr-1"></i>กอง (ของคุณ)
                                </label>
                                <select disabled class="w-full px-4 py-2.5 bg-slate-800 border border-slate-600 rounded-lg text-white opacity-75 cursor-not-allowed">
                                    <option selected><?= htmlspecialchars($userOrgName) ?></option>
                                </select>
                            </div>

                            <!-- Request Title -->
                            <div>
                                <label class="block text-sm font-medium text-white mb-2">ชื่อแบบคำขอ <span class="text-rose-500">*</span></label>
                                <input type="text" name="request_title" required 
                                    class="w-full px-4 py-2.5 bg-slate-800 border border-slate-600 rounded-lg text-white placeholder-slate-500 transition-all duration-200 focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20"
                                    placeholder="ระบุชื่อเรียกของคำขอนี้">
                            </div>

                            <!-- Info Box (User - Moved to bottom) -->
                            <div class="bg-blue-900/20 border border-blue-800/30 rounded-lg p-4 mt-2">
                                <div class="flex items-start gap-3">
                                    <i data-lucide="info" class="w-5 h-5 text-blue-400 mt-0.5"></i>
                                    <div class="text-sm text-blue-300">
                                        ระบบจะสร้างรายการสำหรับกองของคุณ<br>
                                        โดยใช้วันที่ปัจจุบันเป็นวันที่บันทึก
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Footer Buttons -->
                    <div class="flex gap-3 px-6 pb-6 pt-2">
                        <button type="button" id="btnCancelCreate" 
                            class="px-5 py-2.5 bg-slate-700 text-slate-200 rounded-lg font-medium border border-slate-600 hover:bg-slate-600 transition-colors">
                            ยกเลิก
                        </button>
                        <button type="submit" 
                            class="flex-1 px-5 py-2.5 bg-primary-600 text-white rounded-lg font-medium shadow-lg shadow-primary-900/30 hover:bg-primary-500 transition-colors flex items-center justify-center gap-2">
                            ดำเนินการต่อ
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 z-50 hidden" aria-labelledby="delete-modal-title" role="dialog" aria-modal="true">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black/60 backdrop-blur-[4px] transition-opacity opacity-0" id="modalBackdrop"></div>
    
    <!-- Modal Panel -->
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative transform overflow-hidden rounded-xl bg-slate-900/80 border border-slate-700 text-left shadow-2xl transition-all w-full max-w-sm scale-95 opacity-0" id="modalContent">
                <div class="p-6 text-center">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-rose-500/10 border border-rose-500/20 mb-4">
                        <i data-lucide="trash-2" class="h-6 w-6 text-rose-500"></i>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2" id="delete-modal-title">ยืนยันการลบ</h3>
                    <p class="text-slate-400 text-sm mb-6">
                        คุณแน่ใจหรือไม่ว่าต้องการลบคำขอนี้? <br>
                        การกระทำนี้ไม่สามารถย้อนกลับได้
                    </p>
                    
                    <div class="flex gap-3 justify-center">
                        <button type="button" id="cancelDelete" class="w-full px-4 py-2 bg-slate-700 text-slate-200 rounded-lg font-medium border border-slate-600 hover:bg-slate-600 transition-colors">
                            ยกเลิก
                        </button>
                        <button type="button" id="confirmDelete" class="w-full px-4 py-2 bg-rose-600/20 text-rose-400 border border-rose-600/50 rounded-lg font-medium hover:bg-rose-600/30 transition-colors">
                            ลบคำขอ
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Shared functionality for showing/hiding modals
    function setupModal(modalId, backdropId, contentId, triggerSelector, closeIds) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        const backdrop = document.getElementById(backdropId);
        const content = document.getElementById(contentId);
        
        const show = (callback) => {
            modal.classList.remove('hidden');
            requestAnimationFrame(() => {
                backdrop.classList.remove('opacity-0');
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
                if (callback) callback();
            });
        };

        const hide = () => {
            backdrop.classList.add('opacity-0');
            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 200);
        };

        // Bind Close Events
        closeIds.forEach(id => {
            const btn = document.getElementById(id);
            if (btn) btn.addEventListener('click', hide);
        });
        backdrop.addEventListener('click', hide);

        return { show, hide };
    }

    // --- Delete Modal ---
    let currentDeleteForm = null;
    const deleteModal = setupModal('deleteModal', 'modalBackdrop', 'modalContent', '.btn-delete', ['cancelDelete']);
    
    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            currentDeleteForm = this.closest('form');
            deleteModal.show();
        });
    });

    const confirmDeleteBtn = document.getElementById('confirmDelete');
    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', function() {
            if (currentDeleteForm) currentDeleteForm.submit();
        });
    }

    // --- Create Request Modal ---
    const createModal = setupModal('createRequestModal', 'createModalBackdrop', 'createModalContent', '#btnCreateRequest', ['btnCancelCreate', 'btnCloseIcon']);
    const btnCreateRequest = document.getElementById('btnCreateRequest');
    if (btnCreateRequest) {
        btnCreateRequest.addEventListener('click', () => createModal.show());
    }

    // Esc key closes all modals
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const visibleModals = document.querySelectorAll('.fixed:not(.hidden)');
            visibleModals.forEach(m => {
                // Find hide function (this is a bit hacky, but Esc is fine)
                // Just hide them manually or trigger click on backdrop
                const backdrop = m.querySelector('[id*="Backdrop"]');
                if (backdrop) backdrop.click();
            });
        }
    });
});
</script>
