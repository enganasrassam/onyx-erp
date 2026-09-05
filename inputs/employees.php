<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/crud-helper.php';
render_crud_page([
    'table' => 'employees', 'title' => 'بيانات الموظفين', 'description' => 'إدارة بيانات الموظفين الشخصية والوظيفية والمالية.',
    'listUrl' => 'inputs/employees.php', 'addUrl' => 'employee-form.php', 'editUrl' => 'employee-form.php',
    'searchFields' => ['employee_number', 'first_name', 'last_name', 'job_title'], 'orderBy' => 'employee_number ASC',
    'columns' => [
        ['key' => 'employee_number', 'label' => 'رقم الموظف', 'code' => true],
        ['key' => 'full_name', 'label' => 'الاسم', 'render' => fn($i) => '<div class="d-flex align-items-center gap-2"><div class="rounded-circle bg-indigo-50 text-indigo-600 d-flex align-items-center justify-content-center" style="width:32px;height:32px;font-size:12px;font-weight:700;">' . mb_substr($i['first_name'], 0, 1) . '</div><span>' . sanitize($i['first_name'] . ' ' . $i['last_name']) . '</span></div>'],
        ['key' => 'job_title', 'label' => 'المسمى الوظيفي', 'render' => fn($i) => sanitize($i['job_title'] ?? '—')],
        ['key' => 'phone', 'label' => 'الهاتف', 'align' => 'text-end', 'render' => fn($i) => sanitize($i['phone'] ?? '—')],
        ['key' => 'salary', 'label' => 'الراتب', 'align' => 'text-end', 'render' => fn($i) => '<code dir="ltr">' . number_format($i['salary'], 0) . '</code>'],
        ['key' => 'status', 'label' => 'الحالة', 'align' => 'text-center', 'render' => fn($i) => '<span class="badge ' . ($i['status'] === 'active' ? 'bg-emerald-50 text-emerald-700' : ($i['status'] === 'suspended' ? 'bg-amber-50 text-amber-700' : 'bg-rose-50 text-rose-700')) . '" style="font-size:0.7rem;">' . ($i['status'] === 'active' ? 'نشط' : ($i['status'] === 'suspended' ? 'موقوف' : 'منتهي')) . '</span>'],
    ],
]);
require_once __DIR__ . '/../includes/footer.php';
