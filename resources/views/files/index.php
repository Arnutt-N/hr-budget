<div class="animate-fade-in">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white flex items-center gap-2">
                <i data-lucide="archive" class="w-8 h-8"></i> คลังเอกสาร
            </h1>
            <p class="text-dark-muted text-sm mt-1">จัดเก็บเอกสารตามโครงสร้างงบประมาณ</p>
        </div>
        
        <div class="flex items-center gap-3">
            <!-- Fiscal Year Selector -->
            <select id="yearSelector" class="input" onchange="changeYear(this.value)">
                <?php foreach ($availableYears as $y): ?>
                <option value="<?= $y['fiscal_year'] ?>" <?= $y['fiscal_year'] == $fiscalYear ? 'selected' : '' ?>>
                    ปี <?= $y['fiscal_year'] ?>
                </option>
                <?php endforeach; ?>
            </select>
            
            <?php if ($currentFolder): ?>
            <button onclick="showUploadModal()" class="btn btn-primary">
                <i data-lucide="upload" class="w-4 h-4"></i> อัปโหลดไฟล์
            </button>
            <button onclick="showCreateFolderModal()" class="btn btn-secondary">
                <i data-lucide="folder-plus" class="w-4 h-4"></i> สร้างโฟลเดอร์ย่อย
            </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if (!empty($_SESSION['success'])): ?>
    <div class="bg-green-500/20 text-green-400 px-4 py-3 rounded-lg mb-4 flex items-center gap-2">
        <i data-lucide="check-circle" class="w-5 h-5"></i>
        <?= $_SESSION['success']; unset($_SESSION['success']); ?>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($_SESSION['error'])): ?>
    <div class="bg-red-500/20 text-red-400 px-4 py-3 rounded-lg mb-4 flex items-center gap-2">
        <i data-lucide="alert-circle" class="w-5 h-5"></i>
        <?= $_SESSION['error']; unset($_SESSION['error']); ?>
    </div>
    <?php endif; ?>

    <!-- Breadcrumb -->
    <?php if (!empty($breadcrumb)): ?>
    <nav class="flex items-center gap-2 text-sm mb-4 bg-dark-card border border-dark-border rounded-lg px-4 py-2">
        <a href="<?= \App\Core\View::url('/files?year=' . $fiscalYear) ?>" class="text-dark-muted hover:text-white transition-colors flex items-center gap-1">
            <i data-lucide="home" class="w-4 h-4"></i> ปี <?= $fiscalYear ?>
        </a>
        <?php foreach ($breadcrumb as $bc): ?>
        <span class="text-dark-muted">/</span>
        <a href="<?= \App\Core\View::url('/files?folder=' . $bc['id']) ?>" 
           class="<?= end($breadcrumb)['id'] == $bc['id'] ? 'text-white font-medium' : 'text-dark-muted hover:text-white transition-colors' ?>">
            <?= htmlspecialchars($bc['name']) ?>
        </a>
        <?php endforeach; ?>
    </nav>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Left: Folder Tree -->
        <div class="lg:col-span-1">
            <div class="bg-dark-card border border-dark-border rounded-xl p-4 sticky top-4">
                <h3 class="text-sm font-medium text-dark-muted mb-3 flex items-center gap-2">
                    <i data-lucide="network" class="w-4 h-4"></i> โครงสร้างโฟลเดอร์
                </h3>
                
                <div class="space-y-1 max-h-[500px] overflow-y-auto">
                    <a href="<?= \App\Core\View::url('/files?year=' . $fiscalYear) ?>" 
                       class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-dark-hover transition-colors <?= !$currentFolder ? 'bg-dark-hover text-white' : 'text-dark-text' ?>">
                        <i data-lucide="home" class="w-4 h-4"></i>
                        <span>ปี <?= $fiscalYear ?></span>
                    </a>
                    
                    <?php foreach ($folderTree as $folder): ?>
                    <?php include __DIR__ . '/_folder_item.php'; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Right: Content Area -->
        <div class="lg:col-span-3">
            <div class="bg-dark-card border border-dark-border rounded-xl">
                
                <!-- Subfolders Grid -->
                <?php if (!empty($folders)): ?>
                <div class="p-4 <?= !empty($files) ? 'border-b border-dark-border' : '' ?>">
                    <h3 class="text-sm font-medium text-dark-muted mb-3 flex items-center gap-2">
                        <i data-lucide="folders" class="w-4 h-4"></i> โฟลเดอร์ (<?= count($folders) ?>)
                    </h3>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                        <?php foreach ($folders as $folder): ?>
                        <a href="<?= \App\Core\View::url('/files?folder=' . $folder['id']) ?>" 
                           class="flex flex-col items-center p-4 rounded-lg bg-dark-hover hover:bg-dark-border transition-colors group text-center">
                            <i data-lucide="folder" class="w-10 h-10 text-yellow-400 mb-2"></i>
                            <span class="text-sm text-dark-text group-hover:text-white truncate w-full"><?= htmlspecialchars($folder['name']) ?></span>
                            <span class="text-xs text-dark-muted mt-1">
                                <?= $folder['subfolder_count'] ?? 0 ?> โฟลเดอร์ · <?= $folder['file_count'] ?? 0 ?> ไฟล์
                            </span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Files Table -->
                <div class="p-4">
                    <?php if (!$currentFolder && empty($folders)): ?>
                    <div class="text-center py-12 text-dark-muted">
                        <i data-lucide="folder-open" class="w-12 h-12 mx-auto mb-3 text-dark-muted"></i>
                        <p class="text-lg">ยังไม่มีโฟลเดอร์สำหรับปี <?= $fiscalYear ?></p>
                        <form action="<?= \App\Core\View::url('/files/init') ?>" method="POST" class="mt-4">
                            <?= \App\Core\View::csrf() ?>
                            <input type="hidden" name="year" value="<?= $fiscalYear ?>">
                            <button type="submit" class="btn btn-primary">
                                <i data-lucide="plus" class="w-4 h-4 mr-1"></i> สร้างโฟลเดอร์ตามหมวดงบประมาณ
                            </button>
                        </form>
                    </div>
                    <?php elseif ($currentFolder): ?>
                    <h3 class="text-sm font-medium text-dark-muted mb-3 flex items-center gap-2">
                        <i data-lucide="files" class="w-4 h-4"></i> ไฟล์ (<?= count($files) ?>)
                    </h3>
                    
                    <?php if (empty($files)): ?>
                    <div class="text-center py-8 text-dark-muted border-2 border-dashed border-dark-border rounded-lg">
                        <i data-lucide="file" class="w-10 h-10 mx-auto mb-2 text-dark-muted"></i>
                        <p>ยังไม่มีไฟล์ในโฟลเดอร์นี้</p>
                        <button onclick="showUploadModal()" class="btn btn-primary mt-3">
                            <i data-lucide="upload" class="w-4 h-4 mr-1"></i> อัปโหลดไฟล์
                        </button>
                    </div>
                    <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="text-left text-dark-muted text-sm border-b border-dark-border">
                                    <th class="pb-3 font-medium">ชื่อไฟล์</th>
                                    <th class="pb-3 font-medium">ขนาด</th>
                                    <th class="pb-3 font-medium">อัปโหลดโดย</th>
                                    <th class="pb-3 font-medium">วันที่</th>
                                    <th class="pb-3 font-medium text-right">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($files as $file): ?>
                                <tr class="border-b border-dark-border/50 hover:bg-dark-hover/50">
                                    <td class="py-3">
                                        <div class="flex items-center gap-2">
                                            <i data-lucide="<?= \App\Models\File::getIcon($file['file_type']) ?>" class="w-5 h-5 text-blue-400"></i>
                                            <div>
                                                <span class="text-dark-text"><?= htmlspecialchars($file['original_name']) ?></span>
                                                <?php if ($file['description']): ?>
                                                <p class="text-xs text-dark-muted"><?= htmlspecialchars($file['description']) ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 text-dark-muted text-sm"><?= \App\Models\File::formatSize($file['file_size']) ?></td>
                                    <td class="py-3 text-dark-muted text-sm"><?= htmlspecialchars($file['uploaded_by_name'] ?? '-') ?></td>
                                    <td class="py-3 text-dark-muted text-sm"><?= date('d/m/Y H:i', strtotime($file['created_at'])) ?></td>
                                    <td class="py-3 text-right">
                                        <a href="<?= \App\Core\View::url('/files/' . $file['id'] . '/download') ?>" 
                                           class="text-blue-400 hover:text-blue-300 mr-3" title="ดาวน์โหลด">
                                            <i data-lucide="download" class="w-4 h-4"></i>
                                        </a>
                                        <form action="<?= \App\Core\View::url('/files/' . $file['id'] . '/delete') ?>" method="POST" class="inline" 
                                              onsubmit="return confirm('ต้องการลบไฟล์นี้?')">
                                            <?= \App\Core\View::csrf() ?>
                                            <button type="submit" class="text-red-400 hover:text-red-300" title="ลบ">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Folder Modal -->
