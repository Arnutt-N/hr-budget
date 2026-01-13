<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'HR Budget System') ?> - HR Budget</title>

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
                            400: '#38bdf8',
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
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    
    <!-- Custom Styles -->
    <style>
        body { font-family: 'Noto Sans Thai', sans-serif; background-color: #0f172a; color: #f1f5f9; }
        .input { background-color: #1e293b; border: 1px solid #334155; border-radius: 0.5rem; padding: 0.625rem 1rem; color: white; font-size: 0.875rem; transition: border-color 0.2s ease; }
        .input:focus { outline: none; border-color: #0ea5e9; }
        .input-icon { padding-left: 2.5rem; }
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.625rem 1rem; border-radius: 0.5rem; font-weight: 500; font-size: 0.875rem; transition: all 0.2s ease; cursor: pointer; }
        .btn-primary { background-color: #0284c7; color: white; box-shadow: 0 4px 14px rgba(14, 165, 233, 0.25); }
        .btn-primary:hover { background-color: #0ea5e9; }
        .btn-secondary { background-color: #334155; color: #f1f5f9; }
        .btn-secondary:hover { background-color: #475569; }
        .btn-sm { padding: 0.375rem 0.75rem; font-size: 0.75rem; }
        .badge { display: inline-flex; align-items: center; padding: 0.25rem 0.625rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500; }
        .badge-blue { background-color: rgba(14, 165, 233, 0.1); color: #38bdf8; }
        .badge-green { background-color: rgba(34, 197, 94, 0.1); color: #4ade80; }
        .badge-orange { background-color: rgba(251, 146, 60, 0.1); color: #fb923c; }
        .badge-red { background-color: rgba(239, 68, 68, 0.1); color: #f87171; }
        .table { width: 100%; }
        .table th, .table td { padding: 0.75rem 1rem; text-align: left; border-bottom: 1px solid #334155; }
        .table th { background-color: #1e293b; color: #94a3b8; font-weight: 600; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; }
        .table td { color: #f1f5f9; }
        .progress { height: 0.5rem; background-color: #334155; border-radius: 9999px; overflow: hidden; }
        .progress-bar { height: 100%; border-radius: 9999px; transition: width 0.3s ease; }
        .nav-link { display: flex; align-items: center; padding: 0.625rem 0.75rem; border-radius: 0.5rem; color: #94a3b8; transition: all 0.2s ease; }
        .nav-link:hover { background-color: rgba(51, 65, 85, 0.5); color: #f1f5f9; }
        .nav-link.active { background-color: rgba(14, 165, 233, 0.1); color: #0ea5e9; }
        .card-hover { transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .card-hover:hover { transform: translateY(-2px); box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3); }
        .animate-fade-in { animation: fadeIn 0.3s ease forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        /* Custom Scrollbar for Dark Theme */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #1e293b; }
        ::-webkit-scrollbar-thumb { background: #475569; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #64748b; }
        * { scrollbar-width: thin; scrollbar-color: #475569 #1e293b; }

        /* Icon Glassmorphism */
        .icon-glass {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(4px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 0.5rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.5rem;
            height: 2.5rem;
            transition: all 0.3s ease;
        }
        .icon-glass:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.2);
            box-shadow: 0 0 15px rgba(14, 165, 233, 0.2);
        }
        .nav-icon { width: 1.25rem; height: 1.25rem; }

        /* Icon Button Styles */
        .btn-icon {
            width: 2rem;
            height: 2rem;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.375rem;
            transition: all 0.2s ease;
        }

        .btn-ghost-primary {
            color: #94a3b8;
            background: transparent;
        }
        .btn-ghost-primary:hover {
            color: #0ea5e9;
            background-color: rgba(14, 165, 233, 0.1);
        }

        .btn-ghost-warning {
            color: #eab308;
            background: transparent;
        }
        .btn-ghost-warning:hover {
            color: #facc15;
            background-color: rgba(234, 179, 8, 0.1);
        }

        /* Number Tooltip */
        .number-tooltip {
            position: relative;
            cursor: default;
        }
        .number-tooltip::after {
            content: attr(data-full-value);
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            padding: 0.375rem 0.75rem;
            background: #1e1e2d;
            border: 1px solid #4b5563;
            border-radius: 0.375rem;
            color: white;
            font-size: 0.75rem;
            white-space: nowrap;
            opacity: 0;
            visibility: hidden;
            transition: all 0.15s ease;
            z-index: 50;
            pointer-events: none;
            margin-bottom: 0.5rem;
        }
        .number-tooltip:hover::after {
            opacity: 1;
            visibility: visible;
        }

        /* Modal Animations */
        @keyframes modalFadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes modalScaleIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
        @keyframes modalFadeOut { from { opacity: 1; } to { opacity: 0; } }
        @keyframes modalScaleOut { from { opacity: 1; transform: scale(1); } to { opacity: 0; transform: scale(0.95); } }
        
        .modal-overlay.hidden { display: none; }
        .modal-overlay.show .modal-backdrop { animation: modalFadeIn 0.2s ease-out forwards; }
        .modal-overlay.show .modal-card { animation: modalScaleIn 0.2s ease-out forwards; }
        .modal-overlay.hide .modal-backdrop { animation: modalFadeOut 0.2s ease-in forwards; }
        .modal-overlay.hide .modal-card { animation: modalScaleOut 0.2s ease-in forwards; }
        .modal-overlay { position: fixed; inset: 0; z-index: 9999; }
    </style>
</head>
<body class="antialiased min-h-screen flex">
    
    <!-- Mobile Overlay -->
    <div id="mobile-overlay" class="fixed inset-0 bg-black/50 z-20 lg:hidden hidden"></div>

    <!-- Sidebar -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-30 flex flex-col bg-dark-card border-r border-dark-border transition-all duration-300 w-64 overflow-hidden">
        <!-- Logo -->
        <div class="h-16 flex items-center px-6 border-b border-dark-border">
            <i data-lucide="landmark" class="w-8 h-8 text-primary-500"></i>
            <span class="ml-3 font-bold text-lg whitespace-nowrap nav-text">HR Budget</span>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
            <a href="<?= \App\Core\View::url('/') ?>" class="nav-link <?= ($currentPage ?? '') === 'dashboard' ? 'active' : '' ?>">
                <i data-lucide="layout-dashboard" class="nav-icon"></i>
                <span class="ml-3 nav-text">ภาพรวม (Dashboard)</span>
            </a>
            
            <a href="<?= \App\Core\View::url('/budgets') ?>" class="nav-link <?= str_starts_with($currentPage ?? '', 'execution') ? 'active' : '' ?>">
                <i data-lucide="trending-up" class="nav-icon"></i>
                <span class="ml-3 nav-text">ผลการเบิกจ่าย</span>
            </a>

            <a href="<?= \App\Core\View::url('/budgets/list') ?>" class="nav-link <?= $currentPage == 'budgets' ? 'active' : '' ?>">
                <i data-lucide="list" class="nav-icon"></i>
                <span class="ml-3 nav-text">รายการเบิกจ่าย</span>
            </a>
            
            <a href="<?= \App\Core\View::url('/requests') ?>" class="nav-link <?= ($currentPage ?? '') === 'requests' ? 'active' : '' ?>">
                <i data-lucide="file-text" class="nav-icon"></i>
                <span class="ml-3 nav-text">คำขอประมาณ</span>
            </a>
            
            <!-- Reports Section -->
            <div class="pt-4 mt-4 border-t border-dark-border">
                <div class="px-3 mb-2 text-xs font-semibold text-dark-muted uppercase tracking-wider nav-text">
                    รายงาน
                </div>
                <a href="<?= \App\Core\View::url('/reports/disbursement') ?>" class="nav-link <?= ($currentPage ?? '') === 'reports-disbursement' ? 'active' : '' ?>">
                    <i data-lucide="line-chart" class="nav-icon"></i>
                    <span class="ml-3 nav-text">รายงานเบิกจ่าย</span>
                </a>
                <a href="<?= \App\Core\View::url('/reports/requests') ?>" class="nav-link <?= ($currentPage ?? '') === 'reports-requests' ? 'active' : '' ?>">
                    <i data-lucide="file-text" class="nav-icon"></i>
                    <span class="ml-3 nav-text">รายงานคำขอ</span>
                </a>
            </div>
            
            <!-- Management Section -->
            <div class="pt-4 mt-4 border-t border-dark-border">
                <div class="px-3 mb-2 text-xs font-semibold text-dark-muted uppercase tracking-wider nav-text">
                    จัดการ
                </div>
                
                <!-- Organizations -->
                <a href="<?= \App\Core\View::url('/admin/organizations') ?>" class="nav-link <?= ($currentPage ?? '') === 'admin-organizations' ? 'active' : '' ?>">
                    <i data-lucide="building-2" class="nav-icon"></i>
                    <span class="ml-3 nav-text">หน่วยงาน</span>
                </a>
                
                <!-- Budget Plans -->
                <a href="<?= \App\Core\View::url('/admin/plans') ?>" class="nav-link <?= ($currentPage ?? '') === 'admin-plans' ? 'active' : '' ?>">
                    <i data-lucide="network" class="nav-icon"></i>
                    <span class="ml-3 nav-text">แผนงาน/ผลผลิต</span>
                </a>
                
                <!-- Budget Categories -->
                <a href="<?= \App\Core\View::url('/admin/categories') ?>" class="nav-link <?= ($currentPage ?? '') === 'admin-categories' ? 'active' : '' ?>">
                    <i data-lucide="list" class="nav-icon"></i>
                    <span class="ml-3 nav-text">ประเภทรายจ่าย</span>
                </a>
                
                <!-- Target Types -->
                <a href="<?= \App\Core\View::url('/admin/target-types') ?>" class="nav-link <?= ($currentPage ?? '') === 'admin-target-types' ? 'active' : '' ?>">
                    <i data-lucide="target" class="nav-icon"></i>
                    <span class="ml-3 nav-text">ประเภทเป้าหมาย</span>
                </a>
                
                <a href="<?= \App\Core\View::url('/files') ?>" class="nav-link <?= ($currentPage ?? '') === 'files' ? 'active' : '' ?>">
                    <i data-lucide="folder-open" class="nav-icon"></i>
                    <span class="ml-3 nav-text">จัดการไฟล์</span>
                </a>
                
                <?php if (\App\Core\Auth::can('users.manage')): ?>
                <a href="<?= \App\Core\View::url('/admin/users') ?>" class="nav-link <?= ($currentPage ?? '') === 'admin-users' ? 'active' : '' ?>">
                    <i data-lucide="users" class="nav-icon"></i>
                    <span class="ml-3 nav-text">จัดการผู้ใช้</span>
                </a>
                <?php endif; ?>
            </div>
        </nav>

        <!-- User & Logout -->
        <div class="p-4 border-t border-dark-border">
            <a href="<?= \App\Core\View::url('/profile') ?>" class="nav-link mb-2">
                <i data-lucide="user-circle" class="nav-icon"></i>
                <span class="ml-3 nav-text">โปรไฟล์</span>
            </a>
            <a href="<?= \App\Core\View::url('/logout') ?>" class="nav-link text-red-400 hover:text-red-300 hover:bg-red-900/20">
                <i data-lucide="log-out" class="nav-icon"></i>
                <span class="ml-3 nav-text">ออกจากระบบ</span>
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <div id="main-content" class="flex-1 flex flex-col transition-all duration-300 ml-0 lg:ml-64">
        <!-- Topbar -->
        <header class="h-16 bg-dark-card/80 backdrop-blur border-b border-dark-border flex items-center justify-between px-6 sticky top-0 z-20">
            <div class="flex items-center">
                <button id="sidebar-toggle" class="text-dark-muted hover:text-white p-2 rounded-lg hover:bg-slate-800 mr-4">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
                <h2 class="text-lg font-semibold text-white hidden sm:block">
                    <?= htmlspecialchars($title ?? 'Dashboard') ?>
                </h2>
            </div>
            
            <div class="flex items-center gap-4">
                <!-- Fiscal Year Badge -->
                <span class="badge badge-blue">
                    <i data-lucide="calendar" class="w-4 h-4 mr-1"></i>
                    ปี <?= $fiscalYear ?? 2568 ?>
                </span>
                
                <!-- User Info -->
                <div class="flex items-center gap-3">
                    <div class="text-right hidden sm:block">
                        <div class="text-sm font-medium text-white"><?= htmlspecialchars($auth['name'] ?? 'User') ?></div>
                        <div class="text-xs text-dark-muted"><?= htmlspecialchars($auth['role'] ?? 'Viewer') ?></div>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-primary-600 flex items-center justify-center text-white font-medium">
                        <?= strtoupper(substr($auth['name'] ?? 'U', 0, 1)) ?>
                    </div>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main class="flex-1 overflow-y-auto bg-dark-bg p-6">
            <?= \App\Core\View::yield('content') ?>
        </main>
    </div>

    <!-- Toast Container -->
    <div id="toast-container"></div>
    
    <!-- Custom Modal Component -->
    <div id="customModal" class="modal-overlay hidden">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm transition-opacity modal-backdrop"></div>
        
        <!-- Modal Card -->
        <div class="fixed inset-0 overflow-y-auto z-50">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="modal-card relative bg-slate-800/95 backdrop-blur-md rounded-2xl shadow-2xl border border-slate-700/50 max-w-md w-full transform transition-all p-0 overflow-hidden">
                    
                    <!-- Header -->
                    <div class="flex items-start gap-4 p-6 pb-4">
                        <div class="flex-shrink-0">
                            <div id="modal-icon-container" class="w-12 h-12 rounded-full bg-blue-500/10 flex items-center justify-center">
                                <div id="modal-icon"><i data-lucide="check-circle" class="w-6 h-6 text-blue-400"></i></div>
                            </div>
                        </div>
                        <div class="flex-1">
                            <h3 id="modal-title" class="text-lg font-semibold text-slate-100">ยืนยันรายการ?</h3>
                            <p id="modal-message" class="text-sm text-slate-400 mt-1">กรุณาตรวจสอบข้อมูลก่อนยืนยัน</p>
                        </div>
                        <button type="button" class="modal-close text-slate-400 hover:text-slate-200 transition-colors">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="px-6 py-4">
                        <div class="bg-slate-900/50 rounded-lg p-4 border border-slate-700/30">
                            <div class="flex justify-between items-center">
                                <span class="text-slate-400">วงเงินรวมทั้งสิ้น:</span>
                                <span class="text-2xl font-bold text-amber-400" id="modal-total">0.00</span>
                            </div>
                            <div class="text-xs text-slate-500 mt-1 text-right">บาท</div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="flex gap-3 px-6 py-4 bg-slate-900/30 border-t border-white/5">
                        <button type="button" class="modal-cancel-btn flex-1 px-4 py-2.5 bg-slate-700 hover:bg-slate-600 text-slate-200 rounded-lg font-medium transition-colors border border-slate-600">
                            ยกเลิก
                        </button>
                        <button type="button" class="modal-confirm-btn flex-1 px-4 py-2.5 bg-blue-600 hover:bg-blue-500 text-white rounded-lg font-medium transition-colors shadow-lg shadow-blue-900/30">
                            ยืนยัน
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Flash Messages -->
    <?php if (isset($_SESSION['flash_success'])): ?>
        <div id="flash-success" data-message="<?= htmlspecialchars($_SESSION['flash_success']) ?>"></div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['flash_error'])): ?>
        <div id="flash-error" data-message="<?= htmlspecialchars($_SESSION['flash_error']) ?>"></div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <script src="<?= BASE_URL ?>/js/thai-datepicker.js"></script>
    <!-- Custom Modal Script -->
    <script src="<?= BASE_URL ?>/js/modal.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Initialize Lucide Icons
            lucide.createIcons();
            
            if(typeof ThaiDatepicker !== 'undefined') ThaiDatepicker.init();
        });
    </script>
</body>
</html>
