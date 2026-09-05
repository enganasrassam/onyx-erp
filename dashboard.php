<?php
/**
 * لوحة التحكم — نظام أونكس ERP
 * مطابقة لتصميم أونكس الأصلي
 */
$pageTitle = 'لوحة التحكم';
$activeMenu = 'dashboard';
require_once __DIR__ . '/includes/header.php';

// إحصائيات
$stats = [
    'accounts'     => db_fetch_one("SELECT COUNT(*) as c FROM accounts")['c'] ?? 0,
    'cost_centers'  => db_fetch_one("SELECT COUNT(*) as c FROM cost_centers")['c'] ?? 0,
    'currencies'    => db_fetch_one("SELECT COUNT(*) as c FROM currencies")['c'] ?? 0,
    'countries'     => db_fetch_one("SELECT COUNT(*) as c FROM countries")['c'] ?? 0,
    'branches'      => db_fetch_one("SELECT COUNT(*) as c FROM branches")['c'] ?? 0,
    'users'         => db_fetch_one("SELECT COUNT(*) as c FROM users")['c'] ?? 0,
    'suppliers'     => db_fetch_one("SELECT COUNT(*) as c FROM suppliers")['c'] ?? 0,
    'customers'     => db_fetch_one("SELECT COUNT(*) as c FROM customers")['c'] ?? 0,
    'units'         => db_fetch_one("SELECT COUNT(*) as c FROM units")['c'] ?? 0,
    'periods'       => db_fetch_one("SELECT COUNT(*) as c FROM fiscal_periods")['c'] ?? 0,
    'employees'     => db_fetch_one("SELECT COUNT(*) as c FROM employees")['c'] ?? 0,
    'items'         => db_fetch_one("SELECT COUNT(*) as c FROM items")['c'] ?? 0,
    'cash_boxes'    => db_fetch_one("SELECT COUNT(*) as c FROM cash_boxes")['c'] ?? 0,
    'banks'         => db_fetch_one("SELECT COUNT(*) as c FROM banks")['c'] ?? 0,
    'vouchers'      => db_fetch_one("SELECT COUNT(*) as c FROM vouchers")['c'] ?? 0,
    'invoices'      => db_fetch_one("SELECT COUNT(*) as c FROM invoices")['c'] ?? 0,
];

$logs = db_fetch_all("SELECT al.*, u.full_name FROM activity_logs al LEFT JOIN users u ON al.user_id = u.id ORDER BY al.created_at DESC LIMIT 5");

// بطاقات الإحصائيات
$cards = [
    ['label' => 'الحسابات بالدليل', 'value' => $stats['accounts'], 'icon' => 'book', 'gradient' => 'gradient-indigo', 'url' => APP_URL . '/setup/chart-of-accounts.php'],
    ['label' => 'مراكز التكلفة', 'value' => $stats['cost_centers'], 'icon' => 'branch', 'gradient' => 'gradient-purple', 'url' => APP_URL . '/setup/cost-centers.php'],
    ['label' => 'العملات', 'value' => $stats['currencies'], 'icon' => 'coins', 'gradient' => 'gradient-amber', 'url' => APP_URL . '/setup/currencies.php'],
    ['label' => 'الدول', 'value' => $stats['countries'], 'icon' => 'globe', 'gradient' => 'gradient-emerald', 'url' => APP_URL . '/setup/countries.php'],
    ['label' => 'الفروع', 'value' => $stats['branches'], 'icon' => 'building', 'gradient' => 'gradient-cyan', 'url' => APP_URL . '/setup/branches.php'],
    ['label' => 'المستخدمون', 'value' => $stats['users'], 'icon' => 'users', 'gradient' => 'gradient-slate', 'url' => APP_URL . '/system/users.php'],
    ['label' => 'الموردون', 'value' => $stats['suppliers'], 'icon' => 'truck', 'gradient' => 'gradient-rose', 'url' => APP_URL . '/inputs/suppliers.php'],
    ['label' => 'العملاء', 'value' => $stats['customers'], 'icon' => 'users', 'gradient' => 'gradient-teal', 'url' => APP_URL . '/inputs/customers.php'],
    ['label' => 'الأصناف', 'value' => $stats['items'], 'icon' => 'package', 'gradient' => 'gradient-blue', 'url' => APP_URL . '/inputs/items.php'],
    ['label' => 'الموظفون', 'value' => $stats['employees'], 'icon' => 'users', 'gradient' => 'gradient-indigo', 'url' => APP_URL . '/inputs/employees.php'],
    ['label' => 'الصناديق', 'value' => $stats['cash_boxes'], 'icon' => 'wallet', 'gradient' => 'gradient-emerald', 'url' => APP_URL . '/inputs/cash-boxes.php'],
    ['label' => 'البنوك', 'value' => $stats['banks'], 'icon' => 'landmark', 'gradient' => 'gradient-blue', 'url' => APP_URL . '/inputs/banks.php'],
    ['label' => 'السندات', 'value' => $stats['vouchers'], 'icon' => 'receipt', 'gradient' => 'gradient-purple', 'url' => APP_URL . '/operations/payment-vouchers.php'],
    ['label' => 'الفواتير', 'value' => $stats['invoices'], 'icon' => 'file-text', 'gradient' => 'gradient-rose', 'url' => APP_URL . '/operations/purchase-invoices.php'],
];

