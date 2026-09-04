<?php
/**
 * نموذج إنشاء سند (صرف/قبض)
 */
require_once __DIR__ . '/../includes/header.php';

$voucherType = $_GET['type'] ?? 'payment';
$title = $voucherType === 'payment' ? 'سند صرف جديد' : 'سند قبض جديد';

$cashBoxes = db_fetch_all("SELECT id, code, name_ar FROM cash_boxes WHERE active=1 ORDER BY code");
$banks = db_fetch_all("SELECT id, code, name_ar FROM banks WHERE active=1 ORDER BY code");
$currencies = db_fetch_all("SELECT id, code, name_ar, symbol FROM currencies WHERE active=1 ORDER BY code");
$accounts = db_fetch_all("SELECT id, code, name_ar FROM accounts WHERE is_detail=1 AND active=1 ORDER BY code");
$branches = db_fetch_all("SELECT id, code, name_ar FROM branches WHERE active=1 ORDER BY name_ar");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $method = $_POST['method'] ?? 'cash';
    $branchId = (int)($_POST['branch_id'] ?? 0);
    $cashBoxId = (int)($_POST['cash_box_id'] ?? 0);
    $bankId = (int)($_POST['bank_id'] ?? 0);
    $currencyId = (int)($_POST['currency_id'] ?? 0);
    $voucherDate = $_POST['voucher_date'] ?? date('Y-m-d');
    $chequeNumber = trim($_POST['cheque_number'] ?? '');
    $chequeMethod = $_POST['cheque_method'] ?? 'voucher_date';
    $notes = trim($_POST['notes'] ?? '');
    $lineAccounts = $_POST['line_account'] ?? [];
    $lineAmounts = $_POST['line_amount'] ?? [];
    $lineDescriptions = $_POST['line_description'] ?? [];

    $totalAmount = 0;
    foreach ($lineAmounts as $amt) $totalAmount += (float)$amt;

    if ($totalAmount <= 0) {
        flash('error', 'المبلغ يجب أن يكون أكبر من صفر');
    } elseif (empty($lineAccounts[0])) {
        flash('error', 'أضف بندًا واحدًا على الأقل');
    } else {
        $voucherNumber = generate_number($voucherType === 'payment' ? 'PV' : 'RV', 'vouchers');
        $sourceId = $method === 'cash' ? $cashBoxId : $bankId;
        $source = db_fetch_one("SELECT account_id FROM " . ($method === 'cash' ? 'cash_boxes' : 'banks') . " WHERE id = ?", [$sourceId]);
        $sourceAccountId = $source['account_id'] ?? null;

        $data = [
            'voucher_number' => $voucherNumber, 'type' => $voucherType, 'method' => $method,
            'branch_id' => $branchId ?: null,
            'cash_box_id' => $method === 'cash' ? ($cashBoxId ?: null) : null,
            'bank_id' => $method === 'bank' ? ($bankId ?: null) : null,
            'currency_id' => $currencyId, 'exchange_rate' => 1,
            'voucher_date' => $voucherDate, 'amount_local' => $totalAmount,
            'cheque_number' => $method === 'bank' ? ($chequeNumber ?: null) : null,
            'cheque_method' => $method === 'bank' ? $chequeMethod : null,
            'status' => 'draft', 'notes' => $notes ?: null, 'created_by' => $_SESSION['user_id'],
        ];
        $voucherId = db_insert('vouchers', $data);

        // Add lines
        $lineNum = 1;
        foreach ($lineAccounts as $idx => $accId) {
            if (empty($accId)) continue;
            $amount = (float)($lineAmounts[$idx] ?? 0);
            db_insert('voucher_lines', [
                'voucher_id' => $voucherId, 'line_number' => $lineNum++, 'account_id' => $accId,
                'debit_local' => $voucherType === 'payment' ? $amount : 0,
                'credit_local' => $voucherType === 'receipt' ? $amount : 0,
                'description' => $lineDescriptions[$idx] ?? null,
            ]);
        }
        // Auto-add source line (cash/bank)
        if ($sourceAccountId) {
            db_insert('voucher_lines', [
                'voucher_id' => $voucherId, 'line_number' => $lineNum, 'account_id' => $sourceAccountId,
                'debit_local' => $voucherType === 'receipt' ? $totalAmount : 0,
                'credit_local' => $voucherType === 'payment' ? $totalAmount : 0,
                'description' => $method === 'cash' ? 'الصندوق' : 'البنك',
            ]);
        }

        flash('success', $voucherType === 'payment' ? 'تم إنشاء سند الصرف' : 'تم إنشاء سند القبض');
        redirect(APP_URL . "/operations/{$voucherType}-vouchers.php");
    }
}
?>

