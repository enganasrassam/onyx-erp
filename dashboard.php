<?php
$pageTitle = 'لوحة التحكم';
$activeMenu = 'dashboard';
require_once __DIR__ . '/includes/header.php';

$stats = [
    'accounts' => db_fetch_one("SELECT COUNT(*) as c FROM accounts")['c'] ?? 0,
    'cost_centers' => db_fetch_one("SELECT COUNT(*) as c FROM cost_centers")['c'] ?? 0,
    'currencies' => db_fetch_one("SELECT COUNT(*) as c FROM currencies")['c'] ?? 0,
    'countries' => db_fetch_one("SELECT COUNT(*) as c FROM countries")['c'] ?? 0,
    'branches' => db_fetch_one("SELECT COUNT(*) as c FROM branches")['c'] ?? 0,
    'users' => db_fetch_one("SELECT COUNT(*) as c FROM users")['c'] ?? 0,
    'suppliers' => db_fetch_one("SELECT COUNT(*) as c FROM suppliers")['c'] ?? 0,
    'customers' => db_fetch_one("SELECT COUNT(*) as c FROM customers")['c'] ?? 0,
    'items' => db_fetch_one("SELECT COUNT(*) as c FROM items")['c'] ?? 0,
    'employees' => db_fetch_one("SELECT COUNT(*) as c FROM employees")['c'] ?? 0,
    'cash_boxes' => db_fetch_one("SELECT COUNT(*) as c FROM cash_boxes")['c'] ?? 0,
    'banks' => db_fetch_one("SELECT COUNT(*) as c FROM banks")['c'] ?? 0,
    'vouchers' => db_fetch_one("SELECT COUNT(*) as c FROM vouchers")['c'] ?? 0,
    'invoices' => db_fetch_one("SELECT COUNT(*) as c FROM invoices")['c'] ?? 0,
];
$logs = db_fetch_all("SELECT al.*, u.full_name FROM activity_logs al LEFT JOIN users u ON al.user_id = u.id ORDER BY al.created_at DESC LIMIT 5");
$cards = [
    ['label'=>'الحسابات','value'=>$stats['accounts'],'icon'=>'book','gradient'=>'gradient-indigo','url'=>APP_URL.'/setup/chart-of-accounts.php'],
    ['label'=>'مراكز التكلفة','value'=>$stats['cost_centers'],'icon'=>'branch','gradient'=>'gradient-purple','url'=>APP_URL.'/setup/cost-centers.php'],
    ['label'=>'العملات','value'=>$stats['currencies'],'icon'=>'coins','gradient'=>'gradient-amber','url'=>APP_URL.'/setup/currencies.php'],
    ['label'=>'الدول','value'=>$stats['countries'],'icon'=>'globe','gradient'=>'gradient-emerald','url'=>APP_URL.'/setup/countries.php'],
    ['label'=>'الفروع','value'=>$stats['branches'],'icon'=>'building','gradient'=>'gradient-cyan','url'=>APP_URL.'/setup/branches.php'],
    ['label'=>'المستخدمون','value'=>$stats['users'],'icon'=>'users','gradient'=>'gradient-slate','url'=>APP_URL.'/system/users.php'],
    ['label'=>'الموردون','value'=>$stats['suppliers'],'icon'=>'truck','gradient'=>'gradient-rose','url'=>APP_URL.'/inputs/suppliers.php'],
    ['label'=>'العملاء','value'=>$stats['customers'],'icon'=>'users','gradient'=>'gradient-teal','url'=>APP_URL.'/inputs/customers.php'],
    ['label'=>'الأصناف','value'=>$stats['items'],'icon'=>'package','gradient'=>'gradient-blue','url'=>APP_URL.'/inputs/items.php'],
    ['label'=>'الموظفون','value'=>$stats['employees'],'icon'=>'users','gradient'=>'gradient-indigo','url'=>APP_URL.'/inputs/employees.php'],
    ['label'=>'الصناديق','value'=>$stats['cash_boxes'],'icon'=>'wallet','gradient'=>'gradient-emerald','url'=>APP_URL.'/inputs/cash-boxes.php'],
    ['label'=>'البنوك','value'=>$stats['banks'],'icon'=>'landmark','gradient'=>'gradient-blue','url'=>APP_URL.'/inputs/banks.php'],
    ['label'=>'السندات','value'=>$stats['vouchers'],'icon'=>'receipt','gradient'=>'gradient-purple','url'=>APP_URL.'/operations/payment-vouchers.php'],
    ['label'=>'الفواتير','value'=>$stats['invoices'],'icon'=>'file','gradient'=>'gradient-rose','url'=>APP_URL.'/operations/purchase-invoices.php'],
];
$icons = [
    'book'=>'<svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>',
    'branch'=>'<svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><circle cx="6" cy="6" r="3"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="12" r="3"/></svg>',
    'coins'=>'<svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/></svg>',
    'globe'=>'<svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/></svg>',
    'building'=>'<svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M3 21h18M3 10h18M5 6l7-3 7 3M4 10v11m4-11v11m4-11v11m4-11v11"/></svg>',
    'users'=>'<svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><circle cx="9" cy="8" r="4"/><path d="M3 21v-1a6 6 0 016-6 6 6 0 016 6v1"/></svg>',
    'truck'=>'<svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M3 6h11v9H3V6zm11 4h4l3 3v2h-7V10z"/><circle cx="7" cy="17" r="2"/><circle cx="17" cy="17" r="2"/></svg>',
    'package'=>'<svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l9 5v10l-9 5-9-5V7l9-5z"/></svg>',
    'wallet'=>'<svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M3 6h16v12H3V6z"/></svg>',
    'landmark'=>'<svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M3 21h18M3 10h18M5 6l7-3 7 3"/></svg>',
    'receipt'=>'<svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M5 3v18l3-2 3 2 3-2 3 2V3l-3 2-3-2-3 2-3-2z"/></svg>',
    'file'=>'<svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M9 3h7l5 5v13H9V3z"/></svg>',
];
?>
<!-- بانر الترحيب -->
<div class="welcome-banner">
    <h1>مرحبًا، <?= sanitize($user['full_name']) ?> 👋</h1>
    <p>نظام أونكس ERP — تخطيط موارد المؤسسة، الإصدار الثامن</p>
    <p style="font-size:11px;color:rgba(255,255,255,0.6)">منصة محاسبية متكاملة — PHP + MySQL + Bootstrap + Tailwind</p>
