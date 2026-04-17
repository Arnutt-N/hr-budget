<?php
/**
 * Admin Organizations Show View - Read Only Modal
 */
use App\Models\Organization;

$typeLabels = Organization::getTypeLabels();
$regionLabels = Organization::getRegionLabels();

$typeLabel = $typeLabels[$organization['org_type']] ?? '-';
$regionLabel = $regionLabels[$organization['region']] ?? '-';

// Dynamic Labels
$labelMap = [
    'ministry' => ['name' => 'ชื่อกระทรวง', 'abbr' => 'ชื่อย่อกระทรวง', 'code' => 'รหัสกระทรวง'],
    'department' => ['name' => 'ชื่อกรม', 'abbr' => 'ชื่อย่อกรม', 'code' => 'รหัสกรม'],
    'division' => ['name' => 'ชื่อกอง/สำนัก', 'abbr' => 'ชื่อย่อกอง', 'code' => 'รหัสกอง'],
    'section' => ['name' => 'ชื่อกลุ่มงาน', 'abbr' => 'ชื่อย่อกลุ่มงาน', 'code' => 'รหัสกลุ่มงาน'],
    'province' => ['name' => 'ชื่อจังหวัด', 'abbr' => 'ชื่อย่อจังหวัด', 'code' => 'รหัสจังหวัด'],
    'office' => ['name' => 'ชื่อส่วนราชการ', 'abbr' => 'ชื่อย่อส่วนราชการ', 'code' => 'รหัสส่วนราชการ']
];
$labels = $labelMap[$organization['org_type']] ?? ['name' => 'ชื่อหน่วยงาน', 'abbr' => 'ชื่อย่อ', 'code' => 'รหัสหน่วยงาน'];
?>
<!-- Modal Header -->
<div class="flex items-center justify-between mb-6 pb-4 border-b border-slate-700">
    <h3 class="text-xl font-bold text-white flex items-center gap-2">
        <i data-lucide="building-2" class="w-5 h-5 text-primary-500"></i>
        ข้อมูลรายละเอียดหน่วยงาน
    </h3>
    <button type="button" onclick="closeViewModal()" class="text-slate-400 hover:text-white transition-colors">
        <i data-lucide="x" class="w-6 h-6"></i>
    </button>
</div>

<!-- Content -->
<div class="space-y-6">
    
    <!-- Basic Info Section -->
    <div class="bg-slate-800/30 p-6 rounded-lg border border-slate-700">
        <h5 class="text-lg font-semibold text-slate-200 mb-4 border-b border-slate-600 pb-2">ข้อมูลทั่วไป</h5>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1"><?= $labels['code'] ?></label>
                <div class="text-slate-200 font-mono bg-slate-700/30 px-3 py-2 rounded border border-slate-600/50">
                    <?= htmlspecialchars($organization['code']) ?>
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1"><?= $labels['abbr'] ?></label>
                <div class="text-slate-200 bg-slate-700/30 px-3 py-2 rounded border border-slate-600/50">
                    <?= htmlspecialchars($organization['abbreviation'] ?: '-') ?>
                </div>
            </div>
        </div>

        <div class="mb-4">
            <label class="block text-xs font-medium text-slate-400 mb-1"><?= $labels['name'] ?></label>
            <div class="text-slate-100 font-medium text-lg">
                <?= htmlspecialchars($organization['name_th']) ?>
            </div>
        </div>
    </div>

    <!-- Structure Info -->
    <div class="bg-slate-800/30 p-6 rounded-lg border border-slate-700">
        <h5 class="text-lg font-semibold text-slate-200 mb-4 border-b border-slate-600 pb-2">โครงสร้างองค์กร</h5>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">ประเภทหน่วยงาน</label>
                <div class="text-blue-400 font-medium">
                    <?= $typeLabel ?>
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">สังกัด (Parent)</label>
                <div class="text-slate-200">
                    <?php if ($parent): ?>
                        <span class="text-slate-300"><?= htmlspecialchars($parent['name_th']) ?></span>
                    <?php else: ?>
                        <span class="text-slate-500 italic">- ไม่มี (ระดับสูงสุด) -</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">ส่วน/ภาค</label>
                <div class="text-slate-200"><?= $regionLabel ?></div>
            </div>
            
            <?php if (!empty($organization['province_code'])): ?>
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">รหัสจังหวัด</label>
                <div class="text-slate-200"><?= htmlspecialchars($organization['province_code']) ?></div>
            </div>
            <?php endif; ?>

            <?php if (!empty($organization['provincial_group'])): ?>
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">กลุ่มจังหวัด</label>
                <div class="text-emerald-400"><?= htmlspecialchars($organization['provincial_group']) ?></div>
            </div>
            <?php endif; ?>

            <?php if (!empty($organization['provincial_zone'])): ?>
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">เขตจังหวัด</label>
                <div class="text-slate-200"><?= htmlspecialchars($organization['provincial_zone']) ?></div>
            </div>
            <?php endif; ?>

            <?php if (!empty($organization['inspection_zone'])): ?>
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">เขตตรวจราชการ</label>
                <div class="text-slate-200"><?= htmlspecialchars($organization['inspection_zone']) ?></div>
            </div>
            <?php endif; ?>

            <?php if (!empty($organization['custom_zone'])): ?>
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">เขตกำหนดเอง</label>
                <div class="text-slate-200"><?= htmlspecialchars($organization['custom_zone']) ?></div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Contact Info -->
    <div class="bg-slate-800/30 p-6 rounded-lg border border-slate-700">
        <h5 class="text-lg font-semibold text-slate-200 mb-4 border-b border-slate-600 pb-2">ข้อมูลติดต่อ</h5>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">เบอร์โทรศัพท์</label>
                <div class="text-slate-200 flex items-center gap-2">
                    <i data-lucide="phone" class="w-4 h-4 text-slate-500"></i>
                    <?= htmlspecialchars($organization['contact_phone'] ?: '-') ?>
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">อีเมล</label>
                <div class="text-slate-200 flex items-center gap-2">
                    <i data-lucide="mail" class="w-4 h-4 text-slate-500"></i>
                    <?= htmlspecialchars($organization['contact_email'] ?: '-') ?>
                </div>
            </div>
        </div>
        
        <?php if (!empty($organization['address'])): ?>
        <div>
            <label class="block text-xs font-medium text-slate-400 mb-1">ที่อยู่</label>
            <div class="text-slate-300 text-sm leading-relaxed p-3 bg-slate-700/20 rounded border border-slate-700/50">
                <?= nl2br(htmlspecialchars($organization['address'])) ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Status -->
    <div class="flex items-center justify-between pt-4 border-t border-slate-700">
        <div>
            <span class="text-xs text-slate-400 mr-2">สถานะ:</span>
            <?php if ($organization['is_active']): ?>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    <span class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1.5"></span>
                    ใช้งานปกติ
                </span>
            <?php else: ?>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                    <span class="w-1.5 h-1.5 bg-red-500 rounded-full mr-1.5"></span>
                    ปิดใช้งาน
                </span>
            <?php endif; ?>
        </div>
        
        <button type="button" onclick="closeViewModal()" class="px-5 py-2 rounded-lg bg-slate-700 hover:bg-slate-600 text-white font-medium transition-colors">
            ปิดหน้าต่าง
        </button>
    </div>

</div>

<script>
    if(typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
</script>
