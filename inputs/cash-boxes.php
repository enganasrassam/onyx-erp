<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/crud-helper.php';
render_crud_page([
    'table' => 'cash_boxes', 'title' => 'شاشة الصناديق', 'description' => 'إدارة صناديق النقدية. تُستخدم في سندات الصرف والقبض نقدًا.',
    'listUrl' => 'inputs/cash-boxes.php', 'addUrl' => 'cash-box-form.php', 'editUrl' => 'cash-box-form.php',
    'searchFields' => ['code', 'name_ar'], 'orderBy' => 'code ASC',
    'columns' => [
        ['key' => 'code', 'label' => 'الرمز', 'code' => true],
        ['key' => 'name_ar', 'label' => 'اسم الصندوق'],
        ['key' => 'operation_type', 'label' => 'نوع العملية', 'align' => 'text-center', 'render' => fn($i) => '<span class="badge ' . ($i['operation_type']==='both'?'bg-indigo-50 text-indigo-700':($i['operation_type']==='receipt'?'bg-emerald-50 text-emerald-700':'bg-rose-50 text-rose-700')) . '" style="font-size:0.7rem;">' . ($i['operation_type']==='both'?'قبض وصرف':($i['operation_type']==='receipt'?'قبض فقط':'صرف فقط')) . '</span>'],
        ['key' => 'active', 'label' => 'الحالة', 'align' => 'text-center', 'render' => fn($i) => $i['active'] ? status_badge('active') : status_badge('inactive')],
    ],
]);
require_once __DIR__ . '/../includes/footer.php';
