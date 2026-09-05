<?php
require_once __DIR__ . '/../includes/header.php';
$search = trim($_GET['search'] ?? '');
$where = $search ? "WHERE entry_number LIKE ? OR description LIKE ?" : "";
$params = $search ? ["%$search%","%$search%"] : [];
$entries = db_fetch_all("SELECT * FROM daily_entries {$where} ORDER BY entry_date DESC", $params);
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete']; $e = db_fetch_one("SELECT status FROM daily_entries WHERE id = ?", [$id]);
    if ($e && $e['status'] === 'draft') { db_delete('daily_entries', 'id = ?', [$id]); flash('success', 'تم حذف القيد'); }
    else flash('error', 'لا يمكن حذف قيد معتمد'); redirect(APP_URL . '/operations/daily-entries.php');
}
if (isset($_GET['action'])) {
    $id = (int)$_GET['id']; $action = $_GET['action'];
    $statusMap = ['post' => 'posted', 'cancel' => 'cancelled', 'reverse' => 'reversed'];
    if (isset($statusMap[$action])) {
        $data = ['status' => $statusMap[$action]];
        if ($action === 'reverse') $data['is_reversed'] = 1;
        db_update('daily_entries', $data, 'id = ?', [$id]);
        db_insert('document_reviews', ['doc_type' => 'daily_entry', 'doc_id' => $id, 'action' => $action, 'reviewed_by' => $_SESSION['user_id'], 'reviewed_at' => date('Y-m-d H:i:s')]);
        flash('success', 'تم ' . ($action === 'post' ? 'الترحيل' : ($action === 'reverse' ? 'العكس' : 'الإلغاء')));
    } redirect(APP_URL . '/operations/daily-entries.php');
}
$STATUS = ['draft' => ['مسودة','bg-slate-100 text-slate-700'], 'posted' => ['مرحّل','bg-emerald-50 text-emerald-700'], 'cancelled' => ['ملغي','bg-rose-50 text-rose-700'], 'reversed' => ['عكسي','bg-amber-50 text-amber-700']];
?>
<div class="card mb-4"><div class="card-body d-flex justify-content-between align-items-center">
    <div><h4 class="fw-bold text-slate-800 mb-1">القيود اليومية</h4><p class="text-sm text-slate-500 mb-0">القيود المحاسبية اليدوية. يجب أن يتوازن المدين مع الدائن.</p></div>
    <a href="daily-entry-form.php" class="btn btn-primary d-flex align-items-center gap-1"><svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20"><path d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"/></svg> إضافة قيد</a>
</div></div>
<form method="GET" class="mb-3 d-flex gap-2"><input type="text" name="search" class="form-control" placeholder="بحث..." value="<?= sanitize($search) ?>"><button class="btn btn-outline-secondary">بحث</button><?php if ($search): ?><a href="daily-entries.php" class="btn btn-outline-secondary">إلغاء</a><?php endif; ?></form>
<div class="card"><div class="card-body p-0"><table class="table mb-0"><thead><tr>
    <th>رقم القيد</th><th>التاريخ</th><th>البيان</th><th class="text-end">مدين</th><th class="text-end">دائن</th><th class="text-center">الحالة</th><th class="text-center">إجراءات</th>
</tr></thead><tbody>
<?php if (empty($entries)): ?><tr><td colspan="7" class="text-center py-4 text-slate-400">لا توجد قيود</td></tr><?php else: foreach ($entries as $e): $st = $STATUS[$e['status']] ?? $STATUS['draft']; ?>
    <tr><td><code dir="ltr" class="bg-slate-100 px-2 py-0.5 rounded"><?= sanitize($e['entry_number']) ?></code></td>
    <td dir="ltr"><?= date('d/m/Y', strtotime($e['entry_date'])) ?></td>
    <td class="text-sm"><?= sanitize($e['description'] ?? '—') ?></td>
    <td class="text-end font-mono text-emerald-700" dir="ltr"><?= number_format($e['total_debit'], 2) ?></td>
    <td class="text-end font-mono text-rose-700" dir="ltr"><?= number_format($e['total_credit'], 2) ?></td>
    <td class="text-center"><span class="badge <?= $st[1] ?>" style="font-size:0.7rem;"><?= $st[0] ?></span></td>
    <td class="text-center"><div class="d-flex gap-1 justify-content-center">
        <a href="daily-entry-view.php?id=<?= $e['id'] ?>" class="btn btn-sm btn-outline-primary"><svg width="14" height="14" fill="currentColor" viewBox="0 0 20 20"><path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/><path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10z" clip-rule="evenodd"/></svg></a>
        <?php if ($e['status'] === 'draft'): ?>
            <a href="?action=post&id=<?= $e['id'] ?>" class="btn btn-sm btn-outline-success"><svg width="14" height="14" fill="currentColor" viewBox="0 0 20 20"><path d="M3.105 2.288a.5.5 0 01.672.672L4.06 7.5h4.94l1.07-4.55a.5.5 0 11.97.24L9.94 7.5H15a.5.5 0 110 1H9.69l-.94 4 4.05.024a.5.5 0 11-.005 1L8.6 13.5l-1.07 4.55a.5.5 0 11-.97-.24L7.6 13.5H2.66l-1.07 4.31a.5.5 0 11-.97-.24L1.69 12.5H0a.5.5 0 110-1h2l.94-4L2.06 7.5H0a.5.5 0 110-1h1.94l.94-4 .225-1.212z"/></svg></a>
            <a href="?delete=<?= $e['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirmDelete('حذف القيد؟')"><svg width="14" height="14" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9z" clip-rule="evenodd"/></svg></a>
        <?php endif; ?>
        <?php if ($e['status'] === 'posted'): ?>
            <a href="?action=reverse&id=<?= $e['id'] ?>" class="btn btn-sm btn-outline-warning"><svg width="14" height="14" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/></svg></a>
        <?php endif; ?>
    </div></td>
    </tr>
<?php endforeach; endif; ?>
</tbody></table></div></div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
