<?php
/**
 * نموذج إضافة/تعديل مستخدم
 */
require_once __DIR__ . '/../includes/header.php';
require_role('admin');

$id = (int)($_GET['id'] ?? 0);
$user = $id ? db_fetch_one("SELECT * FROM users WHERE id = ?", [$id]) : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $username = trim($_POST['username'] ?? '');
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = $_POST['role'] ?? 'viewer';
    $active = isset($_POST['active']) ? 1 : 0;
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($fullName)) {
        flash('error', 'اسم المستخدم والاسم الكامل مطلوبان');
    } else {
        if ($id) {
            // تعديل
            $data = ['username' => $username, 'full_name' => $fullName, 'email' => $email ?: null, 'role' => $role, 'active' => $active];
            if (!empty($password)) {
                $data['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
            }
            db_update('users', $data, 'id = ?', [$id]);
            flash('success', 'تم تحديث المستخدم');
        } else {
            // إضافة
            if (empty($password)) {
                flash('error', 'كلمة المرور مطلوبة للمستخدم الجديد');
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                db_insert('users', [
                    'username' => $username, 'full_name' => $fullName, 'email' => $email ?: null,
                    'password_hash' => $hash, 'role' => $role, 'active' => $active,
                ]);
                flash('success', 'تم إنشاء المستخدم');
            }
        }
        redirect(APP_URL . '/system/users.php');
    }
}
?>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><?= $user ? 'تعديل مستخدم' : 'إضافة مستخدم جديد' ?></h5>
    </div>
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">اسم المستخدم *</label>
                    <input type="text" name="username" class="form-control" value="<?= sanitize($user['username'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">الاسم الكامل *</label>
                    <input type="text" name="full_name" class="form-control" value="<?= sanitize($user['full_name'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">البريد الإلكتروني</label>
                    <input type="email" name="email" class="form-control" value="<?= sanitize($user['email'] ?? '') ?>" dir="ltr">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">الدور</label>
                    <select name="role" class="form-select">
                        <option value="admin" <?= ($user['role'] ?? '') === 'admin' ? 'selected' : '' ?>>مدير</option>
                        <option value="accountant" <?= ($user['role'] ?? '') === 'accountant' ? 'selected' : '' ?>>محاسب</option>
                        <option value="viewer" <?= ($user['role'] ?? '') === 'viewer' ? 'selected' : '' ?>>مشاهد</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">كلمة المرور <?= $user ? '(اتركها فارغة للإبقاء)' : '*' ?></label>
                    <input type="password" name="password" class="form-control" <?= $user ? '' : 'required' ?> dir="ltr">
                </div>
                <div class="col-md-6 d-flex align-items-end">
                    <div class="form-check">
                        <input type="checkbox" name="active" class="form-check-input" id="activeCheck" <?= (!isset($user) || $user['active']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="activeCheck">نشط</label>
                    </div>
                </div>
            </div>
            <div class="mt-4 d-flex gap-2 justify-content-end">
                <a href="users.php" class="btn btn-secondary">إلغاء</a>
                <button type="submit" class="btn btn-primary">حفظ</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
