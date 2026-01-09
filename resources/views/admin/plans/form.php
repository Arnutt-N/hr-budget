<?php
/**
 * Budget Plan Form View (Create/Edit)
 */
$formData = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_data']);
$plan = $plan ?? null;
$isEdit = $mode === 'edit';
?>

<div class="container mx-auto px-4 py-6 max-w-2xl">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">
        <?= $isEdit ? 'แก้ไขแผนงาน/โครงการ' : 'เพิ่มแผนงาน/โครงการใหม่' ?>
    </h1>

    <!-- Flash Messages -->
    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?= htmlspecialchars($_SESSION['flash_error']) ?>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <!-- Form -->
    <form method="POST" action="<?= $isEdit ? "/admin/plans/{$plan['id']}" : '/admin/plans' ?>" class="bg-white rounded-lg shadow p-6">
        
        <div class="mb-4">
            <label class="block text-gray-700 font-medium mb-2">ปีงบประมาณ <span class="text-red-500">*</span></label>
            <input type="number" name="fiscal_year" value="<?= htmlspecialchars($formData['fiscal_year'] ?? $plan['fiscal_year'] ?? $fiscalYear) ?>" 
                   class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:border-blue-500" required<?= $isEdit ? ' readonly' : '' ?>>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 font-medium mb-2">รหัสแผนงาน/โครงการ <span class="text-red-500">*</span></label>
            <input type="text" name="code" value="<?= htmlspecialchars($formData['code'] ?? $plan['code'] ?? '') ?>" 
                   class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:border-blue-500" required maxlength="50">
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 font-medium mb-2">ชื่อแผนงาน/โครงการ (ไทย) <span class="text-red-500">*</span></label>
            <input type="text" name="name_th" value="<?= htmlspecialchars($formData['name_th'] ?? $plan['name_th'] ?? '') ?>" 
                   class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:border-blue-500" required maxlength="500">
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 font-medium mb-2">ชื่อแผนงาน/โครงการ (English)</label>
            <input type="text" name="name_en" value="<?= htmlspecialchars($formData['name_en'] ?? $plan['name_en'] ?? '') ?>" 
                   class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:border-blue-500" maxlength="500">
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 font-medium mb-2">คำอธิบาย</label>
            <textarea name="description" rows="3" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:border-blue-500"><?= htmlspecialchars($formData['description'] ?? $plan['description'] ?? '') ?></textarea>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 font-medium mb-2">ประเภท <span class="text-red-500">*</span></label>
            <select name="plan_type" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:border-blue-500" required>
                <?php
                $currentType = $formData['plan_type'] ?? $plan['plan_type'] ?? '';
                $types = [
                    'program' => 'แผนงาน',
                    'output' => 'ผลผลิต',
                    'activity' => 'กิจกรรมหลัก',
                    'project' => 'โครงการ'
                ];
                foreach ($types as $value => $label):
                ?>
                    <option value="<?= $value ?>" <?= $currentType === $value ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 font-medium mb-2">แผนงาน/โครงการแม่</label>
            <select name="parent_id" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:border-blue-500">
                <option value="">-- ไม่มี (เป็น Root) --</option>
                <?php 
                $currentParent = $formData['parent_id'] ?? $plan['parent_id'] ?? '';
                foreach ($plans as $p):
                    if ($isEdit && $p['id'] == $plan['id']) continue; // ไม่แสน่งตัวเอง
                    $indent = str_repeat('&nbsp;&nbsp;', ($p['level'] - 1) * 2);
                ?>
                    <option value="<?= $p['id'] ?>" <?= $currentParent == $p['id'] ? 'selected' : '' ?>>
                        <?= $indent ?><?= htmlspecialchars($p['name_th']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <p class="text-sm text-gray-500 mt-1">ระดับจะถูกคำนวณอัตโนมัติจาก Parent</p>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 font-medium mb-2">หน่วยงานรับผิดชอบ</label>
            <select name="division_id" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:border-blue-500">
                <option value="">-- ไม่ระบุ --</option>
                <?php 
                $currentDivision = $formData['division_id'] ?? $plan['division_id'] ?? '';
                foreach ($organizations as $org):
                ?>
                        <?= htmlspecialchars($org['name']) ?>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-6">
            <label class="block text-gray-700 font-medium mb-2">ลำดับการแสดง</label>
            <input type="number" name="sort_order" value="<?= htmlspecialchars($formData['sort_order'] ?? $plan['sort_order'] ?? 0) ?>" 
                   class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:border-blue-500">
        </div>

        <div class="flex gap-2">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                <?= $isEdit ? 'บันทึกการแก้ไข' : 'เพิ่มแผนงาน/โครงการ' ?>
            </button>
            <a href="/admin/plans?year=<?= $fiscalYear ?>" class="bg-gray-300 text-gray-700 px-6 py-2 rounded hover:bg-gray-400">ยกเลิก</a>
        </div>
    </form>
</div>
