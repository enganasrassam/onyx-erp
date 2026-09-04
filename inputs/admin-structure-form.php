<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/crud-helper.php';
render_crud_form([
    'table' => 'admin_structures', 'listUrl' => 'inputs/admin-structure.php',
    'addTitle' => 'إضافة هيكل إداري', 'editTitle' => 'تعديل هيكل إداري',
    'fields' => [
        ['name' => 'code', 'label' => 'رمز الهيكل', 'type' => 'text', 'required' => true, 'dir' => 'ltr', 'readonly' => isset($_GET['id'])],
        ['name' => 'name_ar', 'label' => 'الاسم', 'type' => 'text', 'required' => true],
        ['name' => 'parent_id', 'label' => 'الهيكل الأعلى', 'type' => 'select', 'options' => ['' => '— بدون —'] + array_column(db_fetch_all("SELECT id, CONCAT(code, ' — ', name_ar) as name FROM admin_structures ORDER BY code"), 'name', 'id')],
        ['name' => 'level', 'label' => 'المستوى', 'type' => 'number', 'dir' => 'ltr', 'default' => 1],
        ['name' => 'notes', 'label' => 'ملاحظات', 'type' => 'text', 'col' => 12],
        ['name' => 'active', 'label' => 'نشط', 'type' => 'checkbox', 'default' => 1, 'col' => 12],
    ],
]);
require_once __DIR__ . '/../includes/footer.php';
