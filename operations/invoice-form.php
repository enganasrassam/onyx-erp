<?php
require_once __DIR__ . '/../includes/header.php';
$invoiceType = $_GET['type'] ?? 'purchase';
$isPurchase = str_starts_with($invoiceType, 'purchase');
$suppliers = db_fetch_all("SELECT id, code, name_ar FROM suppliers WHERE active=1 ORDER BY code");
$customers = db_fetch_all("SELECT id, code, name_ar FROM customers WHERE active=1 ORDER BY code");
$currencies = db_fetch_all("SELECT id, code, name_ar FROM currencies WHERE active=1 ORDER BY code");
$warehouses = db_fetch_all("SELECT id, code, name_ar FROM warehouses WHERE active=1 ORDER BY code");
$items = db_fetch_all("SELECT id, code, name_ar FROM items WHERE active=1 ORDER BY code");
$units = db_fetch_all("SELECT id, name_ar FROM units WHERE active=1 ORDER BY name_ar");
$cashBoxes = db_fetch_all("SELECT id, code, name_ar FROM cash_boxes WHERE active=1 ORDER BY code");
$banks = db_fetch_all("SELECT id, code, name_ar FROM banks WHERE active=1 ORDER BY code");
$partyOptions = $isPurchase ? $suppliers : $customers;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $partyId = (int)($_POST['party_id'] ?? 0);
    $warehouseId = (int)($_POST['warehouse_id'] ?? 0);
    $currencyId = (int)($_POST['currency_id'] ?? 0);
    $invoiceDate = $_POST['invoice_date'] ?? date('Y-m-d');
    $paymentMethod = $_POST['payment_method'] ?? 'credit';
    $cashBoxId = (int)($_POST['cash_box_id'] ?? 0);
    $bankId = (int)($_POST['bank_id'] ?? 0);
    $discountPct = (float)($_POST['discount_pct'] ?? 0);
    $taxPct = (float)($_POST['tax_pct'] ?? 0);
    $additionalCosts = (float)($_POST['additional_costs'] ?? 0);
    $notes = trim($_POST['notes'] ?? '');
    $lineItems = $_POST['line_item'] ?? [];
    $lineUnits = $_POST['line_unit'] ?? [];
    $lineQtys = $_POST['line_qty'] ?? [];
    $linePrices = $_POST['line_price'] ?? [];
    $lineDiscounts = $_POST['line_discount'] ?? [];
    $subtotal = 0;
    $processedLines = [];
    foreach ($lineItems as $idx => $itemId) {
        if (empty($itemId)) continue;
        $qty = (float)($lineQtys[$idx] ?? 0);
        $price = (float)($linePrices[$idx] ?? 0);
        $disc = (float)($lineDiscounts[$idx] ?? 0);
        $total = $qty * $price - $disc;
        $subtotal += $total;
        $processedLines[] = ['item_id'=>$itemId, 'unit_id'=>$lineUnits[$idx] ?? null, 'quantity'=>$qty, 'unit_price'=>$price, 'discount_amount'=>$disc, 'total'=>$total];
    }
    if (empty($processedLines)) { flash('error', 'أضف صنفًا واحدًا على الأقل'); }
    else {
        $party = db_fetch_one("SELECT name_ar FROM " . ($isPurchase ? 'suppliers' : 'customers') . " WHERE id = ?", [$partyId]);
        $discountAmount = $subtotal * ($discountPct / 100);
        $afterDiscount = $subtotal - $discountAmount;
        $taxAmount = $afterDiscount * ($taxPct / 100);
        $totalLocal = $afterDiscount + $taxAmount + $additionalCosts;
        $prefix = ['purchase'=>'PI','sales'=>'SI','purchase_return'=>'PR','sales_return'=>'SR','purchase_foreign'=>'PF'][$invoiceType] ?? 'INV';
        $invoiceNumber = generate_number($prefix, 'invoices', 'invoice_number');
        $invoiceId = db_insert('invoices', [
            'invoice_number' => $invoiceNumber, 'type' => $invoiceType,
            'party_type' => $isPurchase ? 'supplier' : 'customer', 'party_id' => $partyId,
            'party_name' => $party['name_ar'] ?? null,
            'branch_id' => null, 'cash_box_id' => $paymentMethod==='cash'?($cashBoxId?:null):null,
            'bank_id' => $paymentMethod==='bank'?($bankId?:null):null,
            'warehouse_id' => $warehouseId ?: null, 'currency_id' => $currencyId,
            'exchange_rate' => 1, 'invoice_date' => $invoiceDate,
            'payment_method' => $paymentMethod, 'subtotal' => $subtotal,
            'discount' => $discountAmount, 'discount_pct' => $discountPct,
            'tax_pct' => $taxPct, 'tax_amount' => $taxAmount,
            'additional_costs' => $additionalCosts, 'total_local' => $totalLocal,
            'total_foreign' => $totalLocal, 'paid_amount' => 0, 'status' => 'draft',
            'notes' => $notes ?: null, 'created_by' => $_SESSION['user_id'],
        ]);
        $lineNum = 1;
        foreach ($processedLines as $l) {
            db_insert('invoice_lines', [
                'invoice_id'=>$invoiceId, 'line_number'=>$lineNum++, 'item_id'=>$l['item_id'],
                'unit_id'=>$l['unit_id'], 'quantity'=>$l['quantity'], 'unit_price'=>$l['unit_price'],
                'discount_amount'=>$l['discount_amount'], 'total'=>$l['total'],
            ]);
        }
        flash('success', 'تم إنشاء الفاتورة');
        redirect(APP_URL . "/operations/{$invoiceType}-invoices.php");
    }
}
?>
<div class="card"><div class="card-header"><h5 class="mb-0"><?= $isPurchase ? 'فاتورة شراء جديدة' : 'فاتورة بيع جديدة' ?></h5></div>
<div class="card-body"><form method="POST">
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
    <div class="row g-3 mb-3">
        <div class="col-md-3"><label class="form-label small fw-semibold"><?= $isPurchase ? 'المورد' : 'العميل' ?> *</label>
            <select name="party_id" class="form-select" required><option value="">— اختر —</option><?php foreach ($partyOptions as $p): ?><option value="<?= $p['id'] ?>"><?= sanitize($p['code'] . ' — ' . $p['name_ar']) ?></option><?php endforeach; ?></select>
        </div>
        <div class="col-md-3"><label class="form-label small fw-semibold">المخزن</label>
            <select name="warehouse_id" class="form-select"><option value="">— اختر —</option><?php foreach ($warehouses as $w): ?><option value="<?= $w['id'] ?>"><?= sanitize($w['code'] . ' — ' . $w['name_ar']) ?></option><?php endforeach; ?></select>
        </div>
        <div class="col-md-3"><label class="form-label small fw-semibold">العملة</label>
            <select name="currency_id" class="form-select" required><?php foreach ($currencies as $c): ?><option value="<?= $c['id'] ?>"><?= sanitize($c['code'] . ' — ' . $c['name_ar']) ?></option><?php endforeach; ?></select>
        </div>
        <div class="col-md-3"><label class="form-label small fw-semibold">التاريخ</label>
            <input type="date" name="invoice_date" class="form-control" dir="ltr" value="<?= date('Y-m-d') ?>" required>
        </div>
        <div class="col-md-3"><label class="form-label small fw-semibold">طريقة الدفع</label>
            <select name="payment_method" class="form-select" onchange="togglePayment()"><option value="credit">آجل</option><option value="cash">نقدًا</option><option value="bank">شيك</option></select>
        </div>
        <div class="col-md-3" id="cash-box-div" style="display:none;"><label class="form-label small fw-semibold">الصندوق</label>
            <select name="cash_box_id" class="form-select"><option value="">— اختر —</option><?php foreach ($cashBoxes as $cb): ?><option value="<?= $cb['id'] ?>"><?= sanitize($cb['code'] . ' — ' . $cb['name_ar']) ?></option><?php endforeach; ?></select>
        </div>
        <div class="col-md-3" id="bank-div" style="display:none;"><label class="form-label small fw-semibold">البنك</label>
            <select name="bank_id" class="form-select"><option value="">— اختر —</option><?php foreach ($banks as $bk): ?><option value="<?= $bk['id'] ?>"><?= sanitize($bk['code'] . ' — ' . $bk['name_ar']) ?></option><?php endforeach; ?></select>
        </div>
        <div class="col-md-3"><label class="form-label small fw-semibold">نسبة الخصم %</label>
            <input type="number" step="0.01" name="discount_pct" class="form-control font-mono" dir="ltr" value="0" oninput="updateTotals()">
        </div>
        <div class="col-md-3"><label class="form-label small fw-semibold">نسبة الضريبة %</label>
            <input type="number" step="0.01" name="tax_pct" class="form-control font-mono" dir="ltr" value="0" oninput="updateTotals()">
        </div>
    </div>
    <div class="border rounded mb-3">
        <table class="table table-sm mb-0"><thead><tr>
            <th>الصنف</th><th style="width:120px">الوحدة</th><th class="text-end" style="width:90px">الكمية</th><th class="text-end" style="width:110px">سعر الوحدة</th><th class="text-end" style="width:90px">خصم</th><th class="text-end" style="width:110px">الإجمالي</th><th style="width:40px"></th>
        </tr></thead><tbody id="lines-body">
            <tr>
                <td><select name="line_item[]" class="form-select form-select-sm"><option value="">— اختر —</option><?php foreach ($items as $i): ?><option value="<?= $i['id'] ?>"><?= sanitize($i['code'] . ' — ' . $i['name_ar']) ?></option><?php endforeach; ?></select></td>
                <td><select name="line_unit[]" class="form-select form-select-sm"><option value="">—</option><?php foreach ($units as $u): ?><option value="<?= $u['id'] ?>"><?= sanitize($u['name_ar']) ?></option><?php endforeach; ?></select></td>
                <td><input type="number" step="0.01" name="line_qty[]" class="form-control form-control-sm font-mono" dir="ltr" value="1" oninput="updateTotals()"></td>
                <td><input type="number" step="0.01" name="line_price[]" class="form-control form-control-sm font-mono" dir="ltr" value="0" oninput="updateTotals()"></td>
                <td><input type="number" step="0.01" name="line_discount[]" class="form-control form-control-sm font-mono" dir="ltr" value="0" oninput="updateTotals()"></td>
                <td class="text-end font-mono line-total" dir="ltr">0.00</td>
                <td></td>
            </tr>
        </tbody></table>
        <button type="button" class="btn btn-link btn-sm w-100 text-decoration-none" onclick="addLine()"><svg width="14" height="14" fill="currentColor" viewBox="0 0 20 20"><path d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"/></svg> إضافة صنف</button>
    </div>
    <div class="row g-2 mb-3">
        <div class="col-md-3"><div class="bg-slate-50 rounded p-2 text-center"><p class="small text-slate-500 mb-0">المجموع الفرعي</p><p id="subtotal" class="font-mono fw-bold mb-0" dir="ltr">0.00</p></div></div>
        <div class="col-md-3"><div class="bg-amber-50 rounded p-2 text-center"><p class="small text-slate-500 mb-0">الخصم</p><p id="discount" class="font-mono fw-bold text-amber-700 mb-0" dir="ltr">0.00</p></div></div>
        <div class="col-md-3"><div class="bg-blue-50 rounded p-2 text-center"><p class="small text-slate-500 mb-0">الضريبة</p><p id="tax" class="font-mono fw-bold text-blue-700 mb-0" dir="ltr">0.00</p></div></div>
        <div class="col-md-3"><div class="bg-gradient-to-l from-indigo-500 to-purple-600 text-white rounded p-2 text-center"><p class="small opacity-80 mb-0">الإجمالي النهائي</p><p id="grand-total" class="font-mono fw-bold mb-0" dir="ltr">0.00</p></div></div>
    </div>
    <div class="mb-3"><label class="form-label small fw-semibold">ملاحظات</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
    <div class="d-flex justify-content-end gap-2"><a href="<?= APP_URL ?>/operations/<?= $invoiceType ?>-invoices.php" class="btn btn-secondary">إلغاء</a><button type="submit" class="btn btn-primary">حفظ الفاتورة</button></div>
