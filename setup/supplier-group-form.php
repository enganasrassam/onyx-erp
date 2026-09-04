<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/crud-helper.php';
render_crud_form([
    'table' => 'supplier_groups', 'listUrl' => 'setup/supplier-groups.php',
    'addTitle' => 'إضافة مجموعة', 'editTitle' => 'تعديل مجموعة',
    'fields' => [
        ['name' => 'code', 'label' => 'الرمز', 'type' => 'text', 'required' => true, 'dir' => 'ltr', 'readonly' => isset($_GET['id'])],
        ['name' => 'name_ar', 'label' => 'الاسم (عربي)', 'type' => 'text', 'required' => true],
        ['name' => 'name_en', 'label' => 'الاسم (إنجليزي)', 'type' => 'text', 'dir' => 'ltr'],
        ['name' => 'parent_id', 'label' => 'المجموعة الأعلى', 'type' => 'select', 'options' => ['' => '— بدون —'] + array_column(db_fetch_all("SELECT id, CONCAT(code, ' — ', name_ar) as name FROM supplier_groups ORDER BY code"), 'name', 'id')],
        ['name' => 'active', 'label' => 'نشطة', 'type' => 'checkbox', 'default' => 1, 'col' => 12],
    ],
]);
require_once __DIR__ . '/../includes/footer.php';
