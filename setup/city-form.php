<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/crud-helper.php';
render_crud_form([
    'table' => 'cities', 'listUrl' => 'setup/cities.php',
    'addTitle' => 'إضافة مدينة', 'editTitle' => 'تعديل مدينة',
    'fields' => [
        ['name' => 'governorate_id', 'label' => 'المحافظة', 'type' => 'select', 'required' => true, 'options' => array_column(db_fetch_all("SELECT id, CONCAT(code, ' — ', name_ar) as name FROM governorates ORDER BY name_ar"), 'name', 'id')],
        ['name' => 'code', 'label' => 'رمز المدينة', 'type' => 'text', 'required' => true, 'dir' => 'ltr'],
        ['name' => 'name_ar', 'label' => 'الاسم', 'type' => 'text', 'required' => true],
        ['name' => 'active', 'label' => 'نشطة', 'type' => 'checkbox', 'default' => 1, 'col' => 12],
    ],
]);
require_once __DIR__ . '/../includes/footer.php';