</div>

<!-- بطاقات الإحصائيات -->
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
    <h3 style="font-size:16px;font-weight:700;color:var(--onyx-text)">إحصائيات النظام</h3>
    <span style="font-size:11px;color:var(--onyx-text-muted)">انقر على البطاقة للانتقال</span>
</div>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:10px;margin-bottom:16px">
    <?php foreach ($cards as $card): ?>
        <a href="<?= $card['url'] ?>" class="stat-card">
            <div class="stat-icon <?= $card['gradient'] ?>"><?= $icons[$card['icon']] ?? '' ?></div>
            <div class="stat-value"><?= number_format($card['value']) ?></div>
            <div class="stat-label"><?= $card['label'] ?></div>
        </a>
    <?php endforeach; ?>
</div>

<!-- مراحل بناء النظام -->
<div class="card">
    <div class="card-header">مراحل بناء النظام</div>
    <div class="card-body" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:10px">
        <?php
        $phases = [[1,'التهيئة','تهيئة النظام والعملات والدليل المحاسبي','done'],[2,'المدخلات','الموظفون والصناديق والبنوك والأصناف','done'],[3,'العمليات','السندات والقيود والفواتير والمخزون','done'],[4,'إدارة النظام','المستخدمون والصلاحيات وسجل النشاط','done']];
        foreach ($phases as $p):
            $cls = $p[3]==='done' ? 'bg-green-light text-green' : 'bg-slate-100 text-slate-500';
            $dot = $p[3]==='done' ? '#16a34a' : '#cbd5e1';
            $lbl = $p[3]==='done' ? 'مكتمل' : 'قيد التنفيذ';
        ?>
        <div style="border:1px solid var(--onyx-border-light);border-radius:6px;padding:10px;<?= $cls ?>">
            <div style="display:flex;align-items:center;gap:6px;margin-bottom:4px">
                <span style="width:22px;height:22px;border-radius:50%;background:#fff;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700"><?= $p[0] ?></span>
                <h6 style="font-weight:700;margin:0;font-size:13px"><?= $p[1] ?></h6>
                <span style="width:7px;height:7px;border-radius:50%;margin-right:auto;background:<?= $dot ?>"></span>
            </div>
            <p style="font-size:11px;margin:0;line-height:1.3"><?= $p[2] ?></p>
            <span style="display:inline-block;margin-top:3px;padding:1px 6px;border-radius:9999px;font-size:9px;background:#fff;color:#475569"><?= $lbl ?></span>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- آخر النشاطات -->
<div class="card">
    <div class="card-header"><span>آخر النشاطات</span><a href="<?= APP_URL ?>/system/activity-logs.php" style="font-size:11px;color:var(--onyx-primary);text-decoration:none">عرض الكل</a></div>
    <div class="card-body" style="padding:0">
        <?php if (empty($logs)): ?>
            <p style="text-align:center;color:var(--onyx-text-light);padding:20px;margin:0">لا توجد نشاطات حديثة</p>
        <?php else: ?>
            <ul style="list-style:none;padding:0;margin:0">
                <?php foreach ($logs as $log): ?>
                    <li style="display:flex;align-items:center;gap:10px;padding:8px 14px;border-bottom:1px solid #f1f5f9">
                        <div style="width:28px;height:28px;border-radius:50%;background:var(--onyx-blue-light);color:var(--onyx-primary);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;flex-shrink:0"><?= mb_substr($log['full_name']??'؟',0,1) ?></div>
                        <div style="flex:1;min-width:0">
                            <p style="margin:0;font-size:12px;font-weight:500;color:#334155"><?= sanitize($log['full_name']??'مستخدم') ?> <span style="color:#64748b;font-weight:400">— <?= sanitize($log['details']??$log['action']) ?></span></p>
                            <p style="margin:0;font-size:10px;color:#94a3b8"><?= !empty($log['screen'])?'شاشة: '.sanitize($log['screen']).' · ':'' ?><?= date('Y-m-d H:i',strtotime($log['created_at'])) ?></p>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
