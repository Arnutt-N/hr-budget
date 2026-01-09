    <!-- KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Card 1: Budget Allocation -->
        <div class="bg-dark-card border border-dark-border rounded-xl p-6 card-hover group relative">
            <div class="flex justify-between items-start mb-2">
                <div>
                    <p class="text-dark-muted text-sm font-medium">งบประมาณจัดสรร</p>
                    <h3 class="text-2xl font-bold text-blue-400 mt-1">
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
                    งบ <?= \App\Core\View::currencyShort($stats['total_budget_act'] ?? 0) ?> 
                    <span class="<?= ($stats['transfer_change_amount'] ?? 0) >= 0 ? 'text-green-400' : 'text-red-400' ?>">
                        โอน <?= ($stats['transfer_change_amount'] ?? 0) >= 0 ? '+' : '' ?><?= \App\Core\View::currencyShort($stats['transfer_change_amount'] ?? 0) ?>
                    </span>
                </span>
                <i data-lucide="info" class="w-4 h-4 text-dark-muted hover:text-white transition-colors"></i>
            </div>

            <!-- Tooltip (Tailwind group-hover) -->
            <div class="absolute bottom-full left-0 mb-2 w-full invisible group-hover:visible opacity-0 group-hover:opacity-100 transition-all duration-200 z-10 px-2 pointer-events-none">
                <div class="bg-[#1e1e2d] border border-gray-700 rounded-lg shadow-xl p-3 text-xs w-full">
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-gray-400">งบจัดสรร:</span>
                        <span class="text-white font-mono"><?= \App\Core\View::currency($stats['total_budget_act'] ?? 0) ?></span>
                    </div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-gray-400">โอน +/-:</span>
                        <span class="<?= ($stats['transfer_change_amount'] ?? 0) >= 0 ? 'text-green-400' : 'text-red-400' ?> font-mono">
                            <?= ($stats['transfer_change_amount'] ?? 0) >= 0 ? '+' : '' ?><?= \App\Core\View::currency($stats['transfer_change_amount'] ?? 0) ?>
                        </span>
                    </div>
                    <div class="pt-2 border-t border-gray-700 flex justify-between items-center font-bold">
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
                    ขอ 0.00 
                    PO <?= \App\Core\View::currencyShort($stats['total_po'] ?? 0) ?>
                </span>
                <i data-lucide="info" class="w-4 h-4 text-dark-muted hover:text-white transition-colors"></i>
            </div>

            <!-- Tooltip -->
            <div class="absolute bottom-full left-0 mb-2 w-full invisible group-hover:visible opacity-0 group-hover:opacity-100 transition-all duration-200 z-10 px-2 pointer-events-none">
                <div class="bg-[#1e1e2d] border border-gray-700 rounded-lg shadow-xl p-3 text-xs w-full">
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-gray-400">เบิกจ่าย:</span>
                        <span class="text-white font-mono"><?= \App\Core\View::currency($stats['total_disbursed'] ?? 0) ?></span>
                    </div>
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-gray-400">ขออนุมัติ:</span>
                         <span class="text-white font-mono">0.00 ฿</span>
                    </div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-gray-400">PO ผูกพัน:</span>
                        <span class="text-white font-mono"><?= \App\Core\View::currency($stats['total_po'] ?? 0) ?></span>
                    </div>
                    <div class="pt-2 border-t border-gray-700 flex justify-between items-center font-bold">
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
                    งบ <?= \App\Core\View::currencyShort($stats['total_allocated'] ?? 0) ?> 
                    - เบิก <?= \App\Core\View::currencyShort($stats['total_spending'] ?? 0) ?>
                </span>
                <i data-lucide="info" class="w-4 h-4 text-dark-muted hover:text-white transition-colors"></i>
            </div>

            <!-- Tooltip -->
            <div class="absolute bottom-full left-0 mb-2 w-full invisible group-hover:visible opacity-0 group-hover:opacity-100 transition-all duration-200 z-10 px-2 pointer-events-none">
                <div class="bg-[#1e1e2d] border border-gray-700 rounded-lg shadow-xl p-3 text-xs w-full">
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-gray-400">งบสุทธิ:</span>
                        <span class="text-white font-mono"><?= \App\Core\View::currency($stats['total_allocated'] ?? 0) ?></span>
                    </div>
                    <div class="flex justify-between items-center mb-2">
                         <span class="text-gray-400">รวมเบิกจ่าย:</span>
                        <span class="text-orange-300 font-mono">-<?= \App\Core\View::currency($stats['total_spending'] ?? 0) ?></span>
                    </div>
                    <div class="pt-2 border-t border-gray-700 flex justify-between items-center font-bold">
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
            
            <!-- Quarterly Breakdown -->
            <div class="grid grid-cols-4 gap-2 text-center text-xs mt-2 border-t border-dark-border pt-3">
                <div>
                    <div class="text-dark-muted mb-1">Q1</div>
                    <div class="font-medium text-dark-text">-</div>
                </div>
                 <div>
                    <div class="text-dark-muted mb-1">Q2</div>
                    <div class="font-medium text-dark-text">-</div>
                </div>
                 <div>
                    <div class="text-dark-muted mb-1">Q3</div>
                    <div class="font-medium text-dark-text">-</div>
                </div>
                 <div>
                    <div class="text-dark-muted mb-1">Q4</div>
                    <div class="font-medium text-dark-text">-</div>
                </div>
            </div>
        </div>
    </div>
