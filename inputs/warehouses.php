<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/crud-helper.php';
render_crud_page([
    'table' => 'warehouses', 'title' => 'بيانات المخازن', 'description' => 'إدارة المخازن. كل مخزن يرتبط بمجموعة وفرع.',
    'listUrl' => 'inputs/warehouses.php', 'addUrl' => 'warehouse-form.php', 'editUrl' => 'warehouse-form.php',
    'searchFields' => ['code', 'name_ar'], 'orderBy' => 'code ASC',
    'columns' => [
        ['key' => 'code', 'label' => 'الرمز', 'code' => true],
        ['key' => 'name_ar', 'label' => 'اسم المخزن'],
        ['key' => 'active', 'label' => 'الحالة', 'align' => 'text-center', 'render' => fn($i) => $i['active'] ? status_badge('active') : status_badge('inactive')],
    ],
]);
require_once __DIR__ . '/../includes/footer.php';
