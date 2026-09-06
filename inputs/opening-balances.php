<?php
/**
 * الأرصدة الافتتاحية — مطابقة لنظام أونكس ERP
 * 
 * من الكتاب (صفحة 52-53):
 * - جدول يحتوي على: رقم الحساب، الحساب التحليلي، اسم الحساب، العملة،
 *   مدين محلي، دائن محلي، مدين أجنبي، دائن أجنبي
 * - إجمالي مدين وإجمالي دائن في الأسفل
 * - الفارق = إجمالي مدين - إجمالي دائن
 * - رأس المال = الفارق بين المدين والدائن
 * - يجب أن يكون الفارق صفر (متوازن)
 * - زر البحث (مرتين) لاستعراض الأرصدة
 * - زر الإضافة لإضافة رصيد جديد
 * - زر التعديل لتعديل الرصيد
 */
require_once __DIR__ . '/../includes/header.php';

$search = trim($_GET['search'] ?? '');
$editingId = (int)($_GET['edit'] ?? 0);

// ====== معالجة الحذف ======
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    db_delete('opening_balances', 'id = ?', [$id]);
    flash('success', 'تم حذف الرصيد الافتتاحي');
    redirect(APP_URL . '/inputs/opening-balances.php');
}

// ====== معالجة الإضافة/التعديل ======
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $accountId = (int)($_POST['account_id'] ?? 0);
    $currencyId = (int)($_POST['currency_id'] ?? 0);
    $debitLocal = (float)($_POST['debit_local'] ?? 0);
    $creditLocal = (float)($_POST['credit_local'] ?? 0);
    $debitForeign = (float)($_POST['debit_foreign'] ?? 0);
    $creditForeign = (float)($_POST['credit_foreign'] ?? 0);
    $notes = trim($_POST['notes'] ?? '');
    $editId = (int)($_POST['edit_id'] ?? 0);

    if (!$accountId || !$currencyId) {
        flash('error', 'يجب اختيار الحساب والعملة');
    } else {
        // التحقق من الطبيعة: الحساب إما مدين أو دائن وليس الاثنين
        if ($debitLocal > 0 && $creditLocal > 0) {
            flash('error', 'لا يمكن أن يكون الحساب مدينًا ودائنًا في نفس الوقت');
        } else {
            $data = [
                'account_id' => $accountId,
                'currency_id' => $currencyId,
                'debit_local' => $debitLocal,
                'credit_local' => $creditLocal,
                'debit_foreign' => $debitForeign,
                'credit_foreign' => $creditForeign,
                'notes' => $notes ?: null,
            ];
            
            if ($editId) {
                db_update('opening_balances', $data, 'id = ?', [$editId]);
                flash('success', 'تم تحديث الرصيد الافتتاحي');
            } else {
                // التحقق من عدم التكرار
                $existing = db_fetch_one("SELECT id FROM opening_balances WHERE account_id = ? AND currency_id = ?", [$accountId, $currencyId]);
                if ($existing) {
                    // تحديث الموجود
                    db_update('opening_balances', $data, 'id = ?', [$existing['id']]);
                    flash('success', 'تم تحديث الرصيد الافتتاحي');
                } else {
                    db_insert('opening_balances', $data);
                    flash('success', 'تمت إضافة الرصيد الافتتاحي');
                }
            }
        }
    }
    redirect(APP_URL . '/inputs/opening-balances.php');
}

