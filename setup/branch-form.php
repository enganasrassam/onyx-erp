<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/crud-helper.php';
render_crud_form([
    'table' => 'branches', 'listUrl' => 'setup/branches.php',
    'addTitle' => 'إضافة فرع', 'editTitle' => 'تعديل فرع',
    'fields' => [
        ['name' => 'company_id', 'label' => 'الشركة', 'type' => 'select', 'required' => true, 'options' => array_column(db_fetch_all("SELECT id, name_ar FROM companies ORDER BY name_ar"), 'name_ar', 'id')],
        ['name' => 'code', 'label' => 'رمز الفرع', 'type' => 'text', 'required' => true, 'dir' => 'ltr'],
        ['name' => 'name_ar', 'label' => 'اسم الفرع (عربي)', 'type' => 'text', 'required' => true],
        ['name' => 'name_en', 'label' => 'اسم الفرع (إنجليزي)', 'type' => 'text', 'dir' => 'ltr'],
        ['name' => 'phone', 'label' => 'الهاتف', 'type' => 'text', 'dir' => 'ltr'],
        ['name' => 'manager', 'label' => 'المدير', 'type' => 'text'],
        ['name' => 'address', 'label' => 'العنوان', 'type' => 'text', 'col' => 12],
        ['name' => 'active', 'label' => 'نشط', 'type' => 'checkbox', 'default' => 1, 'col' => 12],
    ],
]);
require_once __DIR__ . '/../includes/footer.php';
