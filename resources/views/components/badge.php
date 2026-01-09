<?php
/**
 * Badge Component
 * 
 * Props:
 * - label: Badge text
 * - variant: blue, green, orange, red, gray (default: gray)
 * - icon: Lucide icon name (optional)
 * - class: Additional classes
 */

$label = $label ?? '';
$variant = $variant ?? 'gray';
$icon = $icon ?? null;
$class = $class ?? '';

// Variant Styles
$variantClasses = match($variant) {
    'blue' => 'bg-primary-500/10 text-primary-400 border border-primary-500/20',
    'green' => 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20',
    'orange' => 'bg-orange-500/10 text-orange-400 border border-orange-500/20',
    'red' => 'bg-red-500/10 text-red-400 border border-red-500/20',
    'gray' => 'bg-slate-500/10 text-slate-400 border border-slate-500/20',
    default => 'bg-slate-500/10 text-slate-400 border border-slate-500/20'
};

?>

<span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium <?= $variantClasses ?> <?= $class ?>">
    <?php if ($icon): ?>
    <i data-lucide="<?= $icon ?>" class="w-3 h-3 mr-1"></i>
    <?php endif; ?>
    <?= htmlspecialchars($label) ?>
</span>
