<?php
/**
 * شاشة تسجيل الدخول — نظام أونكس ERP
 */
require_once __DIR__ . '/includes/auth.php';

// إذا كان مسجلاً بالفعل
if (is_logged_in()) {
    redirect(APP_URL . '/dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'أدخل اسم المستخدم وكلمة المرور';
    } else {
        $result = login($username, $password);
        if ($result['success']) {
            redirect(APP_URL . '/dashboard.php');
        } else {
            $error = $result['error'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول — نظام أونكس ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Cairo', sans-serif; }
        .login-bg { background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #0f172a 100%); min-height: 100vh; }
        .login-card { background: #fff; border-radius: 16px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); }
        .login-logo { background: linear-gradient(135deg, #818cf8, #4f46e5); }
        .btn-login { background: linear-gradient(135deg, #4f46e5, #4338ca); border: none; color: #fff; font-weight: 700; padding: 0.75rem; border-radius: 0.5rem; box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.3); }
        .btn-login:hover { background: linear-gradient(135deg, #4338ca, #3730a3); }
    </style>
</head>
<body class="login-bg d-flex align-items-center justify-content-center p-4">
    <div class="w-100" style="max-width: 28rem;">
        <!-- الشعار والعنوان -->
        <div class="text-center mb-8 text-white">
            <div class="d-inline-flex align-items-center justify-content-center login-logo shadow-lg mb-4" style="width: 80px; height: 80px; border-radius: 16px;">
                <span class="text-white" style="font-size: 3rem; font-weight: 800;">أ</span>
            </div>
            <h1 class="h3 fw-extrabold text-white mb-1" style="font-weight: 800;">نظام أونكس ERP</h1>
            <p class="text-indigo-200" style="font-size: 0.875rem;">تخطيط موارد المؤسسة — الإصدار الثامن</p>
        </div>

        <!-- بطاقة الدخول -->
        <div class="login-card p-8" style="padding: 2rem;">
            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert"><?= sanitize($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-4">
                    <label class="form-label fw-semibold text-slate-700 mb-2" style="font-size: 0.875rem;">اسم المستخدم</label>
                    <div class="input-group">
                        <span class="input-group-text bg-slate-50 border-slate-300">
                            <svg width="20" height="20" fill="none" stroke="#94a3b8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </span>
                        <input type="text" name="username" class="form-control" placeholder="admin" value="admin" dir="ltr" autocomplete="username" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold text-slate-700 mb-2" style="font-size: 0.875rem;">كلمة المرور</label>
                    <div class="input-group">
                        <span class="input-group-text bg-slate-50 border-slate-300">
                            <svg width="20" height="20" fill="none" stroke="#94a3b8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </span>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" value="admin123" dir="ltr" autocomplete="current-password" required>
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" id="eyeIcon"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-login w-100 d-flex align-items-center justify-content-center gap-2">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3 3a1 1 0 011 1v12a1 1 0 11-2 0V4a1 1 0 011-1zm7.707 3.293a1 1 0 010 1.414L9.414 9H17a1 1 0 110 2H9.414l1.293 1.293a1 1 0 01-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    دخول النظام
                </button>
            </form>

            <div class="mt-5 pt-4 border-top text-center">
                <p class="text-xs text-slate-500 mb-0">
                    بيانات الدخول الافتراضية:
                    <code dir="ltr" class="font-mono font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded">admin / admin123</code>
                </p>
            </div>
        </div>

        <p class="text-center text-indigo-300 mt-6" style="font-size: 0.75rem;">© 2024 نظام أونكس ERP — جميع الحقوق محفوظة</p>
    </div>

    <script>
    document.getElementById('togglePassword').addEventListener('click', function() {
        const input = document.querySelector('input[name="password"]');
        const icon = document.getElementById('eyeIcon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-.732a1 1 0 01-.732-.732l-.732-3.29m13.36 8.13l-3.29-.732a1 1 0 01-.732-.732l-.732-3.29m-9.13-9.13l3.29.732a1 1 0 01.732.732l.732 3.29"/>';
        } else {
            input.type = 'password';
            icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>';
        }
    });
    </script>
</body>
</html>
