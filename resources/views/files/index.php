<div class="animate-fade-in space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-blue-400 via-sky-400 to-cyan-300 flex items-center gap-3">
                <div class="p-2 rounded-xl bg-blue-500/10 border border-blue-500/20 backdrop-blur-sm shadow-[0_0_15px_rgba(59,130,246,0.2)]">
                    <i data-lucide="archive" class="w-8 h-8 text-blue-400"></i>
                </div>
                คลังเอกสาร
            </h1>
            <p class="text-slate-400 mt-2 text-sm pl-1">ระบบจัดเก็บและบริหารจัดการเอกสารตามโครงสร้างงบประมาณ</p>
        </div>
        
        <div class="flex items-center gap-4">
            <!-- Fiscal Year Label & Selector -->
            <div class="flex items-center gap-3">
                <span class="text-sm font-medium text-slate-400">ปีงบประมาณ พ.ศ.</span>
                
                <div class="relative group">
                    <select onchange="changeYear(this.value)" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer bg-slate-800 text-white text-center z-10 peer">
                        <?php foreach ($availableYears as $y): ?>
                        <option value="<?= $y['fiscal_year'] ?>" <?= $y['fiscal_year'] == $fiscalYear ? 'selected' : '' ?> class="bg-slate-800 text-slate-200 py-2 text-center">
                            <?= $y['fiscal_year'] ?>
                        </option>
                        <?php endforeach; ?>
                    </select>

                    <div class="flex items-center justify-between bg-slate-800/50 hover:bg-slate-800 border border-white/5 hover:border-white/10 peer-focus:border-blue-500 peer-focus:ring-4 peer-focus:ring-blue-500/20 px-3 py-2.5 rounded-lg transition-all min-w-[120px] relative">
                        <!-- Icon Left -->
                        <div class="absolute left-3 flex items-center pointer-events-none">
                            <i data-lucide="calendar" class="w-4 h-4 text-slate-500 group-hover:text-blue-400 transition-colors"></i>
                        </div>
                        
                        <!-- Center Text -->
                        <div class="w-full text-center pl-4 pr-3">
                            <span class="text-sm font-bold text-blue-400 tracking-wide"><?= $fiscalYear ?></span>
                        </div>

                        <!-- Chevron Right -->
                        <div class="absolute right-2.5 flex items-center pointer-events-none">
                            <i data-lucide="chevron-down" class="w-3 h-3 text-slate-500 group-hover:text-blue-400 transition-colors opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php if (isset($canUpload) && $canUpload): ?>
            <!-- Action Buttons -->
            <button onclick="showUploadModal()" class="btn btn-primary bg-gradient-to-r from-blue-600 to-cyan-600 border border-transparent shadow-lg shadow-blue-500/20 py-2.5 ml-2">
                <i data-lucide="upload" class="w-4 h-4"></i>
                <span>อัปโหลด</span>
            </button>
            <button onclick="showCreateFolderModal()" class="btn btn-secondary border-slate-600 hover:border-slate-500 py-2.5">
                <i data-lucide="folder-plus" class="w-4 h-4"></i>
                <span class="hidden sm:inline">สร้างโฟลเดอร์</span>
            </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 h-[calc(100vh-12rem)]">
        
        <!-- Sidebar Tree -->
        <div class="lg:col-span-3 flex flex-col h-full bg-slate-800/30 border border-white/5 rounded-2xl backdrop-blur-sm overflow-hidden">
            <div class="p-4 border-b border-white/5 bg-slate-800/50">
                <h3 class="text-sm font-semibold text-slate-300 flex items-center gap-2">
                    <i data-lucide="network" class="w-4 h-4 text-sky-400"></i>
                    โครงสร้างโฟลเดอร์
                </h3>
            </div>
            <div class="flex-1 overflow-y-auto p-2 space-y-1 custom-scrollbar">
                <a href="<?= \App\Core\View::url('/files?year=' . $fiscalYear) ?>" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all group <?= !$currentFolder ? 'bg-blue-500/10 text-blue-400' : 'text-slate-400 hover:bg-white/5 hover:text-slate-200' ?>">
                    <i data-lucide="home" class="w-4 h-4 <?= !$currentFolder ? 'text-blue-400' : 'text-slate-500 group-hover:text-slate-300' ?>"></i>
                    <span class="text-sm font-medium">Root (ปี <?= $fiscalYear ?>)</span>
                </a>
                
                <div class="mt-2 pl-2 border-l border-slate-700/50 ml-3 space-y-1">
                    <?php foreach ($folderTree as $folder): ?>
                    <?php include __DIR__ . '/_folder_item.php'; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Content Area -->
        <div class="lg:col-span-9 flex flex-col h-full gap-4">
            
            <!-- Breadcrumb -->
            <?php if (!empty($breadcrumb)): ?>
            <nav class="flex items-center gap-2 text-sm px-4 py-3 bg-slate-800/50 border border-white/5 rounded-xl backdrop-blur-sm">
                <a href="<?= \App\Core\View::url('/files?year=' . $fiscalYear) ?>" class="text-slate-400 hover:text-blue-400 transition-colors flex items-center gap-1.5 hover:bg-blue-500/10 px-2 py-1 rounded-md">
                    <i data-lucide="home" class="w-4 h-4"></i>
                    หน้าหลัก
                </a>
                <?php foreach ($breadcrumb as $bc): ?>
                <i data-lucide="chevron-right" class="w-4 h-4 text-slate-600"></i>
                <a href="<?= \App\Core\View::url('/files?folder=' . $bc['id']) ?>" 
                   class="<?= end($breadcrumb)['id'] == $bc['id'] ? 'text-blue-400 font-medium bg-blue-500/10 px-2 py-1 rounded-md' : 'text-slate-400 hover:text-blue-400 transition-colors hover:bg-blue-500/10 px-2 py-1 rounded-md' ?>">
                    <div class="flex items-center gap-2">
                        <?php if (end($breadcrumb)['id'] == $bc['id']): ?>
                        <i data-lucide="folder-open" class="w-4 h-4"></i>
                        <?php else: ?>
                        <i data-lucide="folder" class="w-4 h-4"></i>
                        <?php endif; ?>
                        <?= htmlspecialchars($bc['name']) ?>
                    </div>
                </a>
                <?php endforeach; ?>
            </nav>
            <?php endif; ?>

            <!-- Main Content Card -->
            <div class="flex-1 bg-slate-800/30 border border-white/5 rounded-2xl backdrop-blur-sm overflow-hidden flex flex-col relative">
                
                <!-- Folders Grid -->
                <?php if (!empty($folders)): ?>
                <div class="p-6 <?= !empty($files) ? 'border-b border-white/5' : '' ?>">
                    <h3 class="text-sm font-semibold text-slate-400 mb-4 flex items-center gap-2 uppercase tracking-wider">
                        <i data-lucide="folders" class="w-4 h-4"></i> 
                        โฟลเดอร์ <span class="px-2 py-0.5 rounded-full bg-slate-700/50 text-slate-300 text-xs"><?= count($folders) ?></span>
                    </h3>
                    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">
                        <?php foreach ($folders as $folder): ?>
                        <?php 
                            // Determine folder icon based on organization_id
                            $isOrgFolder = !empty($folder['organization_id']);
                            $isCentralFolder = empty($folder['organization_id']) && $folder['is_system'];
                            
                            $bgClass = $isCentralFolder ? 'bg-blue-500/10 group-hover:bg-blue-500/20' : ($isOrgFolder ? 'bg-amber-500/10 group-hover:bg-amber-500/20' : 'bg-yellow-500/10 group-hover:bg-yellow-500/20');
                            $textClass = $isCentralFolder ? 'text-blue-400' : ($isOrgFolder ? 'text-amber-400' : 'text-yellow-400');
                            $iconName = $isCentralFolder ? 'globe' : ($isOrgFolder ? 'building-2' : 'folder');
                        ?>
                        <a href="<?= \App\Core\View::url('/files?folder=' . $folder['id']) ?>" 
                           class="group relative p-4 rounded-xl bg-slate-800/50 hover:bg-slate-700/50 border border-white/5 hover:border-white/10 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:shadow-black/20">
                            <div class="flex flex-col items-center text-center gap-3">
                                <div class="w-14 h-14 rounded-full flex items-center justify-center <?= $bgClass ?> transition-colors shadow-inner">
                                    <i data-lucide="<?= $iconName ?>" class="w-7 h-7 <?= $textClass ?> stroke-[1.5]"></i>
                                </div>
                                <div class="w-full">
                                    <h4 class="text-sm font-medium text-slate-200 group-hover:text-white truncate transition-colors"><?= htmlspecialchars($folder['name']) ?></h4>
                                    <div class="flex items-center justify-center gap-3 mt-1.5 text-xs text-slate-500">
                                        <span class="flex items-center gap-1"><i data-lucide="folder" class="w-3 h-3"></i> <?= $folder['subfolder_count'] ?? 0 ?></span>
                                        <span class="w-px h-3 bg-slate-700"></span>
                                        <span class="flex items-center gap-1"><i data-lucide="file" class="w-3 h-3"></i> <?= $folder['file_count'] ?? 0 ?></span>
                                    </div>
                                </div>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Files List -->
                <div class="flex-1 flex flex-col min-h-0 bg-slate-900/20">
                    <?php if (!$currentFolder && empty($folders)): ?>
                    <div class="m-auto text-center p-8">
                        <div class="w-20 h-20 rounded-full bg-slate-800/50 flex items-center justify-center mx-auto mb-4 border border-white/5 shadow-inner">
                            <i data-lucide="folder-open" class="w-10 h-10 text-slate-600"></i>
                        </div>
                        <h3 class="text-lg font-medium text-slate-300">เริ่มใช้งานระบบจัดเก็บเอกสาร</h3>
                        <p class="text-slate-500 mt-2 max-w-sm mx-auto">ยังไม่มีโฟลเดอร์สำหรับปีงบประมาณนี้ เริ่มต้นด้วยการสร้างโครงสร้างโฟลเดอร์พื้นฐาน</p>
                        <form action="<?= \App\Core\View::url('/files/init') ?>" method="POST" class="mt-6">
                            <?= \App\Core\View::csrf() ?>
                            <input type="hidden" name="year" value="<?= $fiscalYear ?>">
                            <button type="submit" class="btn btn-primary px-6 py-3 shadow-lg shadow-blue-500/20">
                                <i data-lucide="sparkles" class="w-4 h-4 mr-2"></i> สร้างโฟลเดอร์เริ่มต้น
                            </button>
                        </form>
                    </div>
                    <?php elseif ($currentFolder || !empty($files)): ?>
                        <?php if (empty($files) && !empty($folders)): ?>
                         <!-- Empty state for folder with subfolders but no files -->
                             <!-- (Optional: Only show if needed, otherwise just empty area) -->
                        <?php elseif (empty($files)): ?>
                        <div class="m-auto text-center p-8">
                            <div class="w-16 h-16 rounded-full bg-slate-800/50 flex items-center justify-center mx-auto mb-4 border border-dashed border-slate-600">
                                <i data-lucide="file-plus" class="w-8 h-8 text-slate-500"></i>
                            </div>
                            <p class="text-slate-400">ยังไม่มีไฟล์ในโฟลเดอร์นี้</p>
                            <?php if (isset($canUpload) && $canUpload): ?>
                            <button onclick="showUploadModal()" class="btn btn-sm btn-ghost-primary mt-2">
                                อัปโหลดไฟล์แรกเลย
                            </button>
                            <?php endif; ?>
                        </div>
                        <?php else: ?>
                        
                        <div class="px-6 pt-6 pb-2">
                            <h3 class="text-sm font-semibold text-slate-400 flex items-center gap-2 uppercase tracking-wider">
                                <i data-lucide="files" class="w-4 h-4"></i> 
                                รายการไฟล์ <span class="px-2 py-0.5 rounded-full bg-slate-700/50 text-slate-300 text-xs"><?= count($files) ?></span>
                            </h3>
                        </div>

                        <div class="overflow-y-auto flex-1 custom-scrollbar px-6 pb-6">
                            <div class="space-y-1">
                                <?php foreach ($files as $file): ?>
                                <div class="group flex items-center gap-4 p-3 rounded-xl hover:bg-white/5 border border-transparent hover:border-white/5 transition-all duration-200">
                                    <!-- Icon -->
                                    <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-slate-800 flex items-center justify-center border border-white/5 shadow-sm group-hover:scale-110 transition-transform">
                                        <i data-lucide="<?= \App\Models\File::getIcon($file['file_type']) ?>" class="w-5 h-5 text-blue-400"></i>
                                    </div>
                                    
                                    <!-- Details -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between">
                                            <h4 class="text-sm font-medium text-slate-200 truncate group-hover:text-blue-400 transition-colors">
                                                <?= htmlspecialchars($file['original_name']) ?>
                                            </h4>
                                            <span class="text-xs text-slate-500 bg-slate-800/80 px-2 py-0.5 rounded">
                                                <?= \App\Models\File::formatSize($file['file_size']) ?>
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-3 mt-0.5">
                                            <p class="text-xs text-slate-500 flex items-center gap-1">
                                                <i data-lucide="user" class="w-3 h-3"></i> <?= htmlspecialchars($file['uploaded_by_name'] ?? '-') ?>
                                            </p>
                                            <span class="w-0.5 h-0.5 bg-slate-600 rounded-full"></span>
                                            <p class="text-xs text-slate-500">
                                                <?= date('d/m/Y H:i', strtotime($file['created_at'])) ?>
                                            </p>
                                        </div>
                                        <?php if ($file['description']): ?>
                                        <p class="text-xs text-slate-500 mt-1 italic truncate"><?= htmlspecialchars($file['description']) ?></p>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Actions -->
                                    <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <a href="<?= \App\Core\View::url('/files/' . $file['id'] . '/download') ?>" 
                                           class="p-2 rounded-lg text-slate-400 hover:text-blue-400 hover:bg-blue-500/10 transition-colors" 
                                           title="ดาวน์โหลด">
                                            <i data-lucide="download" class="w-4 h-4"></i>
                                        </a>
                                        <?php if (isset($canUpload) && $canUpload): ?>
                                        <form action="<?= \App\Core\View::url('/files/' . $file['id'] . '/delete') ?>" method="POST" class="inline" 
                                              onsubmit="return confirm('ต้องการลบไฟล์นี้?')">
                                            <?= \App\Core\View::csrf() ?>
                                            <button type="submit" class="p-2 rounded-lg text-slate-400 hover:text-red-400 hover:bg-red-500/10 transition-colors" title="ลบ">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Folder Modal -->