// أيقونات SVG للبطاقات
$cardIcons = [
    'book' => '<svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>',
    'branch' => '<svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M3 6h6l2 2h10v10H3V6z" opacity="0.3"/><circle cx="6" cy="6" r="3"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="12" r="3"/></svg>',
    'coins' => '<svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" opacity="0.3"/><path d="M12 7v10M9 9h4a2 2 0 010 4H10a2 2 0 000 4h4"/></svg>',
    'globe' => '<svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" opacity="0.3"/><path d="M3 12h18M12 3a14 14 0 010 18M12 3a14 14 0 000 18"/></svg>',
    'building' => '<svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M3 21h18M5 7l7-3 7 3M4 10v11m4-11v11m4-11v11m4-11v11m4-11v11"/></svg>',
    'users' => '<svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><circle cx="9" cy="8" r="4" opacity="0.6"/><path d="M3 21v-1a6 6 0 016-6 6 6 0 016 6v1M17 11a4 4 0 000-8M21 21v-1a6 6 0 00-4-5.5"/></svg>',
    'truck' => '<svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M3 6h11v9H3V6zm11 4h4l3 3v2h-7v-5z" opacity="0.6"/><circle cx="7" cy="17" r="2"/><circle cx="17" cy="17" r="2"/></svg>',
    'package' => '<svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l9 5v10l-9 5-9-5V7l9-5z" opacity="0.6"/><path d="M3 7l9 5 9-5M12 12v10"/></svg>',
    'wallet' => '<svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M3 6h16v12H3V6z" opacity="0.6"/><circle cx="16" cy="12" r="1.5" fill="#fff"/></svg>',
    'landmark' => '<svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M3 21h18M3 10h18M5 6l7-3 7 3M4 10v11m4-11v11m4-11v11m4-11v11m4-11v11"/></svg>',
    'receipt' => '<svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M5 3v18l3-2 3 2 3-2 3 2V3l-3 2-3-2-3 2-3-2z" opacity="0.6"/><path d="M8 9h8M8 13h8"/></svg>',
    'file-text' => '<svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M9 3h7l5 5v13H9V3z" opacity="0.6"/><path d="M9 9h8M9 13h8M9 17h4"/></svg>',
];
?>

<!-- بانر الترحيب -->
<div class="welcome-banner">
    <div style="display: flex; align-items: center; justify-content: space-between; gap: 16px;">
        <div>
            <h1>مرحبًا، <?= sanitize($user['full_name']) ?> 👋</h1>
            <p>نظام أونكس ERP — تخطيط موارد المؤسسة، الإصدار الثامن</p>
            <p style="font-size: 12px; color: rgba(255,255,255,0.7);">منصة محاسبية متكاملة — PHP + MySQL + Bootstrap + Tailwind</p>
        </div>
        <div style="display: none; align-items: center; gap: 12px;">
            <div style="background: rgba(255,255,255,0.1); backdrop-filter: blur(4px); border-radius: 12px; padding: 12px; text-align: center;">
                <div style="font-size: 28px;">📊</div>
                <p style="font-size: 12px; margin: 0;">المرحلة 4</p>
                <p style="font-weight: 700; margin: 0;">إدارة النظام</p>
            </div>
        </div>
    </div>
