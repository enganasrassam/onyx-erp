<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/crud-helper.php';
render_crud_page([
    'table' => 'districts', 'title' => 'بيانات المناطق', 'description' => 'المناطق/الأحياء ضمن كل مدينة.',
    'listUrl' => 'setup/districts.php', 'addUrl' => 'district-form.php', 'editUrl' => 'district-form.php',
    'searchFields' => ['code', 'name_ar'], 'orderBy' => 'name_ar ASC',
    'columns' => [
        ['key' => 'code', 'label' => 'الرمز', 'code' => true],
        ['key' => 'name_ar', 'label' => 'الاسم'],
        ['key' => 'active', 'label' => 'الحالة', 'align' => 'text-center', 'render' => function($i) { return $i['active'] ? status_badge('active') : status_badge('inactive'); }],
    ],
]);
require_once __DIR__ . '/../includes/footer.php';
