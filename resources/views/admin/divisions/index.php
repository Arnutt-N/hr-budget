<?php
/**
 * Division List View
 */
?>

<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">จัดการหน่วยงาน</h1>
        <a href="/admin/divisions/create" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            + เพิ่มหน่วยงานใหม่
        </a>
    </div>

    <!-- Flash Messages -->
    <?php if (!empty($_SESSION['flash_success'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?= htmlspecialchars($_SESSION['flash_success']) ?>
        </div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?= htmlspecialchars($_SESSION['flash_error']) ?>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <!-- Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">รหัส</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ชื่อหน่วยงาน</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ชื่อย่อ</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ประเภท</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">จัดการ</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php foreach ($divisions as $division): ?>
                <tr>
                    <td class="px-6 py-4"><?= htmlspecialchars($division['code']) ?></td>
                    <td class="px-6 py-4"><?= htmlspecialchars($division['name_th']) ?></td>
                    <td class="px-6 py-4"><?= htmlspecialchars($division['short_name'] ?? '-') ?></td>
                    <td class="px-6 py-4">
                        <?php
                        $types = [
                            'central' => 'ส่วนกลาง',
                            'regional' => 'ภูมิภาค',
                            'provincial' => 'จังหวัด'
                        ];
                        echo $types[$division['type']] ?? $division['type'];
                        ?>
                    </td>
                    <td class="px-6 py-4">
                        <a href="/admin/divisions/<?= $division['id'] ?>/edit" class="text-blue-600 hover:text-blue-800 mr-3">แก้ไข</a>
                        <form method="POST" action="/admin/divisions/<?= $division['id'] ?>/delete" class="inline" onsubmit="return confirm('ยืนยันการลบ?')">
                            <button type="submit" class="text-red-600 hover:text-red-800">ลบ</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                
                <?php if (empty($divisions)): ?>
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">ไม่มีข้อมูล</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
