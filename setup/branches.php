<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/crud-helper.php';
render_crud_page([
    'table' => 'branches', 'title' => 'بيانات الفروع', 'description' => 'فروع الشركة. كل فرع قد له صلاحيات ومخازن مستقلة.',
    'listUrl' => 'setup/branches.php', 'addUrl' => 'branch-form.php', 'editUrl' => 'branch-form.php',
    'searchFields' => ['code', 'name_ar'], 'orderBy' => 'name_ar ASC',
    'columns' => [
        ['key' => 'code', 'label' => 'الرمز', 'code' => true],
        ['key' => 'name_ar', 'label' => 'اسم الفرع'],
        ['key' => 'phone', 'label' => 'الهاتف', 'align' => 'text-end', 'render' => fn($i) => sanitize($i['phone'] ?? '—')],
        ['key' => 'manager', 'label' => 'المدير', 'align' => 'text-end', 'render' => fn($i) => sanitize($i['manager'] ?? '—')],
        ['key' => 'active', 'label' => 'الحالة', 'align' => 'text-center', 'render' => fn($i) => $i['active'] ? status_badge('active') : status_badge('inactive')],
    ],
]);
require_once __DIR__ . '/../includes/footer.php';
