<?php
require_once __DIR__ . '/../includes/header.php';
$accounts = db_fetch_all("SELECT id, code, name_ar FROM accounts WHERE is_detail=1 AND active=1 ORDER BY code");
$branches = db_fetch_all("SELECT id, name_ar FROM branches WHERE active=1 ORDER BY name_ar");
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $branchId = (int)($_POST['branch_id'] ?? 0);
    $entryDate = $_POST['entry_date'] ?? date('Y-m-d');
    $description = trim($_POST['description'] ?? '');
    $lineAccounts = $_POST['line_account'] ?? [];
    $lineDebits = $_POST['line_debit'] ?? [];
    $lineCredits = $_POST['line_credit'] ?? [];
    $lineDescriptions = $_POST['line_desc'] ?? [];
    $totalDebit = 0; $totalCredit = 0;
    foreach ($lineDebits as $d) $totalDebit += (float)$d;
    foreach ($lineCredits as $c) $totalCredit += (float)$c;
    $diff = abs($totalDebit - $totalCredit);
    if ($diff > 0.01) { flash('error', "القيد غير متوازن — الفارق {$diff}"); }
    elseif (count($lineAccounts) < 2) { flash('error', 'يجب إضافة بندين على الأقل'); }
    elseif (empty($lineAccounts[0])) { flash('error', 'اختر الحساب لكل بند'); }
    else {
        $entryNumber = generate_number('JE', 'daily_entries', 'entry_number');
        $entryId = db_insert('daily_entries', [
            'entry_number' => $entryNumber, 'branch_id' => $branchId ?: null,
            'entry_date' => $entryDate, 'description' => $description ?: null,
            'total_debit' => $totalDebit, 'total_credit' => $totalCredit,
            'status' => 'draft', 'source' => 'manual', 'created_by' => $_SESSION['user_id'],
        ]);
        $lineNum = 1;
        foreach ($lineAccounts as $idx => $accId) {
            if (empty($accId)) continue;
            db_insert('daily_entry_lines', [
                'daily_entry_id' => $entryId, 'line_number' => $lineNum++, 'account_id' => $accId,
                'debit_local' => (float)($lineDebits[$idx] ?? 0), 'credit_local' => (float)($lineCredits[$idx] ?? 0),
                'description' => $lineDescriptions[$idx] ?? null,
            ]);
        }
        flash('success', 'تم إنشاء القيد اليومي');
        redirect(APP_URL . '/operations/daily-entries.php');
    }
}
?>
<div class="card"><div class="card-header"><h5 class="mb-0">قيد يومي جديد</h5></div><div class="card-body"><form method="POST">
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
    <div class="row g-3 mb-3">
        <div class="col-md-6"><label class="form-label small fw-semibold">الفرع</label>
            <select name="branch_id" class="form-select"><option value="">— اختر —</option><?php foreach ($branches as $b): ?><option value="<?= $b['id'] ?>"><?= sanitize($b['name_ar']) ?></option><?php endforeach; ?></select>
        </div>
        <div class="col-md-6"><label class="form-label small fw-semibold">التاريخ</label>
            <input type="date" name="entry_date" class="form-control" dir="ltr" value="<?= date('Y-m-d') ?>" required>
        </div>
        <div class="col-12"><label class="form-label small fw-semibold">البيان</label>
            <input type="text" name="description" class="form-control" placeholder="بيان القيد">
        </div>
    </div>
    <div class="border rounded mb-3">
        <table class="table table-sm mb-0"><thead><tr>
            <th>الحساب</th><th class="text-end" style="width:130px">مدين</th><th class="text-end" style="width:130px">دائن</th><th style="width:200px">البيان</th><th style="width:40px"></th>
        </tr></thead><tbody id="lines-body">
            <?php for ($i = 0; $i < 2; $i++): ?>
            <tr>
                <td><select name="line_account[]" class="form-select form-select-sm"><option value="">— اختر —</option><?php foreach ($accounts as $a): ?><option value="<?= $a['id'] ?>"><?= sanitize($a['code'] . ' — ' . $a['name_ar']) ?></option><?php endforeach; ?></select></td>
                <td><input type="number" step="0.01" name="line_debit[]" class="form-control form-control-sm font-mono" dir="ltr" value="0" oninput="updateTotals()"></td>
                <td><input type="number" step="0.01" name="line_credit[]" class="form-control form-control-sm font-mono" dir="ltr" value="0" oninput="updateTotals()"></td>
                <td><input type="text" name="line_desc[]" class="form-control form-control-sm"></td>
                <td></td>
            </tr>
            <?php endfor; ?>
        </tbody></table>
        <button type="button" class="btn btn-link btn-sm w-100 text-decoration-none" onclick="addLine()"><svg width="14" height="14" fill="currentColor" viewBox="0 0 20 20"><path d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"/></svg> إضافة بند</button>
    </div>
    <div class="row g-2 mb-3">
        <div class="col-md-4"><div class="bg-emerald-50 rounded p-2 text-center"><p class="small text-slate-500 mb-0">إجمالي مدين</p><p id="total-debit" class="font-mono fw-bold text-emerald-700 mb-0" dir="ltr">0.00</p></div></div>
        <div class="col-md-4"><div class="bg-rose-50 rounded p-2 text-center"><p class="small text-slate-500 mb-0">إجمالي دائن</p><p id="total-credit" class="font-mono fw-bold text-rose-700 mb-0" dir="ltr">0.00</p></div></div>
        <div class="col-md-4"><div id="diff-box" class="bg-emerald-50 rounded p-2 text-center"><p class="small text-slate-500 mb-0">الفارق</p><p id="diff" class="font-mono fw-bold text-emerald-700 mb-0" dir="ltr">0.00</p></div></div>
    </div>
    <div class="d-flex justify-content-end gap-2"><a href="daily-entries.php" class="btn btn-secondary">إلغاء</a><button type="submit" class="btn btn-primary">حفظ القيد</button></div>
