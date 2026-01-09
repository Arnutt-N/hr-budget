<?php
/**
 * Budget Tracking - Create New Session
 * Layout: main
 */
?>

<div class="space-y-6 animate-fade-in">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-white">สร้างรายการเบิกจ่ายใหม่</h1>
            <p class="text-dark-muted text-sm mt-1">เลือกปีงบประมาณและเดือนที่ต้องการบันทึก</p>
        </div>
    </div>

    <!-- Form Card -->
    <div class="max-w-lg mx-auto">
        <div class="bg-dark-card border border-dark-border rounded-xl overflow-hidden">
            <div class="p-6">
                <form action="<?= \App\Core\View::url('/budgets/tracking/store-session') ?>" method="POST" class="space-y-6">
                    <?= \App\Core\View::csrf() ?>
                    
                    <div>
                        <label class="block text-sm font-medium text-white mb-2">ปีงบประมาณ</label>
                        <select name="fiscal_year" class="input w-full" required>
                            <?php foreach ($fiscalYears as $year): ?>
                                <option value="<?= $year['value'] ?? $year ?>" <?= ($year['value'] ?? $year) == \App\Models\FiscalYear::currentYear() ? 'selected' : '' ?>>
                                    <?= $year['label'] ?? $year ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-white mb-2">ประจำเดือน</label>
                        <select name="month" class="input w-full" required>
                            <?php 
                            $thaiMonths = ['', 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 
                                          'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
                            for ($m = 1; $m <= 12; $m++): ?>
                                <option value="<?= $m ?>" <?= $m == date('n') ? 'selected' : '' ?>>
                                    <?= $thaiMonths[$m] ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <div class="bg-blue-500/10 border border-blue-500/20 rounded-lg p-4">
                        <div class="flex items-start gap-3">
                            <i data-lucide="info" class="w-5 h-5 text-blue-400 mt-0.5"></i>
                            <div class="text-sm text-blue-300">
                                ระบบจะสร้างรายการสำหรับกองของคุณ<br>
                                โดยใช้วันที่ปัจจุบันเป็นวันที่บันทึก
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="submit" class="btn btn-primary flex-1">
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                            ดำเนินการต่อ
                        </button>
                        <a href="<?= \App\Core\View::url('/budgets/tracking') ?>" class="btn btn-secondary">
                            ย้อนกลับ
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
