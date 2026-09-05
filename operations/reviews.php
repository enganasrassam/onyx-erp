<?php
require_once __DIR__ . '/../includes/header.php';
$logs = db_fetch_all("SELECT dr.*, u.full_name FROM document_reviews dr LEFT JOIN users u ON dr.reviewed_by=u.id ORDER BY dr.created_at DESC LIMIT 100");
$DOC = ['voucher'=>'سند','daily_entry'=>'قيد يومي','inventory_order'=>'أمر مخزني','transfer'=>'تحويل','adjustment'=>'تسوية','invoice'=>'فاتورة'];
$ACT = ['review'=>'اعتماد','post'=>'ترحيل','unpost'=>'إلغاء ترحيل','cancel'=>'إلغاء','reverse'=>'عكس','mark_paid'=>'تحصيل'];
$pending = ['vouchers' => db_fetch_one("SELECT COUNT(*) as c FROM vouchers WHERE status='draft'")['c'] ?? 0, 'entries' => db_fetch_one("SELECT COUNT(*) as c FROM daily_entries WHERE status='draft'")['c'] ?? 0, 'invoices' => db_fetch_one("SELECT COUNT(*) as c FROM invoices WHERE status='draft'")['c'] ?? 0];
$total = $pending['vouchers'] + $pending['entries'] + $pending['invoices'];
?>
<div class="card mb-4"><div class="card-body">
    <h4 class="fw-bold text-slate-800 mb-1">إدارة المراجعة والترحيلات</h4>
    <p class="text-sm text-slate-500 mb-0">اعتماد وترحيل الوثائق قبل تأثيرها على الحسابات والمخزون.</p>
</div></div>

<div class="row g-3 mb-4">
    <div class="col-md-4"><div class="card text-center"><div class="card-body"><div class="d-flex align-items-center justify-content-center mb-2"><div class="rounded-circle bg-indigo-50 text-indigo-700 d-flex align-items-center justify-content-center" style="width:40px;height:40px;"><svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20"><path d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9z"/></svg></div></div><p class="h3 fw-bold text-slate-800 mb-0"><?= $pending['vouchers'] ?></p><p class="small text-slate-500 mb-0">سندات بانتظار الاعتماد</p></div></div></div>
    <div class="col-md-4"><div class="card text-center"><div class="card-body"><div class="d-flex align-items-center justify-content-center mb-2"><div class="rounded-circle bg-emerald-50 text-emerald-700 d-flex align-items-center justify-content-center" style="width:40px;height:40px;"><svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20"><path d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9z"/></svg></div></div><p class="h3 fw-bold text-slate-800 mb-0"><?= $pending['entries'] ?></p><p class="small text-slate-500 mb-0">قيود يومية</p></div></div></div>
    <div class="col-md-4"><div class="card text-center"><div class="card-body"><div class="d-flex align-items-center justify-content-center mb-2"><div class="rounded-circle bg-rose-50 text-rose-700 d-flex align-items-center justify-content-center" style="width:40px;height:40px;"><svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20"><path d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9z"/></svg></div></div><p class="h3 fw-bold text-slate-800 mb-0"><?= $pending['invoices'] ?></p><p class="small text-slate-500 mb-0">فواتير</p></div></div></div>
</div>

<?php if ($total === 0): ?>
<div class="card"><div class="card-body text-center py-5"><svg width="48" height="48" fill="#10b981" viewBox="0 0 24 24" class="mx-auto mb-2"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg><h5 class="fw-bold text-slate-800">لا توجد وثائق بانتظار الاعتماد</h5><p class="text-sm text-slate-500">جميع الوثائق معتمدة أو مرحّلة</p></div></div>
<?php endif; ?>

<div class="card"><div class="card-header"><h6 class="mb-0">سجل المراجعات (<?= count($logs) ?>)</h6></div>
<div class="card-body p-0"><table class="table mb-0"><thead><tr>
    <th>النوع</th><th>الإجراء</th><th>المستخدم</th><th>ملاحظات</th><th>التاريخ</th>
</tr></thead><tbody>
<?php if (empty($logs)): ?><tr><td colspan="5" class="text-center py-4 text-slate-400">لا توجد مراجعات</td></tr><?php else: foreach ($logs as $log): ?>
    <tr><td><span class="badge bg-indigo-50 text-indigo-700" style="font-size:0.7rem;"><?= $DOC[$log['doc_type']] ?? $log['doc_type'] ?></span></td>
    <td><span class="badge <?= $log['action']==='post'?'bg-emerald-50 text-emerald-700':($log['action']==='cancel'?'bg-rose-50 text-rose-700':'bg-blue-50 text-blue-700') ?>" style="font-size:0.7rem;"><?= $ACT[$log['action']] ?? $log['action'] ?></span></td>
    <td class="small"><?= sanitize($log['full_name'] ?? '—') ?></td>
    <td class="small text-slate-500"><?= sanitize($log['notes'] ?? '—') ?></td>
    <td class="small text-slate-500" dir="ltr"><?= date('Y-m-d H:i', strtotime($log['created_at'])) ?></td></tr>
<?php endforeach; endif; ?>
</tbody></table></div></div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
