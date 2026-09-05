<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/crud-helper.php';
render_crud_page([
    'table' => 'customers', 'title' => 'بيانات العملاء', 'description' => 'إدارة بيانات العملاء. تُستخدم في فواتير المبيعات.',
    'listUrl' => 'inputs/customers.php', 'addUrl' => 'customer-form.php', 'editUrl' => 'customer-form.php',
    'searchFields' => ['code', 'name_ar'], 'orderBy' => 'code ASC',
    'columns' => [
        ['key' => 'code', 'label' => 'الرمز', 'code' => true],
        ['key' => 'name_ar', 'label' => 'اسم العميل'],
        ['key' => 'contact_person', 'label' => 'شخص التواصل', 'align' => 'text-end', 'render' => fn($i) => sanitize($i['contact_person'] ?? '—')],
        ['key' => 'phone', 'label' => 'الهاتف', 'align' => 'text-end', 'render' => fn($i) => sanitize($i['phone'] ?? '—')],
        ['key' => 'credit_limit', 'label' => 'حد الائتمان', 'align' => 'text-end', 'render' => fn($i) => '<code dir="ltr">' . number_format($i['credit_limit'], 2) . '</code>'],
        ['key' => 'active', 'label' => 'الحالة', 'align' => 'text-center', 'render' => fn($i) => $i['active'] ? status_badge('active') : status_badge('inactive')],
    ],
]);
require_once __DIR__ . '/../includes/footer.php';
