<?php
/**
 * Folder tree item (recursive) for sidebar
 * Variables: $folder (with 'children' key), $currentFolder
 */
$isActive = $currentFolder && $currentFolder['id'] == $folder['id'];
$hasChildren = !empty($folder['children']);
?>
<div class="ml-3">
    <a href="<?= \App\Core\View::url('/files?folder=' . $folder['id']) ?>" 
       class="flex items-center gap-2 px-3 py-2 rounded-lg transition-all <?= $isActive ? 'bg-blue-500/10 text-blue-400' : 'text-slate-400 hover:bg-white/5 hover:text-slate-200' ?>">
        <i data-lucide="<?= $hasChildren ? 'folder-open' : 'folder' ?>" class="w-4 h-4 <?= $isActive ? 'text-blue-400' : 'text-slate-500 group-hover:text-slate-300' ?>"></i>
        <span class="truncate text-sm font-medium"><?= htmlspecialchars($folder['name']) ?></span>
    </a>
    
    <?php if ($hasChildren): ?>
        <?php foreach ($folder['children'] as $child): ?>
            <?php $folder = $child; include __DIR__ . '/_folder_item.php'; ?>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
