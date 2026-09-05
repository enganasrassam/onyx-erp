<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/crud-helper.php';
render_crud_form([
    'table' => 'countries', 'listUrl' => 'setup/countries.php',
    'addTitle' => 'إضافة دولة جديدة', 'editTitle' => 'تعديل دولة',
    'fields' => [
        ['name' => 'code', 'label' => 'رمز الدولة', 'type' => 'text', 'required' => true, 'dir' => 'ltr', 'placeholder' => 'YE', 'readonly' => isset($_GET['id'])],
        ['name' => 'name_ar', 'label' => 'الاسم بالعربية', 'type' => 'text', 'required' => true, 'placeholder' => 'اليمن'],
        ['name' => 'name_en', 'label' => 'الاسم بالإنجليزية', 'type' => 'text', 'dir' => 'ltr', 'placeholder' => 'Yemen'],
        ['name' => 'active', 'label' => 'نشطة', 'type' => 'checkbox', 'default' => 1, 'col' => 12],
    ],
]);
require_once __DIR__ . '/../includes/footer.php';
