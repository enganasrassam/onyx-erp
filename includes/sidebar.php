<?php
/**
 * الشريط الجانبي — مطابق لنظام أونكس ERP
 * الأقسام: الرئيسية + التهيئة + أنظمة الحسابات + أنظمة المخازن + أنظمة الموردين + أنظمة العملاء + إدارة النظام
 */
function render_sidebar_menu(string $active = ''): string {
    $sections = get_menu_sections();
    $html = '';
    $idx = 0;
    foreach ($sections as $section) {
        $sid = 'sec' . $idx++;
        $html .= '<div class="onyx-sec">';
        $html .= '<div class="onyx-sec-hdr" onclick="onyxToggle(\'children-' . $sid . '\', this)">';
        $html .= '<span class="onyx-sec-ico">' . onyx_icon($section['icon']) . '</span>';
        $html .= '<span class="onyx-sec-lbl">' . htmlspecialchars($section['label']) . '</span>';
        $html .= '<span class="onyx-sec-chv">' . ONYX_CHEVRON . '</span>';
        $html .= '</div>';
        $html .= '<div class="onyx-sec-children" id="children-' . $sid . '">';
        foreach ($section['items'] as $item) {
            $html .= render_menu_item($item, $active, 0);
        }
        $html .= '</div>';
        $html .= '</div>';
    }
    return $html;
}

function render_menu_item(array $item, string $active, int $depth): string {
    $hasChildren = !empty($item['children']);
    $url = '#';
    if (!empty($item['url_path'])) {
        $url = APP_URL . '/' . $item['url_path'];
    }
    $isActive = ($item['id'] ?? '') === $active;
    $html = '';

    if ($hasChildren) {
        $gid = 'grp' . rand(1000, 9999);
        $html .= '<div class="onyx-grp">';
        $html .= '<div class="onyx-grp-hdr" onclick="onyxToggle(\'sub-' . $gid . '\', this)">';
        $html .= '<span class="onyx-grp-chv">' . ONYX_CHEVRON . '</span>';
        $html .= '<span class="onyx-grp-lbl">' . htmlspecialchars($item['label']) . '</span>';
        $html .= '</div>';
        $html .= '<div class="onyx-grp-sub" id="sub-' . $gid . '">';
        foreach ($item['children'] as $child) {
            $html .= render_menu_item($child, $active, $depth + 1);
        }
        $html .= '</div>';
        $html .= '</div>';
    } else {
        $cls = 'onyx-link';
        if ($isActive) $cls .= ' onyx-link-active';
        $html .= '<a href="' . $url . '" class="' . $cls . '">';
        $html .= '<span class="onyx-link-dot"></span>';
        $html .= '<span>' . htmlspecialchars($item['label']) . '</span>';
        $html .= '</a>';
    }
    return $html;
}

define('ONYX_CHEVRON', '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>');

function onyx_icon(string $name): string {
    $icons = [
        'dashboard' => '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>',
        'settings' => '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
        'database' => '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>',
        'boxes' => '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>',
        'truck' => '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1"/></svg>',
        'users' => '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
        'key' => '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a4 4 0 11-8 0 4 4 0 018 0zM3 21v-2a4 4 0 014-4h4a4 4 0 014 4v2"/></svg>',
    ];
    return $icons[$name] ?? '<svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/></svg>';
}

