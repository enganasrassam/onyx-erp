<?php
/**
 * المتغيرات العامة للنظام
 */
require_once __DIR__ . '/../includes/header.php';

$vars = db_fetch_all("SELECT * FROM system_variables ORDER BY category, key_name");
$categories = ['general' => 'عامة', 'accounts' => 'أنظمة الحسابات', 'inventory' => 'أنظمة المخازن', 'suppliers' => 'أنظمة الموردين', 'customers' => 'أنظمة العملاء'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $id = (int)($_POST['id'] ?? 0);
    $value = trim($_POST['value'] ?? '');
    if ($id > 0) {
        db_update('system_variables', ['value' => $value], 'id = ?', [$id]);
        flash('success', 'تم تحديث المتغير بنجاح');
        redirect(APP_URL . '/setup/system-variables.php');
    }
}
?>

<div class="card mb-4">
    <div class="card-body">
        <h4 class="fw-bold text-slate-800 mb-1">المتغيرات العامة للنظام</h4>
        <p class="text-sm text-slate-500 mb-0">تحكم هذه المتغيرات في سلوك النظام العام. عدّل القيم بحرص — بعض المتغيرات تؤثر على عدة أنظمة.</p>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="list-group list-group-flush">
            <?php foreach ($vars as $v): ?>
                <div class="list-group-item p-3">
                    <div class="d-flex align-items-start gap-3">
                        <div class="flex-1">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <code dir="ltr" class="text-xs bg-slate-100 px-2 py-0.5 rounded font-mono" style="font-size: 0.75rem;"><?= sanitize($v['key_name']) ?></code>
                                <span class="badge bg-indigo-50 text-indigo-700" style="font-size: 0.65rem;"><?= $categories[$v['category']] ?? $v['category'] ?></span>
                            </div>
                            <p class="fw-medium text-slate-700 mb-2" style="font-size: 0.875rem;"><?= sanitize($v['description_ar'] ?? $v['key_name']) ?></p>
                            <form method="POST" class="d-flex gap-2 align-items-center">
                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                <input type="hidden" name="id" value="<?= $v['id'] ?>">
                                <input type="text" name="value" value="<?= sanitize($v['value']) ?>" class="form-control form-control-sm font-mono" dir="ltr" style="max-width: 400px;">
                                <button type="submit" class="btn btn-sm btn-primary">
                                    <svg width="14" height="14" fill="currentColor" viewBox="0 0 20 20"><path d="M7.707 10.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l7-7a1 1 0 00-1.414-1.414L10 12.586l-2.293-2.293z"/></svg>
                                    حفظ
                                </button>
                            </form>
                            <p class="text-xs text-slate-400 mt-2 mb-0" style="font-size: 0.65rem;">آخر تحديث: <?= date('Y-m-d H:i', strtotime($v['updated_at'])) ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
