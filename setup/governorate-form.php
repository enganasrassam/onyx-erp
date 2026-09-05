<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/crud-helper.php';
render_crud_form([
    'table' => 'governorates', 'listUrl' => 'setup/governorates.php',
    'addTitle' => 'إضافة محافظة', 'editTitle' => 'تعديل محافظة',
    'fields' => [
        ['name' => 'region_id', 'label' => 'الإقليم', 'type' => 'select', 'required' => true, 'options' => array_column(db_fetch_all("SELECT id, CONCAT(code, ' — ', name_ar) as name FROM regions ORDER BY name_ar"), 'name', 'id')],
        ['name' => 'code', 'label' => 'رمز المحافظة', 'type' => 'text', 'required' => true, 'dir' => 'ltr'],
        ['name' => 'name_ar', 'label' => 'الاسم', 'type' => 'text', 'required' => true],
        ['name' => 'active', 'label' => 'نشطة', 'type' => 'checkbox', 'default' => 1, 'col' => 12],
    ],
]);
require_once __DIR__ . '/../includes/footer.php';
