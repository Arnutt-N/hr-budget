<?php
/**
 * PART 1: Activities Selection Page
 * Matches Wireframe exactly with Tailwind CSS
 */

$currentOrg = $session['organization_name'] ?? 'หน่วยงาน';
$currentMonth = \App\Helpers\DateHelper::thaiMonth($session['record_month']);
$currentYear = $session['fiscal_year'];

?>

<style>
/* Custom Tooltip - Flat Design (No Shadow) */
[data-tooltip] {
    position: relative;
    cursor: default;
}

[data-tooltip]:hover::after {
    content: attr(data-tooltip);
    position: absolute;
    bottom: calc(100% + 8px);
    left: 50%;
    transform: translateX(-50%);
    background: #0f172a;
    color: #cbd5e1;
    padding: 8px 14px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 400;
    white-space: nowrap;
    z-index: 1000;
    border: 1px solid rgba(71, 85, 105, 0.3);
    pointer-events: none;
    opacity: 0;
    animation: tooltipSlideUp 0.2s ease-out forwards;
}

[data-tooltip]:hover::before {
    content: '';
    position: absolute;
    bottom: calc(100% + 2px);
    left: 50%;
    transform: translateX(-50%);
    border: 6px solid transparent;
    border-top-color: #0f172a;
    z-index: 1001;
    pointer-events: none;
    opacity: 0;
    animation: tooltipSlideUp 0.2s ease-out forwards;
}

@keyframes tooltipSlideUp {
    from {
        opacity: 0;
        transform: translateX(-50%) translateY(4px);
    }
    to {
        opacity: 1;
        transform: translateX(-50%) translateY(0);
    }
}
</style>

