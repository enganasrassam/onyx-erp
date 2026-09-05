<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/crud-helper.php';

$typeLabels = [
    'currency_diff' => 'فروق عملة', 'missing_items' => 'أصناف مفقودة',
    'payment_notes' => 'أوراق دفع', 'receipt_notes' => 'أوراق قبض',
    'fraction_diff' => 'فروق كسور', 'cost_diff' => 'فروق تكلفة',
    'commission' => 'عمولة', 'commission_num' => 'عمولة رقم', 'other' => 'أخرى',
];

render_crud_page([
    'table' => 'intermediary_accounts',
    'title' => 'الحسابات الوسيطة',
    'description' => 'ربط الحسابات الوسيطة بالدليل المحاسبي. يستخدمها النظام تلقائيًا في القيود.',
    'listUrl' => 'inputs/intermediary-accounts.php',
    'addUrl' => 'intermediary-account-form.php',
    'editUrl' => 'intermediary-account-form.php',
    'searchFields' => ['code', 'name_ar'],
    'orderBy' => 'code ASC',
    'columns' => [
        ['key' => 'code', 'label' => 'الرمز', 'code' => true],
        ['key' => 'name_ar', 'label' => 'الاسم'],
        ['key' => 'type', 'label' => 'النوع', 'align' => 'text-center', 'render' => function($i) use ($typeLabels) {
            $label = isset($typeLabels[$i['type']]) ? $typeLabels[$i['type']] : $i['type'];
            return '<span class="badge bg-indigo-50 text-indigo-700" style="font-size:0.7rem;">' . $label . '</span>';
        }],
        ['key' => 'active', 'label' => 'الحالة', 'align' => 'text-center', 'render' => function($i) {
            return $i['active'] ? status_badge('active') : status_badge('inactive');
        }],
    ],
]);
require_once __DIR__ . '/../includes/footer.php';
