<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/crud-helper.php';
render_crud_page([
    'table' => 'items', 'title' => 'بيانات الأصناف', 'description' => 'إدارة الأصناف المخزنية. كل صنف له مجموعة، وحدة قياس، تسعيرة.',
    'listUrl' => 'inputs/items.php', 'addUrl' => 'item-form.php', 'editUrl' => 'item-form.php',
    'searchFields' => ['code', 'name_ar', 'name_en', 'barcode'], 'orderBy' => 'code ASC',
    'columns' => [
        ['key' => 'code', 'label' => 'الكود', 'code' => true],
        ['key' => 'name_ar', 'label' => 'اسم الصنف'],
        ['key' => 'item_type', 'label' => 'النوع', 'align' => 'text-center', 'render' => fn($i) => '<span class="badge ' . ($i['item_type']==='simple'?'bg-slate-100 text-slate-700':($i['item_type']==='composite'?'bg-purple-50 text-purple-700':'bg-amber-50 text-amber-700')) . '" style="font-size:0.7rem;">' . ($i['item_type']==='simple'?'بسيط':($i['item_type']==='composite'?'مركب':'ملحق')) . '</span>'],
        ['key' => 'active', 'label' => 'الحالة', 'align' => 'text-center', 'render' => fn($i) => $i['active'] ? status_badge('active') : status_badge('inactive')],
    ],
]);
require_once __DIR__ . '/../includes/footer.php';
