<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'เข้าสู่ระบบ') ?> - HR Budget</title>

    <!-- Google Fonts: Noto Sans Thai -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS (CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Noto Sans Thai', 'sans-serif'],
                    },
                    colors: {
                        'dark-bg': '#0f172a',
                        'dark-card': '#1e293b',
                        'dark-text': '#f1f5f9',
                        'dark-muted': '#94a3b8',
                        'dark-border': '#334155',
                        'primary': {
                            500: '#0ea5e9',
                            600: '#0284c7',
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Custom Styles -->
    <style>
        body { font-family: 'Noto Sans Thai', sans-serif; }
        .input { background-color: #1e293b; border: 1px solid #334155; border-radius: 0.5rem; padding: 0.625rem 1rem; color: white; font-size: 0.875rem; transition: border-color 0.2s ease; }
        .input:focus { outline: none; border-color: #0ea5e9; }
        .input-icon { padding-left: 2.5rem; }
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.625rem 1rem; border-radius: 0.5rem; font-weight: 500; font-size: 0.875rem; transition: all 0.2s ease; cursor: pointer; }
        .btn-primary { background-color: #0284c7; color: white; box-shadow: 0 4px 14px rgba(14, 165, 233, 0.25); }
        .btn-primary:hover { background-color: #0ea5e9; }
        .animate-fade-in { animation: fadeIn 0.3s ease forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body class="antialiased min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-900 to-slate-800 p-4">
    
    <?= \App\Core\View::yield('content') ?>
    
    <!-- Toast Container -->
    <div id="toast-container"></div>
    
    <!-- Flash Messages -->
    <?php if (isset($_SESSION['flash_success'])): ?>
        <div id="flash-success" data-message="<?= htmlspecialchars($_SESSION['flash_success']) ?>"></div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['flash_error'])): ?>
        <div id="flash-error" data-message="<?= htmlspecialchars($_SESSION['flash_error']) ?>"></div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
