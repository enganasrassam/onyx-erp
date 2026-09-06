<?php
/**
 * الأرصدة الافتتاحية — مطابقة 100% لنظام أونكس ERP
 *
 * من الكتاب (صفحة 52-53) ومن صور النظام:
 * - 10 أعمدة: رقم الحساب، الحساب التحليلي، اسم الحساب، العملة،
 *   مدين، دائن، مدين أجنبي، دائن أجنبي، إجمالي، الفارق
 * - شريط أدوات علوي بأزرار ملونة
 * - خلفية صفراء فاتحة لأعمدة رقم الحساب واسم الحساب
 * - شريط إجماليات سفلي بخلفية وردية
 * - الفارق يجب أن يكون صفر (متوازن)
 * - رأس المال = إجمالي المدين − إجمالي الدائن
 */
require_once __DIR__ . '/../includes/header.php';

$search = trim($_GET['search'] ?? '');
$editingId = (int)($_GET['edit'] ?? 0);
$showForm = isset($_GET['add']) || $editingId > 0;

// ====== الحذف ======
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    db_delete('opening_balances', 'id = ?', [$id]);
    flash('success', 'تم حذف الرصيد الافتتاحي');
    redirect(APP_URL . '/inputs/opening-balances.php');
}

// ====== الإضافة / التعديل ======
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
    } elseif ($debitLocal > 0 && $creditLocal > 0) {
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
            $existing = db_fetch_one("SELECT id FROM opening_balances WHERE account_id = ? AND currency_id = ?", [$accountId, $currencyId]);
            if ($existing) {
                db_update('opening_balances', $data, 'id = ?', [$existing['id']]);
                flash('success', 'تم تحديث الرصيد الافتتاحي');
            } else {
                db_insert('opening_balances', $data);
                flash('success', 'تمت إضافة الرصيد الافتتاحي');
            }
        }
    }
    redirect(APP_URL . '/inputs/opening-balances.php');
}

