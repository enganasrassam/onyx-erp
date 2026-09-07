<?php
/**
 * الدليل المحاسبي — مطابق 100% لنظام أونكس ERP (صفحة 41-43)
 *
 * ٩ حقول كما في الكتاب بالضبط:
 * ١ الحساب الرئيسي: الأب للحساب الجديد
 * ٢ رقم الحساب: يُولد آليًا أو يُكتب يدويًا
 * ٣ اسم الحساب: اسم الحساب المراد إضافته
 * ٤ النوع: رئيسي أم فرعي
 * ٥ طبيعة الحساب: مدين أو دائن
 * ٦ نوع التقرير: الميزانية العمومية / الأرباح والخسائر
 * ٧ نوع الحساب التحليلي: صندوق/بنك/عميل/مورد/ذمم موظفين/عام
 * ٨ رمز العملة: اختيار واحد أو أكثر
 * ٩ شجرة الحسابات: الدليل المحاسبي (تظهر على نفس الصفحة)
 *
 * التصميم: النموذج والشجرة في نفس الصفحة (مطابق لأونكس)
 */
require_once __DIR__ . '/../includes/header.php';

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

// ====== جلب كل الحسابات للشجرة ======
$allAccounts = db_fetch_all("SELECT * FROM accounts ORDER BY code");
$currencies = db_fetch_all("SELECT * FROM currencies WHERE active = 1 ORDER BY code");

// بناء الشجرة
$byParent = [];
foreach ($allAccounts as $a) {
    $pid = $a['parent_id'] ?? 0;
    if (!isset($byParent[$pid])) $byParent[$pid] = [];
    $byParent[$pid][] = $a;
}

// الحساب المراد تعديله
$editAccount = $editId ? db_fetch_one("SELECT * FROM accounts WHERE id = ?", [$editId]) : null;

// خيارات الحسابات الرئيسية للقائمة المنسدلة
$parentOptions = db_fetch_all("SELECT id, code, name_ar, level FROM accounts WHERE account_nature = 'main' OR level <= 2 ORDER BY code");

// ====== معالجة الإضافة/التعديل ======
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $parentId = (int)($_POST['parent_id'] ?? 0);
    $code = trim($_POST['code'] ?? '');
    $nameAr = trim($_POST['name_ar'] ?? '');
    $accountType = $_POST['account_type'] ?? 'asset';
    $accountNature = $_POST['account_nature'] ?? 'sub';
    $nature = $_POST['nature'] ?? 'debit';
    $reportType = $_POST['report_type'] ?? 'balance_sheet';
    $analyticalType = $_POST['analytical_type'] ?? 'general';
    $active = isset($_POST['active']) ? 1 : 0;
    $isDetail = isset($_POST['is_detail']) ? 1 : 0;
    $currencyIds = isset($_POST['currency_ids']) ? implode(',', $_POST['currency_ids']) : null;
    $eid = (int)($_POST['edit_id'] ?? 0);

    // إذا كان حساب فرعي، اجعله تفصيليًا تلقائيًا (يقبل الأرصدة والقيود)
    if ($accountNature === 'sub') {
        $isDetail = 1;
    }

    // تحديد المستوى بناءً على الحساب الأب
    $level = 1;
    if ($parentId) {
        $parent = db_fetch_one("SELECT level FROM accounts WHERE id = ?", [$parentId]);
        if ($parent) $level = $parent['level'] + 1;
    }

    // توليد رقم الحساب آليًا إذا كان فارغًا
    if (empty($code)) {
        if ($parentId) {
            $parentCode = db_fetch_one("SELECT code FROM accounts WHERE id = ?", [$parentId]);
            $childCount = db_fetch_one("SELECT COUNT(*) as c FROM accounts WHERE parent_id = ?", [$parentId]);
            $code = $parentCode['code'] . str_pad($childCount['c'] + 1, 2, '0', STR_PAD_LEFT);
        } else {
            $maxCode = db_fetch_one("SELECT MAX(CAST(code AS UNSIGNED)) as mx FROM accounts WHERE parent_id IS NULL");
            $code = (int)$maxCode['mx'] + 1;
        }
    }

    if (!$nameAr) {
        flash('error', 'اسم الحساب مطلوب');
    } else {
        $data = [
            'code' => $code,
            'name_ar' => $nameAr,
            'parent_id' => $parentId ?: null,
            'account_type' => $accountType,
            'account_nature' => $accountNature,
            'level' => $level,
            'is_detail' => $isDetail,
            'active' => $active,
            'nature' => $nature,
            'report_type' => $reportType,
            'analytical_type' => $analyticalType,
            'currency_ids' => $currencyIds,
        ];
        if ($eid) {
            db_update('accounts', $data, 'id = ?', [$eid]);
            flash('success', 'تم تحديث الحساب');
        } else {
            try {
                db_insert('accounts', $data);
                flash('success', 'تمت إضافة الحساب: ' . $code);
            } catch (Exception $e) {
                flash('error', 'رمز الحساب موجود مسبقًا');
            }
        }
    }
    redirect(APP_URL . '/setup/chart-of-accounts.php');
}

