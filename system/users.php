<?php
/**
 * بيانات المستخدمين — المرحلة 4
 */
require_once __DIR__ . '/../includes/header.php';
require_role('admin');

$users = db_fetch_all("SELECT * FROM users ORDER BY created_at DESC");

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id !== (int)$_SESSION['user_id']) {
        db_delete('users', 'id = ?', [$id]);
        flash('success', 'تم حذف المستخدم');
    } else {
        flash('error', 'لا يمكن حذف المستخدم الحالي');
    }
    redirect(APP_URL . '/system/users.php');
}
?>

<div class="card mb-4">
    <div class="card-body d-flex align-items-center justify-content-between">
        <div>
            <h4 class="fw-bold text-slate-800 mb-1">بيانات المستخدمين</h4>
            <p class="text-sm text-slate-500 mb-0">إدارة المستخدمين والصلاحيات</p>
        </div>
        <a href="user-form.php" class="btn btn-primary d-flex align-items-center gap-1">
            <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20"><path d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"/></svg>
            إضافة مستخدم
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>المعرف</th>
                    <th>اسم المستخدم</th>
                    <th>الاسم الكامل</th>
                    <th>البريد</th>
                    <th>الدور</th>
                    <th>الحالة</th>
                    <th>آخر دخول</th>
                    <th class="text-center">إجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><code dir="ltr"><?= $u['id'] ?></code></td>
                        <td><code dir="ltr" class="bg-slate-100 px-2 py-0.5 rounded"><?= sanitize($u['username']) ?></code></td>
                        <td><?= sanitize($u['full_name']) ?></td>
                        <td class="text-slate-600" dir="ltr"><?= sanitize($u['email'] ?? '—') ?></td>
                        <td>
                            <?php
                            $roles = ['admin'=>'مدير','accountant'=>'محاسب','viewer'=>'مشاهد'];
                            $roleColors = ['admin'=>'bg-rose-50 text-rose-700','accountant'=>'bg-blue-50 text-blue-700','viewer'=>'bg-slate-100 text-slate-700'];
                            $r = $u['role'];
                            ?>
                            <span class="badge <?= $roleColors[$r] ?? $roleColors['viewer'] ?>" style="font-size: 0.7rem;"><?= $roles[$r] ?? $r ?></span>
                        </td>
                        <td><?= $u['active'] ? status_badge('active') : status_badge('inactive') ?></td>
                        <td class="text-xs text-slate-500" dir="ltr"><?= $u['last_login_at'] ? date('Y-m-d H:i', strtotime($u['last_login_at'])) : '—' ?></td>
                        <td class="text-center">
                            <a href="user-form.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-outline-primary">
                                <svg width="14" height="14" fill="currentColor" viewBox="0 0 20 20"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/></svg>
                            </a>
                            <?php if ((int)$u['id'] !== (int)$_SESSION['user_id']): ?>
                                <a href="?delete=<?= $u['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirmDelete('هل أنت متأكد من حذف المستخدم <?= sanitize($u['username']) ?>؟')">
                                    <svg width="14" height="14" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
