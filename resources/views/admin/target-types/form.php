<?php
$isEdit = ($mode === 'edit');
$formData = $_SESSION['form_data'] ?? ($type ?? []);
unset($_SESSION['form_data']);
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <h4 class="mb-0"><?= $isEdit ? 'แก้ไขประเภท' : 'เพิ่มประเภทใหม่' ?></h4>
            </div>
            <div class="card-body p-4">
                <form action="<?= $isEdit ? "/admin/target-types/{$type['id']}/update" : '/admin/target-types/store' ?>" method="POST">
                    
                    <div class="mb-3">
                        <label class="form-label required">รหัส (Code)</label>
                        <input type="text" class="form-control" name="code" value="<?= htmlspecialchars($formData['code'] ?? '') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">ชื่อประเภท</label>
                        <input type="text" class="form-control" name="name_th" value="<?= htmlspecialchars($formData['name_th'] ?? '') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">คำอธิบาย</label>
                        <textarea class="form-control" name="description"><?= htmlspecialchars($formData['description'] ?? '') ?></textarea>
                    </div>

                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label">ลำดับ</label>
                            <input type="number" class="form-control" name="sort_order" value="<?= $formData['sort_order'] ?? 0 ?>">
                        </div>
                        <div class="col-6 d-flex align-items-end">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" name="is_active" <?= (!isset($formData['is_active']) || $formData['is_active']) ? 'checked' : '' ?>>
                                <label class="form-check-label">เปิดใช้งาน</label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="/admin/target-types" class="btn btn-secondary">ยกเลิก</a>
                        <button type="submit" class="btn btn-primary">บันทึก</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
