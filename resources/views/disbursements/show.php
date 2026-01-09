<div class="space-y-6">
    <!-- Header with Back Button -->
    <div class="flex items-center justify-between">
        <a href="/budgets/disbursements" class="inline-flex items-center gap-2 text-slate-400 hover:text-slate-200 transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            กลับหน้ารายการ
        </a>
        <div class="flex items-center gap-3">
            <a href="/budgets/disbursements/<?= $header['id'] ?>/edit" 
               class="px-4 py-2 bg-slate-700 text-slate-300 rounded-lg hover:bg-slate-600 transition-colors flex items-center gap-2">
                <i data-lucide="edit-2" class="w-4 h-4"></i> แก้ไขหัวข้อ
            </a>
            <form action="/budgets/disbursements/<?= $header['id'] ?>/delete" method="POST" 
                  onsubmit="return confirm('ยืนยันลบรายการทั้งหมด?');">
                <button type="submit" class="px-4 py-2 bg-rose-900/50 text-rose-400 rounded-lg border border-rose-900 hover:bg-rose-900 transition-colors flex items-center gap-2">
                    <i data-lucide="trash-2" class="w-4 h-4"></i> ลบ
                </button>
            </form>
        </div>
    </div>

    <!-- Info Card -->
    <div class="bg-slate-800/50 border border-slate-700 rounded-xl p-6 shadow-lg">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div>
                <label class="block text-xs font-medium text-slate-500 uppercase">หน่วยงาน</label>
                <div class="mt-1 text-lg font-semibold text-slate-100"><?= $organization['name'] ?></div>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 uppercase">ปีงบ / เดือน</label>
                <div class="mt-1 text-lg font-semibold text-slate-100">
                    <?= $header['fiscal_year'] ?> / <span class="text-primary-400"><?= $header['month'] ?></span>
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 uppercase">วันที่บันทึก</label>
                <div class="mt-1 text-lg font-semibold text-slate-100">
                    <?= date('d/m/Y', strtotime($header['record_date'])) ?>
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 uppercase">สถานะ</label>
                <div class="mt-1">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-slate-700 text-slate-300">
                        <?= ucfirst($header['status']) ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Details Section -->
    <div class="bg-slate-800/50 border border-slate-700 rounded-xl overflow-hidden min-h-[400px]">
        <div class="px-6 py-4 border-b border-slate-700 flex items-center justify-between bg-slate-900/30">
            <h2 class="text-lg font-bold text-slate-100 flex items-center gap-2">
                <i data-lucide="list" class="w-5 h-5 text-primary-400"></i>
                รายการรายละเอียด
            </h2>
            <a href="/budgets/disbursements/<?= $header['id'] ?>/items/create" 
               class="px-4 py-2 bg-primary-600 text-white rounded-lg shadow-lg shadow-primary-900/20 hover:bg-primary-500 transition-colors flex items-center gap-2 font-medium">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                เพิ่มรายละเอียด
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-700/50 text-slate-400 uppercase text-xs">
                    <tr>
                        <th class="px-6 py-3 font-medium">ประเภทรายจ่าย</th>
                        <th class="px-6 py-3 font-medium">กิจกรรม</th>
                        <th class="px-6 py-3 font-medium text-right">รวมเงิน</th>
                        <th class="px-6 py-3 font-medium text-right w-32">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    <?php if (empty($details)): ?>
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-slate-500">
                                    <i data-lucide="file-plus" class="w-12 h-12 mb-3 opacity-50"></i>
                                    <p class="text-lg font-medium text-slate-400">ยังไม่มีรายการ</p>
                                    <p class="text-sm">กดปุ่ม "เพิ่มรายละเอียด" เพื่อเริ่มบันทึกข้อมูล</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($details as $detail): 
                            $total = $detail['item_0'] + $detail['item_1'] + $detail['item_2'] + 
                                     $detail['item_3'] + $detail['item_4'] + $detail['item_5'];
                        ?>
                            <tr class="hover:bg-slate-700/30 transition-colors">
                                <td class="px-6 py-4">
                                    <span class="font-medium text-slate-200"><?= $detail['expense_type_name'] ?></span>
                                    <div class="text-xs text-slate-500 mt-1"><?= $detail['plan_name'] ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-slate-300"><?= $detail['output_name'] ?></div>
                                    <div class="text-xs text-primary-400 mt-0.5"><?= $detail['activity_name'] ?></div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="text-slate-100 font-semibold text-lg"><?= number_format($total, 2) ?></span>
                                </td>
                                <td class="px-6 py-4 text-right flex justify-end gap-2">
                                    <a href="/budgets/disbursements/<?= $header['id'] ?>/items/<?= $detail['id'] ?>/edit" 
                                       class="p-2 text-slate-400 hover:text-amber-400 hover:bg-slate-700 rounded-lg transition-colors">
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </a>
                                    <form action="/budgets/disbursements/<?= $header['id'] ?>/items/<?= $detail['id'] ?>/delete" 
                                          method="POST" onsubmit="return confirm('ลบรายการนี้?');" class="inline-block">
                                        <button type="submit" class="p-2 text-slate-400 hover:text-rose-400 hover:bg-slate-700 rounded-lg transition-colors">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
