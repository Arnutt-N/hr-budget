<div class="w-full max-w-md bg-dark-card border border-dark-border rounded-2xl shadow-2xl p-8 animate-fade-in">
    <!-- Header -->
    <div class="text-center mb-8">
        <div class="w-16 h-16 bg-primary-600 rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg shadow-primary-500/20">
            <i data-lucide="landmark" class="w-8 h-8 text-white"></i>
        </div>
        <h1 class="text-2xl font-bold text-white mb-2">ระบบบริหารงบประมาณบุคลากร</h1>
        <p class="text-dark-muted text-sm">กระทรวงยุติธรรม (ปีงบประมาณ 2568)</p>
    </div>

    <!-- Login Form -->
    <form action="<?= \App\Core\View::url('/login') ?>" method="POST" class="space-y-6" data-validate>
        <?= \App\Core\View::csrf() ?>
        
        <div>
            <label class="block text-sm font-medium text-dark-muted mb-1">อีเมล (@moj.go.th)</label>
            <div class="relative">
                <span class="absolute left-3 top-2.5 text-dark-muted">
                    <i data-lucide="mail" class="w-5 h-5"></i>
                </span>
                <input 
                    type="email" 
                    name="email" 
                    class="input input-icon w-full" 
                    placeholder="name@moj.go.th"
                    value="<?= htmlspecialchars($_POST['email'] ?? 'admin@moj.go.th') ?>"
                    required
                >
            </div>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-dark-muted mb-1">รหัสผ่าน</label>
            <div class="relative">
                <span class="absolute left-3 top-2.5 text-dark-muted">
                    <i data-lucide="lock" class="w-5 h-5"></i>
                </span>
                <input 
                    type="password" 
                    name="password" 
                    class="input input-icon w-full" 
                    placeholder="••••••••"
                    value="admin123"
                    required
                >
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-full">
            เข้าสู่ระบบ
        </button>
    </form>

    <!-- Divider -->
    <div class="mt-6">
        <div class="relative">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-dark-border"></div>
            </div>
            <div class="relative flex justify-center text-sm">
                <span class="px-2 bg-dark-card text-dark-muted">หรือเข้าสู่ระบบด้วย</span>
            </div>
        </div>

        <!-- ThaID Button -->
        <a href="<?= \App\Core\View::url('/thaid/login') ?>" class="mt-6 w-full bg-white text-slate-900 font-semibold py-2.5 rounded-lg border border-slate-200 hover:bg-slate-50 transition-all flex items-center justify-center gap-2">
            <img src="https://imauth.bora.dopa.go.th/images/thaid_logo.png" 
                 onerror="this.src='https://placehold.co/24x24/0ea5e9/fff?text=ID'" 
                 class="h-6 w-auto" alt="ThaID">
            <span>ThaID (Mock)</span>
        </a>
    </div>

    <!-- Forgot Password -->
    <div class="mt-6 text-center">
        <a href="<?= \App\Core\View::url('/forgot-password') ?>" class="text-sm text-dark-muted hover:text-primary-500 transition-colors">
            ลืมรหัสผ่าน?
        </a>
    </div>

    <!-- Demo Credentials -->
    <div class="mt-8 p-4 bg-slate-800/50 rounded-lg border border-dashed border-dark-border text-xs text-dark-muted">
        <p class="font-semibold mb-1">Demo Credentials:</p>
        <div class="grid grid-cols-2 gap-2">
            <span>admin@moj.go.th</span> <span>admin123</span>
            <span>editor@moj.go.th</span> <span>editor123</span>
        </div>
    </div>
</div>
