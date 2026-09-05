<?php
require_once __DIR__ . '/includes/auth.php';

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
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;700;800&display=swap" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Cairo',sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:16px;background:linear-gradient(135deg,#1a1a2e 0%,#16213e 50%,#0f172a 100%)}
        .login-container{width:100%;max-width:380px}
        .login-logo{width:72px;height:72px;border-radius:14px;background:linear-gradient(135deg,#e94560,#c81d3e);display:flex;align-items:center;justify-content:center;box-shadow:0 16px 32px rgba(233,69,96,0.35);margin:0 auto 14px}
        .login-logo span{color:#fff;font-size:2.8rem;font-weight:800}
        .login-title{text-align:center;color:#fff;margin-bottom:28px}
        .login-title h1{font-size:26px;font-weight:800;margin-bottom:4px}
        .login-title p{color:#aebfd8;font-size:13px}
        .login-card{background:#fff;border-radius:14px;padding:28px;box-shadow:0 20px 40px rgba(0,0,0,0.25)}
        .form-group{margin-bottom:18px}
        .form-group label{display:block;font-size:12px;font-weight:600;color:#334155;margin-bottom:5px}
        .input-group{position:relative}
        .input-group svg{position:absolute;right:10px;top:50%;transform:translateY(-50%)}
        .input-group input{width:100%;padding:10px 36px 10px 14px;border:1px solid #cbd5e1;border-radius:8px;font-size:13px;font-family:inherit;outline:none;background:#f8fafc;transition:all .2s}
        .input-group input:focus{border-color:#0f3460;background:#fff;box-shadow:0 0 0 3px rgba(15,52,96,.12)}
        .btn-login{width:100%;padding:11px;border:none;border-radius:8px;background:#0f3460;color:#fff;font-size:14px;font-weight:700;font-family:inherit;cursor:pointer;transition:all .2s;box-shadow:0 8px 12px rgba(15,52,96,.25);display:flex;align-items:center;justify-content:center;gap:8px}
        .btn-login:hover{background:#1a4a7a;box-shadow:0 12px 16px rgba(15,52,96,.35);transform:translateY(-1px)}
        .alert{padding:8px 12px;border-radius:6px;margin-bottom:14px;font-size:12px}
        .alert-danger{background:#fee2e2;color:#dc2626;border:1px solid #fca5a5}
        .login-footer{text-align:center;margin-top:18px;padding-top:16px;border-top:1px solid #f1f5f9}
        .login-footer p{font-size:11px;color:#64748b}
        .login-footer code{font-family:'Cairo',monospace;font-weight:700;color:#0f3460;background:#dbeafe;padding:2px 8px;border-radius:4px;direction:ltr;display:inline-block}
        .copyright{text-align:center;color:#aebfd8;font-size:11px;margin-top:20px}
        .toggle-pwd{position:absolute;left:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#94a3b8;padding:4px}
        .toggle-pwd:hover{color:#0f3460}
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-logo"><span>أ</span></div>
        <div class="login-title">
            <h1>نظام أونكس ERP</h1>
            <p>تخطيط موارد المؤسسة — الإصدار الثامن</p>
        </div>
        <div class="login-card">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label>اسم المستخدم</label>
                    <div class="input-group">
                        <svg width="18" height="18" fill="none" stroke="#94a3b8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <input type="text" name="username" placeholder="admin" value="admin" dir="ltr" autocomplete="username" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>كلمة المرور</label>
                    <div class="input-group">
                        <svg width="18" height="18" fill="none" stroke="#94a3b8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <input type="password" name="password" id="password" placeholder="••••••••" value="admin123" dir="ltr" autocomplete="current-password" required>
                        <button type="button" class="toggle-pwd" onclick="togglePassword()">
                            <svg id="eyeOpen" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg id="eyeClosed" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-.732a1 1 0 01-.732-.732m4.522 4.522l-.732 3.29M3 21l18-18"/></svg>
                        </button>
                    </div>
                </div>
                <button type="submit" class="btn-login">
                    <svg width="18" height="18" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3 3a1 1 0 011 1v12a1 1 0 11-2 0V4a1 1 0 011-1zm7.707 3.293a1 1 0 010 1.414L9.414 9H17a1 1 0 110 2H9.414l1.293 1.293a1 1 0 01-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    دخول النظام
                </button>
            </form>
            <div class="login-footer">
                <p>بيانات الدخول الافتراضية: <code>admin / admin123</code></p>
            </div>
        </div>
        <p class="copyright">© 2024 نظام أونكس ERP — جميع الحقوق محفوظة</p>
    </div>
    <script>
    function togglePassword(){var p=document.getElementById('password'),o=document.getElementById('eyeOpen'),c=document.getElementById('eyeClosed');if(p.type==='password'){p.type='text';o.style.display='none';c.style.display=''}else{p.type='password';o.style.display='';c.style.display='none'}}
    </script>
</body>
</html>
