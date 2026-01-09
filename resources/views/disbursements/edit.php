<div class="max-w-2xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <a href="/budgets/disbursements/<?= $header['id'] ?>" class="inline-flex items-center gap-2 text-slate-400 hover:text-slate-200 transition-colors mb-4">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            กลับหน้ารายละเอียด
        </a>
        <h1 class="text-2xl font-bold text-slate-100">แก้ไขข้อมูลหลัก</h1>
    </div>

    <!-- Form Card -->
    <div class="bg-slate-800/50 border border-slate-700 rounded-xl p-6 shadow-xl">
        <form action="/budgets/disbursements/<?= $header['id'] ?>" method="POST" class="space-y-6">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Fiscal Year -->
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">
                        ปีงบประมาณ <span class="text-rose-500">*</span>
                    </label>
                    <select name="fiscal_year" required
                            class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-slate-100 focus:outline-none focus:border-primary-500">
                        <?php foreach ($fiscalYears as $year): ?>
                            <option value="<?= $year ?>" <?= $year == $header['fiscal_year'] ? 'selected' : '' ?>>
                                <?= $year ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Month -->
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">
                        เดือน <span class="text-rose-500">*</span>
                    </label>
                    <select name="month" required
                            class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-slate-100 focus:outline-none focus:border-primary-500">
                        <?php 
                        $months = [
                            10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม',
                            1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม',
                            4 => 'เมษายน', 5 => 'พฤษภาคม', 6 => 'มิถุนายน',
                            7 => 'กรกฎาคม', 8 => 'สิงหาคม', 9 => 'กันยายน'
                        ];
                        foreach ($months as $k => $v): 
                        ?>
                            <option value="<?= $k ?>" <?= $k == $header['month'] ? 'selected' : '' ?>>
                                <?= $v ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Organization -->
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-2">
                    หน่วยงาน <span class="text-rose-500">*</span>
                </label>
                <select name="organization_id" required
                        class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-slate-100 focus:outline-none focus:border-primary-500">
                    <?php foreach ($organizations as $org): ?>
                        <option value="<?= $org['id'] ?>" <?= $org['id'] == $header['organization_id'] ? 'selected' : '' ?>>
                            <?= $org['code'] ? $org['code'] . ' - ' : '' ?><?= $org['name'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Record Date -->
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-2">
                    วันที่บันทึก <span class="text-rose-500">*</span>
                </label>
                <input type="date" name="record_date" required value="<?= $header['record_date'] ?>"
                       class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-slate-100 focus:outline-none focus:border-primary-500">
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-4 pt-4 border-t border-slate-700">
                <a href="/budgets/disbursements/<?= $header['id'] ?>" 
                   class="px-6 py-2.5 bg-slate-700 text-slate-300 rounded-lg hover:bg-slate-600 transition-colors font-medium">
                    ยกเลิก
                </a>
                <button type="submit" 
                        class="px-6 py-2.5 bg-primary-600 text-white rounded-lg hover:bg-primary-500 transition-colors shadow-lg shadow-primary-900/20 font-medium flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    บันทึกการแก้ไข
                </button>
            </div>

        </form>
    </div>
</div>
