<?php
/**
 * لوحة التحكم — نظام أونكس ERP
 */
require_once __DIR__ . '/includes/header.php';

// إحصائيات
$stats = [
    'accounts'        => db_fetch_one("SELECT COUNT(*) as c FROM accounts")['c'] ?? 0,
    'cost_centers'    => db_fetch_one("SELECT COUNT(*) as c FROM cost_centers")['c'] ?? 0,
    'currencies'      => db_fetch_one("SELECT COUNT(*) as c FROM currencies")['c'] ?? 0,
    'countries'       => db_fetch_one("SELECT COUNT(*) as c FROM countries")['c'] ?? 0,
    'branches'        => db_fetch_one("SELECT COUNT(*) as c FROM branches")['c'] ?? 0,
    'users'           => db_fetch_one("SELECT COUNT(*) as c FROM users")['c'] ?? 0,
    'suppliers'       => db_fetch_one("SELECT COUNT(*) as c FROM suppliers")['c'] ?? 0,
    'customers'       => db_fetch_one("SELECT COUNT(*) as c FROM customers")['c'] ?? 0,
    'units'           => db_fetch_one("SELECT COUNT(*) as c FROM units")['c'] ?? 0,
    'periods'         => db_fetch_one("SELECT COUNT(*) as c FROM fiscal_periods")['c'] ?? 0,
    'employees'       => db_fetch_one("SELECT COUNT(*) as c FROM employees")['c'] ?? 0,
    'items'           => db_fetch_one("SELECT COUNT(*) as c FROM items")['c'] ?? 0,
    'cash_boxes'      => db_fetch_one("SELECT COUNT(*) as c FROM cash_boxes")['c'] ?? 0,
    'banks'           => db_fetch_one("SELECT COUNT(*) as c FROM banks")['c'] ?? 0,
    'vouchers'        => db_fetch_one("SELECT COUNT(*) as c FROM vouchers")['c'] ?? 0,
    'invoices'        => db_fetch_one("SELECT COUNT(*) as c FROM invoices")['c'] ?? 0,
];

// آخر النشاطات
$logs = db_fetch_all("SELECT al.*, u.full_name FROM activity_logs al LEFT JOIN users u ON al.user_id = u.id ORDER BY al.created_at DESC LIMIT 5");

// بطاقات الإحصائيات
$cards = [
    ['label' => 'الحسابات بالدليل', 'value' => $stats['accounts'], 'icon' => 'book', 'color' => 'from-indigo-500 to-indigo-600', 'url' => APP_URL . '/setup/chart-of-accounts.php'],
    ['label' => 'مراكز التكلفة', 'value' => $stats['cost_centers'], 'icon' => 'branch', 'color' => 'from-purple-500 to-purple-600', 'url' => APP_URL . '/setup/cost-centers.php'],
    ['label' => 'العملات', 'value' => $stats['currencies'], 'icon' => 'coins', 'color' => 'from-amber-500 to-amber-600', 'url' => APP_URL . '/setup/currencies.php'],
    ['label' => 'الدول', 'value' => $stats['countries'], 'icon' => 'globe', 'color' => 'from-emerald-500 to-emerald-600', 'url' => APP_URL . '/setup/countries.php'],
    ['label' => 'الفروع', 'value' => $stats['branches'], 'icon' => 'building', 'color' => 'from-cyan-500 to-cyan-600', 'url' => APP_URL . '/setup/branches.php'],
    ['label' => 'المستخدمون', 'value' => $stats['users'], 'icon' => 'users', 'color' => 'from-slate-600 to-slate-700', 'url' => APP_URL . '/system/users.php'],
    ['label' => 'الموردون', 'value' => $stats['suppliers'], 'icon' => 'truck', 'color' => 'from-rose-500 to-rose-600', 'url' => APP_URL . '/inputs/suppliers.php'],
    ['label' => 'العملاء', 'value' => $stats['customers'], 'icon' => 'users', 'color' => 'from-teal-500 to-teal-600', 'url' => APP_URL . '/inputs/customers.php'],
    ['label' => 'الأصناف', 'value' => $stats['items'], 'icon' => 'package', 'color' => 'from-blue-500 to-blue-600', 'url' => APP_URL . '/inputs/items.php'],
    ['label' => 'الموظفون', 'value' => $stats['employees'], 'icon' => 'users', 'color' => 'from-indigo-500 to-indigo-600', 'url' => APP_URL . '/inputs/employees.php'],
    ['label' => 'الصناديق', 'value' => $stats['cash_boxes'], 'icon' => 'wallet', 'color' => 'from-emerald-500 to-emerald-600', 'url' => APP_URL . '/inputs/cash-boxes.php'],
    ['label' => 'البنوك', 'value' => $stats['banks'], 'icon' => 'landmark', 'color' => 'from-blue-500 to-blue-600', 'url' => APP_URL . '/inputs/banks.php'],
    ['label' => 'السندات', 'value' => $stats['vouchers'], 'icon' => 'file-text', 'color' => 'from-purple-500 to-purple-600', 'url' => APP_URL . '/operations/payment-vouchers.php'],
    ['label' => 'الفواتير', 'value' => $stats['invoices'], 'icon' => 'file-text', 'color' => 'from-rose-500 to-rose-600', 'url' => APP_URL . '/operations/purchase-invoices.php'],
];

