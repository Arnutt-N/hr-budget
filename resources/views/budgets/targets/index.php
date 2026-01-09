<?php
/**
 * Budget Targets Index View
 */
?>
<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="mb-0"><i class="bi bi-sliders me-2"></i>ตั้งค่าเป้าหมายงบประมาณ</h2>
    </div>
    <div class="col-md-6 text-end">
        <a href="/budgets/targets/create" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> เพิ่มเป้าหมาย
        </a>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body bg-light">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">ปีงบประมาณ</label>
                <input type="number" class="form-control" name="year" value="<?= $filters['fiscal_year'] ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">ประเภทเป้าหมาย</label>
                <select class="form-select" name="type">
                    <option value="">ทั้งหมด</option>
                    <?php foreach ($types as $type): ?>
                        <option value="<?= $type['id'] ?>" <?= ($filters['target_type_id'] == $type['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($type['name_th']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> ค้นหา</button>
            </div>
            <div class="col-md-3 text-end">
                 <a href="/admin/target-types" class="btn btn-outline-secondary btn-sm">
                     <i class="bi bi-gear"></i> จัดการประเภท
                 </a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ปี</th>
                        <th>ระยะเวลา</th>
                        <th>ประเภท</th>
                        <th>เงื่อนไข (หน่วยงาน/หมวด)</th>
                        <th class="text-end">เป้าหมาย</th>
                        <th class="text-end">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($targets)): ?>
                        <tr><td colspan="6" class="text-center py-5 text-muted">ไม่พบข้อมูลตามเงื่อนไข</td></tr>
                    <?php else: ?>
                        <?php foreach ($targets as $item): ?>
                        <tr>
                            <td><?= $item['fiscal_year'] ?></td>
                            <td>
                                <?php if ($item['quarter']): ?>
                                    <span class="badge bg-info text-dark">ไตรมาส <?= $item['quarter'] ?></span>
                                <?php else: ?>
                                    <span class="badge bg-primary">รายปี</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($item['target_type_name']) ?></td>
                            <td>
                                <?php if ($item['org_name']): ?>
                                    <div><small class="text-muted">หน่วยงาน:</small> <?= htmlspecialchars($item['org_name']) ?></div>
                                <?php else: ?>
                                    <div><small class="text-muted">หน่วยงาน:</small> <em>ทุกหน่วยงาน</em></div>
                                <?php endif; ?>
                                
                                <?php if ($item['category_name']): ?>
                                    <div><small class="text-muted">หมวด:</small> <?= htmlspecialchars($item['category_name']) ?></div>
                                <?php else: ?>
                                    <div><small class="text-muted">หมวด:</small> <em>ทุกหมวดหมู่</em></div>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <?php if (!is_null($item['target_percent'])): ?>
                                    <div class="fw-bold text-success"><?= number_format($item['target_percent'], 2) ?>%</div>
                                <?php endif; ?>
                                <?php if (!is_null($item['target_amount'])): ?>
                                    <div class="small text-muted"><?= number_format($item['target_amount'], 2) ?> บาท</div>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="/budgets/targets/<?= $item['id'] ?>/edit" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                <form action="/budgets/targets/<?= $item['id'] ?>/delete" method="POST" class="d-inline" onsubmit="return confirm('ยืนยันการลบ?');">
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
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
