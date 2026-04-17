<?php
/**
 * Admin Organizations Form View - Modal Version
 */
use App\Models\Organization;

$isEdit = ($mode === 'edit');
$formData = $_SESSION['form_data'] ?? ($organization ?? []);
unset($_SESSION['form_data']);

$typeLabels = Organization::getTypeLabels();
$regionLabels = Organization::getRegionLabels();
?>
<!-- Modal Header -->
<div class="flex items-center justify-between mb-6 pb-4 border-b border-slate-700">
    <h3 class="text-xl font-bold text-white flex items-center gap-2">
        <i data-lucide="<?= $isEdit ? 'pencil' : 'plus-circle' ?>" class="w-5 h-5 text-primary-500"></i>
        <span id="modal-title"><?= $isEdit ? 'แก้ไขข้อมูล' : 'เพิ่มข้อมูลใหม่' ?></span>
    </h3>
    <button type="button" onclick="closeModal()" class="text-slate-400 hover:text-white transition-colors">
        <i data-lucide="x" class="w-6 h-6"></i>
    </button>
</div>

<!-- Form Content -->

<form action="<?= \App\Core\View::url($isEdit ? "admin/organizations/{$organization['id']}/update" : 'admin/organizations/store') ?>" method="POST" class="space-y-6">
    
    <!-- Basic Info Section -->
    <div class="bg-slate-800/30 p-6 rounded-lg border border-slate-700">
        <h5 class="text-lg font-semibold text-slate-200 mb-4 border-b border-slate-600 pb-2">ข้อมูลทั่วไป</h5>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1 required" id="label-code">รหัสหน่วยงาน (Code)</label>
                            <input type="text" class="w-full rounded-md bg-slate-700/50 border-slate-600 text-slate-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 py-2 px-3" 
                                   name="code" value="<?= htmlspecialchars($formData['code'] ?? '') ?>" required placeholder="เช่น DEPT001">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1" id="label-abbr">ชื่อย่อ (ถ้ามี)</label>
                            <input type="text" class="w-full rounded-md bg-slate-700/50 border-slate-600 text-slate-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 py-2 px-3" 
                                   name="abbreviation" value="<?= htmlspecialchars($formData['abbreviation'] ?? '') ?>" placeholder="เช่น กค.">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-300 mb-1 required" id="label-name">ชื่อหน่วยงาน (ไทย)</label>
                        <input type="text" class="w-full rounded-md bg-slate-700/50 border-slate-600 text-slate-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 py-2 px-3" 
                               name="name_th" value="<?= htmlspecialchars($formData['name_th'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="bg-slate-800/30 p-6 rounded-lg border border-slate-700">
                    <h5 class="text-lg font-semibold text-slate-200 mb-4 border-b border-slate-600 pb-2">โครงสร้างองค์กร</h5>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">ประเภทหน่วยงาน</label>
                            <select class="w-full rounded-md border-slate-600 bg-slate-700/50 text-slate-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 py-2 px-3" 
                                    name="org_type" id="org_type" onchange="window.updateDynamicLabels()">
                                <?php foreach ($typeLabels as $val => $label): ?>
                                    <option value="<?= $val ?>" <?= (isset($formData['org_type']) && $formData['org_type'] == $val) ? 'selected' : '' ?>>
                                        <?= $label ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">สังกัด (Parent)</label>
                            <div class="relative">
                                <select class="w-full rounded-md border-slate-600 bg-slate-700/50 text-slate-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 py-2 px-3 appearance-none" name="parent_id">
                                    <option value="">- ไม่มี (เป็นระดับสูงสุด) -</option>
                                    <?php foreach ($parents as $p): ?>
                                        <?php if ($isEdit && $p['id'] == $organization['id']) continue; ?>
                                        <option value="<?= $p['id'] ?>" <?= (isset($formData['parent_id']) && $formData['parent_id'] == $p['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($p['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-400">
                                    <i class="bi bi-chevron-down text-xs"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">ส่วน/ภาค</label>
                            <select class="w-full rounded-md border-slate-600 bg-slate-700/50 text-slate-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 py-2 px-3" 
                                    name="region" id="region">
                                <?php foreach ($regionLabels as $val => $label): ?>
                                    <option value="<?= $val ?>" <?= (isset($formData['region']) && $formData['region'] == $val) ? 'selected' : '' ?>>
                                        <?= $label ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        </div>
                        <div id="province_field" class="col-span-1 md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-1">รหัสจังหวัด</label>
                                <input type="text" class="w-full rounded-md border-slate-600 bg-slate-700/50 text-slate-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 py-2 px-3" 
                                       name="province_code" value="<?= htmlspecialchars($formData['province_code'] ?? '') ?>">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-1">กลุ่มจังหวัด</label>
                                <input type="text" class="w-full rounded-md border-slate-600 bg-slate-700/50 text-slate-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 py-2 px-3" 
                                       name="provincial_group" value="<?= htmlspecialchars($formData['provincial_group'] ?? '') ?>" placeholder="เช่น กลุ่มจังหวัดภาคเหนือตอนบน 1">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-1">เขตจังหวัด</label>
                                <input type="text" class="w-full rounded-md border-slate-600 bg-slate-700/50 text-slate-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 py-2 px-3" 
                                       name="provincial_zone" value="<?= htmlspecialchars($formData['provincial_zone'] ?? '') ?>" placeholder="ระบุเขตจังหวัด">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-1">เขตตรวจราชการ</label>
                                <input type="text" class="w-full rounded-md border-slate-600 bg-slate-700/50 text-slate-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 py-2 px-3" 
                                       name="inspection_zone" value="<?= htmlspecialchars($formData['inspection_zone'] ?? '') ?>" placeholder="ระบุเขตตรวจราชการ">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-1">เขตกำหนดเอง</label>
                                <input type="text" class="w-full rounded-md border-slate-600 bg-slate-700/50 text-slate-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 py-2 px-3" 
                                       name="custom_zone" value="<?= htmlspecialchars($formData['custom_zone'] ?? '') ?>" placeholder="ระบุเขตอื่นๆ">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact & Additional Info -->
                <div class="bg-slate-800/30 p-6 rounded-lg border border-slate-700">
                    <h5 class="text-lg font-semibold text-slate-200 mb-4 border-b border-slate-600 pb-2">ข้อมูลติดต่อและงบประมาณ</h5>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                         <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">เบอร์โทรศัพท์</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="bi bi-telephone text-slate-400"></i>
                                </div>
                                <input type="text" class="w-full rounded-md border-slate-600 bg-slate-700/50 text-slate-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 py-2 pl-10 pr-3" 
                                       name="contact_phone" value="<?= htmlspecialchars($formData['contact_phone'] ?? '') ?>">
                            </div>
                        </div>
                         <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">อีเมล</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="bi bi-envelope text-slate-400"></i>
                                </div>
                                <input type="email" class="w-full rounded-md border-slate-600 bg-slate-700/50 text-slate-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 py-2 pl-10 pr-3" 
                                       name="contact_email" value="<?= htmlspecialchars($formData['contact_email'] ?? '') ?>">
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-300 mb-1">ที่อยู่</label>
                        <textarea class="w-full rounded-md border-slate-600 bg-slate-700/50 text-slate-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 py-2 px-3" 
                                  name="address" rows="2"><?= htmlspecialchars($formData['address'] ?? '') ?></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <div>
                             <label class="block text-sm font-medium text-slate-300 mb-1">ลำดับการแสดงผล</label>
                             <input type="number" class="w-full rounded-md border-slate-600 bg-slate-700/50 text-slate-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 py-2 px-3" 
                                    name="sort_order" value="<?= htmlspecialchars($formData['sort_order'] ?? '0') ?>">
                        </div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="flex items-center justify-between pt-6 border-t border-slate-700">
                    <div class="flex items-center">
                        <input id="is_active" name="is_active" type="checkbox" class="h-5 w-5 text-primary-600 focus:ring-primary-500 border-slate-600 rounded bg-slate-700" 
                               <?= (!isset($formData['is_active']) || $formData['is_active']) ? 'checked' : '' ?>>
                        <label for="is_active" class="ml-2 block text-sm text-slate-300 cursor-pointer">
                            เปิดใช้งานหน่วยงานนี้
                        </label>
                    </div>
                    <div class="flex gap-3">
                        <button type="button" onclick="closeModal()" class="px-5 py-2.5 rounded-lg border border-slate-600 text-slate-300 hover:bg-slate-700 font-medium transition-colors">
                            ยกเลิก
                        </button>
                        <button type="submit" class="px-5 py-2.5 rounded-lg bg-primary-600 text-white hover:bg-primary-700 font-medium shadow-sm hover:shadow transition-all flex items-center">
                            <i data-lucide="save" class="w-4 h-4 mr-2"></i> บันทึกข้อมูล
                        </button>
                    </div>
                </div>

            </form>

<script>
// Attach to window to ensure it's globally accessible when loaded via AJAX
window.updateDynamicLabels = function() {
    const type = document.getElementById('org_type').value;
    
    const labelMapping = {
        'ministry': { name: 'ชื่อกระทรวง', abbr: 'ชื่อย่อกระทรวง', code: 'รหัสกระทรวง' },
        'department': { name: 'ชื่อกรม', abbr: 'ชื่อย่อกรม', code: 'รหัสกรม' },
        'division': { name: 'ชื่อกอง/สำนัก', abbr: 'ชื่อย่อกอง', code: 'รหัสกอง' },
        'section': { name: 'ชื่อกลุ่มงาน', abbr: 'ชื่อย่อกลุ่มงาน', code: 'รหัสกลุ่มงาน' },
        'province': { name: 'ชื่อจังหวัด', abbr: 'ชื่อย่อจังหวัด', code: 'รหัสจังหวัด' },
        'office': { name: 'ชื่อส่วนราชการ', abbr: 'ชื่อย่อส่วนราชการ', code: 'รหัสส่วนราชการ' }
    };

    const labels = labelMapping[type] || { name: 'ชื่อหน่วยงาน (ไทย)', abbr: 'ชื่อย่อ (ถ้ามี)', code: 'รหัสหน่วยงาน' };

    document.getElementById('label-name').textContent = labels.name;
    document.getElementById('label-abbr').textContent = labels.abbr;
    document.getElementById('label-code').textContent = labels.code;
    
    // Update Modal Title dynamically if adding new
    const isEdit = <?= $isEdit ? 'true' : 'false' ?>;
    const titleSpan = document.getElementById('modal-title');
    if (titleSpan) {
        titleSpan.textContent = isEdit ? ('แก้ไข' + labels.name.replace('ชื่อ', '')) : ('เพิ่ม' + labels.name.replace('ชื่อ', '') + 'ใหม่');
    }
}

function formatCurrency(input) {
    let value = input.value.replace(/,/g, '');
    if (!isNaN(value) && value.length > 0) {
        input.value = new Intl.NumberFormat('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(value);
    }
}

// Initial check
document.addEventListener('DOMContentLoaded', updateDynamicLabels);
</script>
