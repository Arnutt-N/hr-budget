<?php
/**
 * Admin Budget Categories Form View
 */
$isEdit = ($mode === 'edit');
$formData = $_SESSION['form_data'] ?? ($category ?? []);
unset($_SESSION['form_data']);
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <div class="row align-items-center">
                    <div class="col">
                        <h4 class="mb-0">
                            <i class="bi bi-<?= $isEdit ? 'pencil-square' : 'plus-circle' ?> me-2"></i>
                            <?= $isEdit ? 'แก้ไขหมวดหมู่' : 'เพิ่มหมวดหมู่ใหม่' ?>
                        </h4>
                    </div>
                </div>
            </div>
            <div class="card-body p-4">
                
                <form action="<?= $isEdit ? "/admin/categories/{$category['id']}/update" : '/admin/categories/store' ?>" method="POST">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label required">รหัสหมวดหมู่ (Code)</label>
                            <input type="text" class="form-control" name="code" value="<?= htmlspecialchars($formData['code'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">หมวดหมู่แม่ (Parent)</label>
                            <select class="form-select" name="parent_id">
                                <option value="">- เป็นหมวดหมู่หลัก -</option>
                                <?php foreach ($parents as $p): ?>
                                    <?php 
                                        // Skip self and children if edit mode (primitive cycle prevention)
                                        if ($isEdit && $p['id'] == $category['id']) continue;
                                    ?>
                                    <option value="<?= $p['id'] ?>" <?= (isset($formData['parent_id']) && $formData['parent_id'] == $p['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($p['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">ชื่อภาษาไทย</label>
                        <input type="text" class="form-control" name="name_th" value="<?= htmlspecialchars($formData['name_th'] ?? '') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">ชื่อภาษาอังกฤษ</label>
                        <input type="text" class="form-control" name="name_en" value="<?= htmlspecialchars($formData['name_en'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">รายละเอียด</label>
                        <textarea class="form-control" name="description" rows="2"><?= htmlspecialchars($formData['description'] ?? '') ?></textarea>
                    </div>

                    <hr class="my-4">
                    
                    <h5 class="mb-3">ตั้งค่าเพิ่มเติม</h5>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="is_plan" name="is_plan" <?= (!empty($formData['is_plan'])) ? 'checked' : '' ?> onchange="togglePlanName()">
                                <label class="form-check-label" for="is_plan">ใช้เป็น "แผนงาน" ด้วย</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" <?= (!isset($formData['is_active']) || $formData['is_active']) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="is_active">เปิดใช้งาน</label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3" id="plan_name_group" style="display: none;">
                        <label class="form-label">ชื่อแผนงาน (ถ้าไม่ระบุ จะใช้ชื่อหมวดหมู่)</label>
                        <input type="text" class="form-control" name="plan_name" value="<?= htmlspecialchars($formData['plan_name'] ?? '') ?>" placeholder="e.g. แผนงานบุคลากรภาครัฐ">
                    </div>

                    <div class="mb-4">
                        <label class="form-label">ลำดับการแสดงผล</label>
                        <input type="number" class="form-control" name="sort_order" value="<?= htmlspecialchars($formData['sort_order'] ?? '0') ?>" style="width: 100px;">
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="/admin/categories" class="btn btn-secondary">ยกเลิก</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i> บันทึกข้อมูล
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function togglePlanName() {
    const isPlan = document.getElementById('is_plan').checked;
    const group = document.getElementById('plan_name_group');
    if (isPlan) {
        group.style.display = 'block';
    } else {
        group.style.display = 'none';
        document.querySelector('input[name="plan_name"]').value = '';
    }
}
// Init state
document.addEventListener('DOMContentLoaded', togglePlanName);
</script>
