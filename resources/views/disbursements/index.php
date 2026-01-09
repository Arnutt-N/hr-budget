<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-100">บันทึกเบิกจ่ายงบประมาณ</h1>
            <p class="text-slate-400 mt-1">รายการบันทึกการเบิกจ่ายประจำเดือน</p>
        </div>
        <a href="/budgets/disbursements/create" 
           class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-500 transition-colors shadow-lg shadow-primary-900/20">
            <i data-lucide="plus" class="w-4 h-4"></i>
            สร้างรายการใหม่
        </a>
    </div>

    <!-- Filters (Placeholder) -->
    <div class="bg-slate-800/50 p-4 rounded-xl border border-slate-700 flex gap-4">
        <div class="flex-1">
            <input type="text" placeholder="ค้นหา..." class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-slate-100 focus:outline-none focus:border-primary-500">
        </div>
        <select class="px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-slate-100 focus:outline-none focus:border-primary-500">
            <option value="">ปีงบประมาณทั้งหมด</option>
            <!-- Add options dynamically from Controller if needed -->
        </select>
        <select class="px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-slate-100 focus:outline-none focus:border-primary-500">
            <option value="">ทุกหน่วยงาน</option>
        </select>
    </div>

    <!-- Data Table -->
    <div class="bg-slate-800/50 rounded-xl border border-slate-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-700/50 text-slate-400 uppercase text-xs">
                    <tr>
                        <th class="px-6 py-4 font-medium">วันที่บันทึก</th>
                        <th class="px-6 py-4 font-medium">ปีงบ / เดือน</th>
                        <th class="px-6 py-4 font-medium">หน่วยงาน</th>
                        <th class="px-6 py-4 font-medium text-center">รายการ</th>
                        <th class="px-6 py-4 font-medium text-right">ยอดรวม</th>
                        <th class="px-6 py-4 font-medium text-center">สถานะ</th>
                        <th class="px-6 py-4 font-medium text-right">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    <?php if (empty($disbursements)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-slate-500">
                                ไม่พบข้อมูลรายการเบิกจ่าย
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($disbursements as $item): ?>
                            <tr class="hover:bg-slate-700/30 transition-colors">
                                <td class="px-6 py-4 text-slate-300">
                                    <?= date('d/m/Y', strtotime($item['record_date'])) ?>
                                </td>
                                <td class="px-6 py-4 text-slate-100 font-medium">
                                    <div class="flex flex-col">
                                        <span><?= $item['fiscal_year'] ?></span>
                                        <span class="text-xs text-slate-400">เดือน: <?= $item['month'] ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-slate-300">
                                    <?= htmlspecialchars($item['organization_name']) ?>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-700 text-slate-300">
                                        <?= $item['items_count'] ?? 0 ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right font-medium text-slate-100">
                                    <?= number_format($item['total_amount'] ?? 0, 2) ?>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <?php 
                                        $statusClass = match($item['status']) {
                                            'draft' => 'bg-slate-700 text-slate-400',
                                            'submitted' => 'bg-blue-900/50 text-blue-400',
                                            'approved' => 'bg-emerald-900/50 text-emerald-400',
                                            default => 'bg-slate-700 text-slate-400'
                                        };
                                        $statusLabel = match($item['status']) {
                                            'draft' => 'ร่าง',
                                            'submitted' => 'ส่งแล้ว',
                                            'approved' => 'อนุมัติ',
                                            default => $item['status']
                                        };
                                    ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $statusClass ?>">
                                        <?= $statusLabel ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right flex items-center justify-end gap-2">
                                    <a href="/budgets/disbursements/<?= $item['id'] ?>" 
                                       class="p-2 text-slate-400 hover:text-primary-400 hover:bg-slate-700 rounded-lg transition-colors"
                                       title="ดูรายละเอียด">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </a>
                                    <a href="/budgets/disbursements/<?= $item['id'] ?>/edit" 
                                       class="p-2 text-slate-400 hover:text-amber-400 hover:bg-slate-700 rounded-lg transition-colors"
                                       title="แก้ไข">
                                        <i data-lucide="edit-2" class="w-4 h-4"></i>
                                    </a>
                                    <form action="/budgets/disbursements/<?= $item['id'] ?>/delete" method="POST" class="inline-block"
                                          onsubmit="return confirm('ยืนยันการลบรายการนี้?');">
                                        <button type="submit" 
                                                class="p-2 text-slate-400 hover:text-rose-400 hover:bg-slate-700 rounded-lg transition-colors"
                                                title="ลบ">
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
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="px-6 py-4 border-t border-slate-700 flex justify-center">
            <div class="flex gap-2">
                <?php for($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?= $i ?>" 
                       class="px-3 py-1 rounded <?= $i == $page ? 'bg-primary-600 text-white' : 'bg-slate-700 text-slate-300 hover:bg-slate-600' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
