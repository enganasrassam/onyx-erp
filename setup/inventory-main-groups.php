<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/crud-helper.php';
render_crud_page([
    'table' => 'inventory_main_groups', 'title' => 'بيانات المجموعة الرئيسية', 'description' => 'تقسيم المخزون إلى مجموعات رئيسية للأصناف.',
    'listUrl' => 'setup/inventory-main-groups.php', 'addUrl' => 'inventory-main-group-form.php', 'editUrl' => 'inventory-main-group-form.php',
    'searchFields' => ['code', 'name_ar'], 'orderBy' => 'code ASC',
    'columns' => [
        ['key' => 'code', 'label' => 'الرمز', 'code' => true],
        ['key' => 'name_ar', 'label' => 'الاسم (عربي)'],
        ['key' => 'item_code_prefix', 'label' => 'بادئة كود الصنف', 'align' => 'text-end', 'render' => fn($i) => '<code dir="ltr">' . sanitize($i['item_code_prefix'] ?? '—') . '</code>'],
        ['key' => 'active', 'label' => 'الحالة', 'align' => 'text-center', 'render' => fn($i) => $i['active'] ? status_badge('active') : status_badge('inactive')],
    ],
]);
require_once __DIR__ . '/../includes/footer.php';