</form></div></div>
<script>
function togglePayment() {
    const method = document.querySelector('[name="payment_method"]').value;
    document.getElementById('cash-box-div').style.display = method === 'cash' ? '' : 'none';
    document.getElementById('bank-div').style.display = method === 'bank' ? '' : 'none';
}
function addLine() {
    const tbody = document.getElementById('lines-body');
    const tr = tbody.children[0].cloneNode(true);
    tr.querySelectorAll('input').forEach(i => i.value = '0');
    tr.querySelectorAll('select').forEach(s => s.selectedIndex = 0);
    tr.querySelector('.line-total').textContent = '0.00';
    tr.querySelector('td:last-child').innerHTML = '<button type="button" class="btn btn-sm text-rose-500 p-0" onclick="this.closest(\'tr\').remove(); updateTotals();"><svg width="14" height="14" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9z" clip-rule="evenodd"/></svg></button>';
    tbody.appendChild(tr);
}
function updateTotals() {
    let subtotal = 0;
    document.querySelectorAll('#lines-body tr').forEach(tr => {
        const qty = parseFloat(tr.querySelector('[name="line_qty[]"]').value) || 0;
        const price = parseFloat(tr.querySelector('[name="line_price[]"]').value) || 0;
        const disc = parseFloat(tr.querySelector('[name="line_discount[]"]').value) || 0;
        const total = qty * price - disc;
        tr.querySelector('.line-total').textContent = total.toLocaleString('en-US', {minimumFractionDigits:2});
        subtotal += total;
    });
    const discPct = parseFloat(document.querySelector('[name="discount_pct"]').value) || 0;
    const taxPct = parseFloat(document.querySelector('[name="tax_pct"]').value) || 0;
    const discAmt = subtotal * (discPct / 100);
    const afterDisc = subtotal - discAmt;
    const taxAmt = afterDisc * (taxPct / 100);
    const grand = afterDisc + taxAmt;
    document.getElementById('subtotal').textContent = subtotal.toLocaleString('en-US', {minimumFractionDigits:2});
    document.getElementById('discount').textContent = discAmt.toLocaleString('en-US', {minimumFractionDigits:2});
    document.getElementById('tax').textContent = taxAmt.toLocaleString('en-US', {minimumFractionDigits:2});
    document.getElementById('grand-total').textContent = grand.toLocaleString('en-US', {minimumFractionDigits:2});
}
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
