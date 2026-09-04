<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/crud-helper.php';
render_crud_form([
    'table' => 'employees', 'listUrl' => 'inputs/employees.php',
    'addTitle' => 'إضافة موظف', 'editTitle' => 'تعديل موظف',
    'fields' => [
        ['name' => 'employee_number', 'label' => 'رقم الموظف', 'type' => 'text', 'required' => true, 'dir' => 'ltr', 'readonly' => isset($_GET['id'])],
        ['name' => 'first_name', 'label' => 'الاسم الأول', 'type' => 'text', 'required' => true],
        ['name' => 'last_name', 'label' => 'الاسم الأخير', 'type' => 'text', 'required' => true],
        ['name' => 'job_title', 'label' => 'المسمى الوظيفي', 'type' => 'text'],
        ['name' => 'branch_id', 'label' => 'الفرع', 'type' => 'select', 'options' => ['' => '— اختر —'] + array_column(db_fetch_all("SELECT id, name_ar FROM branches ORDER BY name_ar"), 'name_ar', 'id')],
        ['name' => 'admin_structure_id', 'label' => 'الهيكل الإداري', 'type' => 'select', 'options' => ['' => '— اختر —'] + array_column(db_fetch_all("SELECT id, CONCAT(code, ' — ', name_ar) as name FROM admin_structures ORDER BY code"), 'name', 'id')],
        ['name' => 'currency_id', 'label' => 'العملة', 'type' => 'select', 'options' => array_column(db_fetch_all("SELECT id, CONCAT(code, ' — ', name_ar) as name FROM currencies WHERE active=1 ORDER BY code"), 'name', 'id')],
        ['name' => 'salary', 'label' => 'الراتب', 'type' => 'number', 'dir' => 'ltr', 'default' => 0],
        ['name' => 'hire_date', 'label' => 'تاريخ التعيين', 'type' => 'date', 'dir' => 'ltr'],
        ['name' => 'national_id', 'label' => 'رقم الهوية', 'type' => 'text', 'dir' => 'ltr'],
        ['name' => 'phone', 'label' => 'الهاتف', 'type' => 'text', 'dir' => 'ltr'],
        ['name' => 'email', 'label' => 'البريد الإلكتروني', 'type' => 'text', 'dir' => 'ltr'],
        ['name' => 'address', 'label' => 'العنوان', 'type' => 'text', 'col' => 12],
        ['name' => 'status', 'label' => 'الحالة', 'type' => 'select', 'options' => ['active' => 'نشط', 'suspended' => 'موقوف', 'terminated' => 'منتهي']],
        ['name' => 'notes', 'label' => 'ملاحظات', 'type' => 'text', 'col' => 12],
    ],
]);
require_once __DIR__ . '/../includes/footer.php';
