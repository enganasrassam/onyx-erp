<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/crud-helper.php';
render_crud_form([
    'table' => 'companies', 'listUrl' => 'setup/company.php',
    'addTitle' => 'إضافة شركة', 'editTitle' => 'تعديل شركة',
    'fields' => [
        ['name' => 'name_ar', 'label' => 'اسم الشركة (عربي)', 'type' => 'text', 'required' => true, 'col' => 6],
        ['name' => 'name_en', 'label' => 'اسم الشركة (إنجليزي)', 'type' => 'text', 'dir' => 'ltr', 'col' => 6],
        ['name' => 'tax_number', 'label' => 'الرقم الضريبي', 'type' => 'text', 'dir' => 'ltr', 'col' => 6],
        ['name' => 'commercial_reg', 'label' => 'السجل التجاري', 'type' => 'text', 'dir' => 'ltr', 'col' => 6],
        ['name' => 'phone', 'label' => 'الهاتف', 'type' => 'text', 'dir' => 'ltr', 'col' => 6],
        ['name' => 'email', 'label' => 'البريد الإلكتروني', 'type' => 'text', 'dir' => 'ltr', 'col' => 6],
        ['name' => 'website', 'label' => 'الموقع الإلكتروني', 'type' => 'text', 'dir' => 'ltr', 'col' => 6],
        ['name' => 'fiscal_year_start_month', 'label' => 'شهر بداية السنة المالية', 'type' => 'number', 'dir' => 'ltr', 'default' => 1, 'col' => 6],
        ['name' => 'address', 'label' => 'العنوان', 'type' => 'text', 'col' => 12],
        ['name' => 'active', 'label' => 'نشطة', 'type' => 'checkbox', 'default' => 1, 'col' => 12],
    ],
]);
require_once __DIR__ . '/../includes/footer.php';
