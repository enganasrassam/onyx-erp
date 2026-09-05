<?php
/**
 * Header — بداية الصفحة + الشريط الجانبي + الشريط العلوي
 * مطابق لتصميم نظام أونكس الأصلي
 */
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/sidebar.php';
require_login();

$user = current_user();
$pageTitle = $pageTitle ?? 'نظام أونكس ERP';
$activeMenu = $activeMenu ?? '';
$today = date('l, d F Y');
$days = ['Sunday'=>'الأحد','Monday'=>'الإثنين','Tuesday'=>'الثلاثاء','Wednesday'=>'الأربعاء','Thursday'=>'الخميس','Friday'=>'الجمعة','Saturday'=>'السبت'];
$months = [1=>'يناير',2=>'فبراير',3=>'مارس',4=>'أبريل',5=>'مايو',6=>'يونيو',7=>'يوليو',8=>'أغسطس',9=>'سبتمبر',10=>'أكتوبر',11=>'نوفمبر',12=>'ديسمبر'];
$todayAr = $days[date('l')] . '، ' . date('d') . ' ' . $months[(int)date('n')] . ' ' . date('Y');
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= sanitize($pageTitle) ?> — نظام أونكس ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;700;800&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="<?= APP_URL ?>/assets/css/style.css" rel="stylesheet">
    <script>
        tailwind.config = { theme: { extend: {} } }
    </script>
</head>
<body class="bg-slate-50" style="font-family: 'Cairo', sans-serif;">

<div class="flex min-h-screen">
    <!-- الشريط الجانبي -->
    <aside class="onyx-sidebar w-72 shrink-0 flex flex-col h-screen sticky top-0">
        <!-- شعار النظام -->
        <div class="flex items-center gap-3 p-4 border-b border-sidebar-border">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gradient-to-br from-indigo-400 to-indigo-600 shadow-lg">
                <span class="text-white text-lg font-extrabold">أ</span>
            </div>
            <div>
                <h1 class="font-extrabold text-base text-white">نظام أونكس</h1>
                <p class="text-[11px] text-indigo-200">ERP — الإصدار 8</p>
            </div>
        </div>

        <!-- بحث -->
        <div class="p-3 border-b border-sidebar-border">
            <input type="text" placeholder="بحث عن شاشة..." class="sidebar-search" oninput="filterMenu(this.value)">
        </div>

        <!-- شجرة الشاشات -->
        <nav class="flex-1 overflow-y-auto py-2" id="sidebar-nav">
            <?php echo render_sidebar_menu($activeMenu); ?>
        </nav>

        <!-- المستخدم الحالي -->
        <div class="border-t border-sidebar-border p-3">
            <div class="flex items-center gap-2">
                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-sidebar-accent text-white">
                    <span class="text-sm font-bold"><?= mb_substr($user['full_name'], 0, 1) ?></span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-white truncate"><?= sanitize($user['full_name']) ?></p>
                    <p class="text-[11px] text-indigo-200 truncate" dir="ltr">@<?= sanitize($user['username']) ?> · <?= sanitize($user['role']) ?></p>
                </div>
                <a href="<?= APP_URL ?>/logout.php" class="p-1.5 text-sidebar-foreground/70 hover:text-white" title="خروج">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M10 3.5a.5.5 0 0 0-.5-.5h-4a.5.5 0 0 0-.5.5v9a.5.5 0 0 0 .5.5h4a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 1 1 0v1a1.5 1.5 0 0 1-1.5 1.5h-4A1.5 1.5 0 0 1 4 12.5v-9A1.5 1.5 0 0 1 5.5 2h4A1.5 1.5 0 0 1 11 3.5v1a.5.5 0 0 1-1 0v-1z"/><path d="M15.854 8.354a.5.5 0 0 0 0-.708l-2-2a.5.5 0 0 0-.708.708L14.293 7.5H6.5a.5.5 0 0 0 0 1h7.793l-1.147 1.146a.5.5 0 0 0 .708.708l2-2z"/></svg>
                </a>
            </div>
        </div>
    </aside>

    <!-- المحتوى الرئيسي -->
    <div class="flex-1 flex flex-col min-w-0">
        <!-- الشريط العلوي -->
        <header class="h-16 bg-white border-b border-slate-200 sticky top-0 z-20 flex items-center px-6 gap-4">
            <div class="flex-1 min-w-0">
                <h2 class="text-lg font-bold text-slate-800 truncate"><?= sanitize($pageTitle) ?></h2>
                <p class="text-xs text-slate-500 truncate">نظام أونكس ERP — تخطيط موارد المؤسسة</p>
            </div>
            <div class="hidden md:flex relative">
                <input type="text" placeholder="بحث سريع..." class="bg-slate-50 border border-slate-200 rounded-md pr-9 pl-3 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 w-56">
            </div>
            <div class="hidden lg:flex items-center gap-2 text-xs text-slate-600 bg-slate-50 px-3 py-1.5 rounded-md">
                <span><?= $todayAr ?></span>
            </div>
        </header>

        <!-- المحتوى -->
        <main class="flex-1 p-6 overflow-x-auto">
            <?php
            $flashes = get_flashes();
            foreach ($flashes as $flash): ?>
                <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?> alert-dismissible fade show" role="alert">
                    <?= sanitize($flash['message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endforeach; ?>
