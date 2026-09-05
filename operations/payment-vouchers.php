<?php
/**
 * سندات الصرف والقبض — نظام أونكس ERP
 */
require_once __DIR__ . '/../includes/header.php';

$voucherType = basename($_SERVER['PHP_SELF'], '.php') === 'payment-vouchers' ? 'payment' : 'receipt';
$title = $voucherType === 'payment' ? 'سندات الصرف' : 'سندات القبض';

$search = trim($_GET['search'] ?? '');
$where = "WHERE v.type = ?";
$params = [$voucherType];
if ($search) {
    $where .= " AND (v.voucher_number LIKE ? OR v.notes LIKE ?)";
    $params[] = "%$search%"; $params[] = "%$search%";
}
$vouchers = db_fetch_all("SELECT v.*, c.code as currency_code, c.symbol, cb.name_ar as cashbox_name, b.name_ar as bank_name FROM vouchers v LEFT JOIN currencies c ON v.currency_id=c.id LEFT JOIN cash_boxes cb ON v.cash_box_id=cb.id LEFT JOIN banks b ON v.bank_id=b.id {$where} ORDER BY v.voucher_date DESC", $params);

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $v = db_fetch_one("SELECT status FROM vouchers WHERE id = ?", [$id]);
    if ($v && $v['status'] === 'draft') {
        db_delete('vouchers', 'id = ?', [$id]);
        flash('success', 'تم حذف السند');
    } else flash('error', 'لا يمكن حذف سند معتمد');
    redirect(APP_URL . "/operations/{$voucherType}-vouchers.php");
}

if (isset($_GET['action'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];
    $statusMap = ['review' => 'reviewed', 'post' => 'posted', 'cancel' => 'cancelled'];
    if (isset($statusMap[$action])) {
        db_update('vouchers', ['status' => $statusMap[$action]], 'id = ?', [$id]);
        db_insert('document_reviews', ['doc_type' => 'voucher', 'doc_id' => $id, 'action' => $action, 'reviewed_by' => $_SESSION['user_id'], 'reviewed_at' => date('Y-m-d H:i:s')]);
        flash('success', 'تم ' . ($action === 'post' ? 'الترحيل' : ($action === 'review' ? 'الاعتماد' : 'الإلغاء')));
    }
    redirect(APP_URL . "/operations/{$voucherType}-vouchers.php");
}

$STATUS = ['draft' => ['مسودة','bg-slate-100 text-slate-700'], 'reviewed' => ['معتمد','bg-blue-50 text-blue-700'], 'posted' => ['مرحّل','bg-emerald-50 text-emerald-700'], 'cancelled' => ['ملغي','bg-rose-50 text-rose-700']];
?>

<div class="card mb-4"><div class="card-body d-flex align-items-center justify-content-between gap-3">
    <div><h4 class="fw-bold text-slate-800 mb-1"><?= $title ?></h4>
    <p class="text-sm text-slate-500 mb-0"><?= $voucherType === 'payment' ? 'الصندوق/البنك دائن، والحسابات الأخرى مدينة.' : 'الصندوق/البنك مدين، والحسابات الأخرى دائنة.' ?></p></div>
    <a href="voucher-form.php?type=<?= $voucherType ?>" class="btn btn-primary d-flex align-items-center gap-1">
        <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20"><path d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"/></svg>
        إضافة سند
    </a>
</div></div>

<form method="GET" class="mb-3 d-flex gap-2">
    <input type="text" name="search" class="form-control" placeholder="بحث برقم السند..." value="<?= sanitize($search) ?>">
    <button class="btn btn-outline-secondary">بحث</button>
    <?php if ($search): ?><a href="<?= $voucherType ?>-vouchers.php" class="btn btn-outline-secondary">إلغاء</a><?php endif; ?>
</form>

<div class="card"><div class="card-body p-0">
    <table class="table mb-0">
        <thead><tr>
            <th>رقم السند</th><th>التاريخ</th><th class="text-center">الطريقة</th><th>الصندوق/البنك</th>
            <th class="text-end">المبلغ</th><th class="text-center">العملة</th><th class="text-center">الحالة</th><th class="text-center">إجراءات</th>
        </tr></thead>
        <tbody>
        <?php if (empty($vouchers)): ?>
            <tr><td colspan="8" class="text-center py-4 text-slate-400">لا توجد سندات</td></tr>
        <?php else: foreach ($vouchers as $v): $st = $STATUS[$v['status']] ?? $STATUS['draft']; ?>
            <tr>
                <td><code dir="ltr" class="bg-slate-100 px-2 py-0.5 rounded font-mono"><?= sanitize($v['voucher_number']) ?></code></td>
                <td dir="ltr"><?= date('d/m/Y', strtotime($v['voucher_date'])) ?></td>
                <td class="text-center"><span class="badge <?= $v['method']==='cash'?'bg-emerald-50 text-emerald-700':'bg-blue-50 text-blue-700' ?>" style="font-size:0.7rem;"><?= $v['method']==='cash'?'نقدًا':'شيك' ?></span></td>
                <td class="text-sm"><?= sanitize($v['cashbox_name'] ?? $v['bank_name'] ?? '—') ?></td>
                <td class="text-end font-mono" dir="ltr"><?= number_format($v['amount_local'], 2) ?></td>
                <td class="text-center"><span class="badge bg-slate-100" style="font-size:0.7rem;"><?= sanitize($v['currency_code']) ?></span></td>
                <td class="text-center"><span class="badge <?= $st[1] ?>" style="font-size:0.7rem;"><?= $st[0] ?></span></td>
                <td class="text-center"><div class="d-flex gap-1 justify-content-center">
                    <a href="voucher-view.php?id=<?= $v['id'] ?>" class="btn btn-sm btn-outline-primary"><svg width="14" height="14" fill="currentColor" viewBox="0 0 20 20"><path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/><path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/></svg></a>
                    <?php if ($v['status'] === 'draft'): ?>
                        <a href="?action=post&id=<?= $v['id'] ?>" class="btn btn-sm btn-outline-success" title="ترحيل"><svg width="14" height="14" fill="currentColor" viewBox="0 0 20 20"><path d="M3.105 3.105a.5.5 0 01.707 0L10 9.293l6.188-6.188a.5.5 0 01.707.707L10.707 10l6.188 6.188a.5.5 0 01-.707.707L10 10.707l-6.188 6.188a.5.5 0 01-.707-.707L9.293 10 3.105 3.812a.5.5 0 010-.707z"/></svg></a>
                        <a href="?delete=<?= $v['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirmDelete('حذف السند؟')"><svg width="14" height="14" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9z" clip-rule="evenodd"/></svg></a>
                    <?php endif; ?>
                    <?php if ($v['status'] !== 'draft' && $v['status'] !== 'cancelled'): ?>
                        <a href="?action=cancel&id=<?= $v['id'] ?>" class="btn btn-sm btn-outline-danger"><svg width="14" height="14" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg></a>
                    <?php endif; ?>
                </div></td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
</div></div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
