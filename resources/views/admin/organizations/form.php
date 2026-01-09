<?php
/**
 * Admin Organizations Form View
 */
use App\Models\Organization;

$isEdit = ($mode === 'edit');
$formData = $_SESSION['form_data'] ?? ($organization ?? []);
unset($_SESSION['form_data']);

$typeLabels = Organization::getTypeLabels();
$regionLabels = Organization::getRegionLabels();
?>
<div class="container mx-auto px-4 py-8 max-w-3xl">
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-6 py-4">
            <h4 class="text-xl font-bold text-white flex items-center">
                <i class="bi bi-<?= $isEdit ? 'pencil-square' : 'plus-circle' ?> mr-3 text-lg"></i>
                <?= $isEdit ? 'แก้ไขหน่วยงาน' : 'เพิ่มหน่วยงานใหม่' ?>
            </h4>
            <p class="text-blue-100 text-sm mt-1 ml-8">จัดการข้อมูลหน่วยงานภายในองค์กร</p>
        </div>

        <div class="p-8">
            <form action="<?= $isEdit ? "/admin/organizations/{$organization['id']}/update" : '/admin/organizations/store' ?>" method="POST" class="space-y-6">
                
                <!-- Basic Info Section -->
                <div class="bg-gray-50 p-6 rounded-lg border border-gray-100">
                    <h5 class="text-lg font-semibold text-gray-700 mb-4 border-b pb-2">ข้อมูลทั่วไป</h5>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1 required">รหัสหน่วยงาน (Code)</label>
                            <input type="text" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 px-3" 
                                   name="code" value="<?= htmlspecialchars($formData['code'] ?? '') ?>" required placeholder="เช่น DEPT001">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">ชื่อย่อ (ถ้ามี)</label>
                            <input type="text" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 px-3" 
                                   name="abbreviation" value="<?= htmlspecialchars($formData['abbreviation'] ?? '') ?>" placeholder="เช่น กค.">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1 required">ชื่อหน่วยงาน (ไทย)</label>
                        <input type="text" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 px-3" 
                               name="name_th" value="<?= htmlspecialchars($formData['name_th'] ?? '') ?>" required>
                    </div>
                </div>

                <!-- Organization Structure Section -->
                <div class="bg-blue-50 p-6 rounded-lg border border-blue-100">
                    <h5 class="text-lg font-semibold text-blue-800 mb-4 border-b border-blue-200 pb-2">โครงสร้างองค์กร</h5>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-blue-900 mb-1">ประเภทหน่วยงาน</label>
                            <select class="w-full rounded-md border-blue-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 px-3 bg-white" 
                                    name="org_type" id="org_type" onchange="toggleProvinceField()">
                                <?php foreach ($typeLabels as $val => $label): ?>
                                    <option value="<?= $val ?>" <?= (isset($formData['org_type']) && $formData['org_type'] == $val) ? 'selected' : '' ?>>
                                        <?= $label ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-blue-900 mb-1">สังกัด (Parent)</label>
                            <div class="relative">
                                <select class="w-full rounded-md border-blue-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 px-3 bg-white appearance-none" name="parent_id">
                                    <option value="">- ไม่มี (เป็นระดับสูงสุด) -</option>
                                    <?php foreach ($parents as $p): ?>
                                        <?php if ($isEdit && $p['id'] == $organization['id']) continue; ?>
                                        <option value="<?= $p['id'] ?>" <?= (isset($formData['parent_id']) && $formData['parent_id'] == $p['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($p['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-500">
                                    <i class="bi bi-chevron-down text-xs"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-blue-900 mb-1">ส่วน/ภาค</label>
                            <select class="w-full rounded-md border-blue-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 px-3 bg-white" 
                                    name="region" id="region" onchange="toggleProvinceField()">
                                <?php foreach ($regionLabels as $val => $label): ?>
                                    <option value="<?= $val ?>" <?= (isset($formData['region']) && $formData['region'] == $val) ? 'selected' : '' ?>>
                                        <?= $label ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div id="province_field" style="display: none;">
                            <label class="block text-sm font-medium text-blue-900 mb-1">รหัสจังหวัด (ถ้ามี)</label>
                            <input type="text" class="w-full rounded-md border-blue-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 px-3 focus:bg-white" 
                                   name="province_code" value="<?= htmlspecialchars($formData['province_code'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <!-- Contact & Additional Info -->
                <div class="bg-gray-50 p-6 rounded-lg border border-gray-100">
                    <h5 class="text-lg font-semibold text-gray-700 mb-4 border-b pb-2">ข้อมูลติดต่อและงบประมาณ</h5>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                         <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">เบอร์โทรศัพท์</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="bi bi-telephone text-gray-400"></i>
                                </div>
                                <input type="text" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 pl-10 pr-3" 
                                       name="contact_phone" value="<?= htmlspecialchars($formData['contact_phone'] ?? '') ?>">
                            </div>
                        </div>
                         <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">อีเมล</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="bi bi-envelope text-gray-400"></i>
                                </div>
                                <input type="email" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 pl-10 pr-3" 
                                       name="contact_email" value="<?= htmlspecialchars($formData['contact_email'] ?? '') ?>">
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">ที่อยู่</label>
                        <textarea class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 px-3" 
                                  name="address" rows="2"><?= htmlspecialchars($formData['address'] ?? '') ?></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                             <label class="block text-sm font-medium text-gray-700 mb-1">งบประมาณที่ได้รับจัดสรร (บาท)</label>
                             <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500">฿</span>
                                </div>
                                <input type="text" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 pl-8 pr-3 font-mono" 
                                       name="budget_allocated" value="<?= number_format($formData['budget_allocated'] ?? 0, 2) ?>" onblur="formatCurrency(this)">
                             </div>
                        </div>
                        <div>
                             <label class="block text-sm font-medium text-gray-700 mb-1">ลำดับการแสดงผล</label>
                             <input type="number" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 px-3" 
                                    name="sort_order" value="<?= htmlspecialchars($formData['sort_order'] ?? '0') ?>">
                        </div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                    <div class="flex items-center">
                        <input id="is_active" name="is_active" type="checkbox" class="h-5 w-5 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" 
                               <?= (!isset($formData['is_active']) || $formData['is_active']) ? 'checked' : '' ?>>
                        <label for="is_active" class="ml-2 block text-sm text-gray-900 border-b border-dashed border-gray-400 cursor-pointer">
                            เปิดใช้งานหน่วยงานนี้
                        </label>
                    </div>
                    <div class="flex gap-3">
                        <a href="/admin/organizations" class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium transition-colors">
                            ยกเลิก
                        </a>
                        <button type="submit" class="px-5 py-2.5 rounded-lg bg-blue-600 text-white hover:bg-blue-700 font-medium shadow-sm hover:shadow transition-all flex items-center">
                            <i class="bi bi-save me-2"></i> บันทึกข้อมูล
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
function toggleProvinceField() {
    const region = document.getElementById('region').value;
    const type = document.getElementById('org_type').value;
    const field = document.getElementById('province_field');
    
    if (region === 'provincial' || region === 'regional' || type === 'province') {
        field.style.display = 'block';
    } else {
        field.style.display = 'none';
        // Optional: clear value if hidden? Keeping it for now.
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
document.addEventListener('DOMContentLoaded', toggleProvinceField);
</script>
