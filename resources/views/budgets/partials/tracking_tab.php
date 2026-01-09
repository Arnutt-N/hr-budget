<?php
/**
 * Partial View: Tracking Content for a Tab (Updated for Expense Structure)
 * Variables: $expenseType, $groups, $trackings, $fiscalYear, $orgId
 */
?>

<?php if (empty($groups)): ?>
    <div class="alert alert-info m-3">ไม่มีข้อมูลกลุ่มรายจ่ายในประเภทนี้</div>
<?php else: ?>
    
    <div class="table-responsive">
        <table class="table table-bordered table-hover mb-0">
            <thead class="table-light text-center align-middle sticky-top" style="top: 0; z-index: 1020;">
                <tr>
                    <th rowspan="2" style="width: 30%;">รายการ</th>
                    <th colspan="2" class="bg-primary text-white">งบประมาณ</th>
                    <th colspan="3" class="bg-success text-white">ผลการเบิกจ่าย</th>
                    <th rowspan="2" style="width: 15%;">คงเหลือ</th>
                </tr>
                <tr>
                    <th class="bg-primary text-white" style="width: 12%;">จัดสรร</th>
                    <th class="bg-primary text-white" style="width: 12%;">โอน (+/-)</th>
                    <th class="bg-success text-white" style="width: 12%;">เบิกจ่าย</th>
                    <th class="bg-success text-white" style="width: 12%;">รอเบิก (PO)</th>
                    <th class="bg-success text-white" style="width: 12%;">ผูกพัน</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($groups as $group): ?>
                    <?php 
                        $items = $group['items'] ?? [];
                    ?>
                    
                    <!-- Group Header -->
                    <tr class="table-secondary">
                        <td colspan="7" class="fw-bold ps-2">
                            <i class="bi bi-folder me-2"></i><?= htmlspecialchars($group['name_th']) ?>
                        </td>
                    </tr>

                    <!-- Items under this group -->
                    <?php foreach ($items as $item): ?>
                        <?php
                            $t = $trackings[$item['id']] ?? [];
                            $allocated = (float)($t['allocated'] ?? 0);
                            $transfer = (float)($t['transfer'] ?? 0);
                            $disbursed = (float)($t['disbursed'] ?? 0);
                            $pending = (float)($t['pending'] ?? 0);
                            $po = (float)($t['po'] ?? 0);
                            $remaining = ($allocated + $transfer) - ($disbursed + $pending + $po);
                            
                            // Indentation based on level
                            $indent = ($item['level'] ?? 0) + 1;
                        ?>
                        <tr>
                            <td class="ps-<?= ($indent * 3) + 2 ?>">
                                <?= htmlspecialchars($item['name_th'] ?? $item['name'] ?? '') ?>
                                <?php if(!empty($item['is_header'])): ?> <span class="badge bg-secondary">หัวข้อ</span><?php endif; ?>
                            </td>
                            <td class="p-0">
                                <input type="text" class="form-control form-control-sm border-0 text-end budget-input inp-allocated" 
                                       value="<?= ($allocated != 0) ? number_format($allocated, 2) : '' ?>" 
                                       data-item-id="<?= $item['id'] ?>" data-field="allocated" placeholder="0.00">
                            </td>
                            <td class="p-0">
                                <input type="text" class="form-control form-control-sm border-0 text-end budget-input inp-transfer" 
                                       value="<?= ($transfer != 0) ? number_format($transfer, 2) : '' ?>" 
                                       data-item-id="<?= $item['id'] ?>" data-field="transfer" placeholder="0.00">
                            </td>
                            <td class="p-0">
                                <input type="text" class="form-control form-control-sm border-0 text-end budget-input inp-disbursed" 
                                       value="<?= ($disbursed != 0) ? number_format($disbursed, 2) : '' ?>" 
                                       data-item-id="<?= $item['id'] ?>" data-field="disbursed" placeholder="0.00">
                            </td>
                            <td class="p-0">
                                <input type="text" class="form-control form-control-sm border-0 text-end budget-input inp-pending" 
                                       value="<?= ($pending != 0) ? number_format($pending, 2) : '' ?>" 
                                       data-item-id="<?= $item['id'] ?>" data-field="pending" placeholder="0.00">
                            </td>
                            <td class="p-0">
                                <input type="text" class="form-control form-control-sm border-0 text-end budget-input inp-po" 
                                       value="<?= ($po != 0) ? number_format($po, 2) : '' ?>" 
                                       data-item-id="<?= $item['id'] ?>" data-field="po" placeholder="0.00">
                            </td>
                            <td class="text-end fw-bold align-middle pe-2 val-remaining <?= ($remaining < 0) ? 'text-danger' : '' ?>">
                                <?= number_format($remaining, 2) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

<?php endif; ?>
