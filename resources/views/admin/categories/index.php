<?php
/**
 * Admin Budget Categories Index View
 */
?>
<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="mb-0"><i class="bi bi-diagram-3 me-2"></i>จัดการหมวดหมู่งบประมาณ</h2>
    </div>
    <div class="col-md-6 text-end">
        <a href="/admin/categories/create" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> เพิ่มหมวดหมู่
        </a>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 40%">ชื่อหมวดหมู่</th>
                        <th style="width: 15%">รหัส</th>
                        <th style="width: 10%" class="text-center">ระดับ</th>
                        <th style="width: 15%" class="text-center">ประเภท</th>
                        <th style="width: 10%" class="text-center">สถานะ</th>
                        <th style="width: 10%" class="text-end">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($categories)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">ไม่พบข้อมูล</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td>
                                <div style="padding-left: <?= $cat['depth'] * 24 ?>px">
                                    <?php if ($cat['depth'] > 0): ?>
                                        <i class="bi bi-arrow-return-right text-muted me-2"></i>
                                    <?php endif; ?>
                                    
                                    <?php if ($cat['depth'] == 0): ?>
                                        <strong><?= htmlspecialchars($cat['name_th']) ?></strong>
                                    <?php else: ?>
                                        <?= htmlspecialchars($cat['name_th']) ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($cat['code']) ?></span></td>
                            <td class="text-center"><span class="badge rounded-pill bg-secondary"><?= $cat['level'] ?></span></td>
                            <td class="text-center">
                                <?php if (!empty($cat['is_plan'])): ?>
                                    <span class="badge bg-info text-dark">แผนงาน</span>
                                    <?php if (!empty($cat['plan_name'])): ?>
                                        <div class="small text-muted"><?= htmlspecialchars($cat['plan_name']) ?></div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($cat['is_active']): ?>
                                    <span class="badge bg-success">ใช้งาน</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">ปิด</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="/admin/categories/<?= $cat['id'] ?>/edit" class="btn btn-outline-primary" title="แก้ไข">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-danger" title="ลบ" onclick="confirmDelete(<?= $cat['id'] ?>)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                                <form id="delete-form-<?= $cat['id'] ?>" action="/admin/categories/<?= $cat['id'] ?>/delete" method="POST" style="display: none;"></form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function confirmDelete(id) {
    if (confirm('คุณต้องการลบหมวดหมู่นี้ใช่หรือไม่?')) {
        document.getElementById('delete-form-' + id).submit();
    }
}
</script>