<div id="createFolderModal" class="fixed inset-0 z-50 hidden transition-opacity duration-300">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-slate-900/80 backdrop-blur-sm transition-opacity" onclick="hideCreateFolderModal()"></div>
    
    <!-- Modal -->
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-slate-800/90 backdrop-blur-md border border-white/10 rounded-2xl p-6 w-full max-w-md shadow-2xl transform transition-all scale-100">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-white flex items-center gap-2">
                    <div class="p-1.5 rounded-lg bg-blue-500/20 text-blue-400">
                        <i data-lucide="folder-plus" class="w-5 h-5"></i>
                    </div>
                    สร้างโฟลเดอร์ย่อย
                </h3>
                <button onclick="hideCreateFolderModal()" class="text-slate-400 hover:text-white transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            
            <form action="<?= \App\Core\View::url('/folders') ?>" method="POST">
                <?= \App\Core\View::csrf() ?>
                <input type="hidden" name="parent_id" value="<?= $currentFolder['id'] ?? '' ?>">
                <input type="hidden" name="fiscal_year" value="<?= $fiscalYear ?>">
                
                <div class="space-y-4">
                    <?php if (isset($isAdmin) && $isAdmin && empty($currentFolder)): ?>
                    <!-- Admin: Department & Division Selector -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1.5">กรม</label>
                            <select id="modalDeptSelect" class="w-full bg-slate-900/50 border border-slate-700/50 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all">
                                <option value="">-- ส่วนกลาง --</option>
                                <?php if (!empty($departments)): ?>
                                    <?php foreach ($departments as $dept): ?>
                                    <option value="<?= $dept['id'] ?>"><?= htmlspecialchars($dept['name_th']) ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1.5">กอง</label>
                            <select name="organization_id" id="modalOrgSelect" disabled class="w-full bg-slate-900/50 border border-slate-700/50 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all opacity-50 cursor-not-allowed">
                                <option value="">-- ส่วนกลาง --</option>
                                <?php if (!empty($divisions)): ?>
                                    <?php foreach ($divisions as $div): ?>
                                    <option value="<?= $div['id'] ?>" data-parent-id="<?= $div['parent_id'] ?>" hidden>
                                        <?= htmlspecialchars($div['name_th']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                    
                    <script>
                    document.getElementById('modalDeptSelect').addEventListener('change', function() {
                        const deptId = this.value;
                        const orgSelect = document.getElementById('modalOrgSelect');
                        const options = orgSelect.querySelectorAll('option[data-parent-id]');
                        
                        orgSelect.value = ''; // Reset selection
                        
                        if (!deptId) {
                            orgSelect.disabled = true;
                            orgSelect.classList.add('opacity-50', 'cursor-not-allowed');
                            options.forEach(opt => opt.hidden = true);
                            return;
                        }
                        
                        let count = 0;
                        options.forEach(opt => {
                            if (opt.dataset.parentId == deptId) {
                                opt.hidden = false;
                                count++;
                            } else {
                                opt.hidden = true;
                            }
                        });
                        
                        if (count > 0) {
                            orgSelect.disabled = false;
                            orgSelect.classList.remove('opacity-50', 'cursor-not-allowed');
                            orgSelect.options[0].text = '-- เลือกกอง --';
                        } else {
                            orgSelect.disabled = true;
                            orgSelect.classList.add('opacity-50', 'cursor-not-allowed');
                            orgSelect.options[0].text = '-- ไม่มีกองในกรมนี้ --';
                        }
                    });
                    </script>
                    <?php endif; ?>

                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1.5">ชื่อโฟลเดอร์</label>
                        <input type="text" name="name" required class="w-full bg-slate-900/50 border border-slate-700/50 rounded-lg px-4 py-2.5 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all" placeholder="ระบุชื่อโฟลเดอร์...">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1.5">รายละเอียด <span class="text-slate-500 font-normal">(ถ้ามี)</span></label>
                        <textarea name="description" rows="3" class="w-full bg-slate-900/50 border border-slate-700/50 rounded-lg px-4 py-2.5 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all resize-none" placeholder="รายละเอียดเพิ่มเติม..."></textarea>
                    </div>
                </div>
                
                <div class="flex justify-end gap-3 mt-8">
                    <button type="button" onclick="hideCreateFolderModal()" class="px-5 py-2.5 rounded-lg text-slate-300 hover:bg-slate-700 font-medium transition-colors">ยกเลิก</button>
                    <button type="submit" class="px-5 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-500 text-white font-medium shadow-lg shadow-blue-500/30 transition-all transform hover:scale-[1.02]">สร้างโฟลเดอร์</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Upload Modal -->
<div id="uploadModal" class="fixed inset-0 z-50 hidden transition-opacity duration-300">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-slate-900/80 backdrop-blur-sm transition-opacity" onclick="hideUploadModal()"></div>
    
    <!-- Modal -->
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-slate-800/90 backdrop-blur-md border border-white/10 rounded-2xl p-6 w-full max-w-md shadow-2xl transform transition-all scale-100">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-white flex items-center gap-2">
                    <div class="p-1.5 rounded-lg bg-cyan-500/20 text-cyan-400">
                        <i data-lucide="upload" class="w-5 h-5"></i>
                    </div>
                    อัปโหลดไฟล์
                </h3>
                <button onclick="hideUploadModal()" class="text-slate-400 hover:text-white transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            
            <form action="<?= \App\Core\View::url('/files/upload') ?>" method="POST" enctype="multipart/form-data">
                <?= \App\Core\View::csrf() ?>
                <input type="hidden" name="folder_id" value="<?= $currentFolder['id'] ?? '' ?>">
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1.5">เลือกไฟล์</label>
                        <div class="relative group">
                            <input type="file" name="file" required class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" id="fileInput" onchange="updateFileName(this)">
                            <div class="w-full bg-slate-900/50 border-2 border-dashed border-slate-700/50 group-hover:border-blue-500/50 rounded-lg px-4 py-8 text-center transition-all">
                                <i data-lucide="cloud-upload" class="w-8 h-8 text-slate-500 group-hover:text-blue-400 mx-auto mb-2 transition-colors"></i>
                                <span class="text-sm text-slate-400 group-hover:text-slate-300 block mb-1">คลิกเพื่อเลือกไฟล์ หรือลากไฟล์มาวาง</span>
                                <span class="text-xs text-slate-500">PDF, Excel, Word, Image (Max 10MB)</span>
                            </div>
                        </div>
                        <div id="fileNameDisplay" class="mt-2 text-sm text-blue-400 hidden flex items-center gap-1">
                            <i data-lucide="file" class="w-3 h-3"></i>
                            <span class="truncate"></span>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1.5">รายละเอียด <span class="text-slate-500 font-normal">(ถ้ามี)</span></label>
                        <textarea name="description" rows="2" class="w-full bg-slate-900/50 border border-slate-700/50 rounded-lg px-4 py-2.5 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-cyan-500/50 focus:border-cyan-500/50 transition-all resize-none" placeholder="รายละเอียดไฟล์..."></textarea>
                    </div>
                </div>
                
                <div class="flex justify-end gap-3 mt-8">
                    <button type="button" onclick="hideUploadModal()" class="px-5 py-2.5 rounded-lg text-slate-300 hover:bg-slate-700 font-medium transition-colors">ยกเลิก</button>
                    <button type="submit" class="px-5 py-2.5 rounded-lg bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-500 hover:to-cyan-500 text-white font-medium shadow-lg shadow-blue-500/30 transition-all transform hover:scale-[1.02]">เริ่มการอัปโหลด</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function changeYear(year) {
    window.location.href = '<?= \App\Core\View::url('/files') ?>?year=' + year;
}
function showCreateFolderModal() {
    const el = document.getElementById('createFolderModal');
    el.classList.remove('hidden');
    setTimeout(() => {
        el.firstElementChild.classList.add('opacity-100'); // Backdrop
        el.lastElementChild.firstElementChild.classList.remove('scale-95', 'opacity-0');
        el.lastElementChild.firstElementChild.classList.add('scale-100', 'opacity-100');
    }, 10);
}
function hideCreateFolderModal() {
    const el = document.getElementById('createFolderModal');
    el.firstElementChild.classList.remove('opacity-100');
    el.lastElementChild.firstElementChild.classList.remove('scale-100', 'opacity-100');
    el.lastElementChild.firstElementChild.classList.add('scale-95', 'opacity-0');
    setTimeout(() => {
        el.classList.add('hidden');
    }, 300);
}
function showUploadModal() {
    const el = document.getElementById('uploadModal');
    el.classList.remove('hidden');
    setTimeout(() => {
        el.firstElementChild.classList.add('opacity-100');
        el.lastElementChild.firstElementChild.classList.remove('scale-95', 'opacity-0');
        el.lastElementChild.firstElementChild.classList.add('scale-100', 'opacity-100');
    }, 10);
}
function hideUploadModal() {
    const el = document.getElementById('uploadModal');
    el.firstElementChild.classList.remove('opacity-100');
    el.lastElementChild.firstElementChild.classList.remove('scale-100', 'opacity-100');
    el.lastElementChild.firstElementChild.classList.add('scale-95', 'opacity-0');
    setTimeout(() => {
        el.classList.add('hidden');
    }, 300);
}
function updateFileName(input) {
    const display = document.getElementById('fileNameDisplay');
    if (input.files && input.files[0]) {
        display.classList.remove('hidden');
        display.querySelector('span').textContent = input.files[0].name;
    } else {
        display.classList.add('hidden');
    }
}
</script>