<div class="card"><div class="card-header"><h5 class="mb-0"><?= $title ?></h5></div>
<div class="card-body"><form method="POST">
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
    <div class="row g-3 mb-3">
        <div class="col-md-3"><label class="form-label small fw-semibold">الطريقة</label>
            <select name="method" id="method" class="form-select" onchange="toggleSource()">
                <option value="cash">نقدًا (صندوق)</option><option value="bank">بنكي (شيك)</option>
            </select>
        </div>
        <div class="col-md-3"><label class="form-label small fw-semibold">الفرع</label>
            <select name="branch_id" class="form-select">
                <option value="">— اختر —</option>
                <?php foreach ($branches as $b): ?><option value="<?= $b['id'] ?>"><?= sanitize($b['code'] . ' — ' . $b['name_ar']) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3"><label class="form-label small fw-semibold">العملة</label>
            <select name="currency_id" class="form-select" required>
                <?php foreach ($currencies as $c): ?><option value="<?= $c['id'] ?>"><?= sanitize($c['code'] . ' — ' . $c['name_ar']) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3"><label class="form-label small fw-semibold">التاريخ</label>
            <input type="date" name="voucher_date" class="form-control" dir="ltr" value="<?= date('Y-m-d') ?>" required>
        </div>
    </div>

    <div id="cash-box-row" class="row g-3 mb-3">
        <div class="col-md-6"><label class="form-label small fw-semibold">الصندوق *</label>
            <select name="cash_box_id" class="form-select">
                <option value="">— اختر —</option>
                <?php foreach ($cashBoxes as $cb): ?><option value="<?= $cb['id'] ?>"><?= sanitize($cb['code'] . ' — ' . $cb['name_ar']) ?></option><?php endforeach; ?>
            </select>
        </div>
    </div>

    <div id="bank-row" class="row g-3 mb-3" style="display:none;">
        <div class="col-md-4"><label class="form-label small fw-semibold">البنك *</label>
            <select name="bank_id" class="form-select">
                <option value="">— اختر —</option>
                <?php foreach ($banks as $bk): ?><option value="<?= $bk['id'] ?>"><?= sanitize($bk['code'] . ' — ' . $bk['name_ar']) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4"><label class="form-label small fw-semibold">رقم الشيك</label>
            <input type="text" name="cheque_number" class="form-control font-mono" dir="ltr">
        </div>
        <div class="col-md-4"><label class="form-label small fw-semibold">طريقة ترحيل الشيك</label>
            <select name="cheque_method" class="form-select">
                <option value="voucher_date">بتاريخ السند</option>
                <option value="due_date">بتاريخ الاستحقاق</option>
                <option value="notes_auto">توسيط أوراق دفع/قبض (آلي)</option>
                <option value="manual">استحقاق يدوي</option>
            </select>
        </div>
    </div>

    <div class="border rounded mb-3">
        <div class="bg-slate-50 px-2 py-1.5 small fw-bold">البنود (الحسابات <?= $voucherType === 'payment' ? 'المدينة' : 'الدائنة' ?>)</div>
        <table class="table table-sm mb-0">
            <thead><tr><th>الحساب</th><th class="text-end" style="width:150px">المبلغ</th><th style="width:250px">البيان</th><th style="width:40px"></th></tr></thead>
            <tbody id="lines-body">
                <tr>
                    <td><select name="line_account[]" class="form-select form-select-sm">
                        <option value="">— اختر —</option>
                        <?php foreach ($accounts as $a): ?><option value="<?= $a['id'] ?>"><?= sanitize($a['code'] . ' — ' . $a['name_ar']) ?></option><?php endforeach; ?>
                    </select></td>
                    <td><input type="number" step="0.01" name="line_amount[]" class="form-control form-control-sm font-mono" dir="ltr" oninput="updateTotal()" value="0"></td>
                    <td><input type="text" name="line_description[]" class="form-control form-control-sm"></td>
                    <td></td>
                </tr>
            </tbody>
        </table>
        <button type="button" class="btn btn-link btn-sm w-100 text-decoration-none" onclick="addLine()">
            <svg width="14" height="14" fill="currentColor" viewBox="0 0 20 20" class="inline"><path d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"/></svg>
            إضافة بند
        </button>
    </div>

    <div class="bg-gradient-to-l from-indigo-500 to-purple-600 text-white rounded p-3 d-flex justify-content-between mb-3">
        <span class="small">إجمالي مبلغ السند</span>
        <span id="total-amount" class="font-mono fw-bold fs-5" dir="ltr">0.00</span>
    </div>

    <div class="mb-3"><label class="form-label small fw-semibold">ملاحظات</label>
        <textarea name="notes" class="form-control" rows="2"></textarea>
    </div>

    <div class="d-flex justify-content-end gap-2">
        <a href="<?= APP_URL ?>/operations/<?= $voucherType ?>-vouchers.php" class="btn btn-secondary">إلغاء</a>
        <button type="submit" class="btn btn-primary">حفظ السند</button>
    </div>
</form></div></div>

<script>
function toggleSource() {
    const method = document.getElementById('method').value;
    document.getElementById('cash-box-row').style.display = method === 'cash' ? '' : 'none';
    document.getElementById('bank-row').style.display = method === 'bank' ? '' : 'none';
}
function addLine() {
    const tbody = document.getElementById('lines-body');
    const tr = tbody.children[0].cloneNode(true);
    tr.querySelectorAll('input').forEach(i => { if (i.type === 'number') i.value = '0'; else i.value = ''; });
    tr.querySelectorAll('select').forEach(s => s.selectedIndex = 0);
    const btn = tr.querySelector('td:last-child');
    btn.innerHTML = '<button type="button" class="btn btn-sm text-rose-500 p-0" onclick="this.closest(\'tr\').remove(); updateTotal();"><svg width="14" height="14" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9z" clip-rule="evenodd"/></svg></button>';
    tbody.appendChild(tr);
}
function updateTotal() {
    let total = 0;
    document.querySelectorAll('[name="line_amount[]"]').forEach(i => total += parseFloat(i.value) || 0);
    document.getElementById('total-amount').textContent = total.toLocaleString('en-US', {minimumFractionDigits: 2});
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
