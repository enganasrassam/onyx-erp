<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/crud-helper.php';
render_crud_form([
    'table' => 'price_levels', 'listUrl' => 'setup/price-levels.php',
    'addTitle' => 'إضافة مستوى تسعيرة', 'editTitle' => 'تعديل مستوى تسعيرة',
    'fields' => [
        ['name' => 'level', 'label' => 'المستوى', 'type' => 'number', 'required' => true, 'dir' => 'ltr', 'default' => 1],
        ['name' => 'name_ar', 'label' => 'الاسم', 'type' => 'text', 'required' => true],
        ['name' => 'name_en', 'label' => 'الاسم (إنجليزي)', 'type' => 'text', 'dir' => 'ltr'],
        ['name' => 'default_discount', 'label' => 'الخصم الافتراضي %', 'type' => 'number', 'dir' => 'ltr', 'default' => 0],
        ['name' => 'active', 'label' => 'نشط', 'type' => 'checkbox', 'default' => 1, 'col' => 12],
    ],
]);
require_once __DIR__ . '/../includes/footer.php';
