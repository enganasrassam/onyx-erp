<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/crud-helper.php';
render_crud_page([
    'table' => 'customer_groups', 'title' => 'مجموعات العملاء', 'description' => 'تجميع العملاء في مجموعات هرمية.',
    'listUrl' => 'setup/customer-groups.php', 'addUrl' => 'customer-group-form.php', 'editUrl' => 'customer-group-form.php',
    'searchFields' => ['code', 'name_ar'], 'orderBy' => 'code ASC',
    'columns' => [
        ['key' => 'code', 'label' => 'الرمز', 'code' => true],
        ['key' => 'name_ar', 'label' => 'الاسم (عربي)'],
        ['key' => 'name_en', 'label' => 'الاسم (إنجليزي)', 'align' => 'text-end', 'render' => fn($i) => sanitize($i['name_en'] ?? '—')],
        ['key' => 'active', 'label' => 'الحالة', 'align' => 'text-center', 'render' => fn($i) => $i['active'] ? status_badge('active') : status_badge('inactive')],
    ],
]);
require_once __DIR__ . '/../includes/footer.php';
