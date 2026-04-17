<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-slate-100 flex items-center gap-2">
                <i data-lucide="shield-check" class="w-8 h-8 text-primary-400"></i>
                ตั้งค่าการอนุมัติ (Approval Workflow)
            </h2>
            <p class="text-slate-400 mt-1">จัดการการตั้งค่าและสิทธิ์การอนุมัติคำของบประมาณ</p>
        </div>
    </div>

    <!-- Toggle Switch -->
    <div class="bg-slate-900/50 backdrop-blur-sm border border-slate-700 rounded-xl p-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-white">สถานะระบบอนุมัติ</h3>
                <p class="text-slate-400 text-sm mt-1">
                    เมื่อเปิดใช้งาน จะต้องมีการอนุมัติคำขอก่อนที่ข้อมูลจะถูกนำไปคำนวณในการ์ดผลรวม <br>
                    <span class="text-amber-400 text-xs">(หากปิดใช้งาน ระบบจะนับคำขอที่ "ยืนยัน" แล้วทันที)</span>
                </p>
            </div>
            
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" id="toggleApproval" class="sr-only peer" <?= $isEnabled ? 'checked' : '' ?>>
                <div class="w-14 h-7 bg-slate-700 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-primary-600"></div>
                <span class="ml-3 text-sm font-medium text-slate-300 status-text"><?= $isEnabled ? 'เปิดใช้งาน' : 'ปิดใช้งาน' ?></span>
            </label>
        </div>
    </div>

    <!-- Approvers List -->
    <div class="bg-slate-900/50 backdrop-blur-sm border border-slate-700 rounded-xl overflow-hidden shadow-xl">
        <div class="px-6 py-4 border-b border-slate-700 flex items-center justify-between bg-slate-800/30">
            <h3 class="font-semibold text-lg text-white flex items-center gap-2">
                <i data-lucide="users" class="w-5 h-5 text-blue-400"></i>
                รายชื่อผู้อนุมัติ (Approvers)
            </h3>
            <button onclick="document.getElementById('addApproverModal').classList.remove('hidden')" 
                    class="px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-sm font-medium rounded-lg transition-colors flex items-center gap-2 shadow-lg shadow-primary-900/20">
                <i data-lucide="user-plus" class="w-4 h-4"></i> เพิ่มผู้อนุมัติ
            </button>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-400">
                <thead class="bg-slate-800/50 text-slate-200 uppercase text-xs">
                    <tr>
                        <th class="px-6 py-3 font-semibold">ชื่อ-นามสกุล</th>
                        <th class="px-6 py-3 font-semibold">หน่วยงานที่รับผิดชอบ</th>
                        <th class="px-6 py-3 font-semibold text-center">สถานะ</th>
                        <th class="px-6 py-3 font-semibold text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    <?php if (empty($approvers)): ?>
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-slate-500">
                                <i data-lucide="user-x" class="w-12 h-12 mx-auto mb-3 opacity-50"></i>
                                <p>ยังไม่มีการกำหนดผู้อนุมัติ</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($approvers as $approver): ?>
                        <tr class="hover:bg-slate-800/30 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-slate-700 flex items-center justify-center text-slate-300 font-bold">
                                        <?= mb_substr($approver['user_name'], 0, 1) ?>
                                    </div>
                                    <div>
                                        <div class="font-medium text-white"><?= htmlspecialchars($approver['user_name']) ?></div>
                                        <div class="text-xs text-slate-500"><?= htmlspecialchars($approver['email']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full bg-blue-500/10 text-blue-400 border border-blue-500/20 text-xs">
                                    <?= htmlspecialchars($approver['org_name']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <?php if ($approver['is_active']): ?>
                                    <span class="text-emerald-400 flex justify-center"><i data-lucide="check-circle" class="w-4 h-4"></i></span>
                                <?php else: ?>
                                    <span class="text-slate-500 flex justify-center"><i data-lucide="slash" class="w-4 h-4"></i></span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <form action="<?= \App\Core\View::url('/admin/approvals/' . $approver['id'] . '/remove') ?>" method="POST" onsubmit="return confirm('ยืนยันการลบสิทธิ์อนุมัติ?');">
                                    <button type="submit" class="text-rose-400 hover:text-rose-300 transition-colors p-2 hover:bg-rose-500/10 rounded-lg" title="ลบสิทธิ์">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Approver Modal -->
<div id="addApproverModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-black/70 backdrop-blur-sm transition-opacity" onclick="document.getElementById('addApproverModal').classList.add('hidden')"></div>
    
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-lg bg-slate-900 border border-slate-700 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                <form action="<?= \App\Core\View::url('/admin/approvals/add') ?>" method="POST">
                    <div class="px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-blue-900/30 sm:mx-0 sm:h-10 sm:w-10">
                                <i data-lucide="user-plus" class="w-6 h-6 text-blue-400"></i>
                            </div>
                            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                                <h3 class="text-base font-semibold leading-6 text-white" id="modal-title">เพิ่มผู้มีสิทธิ์อนุมัติ</h3>
                                <div class="mt-4 space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-300 mb-1">ผู้ใช้งาน</label>
                                        <select name="user_id" required class="w-full bg-slate-800 border-slate-600 rounded-md text-slate-200 focus:border-blue-500 focus:ring-blue-500">
                                            <option value="">-- เลือกผู้ใช้งาน --</option>
                                            <?php foreach ($users as $user): ?>
                                                <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['name']) ?> (<?= $user['email'] ?>)</option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-slate-300 mb-1">หน่วยงานที่รับผิดชอบ</label>
                                        <select name="org_id" required class="w-full bg-slate-800 border-slate-600 rounded-md text-slate-200 focus:border-blue-500 focus:ring-blue-500">
                                            <option value="">-- เลือกหน่วยงาน --</option>
                                            <?php foreach ($organizations as $org): ?>
                                                <option value="<?= $org['id'] ?>"><?= htmlspecialchars($org['name_th']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <p class="text-xs text-slate-500 mt-1">* ผู้อนุมัติจะมีสิทธิ์อนุมัติเฉพาะคำขอจากหน่วยงานที่เลือกเท่านั้น</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-slate-800/50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <button type="submit" class="inline-flex w-full justify-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 sm:ml-3 sm:w-auto">บันทึก</button>
                        <button type="button" class="mt-3 inline-flex w-full justify-center rounded-md bg-slate-700 px-3 py-2 text-sm font-semibold text-slate-300 shadow-sm ring-1 ring-inset ring-slate-600 hover:bg-slate-600 sm:mt-0 sm:w-auto" onclick="document.getElementById('addApproverModal').classList.add('hidden')">ยกเลิก</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('toggleApproval').addEventListener('change', function() {
    const isChecked = this.checked;
    const statusText = document.querySelector('.status-text');
    
    // Update UI immediately for responsiveness
    statusText.textContent = isChecked ? 'กำลังบันทึก...' : 'กำลังบันทึก...';
    
    fetch('<?= \App\Core\View::url('/admin/approvals/toggle') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'enabled=' + isChecked
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            statusText.textContent = isChecked ? 'เปิดใช้งาน' : 'ปิดใช้งาน';
            // Show toast or updated UI
        } else {
            alert('เกิดข้อผิดพลาดในการบันทึกค่า');
            this.checked = !isChecked; // Revert
            statusText.textContent = !isChecked ? 'เปิดใช้งาน' : 'ปิดใช้งาน';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
        this.checked = !isChecked; // Revert
    });
});
</script>
