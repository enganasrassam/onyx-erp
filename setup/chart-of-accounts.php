<?php
/**
 * الدليل المحاسبي — مطابق 100% لنظام أونكس ERP
 *
 * من الكتاب (صفحة 41-43):
 * 9 حقول مرقمة:
 * ١ الحساب الرئيسي | ٢ رقم الحساب | ٣ اسم الحساب
 * ٤ النوع (رئيسي/فرعي) | ٥ طبيعة الحساب (مدين/دائن) | ٦ نوع التقرير
 * ٧ نوع الحساب التحليلي | ٨ رمز العملة | ٩ شجرة الحسابات
 *
 * شجرة هرمية قابلة للطي + نموذج إضافة/تعديل
 */
require_once __DIR__ . '/../includes/header.php';

$search = trim($_GET['search'] ?? '');
$showForm = isset($_GET['add']) || isset($_GET['edit']);
$editId = (int)($_GET['edit'] ?? 0);

// ====== الحذف ======
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $children = db_fetch_one("SELECT COUNT(*) as c FROM accounts WHERE parent_id = ?", [$id]);
    if ($children['c'] > 0) {
        flash('error', 'لا يمكن حذف حساب له أبناء');
    } else {
        db_delete('accounts', 'id = ?', [$id]);
        flash('success', 'تم حذف الحساب');
    }
    redirect(APP_URL . '/setup/chart-of-accounts.php');
}

// ====== جلب البيانات ======
$accounts = db_fetch_all("SELECT a.*, p.code as parent_code, p.name_ar as parent_name FROM accounts a LEFT JOIN accounts p ON a.parent_id = p.id ORDER BY a.code");
$allAccounts = $accounts;
$currencies = db_fetch_all("SELECT id, code, name_ar FROM currencies WHERE active = 1 ORDER BY code");

// بناء شجرة
$byParent = [];
foreach ($accounts as $a) {
    $pid = $a['parent_id'] ?? 0;
    if (!isset($byParent[$pid])) $byParent[$pid] = [];
    $byParent[$pid][] = $a;
}

$typeLabels = ['asset'=>'أصول','liability'=>'خصوم','equity'=>'حقوق ملكية','revenue'=>'إيرادات','expense'=>'مصروفات'];
$typeColors = [
    'asset' => 'background:#dbeafe;color:#1e40af',
    'liability' => 'background:#fee2e2;color:#991b1b',
    'equity' => 'background:#f3e8ff;color:#6b21a8',
    'revenue' => 'background:#d1fae5;color:#065f46',
    'expense' => 'background:#fef3c7;color:#92400e',
];
$natureLabels = ['debit'=>'مدين','credit'=>'دائن'];
$reportLabels = ['balance_sheet'=>'الميزانية العمومية','income_statement'=>'الأرباح والخسائر'];
$analyticalLabels = [
    'cash' => 'صندوق', 'bank' => 'بنك', 'customer' => 'عميل',
    'supplier' => 'مورد', 'employee_advance' => 'سلف موظفين',
    'employee_custody' => 'عهد موظفين', 'general' => 'عام',
];

// حساب الافتتاحي للحساب المراد تعديله
$editAccount = $editId ? db_fetch_one("SELECT * FROM accounts WHERE id = ?", [$editId]) : null;

