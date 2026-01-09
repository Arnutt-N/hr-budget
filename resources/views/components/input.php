<?php
/**
 * Input Component
 * 
 * Props:
 * - name: Input name
 * - label: Label text
 * - type: text, number, email, password, date (default: text)
 * - value: Default value
 * - placeholder: Placeholder text
 * - required: boolean (default: false)
 * - error: Error message text (optional)
 * - class: Additional classes for input
 * - wrapperClass: Additional classes for container
 * - attributes: Additional HTML attributes
 */

$name = $name ?? '';
$label = $label ?? '';
$type = $type ?? 'text';
$value = $value ?? '';
$placeholder = $placeholder ?? '';
$required = $required ?? false;
$error = $error ?? null;
$class = $class ?? '';
$wrapperClass = $wrapperClass ?? '';
$attributes = $attributes ?? '';

// Error state styling
$inputBorderClass = $error 
    ? 'border-red-500 text-red-500 focus:border-red-500 focus:ring-red-500/20' 
    : 'border-slate-600 text-white focus:border-primary-500 focus:ring-primary-500/20 hover:border-slate-500';

?>

<div class="space-y-1.5 <?= $wrapperClass ?>">
    <?php if ($label): ?>
    <label for="<?= $name ?>" class="block text-[10px] uppercase font-bold text-slate-400 tracking-wider">
        <?= htmlspecialchars($label) ?> 
        <?php if ($required): ?><span class="text-red-500">*</span><?php endif; ?>
    </label>
    <?php endif; ?>
    
    <div class="relative">
        <input 
            type="<?= $type ?>" 
            name="<?= $name ?>" 
            id="<?= $name ?>"
            value="<?= htmlspecialchars($value) ?>"
            class="block w-full h-[38px] rounded-lg bg-slate-900/50 border <?= $inputBorderClass ?> shadow-sm focus:ring-2 focus:ring-offset-0 transition-all text-sm px-3 placeholder:text-slate-600 <?= $class ?>"
            placeholder="<?= $placeholder ?>"
            <?= $required ? 'required' : '' ?>
            <?= $attributes ?>
        >
        
        <!-- Error Icon (Optional, can add later) -->
    </div>
    
    <?php if ($error): ?>
    <p class="text-xs text-red-500 mt-1 flex items-center gap-1">
        <i data-lucide="alert-circle" class="w-3 h-3"></i>
        <?= htmlspecialchars($error) ?>
    </p>
    <?php endif; ?>
</div>
