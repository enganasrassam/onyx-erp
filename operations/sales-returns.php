<?php
require_once __DIR__ . '/../includes/header.php';
$invoiceType = 'sales_return';
$search = trim($_GET['search'] ?? '');
$where = "WHERE type = ?"; $params = [$invoiceType];
if ($search) { $where .= " AND (invoice_number LIKE ? OR party_name LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
$invoices = db_fetch_all("SELECT i.*, c.code as currency_code FROM invoices i LEFT JOIN currencies c ON i.currency_id=c.id {$where} ORDER BY i.invoice_date DESC", $params);
if (isset($_GET['delete'])) { $id = (int)$_GET['delete']; $inv = db_fetch_one("SELECT status FROM invoices WHERE id=?",[$id]); if ($inv && $inv['status']==='draft') { db_delete('invoices','id=?',[$id]); flash('success','تم الحذف'); } else flash('error','لا يمكن حذف فاتورة معتمدة'); redirect(APP_URL . '/operations/sales-returns.php'); }
if (isset($_GET['action'])) { $id=(int)$_GET['id']; $action=$_GET['action']; $sm=['post'=>'posted','cancel'=>'cancelled','mark_paid'=>'paid']; if (isset($sm[$action])) { db_update('invoices',['status'=>$sm[$action]],'id=?',[$id]); db_insert('document_reviews',['doc_type'=>'invoice','doc_id'=>$id,'action'=>$action,'reviewed_by'=>$_SESSION['user_id'],'reviewed_at'=>date('Y-m-d H:i:s')]); flash('success','تم '.($action==='post'?'الترحيل':($action==='cancel'?'الإلغاء':'التحصيل'))); } redirect(APP_URL . '/operations/sales-returns.php'); }
$STATUS = ['draft'=>['مسودة','bg-slate-100 text-slate-700'],'posted'=>['مرحّل','bg-emerald-50 text-emerald-700'],'cancelled'=>['ملغي','bg-rose-50 text-rose-700'],'paid'=>['مدفوعة','bg-blue-50 text-blue-700'],'partial'=>['جزئي','bg-amber-50 text-amber-700']];
?>
<div class="card mb-4"><div class="card-body d-flex justify-content-between align-items-center">
    <div><h4 class="fw-bold text-slate-800 mb-1">فواتير مردود المبيعات</h4><p class="text-sm text-slate-500 mb-0">فواتير مردود المبيعات من العملاء.</p></div>
    <a href="invoice-form.php?type=sales_return" class="btn btn-primary d-flex align-items-center gap-1"><svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20"><path d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"/></svg> إضافة فاتورة</a>
</div></div>
<form method="GET" class="mb-3 d-flex gap-2"><input type="text" name="search" class="form-control" placeholder="بحث..." value="<?= sanitize($search) ?>"><button class="btn btn-outline-secondary">بحث</button><?php if ($search): ?><a href="sales-returns.php" class="btn btn-outline-secondary">إلغاء</a><?php endif; ?></form>
<div class="card"><div class="card-body p-0"><table class="table mb-0"><thead><tr>
    <th>رقم الفاتورة</th><th>التاريخ</th><th>العميل</th><th class="text-end">الإجمالي</th><th class="text-center">العملة</th><th class="text-center">الحالة</th><th class="text-center">إجراءات</th>
</tr></thead><tbody>
<?php if (empty($invoices)): ?><tr><td colspan="7" class="text-center py-4 text-slate-400">لا توجد فواتير</td></tr><?php else: foreach ($invoices as $inv): $st=$STATUS[$inv['status']]??$STATUS['draft']; ?>
    <tr><td><code dir="ltr" class="bg-slate-100 px-2 py-0.5 rounded"><?= sanitize($inv['invoice_number']) ?></code></td>
    <td dir="ltr"><?= date('d/m/Y', strtotime($inv['invoice_date'])) ?></td>
    <td><?= sanitize($inv['party_name'] ?? '—') ?></td>
    <td class="text-end font-mono" dir="ltr"><?= number_format($inv['total_local'], 2) ?></td>
    <td class="text-center"><span class="badge bg-slate-100" style="font-size:0.7rem;"><?= sanitize($inv['currency_code']) ?></span></td>
    <td class="text-center"><span class="badge <?= $st[1] ?>" style="font-size:0.7rem;"><?= $st[0] ?></span></td>
    <td class="text-center"><div class="d-flex gap-1 justify-content-center">
        <a href="invoice-view.php?id=<?= $inv['id'] ?>" class="btn btn-sm btn-outline-primary"><svg width="14" height="14" fill="currentColor" viewBox="0 0 20 20"><path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/><path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10z" clip-rule="evenodd"/></svg></a>
        <?php if ($inv['status']==='draft'): ?>
            <a href="?action=post&id=<?= $inv['id'] ?>" class="btn btn-sm btn-outline-success"><svg width="14" height="14" fill="currentColor" viewBox="0 0 20 20"><path d="M3.105 2.288a.5.5 0 01.672.672L4.06 7.5h4.94l1.07-4.55a.5.5 0 11.97.24L9.94 7.5H15a.5.5 0 110 1H9.69l-.94 4 4.05.024a.5.5 0 11-.005 1L8.6 13.5l-1.07 4.55a.5.5 0 11-.97-.24L7.6 13.5H2.66l-1.07 4.31a.5.5 0 11-.97-.24L1.69 12.5H0a.5.5 0 110-1h2l.94-4L2.06 7.5H0a.5.5 0 110-1h1.94l.94-4 .225-1.212z"/></svg></a>
            <a href="?delete=<?= $inv['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirmDelete('حذف الفاتورة؟')"><svg width="14" height="14" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9z" clip-rule="evenodd"/></svg></a>
        <?php endif; ?>
        <?php if ($inv['status']!=='draft' && $inv['status']!=='cancelled'): ?>
            <a href="?action=cancel&id=<?= $inv['id'] ?>" class="btn btn-sm btn-outline-danger"><svg width="14" height="14" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg></a>
        <?php endif; ?>
    </div></td></tr>
<?php endforeach; endif; ?>
</tbody></table></div></div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
