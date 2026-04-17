<?php
/**
 * Admin Organizations Index View - Redesigned with Filters and Modal
 */
use App\Models\Organization;

$currentFilters = $filters ?? [];
?>
<div class="container-fluid px-4 py-6">
    <!-- Header with Add Button -->
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-white flex items-center gap-3">
            <i data-lucide="building-2" class="w-8 h-8 text-primary-500"></i>
            จัดการโครงสร้างหน่วยงาน
        </h2>
        <button type="button" class="btn btn-primary" onclick="openModal('create')">
            <i data-lucide="circle-plus" class="w-4 h-4"></i>
            เพิ่มหน่วยงาน
        </button>
    </div>

    <!-- Filter Bar (Image-style: Dark Cards) -->
    <form method="GET" action="<?= \App\Core\View::url('admin/organizations') ?>" class="mb-6">
        <div class="flex gap-3 items-center">
            <!-- Ministry Dropdown -->
            <div class="flex-1">
                <select name="ministry_id" 
                        class="w-full bg-slate-800/50 border border-slate-600 text-slate-200 rounded-lg px-4 py-2.5 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                    <option value="">ทุกกระทรวง</option>
                    <?php foreach ($ministries as $m): ?>
                        <option value="<?= $m['id'] ?>" <?= isset($currentFilters['ministry_id']) && $currentFilters['ministry_id'] == $m['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($m['name_th']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Department Dropdown -->
            <div class="flex-1">
                <select name="department_id" 
                        class="w-full bg-slate-800/50 border border-slate-600 text-slate-200 rounded-lg px-4 py-2.5 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                    <option value="">ทุกกรม</option>
                    <?php foreach ($departments as $d): ?>
                        <option value="<?= $d['id'] ?>" <?= isset($currentFilters['department_id']) && $currentFilters['department_id'] == $d['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($d['name_th']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Region Dropdown -->
            <div class="flex-1">
                <select name="region" 
                        class="w-full bg-slate-800/50 border border-slate-600 text-slate-200 rounded-lg px-4 py-2.5 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                    <option value="">ทุกส่วนราชการ</option>
                    <?php foreach ($regionLabels as $val => $label): ?>
                        <option value="<?= $val ?>" <?= isset($currentFilters['region']) && $currentFilters['region'] == $val ? 'selected' : '' ?>>
                            <?= $label ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Search Input -->
            <div class="flex-1">
                <input type="text" 
                       name="search" 
                       value="<?= htmlspecialchars($currentFilters['search'] ?? '') ?>"
                       placeholder="พิมพ์ค้นหา..."
                       class="w-full bg-slate-800/50 border border-slate-600 text-slate-200 rounded-lg px-4 py-2.5 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
            </div>

            <!-- Search Button -->
            <button type="submit" class="btn btn-primary px-6">
                <i data-lucide="search" class="w-4 h-4"></i>
                ค้นหา
            </button>

            <!-- Reset Button (Always visible) -->
            <a href="<?= \App\Core\View::url('admin/organizations') ?>" class="btn btn-secondary px-3 h-[41px] flex items-center justify-center" title="ล้างค่า">
                <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
            </a>
        </div>
    </form>

    <!-- Table -->
    <div class="bg-slate-900/50 backdrop-blur-sm border border-slate-700 rounded-xl shadow-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-800/80 backdrop-blur-sm border-b border-slate-700">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider w-1/2">กอง</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider w-1/3">กลุ่มงาน</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-slate-400 uppercase tracking-wider w-[150px]">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    <?php if (empty($organizations)): ?>
                    <tr>
                        <td colspan="3" class="px-6 py-12 text-center text-slate-500">
                            <i data-lucide="folder-open" class="w-12 h-12 mx-auto mb-3 opacity-50"></i>
                            <p class="text-sm">ไม่พบข้อมูลหน่วยงาน</p>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($organizations as $org): ?>
                        <tr class="hover:bg-white/5 transition-colors <?= !$org['is_active'] ? 'opacity-50' : '' ?>">
                            <!-- Division Name (Only Division/Section level, no Ministry/Dept) -->
                            <td class="px-6 py-4">
                                <?php if ($org['org_type'] === 'division' || $org['org_type'] === 'section'): ?>
                                    <div class="flex flex-col">
                                        <span class="font-medium text-slate-200"><?= htmlspecialchars($org['name_th']) ?></span>
                                        <?php if (!empty($org['abbreviation'])): ?>
                                            <span class="text-slate-500 text-xs">(<?= htmlspecialchars($org['abbreviation']) ?>)</span>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-slate-500 text-sm">-</span>
                                <?php endif; ?>
                            </td>
                            
                            <!-- Group Info -->
                            <td class="px-6 py-4">
                                <div class="flex flex-col gap-0.5 text-sm text-slate-300">
                                    <?php if (!empty($org['provincial_group'])): ?>
                                        <span class="text-emerald-400"><?= htmlspecialchars($org['provincial_group']) ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($org['provincial_zone'])): ?>
                                        <span class="text-xs text-slate-500">เขต: <?= htmlspecialchars($org['provincial_zone']) ?></span>
                                    <?php endif; ?>
                                    <?php if (empty($org['provincial_group']) && empty($org['provincial_zone'])): ?>
                                        <span class="text-slate-500 text-xs">-</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            
                            <!-- Actions (Match budgets/list icons) -->
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button type="button" 
                                            class="btn btn-icon btn-ghost-primary" 
                                            title="เรียกดู"
                                            onclick="viewOrg(<?= $org['id'] ?>)">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </button>
                                    <button type="button" 
                                            class="btn btn-icon btn-ghost-warning" 
                                            title="แก้ไข"
                                            onclick="openModal('edit', <?= $org['id'] ?>)">
                                        <i data-lucide="square-pen" class="w-4 h-4"></i>
                                    </button>
                                    <button type="button" 
                                            class="btn btn-icon text-red-400 hover:text-red-300 hover:bg-red-400/10" 
                                            title="ลบ" 
                                            onclick="confirmDelete(<?= $org['id'] ?>)">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </div>
                                <form id="delete-form-<?= $org['id'] ?>" action="<?= \App\Core\View::url("admin/organizations/{$org['id']}/delete") ?>" method="POST" style="display: none;"></form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Container -->
<div id="org-modal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4" onclick="closeModalOnBackdrop(event)">
    <div class="bg-slate-900 border border-slate-700 rounded-xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
        <div id="modal-content" class="p-6">
            <!-- Form content will be loaded here -->
            <div class="text-center py-12">
                <i data-lucide="loader" class="w-12 h-12 mx-auto mb-3 text-primary-500 animate-spin"></i>
                <p class="text-slate-400">กำลังโหลด...</p>
            </div>
        </div>
    </div>
</div>

<!-- View Modal (Simpler Read-only) -->
<div id="view-modal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4" onclick="closeViewModal(event)">
    <div class="bg-slate-900 border border-slate-700 rounded-xl shadow-2xl w-full max-w-2xl" onclick="event.stopPropagation()">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-white">ข้อมูลหน่วยงาน</h3>
                <button type="button" onclick="closeViewModal()" class="text-slate-400 hover:text-white">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            <div id="view-content" class="space-y-3 text-slate-300">
                <!-- View details will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Flash Message Handler -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Check for flash messages and show SweetAlert
    const flashSuccess = document.getElementById('flash-success');
    if (flashSuccess) {
        Swal.fire({
            icon: 'success',
            title: 'สำเร็จ',
            text: flashSuccess.dataset.message,
            timer: 2000,
            showConfirmButton: false,
            background: '#1e293b',
            color: '#f1f5f9'
        });
    }
    
    const flashError = document.getElementById('flash-error');
    if (flashError) {
        Swal.fire({
            icon: 'error',
            title: 'ข้อผิดพลาด',
            text: flashError.dataset.message,
            confirmButtonText: 'ตกลง',
            confirmButtonColor: '#0ea5e9',
            background: '#1e293b',
            color: '#f1f5f9'
        });
    }
});

