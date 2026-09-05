<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/crud-helper.php';
render_crud_page([
    'table' => 'opening_balances',
    'title' => 'الأرصدة الافتتاحية',
    'description' => 'إدخال أرصدة بداية النشاط. رأس المال = الفرق بين الإجمالي المدين والدائن.',
    'listUrl' => 'inputs/opening-balances.php',
    'addUrl' => 'opening-balance-form.php',
    'editUrl' => 'opening-balance-form.php',
    'searchFields' => [],
    'orderBy' => 'id ASC',
    'columns' => [
        ['key' => 'account_id', 'label' => 'الحساب', 'render' => function($i) {
            $acc = db_fetch_one("SELECT code, name_ar FROM accounts WHERE id = ?", [$i['account_id']]);
            return $acc ? '<code dir="ltr" class="bg-slate-100 px-2 py-0.5 rounded font-mono">' . $acc['code'] . '</code> ' . $acc['name_ar'] : '—';
        }],
        ['key' => 'debit_local', 'label' => 'مدين (محلي)', 'align' => 'text-end', 'render' => function($i) { return $i['debit_local'] ? '<code dir="ltr" class="text-emerald-700">' . number_format($i['debit_local'], 2) . '</code>' : '—'; }],
        ['key' => 'credit_local', 'label' => 'دائن (محلي)', 'align' => 'text-end', 'render' => function($i) { return $i['credit_local'] ? '<code dir="ltr" class="text-rose-700">' . number_format($i['credit_local'], 2) . '</code>' : '—'; }],
    ],
]);
require_once __DIR__ . '/../includes/footer.php';