// ====== جلب البيانات ======
$balances = db_fetch_all("
    SELECT ob.*, 
           a.code as account_code, 
           a.name_ar as account_name,
           c.code as currency_code,
           c.name_ar as currency_name,
           c.symbol as currency_symbol,
           c.is_foreign
    FROM opening_balances ob
    LEFT JOIN accounts a ON ob.account_id = a.id
    LEFT JOIN currencies c ON ob.currency_id = c.id
    ORDER BY a.code ASC
");

// ====== حساب الإجماليات ======
$totalDebitLocal = 0;
$totalCreditLocal = 0;
$totalDebitForeign = 0;
$totalCreditForeign = 0;
foreach ($balances as $b) {
    $totalDebitLocal += $b['debit_local'];
    $totalCreditLocal += $b['credit_local'];
    $totalDebitForeign += $b['debit_foreign'];
    $totalCreditForeign += $b['credit_foreign'];
}
$diffLocal = $totalDebitLocal - $totalCreditLocal;
$diffForeign = $totalDebitForeign - $totalCreditForeign;
$capital = $diffLocal; // رأس المال = الفارق
$isBalanced = abs($diffLocal) < 0.01 && abs($diffForeign) < 0.01;

// ====== جلب الخيارات للنموذج ======
$accounts = db_fetch_all("SELECT id, code, name_ar, account_type FROM accounts WHERE is_detail = 1 AND active = 1 ORDER BY code");
$currencies = db_fetch_all("SELECT id, code, name_ar, symbol, is_foreign FROM currencies WHERE active = 1 ORDER BY code");

// إذا كان في وضع التعديل
$editBalance = null;
if ($editingId) {
    $editBalance = db_fetch_one("SELECT * FROM opening_balances WHERE id = ?", [$editingId]);
}
?>

<!-- عنوان الشاشة -->
<div class="card mb-2">
    <div class="card-body" style="padding:10px 14px">
        <h4 style="font-size:15px;font-weight:700;color:var(--onyx-text);margin:0">الأرصدة الافتتاحية</h4>
        <p style="font-size:11px;color:var(--onyx-text-muted);margin:2px 0 0">إدخال أرصدة بداية النشاط — رأس المال = إجمالي المدين − إجمالي الدائن</p>
    </div>
</div>

<!-- شريط الأدوات -->
<div class="onyx-toolbar">
    <button type="button" class="toolbar-btn toolbar-btn-primary" onclick="toggleForm()">
        <svg width="14" height="14" fill="currentColor" viewBox="0 0 20 20"><path d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"/></svg>
        إضافة رصيد
    </button>
    <div class="toolbar-divider"></div>
    <button class="toolbar-btn" onclick="window.location.reload()">
        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
        استعراض (بحث مرتين)
    </button>
    <button class="toolbar-btn" onclick="window.print()">
        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2z"/></svg>
        طباعة
    </button>
</div>

<!-- ====== بطاقات الإجماليات (مثل أونكس) ====== -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:8px;margin-bottom:12px">
    <div style="background:#fff;border:1px solid var(--onyx-border-light);border-radius:6px;padding:10px;text-align:center">
        <p style="font-size:11px;color:var(--onyx-text-muted);margin:0">إجمالي مدين (محلي)</p>
        <p style="font-size:18px;font-weight:800;color:var(--onyx-green);margin:2px 0 0" dir="ltr"><?= number_format($totalDebitLocal, 2) ?></p>
    </div>
    <div style="background:#fff;border:1px solid var(--onyx-border-light);border-radius:6px;padding:10px;text-align:center">
        <p style="font-size:11px;color:var(--onyx-text-muted);margin:0">إجمالي دائن (محلي)</p>
        <p style="font-size:18px;font-weight:800;color:var(--onyx-red);margin:2px 0 0" dir="ltr"><?= number_format($totalCreditLocal, 2) ?></p>
    </div>
    <div style="background:#fff;border:1px solid var(--onyx-border-light);border-radius:6px;padding:10px;text-align:center">
        <p style="font-size:11px;color:var(--onyx-text-muted);margin:0">إجمالي مدين (أجنبي)</p>
        <p style="font-size:18px;font-weight:800;color:var(--onyx-green);margin:2px 0 0" dir="ltr"><?= number_format($totalDebitForeign, 2) ?></p>
    </div>
    <div style="background:#fff;border:1px solid var(--onyx-border-light);border-radius:6px;padding:10px;text-align:center">
        <p style="font-size:11px;color:var(--onyx-text-muted);margin:0">إجمالي دائن (أجنبي)</p>
        <p style="font-size:18px;font-weight:800;color:var(--onyx-red);margin:2px 0 0" dir="ltr"><?= number_format($totalCreditForeign, 2) ?></p>
    </div>
    <div style="background:<?= $isBalanced ? '#dcfce7' : '#fef3c7' ?>;border:1px solid <?= $isBalanced ? '#86efac' : '#fcd34d' ?>;border-radius:6px;padding:10px;text-align:center">
        <p style="font-size:11px;color:<?= $isBalanced ? 'var(--onyx-green)' : 'var(--onyx-amber)' ?>;margin:0">الفارق</p>
        <p style="font-size:18px;font-weight:800;color:<?= $isBalanced ? 'var(--onyx-green)' : 'var(--onyx-amber)' ?>;margin:2px 0 0" dir="ltr"><?= number_format(abs($diffLocal), 2) ?></p>
        <p style="font-size:9px;color:<?= $isBalanced ? 'var(--onyx-green)' : 'var(--onyx-amber)' ?>;margin:0"><?= $isBalanced ? '✓ متوازن' : '⚠ غير متوازن' ?></p>
    </div>
    <div style="background:linear-gradient(135deg,var(--onyx-primary),var(--onyx-primary-light));border-radius:6px;padding:10px;text-align:center;color:#fff">
        <p style="font-size:11px;opacity:0.8;margin:0">رأس المال</p>
        <p style="font-size:18px;font-weight:800;margin:2px 0 0" dir="ltr"><?= number_format($capital, 2) ?></p>
    </div>
</div>

<!-- ====== نموذج الإضافة/التعديل ====== -->
<div id="balanceForm" style="display:<?= ($editBalance || isset($_GET['add'])) ? 'block' : 'none' ?>;margin-bottom:12px">
    <div class="card">
        <div class="card-header"><?= $editBalance ? 'تعديل الرصيد الافتتاحي' : 'إضافة رصيد افتتاحي جديد' ?></div>
        <div class="card-body">
            <form method="POST" id="crudForm">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="edit_id" value="<?= $editBalance['id'] ?? '' ?>">
                <div class="row g-2">
                    <div class="col-md-4">
                        <label class="form-label">رقم الحساب (الحساب التحليلي) *</label>
                        <select name="account_id" class="form-select" required id="account_select">
                            <option value="">— اختر الحساب —</option>
                            <?php foreach ($accounts as $a): ?>
                                <option value="<?= $a['id'] ?>" data-type="<?= $a['account_type'] ?>" <?= ($editBalance['account_id'] ?? 0) == $a['id'] ? 'selected' : '' ?>>
                                    <?= sanitize($a['code']) ?> — <?= sanitize($a['name_ar']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">العملة *</label>
                        <select name="currency_id" class="form-select" required id="currency_select">
                            <option value="">— اختر —</option>
                            <?php foreach ($currencies as $c): ?>
                                <option value="<?= $c['id'] ?>" data-foreign="<?= $c['is_foreign'] ?>" <?= ($editBalance['currency_id'] ?? 0) == $c['id'] ? 'selected' : '' ?>>
                                    <?= sanitize($c['code']) ?> — <?= sanitize($c['name_ar']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="row g-2 mt-1">
                    <div class="col-md-3">
                        <label class="form-label" style="color:var(--onyx-green)">مدين (محلي)</label>
                        <input type="number" step="0.01" name="debit_local" class="form-control font-mono" dir="ltr" value="<?= $editBalance['debit_local'] ?? 0 ?>" oninput="checkBalance()">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" style="color:var(--onyx-red)">دائن (محلي)</label>
                        <input type="number" step="0.01" name="credit_local" class="form-control font-mono" dir="ltr" value="<?= $editBalance['credit_local'] ?? 0 ?>" oninput="checkBalance()">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" style="color:var(--onyx-green)">مدين (أجنبي)</label>
                        <input type="number" step="0.01" name="debit_foreign" class="form-control font-mono" dir="ltr" value="<?= $editBalance['debit_foreign'] ?? 0 ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" style="color:var(--onyx-red)">دائن (أجنبي)</label>
                        <input type="number" step="0.01" name="credit_foreign" class="form-control font-mono" dir="ltr" value="<?= $editBalance['credit_foreign'] ?? 0 ?>">
                    </div>
                </div>
                <div class="row g-2 mt-1">
                    <div class="col-md-6">
                        <label class="form-label">ملاحظات</label>
                        <input type="text" name="notes" class="form-control" value="<?= sanitize($editBalance['notes'] ?? '') ?>">
                    </div>
                </div>
                <div style="margin-top:12px;display:flex;gap:8px;justify-content:flex-end">
                    <a href="<?= APP_URL ?>/inputs/opening-balances.php" class="btn btn-secondary">إلغاء</a>
                    <button type="submit" class="btn btn-primary">حفظ</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ====== جدول الأرصدة الافتتاحية ====== -->
<div class="card">
    <div class="card-body" style="padding:0;overflow-x:auto">
        <table class="table">
            <thead>
                <tr>
                    <th>رقم الحساب</th>
                    <th>اسم الحساب</th>
                    <th class="text-center">العملة</th>
                    <th class="text-end">مدين (محلي)</th>
                    <th class="text-end">دائن (محلي)</th>
                    <th class="text-end">مدين (أجنبي)</th>
                    <th class="text-end">دائن (أجنبي)</th>
                    <th class="text-center">إجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($balances)): ?>
                    <tr><td colspan="8" class="empty-state">لا توجد أرصدة افتتاحية. اضغط "إضافة رصيد" للبدء.</td></tr>
                <?php else: ?>
                    <?php foreach ($balances as $b): ?>
                        <tr>
                            <td><code dir="ltr" class="bg-slate-100 px-1 py-0.5 rounded" style="font-size:11px"><?= sanitize($b['account_code']) ?></code></td>
                            <td><?= sanitize($b['account_name']) ?></td>
                            <td class="text-center"><span class="badge bg-blue-light text-blue" style="font-size:10px"><?= sanitize($b['currency_code']) ?></span></td>
                            <td class="text-end font-mono text-green" dir="ltr"><?= $b['debit_local'] ? number_format($b['debit_local'], 2) : '—' ?></td>
                            <td class="text-end font-mono text-red" dir="ltr"><?= $b['credit_local'] ? number_format($b['credit_local'], 2) : '—' ?></td>
                            <td class="text-end font-mono text-green" dir="ltr"><?= $b['debit_foreign'] ? number_format($b['debit_foreign'], 2) : '—' ?></td>
                            <td class="text-end font-mono text-red" dir="ltr"><?= $b['credit_foreign'] ? number_format($b['credit_foreign'], 2) : '—' ?></td>
                            <td class="text-center">
                                <div style="display:flex;gap:4px;justify-content:center">
                                    <a href="?edit=<?= $b['id'] ?>" class="toolbar-btn" style="padding:3px 6px" title="تعديل"><svg width="13" height="13" fill="currentColor" viewBox="0 0 20 20"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/></svg></a>
                                    <a href="?delete=<?= $b['id'] ?>" class="toolbar-btn toolbar-btn-danger" style="padding:3px 6px" title="حذف" onclick="return confirmDelete('هل أنت متأكد من حذف هذا الرصيد؟')"><svg width="13" height="13" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9z" clip-rule="evenodd"/></svg></a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            <!-- ====== صف الإجماليات (مثل أونكس) ====== -->
            <?php if (!empty($balances)): ?>
            <tfoot>
                <tr style="background:#e2e8f0;font-weight:700">
                    <td colspan="3" style="text-align:left">الإجمالي</td>
                    <td class="text-end font-mono text-green" dir="ltr"><?= number_format($totalDebitLocal, 2) ?></td>
                    <td class="text-end font-mono text-red" dir="ltr"><?= number_format($totalCreditLocal, 2) ?></td>
                    <td class="text-end font-mono text-green" dir="ltr"><?= number_format($totalDebitForeign, 2) ?></td>
                    <td class="text-end font-mono text-red" dir="ltr"><?= number_format($totalCreditForeign, 2) ?></td>
                    <td></td>
                </tr>
                <tr style="background:<?= $isBalanced ? '#dcfce7' : '#fef3c7' ?>;font-weight:700">
                    <td colspan="3" style="text-align:left;color:<?= $isBalanced ? 'var(--onyx-green)' : 'var(--onyx-amber)' ?>">الفارق</td>
                    <td colspan="2" class="text-end font-mono" style="color:<?= $isBalanced ? 'var(--onyx-green)' : 'var(--onyx-amber)' ?>" dir="ltr"><?= number_format($diffLocal, 2) ?> <?= $isBalanced ? '✓ متوازن' : '⚠ غير متوازن' ?></td>
                    <td colspan="2" class="text-end font-mono" style="color:<?= $isBalanced ? 'var(--onyx-green)' : 'var(--onyx-amber)' ?>" dir="ltr"><?= number_format($diffForeign, 2) ?></td>
                    <td></td>
                </tr>
                <tr style="background:linear-gradient(135deg,var(--onyx-primary),var(--onyx-primary-light));color:#fff;font-weight:800">
                    <td colspan="3" style="text-align:left">رأس المال = إجمالي المدين − إجمالي الدائن</td>
                    <td colspan="5" class="text-end font-mono" dir="ltr"><?= number_format($capital, 2) ?> ريال يمني</td>
                    <td></td>
                </tr>
            </tfoot>
            <?php endif; ?>
        </table>
    </div>
</div>

<!-- ملاحظات من الكتاب -->
<div class="card mt-2">
    <div class="card-body" style="padding:10px 14px">
        <p style="font-size:11px;color:var(--onyx-text-muted);margin:0">
            <strong>ملاحظات من نظام أونكس:</strong><br>
            • لن يتزن ميزان المراجعة أو قائمة المركز المالي إذا كان هناك فارق في الأرصدة الافتتاحية.<br>
            • لا تستطيع التعديل على الأرصدة الافتتاحية بعد الإقفال الشهري لأول شهر.<br>
            • الأرصدة الافتتاحية لا تشمل المصاريف والإيرادات.<br>
            • <strong>حسابات مدينة بطبيعتها:</strong> كل حسابات الأصول والمصروفات والمسحوبات.<br>
            • <strong>حسابات دائنة بطبيعتها:</strong> كل حسابات الالتزامات ورأس المال والإيرادات.
        </p>
    </div>
</div>

<script>
function toggleForm() {
    var form = document.getElementById('balanceForm');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}
function checkBalance() {
    var debit = parseFloat(document.querySelector('[name="debit_local"]').value) || 0;
    var credit = parseFloat(document.querySelector('[name="credit_local"]').value) || 0;
    if (debit > 0 && credit > 0) {
        alert('تنبيه: لا يمكن أن يكون الحساب مدينًا ودائنًا في نفس الوقت (من نظام أونكس)');
    }
}
// إذا كان في وضع التعديل، اظهر النموذج
<?php if ($editBalance): ?>
document.getElementById('balanceForm').style.display = 'block';
<?php endif; ?>
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
