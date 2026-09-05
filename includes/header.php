<?php
/**
 * Header — مطابق لنظام أونكس الأصلي
 * يحتوي على: شريط جانبي + شريط علوي + شريط أدوات
 */
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/sidebar.php';
require_login();

$user = current_user();
$pageTitle = $pageTitle ?? 'نظام أونكس ERP';
$activeMenu = $activeMenu ?? '';

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
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link href="<?= APP_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>

<div class="onyx-layout">
    <!-- ===== الشريط الجانبي ===== -->
    <aside class="onyx-sidebar">
        <!-- شعار النظام -->
        <div class="sidebar-brand">
            <div class="sidebar-brand-logo"><span>أ</span></div>
            <div>
                <h1>نظام أونكس</h1>
                <p>ERP — الإصدار 8</p>
            </div>
        </div>

        <!-- بحث -->
        <div class="sidebar-search-wrap">
            <input type="text" class="sidebar-search" placeholder="بحث عن شاشة..." oninput="filterMenu(this.value)">
        </div>

        <!-- شجرة الشاشات -->
        <nav id="sidebar-nav" style="padding: 4px 0; flex: 1; overflow-y: auto;">
            <?php echo render_sidebar_menu($activeMenu); ?>
        </nav>

        <!-- المستخدم الحالي -->
        <div class="sidebar-user">
            <div class="sidebar-user-avatar"><span><?= mb_substr($user['full_name'], 0, 1) ?></span></div>
            <div class="sidebar-user-info">
                <p class="sidebar-user-name"><?= sanitize($user['full_name']) ?></p>
                <p class="sidebar-user-role" dir="ltr">@<?= sanitize($user['username']) ?> · <?= sanitize($user['role']) ?></p>
            </div>
            <a href="<?= APP_URL ?>/logout.php" class="sidebar-logout" title="خروج">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
            </a>
        </div>
    </aside>

    <!-- ===== المحتوى الرئيسي ===== -->
    <div class="onyx-main">
        <!-- الشريط العلوي -->
        <header class="onyx-header">
            <div class="onyx-header-title">
                <h2><?= sanitize($pageTitle) ?></h2>
                <p>نظام أونكس ERP — تخطيط موارد المؤسسة</p>
            </div>
            <div class="onyx-header-info">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span><?= $todayAr ?></span>
            </div>
        </header>

        <!-- المحتوى -->
        <main class="onyx-content">
            <?php
            $flashes = get_flashes();
            foreach ($flashes as $flash): ?>
                <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?> alert-dismissible fade show" role="alert">
                    <?= sanitize($flash['message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endforeach; ?>