<div id="createFolderModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center">
    <div class="bg-dark-card border border-dark-border rounded-xl p-6 w-full max-w-md mx-4">
        <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
            <i data-lucide="folder-plus" class="w-5 h-5"></i> สร้างโฟลเดอร์ย่อย
        </h3>
        <form action="<?= \App\Core\View::url('/folders') ?>" method="POST">
            <?= \App\Core\View::csrf() ?>
            <input type="hidden" name="parent_id" value="<?= $currentFolder['id'] ?? '' ?>">
            <input type="hidden" name="fiscal_year" value="<?= $fiscalYear ?>">
            
            <div class="mb-4">
                <label class="block text-sm text-dark-muted mb-2">ชื่อโฟลเดอร์</label>
                <input type="text" name="name" required class="input w-full" placeholder="ชื่อโฟลเดอร์...">
            </div>
            
            <div class="mb-4">
                <label class="block text-sm text-dark-muted mb-2">รายละเอียด (ถ้ามี)</label>
                <textarea name="description" class="input w-full" rows="2" placeholder="รายละเอียดโฟลเดอร์..."></textarea>
            </div>
            
            <div class="flex justify-end gap-2">
                <button type="button" onclick="hideCreateFolderModal()" class="btn btn-secondary">ยกเลิก</button>
                <button type="submit" class="btn btn-primary">สร้าง</button>
            </div>
        </form>
    </div>