function get_menu_sections(): array {
    return [
        ['id'=>'home','label'=>'الرئيسية','icon'=>'dashboard','items'=>[
            ['id'=>'dashboard','label'=>'لوحة التحكم','url_path'=>'dashboard.php'],
        ]],
        ['id'=>'setup','label'=>'تهيئة النظام','icon'=>'settings','items'=>[
            ['label'=>'إعدادات عامة','children'=>[
                ['id'=>'setup-system-variables','label'=>'المتغيرات العامة','url_path'=>'setup/system-variables.php'],
                ['id'=>'setup-periods','label'=>'إعداد فترات النظام','url_path'=>'setup/periods.php'],
                ['id'=>'setup-currencies','label'=>'تهيئة العملات','url_path'=>'setup/currencies.php'],
            ]],
            ['label'=>'الأقاليم والدول','children'=>[
                ['id'=>'setup-countries','label'=>'بيانات الدول','url_path'=>'setup/countries.php'],
                ['id'=>'setup-governorates','label'=>'بيانات المحافظات','url_path'=>'setup/governorates.php'],
                ['id'=>'setup-cities','label'=>'بيانات المدن','url_path'=>'setup/cities.php'],
                ['id'=>'setup-districts','label'=>'بيانات المناطق','url_path'=>'setup/districts.php'],
            ]],
            ['label'=>'بيانات الشركة','children'=>[
                ['id'=>'setup-company','label'=>'بيانات الشركة','url_path'=>'setup/company.php'],
                ['id'=>'setup-branches','label'=>'بيانات الفروع','url_path'=>'setup/branches.php'],
            ]],
            ['id'=>'setup-chart-of-accounts','label'=>'الدليل المحاسبي','url_path'=>'setup/chart-of-accounts.php'],
            ['id'=>'setup-cost-centers','label'=>'مراكز التكلفة','url_path'=>'setup/cost-centers.php'],
            ['label'=>'تهيئة أنظمة المخازن','children'=>[
                ['id'=>'setup-units','label'=>'وحدات القياس','url_path'=>'setup/units.php'],
                ['id'=>'setup-price-levels','label'=>'مستويات التسعيرة','url_path'=>'setup/price-levels.php'],
                ['id'=>'setup-supply-types','label'=>'أنواع التوريد','url_path'=>'setup/supply-types.php'],
                ['id'=>'setup-discharge-types','label'=>'أنواع الصرف','url_path'=>'setup/discharge-types.php'],
                ['id'=>'setup-transfer-types','label'=>'أنواع التحويل','url_path'=>'setup/transfer-types.php'],
            ]],
            ['label'=>'تهيئة الموردين','children'=>[
                ['id'=>'setup-supplier-groups','label'=>'مجموعات الموردين','url_path'=>'setup/supplier-groups.php'],
            ]],
            ['label'=>'تهيئة العملاء','children'=>[
                ['id'=>'setup-customer-groups','label'=>'مجموعات العملاء','url_path'=>'setup/customer-groups.php'],
            ]],
        ]],
        ['id'=>'accounts','label'=>'أنظمة الحسابات','icon'=>'database','items'=>[
            ['label'=>'مدخلات الحسابات','children'=>[
                ['id'=>'inputs-admin-structure','label'=>'الهيكل الإداري','url_path'=>'inputs/admin-structure.php'],
                ['id'=>'inputs-employees','label'=>'بيانات الموظفين','url_path'=>'inputs/employees.php'],
                ['id'=>'inputs-intermediary-accounts','label'=>'الحسابات الوسيطة','url_path'=>'inputs/intermediary-accounts.php'],
                ['id'=>'inputs-cash-boxes','label'=>'شاشة الصناديق','url_path'=>'inputs/cash-boxes.php'],
                ['id'=>'inputs-banks','label'=>'شاشة البنوك','url_path'=>'inputs/banks.php'],
                ['id'=>'inputs-opening-balances','label'=>'الأرصدة الافتتاحية','url_path'=>'inputs/opening-balances.php'],
            ]],
            ['label'=>'عمليات الحسابات','children'=>[
                ['id'=>'ops-payment-vouchers','label'=>'سندات الصرف','url_path'=>'operations/payment-vouchers.php'],
                ['id'=>'ops-receipt-vouchers','label'=>'سندات القبض','url_path'=>'operations/receipt-vouchers.php'],
                ['id'=>'ops-daily-entries','label'=>'القيود اليومية','url_path'=>'operations/daily-entries.php'],
                ['id'=>'ops-currency-diff','label'=>'فوارق العملة','url_path'=>'operations/currency-diff.php'],
                ['id'=>'ops-debit-notes','label'=>'الإشعارات المدينة','url_path'=>'operations/debit-notes.php'],
                ['id'=>'ops-credit-notes','label'=>'الإشعارات الدائنة','url_path'=>'operations/credit-notes.php'],
                ['id'=>'ops-general-ledger-reports','label'=>'تقارير الأستاذ العام','url_path'=>'operations/general-ledger-reports.php'],
            ]],
        ]],
        ['id'=>'inventory','label'=>'أنظمة المخازن','icon'=>'boxes','items'=>[
            ['label'=>'مدخلات المخازن','children'=>[
                ['id'=>'inputs-inventory-main-groups','label'=>'المجموعة الرئيسية','url_path'=>'setup/inventory-main-groups.php'],
                ['id'=>'inputs-warehouse-groups','label'=>'مجموعات المخازن','url_path'=>'setup/warehouse-groups.php'],
                ['id'=>'inputs-warehouses','label'=>'بيانات المخازن','url_path'=>'inputs/warehouses.php'],
                ['id'=>'inputs-items','label'=>'بيانات الأصناف','url_path'=>'inputs/items.php'],
                ['id'=>'inputs-item-prices','label'=>'تسعيرة الأصناف','url_path'=>'inputs/item-prices.php'],
                ['id'=>'inputs-item-stock','label'=>'المخزون الافتتاحي','url_path'=>'inputs/item-stock.php'],
            ]],
            ['label'=>'عمليات المخازن','children'=>[
                ['id'=>'ops-supply-orders','label'=>'أمر التوريد المخزني','url_path'=>'operations/supply-orders.php'],
                ['id'=>'ops-discharge-orders','label'=>'أمر الصرف المخزني','url_path'=>'operations/discharge-orders.php'],
                ['id'=>'ops-inventory-transfers','label'=>'التحويل المخزني','url_path'=>'operations/inventory-transfers.php'],
                ['id'=>'ops-inventory-adjustments','label'=>'تسوية المخزون','url_path'=>'operations/inventory-adjustments.php'],
                ['id'=>'ops-inventory-reports','label'=>'التقارير المخزنية','url_path'=>'operations/inventory-reports.php'],
            ]],
        ]],
        ['id'=>'suppliers','label'=>'أنظمة الموردين','icon'=>'truck','items'=>[
            ['label'=>'مدخلات الموردين','children'=>[
                ['id'=>'inputs-suppliers','label'=>'بيانات الموردين','url_path'=>'inputs/suppliers.php'],
            ]],
            ['label'=>'عمليات المشتريات','children'=>[
                ['id'=>'ops-purchase-invoices','label'=>'فاتورة المشتريات','url_path'=>'operations/purchase-invoices.php'],
                ['id'=>'ops-purchase-returns','label'=>'مردود المشتريات','url_path'=>'operations/purchase-returns.php'],
                ['id'=>'ops-purchase-foreign','label'=>'المشتريات الخارجية','url_path'=>'operations/purchase-foreign.php'],
                ['id'=>'ops-purchase-reports','label'=>'تقارير المشتريات','url_path'=>'operations/purchase-reports.php'],
            ]],
        ]],
        ['id'=>'customers','label'=>'أنظمة العملاء','icon'=>'users','items'=>[
            ['label'=>'مدخلات العملاء','children'=>[
                ['id'=>'inputs-customers','label'=>'بيانات العملاء','url_path'=>'inputs/customers.php'],
            ]],
            ['label'=>'عمليات المبيعات','children'=>[
                ['id'=>'ops-sales-invoices','label'=>'فاتورة المبيعات','url_path'=>'operations/sales-invoices.php'],
                ['id'=>'ops-sales-returns','label'=>'مردود المبيعات','url_path'=>'operations/sales-returns.php'],
                ['id'=>'ops-sales-reports','label'=>'تقارير المبيعات','url_path'=>'operations/sales-reports.php'],
            ]],
        ]],
        ['id'=>'system','label'=>'إدارة النظام','icon'=>'key','items'=>[
            ['label'=>'المراجعة والترحيل','children'=>[
                ['id'=>'ops-reviews','label'=>'اعتماد الوثائق','url_path'=>'operations/reviews.php'],
                ['id'=>'ops-closures','label'=>'الإقفال والتوقيف','url_path'=>'operations/closures.php'],
            ]],
            ['id'=>'system-users','label'=>'بيانات المستخدمين','url_path'=>'system/users.php'],
            ['id'=>'system-permissions','label'=>'صلاحيات الشاشة','url_path'=>'system/screen-permissions.php'],
            ['id'=>'system-activity-logs','label'=>'سجل النشاط','url_path'=>'system/activity-logs.php'],
            ['id'=>'system-backup','label'=>'النسخ الاحتياطي','url_path'=>'system/backup.php'],
        ]],
    ];
}