</div>

<!-- بطاقات الإحصائيات -->
<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
    <h3 style="font-size: 18px; font-weight: 700; color: var(--onyx-text);">إحصائيات النظام</h3>
    <span style="font-size: 12px; color: var(--onyx-text-muted);">انقر على البطاقة للانتقال إلى الشاشة</span>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 12px; margin-bottom: 24px;">
    <?php foreach ($cards as $card): ?>
        <a href="<?= $card['url'] ?>" class="stat-card">
            <div class="stat-icon <?= $card['gradient'] ?>">
                <?= $cardIcons[$card['icon']] ?? '<span>●</span>' ?>
            </div>
            <div class="stat-value"><?= number_format($card['value']) ?></div>
            <div class="stat-label"><?= $card['label'] ?></div>
        </a>
    <?php endforeach; ?>
</div>

<!-- مراحل بناء النظام -->
<div class="card">
    <div class="card-header">مراحل بناء النظام</div>
    <div class="card-body" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px;">
        <?php
        $phases = [
            [1, 'التهيئة', 'تهيئة النظام، العملات، الأقاليم، الشركة، الدليل المحاسبي', 'done'],
            [2, 'المدخلات', 'الدليل المحاسبي، الهيكل الإداري، الموظفون، الصناديق، البنوك، الأصناف', 'done'],
            [3, 'العمليات', 'السندات، القيود اليومية، المخزون، المشتريات، المبيعات', 'done'],
            [4, 'إدارة النظام', 'المستخدمون، الصلاحيات، سجل النشاط، النسخ الاحتياطي', 'done'],
        ];
        foreach ($phases as $phase):
            $colorClass = $phase[3] === 'done' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-50 text-slate-500';
            $dotColor = $phase[3] === 'done' ? 'background:#10b981' : 'background:#cbd5e1';
            $label = $phase[3] === 'done' ? 'مكتمل' : 'قيد التنفيذ';
        ?>
            <div style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; <?= $colorClass ?>">
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                    <span style="width: 24px; height: 24px; border-radius: 50%; background: #fff; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700;"><?= $phase[0] ?></span>
                    <h6 style="font-weight: 700; margin: 0; font-size: 14px;"><?= $phase[1] ?></h6>
                    <span style="width: 8px; height: 8px; border-radius: 50%; margin-right: auto; <?= $dotColor ?>"></span>
                </div>
                <p style="font-size: 12px; margin: 0; line-height: 1.4;"><?= $phase[2] ?></p>
                <span style="display: inline-block; margin-top: 4px; padding: 2px 8px; border-radius: 9999px; font-size: 10px; background: #fff; color: #475569;"><?= $label ?></span>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- آخر النشاطات -->
<div class="card">
    <div class="card-header" style="display: flex; align-items: center; justify-content: space-between;">
        <span>آخر النشاطات</span>
        <a href="<?= APP_URL ?>/system/activity-logs.php" style="font-size: 12px; color: var(--onyx-primary); text-decoration: none;">عرض الكل</a>
    </div>
    <div class="card-body" style="padding: 0;">
        <?php if (empty($logs)): ?>
            <p style="text-align: center; color: var(--onyx-text-light); padding: 24px; margin: 0;">لا توجد نشاطات حديثة</p>
        <?php else: ?>
            <ul style="list-style: none; padding: 0; margin: 0;">
                <?php foreach ($logs as $log): ?>
                    <li style="display: flex; align-items: center; gap: 12px; padding: 10px 16px; border-bottom: 1px solid #f1f5f9;">
                        <div style="width: 32px; height: 32px; border-radius: 50%; background: #eef2ff; color: #4f46e5; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; flex-shrink: 0;">
                            <?= mb_substr($log['full_name'] ?? '؟', 0, 1) ?>
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <p style="margin: 0; font-size: 13px; font-weight: 500; color: #334155;">
                                <?= sanitize($log['full_name'] ?? 'مستخدم') ?>
                                <span style="color: #64748b; font-weight: 400;"> — <?= sanitize($log['details'] ?? $log['action']) ?></span>
                            </p>
                            <p style="margin: 0; font-size: 11px; color: #94a3b8;">
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
