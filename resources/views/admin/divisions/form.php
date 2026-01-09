<?php
/**
 * Division Form View (Create/Edit)
 */
$formData = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_data']);
$division = $division ?? null;
$isEdit = $mode === 'edit';
?>

<div class="container mx-auto px-4 py-6 max-w-2xl">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">
        <?= $isEdit ? 'แก้ไขหน่วยงาน' : 'เพิ่มหน่วยงานใหม่' ?>
    </h1>

    <!-- Flash Messages -->
    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?= htmlspecialchars($_SESSION['flash_error']) ?>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <!-- Form -->
    <form method="POST" action="<?= $isEdit ? "/admin/divisions/{$division['id']}" : '/admin/divisions' ?>" class="bg-white rounded-lg shadow p-6">
        
        <div class="mb-4">
            <label class="block text-gray-700 font-medium mb-2">รหัสหน่วยงาน <span class="text-red-500">*</span></label>
            <input type="text" name="code" value="<?= htmlspecialchars($formData['code'] ?? $division['code'] ?? '') ?>" 
                   class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:border-blue-500" required maxlength="20">
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 font-medium mb-2">ชื่อหน่วยงาน (ไทย) <span class="text-red-500">*</span></label>
            <input type="text" name="name_th" value="<?= htmlspecialchars($formData['name_th'] ?? $division['name_th'] ?? '') ?>" 
                   class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:border-blue-500" required maxlength="255">
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 font-medium mb-2">ชื่อหน่วยงาน (English)</label>
            <input type="text" name="name_en" value="<?= htmlspecialchars($formData['name_en'] ?? $division['name_en'] ?? '') ?>" 
                   class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:border-blue-500" maxlength="255">
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 font-medium mb-2">ชื่อย่อ</label>
            <input type="text" name="short_name" value="<?= htmlspecialchars($formData['short_name'] ?? $division['short_name'] ?? '') ?>" 
                   class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:border-blue-500" maxlength="50">
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 font-medium mb-2">ประเภท</label>
            <select name="type" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:border-blue-500">
                <?php
                $currentType = $formData['type'] ?? $division['type'] ?? 'central';
                $types = [
                    'central' => 'ส่วนกลาง',
                    'regional' => 'ภูมิภาค',
                    'provincial' => 'จังหวัด'
                ];
                foreach ($types as $value => $label):
                ?>
                    <option value="<?= $value ?>" <?= $currentType === $value ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-6">
            <label class="block text-gray-700 font-medium mb-2">ลำดับการแสดง</label>
            <input type="number" name="sort_order" value="<?= htmlspecialchars($formData['sort_order'] ?? $division['sort_order'] ?? 0) ?>" 
                   class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:border-blue-500">
        </div>

        <div class="flex gap-2">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                <?= $isEdit ? 'บันทึกการแก้ไข' : 'เพิ่มหน่วยงาน' ?>
            </button>
            <a href="/admin/divisions" class="bg-gray-300 text-gray-700 px-6 py-2 rounded hover:bg-gray-400">ยกเลิก</a>
        </div>
    </form>
</div>
