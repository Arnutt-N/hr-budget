<div class="space-y-6 animate-fade-in">
    <!-- Header & Filter -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white mb-1">ภาพรวมคำของบประมาณ</h1>
            <p class="text-dark-muted text-sm">สถิติและสถานะคำขอประจำปีงบประมาณ <?= $fiscalYear ?></p>
        </div>
        
        <div class="flex items-center gap-3">
            <form action="" method="GET" class="flex items-center gap-2">
                <select name="year" onchange="this.form.submit()" class="input py-1.5 text-sm">
                    <?php foreach ($fiscalYears as $year): ?>
                        <option value="<?= $year['year'] ?>" <?= $year['year'] == $fiscalYear ? 'selected' : '' ?>>
                            ปีงบประมาณ <?= $year['year'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
            
            <a href="<?= \App\Core\View::url('/requests/create') ?>" class="btn btn-primary">
                <i data-lucide="plus" class="w-4 h-4"></i> สร้างคำขอใหม่
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Requests -->
        <div class="bg-dark-card border border-dark-border rounded-xl p-6">
            <div class="flex justify-between items-start mb-4">
                <div class="p-3 bg-blue-500/10 rounded-lg">
                    <i data-lucide="files" class="w-6 h-6 text-blue-500"></i>
                </div>
                <span class="text-xs font-medium text-dark-muted">คำขอทั้งหมด</span>
            </div>
            <div class="text-2xl font-bold text-white mb-1"><?= number_format($stats['total_requests'] ?? 0) ?></div>
            <div class="text-xs text-dark-muted">รายการ</div>
        </div>

        <!-- Pending Approval -->
        <div class="bg-dark-card border border-dark-border rounded-xl p-6">
            <div class="flex justify-between items-start mb-4">
                <div class="p-3 bg-orange-500/10 rounded-lg">
                    <i data-lucide="clock" class="w-6 h-6 text-orange-500"></i>
                </div>
                <span class="text-xs font-medium text-dark-muted">รออนุมัติ</span>
            </div>
            <div class="text-2xl font-bold text-white mb-1"><?= number_format($stats['pending_count'] ?? 0) ?></div>
            <div class="text-xs text-dark-muted">รายการ</div>
        </div>

        <!-- Approved Amount -->
        <div class="bg-dark-card border border-dark-border rounded-xl p-6">
            <div class="flex justify-between items-start mb-4">
                <div class="p-3 bg-green-500/10 rounded-lg">
                    <i data-lucide="check-circle" class="w-6 h-6 text-green-500"></i>
                </div>
                <span class="text-xs font-medium text-dark-muted">อนุมัติแล้ว</span>
            </div>
            <div class="text-2xl font-bold text-green-400 mb-1"><?= \App\Core\View::currency($stats['approved_amount'] ?? 0) ?></div>
            <div class="text-xs text-dark-muted">จาก <?= number_format($stats['approved_count'] ?? 0) ?> รายการ</div>
        </div>

        <!-- Total Requested Amount -->
        <div class="bg-dark-card border border-dark-border rounded-xl p-6">
            <div class="flex justify-between items-start mb-4">
                <div class="p-3 bg-purple-500/10 rounded-lg">
                    <i data-lucide="coins" class="w-6 h-6 text-purple-500"></i>
                </div>
                <span class="text-xs font-medium text-dark-muted">ยอดรวมที่ขอ</span>
            </div>
            <div class="text-2xl font-bold text-white mb-1"><?= \App\Core\View::currency($stats['total_amount'] ?? 0) ?></div>
            <div class="text-xs text-dark-muted">บาท</div>
        </div>
    </div>

    <!-- Recent Component -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Requests -->
        <div class="lg:col-span-2 bg-dark-card border border-dark-border rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-dark-border flex justify-between items-center">
                <h3 class="font-semibold text-white">คำขอล่าสุด</h3>
                <a href="<?= \App\Core\View::url('/requests') ?>" class="text-sm text-primary hover:text-primary-hover">ดูทั้งหมด</a>
            </div>
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr>
                            <th>หัวข้อ</th>
                            <th>ผู้ขอ</th>
                            <th>วันที่</th>
                            <th>สถานะ</th>
                            <th class="text-right">จำนวนเงิน</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recentRequests)): ?>
                            <?php foreach ($recentRequests as $req): ?>
                                <tr class="group hover:bg-dark-bg/50 transition-colors cursor-pointer" 
                                    onclick="window.location='<?= \App\Core\View::url('/requests/' . $req['id']) ?>'">
                                    <td>
                                        <div class="font-medium text-white group-hover:text-primary transition-colors">
                                            <?= htmlspecialchars($req['request_title']) ?>
                                        </div>
                                    </td>
                                    <td class="text-sm text-dark-muted">
                                        <?= htmlspecialchars($req['created_by_name'] ?? '-') ?>
                                    </td>
                                    <td class="text-sm text-dark-muted">
                                        <?= date('d/m/Y', strtotime($req['created_at'])) ?>
                                    </td>
                                    <td>
                                        <?php
                                            $statusClass = match($req['request_status']) {
                                                'draft' => 'badge-gray',
                                                'pending' => 'badge-orange',
                                                'approved' => 'badge-green',
                                                'rejected' => 'badge-red',
                                                default => 'badge-gray'
                                            };
                                            $statusLabel = match($req['request_status']) {
                                                'draft' => 'ร่าง',
                                                'pending' => 'รออนุมัติ',
                                                'approved' => 'อนุมัติ',
                                                'rejected' => 'ไม่อนุมัติ',
                                                default => 'ร่าง'
                                            };
                                        ?>
                                        <span class="badge <?= $statusClass ?>"><?= $statusLabel ?></span>
                                    </td>
                                    <td class="text-right font-mono text-white">
                                        <?= number_format($req['total_amount'], 2) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-8 text-dark-muted">
                                    ยังไม่มีคำของบประมาณ
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Quick Actions & Info -->
        <div class="space-y-6">
            <div class="bg-gradient-to-br from-primary/20 to-primary/5 border border-primary/20 rounded-xl p-6">
                <h3 class="font-semibold text-white mb-2">ยินดีต้อนรับสู่ระบบคำขอ</h3>
                <p class="text-sm text-dark-muted mb-4">
                    ระบบจัดการคำของบประมาณออนไลน์ ช่วยให้คุณส่งคำขอและติดตามสถานะได้อย่างรวดเร็ว
                </p>
                <a href="<?= \App\Core\View::url('/requests/create') ?>" class="btn btn-primary w-full justify-center">
                    เริ่มสร้างคำขอใหม่
                </a>
            </div>

            <!-- Status Legends -->
            <div class="bg-dark-card border border-dark-border rounded-xl p-6">
                <h3 class="font-semibold text-white mb-4">คำอธิบายสถานะ</h3>
                <div class="space-y-3">
                    <div class="flex items-center gap-3">
                        <span class="badge badge-gray w-20 justify-center">ร่าง</span>
                        <span class="text-sm text-dark-muted">บันทึกชั่วคราว ยังไม่ส่ง</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="badge badge-orange w-20 justify-center">รออนุมัติ</span>
                        <span class="text-sm text-dark-muted">ส่งแล้ว รอการตรวจสอบ</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="badge badge-green w-20 justify-center">อนุมัติ</span>
                        <span class="text-sm text-dark-muted">อนุมัติแล้ว ดำเนินการต่อได้</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="badge badge-red w-20 justify-center">ไม่อนุมัติ</span>
                        <span class="text-sm text-dark-muted">ถูกปฏิเสธ (ดูเหตุผลในรายละเอียด)</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
