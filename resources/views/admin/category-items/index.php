<?php
/**
 * Admin Budget Category Items Index View
 * Tree view display with hierarchy
 */
?>
<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="mb-0"><i class="bi bi-diagram-3 me-2"></i>จัดการหมวดหมู่รายจ่าย</h2>
    </div>
    <div class="col-md-6 text-end">
        <a href="/admin/category-items/create" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> เพิ่มหมวดหมู่
        </a>
    </div>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="showDeleted" <?= $showDeleted ? 'checked' : '' ?> onchange="toggleDeleted()">
            <label class="form-check-label" for="showDeleted">แสดงรายการที่ถูกลบ</label>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 40%">ชื่อหมวดหมู่</th>
                        <th style="width: 12%">รหัส</th>
                        <th style="width: 8%" class="text-center">ระดับ</th>
                        <th style="width: 10%" class="text-center">ลำดับ</th>
                        <th style="width: 10%" class="text-center">สถานะ</th>
                        <th style="width: 20%" class="text-end">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($tree)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">ไม่พบข้อมูล</td>
                    </tr>
                    <?php else: ?>
                        <?php 
                        function renderTree($nodes, $depth = 0) {
                            foreach ($nodes as $item):
                                $isDeleted = !empty($item['deleted_at']);
                                $rowClass = $isDeleted ? 'table-secondary text-muted' : '';
                        ?>
                        <tr class="<?= $rowClass ?>">
                            <td>
                                <div style="padding-left: <?= $depth * 24 ?>px">
                                    <?php if ($depth > 0): ?>
                                        <i class="bi bi-arrow-return-right text-muted me-2"></i>
                                    <?php endif; ?>
                                    
                                    <?php if ($depth == 0): ?>
                                        <strong><?= htmlspecialchars($item['name']) ?></strong>
                                    <?php else: ?>
                                        <?= htmlspecialchars($item['name']) ?>
                                    <?php endif; ?>
                                    
                                    <?php if ($isDeleted): ?>
                                        <span class="badge bg-danger ms-2">ถูกลบ</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($item['code']) ?></span></td>
                            <td class="text-center"><span class="badge rounded-pill bg-secondary"><?= $item['level'] ?></span></td>
                            <td class="text-center"><?= $item['sort_order'] ?></td>
                            <td class="text-center">
                                <?php if ($item['is_active']): ?>
                                    <span class="badge bg-success">เปิดใช้งาน</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">ปิด</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <?php if (!$isDeleted): ?>
                                        <a href="/admin/category-items/<?= $item['id'] ?>/edit" class="btn btn-outline-primary" title="แก้ไข">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-warning" title="Toggle สถานะ" onclick="confirmToggle(<?= $item['id'] ?>)">
                                            <i class="bi bi-power"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-danger" title="ลบ" onclick="confirmDelete(<?= $item['id'] ?>)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-outline-success" title="กู้คืน" onclick="confirmRestore(<?= $item['id'] ?>)">
                                            <i class="bi bi-arrow-counterclockwise"></i> กู้คืน
                                        </button>
                                    <?php endif; ?>
                                </div>
                                <form id="delete-form-<?= $item['id'] ?>" action="/admin/category-items/<?= $item['id'] ?>/delete" method="POST" style="display: none;"></form>
                                <form id="restore-form-<?= $item['id'] ?>" action="/admin/category-items/<?= $item['id'] ?>/restore" method="POST" style="display: none;"></form>
                                <form id="toggle-form-<?= $item['id'] ?>" action="/admin/category-items/<?= $item['id'] ?>/toggle" method="POST" style="display: none;"></form>
                            </td>
                        </tr>
                        <?php 
                                if (!empty($item['children'])) {
                                    renderTree($item['children'], $depth + 1);
                                }
                            endforeach;
                        }
                        renderTree($tree);
                        ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function toggleDeleted() {
    const showDeleted = document.getElementById('showDeleted').checked;
    window.location.href = '/admin/category-items' + (showDeleted ? '?show_deleted=1' : '');
}

function confirmDelete(id) {
    if (confirm('คุณต้องการลบหมวดหมู่นี้ใช่หรือไม่? (จะทำ Soft Delete)')) {
        document.getElementById('delete-form-' + id).submit();
    }
}

function confirmRestore(id) {
    if (confirm('คุณต้องการกู้คืนหมวดหมู่นี้ใช่หรือไม่?')) {
        document.getElementById('restore-form-' + id).submit();
    }
}

function confirmToggle(id) {
    if (confirm('คุณต้องการเปลี่ยนสถานะการใช้งานใช่หรือไม่?')) {
        document.getElementById('toggle-form-' + id).submit();
    }
}
</script>
