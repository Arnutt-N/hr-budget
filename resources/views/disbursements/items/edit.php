<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <a href="/budgets/disbursements/<?= $header['id'] ?>" class="inline-flex items-center gap-2 text-slate-400 hover:text-slate-200 transition-colors mb-4">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            กลับหน้ารายละเอียด
        </a>
        <h1 class="text-2xl font-bold text-slate-100">แก้ไขรายละเอียด</h1>
    </div>

    <!-- Main Card -->
    <div class="bg-slate-800/50 border border-slate-700 rounded-xl shadow-xl overflow-hidden">
        
        <!-- Tabs (Locked/Highlight only) - For Edit, we usually lock the type or allow change. 
             If we allow change, we just show tabs. If locked, maybe just show current type info.
             Let's show tabs but pre-select and allow change (which updates hidden input).
        -->
        <div class="border-b border-slate-700 bg-slate-900/50">
            <nav class="flex overflow-x-auto" id="expense-tabs">
                <?php 
                $types = [
                    'personnel' => ['icon' => 'users', 'color' => 'blue', 'label' => 'งบบุคลากร', 'id' => 1],
                    'operation' => ['icon' => 'briefcase', 'color' => 'emerald', 'label' => 'งบดำเนินงาน', 'id' => 2],
                    'investment' => ['icon' => 'building', 'color' => 'purple', 'label' => 'งบลงทุน', 'id' => 3],
                    'subsidy' => ['icon' => 'heart-handshake', 'color' => 'amber', 'label' => 'งบเงินอุดหนุน', 'id' => 4],
                    'other' => ['icon' => 'more-horizontal', 'color' => 'rose', 'label' => 'งบรายจ่ายอื่น', 'id' => 5],
                ];
                
                // Find key for current type
                $currentKey = 'personnel'; // default;
                foreach ($types as $k => $t) {
                    if ($t['id'] == $detail['expense_type_id']) {
                        $currentKey = $k;
                        break;
                    }
                }

                foreach ($types as $key => $t): 
                ?>
                <button type="button" onclick="selectTab('<?= $key ?>', <?= $t['id'] ?>)"
                        class="tab-btn group flex-1 min-w-[140px] px-4 py-4 text-sm font-semibold flex items-center justify-center gap-2 hover:bg-slate-800 transition-colors border-b-2 border-transparent data-[active=true]:border-<?= $t['color'] ?>-500 data-[active=true]:bg-slate-800/80 data-[active=true]:text-<?= $t['color'] ?>-400 text-slate-400"
                        data-tab="<?= $key ?>"
                        data-active="<?= $key === $currentKey ? 'true' : 'false' ?>">
                    <i data-lucide="<?= $t['icon'] ?>" class="w-4 h-4"></i>
                    <?= $t['label'] ?>
                </button>
                <?php endforeach; ?>
            </nav>
        </div>

        <div class="p-6">
            <form action="/budgets/disbursements/<?= $header['id'] ?>/items/<?= $detail['id'] ?>" method="POST" id="itemForm" class="space-y-6">
                <!-- Use hidden input for expense_type_id -->
                <input type="hidden" name="expense_type_id" id="expense_type_id" value="<?= $detail['expense_type_id'] ?>">

                <!-- Hierarchy Selects -->
                <div class="grid grid-cols-1 gap-6 p-6 bg-slate-900/30 rounded-xl border border-slate-700/50">
                    <h3 class="text-sm font-bold text-slate-300 uppercase tracking-wide mb-2 flex items-center gap-2">
                        <i data-lucide="layers" class="w-4 h-4"></i> โครงสร้างงบประมาณ
                    </h3>
                    
                    <!-- Plan -->
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-1">แผนงาน</label>
                        <select name="plan_id" id="plan_id" class="w-full px-3 py-2 bg-slate-800 border border-slate-600 rounded-lg text-slate-100 focus:outline-none focus:border-primary-500 text-sm">
                            <option value="">-- เลือกแผนงาน --</option>
                            <?php foreach ($plans as $p): ?>
                                <option value="<?= $p['id'] ?>" <?= $p['id'] == $detail['plan_id'] ? 'selected' : '' ?>>
                                    <?= $p['code'] ?> <?= $p['name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Output (AJAX) -->
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-1">ผลผลิต / โครงการ</label>
                        <select name="output_id" id="output_id" class="w-full px-3 py-2 bg-slate-800 border border-slate-600 rounded-lg text-slate-100 focus:outline-none focus:border-primary-500 text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                            <option value="">-- เลือกผลผลิต / โครงการ --</option>
                            <?php foreach ($outputs as $o): ?>
                                <option value="<?= $o['id'] ?>" <?= $o['id'] == $detail['output_id'] ? 'selected' : '' ?>>
                                    <?= $o['code'] ? $o['code'] . ' ' : '' ?><?= $o['name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Activity (AJAX) -->
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-1">กิจกรรม</label>
                        <select name="activity_id" id="activity_id" class="w-full px-3 py-2 bg-slate-800 border border-slate-600 rounded-lg text-slate-100 focus:outline-none focus:border-primary-500 text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                            <option value="">-- เลือกกิจกรรม --</option>
                            <?php foreach ($activities as $a): ?>
                                <option value="<?= $a['id'] ?>" <?= $a['id'] == $detail['activity_id'] ? 'selected' : '' ?>>
                                    <?= $a['code'] ? $a['code'] . ' ' : '' ?><?= $a['name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Input Money Items -->
                <div class="p-6 bg-slate-900/30 rounded-xl border border-slate-700/50">
                    <h3 class="text-sm font-bold text-slate-300 uppercase tracking-wide mb-6 flex items-center gap-2">
                        <i data-lucide="coins" class="w-4 h-4"></i> รายละเอียดจำนวนเงิน
                        <span class="text-xs font-normal text-slate-500 ml-2">(หน่วย: บาท)</span>
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
                        <?php for ($i=0; $i<=5; $i++): ?>
                        <div class="flex items-center gap-4">
                            <label class="w-32 text-sm text-slate-400 text-right">รายการที่ <?= $i ?></label>
                            <input type="number" step="0.01" name="item_<?= $i ?>" placeholder="0.00"
                                   value="<?= $detail["item_$i"] > 0 ? $detail["item_$i"] : '' ?>"
                                   class="flex-1 px-3 py-2 bg-slate-800 border border-slate-600 rounded-lg text-right text-slate-100 focus:outline-none focus:border-primary-500 font-mono">
                        </div>
                        <?php endfor; ?>
                    </div>
                </div>

                <!-- Notes -->
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-1">หมายเหตุ</label>
                    <textarea name="notes" rows="2" class="w-full px-3 py-2 bg-slate-800 border border-slate-600 rounded-lg text-slate-100 focus:outline-none focus:border-primary-500 text-sm"><?= $detail['notes'] ?></textarea>
                </div>

                <!-- Footer Actions -->
                <div class="flex justify-end gap-3 pt-4 border-t border-slate-700">
                    <a href="/budgets/disbursements/<?= $header['id'] ?>" class="px-6 py-2.5 bg-slate-700 text-slate-300 rounded-lg hover:bg-slate-600 transition-colors font-medium">ยกเลิก</a>
                    <button type="submit" class="px-6 py-2.5 bg-primary-600 text-white rounded-lg hover:bg-primary-500 transition-colors font-medium shadow-lg shadow-primary-900/20 flex items-center gap-2">
                        <i data-lucide="save" class="w-4 h-4"></i> บันทึกการแก้ไข
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
    // Reuse script from create.php with adjustments for edit logic if needed
    
    // 1. Tab Logic
    function selectTab(tabName, typeId) {
        document.getElementById('expense_type_id').value = typeId;
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.dataset.active = (btn.dataset.tab === tabName) ? 'true' : 'false';
        });
    }

    // 2. Hierarchy Logic (Copy-Paste from create.php)
    const planSelect = document.getElementById('plan_id');
    const outputSelect = document.getElementById('output_id');
    const activitySelect = document.getElementById('activity_id');

    planSelect.addEventListener('change', async function() {
        const planId = this.value;
        outputSelect.innerHTML = '<option value="">กำลังโหลด...</option>';
        outputSelect.disabled = true;
        activitySelect.innerHTML = '<option value="">-- กรุณาเลือกผลผลิตก่อน --</option>';
        activitySelect.disabled = true;

        if (planId) {
            try {
                const res = await fetch(`/api/budget-plans/outputs?parent_id=${planId}`);
                const data = await res.json();
                
                outputSelect.innerHTML = '<option value="">-- เลือกผลผลิต / โครงการ --</option>';
                Object.values(data).forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.textContent = (item.code ? item.code + ' ' : '') + item.name;
                    outputSelect.appendChild(option);
                });
                outputSelect.disabled = false;
            } catch (e) {
                console.error(e);
                outputSelect.innerHTML = '<option value="">โหลดข้อมูลล้มเหลว</option>';
            }
        } else {
            outputSelect.innerHTML = '<option value="">-- กรุณาเลือกแผนงานก่อน --</option>';
        }
    });

    outputSelect.addEventListener('change', async function() {
        const outputId = this.value;
        activitySelect.innerHTML = '<option value="">กำลังโหลด...</option>';
        activitySelect.disabled = true;

        if (outputId) {
            try {
                const res = await fetch(`/api/budget-plans/activities?parent_id=${outputId}`);
                const data = await res.json();
                
                activitySelect.innerHTML = '<option value="">-- เลือกกิจกรรม --</option>';
                Object.values(data).forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.textContent = (item.code ? item.code + ' ' : '') + item.name;
                    activitySelect.appendChild(option);
                });
                activitySelect.disabled = false;
            } catch (e) {
                console.error(e);
                activitySelect.innerHTML = '<option value="">โหลดข้อมูลล้มเหลว</option>';
            }
        } else {
            activitySelect.innerHTML = '<option value="">-- กรุณาเลือกผลผลิตก่อน --</option>';
        }
    });
</script>
