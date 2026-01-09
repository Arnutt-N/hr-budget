<?php
/**
 * Select Component
 * 
 * Props:
 * - name: Select name
 * - label: Label text
 * - options: Array of options ['value' => 'Label'] or [['value' => '1', 'label' => 'One']]
 * - value: Selected value
 * - required: boolean (default: false)
 * - icon: Lucide icon name for left side (optional)
 * - error: Error message text (optional)
 * - class: Additional classes
 * - attributes: Additional HTML attributes
 */

$name = $name ?? '';
$label = $label ?? '';
$options = $options ?? [];
$value = $value ?? '';
$required = $required ?? false;
$icon = $icon ?? null;
$error = $error ?? null;
$class = $class ?? '';
$attributes = $attributes ?? '';

// Error state styling
$inputBorderClass = $error 
    ? 'border-red-500 text-red-500 focus:border-red-500 focus:ring-red-500/20' 
    : 'border-slate-600 text-white focus:border-primary-500 focus:ring-primary-500/20 hover:border-slate-500';

// Padding adjustment for icon
$paddingLeft = $icon ? 'pl-9' : 'pl-3';
?>

<div class="space-y-1.5">
    <?php if ($label): ?>
    <label for="<?= $name ?>" class="block text-[10px] uppercase font-bold text-slate-400 tracking-wider">
        <?= htmlspecialchars($label) ?>
        <?php if ($required): ?><span class="text-red-500">*</span><?php endif; ?>
    </label>
    <?php endif; ?>
    
    <div class="relative">
        <!-- Left Icon -->
        <?php if ($icon): ?>
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <i data-lucide="<?= $icon ?>" class="w-4 h-4 text-slate-500"></i>
        </div>
        <?php endif; ?>

        <select 
            name="<?= $name ?>" 
            id="<?= $name ?>"
            class="block w-full h-[38px] rounded-lg bg-slate-900/50 border <?= $inputBorderClass ?> shadow-sm focus:ring-2 focus:ring-offset-0 transition-all text-sm <?= $paddingLeft ?> pr-10 appearance-none cursor-pointer <?= $class ?>"
            <?= $required ? 'required' : '' ?>
            <?= $attributes ?>
        >
            <option value="" disabled <?= empty($value) ? 'selected' : '' ?>>เลือก...</option>
            <?php foreach ($options as $optValue => $optLabel): ?>
                <?php 
                    // Handle both key=>value array and [['value'=>'', 'label'=>'']] format
                    $val = is_array($optLabel) ? $optLabel['value'] : $optValue;
                    $text = is_array($optLabel) ? $optLabel['label'] : $optLabel;
                ?>
                <option value="<?= htmlspecialchars($val) ?>" <?= (string)$val === (string)$value ? 'selected' : '' ?>>
                    <?= htmlspecialchars($text) ?>
                </option>
            <?php endforeach; ?>
        </select>
        
        <!-- Custom Chevron -->
        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
            <i data-lucide="chevron-down" class="w-4 h-4 text-slate-500"></i>
        </div>
    </div>

    <?php if ($error): ?>
    <p class="text-xs text-red-500 mt-1 flex items-center gap-1">
        <i data-lucide="alert-circle" class="w-3 h-3"></i>
        <?= htmlspecialchars($error) ?>
    </p>
    <?php endif; ?>
</div>