// ====== معالجة الإضافة/التعديل ======
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $code = trim($_POST['code'] ?? '');
    $nameAr = trim($_POST['name_ar'] ?? '');
    $parentId = (int)($_POST['parent_id'] ?? 0);
    $accountType = $_POST['account_type'] ?? 'asset';
    $isDetail = isset($_POST['is_detail']) ? 1 : 0;
    $active = isset($_POST['active']) ? 1 : 0;
    $nature = $_POST['nature'] ?? null;
    $reportType = $_POST['report_type'] ?? null;
    $analyticalType = $_POST['analytical_type'] ?? null;
    $openingBalance = (float)($_POST['opening_balance'] ?? 0);
    $currencyIds = isset($_POST['currency_ids']) ? implode(',', $_POST['currency_ids']) : null;
    $eid = (int)($_POST['edit_id'] ?? 0);
    $level = 1;
    if ($parentId) {
        $parent = db_fetch_one("SELECT level FROM accounts WHERE id = ?", [$parentId]);
        if ($parent) $level = $parent['level'] + 1;
    }

    if (!$code || !$nameAr || !$accountType) {
        flash('error', 'رمز الحساب والاسم والنوع مطلوبة');
    } else {
        $data = [
            'code' => $code,
            'name_ar' => $nameAr,
            'parent_id' => $parentId ?: null,
            'account_type' => $accountType,
            'level' => $level,
            'is_detail' => $isDetail,
            'active' => $active,
            'nature' => $nature ?: null,
            'report_type' => $reportType ?: null,
            'analytical_type' => $analyticalType ?: null,
            'currency_ids' => $currencyIds,
            'opening_balance' => $openingBalance,
        ];
        if ($eid) {
            db_update('accounts', $data, 'id = ?', [$eid]);
            flash('success', 'تم تحديث الحساب');
        } else {
            try {
                db_insert('accounts', $data);
                flash('success', 'تمت إضافة الحساب');
            } catch (Exception $e) {
                flash('error', 'رمز الحساب موجود مسبقًا');
            }
        }
    }
    redirect(APP_URL . '/setup/chart-of-accounts.php');
}

function renderAccountNode($node, $depth, $byParent, $typeLabels, $typeColors, $natureLabels, $reportLabels, $analyticalLabels, $search) {
    $children = $byParent[$node['id']] ?? [];
    $hasChildren = count($children) > 0;
    $type = $typeLabels[$node['account_type']] ?? $node['account_type'];
    $typeColor = $typeColors[$node['account_type']] ?? 'background:#f1f5f9;color:#475569';

    $html = '<tr class="ob-row" style="border-bottom:1px solid #edf2f7">';
    $html .= '<td style="padding:4px 8px">';
    $html .= '<div style="display:flex;align-items:center;gap:4px;padding-right:' . ($depth * 20) . 'px">';
    if ($hasChildren) {
        $html .= '<button onclick="toggleNode(' . $node['id'] . ')" style="border:none;background:none;cursor:pointer;padding:2px"><svg id="chev-' . $node['id'] . '" width="12" height="12" fill="none" stroke="#718096" viewBox="0 0 24 24" style="transform:rotate(-90deg)"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg></button>';
    } else {
        $html .= '<span style="width:16px"></span>';
    }
    if ($hasChildren) {
        $html .= '<svg width="14" height="14" fill="#ecc94b" viewBox="0 0 24 24"><path d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>';
    } else {
        $html .= '<svg width="14" height="14" fill="#a0aec0" viewBox="0 0 24 24"><path d="M9 3h7l5 5v13H9V3z"/></svg>';
    }
    $html .= '<code dir="ltr" style="font-size:11px;background:#fffbeb;padding:1px 6px;border-radius:3px;font-weight:600">' . htmlspecialchars($node['code']) . '</code>';
    $html .= '<span style="font-size:12px;color:#2d3748">' . htmlspecialchars($node['name_ar']) . '</span>';
    if ($node['is_detail']) {
        $html .= '<span style="font-size:9px;background:#dbeafe;color:#1e40af;padding:1px 6px;border-radius:9999px;font-weight:600">تفصيلي</span>';
    }
    $html .= '</div>';
    $html .= '</td>';
    $html .= '<td style="text-align:center"><span style="font-size:10px;padding:2px 8px;border-radius:9999px;font-weight:600;' . $typeColor . '">' . $type . '</span></td>';
    $html .= '<td style="text-align:center;font-size:11px;color:#718096">' . ($node['level'] ? 'مستوى ' . $node['level'] : '—') . '</td>';
    $html .= '<td style="text-align:center">' . ($node['nature'] ? '<span style="font-size:10px;font-weight:600;color:' . ($node['nature'] == 'debit' ? '#22543d' : '#9b2c2c') . '">' . ($natureLabels[$node['nature']] ?? $node['nature']) . '</span>' : '—') . '</td>';
    $html .= '<td style="text-align:center;font-size:11px">' . ($node['report_type'] ? ($reportLabels[$node['report_type']] ?? '—') : '—') . '</td>';
    $html .= '<td style="text-align:center;font-size:11px">' . ($node['analytical_type'] ? '<span style="font-size:10px;background:#e6fffa;color:#234e52;padding:1px 6px;border-radius:3px">' . ($analyticalLabels[$node['analytical_type']] ?? $node['analytical_type']) . '</span>' : '—') . '</td>';
    $html .= '<td style="text-align:center">' . ($node['active'] ? '<span style="font-size:10px;background:#c6f6d5;color:#22543d;padding:1px 6px;border-radius:9999px">نشط</span>' : '<span style="font-size:10px;background:#fed7d7;color:#9b2c2c;padding:1px 6px;border-radius:9999px">غير نشط</span>') . '</td>';
    $html .= '<td style="text-align:center"><div style="display:flex;gap:3px;justify-content:center">';
    $html .= '<a href="?edit=' . $node['id'] . '" style="padding:2px 5px;font-size:11px;color:#2c5282;text-decoration:none" title="تعديل"><svg width="12" height="12" fill="currentColor" viewBox="0 0 20 20"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/></svg></a>';
    if (!$hasChildren) {
        $html .= '<a href="?delete=' . $node['id'] . '" style="padding:2px 5px;font-size:11px;color:#c53030;text-decoration:none" title="حذف" onclick="return confirm(\'حذف الحساب؟\')"><svg width="12" height="12" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9z" clip-rule="evenodd"/></svg></a>';
    }
    $html .= '</div></td>';
    $html .= '</tr>';

    if ($hasChildren) {
        $html .= '<tbody id="children-' . $node['id'] . '" style="display:none">';
        foreach ($children as $child) {
            $html .= renderAccountNode($child, $depth + 1, $byParent, $typeLabels, $typeColors, $natureLabels, $reportLabels, $analyticalLabels, $search);
        }
        $html .= '</tbody>';
    }
    return $html;
}
?>

