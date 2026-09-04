<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/crud-helper.php';
render_crud_page([
    'table' => 'banks', 'title' => 'شاشة البنوك', 'description' => 'إدارة البنوك. تُستخدم في سندات الصرف والقبض بشيك.',
    'listUrl' => 'inputs/banks.php', 'addUrl' => 'bank-form.php', 'editUrl' => 'bank-form.php',
    'searchFields' => ['code', 'name_ar'], 'orderBy' => 'code ASC',
    'columns' => [
        ['key' => 'code', 'label' => 'الرمز', 'code' => true],
        ['key' => 'name_ar', 'label' => 'اسم البنك'],
        ['key' => 'receipt_sequence', 'label' => 'نوع التسلسل', 'align' => 'text-center', 'render' => fn($i) => sanitize($i['receipt_sequence'] ?? '—')],
        ['key' => 'active', 'label' => 'الحالة', 'align' => 'text-center', 'render' => fn($i) => $i['active'] ? status_badge('active') : status_badge('inactive')],
    ],
]);
require_once __DIR__ . '/../includes/footer.php';
