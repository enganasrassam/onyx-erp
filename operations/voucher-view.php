<?php
require_once __DIR__ . '/../includes/header.php';
$id = (int)($_GET['id'] ?? 0);
$v = db_fetch_one("SELECT v.*, c.code as currency_code, c.symbol, c.name_ar as currency_name, cb.name_ar as cashbox_name, b.name_ar as bank_name FROM vouchers v LEFT JOIN currencies c ON v.currency_id=c.id LEFT JOIN cash_boxes cb ON v.cash_box_id=cb.id LEFT JOIN banks b ON v.bank_id=b.id WHERE v.id = ?", [$id]);
$lines = db_fetch_all("SELECT vl.*, a.code, a.name_ar FROM voucher_lines vl JOIN accounts a ON vl.account_id=a.id WHERE vl.voucher_id = ? ORDER BY vl.line_number", [$id]);
if (!$v) { flash('error', 'السند غير موجود'); redirect(APP_URL . '/operations/payment-vouchers.php'); }
$STATUS = ['draft' => ['مسودة','bg-slate-100 text-slate-700'], 'reviewed' => ['معتمد','bg-blue-50 text-blue-700'], 'posted' => ['مرحّل','bg-emerald-50 text-emerald-700'], 'cancelled' => ['ملغي','bg-rose-50 text-rose-700']];
$st = $STATUS[$v['status']] ?? $STATUS['draft'];
$totalDebit = array_sum(array_column($lines, 'debit_local'));
$totalCredit = array_sum(array_column($lines, 'credit_local'));
?>
<div class="card"><div class="card-header d-flex justify-content-between"><h5 class="mb-0"><?= sanitize($v['voucher_number']) ?></h5>
    <div class="d-flex gap-2">
        <span class="badge <?= $st[1] ?>" style="font-size:0.7rem;"><?= $st[0] ?></span>
        <span class="badge bg-slate-100" style="font-size:0.7rem;"><?= $v['type']==='payment'?'سند صرف':'سند قبض' ?></span>
    </div>
</div><div class="card-body">
    <div class="row g-3 mb-3 text-sm">
        <div class="col-md-3"><span class="text-slate-500">التاريخ:</span> <code dir="ltr"><?= date('d/m/Y', strtotime($v['voucher_date'])) ?></code></div>
        <div class="col-md-3"><span class="text-slate-500">العملة:</span> <?= sanitize($v['currency_code'] . ' (' . ($v['symbol'] ?? '') . ')') ?></div>
        <div class="col-md-3"><span class="text-slate-500"><?= $v['method']==='cash'?'الصندوق':'البنك' ?>:</span> <?= sanitize($v['cashbox_name'] ?? $v['bank_name'] ?? '—') ?></div>
        <?php if (!empty($v['cheque_number'])): ?><div class="col-md-3"><span class="text-slate-500">رقم الشيك:</span> <code dir="ltr"><?= sanitize($v['cheque_number']) ?></code></div><?php endif; ?>
    </div>
    <table class="table table-bordered table-sm">
        <thead><tr><th>الحساب</th><th class="text-end">مدين</th><th class="text-end">دائن</th></tr></thead>
        <tbody>
        <?php foreach ($lines as $l): ?>
            <tr><td><code dir="ltr" class="text-xs"><?= sanitize($l['code']) ?></code> <?= sanitize($l['name_ar']) ?><?php if ($l['description']): ?><span class="text-slate-400"> — <?= sanitize($l['description']) ?></span><?php endif; ?></td>
            <td class="text-end font-mono text-emerald-700"><?= $l['debit_local'] ? number_format($l['debit_local'], 2) : '—' ?></td>
            <td class="text-end font-mono text-rose-700"><?= $l['credit_local'] ? number_format($l['credit_local'], 2) : '—' ?></td></tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot class="fw-bold bg-slate-100"><tr><td class="text-end">الإجمالي</td>
        <td class="text-end font-mono text-emerald-700"><?= number_format($totalDebit, 2) ?></td>
        <td class="text-end font-mono text-rose-700"><?= number_format($totalCredit, 2) ?></td></tr></tfoot>
    </table>
    <?php if ($v['notes']): ?><div class="bg-slate-50 rounded p-2 small text-slate-600"><strong>ملاحظات:</strong> <?= sanitize($v['notes']) ?></div><?php endif; ?>
    <div class="mt-3 d-flex gap-2 justify-content-end">
        <a href="<?= APP_URL ?>/operations/<?= $v['type'] ?>-vouchers.php" class="btn btn-secondary">رجوع</a>
    </div>
</div></div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
