<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/crud-helper.php';
render_crud_page([
    'table' => 'price_levels', 'title' => 'مستويات التسعيرة', 'description' => 'مستويات التسعيرة للعملاء مع نسبة خصم افتراضية.',
    'listUrl' => 'setup/price-levels.php', 'addUrl' => 'price-level-form.php', 'editUrl' => 'price-level-form.php',
    'searchFields' => ['name_ar'], 'orderBy' => 'level ASC',
    'columns' => [
        ['key' => 'level', 'label' => 'المستوى', 'align' => 'text-center', 'render' => fn($i) => '<span class="badge bg-indigo-50 text-indigo-700">' . $i['level'] . '</span>'],
        ['key' => 'name_ar', 'label' => 'الاسم'],
        ['key' => 'default_discount', 'label' => 'الخصم الافتراضي %', 'align' => 'text-end', 'render' => fn($i) => '<code dir="ltr">' . number_format($i['default_discount'], 2) . '%</code>'],
        ['key' => 'active', 'label' => 'الحالة', 'align' => 'text-center', 'render' => fn($i) => $i['active'] ? status_badge('active') : status_badge('inactive')],
    ],
]);
require_once __DIR__ . '/../includes/footer.php';
