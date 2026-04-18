<?php
/**
 * Server Diagnostic Script — HR Budget PRD Phase 1 prerequisite
 *
 * วิธีใช้:
 *   1. Upload ไฟล์นี้ไปที่ topzlab.com (ในโฟลเดอร์ public_html/ หรือ root)
 *   2. เปิด URL: https://<your-domain>/server-check.php?token=hrbudget2026
 *   3. Copy output กลับมาให้ดู
 *   4. **ลบไฟล์นี้ทันที** หลังใช้งานเสร็จ (รั่ว config = security risk)
 *
 * Token ใน URL กันคนนอกเปิดไฟล์นี้ (แค่นิดหน่อย — ควรลบออกเร็ว)
 */

// ===== Security: basic token + error suppress =====
$TOKEN = 'hrbudget2026';
if (!isset($_GET['token']) || $_GET['token'] !== $TOKEN) {
    http_response_code(404);
    echo '404 Not Found';
    exit;
}

// ===== Collect diagnostics =====
$diagnostics = [];

// --- PHP core info ---
$diagnostics['php'] = [
    'version' => PHP_VERSION,
    'sapi' => PHP_SAPI,
    'os' => PHP_OS,
    'meets_8_1_minimum' => version_compare(PHP_VERSION, '8.1.0', '>='),
    'meets_8_3_recommended' => version_compare(PHP_VERSION, '8.3.0', '>='),
];

// --- Extensions required for HR Budget REST API ---
$required_extensions = [
    'pdo'          => 'Database access core',
    'pdo_mysql'    => 'MySQL driver for PDO',
    'json'         => 'JSON encode/decode',
    'mbstring'     => 'UTF-8 string handling (Thai text)',
    'openssl'      => 'JWT signing + HTTPS',
    'curl'         => 'Outbound HTTP (future integrations)',
    'fileinfo'     => 'File upload MIME detection',
    'zip'          => 'Excel export via PhpSpreadsheet',
    'gd'           => 'Image handling (file upload thumbnails)',
    'intl'         => 'Thai locale, date formatting',
];
$diagnostics['extensions'] = [];
foreach ($required_extensions as $ext => $why) {
    $diagnostics['extensions'][$ext] = [
        'loaded' => extension_loaded($ext),
        'purpose' => $why,
    ];
}

// --- PHP.ini limits ---
$diagnostics['limits'] = [
    'memory_limit'          => ini_get('memory_limit'),
    'max_execution_time'    => ini_get('max_execution_time') . 's',
    'upload_max_filesize'   => ini_get('upload_max_filesize'),
    'post_max_size'         => ini_get('post_max_size'),
    'max_input_vars'        => ini_get('max_input_vars'),
    'default_socket_timeout' => ini_get('default_socket_timeout') . 's',
    'session.gc_maxlifetime' => ini_get('session.gc_maxlifetime') . 's',
    'date.timezone'         => ini_get('date.timezone') ?: '(empty)',
    'display_errors'        => ini_get('display_errors'),
    'log_errors'            => ini_get('log_errors'),
];

// --- MySQL version (try to connect with common credentials) ---
$diagnostics['mysql'] = ['check' => 'SKIPPED — populate credentials manually if needed'];
// Uncomment + fill in to test DB connection:
// try {
//     $pdo = new PDO('mysql:host=localhost;dbname=information_schema;charset=utf8mb4', 'DB_USER', 'DB_PASS', [
//         PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
//     ]);
//     $diagnostics['mysql']['version'] = $pdo->query('SELECT VERSION()')->fetchColumn();
//     $diagnostics['mysql']['charset'] = $pdo->query("SHOW VARIABLES LIKE 'character_set_server'")->fetch(PDO::FETCH_ASSOC);
//     $diagnostics['mysql']['collation'] = $pdo->query("SHOW VARIABLES LIKE 'collation_server'")->fetch(PDO::FETCH_ASSOC);
// } catch (Throwable $e) {
//     $diagnostics['mysql']['error'] = $e->getMessage();
// }

// --- Writable paths ---
$paths_to_check = [
    __DIR__ . '/',
    sys_get_temp_dir(),
    ini_get('upload_tmp_dir') ?: sys_get_temp_dir(),
];
$diagnostics['filesystem'] = [];
foreach ($paths_to_check as $path) {
    $diagnostics['filesystem'][$path] = [
        'exists' => file_exists($path),
        'writable' => is_writable($path),
    ];
}

// --- Composer/opcache ---
$diagnostics['runtime'] = [
    'opcache_enabled' => function_exists('opcache_get_status') && opcache_get_status(false) !== false,
    'composer_autoload_likely' => file_exists(__DIR__ . '/vendor/autoload.php'),
    'zend_version' => zend_version(),
];

// --- .htaccess / mod_rewrite detection ---
$diagnostics['apache'] = [
    'mod_rewrite' => in_array('mod_rewrite', apache_get_modules() ?: [], true),
    'htaccess_honored' => '(see test below)',
    'notes' => 'On shared hosting, .htaccess is usually allowed — verify by testing rewrite rule later',
];

