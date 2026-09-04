<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/crud-helper.php';
render_crud_page([
    'table' => 'countries', 'title' => 'بيانات الدول', 'description' => 'إدارة الدول المتاحة في النظام. تُستخدم في عناوين العملاء والموردين والشركات.',
    'listUrl' => 'setup/countries.php', 'addUrl' => 'country-form.php', 'editUrl' => 'country-form.php',
    'searchFields' => ['code', 'name_ar', 'name_en'], 'orderBy' => 'name_ar ASC',
    'columns' => [
        ['key' => 'code', 'label' => 'الرمز', 'code' => true],
        ['key' => 'name_ar', 'label' => 'الاسم (عربي)'],
        ['key' => 'name_en', 'label' => 'الاسم (إنجليزي)', 'align' => 'text-end', 'render' => fn($i) => sanitize($i['name_en'] ?? '—')],
        ['key' => 'active', 'label' => 'الحالة', 'align' => 'text-center', 'render' => fn($i) => $i['active'] ? status_badge('active') : status_badge('inactive')],
    ],
]);
require_once __DIR__ . '/../includes/footer.php';