// مراحل بناء النظام
$phases = [
    ['number' => 1, 'title' => 'التهيئة', 'desc' => 'تهيئة النظام، العملات، الأقاليم، الشركة، الدليل المحاسبي', 'status' => 'done'],
    ['number' => 2, 'title' => 'المدخلات', 'desc' => 'الدليل المحاسبي التفصيلي، الهيكل الإداري، الموظفون، الصناديق، البنوك، الأصناف', 'status' => 'done'],
    ['number' => 3, 'title' => 'العمليات', 'desc' => 'السندات، القيود اليومية، المخزون، المشتريات، المبيعات', 'status' => 'done'],
    ['number' => 4, 'title' => 'إدارة النظام', 'desc' => 'المستخدمون، الصلاحيات، سجل النشاط، النسخ الاحتياطي', 'status' => 'in_progress'],
];
?>

<!-- بطاقة الترحيب -->
<div class="bg-gradient-to-l from-indigo-600 via-indigo-700 to-purple-700 rounded-2xl p-6 text-white shadow-xl mb-6">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="h3 fw-bold mb-1" style="font-weight: 800;">مرحبًا، <?= sanitize($user['full_name']) ?> 👋</h1>
            <p class="text-indigo-100 mb-2" style="font-size: 0.875rem;">نظام أونكس ERP — تخطيط موارد المؤسسة، الإصدار الثامن</p>
            <p class="text-indigo-200" style="font-size: 0.75rem;">منصة محاسبية متكاملة — PHP + MySQL + Bootstrap + Tailwind</p>
        </div>
        <div class="d-none d-md-flex align-items-center gap-3">
            <div class="bg-white/10 backdrop-blur rounded-xl p-3 text-center">
                <div style="font-size: 1.75rem;">📊</div>
                <p class="mb-0" style="font-size: 0.75rem;">المرحلة 4</p>
                <p class="fw-bold mb-0">إدارة النظام</p>
            </div>
        </div>
    </div>
</div>

<!-- بطاقات الإحصائيات -->
<div class="mb-4 d-flex align-items-center justify-content-between">
    <h5 class="fw-bold text-slate-800 mb-0">إحصائيات النظام</h5>
    <span class="text-xs text-slate-500">انقر على البطاقة للانتقال إلى الشاشة</span>
</div>

