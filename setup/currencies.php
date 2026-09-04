<?php
/**
 * العملات — نظام أونكس ERP
 */
require_once __DIR__ . '/../includes/header.php';

$search = trim($_GET['search'] ?? '');
$where = $search ? "WHERE code LIKE ? OR name_ar LIKE ? OR name_en LIKE ?" : "";
$params = $search ? ["%$search%", "%$search%", "%$search%"] : [];
$currencies = db_fetch_all("SELECT * FROM currencies {$where} ORDER BY code ASC", $params);

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $isBase = db_fetch_one("SELECT is_base FROM currencies WHERE id = ?", [$id]);
    if ($isBase && $isBase['is_base']) {
        flash('error', 'لا يمكن حذف العملة الأساسية');
    } else {
        db_delete('currencies', 'id = ?', [$id]);
        flash('success', 'تم حذف العملة');
    }
    redirect(APP_URL . '/setup/currencies.php');
}
?>

<div class="card mb-4">
    <div class="card-body d-flex align-items-center justify-content-between gap-3">
        <div>
            <h4 class="fw-bold text-slate-800 mb-1">تهيئة العملات</h4>
            <p class="text-sm text-slate-500 mb-0">إدارة العملات المستخدمة في النظام. العملة الأساسية تُسجل بها الحسابات، والعملات الأجنبية للمعاملات الدولية.</p>
        </div>
        <a href="currency-form.php" class="btn btn-primary d-flex align-items-center gap-1">
            <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20"><path d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"/></svg>
            إضافة عملة
        </a>
    </div>
</div>

<form method="GET" class="mb-3 d-flex gap-2">
    <input type="text" name="search" class="form-control" placeholder="بحث بالرمز أو الاسم..." value="<?= sanitize($search) ?>">
    <button class="btn btn-outline-secondary">بحث</button>
    <a href="currencies.php" class="btn btn-outline-secondary">إلغاء</a>
</form>

<div class="card">
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>الرمز</th>
                    <th>الاسم (عربي)</th>
                    <th>الاسم (إنجليزي)</th>
                    <th>الرمز</th>
                    <th class="text-end">سعر الصرف</th>
                    <th class="text-center">النوع</th>
                    <th class="text-center">الحالة</th>
                    <th class="text-center">إجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($currencies)): ?>
                    <tr><td colspan="8" class="text-center py-4 text-slate-400">لا توجد عملات. اضغط "إضافة عملة" للبدء.</td></tr>
                <?php else: foreach ($currencies as $c): ?>
                    <tr>
                        <td><code dir="ltr" class="bg-slate-100 px-2 py-0.5 rounded font-mono"><?= sanitize($c['code']) ?></code></td>
                        <td><?= sanitize($c['name_ar']) ?></td>
                        <td class="text-slate-600" dir="ltr"><?= sanitize($c['name_en'] ?? '—') ?></td>
                        <td><?= sanitize($c['symbol'] ?? '—') ?></td>
                        <td class="text-end font-mono" dir="ltr"><?= number_format($c['exchange_rate'], 4) ?></td>
                        <td class="text-center">
                            <?php if ($c['is_base']): ?>
                                <span class="badge bg-emerald-50 text-emerald-700" style="font-size: 0.7rem;">أساسية</span>
                            <?php elseif ($c['is_foreign']): ?>
                                <span class="badge bg-amber-50 text-amber-700" style="font-size: 0.7rem;">أجنبية</span>
                            <?php else: ?>—<?php endif; ?>
                        </td>
                        <td class="text-center"><?= $c['active'] ? status_badge('active') : status_badge('inactive') ?></td>
                        <td class="text-center">
                            <a href="currency-form.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary">
                                <svg width="14" height="14" fill="currentColor" viewBox="0 0 20 20"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/></svg>
                            </a>
                            <?php if (!$c['is_base']): ?>
                                <a href="?delete=<?= $c['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirmDelete('هل أنت متأكد من حذف العملة <?= sanitize($c['name_ar']) ?>؟')">
                                    <svg width="14" height="14" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9z" clip-rule="evenodd"/></svg>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
