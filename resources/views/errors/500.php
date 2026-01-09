<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 Internal Server Error</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Noto Sans Thai', sans-serif; }
    </style>
</head>
<body class="bg-slate-900 text-slate-200 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-lg w-full bg-slate-800 rounded-2xl p-8 shadow-2xl border border-slate-700 text-center">
        <div class="w-20 h-20 bg-red-500/10 rounded-full flex items-center justify-center mx-auto mb-6">
            <i data-lucide="alert-octagon" class="w-10 h-10 text-red-500"></i>
        </div>
        
        <h1 class="text-3xl font-bold text-white mb-2">500 Server Error</h1>
        <p class="text-slate-400 mb-8">เกิดข้อผิดพลาดภายในระบบ กรุณาลองใหม่อีกครั้ง หรือติดต่อผู้ดูแลระบบ</p>
        
        <?php if (!empty($exception) && ($_ENV['APP_DEBUG'] ?? false) === 'true'): ?>
        <div class="text-left bg-slate-900 p-4 rounded-lg mb-8 overflow-auto max-h-60 border border-slate-700">
            <p class="text-red-400 font-mono text-sm font-bold mb-2"><?= get_class($exception) ?>: <?= $exception->getMessage() ?></p>
            <pre class="text-xs text-slate-500 font-mono"><?= $exception->getTraceAsString() ?></pre>
        </div>
        <?php endif; ?>

        <div class="flex justify-center gap-4">
            <button onclick="history.back()" class="px-6 py-2.5 rounded-xl border border-slate-600 hover:bg-slate-700 transition-colors text-slate-300">
                ย้อนกลับ
            </button>
            <a href="<?= \App\Core\View::url('/') ?>" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 transition-colors text-white font-medium">
                กลับสู่หน้าหลัก
            </a>
        </div>
    </div>
    </div>
    <script>
        lucide.createIcons();
    </script>
</body>
</html>
