<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/crud-helper.php';
render_crud_page([
    'table' => 'suppliers', 'title' => 'بيانات الموردين', 'description' => 'إدارة بيانات الموردين. تُستخدم في فواتير المشتريات.',
    'listUrl' => 'inputs/suppliers.php', 'addUrl' => 'supplier-form.php', 'editUrl' => 'supplier-form.php',
    'searchFields' => ['code', 'name_ar'], 'orderBy' => 'code ASC',
    'columns' => [
        ['key' => 'code', 'label' => 'الرمز', 'code' => true],
        ['key' => 'name_ar', 'label' => 'اسم المورد'],
        ['key' => 'contact_person', 'label' => 'شخص التواصل', 'align' => 'text-end', 'render' => fn($i) => sanitize($i['contact_person'] ?? '—')],
        ['key' => 'phone', 'label' => 'الهاتف', 'align' => 'text-end', 'render' => fn($i) => sanitize($i['phone'] ?? '—')],
        ['key' => 'opening_balance', 'label' => 'رصيد افتتاحي', 'align' => 'text-end', 'render' => fn($i) => '<code dir="ltr">' . number_format($i['opening_balance'], 2) . '</code>'],
        ['key' => 'active', 'label' => 'الحالة', 'align' => 'text-center', 'render' => fn($i) => $i['active'] ? status_badge('active') : status_badge('inactive')],
    ],
]);
require_once __DIR__ . '/../includes/footer.php';
