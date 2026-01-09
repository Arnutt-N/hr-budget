<div class="max-w-xl mx-auto animate-fade-in">
    <div class="mb-6">
        <a href="<?= \App\Core\View::url('/requests') ?>" class="text-dark-muted hover:text-white mb-2 inline-flex items-center gap-1 transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> ย้อนกลับ
        </a>
        <h1 class="text-2xl font-bold text-white">สร้างคำของบประมาณใหม่</h1>
    </div>

    <div class="bg-dark-card border border-dark-border rounded-xl p-6">
        <form method="POST" action="<?= \App\Core\View::url('/requests') ?>">
            <?= \App\Core\View::csrf() ?>
            
            <div class="space-y-6">
                <!-- Fiscal Year -->
                <div>
                    <label class="block text-sm font-medium text-dark-text mb-2">
                        ปีงบประมาณ <span class="text-red-400">*</span>
                    </label>
                    <select name="fiscal_year" required class="input w-full">
                        <?php foreach ($fiscalYears as $year): ?>
                        <option value="<?= $year['value'] ?>" <?= $year['value'] == $fiscalYear ? 'selected' : '' ?>>
                            <?= $year['label'] ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Organization (Phase 3) -->
                <div>
                    <label class="block text-sm font-medium text-dark-text mb-2">
                        หน่วยงานเจ้าของงบ <span class="text-red-400">*</span>
                    </label>
                    <select name="org_id" required class="input w-full">
                        <option value="">-- เลือกหน่วยงาน --</option>
                        <?php if (!empty($organizations)): ?>
                            <?php foreach ($organizations as $org): ?>
                            <option value="<?= $org['org_id'] ?>">
                                <?= $org['org_name'] ?>
                            </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="" disabled>ไม่พบข้อมูลหน่วยงาน</option>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- Title -->
                <div>
                    <label class="block text-sm font-medium text-dark-text mb-2">
                        หัวข้อคำขอ <span class="text-red-400">*</span>
                    </label>
                    <input type="text" name="request_title" required class="input w-full" placeholder="ระบุชื่อรายการคำขอ เช่น ขออนุมัติจัดซื้อ..." autofocus>
                </div>

                <div class="pt-4 flex justify-end gap-3">
                    <a href="<?= \App\Core\View::url('/requests') ?>" class="btn btn-secondary">ยกเลิก</a>
                    <button type="submit" class="btn btn-primary">
                        <i data-lucide="check" class="w-5 h-5"></i>
                        สร้างคำขอ
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
