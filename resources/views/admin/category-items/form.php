<?php
/**
 * Admin Budget Category Items Form View
 */
$isEdit = ($mode === 'edit');
$formData = $_SESSION['form_data'] ?? ($item ?? []);
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
                            <?= $isEdit ? 'แก้ไขหมวดหมู่รายจ่าย' : 'เพิ่มหมวดหมู่รายจ่ายใหม่' ?>
                        </h4>
                    </div>
                </div>
            </div>
            <div class="card-body p-4">
                
                <form action="<?= $isEdit ? "/admin/category-items/{$item['id']}/update" : '/admin/category-items/store' ?>" method="POST">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label required">รหัสหมวดหมู่ (Code)</label>
                            <input type="text" class="form-control" name="code" value="<?= htmlspecialchars($formData['code'] ?? '') ?>" required>
                            <div class="form-text">ตัวอย่าง: 01, 0101, 010101</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">หมวดหมู่แม่ (Parent)</label>
                            <select class="form-select" name="parent_id">
                                <option value="">- เป็นหมวดหมู่หลัก (Root) -</option>
                                <?php 
                                function renderParentOptions($items, $selectedId = null, $excludeId = null, $depth = 0) {
                                    foreach ($items as $p) {
                                        // Skip self if edit mode
                                        if ($excludeId && $p['id'] == $excludeId) continue;
                                        
                                        $indent = str_repeat('&nbsp;&nbsp;&nbsp;', $depth);
                                        $selected = ($selectedId && $selectedId == $p['id']) ? 'selected' : '';
                                        
                                        echo "<option value=\"{$p['id']}\" {$selected}>{$indent}" . htmlspecialchars($p['name']) . "</option>";
                                        
                                        // Recursively render children
                                        if (!empty($p['children'])) {
                                            renderParentOptions($p['children'], $selectedId, $excludeId, $depth + 1);
                                        }
                                    }
                                }
                                
                                // Build tree for select
                                function buildParentTree($items, $parentId = null) {
                                    $tree = [];
                                    foreach ($items as $item) {
                                        if (($item['parent_id'] ?? null) == $parentId) {
                                            $item['children'] = buildParentTree($items, $item['id']);
                                            $tree[] = $item;
                                        }
                                    }
                                    return $tree;
                                }
                                
                                $parentTree = buildParentTree($parents);
                                renderParentOptions($parentTree, $formData['parent_id'] ?? null, $item['id'] ?? null);
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">ชื่อหมวดหมู่</label>
                        <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($formData['name'] ?? '') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">คำอธิบาย</label>
                        <textarea class="form-control" name="description" rows="2"><?= htmlspecialchars($formData['description'] ?? '') ?></textarea>
                    </div>

                    <hr class="my-4">
                    
                    <h5 class="mb-3">ตั้งค่าเพิ่มเติม</h5>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">ลำดับการแสดงผล</label>
                            <input type="number" class="form-control" name="sort_order" value="<?= htmlspecialchars($formData['sort_order'] ?? '0') ?>" style="width: 120px;">
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mt-4">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" <?= (!isset($formData['is_active']) || $formData['is_active']) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="is_active">เปิดใช้งาน</label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="/admin/category-items" class="btn btn-secondary">ยกเลิก</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i> บันทึกข้อมูล
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.form-label.required::after {
    content: " *";
    color: red;
}
</style>