<style>
.ob-tbl{width:100%;border-collapse:collapse;font-size:12px;background:#fff}
.ob-tbl thead th{background:#d9e2ec;border:1px solid #b0bec5;padding:6px 8px;font-weight:700;font-size:11px;color:#2d3748;text-align:center;white-space:nowrap}
.ob-row:hover{background:#ebf8ff !important}
.ob-form-card{background:#fff;border:1px solid #cbd5e0;border-radius:6px;margin-bottom:10px;overflow:hidden}
.ob-form-hdr{background:#edf2f7;border-bottom:1px solid #e2e8f0;padding:8px 12px;font-weight:700;font-size:13px;display:flex;justify-content:space-between;align-items:center}
.ob-form-body{padding:12px}
.ob-field-num{display:inline-flex;align-items:center;justify-content:center;width:18px;height:18px;border-radius:50%;background:#2c5282;color:#fff;font-size:10px;font-weight:700;margin-left:4px}
.ob-btn-green{background:#48bb78;color:#fff !important;border:1px solid #2f855a;padding:5px 12px;border-radius:4px;font-size:12px;font-weight:600;cursor:pointer;text-decoration:none;font-family:inherit;display:inline-flex;align-items:center;gap:4px}
.ob-btn-green:hover{background:#2f855a}
.ob-btn-blue{background:#3182ce;color:#fff !important;border:1px solid #2c5282;padding:5px 12px;border-radius:4px;font-size:12px;font-weight:600;cursor:pointer;text-decoration:none;font-family:inherit;display:inline-flex;align-items:center;gap:4px}
.ob-btn-blue:hover{background:#2c5282}
.ob-btn-red{background:#e53e3e;color:#fff !important;border:1px solid #c53030;padding:5px 12px;border-radius:4px;font-size:12px;font-weight:600;cursor:pointer;text-decoration:none;font-family:inherit;display:inline-flex;align-items:center;gap:4px}
.ob-btn-red:hover{background:#c53030}
.ob-toolbar{background:#d9e2ec;border:1px solid #b0bec5;border-radius:4px;padding:4px;margin-bottom:8px;display:flex;align-items:center;gap:2px;flex-wrap:wrap}
.ob-tb-btn{display:inline-flex;align-items:center;gap:3px;padding:4px 8px;font-size:11px;font-weight:500;color:#2d3748;background:transparent;border:1px solid transparent;border-radius:3px;cursor:pointer;text-decoration:none;font-family:inherit}
.ob-tb-btn:hover{background:#fff;border-color:#b0bec5}
</style>

<!-- عنوان -->
<div class="card mb-2"><div class="card-body" style="padding:8px 12px">
<h4 style="font-size:14px;font-weight:700;margin:0">الدليل المحاسبي <span class="ob-field-num">٩</span></h4>
<p style="font-size:11px;color:#718096;margin:2px 0 0">شجرة الحسابات الهرمية — الحسابات التفصيلية فقط هي التي تقبل القيود</p>
</div></div>

<!-- شريط الأدوات -->
<div class="ob-toolbar">
    <a href="?add=1" class="ob-btn-green"><svg width="13" height="13" fill="currentColor" viewBox="0 0 20 20"><path d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"/></svg>إضافة حساب</a>
    <div class="toolbar-divider"></div>
    <button class="ob-tb-btn" onclick="window.location.reload()"><svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>تحديث</button>
    <button class="ob-tb-btn" onclick="window.print()"><svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2"/></svg>طباعة</button>
    <div style="margin-right:auto;position:relative">
        <form method="GET" style="display:inline">
            <input type="text" name="search" placeholder="بحث برقم أو اسم الحساب..." value="<?= sanitize($search) ?>" style="width:220px;padding:4px 28px 4px 8px;font-size:11px;border:1px solid #b0bec5;border-radius:3px;background:#fff;outline:none;font-family:inherit">
            <svg width="13" height="13" fill="none" stroke="#a0aec0" viewBox="0 0 24 24" style="position:absolute;right:6px;top:50%;transform:translateY(-50%)"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </form>
    </div>
</div>

<!-- ====== نموذج الإضافة/التعديل (9 حقول مطابقة لأونكس) ====== -->
<?php if ($showForm): ?>
<div class="ob-form-card">
    <div class="ob-form-hdr">
        <span><?= $editAccount ? 'تعديل حساب' : 'إضافة حساب جديد' ?></span>
        <a href="<?= APP_URL ?>/setup/chart-of-accounts.php" class="ob-btn-red">إغلاق ✕</a>
    </div>
    <div class="ob-form-body">
        <form method="POST" id="crudForm">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="edit_id" value="<?= $editAccount['id'] ?? '' ?>">
            <div class="row g-2">
                <!-- ١ الحساب الرئيسي -->
                <div class="col-md-4">
                    <label class="form-label"><span class="ob-field-num">١</span> الحساب الرئيسي</label>
                    <select name="parent_id" class="form-select">
                        <option value="">— بدون (حساب رئيسي) —</option>
                        <?php foreach ($allAccounts as $a): ?>
                            <option value="<?= $a['id'] ?>" <?= ($editAccount['parent_id'] ?? 0) == $a['id'] ? 'selected' : '' ?>><?= sanitize($a['code']) ?> — <?= sanitize($a['name_ar']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <!-- ٢ رقم الحساب -->
                <div class="col-md-4">
                    <label class="form-label"><span class="ob-field-num">٢</span> رقم الحساب *</label>
                    <input type="text" name="code" class="form-control font-mono" dir="ltr" value="<?= sanitize($editAccount['code'] ?? '') ?>" required <?= $editAccount ? 'readonly' : '' ?> placeholder="1101" style="background:#fffbeb">
                </div>
                <!-- ٣ اسم الحساب -->
                <div class="col-md-4">
                    <label class="form-label"><span class="ob-field-num">٣</span> اسم الحساب *</label>
                    <input type="text" name="name_ar" class="form-control" value="<?= sanitize($editAccount['name_ar'] ?? '') ?>" required placeholder="الصندوق" style="background:#fffbeb">
                </div>
            </div>
            <div class="row g-2 mt-1">
                <!-- ٤ النوع -->
                <div class="col-md-3">
                    <label class="form-label"><span class="ob-field-num">٤</span> النوع</label>
                    <select name="account_type" class="form-select" required>
                        <option value="asset" <?= ($editAccount['account_type'] ?? '') === 'asset' ? 'selected' : '' ?>>أصول</option>
                        <option value="liability" <?= ($editAccount['account_type'] ?? '') === 'liability' ? 'selected' : '' ?>>خصوم</option>
                        <option value="equity" <?= ($editAccount['account_type'] ?? '') === 'equity' ? 'selected' : '' ?>>حقوق ملكية</option>
                        <option value="revenue" <?= ($editAccount['account_type'] ?? '') === 'revenue' ? 'selected' : '' ?>>إيرادات</option>
                        <option value="expense" <?= ($editAccount['account_type'] ?? '') === 'expense' ? 'selected' : '' ?>>مصروفات</option>
                    </select>
                </div>
                <!-- ٥ طبيعة الحساب -->
                <div class="col-md-3">
                    <label class="form-label"><span class="ob-field-num">٥</span> طبيعة الحساب</label>
                    <select name="nature" class="form-select">
                        <option value="">— تلقائي —</option>
                        <option value="debit" <?= ($editAccount['nature'] ?? '') === 'debit' ? 'selected' : '' ?>>مدين</option>
                        <option value="credit" <?= ($editAccount['nature'] ?? '') === 'credit' ? 'selected' : '' ?>>دائن</option>
                    </select>
                </div>
                <!-- ٦ نوع التقرير -->
                <div class="col-md-3">
                    <label class="form-label"><span class="ob-field-num">٦</span> نوع التقرير</label>
                    <select name="report_type" class="form-select">
                        <option value="">— تلقائي —</option>
                        <option value="balance_sheet" <?= ($editAccount['report_type'] ?? '') === 'balance_sheet' ? 'selected' : '' ?>>الميزانية العمومية</option>
                        <option value="income_statement" <?= ($editAccount['report_type'] ?? '') === 'income_statement' ? 'selected' : '' ?>>الأرباح والخسائر</option>
                    </select>
                </div>
                <!-- ٧ نوع الحساب التحليلي -->
                <div class="col-md-3">
                    <label class="form-label"><span class="ob-field-num">٧</span> نوع الحساب التحليلي</label>
                    <select name="analytical_type" class="form-select">
                        <option value="">— عام —</option>
                        <option value="cash" <?= ($editAccount['analytical_type'] ?? '') === 'cash' ? 'selected' : '' ?>>صندوق</option>
                        <option value="bank" <?= ($editAccount['analytical_type'] ?? '') === 'bank' ? 'selected' : '' ?>>بنك</option>
                        <option value="customer" <?= ($editAccount['analytical_type'] ?? '') === 'customer' ? 'selected' : '' ?>>عميل</option>
                        <option value="supplier" <?= ($editAccount['analytical_type'] ?? '') === 'supplier' ? 'selected' : '' ?>>مورد</option>
                        <option value="employee_advance" <?= ($editAccount['analytical_type'] ?? '') === 'employee_advance' ? 'selected' : '' ?>>سلف موظفين</option>
                        <option value="employee_custody" <?= ($editAccount['analytical_type'] ?? '') === 'employee_custody' ? 'selected' : '' ?>>عهد موظفين</option>
                    </select>
                </div>
            </div>
            <div class="row g-2 mt-1">
                <!-- ٨ رمز العملة -->
                <div class="col-md-6">
                    <label class="form-label"><span class="ob-field-num">٨</span> رمز العملة (اختر عملة أو أكثر)</label>
                    <div style="border:1px solid #cbd5e0;border-radius:4px;padding:6px;background:#f7fafc;display:flex;gap:10px;flex-wrap:wrap">
                        <?php foreach ($currencies as $c): ?>
                            <label style="font-size:12px;display:flex;align-items:center;gap:3px;cursor:pointer">
                                <input type="checkbox" name="currency_ids[]" value="<?= $c['id'] ?>" style="width:14px;height:14px;accent-color:#2c5282" <?= $editAccount && in_array($c['id'], explode(',', $editAccount['currency_ids'] ?? '')) ? 'checked' : '' ?>>
                                <?= sanitize($c['code']) ?> — <?= sanitize($c['name_ar']) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">الرصيد الافتتاحي</label>
                    <input type="number" step="0.01" name="opening_balance" class="form-control font-mono" dir="ltr" value="<?= $editAccount['opening_balance'] ?? 0 ?>">
                </div>
                <div class="col-md-3" style="display:flex;flex-direction:column;justify-content:flex-end;gap:6px">
                    <label style="font-size:12px;display:flex;align-items:center;gap:4px;cursor:pointer">
                        <input type="checkbox" name="is_detail" style="width:14px;height:14px;accent-color:#2c5282" <?= !empty($editAccount['is_detail']) ? 'checked' : '' ?>>
                        حساب تفصيلي (يقبل القيود)
                    </label>
                    <label style="font-size:12px;display:flex;align-items:center;gap:4px;cursor:pointer">
                        <input type="checkbox" name="active" style="width:14px;height:14px;accent-color:#48bb78" <?= (!isset($editAccount) || $editAccount['active']) ? 'checked' : '' ?>>
                        نشط
                    </label>
                </div>
            </div>
            <div style="margin-top:10px;display:flex;gap:6px;justify-content:flex-end">
                <a href="<?= APP_URL ?>/setup/chart-of-accounts.php" class="ob-btn-red">إلغاء</a>
                <button type="submit" class="ob-btn-blue"><svg width="13" height="13" fill="currentColor" viewBox="0 0 20 20"><path d="M7.707 10.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l7-7a1 1 0 00-1.414-1.414L10 12.586l-2.293-2.293z"/></svg>حفظ</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- ====== شجرة الحسابات (٩) ====== -->
<div style="border:1px solid #cbd5e0;border-radius:4px;overflow:auto">
<table class="ob-tbl">
    <thead>
        <tr>
            <th style="text-align:right;padding:6px 8px">الحساب <span class="ob-field-num">٩</span></th>
            <th style="width:80px">٤ النوع</th>
            <th style="width:70px">المستوى</th>
            <th style="width:70px">٥ الطبيعة</th>
            <th style="width:120px">٦ التقرير</th>
            <th style="width:90px">٧ التحليلي</th>
            <th style="width:70px">الحالة</th>
            <th style="width:70px">إجراءات</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach (($byParent[0] ?? []) as $root) echo renderAccountNode($root, 0, $byParent, $typeLabels, $typeColors, $natureLabels, $reportLabels, $analyticalLabels, $search); ?>
    </tbody>
</table>
</div>

<!-- ملاحظات من الكتاب -->
<div class="card mt-2"><div class="card-body" style="padding:8px 12px">
<p style="font-size:11px;color:#718096;margin:0;line-height:1.6">
<strong>من نظام أونكس (صفحة 42-43):</strong><br>
• <strong>الحساب التحليلي:</strong> الصناديق → صندوق | البنوك → بنك | العملاء → عميل | الموردون → مورد | سلف الموظفين → سلف | عهد الموظفين → عهد<br>
• <strong>معادلة الميزانية:</strong> الأصول = الالتزامات − حقوق الملكية<br>
• <strong>طرق الإضافة:</strong> يدوي / استيراد ملف Excel / إضافة من حساب موجود<br>
• لا يمكن حذف حساب له أبناء أو عليه حركات محاسبية
</p>
</div></div>

<script>
function toggleNode(id) {
    var tbody = document.getElementById('children-' + id);
    var chev = document.getElementById('chev-' + id);
    if (tbody) {
        var hidden = tbody.style.display === 'none';
        tbody.style.display = hidden ? '' : 'none';
        if (chev) chev.style.transform = hidden ? 'rotate(0deg)' : 'rotate(-90deg)';
    }
}
// توسيع المستوى الأول تلقائيًا
document.addEventListener('DOMContentLoaded', function() {
    <?php foreach (($byParent[0] ?? []) as $root): ?>
    toggleNode(<?= $root['id'] ?>);
    <?php endforeach; ?>
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