<div class="row g-3 mb-6">
    <?php foreach ($cards as $card): ?>
        <div class="col-6 col-md-3 col-lg-2">
            <a href="<?= $card['url'] ?>" class="text-decoration-none">
                <div class="stat-card h-100 d-flex flex-column">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="stat-icon bg-gradient-to-br <?= $card['color'] ?>" style="background: linear-gradient(135deg, var(--tw-gradient-stops));">
                            <?php if ($card['icon'] === 'book'): ?>
                                <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24" class="text-white"><path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                            <?php elseif ($card['icon'] === 'branch'): ?>
                                <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24" class="text-white"><circle cx="6" cy="6" r="3"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="12" r="3"/><path stroke="currentColor" stroke-width="2" d="M6 9v6M9 6h6a3 3 0 013 3v0M15 12h-3"/></svg>
                            <?php elseif ($card['icon'] === 'coins'): ?>
                                <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24" class="text-white"><circle cx="12" cy="12" r="9"/></svg>
                            <?php elseif ($card['icon'] === 'globe'): ?>
                                <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24" class="text-white"><circle cx="12" cy="12" r="9"/></svg>
                            <?php elseif ($card['icon'] === 'building'): ?>
                                <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24" class="text-white"><path d="M3 21h18M3 10h18M5 6l7-3 7 3M4 10v11m4-11v11m4-11v11m4-11v11m4-11v11"/></svg>
                            <?php elseif ($card['icon'] === 'users'): ?>
                                <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24" class="text-white"><path d="M12 12a5 5 0 100-10 5 5 0 000 10zM2 22a10 10 0 0120 0H2z"/></svg>
                            <?php elseif ($card['icon'] === 'truck'): ?>
                                <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24" class="text-white"><path d="M3 6h11v9H3V6zm11 4h4l3 3v2h-7v-5zM7 18a2 2 0 100-4 2 2 0 000 4zm10 0a2 2 0 100-4 2 2 0 000 4z"/></svg>
                            <?php elseif ($card['icon'] === 'package'): ?>
                                <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24" class="text-white"><path d="M12 2l9 5v10l-9 5-9-5V7l9-5z"/></svg>
                            <?php elseif ($card['icon'] === 'wallet'): ?>
                                <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24" class="text-white"><path d="M3 6h16v12H3V6zm0 0V4a1 1 0 011-1h13v3M16 12a1 1 0 100-2 1 1 0 000 2z"/></svg>
                            <?php elseif ($card['icon'] === 'landmark'): ?>
                                <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24" class="text-white"><path d="M3 21h18M3 10h18M5 6l7-3 7 3M4 10v11m4-11v11m4-11v11m4-11v11m4-11v11"/></svg>
                            <?php else: ?>
                                <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24" class="text-white"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <?php endif; ?>
                        </div>
                    </div>
                    <p class="h4 fw-extrabold text-slate-800 mb-0" style="font-weight: 800;"><?= number_format($card['value']) ?></p>
                    <p class="text-xs text-slate-500 mt-1 mb-0" style="font-size: 0.75rem;"><?= $card['label'] ?></p>
                </div>
            </a>
        </div>
    <?php endforeach; ?>
</div>

<!-- مراحل بناء النظام -->
<div class="card mb-4">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span>مراحل بناء النظام</span>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <?php foreach ($phases as $phase): ?>
                <?php
                    $color = $phase['status'] === 'done' ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                            : ($phase['status'] === 'in_progress' ? 'bg-amber-50 text-amber-700 border-amber-200'
                            : 'bg-slate-50 text-slate-500 border-slate-200');
                    $dotColor = $phase['status'] === 'done' ? 'bg-emerald-500' : ($phase['status'] === 'in_progress' ? 'bg-amber-500' : 'bg-slate-300');
                    $label = $phase['status'] === 'done' ? 'مكتمل' : ($phase['status'] === 'in_progress' ? 'قيد التنفيذ' : 'قريبًا');
                ?>
                <div class="col-md-3">
                    <div class="border rounded p-3 <?= $color ?>">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="rounded-circle bg-white d-flex align-items-center justify-content-center" style="width: 24px; height: 24px; font-size: 12px; font-weight: 700;"><?= $phase['number'] ?></span>
                            <h6 class="fw-bold mb-0"><?= $phase['title'] ?></h6>
                            <span class="rounded-circle ms-auto <?= $dotColor ?>" style="width: 8px; height: 8px;"></span>
                        </div>
                        <p class="small mb-1" style="font-size: 0.75rem;"><?= $phase['desc'] ?></p>
                        <span class="badge bg-white text-dark" style="font-size: 0.65rem;"><?= $label ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- آخر النشاطات -->
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span>آخر النشاطات</span>
        <a href="<?= APP_URL ?>/system/activity-logs.php" class="text-decoration-none small">عرض الكل</a>
    </div>
    <div class="card-body">
        <?php if (empty($logs)): ?>
            <p class="text-center text-slate-400 py-3">لا توجد نشاطات حديثة</p>
        <?php else: ?>
            <ul class="list-group list-group-flush">
                <?php foreach ($logs as $log): ?>
                    <li class="list-group-item d-flex align-items-center gap-3 px-0">
                        <div class="rounded-circle bg-indigo-50 d-flex align-items-center justify-content-center text-indigo-600" style="width: 32px; height: 32px; font-size: 12px; font-weight: 700;">
                            <?= mb_substr($log['full_name'] ?? '؟', 0, 1) ?>
                        </div>
                        <div class="flex-1">
                            <p class="mb-0 text-sm fw-medium text-slate-700">
                                <?= sanitize($log['full_name'] ?? 'مستخدم') ?>
                                <span class="text-slate-500 fw-normal"> — <?= sanitize($log['details'] ?? $log['action']) ?></span>
                            </p>
                            <p class="text-xs text-slate-400 mb-0">
                                <?php if (!empty($log['screen'])): ?><span>شاشة: <?= sanitize($log['screen']) ?> · </span><?php endif; ?>
                                <?= date('Y-m-d H:i', strtotime($log['created_at'])) ?>
                            </p>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
