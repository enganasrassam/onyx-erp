<?php
require_once __DIR__ . '/../includes/header.php';
$id = (int)($_GET['id'] ?? 0);
$e = db_fetch_one("SELECT * FROM daily_entries WHERE id = ?", [$id]);
$lines = db_fetch_all("SELECT del.*, a.code, a.name_ar FROM daily_entry_lines del JOIN accounts a ON del.account_id=a.id WHERE del.daily_entry_id = ? ORDER BY del.line_number", [$id]);
if (!$e) { flash('error', 'القيد غير موجود'); redirect(APP_URL . '/operations/daily-entries.php'); }
$STATUS = ['draft' => ['مسودة','bg-slate-100 text-slate-700'], 'posted' => ['مرحّل','bg-emerald-50 text-emerald-700'], 'cancelled' => ['ملغي','bg-rose-50 text-rose-700'], 'reversed' => ['عكسي','bg-amber-50 text-amber-700']];
$st = $STATUS[$e['status']] ?? $STATUS['draft'];
?>
<div class="card"><div class="card-header d-flex justify-content-between"><h5 class="mb-0"><?= sanitize($e['entry_number']) ?></h5>
    <div class="d-flex gap-2"><span class="badge <?= $st[1] ?>" style="font-size:0.7rem;"><?= $st[0] ?></span>
    <?php if ($e['is_reversed']): ?><span class="badge bg-amber-100 text-amber-700" style="font-size:0.7rem;">عكسي</span><?php endif; ?></div>
</div><div class="card-body">
    <?php if ($e['description']): ?><div class="mb-3 small text-slate-600"><strong>البيان:</strong> <?= sanitize($e['description']) ?></div><?php endif; ?>
    <table class="table table-bordered table-sm"><thead><tr><th>الحساب</th><th class="text-end">مدين</th><th class="text-end">دائن</th></tr></thead><tbody>
    <?php foreach ($lines as $l): ?>
        <tr><td><code dir="ltr" class="text-xs"><?= sanitize($l['code']) ?></code> <?= sanitize($l['name_ar']) ?><?php if ($l['description']): ?><span class="text-slate-400"> — <?= sanitize($l['description']) ?></span><?php endif; ?></td>
        <td class="text-end font-mono text-emerald-700"><?= $l['debit_local'] ? number_format($l['debit_local'], 2) : '—' ?></td>
        <td class="text-end font-mono text-rose-700"><?= $l['credit_local'] ? number_format($l['credit_local'], 2) : '—' ?></td></tr>
    <?php endforeach; ?>
    </tbody><tfoot class="fw-bold bg-slate-100"><tr><td class="text-end">الإجمالي</td>
    <td class="text-end font-mono text-emerald-700"><?= number_format($e['total_debit'], 2) ?></td>
    <td class="text-end font-mono text-rose-700"><?= number_format($e['total_credit'], 2) ?></td></tr></tfoot></table>
    <div class="mt-3"><a href="daily-entries.php" class="btn btn-secondary">رجوع</a></div>
</div></div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