const BASE_URL = '<?= \App\Core\View::url('') ?>';

function openModal(mode, id = null) {
    const modal = document.getElementById('org-modal');
    const content = document.getElementById('modal-content');
    modal.classList.remove('hidden');
    
    // Add ?ajax=1 to ensure we get partial view without layout, and timestamp to prevent caching
    const url = mode === 'create' 
        ? `${BASE_URL}/admin/organizations/create?ajax=1&t=${Date.now()}` 
        : `${BASE_URL}/admin/organizations/${id}/edit?ajax=1&t=${Date.now()}`;
    
    fetch(url)
        .then(r => r.text())
        .then(html => {
            content.innerHTML = html;
            // Re-initialize icons for the new content
            if(typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        })
        .catch(err => {
            content.innerHTML = '<p class="text-red-400 text-center py-8">เกิดข้อผิดพลาดในการโหลดฟอร์ม</p>';
        });
}

function closeModal() {
    document.getElementById('org-modal').classList.add('hidden');
}

function closeModalOnBackdrop(event) {
    if (event.target.id === 'org-modal') {
        closeModal();
    }
}

function viewOrg(id) {
    const modal = document.getElementById('view-modal');
    const content = document.getElementById('view-content');
    modal.classList.remove('hidden');
    
    // Use the dedicated show endpoint with timestamp
    fetch(`${BASE_URL}/admin/organizations/${id}?ajax=1&t=${Date.now()}`)
        .then(r => r.text())
        .then(html => {
            content.innerHTML = html;
            // Re-initialize icons
            if(typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        })
        .catch(err => {
            content.innerHTML = '<p class="text-red-400 p-4 text-center">เกิดข้อผิดพลาดในการโหลดข้อมูล</p>';
        });
}

function closeViewModal(event) {
    if (!event || event.target.id === 'view-modal') {
        document.getElementById('view-modal').classList.add('hidden');
    }
}

function confirmDelete(id) {
    Swal.fire({
        title: 'ยืนยันการลบ?',
        text: "คุณต้องการลบหน่วยงานนี้ใช่หรือไม่? หากมีข้อมูลย่อยอาจถูกลบไปด้วย",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'ลบข้อมูล',
        cancelButtonText: 'ยกเลิก',
        background: '#1e293b',
        color: '#f1f5f9'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-form-' + id).submit();
        }
    });
}
</script>
