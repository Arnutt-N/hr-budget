<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>505 - HTTP Version Not Supported</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@400;500;600&display=swap" rel="stylesheet">
    <style>body { font-family: 'Noto Sans Thai', sans-serif; }</style>
</head>
<body class="bg-slate-900 text-white min-h-screen flex items-center justify-center">
    <div class="text-center p-8">
        <div class="text-8xl font-bold text-slate-500 mb-4">505</div>
        <h1 class="text-2xl font-semibold mb-2">HTTP Version Not Supported</h1>
        <p class="text-slate-400 mb-6">เซิร์ฟเวอร์ไม่รองรับเวอร์ชันของ HTTP protocol ที่ใช้งาน</p>
        <a href="<?= \App\Core\View::url('/') ?>" class="inline-flex items-center gap-2 bg-sky-600 hover:bg-sky-500 px-6 py-2 rounded-lg transition-colors">
            <span>กลับหน้าหลัก</span>
        </a>
    </div>
</body>
</html>
