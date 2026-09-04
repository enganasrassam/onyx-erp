<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/crud-helper.php';
render_crud_page([
    'table' => 'governorates', 'title' => 'بيانات المحافظات', 'description' => 'المحافظات ضمن كل إقليم.',
    'listUrl' => 'setup/governorates.php', 'addUrl' => 'governorate-form.php', 'editUrl' => 'governorate-form.php',
    'searchFields' => ['code', 'name_ar'], 'orderBy' => 'name_ar ASC',
    'columns' => [
        ['key' => 'code', 'label' => 'الرمز', 'code' => true],
        ['key' => 'name_ar', 'label' => 'الاسم'],
        ['key' => 'active', 'label' => 'الحالة', 'align' => 'text-center', 'render' => fn($i) => $i['active'] ? status_badge('active') : status_badge('inactive')],
    ],
]);
require_once __DIR__ . '/../includes/footer.php';
