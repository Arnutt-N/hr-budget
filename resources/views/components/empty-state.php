<?php
/**
 * Empty State Component
 * 
 * Props:
 * - icon: Lucide icon name (default: folder-open)
 * - title: Main message (default: ไม่พบข้อมูล)
 * - description: Secondary message (optional)
 * - action: Array ['label' => '', 'url' => '', 'icon' => ''] (optional)
 * - class: Additional classes
 * - height: Height class (default: py-12)
 */

$icon = $icon ?? 'folder-open';
$title = $title ?? 'ไม่พบข้อมูล';
$description = $description ?? '';
$action = $action ?? null;
$class = $class ?? '';
$height = $height ?? 'py-12';

?>

<div class="text-center <?= $height ?> text-slate-500 <?= $class ?>">
    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-800/50 mb-4">
        <i data-lucide="<?= $icon ?>" class="w-8 h-8 text-slate-600"></i>
    </div>
    
    <h3 class="text-lg font-medium text-slate-300 mb-1">
        <?= htmlspecialchars($title) ?>
    </h3>
    
    <?php if ($description): ?>
    <p class="text-sm text-slate-500 max-w-xs mx-auto mb-6">
        <?= htmlspecialchars($description) ?>
    </p>
    <?php endif; ?>
    
    <?php if ($action): ?>
    <a href="<?= $action['url'] ?>" class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-500 transition-all shadow-lg shadow-primary-500/20">
        <?php if (!empty($action['icon'])): ?>
        <i data-lucide="<?= $action['icon'] ?>" class="w-4 h-4"></i>
        <?php endif; ?>
        <?= htmlspecialchars($action['label']) ?>
    </a>
    <?php endif; ?>
</div>
