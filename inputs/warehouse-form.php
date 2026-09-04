<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/crud-helper.php';
render_crud_form([
    'table' => 'warehouses', 'listUrl' => 'inputs/warehouses.php',
    'addTitle' => 'إضافة مخزن', 'editTitle' => 'تعديل مخزن',
    'fields' => [
        ['name' => 'code', 'label' => 'رمز المخزن', 'type' => 'text', 'required' => true, 'dir' => 'ltr', 'readonly' => isset($_GET['id'])],
        ['name' => 'name_ar', 'label' => 'اسم المخزن', 'type' => 'text', 'required' => true],
        ['name' => 'name_en', 'label' => 'الاسم (إنجليزي)', 'type' => 'text', 'dir' => 'ltr'],
        ['name' => 'group_id', 'label' => 'مجموعة المخازن', 'type' => 'select', 'options' => ['' => '— اختر —'] + array_column(db_fetch_all("SELECT id, CONCAT(code, ' — ', name_ar) as name FROM warehouse_groups ORDER BY code"), 'name', 'id')],
        ['name' => 'branch_id', 'label' => 'الفرع', 'type' => 'select', 'options' => ['' => '— اختر —'] + array_column(db_fetch_all("SELECT id, name_ar FROM branches ORDER BY name_ar"), 'name_ar', 'id')],
        ['name' => 'active', 'label' => 'نشط', 'type' => 'checkbox', 'default' => 1, 'col' => 12],
    ],
]);
require_once __DIR__ . '/../includes/footer.php';
