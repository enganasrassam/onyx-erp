<?php
require_once __DIR__ . '/../includes/header.php';
$id = (int)($_GET['id'] ?? 0);
$inv = db_fetch_one("SELECT i.*, c.code as currency_code, c.symbol FROM invoices i LEFT JOIN currencies c ON i.currency_id=c.id WHERE i.id = ?", [$id]);
$lines = db_fetch_all("SELECT il.*, it.code as item_code, it.name_ar as item_name, u.name_ar as unit_name FROM invoice_lines il JOIN items it ON il.item_id=it.id LEFT JOIN units u ON il.unit_id=u.id WHERE il.invoice_id = ? ORDER BY il.line_number", [$id]);
if (!$inv) { flash('error', 'الفاتورة غير موجودة'); redirect(APP_URL . '/operations/' . ($inv['type'] ?? 'purchase') . '-invoices.php'); }
$STATUS = ['draft'=>['مسودة','bg-slate-100 text-slate-700'],'posted'=>['مرحّل','bg-emerald-50 text-emerald-700'],'cancelled'=>['ملغي','bg-rose-50 text-rose-700'],'paid'=>['مدفوعة','bg-blue-50 text-blue-700']];
$st = $STATUS[$inv['status']] ?? $STATUS['draft'];
?>
<div class="card"><div class="card-header d-flex justify-content-between"><h5 class="mb-0"><?= sanitize($inv['invoice_number']) ?></h5>
    <span class="badge <?= $st[1] ?>" style="font-size:0.7rem;"><?= $st[0] ?></span>
</div><div class="card-body">
    <div class="row g-3 mb-3 text-sm">
        <div class="col-md-3"><span class="text-slate-500">التاريخ:</span> <code dir="ltr"><?= date('d/m/Y', strtotime($inv['invoice_date'])) ?></code></div>
        <div class="col-md-3"><span class="text-slate-500"><?= $inv['party_type']==='supplier'?'المورد':'العميل' ?>:</span> <?= sanitize($inv['party_name'] ?? '—') ?></div>
        <div class="col-md-3"><span class="text-slate-500">طريقة الدفع:</span> <?= $inv['payment_method']==='cash'?'نقدًا':($inv['payment_method']==='bank'?'شيك':'آجل') ?></div>
        <div class="col-md-3"><span class="text-slate-500">العملة:</span> <?= sanitize($inv['currency_code']) ?></div>
    </div>
    <table class="table table-bordered table-sm"><thead><tr><th>الصنف</th><th>الوحدة</th><th class="text-end">الكمية</th><th class="text-end">سعر الوحدة</th><th class="text-end">الخصم</th><th class="text-end">الإجمالي</th></tr></thead><tbody>
    <?php foreach ($lines as $l): ?>
        <tr><td><code dir="ltr" class="text-xs"><?= sanitize($l['item_code']) ?></code> <?= sanitize($l['item_name']) ?></td>
        <td class="small text-slate-600"><?= sanitize($l['unit_name'] ?? '—') ?></td>
        <td class="text-end font-mono"><?= $l['quantity'] ?></td>
        <td class="text-end font-mono"><?= number_format($l['unit_price']) ?></td>
        <td class="text-end font-mono text-amber-700"><?= $l['discount_amount'] ? number_format($l['discount_amount']) : '—' ?></td>
        <td class="text-end font-mono fw-bold"><?= number_format($l['total'], 2) ?></td></tr>
    <?php endforeach; ?>
    </tbody></table>
    <div class="row g-2 mt-3">
        <div class="col-md-3"><div class="bg-slate-50 rounded p-2 text-center"><p class="small text-slate-500 mb-0">المجموع الفرعي</p><p class="font-mono fw-bold mb-0" dir="ltr"><?= number_format($inv['subtotal'], 2) ?></p></div></div>
        <div class="col-md-3"><div class="bg-amber-50 rounded p-2 text-center"><p class="small text-slate-500 mb-0">الخصم</p><p class="font-mono fw-bold text-amber-700 mb-0" dir="ltr"><?= number_format($inv['discount'], 2) ?></p></div></div>
        <div class="col-md-3"><div class="bg-blue-50 rounded p-2 text-center"><p class="small text-slate-500 mb-0">الضريبة (<?= $inv['tax_pct'] ?>%)</p><p class="font-mono fw-bold text-blue-700 mb-0" dir="ltr"><?= number_format($inv['tax_amount'], 2) ?></p></div></div>
        <div class="col-md-3"><div class="bg-gradient-to-l from-indigo-500 to-purple-600 text-white rounded p-2 text-center"><p class="small opacity-80 mb-0">الإجمالي</p><p class="font-mono fw-bold mb-0" dir="ltr"><?= number_format($inv['total_local'], 2) ?> <?= sanitize($inv['currency_code']) ?></p></div></div>
    </div>
    <?php if ($inv['notes']): ?><div class="mt-3 bg-slate-50 rounded p-2 small"><strong>ملاحظات:</strong> <?= sanitize($inv['notes']) ?></div><?php endif; ?>
    <div class="mt-3"><a href="<?= APP_URL . '/operations/' . $inv['type'] . '-invoices.php' ?>" class="btn btn-secondary">رجوع</a></div>
</div></div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