<!-- Page Container with Wireframe Background -->
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900">
    <div class="max-w-7xl mx-auto px-6 py-8">
        
        <!-- Header & Filter -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-bold text-slate-100 flex items-center gap-3">
                    <i data-lucide="file-text" class="w-8 h-8 text-primary-400"></i>
                    บันทึกเบิกจ่ายงบประมาณ
                </h1>
                <p class="text-slate-400 mt-1">เลือกกิจกรรมที่ต้องการบันทึกข้อมูลประจำเดือน</p>
            </div>

            <div class="flex items-center gap-3 bg-slate-800 p-2 rounded-lg border border-slate-700">
                <div class="flex items-center gap-2 px-3 py-1.5 bg-slate-900 rounded border border-slate-600">
                    <span class="text-slate-400 text-sm">ปีงบประมาณ:</span>
                    <span class="text-emerald-400 font-bold"><?= $currentYear ?></span>
                </div>
                <div class="h-6 w-px bg-slate-600"></div>
                <div class="relative flex items-center">
                    <i data-lucide="calendar" class="w-4 h-4 text-slate-400 mr-2"></i>
                    <span class="text-slate-100 text-sm"><?= $currentMonth ?> <?= $currentYear - 1 ?></span>
                </div>
            </div>
        </div>

        <!-- Hierarchical List - Programs -->
        <div class="space-y-6">
            <?php 
            $programIndex = 0;
            foreach ($tree as $program): 
                $programIndex++;
                $productCount = count($program['children'] ?? []);
                $activityCount = 0;
                foreach ($program['children'] ?? [] as $p) {
                    $activityCount += count($p['children'] ?? []);
                }
            ?>
                
                <!-- Plan/Program Card -->
                <div class="bg-slate-900/50 border border-slate-700 rounded-xl overflow-hidden">
                    <div class="bg-gradient-to-r from-slate-800 to-slate-900 px-6 py-4 border-b border-slate-700 flex items-center justify-between">
                        <h3 class="text-lg font-bold text-slate-100 flex items-center gap-2">
                            <span class="w-8 h-8 rounded-lg bg-blue-900/50 text-blue-400 flex items-center justify-center text-sm font-bold border border-blue-800">
                                <?= str_pad($programIndex, 2, '0', STR_PAD_LEFT) ?>
                            </span>
                            <?= htmlspecialchars($program['name_th']) ?>
                        </h3>
                        <span class="px-3 py-1 rounded-full bg-slate-800 border border-slate-700 text-xs text-slate-400">
                            <?= $productCount ?> ผลผลิต / <?= $activityCount ?> กิจกรรม
                        </span>
                    </div>

                    <!-- Products/Projects -->
                    <?php foreach ($program['children'] ?? [] as $project): ?>
                        <div class="border-b border-slate-800 last:border-0">
                            <div class="px-6 py-3 bg-slate-800/30 text-sm font-semibold text-slate-300 flex items-center gap-2">
                                <i data-lucide="folder" class="w-4 h-4 text-slate-500"></i>
                                <?= htmlspecialchars($project['name_th']) ?>
                            </div>

                            <!-- Activity List -->
                            <div class="divide-y divide-slate-800">
                                <?php foreach ($project['children'] ?? [] as $activity): 
                                    $isRecorded = !empty($activity['record_id']) && ($activity['record_status'] === 'completed');
                                    // Placeholder for budget data (will be added by controller later)
                                    $allocated = 0;
                                    $remaining = 0;
                                ?>
                                    
                                    <!-- Activity Row -->
                                    <div class="px-6 py-4 flex items-center justify-between hover:bg-slate-800/50 transition-colors group">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-3">
                                                <div class="text-slate-200 font-medium group-hover:text-primary-400 transition-colors">
                                                    <?= htmlspecialchars($activity['name_th']) ?>
                                                </div>
                                                
                                                <?php if ($isRecorded): ?>
                                                    <!-- Expense Type Status Labels -->
                                                    <div class="flex items-center gap-1.5 flex-wrap">
                                                        <?php 
                                                        $recordTypes = $recordExpenseTypes[$activity['record_id']] ?? [];
                                                        foreach ($recordTypes as $et): 
                                                            if ($et['has_data']) {
                                                                // Green badge for saved with check icon
                                                                $badgeClasses = 'bg-emerald-900/30 text-emerald-400 border-emerald-800/50';
                                                                $icon = 'check-circle-2';
                                                            } else {
                                                                // Gray badge for not saved with circle icon
                                                                $badgeClasses = 'bg-slate-800 text-slate-500 border-slate-700';
                                                                $icon = 'circle';
                                                            }
                                                        ?>
                                                        <span class="px-2 py-0.5 rounded text-xs <?= $badgeClasses ?> border flex items-center gap-1"
                                                              data-tooltip="<?= $et['has_data'] ? 'บันทึกแล้ว' : 'ยังไม่ได้บันทึก' ?>">
                                                            <i data-lucide="<?= $icon ?>" class="w-3 h-3"></i>
                                                            <?= htmlspecialchars($et['name']) ?>
                                                        </span>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="px-2 py-0.5 rounded text-xs bg-slate-800 text-slate-500 border border-slate-700 flex items-center gap-1">
                                                        <i data-lucide="circle" class="w-3 h-3"></i> ยังไม่บันทึก
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <?php if ($allocated > 0 || $remaining > 0): ?>
                                                <div class="text-xs text-slate-500 mt-1 flex gap-4">
                                                    <span>งบจัดสรร: <?= number_format($allocated) ?></span>
                                                    <span>คงเหลือ: <span class="text-slate-400"><?= number_format($remaining) ?></span></span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="flex items-center gap-2">
                                            <?php if ($isRecorded): ?>
                                                <a href="<?= BASE_URL ?>/budgets/tracking/<?= $activity['record_id'] ?>/form?readonly=1" 
                                                   class="px-3 py-2 bg-slate-800 hover:bg-blue-600/30 text-slate-300 hover:text-blue-400 rounded border border-slate-600 hover:border-blue-500/50 text-xs font-medium transition-colors flex items-center gap-1">
                                                    <i data-lucide="eye" class="w-3.5 h-3.5"></i> เรียกดู
                                                </a>
                                                <a href="<?= BASE_URL ?>/budgets/tracking/<?= $activity['record_id'] ?>/form" 
                                                   class="px-3 py-2 bg-amber-600/20 hover:bg-amber-600/30 text-amber-400 rounded border border-amber-600/50 text-xs font-medium transition-colors flex items-center gap-1">
                                                    <i data-lucide="edit-2" class="w-3.5 h-3.5"></i> แก้ไข
                                                </a>
                                            <?php else: ?>
                                                <form action="<?= BASE_URL ?>/budgets/tracking/create-record" method="POST" class="inline">
                                                    <?= \App\Core\View::csrf() ?>
                                                    <input type="hidden" name="session_id" value="<?= $session['id'] ?>">
                                                    <input type="hidden" name="activity_id" value="<?= $activity['id'] ?>">
                                                    <button type="submit" 
                                                            class="px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white rounded shadow-lg shadow-primary-900/20 text-sm font-medium transition-colors flex items-center gap-2">
                                                        <i data-lucide="plus" class="w-3.5 h-3.5"></i> บันทึกข้อมูล
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
            <?php endforeach; ?>
        </div>

        <!-- Bottom Navigation -->
        <div class="flex justify-start mt-6">
            <a href="<?= \App\Core\View::url('/budgets/tracking?year=' . $currentYear) ?>" class="btn btn-secondary">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>กลับ</span>
            </a>
        </div>
    </div>
</div>

<!-- Initialize Lucide Icons -->
<script>
    lucide.createIcons();
</script>