</div>

<!-- Upload Modal -->
<div id="uploadModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center">
    <div class="bg-dark-card border border-dark-border rounded-xl p-6 w-full max-w-md mx-4">
        <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
            <i data-lucide="upload" class="w-5 h-5"></i> อัปโหลดไฟล์
        </h3>
        <form action="<?= \App\Core\View::url('/files/upload') ?>" method="POST" enctype="multipart/form-data">
            <?= \App\Core\View::csrf() ?>
            <input type="hidden" name="folder_id" value="<?= $currentFolder['id'] ?? '' ?>">
            
            <div class="mb-4">
                <label class="block text-sm text-dark-muted mb-2">เลือกไฟล์</label>
                <input type="file" name="file" required class="input w-full">
                <p class="text-xs text-dark-muted mt-1">รองรับ: PDF, Excel, Word, รูปภาพ (ไม่เกิน 10MB)</p>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm text-dark-muted mb-2">รายละเอียด (ถ้ามี)</label>
                <textarea name="description" class="input w-full" rows="2" placeholder="รายละเอียดไฟล์..."></textarea>
            </div>
            
            <div class="flex justify-end gap-2">
                <button type="button" onclick="hideUploadModal()" class="btn btn-secondary">ยกเลิก</button>
                <button type="submit" class="btn btn-primary">อัปโหลด</button>
            </div>
        </form>
    </div>
</div>

<script>
function changeYear(year) {
    window.location.href = '<?= \App\Core\View::url('/files') ?>?year=' + year;
}
function showCreateFolderModal() {
    document.getElementById('createFolderModal').classList.remove('hidden');
    document.getElementById('createFolderModal').classList.add('flex');
}
function hideCreateFolderModal() {
    document.getElementById('createFolderModal').classList.add('hidden');
    document.getElementById('createFolderModal').classList.remove('flex');
}
function showUploadModal() {
    document.getElementById('uploadModal').classList.remove('hidden');
    document.getElementById('uploadModal').classList.add('flex');
}
function hideUploadModal() {
    document.getElementById('uploadModal').classList.add('hidden');
    document.getElementById('uploadModal').classList.remove('flex');
}
</script>
