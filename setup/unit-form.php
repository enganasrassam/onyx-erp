<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/crud-helper.php';
render_crud_form([
    'table' => 'units', 'listUrl' => 'setup/units.php',
    'addTitle' => 'إضافة وحدة قياس', 'editTitle' => 'تعديل وحدة قياس',
    'fields' => [
        ['name' => 'name_ar', 'label' => 'الاسم (عربي)', 'type' => 'text', 'required' => true, 'readonly' => isset($_GET['id'])],
        ['name' => 'name_en', 'label' => 'الاسم (إنجليزي)', 'type' => 'text', 'dir' => 'ltr'],
        ['name' => 'factor', 'label' => 'معامل التحويل', 'type' => 'number', 'dir' => 'ltr', 'default' => 1],
        ['name' => 'base_unit', 'label' => 'وحدة أساسية', 'type' => 'checkbox', 'default' => 0],
        ['name' => 'active', 'label' => 'نشطة', 'type' => 'checkbox', 'default' => 1, 'col' => 12],
    ],
]);
require_once __DIR__ . '/../includes/footer.php';