// --- Server software ---
$diagnostics['server'] = [
    'software' => $_SERVER['SERVER_SOFTWARE'] ?? '?',
    'https' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'yes' : 'no',
    'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? '?',
    'script_path' => __FILE__,
];

// --- Compatibility summary ---
$checklist = [];
$checklist['PHP >= 8.1']     = $diagnostics['php']['meets_8_1_minimum'];
$checklist['PHP >= 8.3']     = $diagnostics['php']['meets_8_3_recommended'];
$checklist['PDO + MySQL']    = $diagnostics['extensions']['pdo']['loaded'] && $diagnostics['extensions']['pdo_mysql']['loaded'];
$checklist['JSON']           = $diagnostics['extensions']['json']['loaded'];
$checklist['OpenSSL (JWT)']  = $diagnostics['extensions']['openssl']['loaded'];
$checklist['mbstring']       = $diagnostics['extensions']['mbstring']['loaded'];
$checklist['zip (Excel)']    = $diagnostics['extensions']['zip']['loaded'];
$checklist['fileinfo']       = $diagnostics['extensions']['fileinfo']['loaded'];
$checklist['HTTPS']          = !empty($_SERVER['HTTPS']);

$passed = count(array_filter($checklist));
$total = count($checklist);
$diagnostics['_compatibility_summary'] = [
    'passed' => "{$passed} / {$total}",
    'ready_for_phase_1' => $passed >= ($total - 1),
    'checklist' => $checklist,
];

// ===== Render =====
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>HR Budget — Server Check</title>
    <style>
        body { font: 14px/1.5 -apple-system,Segoe UI,sans-serif; max-width: 900px; margin: 20px auto; padding: 0 20px; color: #222; }
        h1 { border-bottom: 3px solid #4F46E5; padding-bottom: 8px; }
        h2 { background: #EEF2FF; padding: 8px 12px; border-left: 4px solid #4F46E5; margin-top: 24px; }
        pre { background: #F3F4F6; padding: 12px; border-radius: 6px; overflow-x: auto; font-size: 12px; }
        .ok  { color: #059669; font-weight: 600; }
        .no  { color: #DC2626; font-weight: 600; }
        .warn { color: #D97706; font-weight: 600; }
        table { border-collapse: collapse; width: 100%; margin: 10px 0; }
        th, td { border: 1px solid #E5E7EB; padding: 6px 10px; text-align: left; font-size: 13px; }
        th { background: #F9FAFB; }
        .banner { padding: 16px; border-radius: 8px; margin: 16px 0; }
        .banner-ok { background: #D1FAE5; border: 1px solid #10B981; }
        .banner-no { background: #FEE2E2; border: 1px solid #EF4444; }
        .copy-btn { background: #4F46E5; color: white; border: 0; padding: 8px 16px; border-radius: 6px; cursor: pointer; }
    </style>
</head>
<body>

<h1>🏥 HR Budget — Server Diagnostic</h1>
<p>เครื่อง <code><?= htmlspecialchars($_SERVER['SERVER_NAME'] ?? '?') ?></code> @ <?= date('Y-m-d H:i:s') ?></p>

<?php
$ready = $diagnostics['_compatibility_summary']['ready_for_phase_1'];
if ($ready): ?>
<div class="banner banner-ok">
    <strong>✅ พร้อมใช้งาน Phase 1</strong> — ผ่าน <?= $diagnostics['_compatibility_summary']['passed'] ?> ข้อ
</div>
<?php else: ?>
<div class="banner banner-no">
    <strong>❌ ยังไม่พร้อม</strong> — ผ่าน <?= $diagnostics['_compatibility_summary']['passed'] ?> ข้อ (ต้องแก้ก่อนเริ่ม)
</div>
<?php endif; ?>

<h2>🎯 Compatibility Checklist</h2>
<table>
    <tr><th>Requirement</th><th>Status</th></tr>
    <?php foreach ($checklist as $req => $pass): ?>
    <tr>
        <td><?= $req ?></td>
        <td class="<?= $pass ? 'ok' : 'no' ?>"><?= $pass ? '✓ OK' : '✗ FAIL' ?></td>
    </tr>
    <?php endforeach; ?>
</table>

<h2>📋 Raw Diagnostics (copy ให้ผม)</h2>
<button class="copy-btn" onclick="copyJson()">📋 Copy JSON</button>
<pre id="json"><?= htmlspecialchars(json_encode($diagnostics, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?></pre>

<h2>🔐 Cleanup (สำคัญ!)</h2>
<p class="warn">
    ⚠️ <strong>ลบไฟล์นี้ทันทีหลังใช้งาน</strong> ผ่าน FTP หรือ File Manager —
    ไฟล์นี้เปิดเผยข้อมูล server ถ้าเอาไว้ยาวนาน attacker เดา URL ได้จะเห็น config
</p>

<script>
function copyJson() {
    const text = document.getElementById('json').innerText;
    navigator.clipboard.writeText(text).then(() => alert('Copied! ส่งกลับให้ Claude'));
}
</script>

</body>
</html>
