<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/crud-helper.php';
render_crud_form([
    'table' => 'warehouse_groups', 'listUrl' => 'setup/warehouse-groups.php',
    'addTitle' => 'إضافة مجموعة مخازن', 'editTitle' => 'تعديل مجموعة مخازن',
    'fields' => [
        ['name' => 'code', 'label' => 'رمز المجموعة', 'type' => 'text', 'required' => true, 'dir' => 'ltr', 'readonly' => isset($_GET['id'])],
        ['name' => 'name_ar', 'label' => 'الاسم', 'type' => 'text', 'required' => true],
        ['name' => 'name_en', 'label' => 'الاسم (إنجليزي)', 'type' => 'text', 'dir' => 'ltr'],
        ['name' => 'main_group_id', 'label' => 'المجموعة الرئيسية', 'type' => 'select', 'options' => ['' => '— اختر —'] + array_column(db_fetch_all("SELECT id, CONCAT(code, ' — ', name_ar) as name FROM inventory_main_groups ORDER BY code"), 'name', 'id')],
        ['name' => 'active', 'label' => 'نشطة', 'type' => 'checkbox', 'default' => 1, 'col' => 12],
    ],
]);
require_once __DIR__ . '/../includes/footer.php';
