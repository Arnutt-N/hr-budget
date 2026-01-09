<?php
/**
 * Admin Organizations Index View
 */
use App\Models\Organization;

$typeLabels = Organization::getTypeLabels();
$regionLabels = Organization::getRegionLabels();

// Get filters from query string or default
$filterType = $_GET['type'] ?? '';
$filterRegion = $_GET['region'] ?? '';
?>
<div class="row mb-4 align-items-center">
    <div class="col-md-4">
        <h2 class="mb-0 text-gray-800 text-2xl font-bold"><i class="bi bi-building me-2"></i>จัดการโครงสร้างหน่วยงาน</h2>
    </div>
    <div class="col-md-8 text-end">
        <form action="" method="GET" class="d-inline-flex gap-2 align-items-center">
            <select name="type" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">-- ทุกประเภทหน่วยงาน --</option>
                <?php foreach ($typeLabels as $val => $label): ?>
                    <option value="<?= $val ?>" <?= $filterType === $val ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
            
            <select name="region" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">-- ทุกส่วน --</option>
                <?php foreach ($regionLabels as $val => $label): ?>
                    <option value="<?= $val ?>" <?= $filterRegion === $val ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
            
            <?php if ($filterType || $filterRegion): ?>
                <a href="/admin/organizations" class="btn btn-outline-secondary btn-sm" title="ล้างตัวกรอง">
                    <i class="bi bi-x-lg"></i>
                </a>
            <?php endif; ?>

            <a href="/admin/organizations/create" class="btn btn-primary btn-sm ms-2">
                <i class="bi bi-plus-lg me-1"></i> เพิ่มหน่วยงาน
            </a>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0 rounded-lg">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th style="width: 35%" class="px-4 py-3">ชื่อหน่วยงาน</th>
                        <th style="width: 15%" class="px-4 py-3">รหัส</th>
                        <th style="width: 15%" class="text-center px-4 py-3">ประเภท</th>
                        <th style="width: 10%" class="text-center px-4 py-3">ส่วน</th>
                        <th style="width: 15%" class="text-end px-4 py-3">งบจัดสรร</th>
                        <th style="width: 10%" class="text-end px-4 py-3">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php if (empty($organizations)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            ไม่พบข้อมูลหน่วยงาน
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($organizations as $org): ?>
                        <tr class="<?= !$org['is_active'] ? 'bg-gray-50 text-muted' : '' ?>">
                            <td class="px-4 py-3">
                                <!-- Indentation logic needs to be handled via level since depth might not be available in flat list filtered -->
                                <div style="padding-left: <?= isset($org['depth']) ? $org['depth'] * 20 : ($org['level'] * 20) ?>px">
                                    <?php if ((isset($org['depth']) && $org['depth'] > 0) || $org['level'] > 0): ?>
                                        <i class="bi bi-arrow-return-right text-gray-400 me-2 text-sm"></i>
                                    <?php endif; ?>
                                    
                                    <span class="fw-medium text-gray-800"><?= htmlspecialchars($org['name_th']) ?></span>
                                    <?php if (!empty($org['abbreviation'])): ?>
                                        <span class="text-muted text-sm ms-1">(<?= htmlspecialchars($org['abbreviation']) ?>)</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="font-monospace text-sm bg-gray-100 text-gray-600 px-2 py-1 rounded">
                                    <?= htmlspecialchars($org['code']) ?>
                                </span>
                            </td>
                            <td class="text-center px-4 py-3">
                                <?php
                                    $typeKey = $org['org_type'] ?? 'division';
                                    $typeName = $typeLabels[$typeKey] ?? $typeKey;
                                    
                                    $badgeClass = match($typeKey) {
                                        'ministry' => 'bg-purple-100 text-purple-800',
                                        'department' => 'bg-indigo-100 text-indigo-800',
                                        'division' => 'bg-blue-100 text-blue-800',
                                        'section' => 'bg-sky-100 text-sky-800',
                                        'province' => 'bg-emerald-100 text-emerald-800',
                                        'office' => 'bg-teal-100 text-teal-800',
                                        default => 'bg-gray-100 text-gray-800'
                                    };
                                ?>
                                <span class="badge rounded-pill <?= $badgeClass ?> border-0 fw-normal">
                                    <?= $typeName ?>
                                </span>
                            </td>
                            <td class="text-center px-4 py-3">
                                <?php
                                    $regionKey = $org['region'] ?? 'central';
                                    $regionName = $regionLabels[$regionKey] ?? $regionKey;
                                    $isProvince = $regionKey === 'provincial' || $regionKey === 'regional';
                                ?>
                                <span class="text-sm <?= $isProvince ? 'text-emerald-600' : 'text-gray-500' ?>">
                                    <?= $regionName ?>
                                </span>
                                <?php if (!empty($org['province_code'])): ?>
                                    <small class="text-xs text-muted d-block"><?= htmlspecialchars($org['province_code']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td class="text-end px-4 py-3 font-monospace text-gray-700">
                                <?= number_format($org['budget_allocated'], 2) ?>
                            </td>
                            <td class="text-end px-4 py-3">
                                <div class="btn-group">
                                    <a href="/admin/organizations/<?= $org['id'] ?>/edit" class="btn btn-sm btn-link text-blue-600 hover:text-blue-800 p-1" title="แก้ไข">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-link text-red-500 hover:text-red-700 p-1" title="ลบ" onclick="confirmDelete(<?= $org['id'] ?>)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                                <form id="delete-form-<?= $org['id'] ?>" action="/admin/organizations/<?= $org['id'] ?>/delete" method="POST" style="display: none;"></form>
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
    if (confirm('คุณต้องการลบหน่วยงานนี้ใช่หรือไม่?\nหากลบแล้ว หน่วยงานย่อย (ถ้ามี) จะถูกลบไปด้วย')) {
        document.getElementById('delete-form-' + id).submit();
    }
}
</script>
