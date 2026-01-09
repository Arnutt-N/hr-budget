<?php
$isEdit = ($mode === 'edit');
$formData = $_SESSION['form_data'] ?? ($target ?? []);
unset($_SESSION['form_data']);

$currentYear = date('Y') + 543; // Thai year
?>

<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <h4 class="mb-0"><?= $isEdit ? 'แก้ไขเป้าหมาย' : 'เพิ่มเป้าหมายใหม่' ?></h4>
            </div>
            <div class="card-body p-4">
                <form action="<?= $isEdit ? "/budgets/targets/{$target['id']}/update" : '/budgets/targets/store' ?>" method="POST">
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label required">ประเภทเป้าหมาย</label>
                            <select class="form-select" name="target_type_id" required>
                                <option value="">-- เลือกประเภท --</option>
                                <?php foreach ($types as $type): ?>
                                    <option value="<?= $type['id'] ?>" <?= (isset($formData['target_type_id']) && $formData['target_type_id'] == $type['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($type['name_th']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">ปีงบประมาณ</label>
                            <input type="number" class="form-control" name="fiscal_year" value="<?= $formData['fiscal_year'] ?? $currentYear ?>" required>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label class="form-label">ระยะเวลา (Period)</label>
                            <select class="form-select" name="quarter">
                                <option value="">รายปี (ทั้งปี)</option>
                                <option value="1" <?= (isset($formData['quarter']) && $formData['quarter'] == 1) ? 'selected' : '' ?>>ไตรมาส 1 (ต.ค.-ธ.ค.)</option>
                                <option value="2" <?= (isset($formData['quarter']) && $formData['quarter'] == 2) ? 'selected' : '' ?>>ไตรมาส 2 (ม.ค.-มี.ค.)</option>
                                <option value="3" <?= (isset($formData['quarter']) && $formData['quarter'] == 3) ? 'selected' : '' ?>>ไตรมาส 3 (เม.ย.-มิ.ย.)</option>
                                <option value="4" <?= (isset($formData['quarter']) && $formData['quarter'] == 4) ? 'selected' : '' ?>>ไตรมาส 4 (ก.ค.-ก.ย.)</option>
                            </select>
                        </div>
                    </div>
                    
                    <hr>
                    <h5 class="mb-3">ขอบเขต (Scope) <span class="text-muted fs-6 fw-normal">- เว้นว่างถ้ารวมทั้งหมด</span></h5>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label">หน่วยงาน</label>
                            <select class="form-select" name="organization_id">
                                <option value="">-- ทุกหน่วยงาน --</option>
                                <?php foreach ($orgs as $org): ?>
                                    <option value="<?= $org['id'] ?>" <?= (isset($formData['organization_id']) && $formData['organization_id'] == $org['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($org['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">หมวดหมู่งบประมาณ</label>
                            <select class="form-select" name="category_id">
                                <option value="">-- ทุกหมวดหมู่ --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= (isset($formData['category_id']) && $formData['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <hr>
                    <h5 class="mb-3">ค่าเป้าหมาย (Target Value)</h5>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">เป้าหมายร้อยละ (%)</label>
                            <div class="input-group">
                                <input type="number" step="0.01" class="form-control" name="target_percent" value="<?= $formData['target_percent'] ?? '' ?>" placeholder="e.g. 25.00">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">เป้าหมายจำนวนเงิน (บาท)</label>
                            <input type="text" class="form-control" name="target_amount" value="<?= number_format($formData['target_amount'] ?? 0, 2) ?>" placeholder="ระบุเฉพาะกรณี fix ยอดเงิน" oninput="formatCurrency(this)">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">หมายเหตุ</label>
                        <textarea class="form-control" name="notes" rows="2"><?= htmlspecialchars($formData['notes'] ?? '') ?></textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="/budgets/targets" class="btn btn-secondary">ยกเลิก</a>
                        <button type="submit" class="btn btn-primary">บันทึก</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
function formatCurrency(input) {
    let value = input.value.replace(/,/g, '');
    // simple pass-through for now or use library if available
}
</script>