// الأنواع
$typeLabels = ['asset'=>'أصول','liability'=>'خصوم','equity'=>'حقوق ملكية','revenue'=>'إيرادات','expense'=>'مصروفات'];
$analyticalLabels = [
    'cash'=>'صندوق','bank'=>'بنك','customer'=>'عميل','supplier'=>'مورد',
    'employee_advance'=>'ذمم موظفين','employee_custody'=>'ذمم موظفين','general'=>'عام'
];

// دالة عرض عقدة الشجرة
function renderNode($node, $depth, $byParent, $typeLabels, $analyticalLabels) {
    $children = $byParent[$node['id']] ?? [];
    $hasChildren = count($children) > 0;
    $typeLabel = $typeLabels[$node['account_type']] ?? $node['account_type'];

    $html = '<div class="tree-node" data-id="' . $node['id'] . '">';
    $html .= '<div class="tree-row" style="padding-right:' . ($depth * 20 + 8) . 'px">';
    $html .= '<div class="tree-content">';

    // أيقونة الطي/التوسيع
    if ($hasChildren) {
        $html .= '<button class="tree-toggle" onclick="toggleTreeNode(' . $node['id'] . ')">';
        $html .= '<svg id="chev-' . $node['id'] . '" width="12" height="12" fill="none" stroke="#718096" viewBox="0 0 24 24" style="transform:rotate(-90deg);transition:transform 0.15s"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>';
        $html .= '</button>';
    } else {
        $html .= '<span class="tree-toggle-placeholder"></span>';
    }

    // أيقونة المجلد/الملف
    if ($hasChildren) {
        $html .= '<svg width="14" height="14" fill="#ecc94b" viewBox="0 0 24 24"><path d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>';
    } else {
        $html .= '<svg width="14" height="14" fill="#a0aec0" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6z"/></svg>';
    }

    // رقم الحساب
    $html .= '<code class="tree-code">' . htmlspecialchars($node['code']) . '</code>';

    // اسم الحساب
    $html .= '<span class="tree-name">' . htmlspecialchars($node['name_ar']) . '</span>';

    // نوع الحساب (رئيسي/فرعي)
    $html .= '<span class="tree-badge ' . ($node['account_nature'] === 'main' ? 'badge-main' : 'badge-sub') . '">' . ($node['account_nature'] === 'main' ? 'رئيسي' : 'فرعي') . '</span>';

    // النوع (أصول/خصوم/إلخ)
    $typeColors = ['asset'=>'badge-asset','liability'=>'badge-liab','equity'=>'badge-eq','revenue'=>'badge-rev','expense'=>'badge-exp'];
    $html .= '<span class="tree-badge ' . ($typeColors[$node['account_type']] ?? 'badge-gen') . '">' . $typeLabel . '</span>';

    // طبيعة الحساب
    if (!empty($node['nature'])) {
        $nColor = $node['nature'] === 'debit' ? 'color:#22543d' : 'color:#9b2c2c';
        $nLabel = $node['nature'] === 'debit' ? 'مدين' : 'دائن';
        $html .= '<span class="tree-nature" style="' . $nColor . '">' . $nLabel . '</span>';
    }

    // نوع الحساب التحليلي
    if (!empty($node['analytical_type']) && $node['analytical_type'] !== 'general') {
        $html .= '<span class="tree-analytical">' . ($analyticalLabels[$node['analytical_type']] ?? $node['analytical_type']) . '</span>';
    }

    // تفصيلي
    if ($node['is_detail']) {
        $html .= '<span class="tree-badge badge-detail">تفصيلي</span>';
    }

    // الحالة
    $html .= '<span class="tree-status ' . ($node['active'] ? 'status-active' : 'status-inactive') . '">' . ($node['active'] ? 'نشط' : 'غير نشط') . '</span>';

    // الأزرار
    $html .= '<div class="tree-actions">';
    $html .= '<a href="?edit=' . $node['id'] . '" class="tree-btn" title="تعديل"><svg width="12" height="12" fill="#2c5282" viewBox="0 0 20 20"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/></svg></a>';
    if (!$hasChildren) {
        $html .= '<a href="?delete=' . $node['id'] . '" class="tree-btn" title="حذف" onclick="return confirm(\'حذف الحساب؟\')"><svg width="12" height="12" fill="#c53030" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9z" clip-rule="evenodd"/></svg></a>';
    }
    $html .= '</div>';

    $html .= '</div>'; // tree-content
    $html .= '</div>'; // tree-row

    if ($hasChildren) {
        $html .= '<div class="tree-children" id="children-' . $node['id'] . '" style="display:none">';
        foreach ($children as $child) {
            $html .= renderNode($child, $depth + 1, $byParent, $typeLabels, $analyticalLabels);
        }
        $html .= '</div>';
    }

    $html .= '</div>';
    return $html;
}
?>
<style>
/* تنسيق الدليل المحاسبي — مطابق لأونكس */
.coa-layout{display:flex;gap:12px;align-items:flex-start}
.coa-form-side{flex:0 0 380px;position:sticky;top:60px}
.coa-tree-side{flex:1;min-width:0}
.coa-form-card{background:#fff;border:1px solid #cbd5e0;border-radius:6px;overflow:hidden}
.coa-form-hdr{background:#2c5282;color:#fff;padding:8px 12px;font-weight:700;font-size:13px;display:flex;justify-content:space-between;align-items:center}
.coa-form-body{padding:12px;background:#f7fafc}
.coa-field{margin-bottom:8px}
.coa-field-label{display:flex;align-items:center;gap:6px;font-size:11px;font-weight:600;color:#2d3748;margin-bottom:3px}
.coa-num{display:inline-flex;align-items:center;justify-content:center;width:18px;height:18px;border-radius:50%;background:#2c5282;color:#fff;font-size:10px;font-weight:700;flex-shrink:0}
.coa-input,.coa-select{width:100%;padding:5px 8px;font-size:12px;border:1px solid #cbd5e0;border-radius:4px;background:#fff;font-family:inherit;outline:none}
.coa-input:focus,.coa-select:focus{border-color:#3182ce;box-shadow:0 0 0 2px rgba(49,130,206,0.15)}
.coa-input-yellow{background:#fffbeb !important}
.coa-checkbox-row{display:flex;gap:10px;flex-wrap:wrap;padding:6px;border:1px solid #e2e8f0;border-radius:4px;background:#fff}
.coa-checkbox-row label{font-size:11px;display:flex;align-items:center;gap:3px;cursor:pointer}
.coa-checkbox-row input{width:14px;height:14px;accent-color:#2c5282}
.coa-btn-save{background:#3182ce;color:#fff !important;border:1px solid #2c5282;padding:6px 16px;border-radius:4px;font-size:12px;font-weight:600;cursor:pointer;text-decoration:none;font-family:inherit;display:inline-flex;align-items:center;gap:4px}
.coa-btn-save:hover{background:#2c5282}
.coa-btn-close{background:#e53e3e;color:#fff !important;border:1px solid #c53030;padding:6px 16px;border-radius:4px;font-size:12px;font-weight:600;cursor:pointer;text-decoration:none;font-family:inherit;display:inline-flex;align-items:center;gap:4px}
.coa-btn-add{background:#48bb78;color:#fff !important;border:1px solid #2f855a;padding:5px 12px;border-radius:4px;font-size:12px;font-weight:600;cursor:pointer;text-decoration:none;font-family:inherit;display:inline-flex;align-items:center;gap:4px}
.coa-btn-add:hover{background:#2f855a}
.coa-toolbar{background:#d9e2ec;border:1px solid #b0bec5;border-radius:4px;padding:4px;margin-bottom:8px;display:flex;align-items:center;gap:2px;flex-wrap:wrap}
.coa-tb-btn{display:inline-flex;align-items:center;gap:3px;padding:4px 8px;font-size:11px;font-weight:500;color:#2d3748;background:transparent;border:1px solid transparent;border-radius:3px;cursor:pointer;text-decoration:none;font-family:inherit}
.coa-tb-btn:hover{background:#fff;border-color:#b0bec5}

/* شجرة الحسابات */
.tree-container{background:#fff;border:1px solid #cbd5e0;border-radius:6px;overflow:auto;max-height:calc(100vh - 140px)}
.tree-node{border-bottom:1px solid #edf2f7}
.tree-row{padding:5px 8px;display:flex;align-items:center;transition:background 0.1s}
.tree-row:hover{background:#ebf8ff}
.tree-content{display:flex;align-items:center;gap:6px;flex:1;flex-wrap:wrap}
.tree-toggle{border:none;background:none;cursor:pointer;padding:2px;display:flex;align-items:center}
.tree-toggle-placeholder{width:16px;display:inline-block}
.tree-code{font-size:11px;background:#fffbeb;padding:1px 6px;border-radius:3px;font-weight:600;font-family:'Cairo',monospace;border:1px solid #ecc94b;color:#744210}
.tree-name{font-size:12px;color:#2d3748;font-weight:500}
.tree-badge{font-size:9px;padding:1px 6px;border-radius:9999px;font-weight:600;white-space:nowrap}
.badge-main{background:#ebf8ff;color:#2c5282}
.badge-sub{background:#f1f5f9;color:#718096}
.badge-asset{background:#dbeafe;color:#1e40af}
.badge-liab{background:#fee2e2;color:#991b1b}
.badge-eq{background:#f3e8ff;color:#6b21a8}
.badge-rev{background:#d1fae5;color:#065f46}
.badge-exp{background:#fef3c7;color:#92400e}
.badge-gen{background:#f1f5f9;color:#475569}
.badge-detail{background:#fed7aa;color:#9c4221}
.tree-nature{font-size:10px;font-weight:600;padding:1px 6px;background:#f7fafc;border-radius:3px}
.tree-analytical{font-size:9px;padding:1px 6px;border-radius:3px;background:#e6fffa;color:#234e52;font-weight:600}
.tree-status{font-size:9px;padding:1px 6px;border-radius:9999px;font-weight:600}
.status-active{background:#c6f6d5;color:#22543d}
.status-inactive{background:#fed7d7;color:#9b2c2c}
.tree-actions{display:flex;gap:3px;margin-right:auto}
.tree-btn{padding:2px 4px;text-decoration:none;display:flex;align-items:center}
.tree-children{border-right:2px solid #e2e8f0}

@media(max-width:992px){.coa-layout{flex-direction:column}.coa-form-side{position:relative;flex:1;width:100%}}
</style>

<!-- عنوان + شريط أدوات -->
<div class="coa-toolbar">
    <a href="?add=1" class="coa-btn-add"><svg width="13" height="13" fill="currentColor" viewBox="0 0 20 20"><path d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"/></svg>إضافة حساب</a>
    <div class="toolbar-divider"></div>
    <button class="coa-tb-btn" onclick="window.location.reload()"><svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>تحديث</button>
    <button class="coa-tb-btn" onclick="expandAll()"><svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>توسيع الكل</button>
    <button class="coa-tb-btn" onclick="collapseAll()"><svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>طي الكل</button>
    <div style="margin-right:auto;position:relative">
        <input type="text" id="treeSearch" placeholder="بحث برقم أو اسم الحساب..." oninput="searchTree(this.value)" style="width:220px;padding:4px 28px 4px 8px;font-size:11px;border:1px solid #b0bec5;border-radius:3px;background:#fff;outline:none;font-family:inherit">
        <svg width="13" height="13" fill="none" stroke="#a0aec0" viewBox="0 0 24 24" style="position:absolute;right:6px;top:50%;transform:translateY(-50%)"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
    </div>
</div>

<!-- ====== التخطيط: النموذج يسار + الشجرة يمين (نفس صفحة أونكس) ====== -->
<div class="coa-layout">

    <!-- ====== النموذج (يسار الصفحة) ====== -->
    <?php if ($showForm): ?>
    <div class="coa-form-side">
        <div class="coa-form-card">
            <div class="coa-form-hdr">
                <span><?= $editAccount ? 'تعديل حساب' : 'إضافة حساب جديد' ?></span>
                <a href="<?= APP_URL ?>/setup/chart-of-accounts.php" style="color:#fff;text-decoration:none;font-size:16px">✕</a>
            </div>
            <div class="coa-form-body">
                <form method="POST" id="crudForm">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <input type="hidden" name="edit_id" value="<?= $editAccount['id'] ?? '' ?>">

                    <!-- ١ الحساب الرئيسي -->
                    <div class="coa-field">
                        <label class="coa-field-label"><span class="coa-num">١</span> الحساب الرئيسي</label>
                        <select name="parent_id" class="coa-select">
                            <option value="">— بدون (حساب رئيسي) —</option>
                            <?php foreach ($parentOptions as $a): ?>
                                <option value="<?= $a['id'] ?>" <?= ($editAccount['parent_id'] ?? 0) == $a['id'] ? 'selected' : '' ?>><?= sanitize($a['code']) ?> — <?= sanitize($a['name_ar']) ?> (مستوى <?= $a['level'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- ٢ رقم الحساب -->
                    <div class="coa-field">
                        <label class="coa-field-label"><span class="coa-num">٢</span> رقم الحساب (اتركه فارغًا للتوليد الآلي)</label>
                        <input type="text" name="code" class="coa-input coa-input-yellow font-mono" dir="ltr" value="<?= sanitize($editAccount['code'] ?? '') ?>" <?= $editAccount ? 'readonly' : '' ?> placeholder="تلقائي">
                    </div>

                    <!-- ٣ اسم الحساب -->
                    <div class="coa-field">
                        <label class="coa-field-label"><span class="coa-num">٣</span> اسم الحساب *</label>
                        <input type="text" name="name_ar" class="coa-input coa-input-yellow" value="<?= sanitize($editAccount['name_ar'] ?? '') ?>" required placeholder="مثال: الصندوق الرئيسي">
                    </div>

                    <!-- ٤ النوع (رئيسي / فرعي) -->
                    <div class="coa-field">
                        <label class="coa-field-label"><span class="coa-num">٤</span> النوع</label>
                        <select name="account_nature" class="coa-select" onchange="toggleType(this.value)">
                            <option value="main" <?= ($editAccount['account_nature'] ?? '') === 'main' ? 'selected' : '' ?>>حساب رئيسي</option>
                            <option value="sub" <?= ($editAccount['account_nature'] ?? 'sub') === 'sub' ? 'selected' : '' ?>>حساب فرعي</option>
                        </select>
                    </div>

                    <!-- تصنيف الحساب (أصول/خصوم/إلخ) -->
                    <div class="coa-field" id="type-field">
                        <label class="coa-field-label">تصنيف الحساب</label>
                        <select name="account_type" class="coa-select">
                            <option value="asset" <?= ($editAccount['account_type'] ?? '') === 'asset' ? 'selected' : '' ?>>أصول</option>
                            <option value="liability" <?= ($editAccount['account_type'] ?? '') === 'liability' ? 'selected' : '' ?>>خصوم</option>
                            <option value="equity" <?= ($editAccount['account_type'] ?? '') === 'equity' ? 'selected' : '' ?>>حقوق ملكية</option>
                            <option value="revenue" <?= ($editAccount['account_type'] ?? '') === 'revenue' ? 'selected' : '' ?>>إيرادات</option>
                            <option value="expense" <?= ($editAccount['account_type'] ?? '') === 'expense' ? 'selected' : '' ?>>مصروفات</option>
                        </select>
                    </div>

                    <!-- ٥ طبيعة الحساب -->
                    <div class="coa-field">
                        <label class="coa-field-label"><span class="coa-num">٥</span> طبيعة الحساب</label>
                        <select name="nature" class="coa-select">
                            <option value="debit" <?= ($editAccount['nature'] ?? '') === 'debit' ? 'selected' : '' ?>>مدين</option>
                            <option value="credit" <?= ($editAccount['nature'] ?? '') === 'credit' ? 'selected' : '' ?>>دائن</option>
                        </select>
                    </div>

                    <!-- ٦ نوع التقرير -->
                    <div class="coa-field">
                        <label class="coa-field-label"><span class="coa-num">٦</span> نوع التقرير</label>
                        <select name="report_type" class="coa-select">
                            <option value="balance_sheet" <?= ($editAccount['report_type'] ?? '') === 'balance_sheet' ? 'selected' : '' ?>>الميزانية العمومية (الأصول والخصوم)</option>
                            <option value="income_statement" <?= ($editAccount['report_type'] ?? '') === 'income_statement' ? 'selected' : '' ?>>الأرباح والخسائر (المصروفات والإيرادات)</option>
                        </select>
                    </div>

                    <!-- ٧ نوع الحساب التحليلي -->
                    <div class="coa-field">
                        <label class="coa-field-label"><span class="coa-num">٧</span> نوع الحساب التحليلي</label>
                        <select name="analytical_type" class="coa-select">
                            <option value="general" <?= ($editAccount['analytical_type'] ?? 'general') === 'general' ? 'selected' : '' ?>>عام</option>
                            <option value="cash" <?= ($editAccount['analytical_type'] ?? '') === 'cash' ? 'selected' : '' ?>>صندوق — لربط بشاشة الصناديق</option>
                            <option value="bank" <?= ($editAccount['analytical_type'] ?? '') === 'bank' ? 'selected' : '' ?>>بنك — لربط بشاشة البنوك</option>
                            <option value="customer" <?= ($editAccount['analytical_type'] ?? '') === 'customer' ? 'selected' : '' ?>>عميل — لربط بشاشة العملاء</option>
                            <option value="supplier" <?= ($editAccount['analytical_type'] ?? '') === 'supplier' ? 'selected' : '' ?>>مورد — لربط بشاشة الموردين</option>
                            <option value="employee_advance" <?= ($editAccount['analytical_type'] ?? '') === 'employee_advance' ? 'selected' : '' ?>>ذمم موظفين — لربط بالسلف</option>
                            <option value="employee_custody" <?= ($editAccount['analytical_type'] ?? '') === 'employee_custody' ? 'selected' : '' ?>>ذمم موظفين — لربط بالعهد</option>
                        </select>
                    </div>

                    <!-- ٨ رمز العملة -->
                    <div class="coa-field">
                        <label class="coa-field-label"><span class="coa-num">٨</span> رمز العملة (اختر عملة أو أكثر)</label>
                        <div class="coa-checkbox-row">
                            <?php foreach ($currencies as $c): ?>
                                <label><input type="checkbox" name="currency_ids[]" value="<?= $c['id'] ?>" <?= $editAccount && in_array($c['id'], explode(',', $editAccount['currency_ids'] ?? '')) ? 'checked' : '' ?>> <?= sanitize($c['code']) ?> — <?= sanitize($c['name_ar']) ?></label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- خيارات -->
                    <div class="coa-field">
                        <label style="font-size:12px;display:flex;align-items:center;gap:4px;cursor:pointer">
                            <input type="checkbox" name="is_detail" style="width:14px;height:14px;accent-color:#2c5282" <?= !empty($editAccount['is_detail']) ? 'checked' : '' ?>>
                            حساب تفصيلي (يقبل القيود)
                        </label>
                        <label style="font-size:12px;display:flex;align-items:center;gap:4px;cursor:pointer;margin-top:4px">
                            <input type="checkbox" name="active" style="width:14px;height:14px;accent-color:#48bb78" <?= (!isset($editAccount) || $editAccount['active']) ? 'checked' : '' ?>>
                            نشط
                        </label>
                    </div>

                    <!-- الأزرار -->
                    <div style="display:flex;gap:6px;justify-content:flex-end;margin-top:10px">
                        <a href="<?= APP_URL ?>/setup/chart-of-accounts.php" class="coa-btn-close">إلغاء</a>
                        <button type="submit" class="coa-btn-save"><svg width="13" height="13" fill="currentColor" viewBox="0 0 20 20"><path d="M7.707 10.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l7-7a1 1 0 00-1.414-1.414L10 12.586l-2.293-2.293z"/></svg>حفظ</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- ====== ٩ شجرة الحسابات (الدليل المحاسبي) ====== -->
    <div class="coa-tree-side">
        <div style="background:#2c5282;color:#fff;padding:6px 12px;border-radius:6px 6px 0 0;font-weight:700;font-size:13px;display:flex;align-items:center;gap:6px">
            <span class="coa-num" style="background:#fff;color:#2c5282">٩</span>
            شجرة الحسابات — الدليل المحاسبي
            <span style="margin-right:auto;font-size:11px;font-weight:400"><?= count($allAccounts) ?> حساب</span>
        </div>
        <div class="tree-container">
            <?php foreach (($byParent[0] ?? []) as $root) echo renderNode($root, 0, $byParent, $typeLabels, $analyticalLabels); ?>
        </div>

        <!-- ملاحظات من الكتاب -->
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:6px;padding:8px 12px;margin-top:8px">
            <p style="font-size:11px;color:#718096;margin:0;line-height:1.6">
                <strong>من نظام أونكس (صفحة 42):</strong><br>
                • الصناديق → ربط بنوع (صندوق) | البنوك → (بنك) | العملاء → (عميل) | الموردون → (مورد) | سلف الموظفين → (ذمم موظفين) | عهد الموظفين → (ذمم موظفين) | بقية الحسابات → (عام)<br>
                • <strong>معادلة الميزانية:</strong> الأصول = الالتزامات − حقوق الملكية<br>
                • رقم الحساب يُولد آليًا إذا تُرك فارغًا أو عند تفعيل التوليد الآلي<br>
                • لا يمكن حذف حساب له أبناء
            </p>
        </div>
    </div>
</div>

<script>
function toggleTreeNode(id) {
    var children = document.getElementById('children-' + id);
    var chev = document.getElementById('chev-' + id);
    if (children) {
        var hidden = children.style.display === 'none';
        children.style.display = hidden ? '' : 'none';
        if (chev) chev.style.transform = hidden ? 'rotate(0deg)' : 'rotate(-90deg)';
    }
}
function expandAll() {
    document.querySelectorAll('.tree-children').forEach(function(c) { c.style.display = ''; });
    document.querySelectorAll('[id^="chev-"]').forEach(function(c) { c.style.transform = 'rotate(0deg)'; });
}
function collapseAll() {
    document.querySelectorAll('.tree-children').forEach(function(c) { c.style.display = 'none'; });
    document.querySelectorAll('[id^="chev-"]').forEach(function(c) { c.style.transform = 'rotate(-90deg)'; });
}
function searchTree(q) {
    q = q.trim().toLowerCase();
    if (!q) {
        document.querySelectorAll('.tree-node').forEach(function(n) { n.style.display = ''; });
        return;
    }
    document.querySelectorAll('.tree-node').forEach(function(n) {
        var text = n.textContent.toLowerCase();
        n.style.display = text.includes(q) ? '' : 'none';
        if (text.includes(q)) {
            var parent = n.parentElement;
            while (parent && parent.classList.contains('tree-children')) {
                parent.style.display = '';
                var prev = parent.previousElementSibling;
                if (prev) {
                    var chev = prev.querySelector('[id^="chev-"]');
                    if (chev) chev.style.transform = 'rotate(0deg)';
                }
                var pNode = parent.closest('.tree-node');
                if (pNode) pNode.style.display = '';
                parent = pNode ? pNode.parentElement : null;
            }
        }
    });
}
function toggleType(val) {
    var typeField = document.getElementById('type-field');
    if (typeField) typeField.style.display = val === 'main' ? 'none' : 'block';
}
// توسيع المستوى الأول
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.tree-node > .tree-children').forEach(function(c) {
        c.style.display = '';
        var chev = c.previousElementSibling?.querySelector('[id^="chev-"]');
        if (chev) chev.style.transform = 'rotate(0deg)';
    });
    // تفعيل نوع الحساب عند التحميل
    var nature = document.querySelector('[name="account_nature"]');
    if (nature) toggleType(nature.value);
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
