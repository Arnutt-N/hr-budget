<?php
/**
 * Card Component
 * 
 * Props:
 * - title: Card title
 * - icon: Lucide icon name for title (optional)
 * - content: HTML content (if not using slot/ob_start)
 * - class: Additional classes for container
 * - headerClass: Additional classes for header
 */

$title = $title ?? '';
$icon = $icon ?? null;
$content = $content ?? ''; // Can be passed directly or used via ob_get_clean() before call
$class = $class ?? '';
$headerClass = $headerClass ?? '';

?>

<div class="bg-slate-900/50 backdrop-blur-sm border border-slate-700 rounded-xl p-6 shadow-xl <?= $class ?>">
    <?php if ($title): ?>
    <div class="flex items-center justify-between mb-6 <?= $headerClass ?>">
        <h2 class="text-xl font-semibold text-slate-100 flex items-center gap-2">
            <?php if ($icon): ?>
            <i data-lucide="<?= $icon ?>" class="text-primary-500 w-5 h-5"></i>
            <?php endif; ?>
            <?= htmlspecialchars($title) ?>
        </h2>
        <!-- Optional Actions Slot could go here -->
    </div>
    <?php endif; ?>
    
    <div class="card-content">
        <?= $content ?>
    </div>
</div>
