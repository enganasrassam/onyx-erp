<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/crud-helper.php';
render_crud_page([
    'table' => 'companies', 'title' => 'بيانات الشركة', 'description' => 'الشركة المالكة للنظام. يمكن إدارة عدة شركات وفروع.',
    'listUrl' => 'setup/company.php', 'addUrl' => 'company-form.php', 'editUrl' => 'company-form.php',
    'searchFields' => ['name_ar', 'name_en', 'tax_number'], 'orderBy' => 'name_ar ASC',
    'columns' => [
        ['key' => 'name_ar', 'label' => 'اسم الشركة'],
        ['key' => 'tax_number', 'label' => 'الرقم الضريبي', 'align' => 'text-end', 'render' => fn($i) => '<code dir="ltr">' . sanitize($i['tax_number'] ?? '—') . '</code>'],
        ['key' => 'phone', 'label' => 'الهاتف', 'align' => 'text-end', 'render' => fn($i) => sanitize($i['phone'] ?? '—')],
        ['key' => 'email', 'label' => 'البريد', 'align' => 'text-end', 'render' => fn($i) => sanitize($i['email'] ?? '—')],
        ['key' => 'active', 'label' => 'الحالة', 'align' => 'text-center', 'render' => fn($i) => $i['active'] ? status_badge('active') : status_badge('inactive')],
    ],
]);
require_once __DIR__ . '/../includes/footer.php';