</form></div></div>
<script>
function addLine() {
    const tbody = document.getElementById('lines-body');
    const tr = tbody.children[0].cloneNode(true);
    tr.querySelectorAll('input').forEach(i => i.value = '0' === i.type ? '0' : '');
    tr.querySelectorAll('select').forEach(s => s.selectedIndex = 0);
    tr.querySelector('td:last-child').innerHTML = '<button type="button" class="btn btn-sm text-rose-500 p-0" onclick="this.closest(\'tr\').remove(); updateTotals();"><svg width="14" height="14" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9z" clip-rule="evenodd"/></svg></button>';
    tbody.appendChild(tr);
}
function updateTotals() {
    let debit = 0, credit = 0;
    document.querySelectorAll('[name="line_debit[]"]').forEach(i => debit += parseFloat(i.value) || 0);
    document.querySelectorAll('[name="line_credit[]"]').forEach(i => credit += parseFloat(i.value) || 0);
    const diff = Math.abs(debit - credit);
    document.getElementById('total-debit').textContent = debit.toLocaleString('en-US', {minimumFractionDigits: 2});
    document.getElementById('total-credit').textContent = credit.toLocaleString('en-US', {minimumFractionDigits: 2});
    document.getElementById('diff').textContent = diff.toLocaleString('en-US', {minimumFractionDigits: 2});
    const box = document.getElementById('diff-box');
    const diffEl = document.getElementById('diff');
    if (diff < 0.01) { box.className = 'bg-emerald-50 rounded p-2 text-center'; diffEl.className = 'font-mono fw-bold text-emerald-700 mb-0'; }
    else { box.className = 'bg-amber-50 rounded p-2 text-center'; diffEl.className = 'font-mono fw-bold text-amber-700 mb-0'; }
}
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
