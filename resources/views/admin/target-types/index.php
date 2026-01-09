<?php
/**
 * Admin Target Types Index View
 */
?>
<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="mb-0"><i class="bi bi-bullseye me-2"></i>จัดการประเภทเป้าหมาย</h2>
    </div>
    <div class="col-md-6 text-end">
        <a href="/admin/target-types/create" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> เพิ่มประเภทใหม่
        </a>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>รหัส</th>
                        <th>ชื่อประเภท</th>
                        <th>คำอธิบาย</th>
                        <th class="text-center">สถานะ</th>
                        <th class="text-end">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($types)): ?>
                        <tr><td colspan="5" class="text-center py-4">ไม่พบข้อมูล</td></tr>
                    <?php else: ?>
                        <?php foreach ($types as $t): ?>
                        <tr>
                            <td><code><?= htmlspecialchars($t['code']) ?></code></td>
                            <td><?= htmlspecialchars($t['name_th']) ?></td>
                            <td class="text-muted"><?= htmlspecialchars($t['description'] ?? '') ?></td>
                            <td class="text-center">
                                <?php if ($t['is_active']): ?>
                                    <span class="badge bg-success">ใช้งาน</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">ปิด</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="/admin/target-types/<?= $t['id'] ?>/edit" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                <button onclick="deleteType(<?= $t['id'] ?>)" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                <form id="delete-form-<?= $t['id'] ?>" action="/admin/target-types/<?= $t['id'] ?>/delete" method="POST" style="display:none;"></form>
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
function deleteType(id) {
    if(confirm('ยืนยันการลบ?')) document.getElementById('delete-form-'+id).submit();
}
</script>
