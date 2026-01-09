    <!-- KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Card 1: Budget Allocation -->
        <div class="bg-dark-card border border-dark-border rounded-xl p-6 card-hover group relative">
            <div class="flex justify-between items-start mb-2">
                <div>
                    <p class="text-dark-muted text-sm font-medium">งบประมาณจัดสรร</p>
                    <h3 class="text-2xl font-bold text-white mt-1">
                        <?= \App\Core\View::currency($stats['total_allocated'] ?? 0) ?>
                    </h3>
                </div>
                <div class="p-2 bg-blue-500/10 rounded-lg text-blue-500">
                    <i data-lucide="wallet" class="w-6 h-6"></i>
                </div>
            </div>
            
            <!-- Footer with Tooltip -->
            <div class="pt-3 border-t border-dark-border flex items-center justify-between text-xs cursor-pointer">
                <span class="text-dark-muted">
                    จัดสรร <?= \App\Core\View::currencyShort($stats['total_budget_act'] ?? 0) ?> 
                    <span class="<?= ($stats['transfer_change_amount'] ?? 0) >= 0 ? 'text-green-400' : 'text-red-400' ?>">
                        <?= ($stats['transfer_change_amount'] ?? 0) >= 0 ? '+' : '' ?><?= \App\Core\View::currencyShort($stats['transfer_change_amount'] ?? 0) ?>
                    </span>
                </span>
                <i data-lucide="info" class="w-4 h-4 text-dark-muted hover:text-white transition-colors"></i>
            </div>

            <!-- Tooltip (Tailwind group-hover) -->
            <div class="absolute bottom-full left-0 mb-2 w-full invisible group-hover:visible opacity-0 group-hover:opacity-100 transition-all duration-200 z-10 px-2">
                <div class="bg-dark-overlay border border-dark-border rounded-lg shadow-xl p-3 text-xs w-full backdrop-blur-md">
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-dark-muted">งบตั้งต้น:</span>
                        <span class="text-white font-mono"><?= \App\Core\View::currency($stats['total_budget_act'] ?? 0) ?></span>
                    </div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-dark-muted">โอน/ปรับ:</span>
                        <span class="<?= ($stats['transfer_change_amount'] ?? 0) >= 0 ? 'text-green-400' : 'text-red-400' ?> font-mono">
                            <?= ($stats['transfer_change_amount'] ?? 0) >= 0 ? '+' : '' ?><?= \App\Core\View::currency($stats['transfer_change_amount'] ?? 0) ?>
                        </span>
                    </div>
                    <div class="pt-2 border-t border-dark-border flex justify-between items-center font-bold">
                        <span class="text-white">สุทธิ:</span>
                        <span class="text-blue-400"><?= \App\Core\View::currency($stats['total_allocated'] ?? 0) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2: Total Spent -->
        <div class="bg-dark-card border border-dark-border rounded-xl p-6 card-hover group relative">
            <div class="flex justify-between items-start mb-2">
                <div>
                    <p class="text-dark-muted text-sm font-medium">เบิกจ่ายแล้ว</p>
                    <h3 class="text-2xl font-bold text-orange-400 mt-1">
                        <?= \App\Core\View::currency($stats['total_spending'] ?? 0) ?>
                    </h3>
                </div>
                <div class="p-2 bg-orange-500/10 rounded-lg text-orange-500">
                    <i data-lucide="banknote" class="w-6 h-6"></i>
                </div>
            </div>

             <!-- Footer with Tooltip -->
             <div class="pt-3 border-t border-dark-border flex items-center justify-between text-xs cursor-pointer">
                <span class="text-dark-muted">
                    เบิก <?= \App\Core\View::currencyShort($stats['total_disbursed'] ?? 0) ?> 
                    + PO <?= \App\Core\View::currencyShort($stats['total_po'] ?? 0) ?>
                </span>
                <i data-lucide="info" class="w-4 h-4 text-dark-muted hover:text-white transition-colors"></i>
            </div>

            <!-- Tooltip -->
            <div class="absolute bottom-full left-0 mb-2 w-full invisible group-hover:visible opacity-0 group-hover:opacity-100 transition-all duration-200 z-10 px-2">
                <div class="bg-dark-overlay border border-dark-border rounded-lg shadow-xl p-3 text-xs w-full backdrop-blur-md">
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-dark-muted">เบิกจ่ายจริง:</span>
                        <span class="text-white font-mono"><?= \App\Core\View::currency($stats['total_disbursed'] ?? 0) ?></span>
                    </div>
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-dark-muted">PO ผูกพัน:</span>
                        <span class="text-white font-mono"><?= \App\Core\View::currency($stats['total_po'] ?? 0) ?></span>
                    </div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-dark-muted">ขออนุมัติ:</span>
                        <span class="text-white font-mono">0.00 ฿</span>
                    </div>
                    <div class="pt-2 border-t border-dark-border flex justify-between items-center font-bold">
                        <span class="text-white">รวมใช้:</span>
                        <span class="text-orange-400"><?= \App\Core\View::currency($stats['total_spending'] ?? 0) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 3: Remaining -->
        <div class="bg-dark-card border border-dark-border rounded-xl p-6 card-hover group relative">
            <div class="flex justify-between items-start mb-2">
                <div>
                    <p class="text-dark-muted text-sm font-medium">คงเหลือ</p>
                    <h3 class="text-2xl font-bold text-green-400 mt-1">
                        <?= \App\Core\View::currency($stats['total_balance'] ?? ($stats['total_allocated'] - $stats['total_spending'])) ?>
                    </h3>
                </div>
                <div class="p-2 bg-green-500/10 rounded-lg text-green-500">
                    <i data-lucide="piggy-bank" class="w-6 h-6"></i>
                </div>
            </div>

             <!-- Footer with Tooltip -->
             <div class="pt-3 border-t border-dark-border flex items-center justify-between text-xs cursor-pointer">
                <span class="text-dark-muted">
                    สุทธิ <?= \App\Core\View::currencyShort($stats['total_allocated'] ?? 0) ?> 
                    - ใช้ <?= \App\Core\View::currencyShort($stats['total_spending'] ?? 0) ?>
                </span>
                <i data-lucide="info" class="w-4 h-4 text-dark-muted hover:text-white transition-colors"></i>
            </div>

            <!-- Tooltip -->
            <div class="absolute bottom-full left-0 mb-2 w-full invisible group-hover:visible opacity-0 group-hover:opacity-100 transition-all duration-200 z-10 px-2">
                <div class="bg-dark-overlay border border-dark-border rounded-lg shadow-xl p-3 text-xs w-full backdrop-blur-md">
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-dark-muted">งบสุทธิ:</span>
                        <span class="text-white font-mono"><?= \App\Core\View::currency($stats['total_allocated'] ?? 0) ?></span>
                    </div>
                    <div class="flex justify-between items-center mb-2">
                         <span class="text-dark-muted">รวมใช้จ่าย:</span>
                        <span class="text-orange-300 font-mono">-<?= \App\Core\View::currency($stats['total_spending'] ?? 0) ?></span>
                    </div>
                    <div class="pt-2 border-t border-dark-border flex justify-between items-center font-bold">
                        <span class="text-white">คงเหลือ:</span>
                        <span class="text-green-400"><?= \App\Core\View::currency($stats['total_balance'] ?? 0) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 4: Disbursement Rate -->
        <div class="bg-dark-card border border-dark-border rounded-xl p-6 card-hover">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <p class="text-dark-muted text-sm font-medium">อัตราการเบิกจ่าย</p>
                    <h3 class="text-2xl font-bold text-white mt-1">
                        <?= number_format($stats['percent_spending'] ?? 0, 2) ?>%
                    </h3>
                </div>
                <?php 
                    $rate = $stats['percent_spending'] ?? 0;
                    $rateColor = $rate >= 80 ? 'green' : ($rate >= 50 ? 'orange' : 'red');
                ?>
                <div class="p-2 bg-<?= $rateColor ?>-500/10 rounded-lg text-<?= $rateColor ?>-500">
                    <i data-lucide="trending-up" class="w-6 h-6"></i>
                </div>
            </div>
            <?php if ($rate >= 80): ?>
                <span class="badge badge-green">ดีมาก</span>
            <?php elseif ($rate >= 50): ?>
                <span class="badge badge-orange">ปานกลาง</span>
            <?php else: ?>
                <span class="badge badge-red">ต่ำ</span>
            <?php endif; ?>
             <div class="text-xs text-dark-muted mt-2">เป้าหมาย KPI: 100%</div>
        </div>
    </div>
