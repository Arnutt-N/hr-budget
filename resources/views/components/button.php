<?php
/**
 * Button Component
 * 
 * Props:
 * - variant: primary, secondary, danger, success (default: primary)
 * - label: Button text
 * - icon: Lucide icon name (optional)
 * - type: button, submit (default: button)
 * - class: Additional classes
 * - attributes: Additional HTML attributes
 */

$variant = $variant ?? 'primary';
$label = $label ?? '';
$icon = $icon ?? null;
$type = $type ?? 'button';
$class = $class ?? '';
$attributes = $attributes ?? '';

// Base classes for all buttons
$baseClasses = 'inline-flex items-center justify-center gap-1 px-3 py-1.5 text-xs rounded-lg font-medium transition-all whitespace-nowrap focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-offset-slate-900';

// Variant specific classes
$variantClasses = match($variant) {
    'primary' => 'bg-primary-600 text-white hover:bg-primary-500 shadow-md shadow-primary-500/20 focus:ring-primary-500',
    'secondary' => 'bg-slate-700 text-slate-200 hover:bg-slate-600 border border-slate-600 hover:border-slate-500 focus:ring-slate-500',
    'danger' => 'bg-red-600/10 text-red-400 border border-red-500/50 hover:bg-red-600 hover:text-white hover:border-red-600 shadow-lg shadow-red-900/20 focus:ring-red-500',
    'success' => 'bg-emerald-600 text-white hover:bg-emerald-500 shadow-md shadow-emerald-500/20 focus:ring-emerald-500',
    'ghost' => 'text-slate-400 hover:text-white hover:bg-slate-800 focus:ring-slate-500',
    'ghost-primary' => 'text-primary-400 hover:text-primary-300 hover:bg-primary-500/10 focus:ring-primary-500',
    default => 'bg-primary-600 text-white hover:bg-primary-500'
};

$finalClass = "{$baseClasses} {$variantClasses} {$class}";
?>

<button type="<?= $type ?>" class="<?= $finalClass ?>" <?= $attributes ?>>
    <?php if ($icon): ?>
        <i data-lucide="<?= $icon ?>" class="w-4 h-4"></i>
    <?php endif; ?>
    <?= htmlspecialchars($label) ?>
</button>
