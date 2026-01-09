<?php
/**
 * KPI Card Component
 * 
 * Props:
 * - title: Card title (e.g., "งบประมาณจัดสรร")
 * - value: Main value (e.g., "5,000,000.00")
 * - icon: Lucide icon name (e.g., "wallet")
 * - variant: blue, green, orange, red (default: blue) - Controls icon/text colors
 * - footer: HTML content for the footer (slot)
 * - tooltip: HTML content for the tooltip (slot)
 * - class: Additional classes
 */

$title = $title ?? '';
$value = $value ?? '0.00';
$icon = $icon ?? 'circle';
$variant = $variant ?? 'blue';
$footer = $footer ?? ''; // Slot for footer content
$tooltip = $tooltip ?? ''; // Slot for tooltip content
$class = $class ?? '';

// Variant Colors
$variantColors = match($variant) {
    'blue' => ['text' => 'text-blue-400', 'bg' => '', 'icon' => 'text-blue-400'],
    'green' => ['text' => 'text-green-400', 'bg' => '', 'icon' => 'text-green-400'],
    'orange' => ['text' => 'text-orange-400', 'bg' => '', 'icon' => 'text-orange-400'],
    'red' => ['text' => 'text-red-400', 'bg' => '', 'icon' => 'text-red-400'],
    default => ['text' => 'text-white', 'bg' => '', 'icon' => 'text-slate-400']
};

?>

<div class="bg-dark-card border border-dark-border rounded-xl p-6 card-hover group relative <?= $class ?>">
    <div class="flex justify-between items-start <?= !empty($footer) ? 'mb-2' : '' ?>">
        <div>
            <p class="text-dark-muted text-sm font-medium"><?= htmlspecialchars($title) ?></p>
            <h3 class="text-2xl font-bold <?= $variantColors['text'] ?> mt-1">
                <?= $value ?>
            </h3>
        </div>
        <div class="icon-glass <?= $variantColors['icon'] ?>">
            <i data-lucide="<?= $icon ?>" class="w-6 h-6"></i>
        </div>
    </div>
    
    <?php if (!empty($footer)): ?>
    <!-- Footer Section -->
    <div class="pt-3 border-t border-dark-border flex items-center justify-between text-xs cursor-pointer">
        <div class="text-dark-muted w-full">
            <?= $footer ?>
        </div>
        <?php if (!empty($tooltip)): ?>
        <i data-lucide="info" class="w-4 h-4 text-dark-muted hover:text-white transition-colors ml-2"></i>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($tooltip)): ?>
    <!-- Tooltip (Tailwind group-hover) -->
    <div class="absolute bottom-full left-0 mb-2 w-full invisible group-hover:visible opacity-0 group-hover:opacity-100 transition-all duration-200 z-10 px-2 pointer-events-none">
        <div class="bg-[#1e1e2d] border border-gray-700 rounded-lg shadow-xl p-3 text-xs w-full">
            <?= $tooltip ?>
        </div>
    </div>
    <?php endif; ?>
</div>
