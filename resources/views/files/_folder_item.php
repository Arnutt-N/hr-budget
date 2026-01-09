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
       class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-dark-hover transition-colors <?= $isActive ? 'bg-dark-hover text-white' : 'text-dark-text' ?>">
        <i data-lucide="<?= $hasChildren ? 'folder-open' : 'folder' ?>" class="w-5 h-5 text-yellow-400"></i>
        <span class="truncate text-sm"><?= htmlspecialchars($folder['name']) ?></span>
    </a>
    
    <?php if ($hasChildren): ?>
        <?php foreach ($folder['children'] as $child): ?>
            <?php $folder = $child; include __DIR__ . '/_folder_item.php'; ?>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
