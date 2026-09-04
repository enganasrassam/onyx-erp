<?php
/**
 * ملف التثبيت — يُشغَّل مرة واحدة فقط
 *
 * 1. عدّل config/config.php بمعلومات قاعدة البيانات
 * 2. استورد database/schema.sql في MySQL
 * 3. شغّل هذا الملف في المتصفح: http://localhost/onyx/install.php
 * 4. احذف هذا الملف بعد التثبيت
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // إنشاء حساب المدير بكلمة مرور مشفّرة
        $username = trim($_POST['username'] ?? 'admin');
        $password = $_POST['password'] ?? 'admin123';
        $fullName = trim($_POST['full_name'] ?? 'مدير النظام');
        $email = trim($_POST['email'] ?? 'admin@onyx.local');

        if (empty($username) || empty($password)) {
            throw new Exception('اسم المستخدم وكلمة المرور مطلوبان');
        }

        // تحقق من عدم وجود المستخدم
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            // تحديث كلمة المرور
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password_hash = ?, full_name = ?, email = ? WHERE username = ?");
            $stmt->execute([$hash, $fullName, $email, $username]);
            $message = "✓ تم تحديث بيانات المدير بنجاح";
        } else {
            // إنشاء جديد
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, full_name, role, active) VALUES (?, ?, ?, ?, 'admin', 1)");
            $stmt->execute([$username, $email, $hash, $fullName]);
            $message = "✓ تم إنشاء حساب المدير بنجاح";
        }
        $success = true;
    } catch (Exception $e) {
        $message = "✗ خطأ: " . $e->getMessage();
    }
}

// التحقق من الاتصال بقاعدة البيانات
try {
    $pdo->query("SELECT 1");
    $dbConnected = true;
} catch (Exception $e) {
    $dbConnected = false;
    $message = "✗ تعذّر الاتصال بقاعدة البيانات. تحقق من config/config.php — " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تثبيت نظام أونكس ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Cairo', 'Tajawal', sans-serif; background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #1e293b 100%); min-height: 100vh; }
        .install-card { background: white; border-radius: 16px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); }
        .logo { background: linear-gradient(135deg, #818cf8, #4f46e5); }
    </style>
</head>
<body class="d-flex align-items-center min-vh-100 p-4">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="text-center mb-4 text-white">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-3 logo text-white shadow-lg mb-3" style="width: 80px; height: 80px;">
                        <span style="font-size: 2.5rem; font-weight: 800;">أ</span>
                    </div>
                    <h1 class="h3 fw-bold mb-1">نظام أونكس ERP</h1>
                    <p class="text-indigo-200 mb-0">معالج التثبيت</p>
                </div>

                <div class="install-card p-4 p-md-5">
                    <?php if (!$dbConnected): ?>
                        <div class="alert alert-danger">
                            <strong>تعذّر الاتصال بقاعدة البيانات!</strong><br>
                            <?= htmlspecialchars($message ?? '') ?>
                        </div>
                        <div class="mt-3 small text-muted">
                            <p class="fw-bold">خطوات التثبيت:</p>
                            <ol>
                                <li>عدّل <code>config/config.php</code> بمعلومات قاعدة البيانات</li>
                                <li>استورد <code>database/schema.sql</code> في MySQL</li>
                                <li>أعد تحميل هذه الصفحة</li>
                            </ol>
                        </div>
                    <?php elseif ($success): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
                        <div class="text-center mt-4">
                            <a href="login.php" class="btn btn-primary btn-lg w-100">الذهاب لصفحة تسجيل الدخول</a>
                        </div>
                        <div class="alert alert-warning mt-4 small">
                            <strong>⚠ مهم:</strong> احذف ملف <code>install.php</code> الآن لأسباب أمنية.
                        </div>
                    <?php else: ?>
                        <?php if ($message): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($message) ?></div>
                        <?php endif; ?>

                        <h4 class="mb-3">إنشاء حساب المدير</h4>
                        <p class="text-muted small mb-4">تم الاتصال بقاعدة البيانات بنجاح. أنشئ حساب المدير للمتابعة.</p>

                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">اسم المستخدم</label>
                                <input type="text" name="username" class="form-control" value="admin" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">الاسم الكامل</label>
                                <input type="text" name="full_name" class="form-control" value="مدير النظام" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">البريد الإلكتروني</label>
                                <input type="email" name="email" class="form-control" value="admin@onyx.local">
                            </div>
                            <div class="mb-4">
                                <label class="form-label small fw-semibold">كلمة المرور</label>
                                <input type="text" name="password" class="form-control font-monospace" value="admin123" required>
                                <small class="text-muted">الافتراضية: admin123 (غيّرها لاحقًا من شاشة المستخدمين)</small>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg w-100">إنشاء الحساب وتثبيت النظام</button>
                        </form>
                    <?php endif; ?>
                </div>

                <p class="text-center text-indigo-300 small mt-4 mb-0">© 2024 نظام أونكس ERP — الإصدار 8.0</p>
            </div>
        </div>
    </div>
</body>
</html>
