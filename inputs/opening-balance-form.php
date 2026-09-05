<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/crud-helper.php';
render_crud_form([
    'table' => 'opening_balances',
    'listUrl' => 'inputs/opening-balances.php',
    'addTitle' => 'إضافة رصيد افتتاحي',
    'editTitle' => 'تعديل رصيد افتتاحي',
    'fields' => [
        ['name' => 'account_id', 'label' => 'الحساب', 'type' => 'select', 'required' => true, 'options' => array_column(db_fetch_all("SELECT id, CONCAT(code, ' — ', name_ar) as name FROM accounts WHERE is_detail=1 AND active=1 ORDER BY code"), 'name', 'id')],
        ['name' => 'currency_id', 'label' => 'العملة', 'type' => 'select', 'required' => true, 'options' => array_column(db_fetch_all("SELECT id, CONCAT(code, ' — ', name_ar) as name FROM currencies WHERE active=1 ORDER BY code"), 'name', 'id')],
        ['name' => 'debit_local', 'label' => 'مدين (محلي)', 'type' => 'number', 'dir' => 'ltr', 'default' => 0],
        ['name' => 'credit_local', 'label' => 'دائن (محلي)', 'type' => 'number', 'dir' => 'ltr', 'default' => 0],
        ['name' => 'debit_foreign', 'label' => 'مدين (أجنبي)', 'type' => 'number', 'dir' => 'ltr', 'default' => 0],
        ['name' => 'credit_foreign', 'label' => 'دائن (أجنبي)', 'type' => 'number', 'dir' => 'ltr', 'default' => 0],
        ['name' => 'notes', 'label' => 'ملاحظات', 'type' => 'text', 'col' => 12],
    ],
]);
require_once __DIR__ . '/../includes/footer.php';
