<?php
require_once __DIR__ . '/../includes/header.php';
$years = db_fetch_all("SELECT * FROM fiscal_years ORDER BY year_name DESC");
$closures = db_fetch_all("SELECT pc.*, fy.year_name, u.full_name FROM period_closures pc JOIN fiscal_years fy ON pc.fiscal_year_id=fy.id LEFT JOIN users u ON pc.closed_by=u.id ORDER BY pc.created_at DESC");
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $c = db_fetch_one("SELECT * FROM period_closures WHERE id = ?", [$id]);
    if ($c) {
        db_update('fiscal_periods', ['status' => 'open'], 'fiscal_year_id = ? AND period_number = ?', [$c['fiscal_year_id'], $c['period_number']]);
        if ($c['closure_type'] === 'yearly_close') db_update('fiscal_years', ['closed' => 0], 'id = ?', [$c['fiscal_year_id']]);
        db_delete('period_closures', 'id = ?', [$id]);
        flash('success', 'تم فتح الفترة');
    }
    redirect(APP_URL . '/operations/closures.php');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $fyId = (int)$_POST['fiscal_year_id'];
    $periodNum = (int)$_POST['period_number'];
    $closureType = $_POST['closure_type'];
    $notes = trim($_POST['notes'] ?? '');
    $exists = db_fetch_one("SELECT id FROM period_closures WHERE fiscal_year_id=? AND period_number=? AND closure_type=?", [$fyId, $periodNum, $closureType]);
    if ($exists) { flash('error', 'الإقفال موجود مسبقًا'); }
    else {
        db_update('fiscal_periods', ['status' => $closureType === 'monthly_suspension' ? 'open' : 'closed'], 'fiscal_year_id=? AND period_number=?', [$fyId, $periodNum]);
        if ($closureType === 'yearly_close') db_update('fiscal_years', ['closed' => 1], 'id=?', [$fyId]);
        db_insert('period_closures', ['fiscal_year_id'=>$fyId, 'period_number'=>$periodNum, 'closure_type'=>$closureType, 'closed_by'=>$_SESSION['user_id'], 'notes'=>$notes ?: null]);
        flash('success', 'تم الإقفال بنجاح');
    }
    redirect(APP_URL . '/operations/closures.php');
}
$TYPES = ['monthly_suspension' => ['توقيف شهري', 'bg-amber-50 text-amber-700'], 'monthly_close' => ['إقفال شهري', 'bg-rose-50 text-rose-700'], 'yearly_close' => ['إقفال سنوي', 'bg-purple-50 text-purple-700']];
?>
<div class="card mb-4"><div class="card-body d-flex justify-content-between align-items-center">
    <div><h4 class="fw-bold text-slate-800 mb-1">الإقفال والتوقيف الشهري</h4><p class="text-sm text-slate-500 mb-0">توقيف العمليات الشهرية، إقفال الفترات شهريًا أو سنويًا.</p></div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#closureModal"><svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20"><path d="M5 4a2 2 0 012-2h6a2 2 0 012 2v14l-5-2.5L5 18V4z"/></svg> إقفال جديد</button>
</div></div>

<div class="space-y-3">
<?php if (empty($closures)): ?>
<div class="card"><div class="card-body text-center py-5 text-slate-400">لا توجد إقفالات</div></div>
<?php else: foreach ($closures as $c): $info = $TYPES[$c['closure_type']] ?? $TYPES['monthly_close']; ?>
<div class="card"><div class="card-body d-flex align-items-center gap-3">
    <div class="d-flex align-items-center justify-content-center rounded-xl <?= $info[1] ?>" style="width:48px;height:48px;"><svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z"/></svg></div>
    <div class="flex-1"><div class="d-flex align-items-center gap-2"><h6 class="fw-bold mb-0">سنة <?= sanitize($c['year_name']) ?> — فترة <?= $c['period_number'] ?></h6><span class="badge <?= $info[1] ?>" style="font-size:0.65rem;"><?= $info[0] ?></span></div><p class="text-xs text-slate-500 mt-1 mb-0">أُغلق بواسطة: <?= sanitize($c['full_name'] ?? '—') ?> · <?= date('d/m/Y H:i', strtotime($c['created_at'])) ?><?php if ($c['notes']): ?> · <?= sanitize($c['notes']) ?><?php endif; ?></p></div>
    <a href="?delete=<?= $c['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('فتح الفترة؟')">فتح الفترة</a>
</div></div>
<?php endforeach; endif; ?>
</div>

<div class="modal fade" id="closureModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <form method="POST"><input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
    <div class="modal-header"><h5 class="modal-title">إقفال جديد</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <div class="mb-3"><label class="form-label small fw-semibold">السنة المالية</label>
            <select name="fiscal_year_id" class="form-select" required><?php foreach ($years as $y): ?><option value="<?= $y['id'] ?>"><?= sanitize($y['year_name']) ?></option><?php endforeach; ?></select>
        </div>
        <div class="mb-3"><label class="form-label small fw-semibold">رقم الفترة (1-12)</label>
            <input type="number" name="period_number" class="form-control" dir="ltr" min="1" max="12" value="1" required>
        </div>
        <div class="mb-3"><label class="form-label small fw-semibold">نوع الإقفال</label>
            <select name="closure_type" class="form-select"><option value="monthly_suspension">توقيف شهري (يمكن فتحه)</option><option value="monthly_close">إقفال شهري نهائي</option><option value="yearly_close">إقفال سنوي</option></select>
        </div>
        <div class="mb-3"><label class="form-label small fw-semibold">ملاحظات</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button><button type="submit" class="btn btn-primary">تأكيد الإقفال</button></div>
    </form>
</div></div></div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
