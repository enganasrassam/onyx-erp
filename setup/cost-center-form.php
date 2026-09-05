<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/crud-helper.php';
render_crud_form([
    'table' => 'cost_centers', 'listUrl' => 'setup/cost-centers.php',
    'addTitle' => 'إضافة مركز تكلفة', 'editTitle' => 'تعديل مركز تكلفة',
    'fields' => [
        ['name' => 'code', 'label' => 'رمز المركز', 'type' => 'text', 'required' => true, 'dir' => 'ltr', 'readonly' => isset($_GET['id'])],
        ['name' => 'name_ar', 'label' => 'الاسم (عربي)', 'type' => 'text', 'required' => true],
        ['name' => 'name_en', 'label' => 'الاسم (إنجليزي)', 'type' => 'text', 'dir' => 'ltr'],
        ['name' => 'parent_id', 'label' => 'المركز الأعلى', 'type' => 'select', 'options' => ['' => '— بدون —'] + array_column(db_fetch_all("SELECT id, CONCAT(code, ' — ', name_ar) as name FROM cost_centers ORDER BY code"), 'name', 'id')],
        ['name' => 'level', 'label' => 'المستوى', 'type' => 'number', 'default' => 1, 'dir' => 'ltr'],
        ['name' => 'active', 'label' => 'نشط', 'type' => 'checkbox', 'default' => 1],
    ],
]);
require_once __DIR__ . '/../includes/footer.php';