// ====== جلب البيانات ======
$balances = db_fetch_all("
    SELECT ob.*, a.code as account_code, a.name_ar as account_name, a.account_type,
           c.code as currency_code, c.name_ar as currency_name, c.symbol as currency_symbol, c.is_foreign
    FROM opening_balances ob
    LEFT JOIN accounts a ON ob.account_id = a.id
    LEFT JOIN currencies c ON ob.currency_id = c.id
    ORDER BY a.code ASC
");

// ====== حساب الإجماليات ======
$totDebitLocal = 0; $totCreditLocal = 0;
$totDebitForeign = 0; $totCreditForeign = 0;
foreach ($balances as $b) {
    $totDebitLocal += $b['debit_local'];
    $totCreditLocal += $b['credit_local'];
    $totDebitForeign += $b['debit_foreign'];
    $totCreditForeign += $b['credit_foreign'];
}
$diffLocal = $totDebitLocal - $totCreditLocal;
$diffForeign = $totDebitForeign - $totCreditForeign;
$capital = $diffLocal;
$isBalanced = abs($diffLocal) < 0.01 && abs($diffForeign) < 0.01;

// ====== الخيارات ======
$accounts = db_fetch_all("SELECT id, code, name_ar, account_type FROM accounts WHERE is_detail = 1 AND active = 1 ORDER BY code");
$currencies = db_fetch_all("SELECT id, code, name_ar, symbol, is_foreign FROM currencies WHERE active = 1 ORDER BY code");
$editBalance = $editingId ? db_fetch_one("SELECT * FROM opening_balances WHERE id = ?", [$editingId]) : null;

$typeLabels = ['asset'=>'أصول','liability'=>'خصوم','equity'=>'حقوق ملكية','revenue'=>'إيرادات','expense'=>'مصروفات'];
?>
<style>
.ob-tbl{width:100%;border-collapse:collapse;font-size:12px;background:#fff}
.ob-tbl thead th{background:#d9e2ec;border:1px solid #b0bec5;padding:6px 8px;font-weight:700;font-size:11px;color:#2d3748;text-align:center;white-space:nowrap}
.ob-tbl tbody td{border:1px solid #e2e8f0;padding:4px 8px;vertical-align:middle}
.ob-col-yellow{background:#fffbeb !important}
.ob-col-debit{background:#f0fff4 !important;text-align:left;font-family:'Cairo',monospace;font-weight:600;color:#22543d}
.ob-col-credit{background:#fff5f5 !important;text-align:left;font-family:'Cairo',monospace;font-weight:600;color:#9b2c2c}
.ob-col-debit-f{background:#ebf8ff !important;text-align:left;font-family:'Cairo',monospace;font-size:11px;color:#2a4365}
.ob-col-credit-f{background:#fffaf0 !important;text-align:left;font-family:'Cairo',monospace;font-size:11px;color:#7c2d12}
.ob-total-row td{background:#fed7d7 !important;font-weight:800;font-family:'Cairo',monospace;border:2px solid #fc8181 !important}
.ob-diff-row td{background:<?= $isBalanced ? '#c6f6d5' : '#fefcbf' ?> !important;font-weight:800;border:2px solid <?= $isBalanced ? '#48bb78' : '#ecc94b' ?> !important}
.ob-capital-row td{background:linear-gradient(135deg,#2c5282,#3182ce) !important;color:#fff;font-weight:800;border:2px solid #2c5282 !important}
.ob-btn-add{background:#48bb78;color:#fff;border:1px solid #2f855a;padding:5px 12px;border-radius:4px;font-size:12px;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:4px;text-decoration:none;font-family:inherit}
.ob-btn-add:hover{background:#2f855a;color:#fff}
.ob-btn-nav{background:#ecc94b;color:#fff;border:1px solid #d69e2e;padding:4px 8px;border-radius:3px;font-size:11px;cursor:pointer;text-decoration:none;color:#fff !important}
.ob-btn-nav:hover{background:#d69e2e}
.ob-btn-save{background:#3182ce;color:#fff;border:1px solid #2c5282;padding:5px 14px;border-radius:4px;font-size:12px;font-weight:600;cursor:pointer;text-decoration:none;color:#fff !important;font-family:inherit}
.ob-btn-save:hover{background:#2c5282}
.ob-btn-close{background:#e53e3e;color:#fff;border:1px solid #c53030;padding:5px 14px;border-radius:4px;font-size:12px;font-weight:600;cursor:pointer;text-decoration:none;color:#fff !important;font-family:inherit}
.ob-btn-close:hover{background:#c53030}
.ob-toolbar{background:#d9e2ec;border:1px solid #b0bec5;border-radius:4px;padding:4px;margin-bottom:8px;display:flex;align-items:center;gap:2px;flex-wrap:wrap}
.ob-toolbar-btn{display:inline-flex;align-items:center;gap:3px;padding:4px 8px;font-size:11px;font-weight:500;color:#2d3748;background:transparent;border:1px solid transparent;border-radius:3px;cursor:pointer;text-decoration:none;font-family:inherit}
.ob-toolbar-btn:hover{background:#fff;border-color:#b0bec5}
.ob-toolbar-btn-green{background:#48bb78;color:#fff !important;border-color:#2f855a}
.ob-toolbar-btn-green:hover{background:#2f855a}
.ob-form-card{background:#fff;border:1px solid #cbd5e0;border-radius:6px;margin-bottom:10px;overflow:hidden}
.ob-form-hdr{background:#edf2f7;border-bottom:1px solid #e2e8f0;padding:8px 12px;font-weight:700;font-size:13px}
.ob-form-body{padding:12px}
.ob-summary-box{display:grid;grid-template-columns:repeat(3,1fr);gap:6px;margin-bottom:8px}
.ob-sum-card{border-radius:6px;padding:8px;text-align:center;border:1px solid}
</style>

<!-- عنوان -->
<div class="card mb-2"><div class="card-body" style="padding:8px 12px">
<h4 style="font-size:14px;font-weight:700;margin:0">الأرصدة الافتتاحية</h4>
<p style="font-size:11px;color:#718096;margin:2px 0 0">إدخال أرصدة بداية النشاط — رأس المال = إجمالي المدين − إجمالي الدائن — يجب أن يكون الفارق صفر</p>
</div></div>

<!-- شريط الأدوات (مطابق لأونكس) -->
<div class="ob-toolbar">
    <a href="?add=1" class="ob-toolbar-btn ob-toolbar-btn-green"><svg width="13" height="13" fill="currentColor" viewBox="0 0 20 20"><path d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"/></svg>إضافة</a>
    <div class="toolbar-divider"></div>
    <button class="ob-toolbar-btn" onclick="window.location.reload()"><svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>استعراض</button>
    <button class="ob-toolbar-btn" onclick="window.print()"><svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2z"/></svg>طباعة</button>
    <div class="toolbar-divider"></div>
    <div style="margin-right:auto;position:relative">
        <form method="GET" style="display:inline">
            <input type="text" name="search" placeholder="بحث برقم الحساب أو الاسم..." value="<?= sanitize($search) ?>" style="width:220px;padding:4px 28px 4px 8px;font-size:11px;border:1px solid #b0bec5;border-radius:3px;background:#fff;outline:none;font-family:inherit">
            <svg width="13" height="13" fill="none" stroke="#a0aec0" viewBox="0 0 24 24" style="position:absolute;right:6px;top:50%;transform:translateY(-50%)"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </form>
    </div>
</div>

<!-- بطاقات الإجماليات (مثل شريط الحالة السفلي في أونكس) -->
<div class="ob-summary-box">
    <div class="ob-sum-card" style="background:#f0fff4;border-color:#9ae6b4">
        <p style="font-size:10px;color:#276749;margin:0">إجمالي مدين (محلي)</p>
        <p style="font-size:17px;font-weight:800;color:#22543d;margin:1px 0 0" dir="ltr"><?= number_format($totDebitLocal, 2) ?></p>
    </div>
    <div class="ob-sum-card" style="background:#fff5f5;border-color:#feb2b2">
        <p style="font-size:10px;color:#9b2c2c;margin:0">إجمالي دائن (محلي)</p>
        <p style="font-size:17px;font-weight:800;color:#9b2c2c;margin:1px 0 0" dir="ltr"><?= number_format($totCreditLocal, 2) ?></p>
    </div>
    <div class="ob-sum-card" style="background:<?= $isBalanced ? '#c6f6d5' : '#fefcbf' ?>;border-color:<?= $isBalanced ? '#48bb78' : '#ecc94b' ?>">
        <p style="font-size:10px;color:<?= $isBalanced ? '#276749' : '#975a16' ?>;margin:0">الفارق <?= $isBalanced ? '✓ متوازن' : '⚠ غير متوازن' ?></p>
        <p style="font-size:17px;font-weight:800;color:<?= $isBalanced ? '#276749' : '#975a16' ?>;margin:1px 0 0" dir="ltr"><?= number_format(abs($diffLocal), 2) ?></p>
    </div>
    <div class="ob-sum-card" style="background:#ebf8ff;border-color:#90cdf4">
        <p style="font-size:10px;color:#2a4365;margin:0">إجمالي مدين (أجنبي)</p>
        <p style="font-size:17px;font-weight:800;color:#2a4365;margin:1px 0 0" dir="ltr"><?= number_format($totDebitForeign, 2) ?></p>
    </div>
    <div class="ob-sum-card" style="background:#fffaf0;border-color:#fbd38d">
        <p style="font-size:10px;color:#7c2d12;margin:0">إجمالي دائن (أجنبي)</p>
        <p style="font-size:17px;font-weight:800;color:#7c2d12;margin:1px 0 0" dir="ltr"><?= number_format($totCreditForeign, 2) ?></p>
    </div>
    <div class="ob-sum-card" style="background:linear-gradient(135deg,#2c5282,#3182ce);border-color:#2c5282;color:#fff">
        <p style="font-size:10px;opacity:0.8;margin:0">رأس المال</p>
        <p style="font-size:17px;font-weight:800;margin:1px 0 0" dir="ltr"><?= number_format($capital, 2) ?></p>
    </div>
</div>

<!-- ====== نموذج الإضافة/التعديل ====== -->
<?php if ($showForm): ?>
<div class="ob-form-card">
    <div class="ob-form-hdr"><?= $editBalance ? '٢ تعديل رصيد افتتاحي' : '١ إضافة رصيد افتتاحي جديد' ?></div>
    <div class="ob-form-body">
        <form method="POST" id="crudForm">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="edit_id" value="<?= $editBalance['id'] ?? '' ?>">
            <div class="row g-2">
                <div class="col-md-3">
                    <label class="form-label">١ رقم الحساب *</label>
                    <select name="account_id" class="form-select" required>
                        <option value="">— اختر —</option>
                        <?php foreach ($accounts as $a): ?>
                            <option value="<?= $a['id'] ?>" <?= ($editBalance['account_id'] ?? 0) == $a['id'] ? 'selected' : '' ?>><?= sanitize($a['code']) ?> — <?= sanitize($a['name_ar']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">٤ العملة *</label>
                    <select name="currency_id" class="form-select" required id="currency_select">
                        <option value="">— اختر —</option>
                        <?php foreach ($currencies as $c): ?>
                            <option value="<?= $c['id'] ?>" data-foreign="<?= $c['is_foreign'] ?>" <?= ($editBalance['currency_id'] ?? 0) == $c['id'] ? 'selected' : '' ?>><?= sanitize($c['code']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="row g-2 mt-1">
                <div class="col-md-3">
                    <label class="form-label" style="color:#22543d">٥ مدين (محلي)</label>
                    <input type="number" step="0.01" name="debit_local" class="form-control font-mono" dir="ltr" value="<?= $editBalance['debit_local'] ?? 0 ?>" style="background:#f0fff4" oninput="checkNature()">
                </div>
                <div class="col-md-3">
                    <label class="form-label" style="color:#9b2c2c">٦ دائن (محلي)</label>
                    <input type="number" step="0.01" name="credit_local" class="form-control font-mono" dir="ltr" value="<?= $editBalance['credit_local'] ?? 0 ?>" style="background:#fff5f5" oninput="checkNature()">
                </div>
                <div class="col-md-3">
                    <label class="form-label" style="color:#2a4365">٧ مدين أجنبي</label>
                    <input type="number" step="0.01" name="debit_foreign" class="form-control font-mono" dir="ltr" value="<?= $editBalance['debit_foreign'] ?? 0 ?>" style="background:#ebf8ff">
                </div>
                <div class="col-md-3">
                    <label class="form-label" style="color:#7c2d12">٨ دائن أجنبي</label>
                    <input type="number" step="0.01" name="credit_foreign" class="form-control font-mono" dir="ltr" value="<?= $editBalance['credit_foreign'] ?? 0 ?>" style="background:#fffaf0">
                </div>
            </div>
            <div class="row g-2 mt-1">
                <div class="col-md-6">
                    <label class="form-label">ملاحظات</label>
                    <input type="text" name="notes" class="form-control" value="<?= sanitize($editBalance['notes'] ?? '') ?>">
                </div>
            </div>
            <div style="margin-top:10px;display:flex;gap:6px;justify-content:flex-end">
                <a href="<?= APP_URL ?>/inputs/opening-balances.php" class="ob-btn-close">إغلاق</a>
                <button type="submit" class="ob-btn-save"><svg width="13" height="13" fill="currentColor" viewBox="0 0 20 20"><path d="M7.707 10.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l7-7a1 1 0 00-1.414-1.414L10 12.586l-2.293-2.293z"/></svg>حفظ</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- ====== الجدول الرئيسي (مطابق لأونكس) ====== -->
<div style="overflow-x:auto;border:1px solid #cbd5e0;border-radius:4px">
<table class="ob-tbl">
    <thead>
        <tr>
            <th style="width:40px">#</th>
            <th>١ رقم الحساب</th>
            <th>٢ الحساب التحليلي</th>
            <th>٣ اسم الحساب</th>
            <th style="width:60px">٤ العملة</th>
            <th style="width:110px">٥ مدين</th>
            <th style="width:110px">٦ دائن</th>
            <th style="width:100px">٧ مدين أجنبي</th>
            <th style="width:100px">٨ دائن أجنبي</th>
            <th style="width:70px">إجراءات</th>
        </tr>
    </thead>
    <tbody>
    <?php if (empty($balances)): ?>
        <tr><td colspan="10" style="text-align:center;padding:30px;color:#a0aec0">لا توجد أرصدة افتتاحية — اضغط "إضافة" للبدء</td></tr>
    <?php else: $rowNum = 1; foreach ($balances as $b): ?>
        <tr>
            <td style="text-align:center;color:#a0aec0;font-size:11px"><?= $rowNum++ ?></td>
            <td class="ob-col-yellow"><code dir="ltr" style="font-size:11px"><?= sanitize($b['account_code']) ?></code></td>
            <td class="ob-col-yellow" style="font-size:11px;color:#4a5568"><?= sanitize($b['account_name']) ?></td>
            <td class="ob-col-yellow" style="font-size:11px">
                <span class="badge bg-slate-100 text-slate-700" style="font-size:9px"><?= $typeLabels[$b['account_type']] ?? $b['account_type'] ?></span>
            </td>
            <td style="text-align:center"><span class="badge bg-blue-light text-blue" style="font-size:9px"><?= sanitize($b['currency_code']) ?></span></td>
            <td class="ob-col-debit"><?= $b['debit_local'] ? number_format($b['debit_local'], 2) : '—' ?></td>
            <td class="ob-col-credit"><?= $b['credit_local'] ? number_format($b['credit_local'], 2) : '—' ?></td>
            <td class="ob-col-debit-f"><?= $b['debit_foreign'] ? number_format($b['debit_foreign'], 2) : '—' ?></td>
            <td class="ob-col-credit-f"><?= $b['credit_foreign'] ? number_format($b['credit_foreign'], 2) : '—' ?></td>
            <td style="text-align:center">
                <div style="display:flex;gap:3px;justify-content:center">
                    <a href="?edit=<?= $b['id'] ?>" class="ob-toolbar-btn" style="padding:2px 5px" title="تعديل"><svg width="12" height="12" fill="currentColor" viewBox="0 0 20 20"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/></svg></a>
                    <a href="?delete=<?= $b['id'] ?>" class="ob-toolbar-btn" style="padding:2px 5px;color:#c53030" title="حذف" onclick="return confirm('هل أنت متأكد من الحذف؟')"><svg width="12" height="12" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9z" clip-rule="evenodd"/></svg></a>
                </div>
            </td>
        </tr>
    <?php endforeach; endif; ?>
    </tbody>
    <?php if (!empty($balances)): ?>
    <!-- ٩ إجمالي الحسابات التحليلية -->
    <tfoot>
        <tr class="ob-total-row">
            <td colspan="5" style="text-align:left">٩ إجمالي الحسابات التحليلية</td>
            <td style="text-align:left" dir="ltr"><?= number_format($totDebitLocal, 2) ?></td>
            <td style="text-align:left" dir="ltr"><?= number_format($totCreditLocal, 2) ?></td>
            <td style="text-align:left" dir="ltr"><?= number_format($totDebitForeign, 2) ?></td>
            <td style="text-align:left" dir="ltr"><?= number_format($totCreditForeign, 2) ?></td>
            <td></td>
        </tr>
        <!-- ١٠ الفارق -->
        <tr class="ob-diff-row">
            <td colspan="5" style="text-align:left">١٠ الفارق <?= $isBalanced ? '✓ متوازن' : '⚠ غير متوازن' ?></td>
            <td colspan="2" style="text-align:left" dir="ltr"><?= number_format($diffLocal, 2) ?></td>
            <td colspan="2" style="text-align:left" dir="ltr"><?= number_format($diffForeign, 2) ?></td>
            <td></td>
        </tr>
        <!-- رأس المال -->
        <tr class="ob-capital-row">
            <td colspan="5" style="text-align:left">رأس المال = إجمالي المدين − إجمالي الدائن</td>
            <td colspan="5" style="text-align:left" dir="ltr"><?= number_format($capital, 2) ?> ريال يمني</td>
        </tr>
    </tfoot>
    <?php endif; ?>
</table>
</div>

<!-- ملاحظات من الكتاب -->
<div class="card mt-2"><div class="card-body" style="padding:8px 12px">
<p style="font-size:11px;color:#718096;margin:0;line-height:1.6">
<strong>ملاحظات من نظام أونكس:</strong><br>
• لن يتزن ميزان المراجعة أو قائمة المركز المالي إذا كان هناك فارق في الأرصدة الافتتاحية.<br>
• لا تستطيع التعديل على الأرصدة الافتتاحية بعد الإقفال الشهري لأول شهر.<br>
• الأرصدة الافتتاحية لا تشمل المصاريف والإيرادات.<br>
• <strong>حسابات مدينة بطبيعتها:</strong> كل حسابات الأصول والمصروفات والمسحوبات — الزيادة تجعلها مدينة.<br>
• <strong>حسابات دائنة بطبيعتها:</strong> كل حسابات الالتزامات ورأس المال والإيرادات — الزيادة تجعلها دائنة.<br>
• <strong>المدين (Debit):</strong> الجانب الأيمن للحساب — <strong>الدائن (Credit):</strong> الجانب الأيسر للحساب.
</p>
</div></div>

<script>
function checkNature() {
    var d = parseFloat(document.querySelector('[name="debit_local"]').value) || 0;
    var c = parseFloat(document.querySelector('[name="credit_local"]').value) || 0;
    if (d > 0 && c > 0) {
        alert('تنبيه من نظام أونكس:\nلا يمكن أن يكون الحساب مدينًا ودائنًا في نفس الوقت');
    }
}
// إظهار/إخفاء حقول العملة الأجنبية حسب نوع العملة
document.getElementById('currency_select')?.addEventListener('change', function() {
    var opt = this.options[this.selectedIndex];
    var isForeign = opt.getAttribute('data-foreign') === '1';
    var foreignFields = document.querySelectorAll('[name="debit_foreign"], [name="credit_foreign"]');
    foreignFields.forEach(function(f) {
        f.parentElement.style.opacity = isForeign ? '1' : '0.4';
        if (!isForeign) { f.value = '0'; }
    });
});
// تشغيل عند التحميل
document.getElementById('currency_select')?.dispatchEvent(new Event('change'));
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
