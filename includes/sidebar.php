<?php
/**
 * توليد شجرة القائمة الجانبية — مطابقة لنظام أونكس
 */
function render_sidebar_menu(string $active = ''): string {
    $sections = get_menu_sections();
    $html = '';
    foreach ($sections as $section) {
        $html .= '<div class="mb-2">';
        $html .= '<div class="px-4 py-1.5 text-[11px] font-bold uppercase tracking-wider text-sidebar-foreground/50 flex items-center gap-2">';
        $html .= render_icon($section['icon'], 'h-3.5 w-3.5');
        $html .= sanitize($section['label']);
        $html .= '</div><ul>';
        foreach ($section['items'] as $item) {
            $html .= render_menu_item($item, $active, 0);
        }
        $html .= '</ul></div>';
    }
    return $html;
}

function render_menu_item(array $item, string $active, int $depth): string {
    $hasChildren = !empty($item['children']);
    $isDisabled = !empty($item['disabled']) || ($item['badge'] ?? '') === 'قريبًا';
    $isActive = ($item['id'] ?? '') === $active;

    $paddingRight = $depth * 12 + 16;
    $html = '<li>';

    if ($hasChildren) {
        $html .= '<button type="button" class="sidebar-item" style="padding-right: ' . $paddingRight . 'px" onclick="toggleSubmenu(this)">';
        if (!empty($item['icon'])) $html .= render_icon($item['icon'], 'h-4 w-4 shrink-0 opacity-70');
        $html .= '<span class="flex-1 text-right truncate">' . sanitize($item['label']) . '</span>';
        $html .= '<svg class="chevron h-3.5 w-3.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>';
        $html .= '</button><ul class="hidden">';
        foreach ($item['children'] as $child) {
            $html .= render_menu_item($child, $active, $depth + 1);
        }
        $html .= '</ul>';
    } else {
        $url = $item['url'] ?? ($item['id'] ? APP_URL . '/' . ($item['url_path'] ?? $item['id'] . '.php') : '#');
        $classes = 'sidebar-item';
        if ($isActive) $classes .= ' sidebar-item-active';
        if ($isDisabled) $classes .= ' sidebar-item-disabled';
        $html .= '<a href="' . ($isDisabled ? '#' : $url) . '" class="' . $classes . '" style="padding-right: ' . $paddingRight . 'px"';
        if ($isDisabled) $html .= ' onclick="return false;"';
        $html .= '>';
        if (!empty($item['icon'])) {
            $html .= render_icon($item['icon'], 'h-4 w-4 shrink-0 opacity-70');
        } else {
            $html .= '<span class="w-3 h-3 inline-block rounded-full bg-current opacity-30 mr-2"></span>';
        }
        $html .= '<span class="flex-1 text-right truncate">' . sanitize($item['label']) . '</span>';
        if (!empty($item['badge'])) {
            $badgeClass = $item['badge'] === 'قريبًا' ? 'bg-sidebar-accent text-sidebar-foreground/60' : 'bg-amber-500/20 text-amber-300';
            $html .= '<span class="text-[9px] px-1.5 py-0.5 rounded-full ' . $badgeClass . '">' . sanitize($item['badge']) . '</span>';
        }
        $html .= '</a>';
    }

    $html .= '</li>';
    return $html;
}

function render_icon(string $name, string $classes = 'h-4 w-4'): string {
    // أيقونات SVG (من تصميم Heroicons/Lucide)
    $icons = [
        'dashboard' => '<svg class="' . $classes . '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>',
        'settings' => '<svg class="' . $classes . '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
        'database' => '<svg class="' . $classes . '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>',
        'file-text' => '<svg class="' . $classes . '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
        'users' => '<svg class="' . $classes . '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>',
        'boxes' => '<svg class="' . $classes . '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>',
        'truck' => '<svg class="' . $classes . '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1"/></svg>',
        'cart' => '<svg class="' . $classes . '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>',
        'book' => '<svg class="' . $classes . '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>',
        'branch' => '<svg class="' . $classes . '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>',
        'calendar' => '<svg class="' . $classes . '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>',
        'coins' => '<svg class="' . $classes . '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        'globe' => '<svg class="' . $classes . '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        'building' => '<svg class="' . $classes . '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>',
        'arrow-left-right' => '<svg class="' . $classes . '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7l-4 4 4 4M4 11h12m4 4l4-4-4-4M20 13H8"/></svg>',
        'package' => '<svg class="' . $classes . '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>',
        'wallet' => '<svg class="' . $classes . '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H5.25A2.25 2.25 0 003 12m18 0v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6m18 0V9M3 12V9m18 0a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 9m18 0V6a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v3"/></svg>',
        'landmark' => '<svg class="' . $classes . '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M3 10h18M5 6l7-3 7 3M4 10v11m4-11v11m4-11v11m4-11v11m4-11v11"/></svg>',
        'lock' => '<svg class="' . $classes . '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>',
    ];
    return $icons[$name] ?? '<span class="' . $classes . '">●</span>';
}

function get_menu_sections(): array {
    return [
        [
            'id' => 'dashboard',
            'label' => 'الرئيسية',
            'icon' => 'dashboard',
            'items' => [
                ['id' => 'dashboard', 'label' => 'لوحة التحكم', 'icon' => 'dashboard', 'url_path' => 'dashboard'],
            ],
        ],
        [
            'id' => 'setup',
            'label' => 'التهيئة',
            'icon' => 'settings',
            'items' => [
                [
                    'label' => 'تهيئة النظام', 'icon' => 'settings',
                    'children' => [
                        ['id' => 'setup-system-variables', 'label' => 'المتغيرات العامة', 'url_path' => 'setup/system-variables'],
                        ['id' => 'setup-periods', 'label' => 'إعداد فترات النظام', 'url_path' => 'setup/periods'],
                        ['id' => 'setup-currencies', 'label' => 'تهيئة العملات', 'url_path' => 'setup/currencies'],
                    ],
                ],
                [
                    'label' => 'الأقاليم والدول', 'icon' => 'globe',
                    'children' => [
                        ['id' => 'setup-countries', 'label' => 'بيانات الدول', 'url_path' => 'setup/countries'],
                        ['id' => 'setup-governorates', 'label' => 'بيانات المحافظات', 'url_path' => 'setup/governorates'],
                        ['id' => 'setup-cities', 'label' => 'بيانات المدن', 'url_path' => 'setup/cities'],
                    ],
                ],
                [
                    'label' => 'بيانات الشركة', 'icon' => 'building',
                    'children' => [
                        ['id' => 'setup-company', 'label' => 'بيانات الشركة', 'url_path' => 'setup/company'],
                        ['id' => 'setup-branches', 'label' => 'بيانات الفروع', 'url_path' => 'setup/branches'],
                    ],
                ],
                ['id' => 'setup-chart-of-accounts', 'label' => 'الدليل المحاسبي', 'icon' => 'book', 'url_path' => 'setup/chart-of-accounts'],
                ['id' => 'setup-cost-centers', 'label' => 'مراكز التكلفة', 'icon' => 'branch', 'url_path' => 'setup/cost-centers'],
                [
                    'label' => 'تهيئة أنظمة الحسابات', 'icon' => 'database',
                    'children' => [
                        ['id' => 'setup-units', 'label' => 'وحدات القياس', 'url_path' => 'setup/units', 'badge' => '—'],
                    ],
                ],
                [
                    'label' => 'تهيئة أنظمة المخازن', 'icon' => 'boxes',
                    'children' => [
                        ['id' => 'setup-units', 'label' => 'وحدات القياس', 'url_path' => 'setup/units'],
                        ['id' => 'setup-price-levels', 'label' => 'مستويات التسعيرة', 'url_path' => 'setup/price-levels'],
                        ['id' => 'setup-supply-types', 'label' => 'أنواع التوريد', 'url_path' => 'setup/supply-types'],
                        ['id' => 'setup-discharge-types', 'label' => 'أنواع الصرف', 'url_path' => 'setup/discharge-types'],
                        ['id' => 'setup-transfer-types', 'label' => 'أنواع التحويل', 'url_path' => 'setup/transfer-types'],
                    ],
                ],
                [
                    'label' => 'تهيئة أنظمة الموردين', 'icon' => 'truck',
                    'children' => [
                        ['id' => 'setup-supplier-groups', 'label' => 'مجموعات الموردين', 'url_path' => 'setup/supplier-groups'],
                    ],
                ],
                [
                    'label' => 'تهيئة أنظمة العملاء', 'icon' => 'users',
                    'children' => [
                        ['id' => 'setup-customer-groups', 'label' => 'مجموعات العملاء', 'url_path' => 'setup/customer-groups'],
                    ],
                ],
            ],
        ],
        [
            'id' => 'inputs',
            'label' => 'المدخلات',
            'icon' => 'file-text',
            'items' => [
                [
                    'label' => 'مدخلات عامة', 'icon' => 'file-text',
                    'children' => [
                        ['id' => 'inputs-admin-structure', 'label' => 'الهيكل الإداري', 'url_path' => 'inputs/admin-structure'],
                        ['id' => 'inputs-employees', 'label' => 'بيانات الموظفين', 'url_path' => 'inputs/employees'],
                        ['id' => 'inputs-intermediary-accounts', 'label' => 'الحسابات الوسيطة', 'url_path' => 'inputs/intermediary-accounts'],
                    ],
                ],
                [
                    'label' => 'مدخلات أنظمة الحسابات', 'icon' => 'database',
                    'children' => [
                        ['id' => 'inputs-cash-boxes', 'label' => 'شاشة الصناديق', 'url_path' => 'inputs/cash-boxes'],
                        ['id' => 'inputs-banks', 'label' => 'شاشة البنوك', 'url_path' => 'inputs/banks'],
                        ['id' => 'inputs-opening-balances', 'label' => 'الأرصدة الافتتاحية', 'url_path' => 'inputs/opening-balances'],
                    ],
                ],
                [
                    'label' => 'مدخلات أنظمة المخازن', 'icon' => 'boxes',
                    'children' => [
                        ['id' => 'inputs-inventory-main-groups', 'label' => 'المجموعة الرئيسية', 'url_path' => 'inputs/inventory-main-groups'],
                        ['id' => 'inputs-warehouse-groups', 'label' => 'مجموعات المخازن', 'url_path' => 'inputs/warehouse-groups'],
                        ['id' => 'inputs-warehouses', 'label' => 'بيانات المخازن', 'url_path' => 'inputs/warehouses'],
                        ['id' => 'inputs-items', 'label' => 'بيانات الأصناف', 'url_path' => 'inputs/items'],
                        ['id' => 'inputs-item-prices', 'label' => 'تسعيرة الأصناف', 'url_path' => 'inputs/item-prices'],
                        ['id' => 'inputs-item-stock', 'label' => 'المخزون الافتتاحي', 'url_path' => 'inputs/item-stock'],
                    ],
                ],
                [
                    'label' => 'مدخلات أنظمة الموردين', 'icon' => 'truck',
                    'children' => [
                        ['id' => 'inputs-suppliers', 'label' => 'بيانات الموردين', 'url_path' => 'inputs/suppliers'],
                    ],
                ],
                [
                    'label' => 'مدخلات أنظمة العملاء', 'icon' => 'users',
                    'children' => [
                        ['id' => 'inputs-customers', 'label' => 'بيانات العملاء', 'url_path' => 'inputs/customers'],
                    ],
                ],
            ],
        ],
        [
            'id' => 'operations',
            'label' => 'العمليات',
            'icon' => 'cart',
            'items' => [
                [
                    'label' => 'عمليات أنظمة الحسابات', 'icon' => 'database',
                    'children' => [
                        ['id' => 'ops-payment-vouchers', 'label' => 'سندات الصرف', 'url_path' => 'operations/payment-vouchers'],
                        ['id' => 'ops-receipt-vouchers', 'label' => 'سندات القبض', 'url_path' => 'operations/receipt-vouchers'],
                        ['id' => 'ops-daily-entries', 'label' => 'القيود اليومية', 'url_path' => 'operations/daily-entries'],
                    ],
                ],
                [
                    'label' => 'عمليات أنظمة المخزون', 'icon' => 'boxes',
                    'children' => [
                        ['id' => 'ops-supply-orders', 'label' => 'أوامر التوريد المخزني', 'url_path' => 'operations/supply-orders'],
                        ['id' => 'ops-discharge-orders', 'label' => 'أوامر الصرف المخزني', 'url_path' => 'operations/discharge-orders'],
                        ['id' => 'ops-inventory-transfers', 'label' => 'التحويلات المخزنية', 'url_path' => 'operations/inventory-transfers'],
                        ['id' => 'ops-inventory-adjustments', 'label' => 'تسوية المخزون', 'url_path' => 'operations/inventory-adjustments'],
                    ],
                ],
                [
                    'label' => 'عمليات المشتريات والمبيعات', 'icon' => 'cart',
                    'children' => [
                        ['id' => 'ops-purchase-invoices', 'label' => 'فواتير المشتريات', 'url_path' => 'operations/purchase-invoices'],
                        ['id' => 'ops-purchase-returns', 'label' => 'مردود المشتريات', 'url_path' => 'operations/purchase-returns'],
                        ['id' => 'ops-purchase-foreign', 'label' => 'المشتريات الخارجية', 'url_path' => 'operations/purchase-foreign'],
                        ['id' => 'ops-sales-invoices', 'label' => 'فواتير المبيعات', 'url_path' => 'operations/sales-invoices'],
                        ['id' => 'ops-sales-returns', 'label' => 'مردود المبيعات', 'url_path' => 'operations/sales-returns'],
                    ],
                ],
                [
                    'label' => 'إدارة المراجعة والترحيلات', 'icon' => 'file-text',
                    'children' => [
                        ['id' => 'ops-reviews', 'label' => 'اعتماد الوثائق', 'url_path' => 'operations/reviews'],
                        ['id' => 'ops-closures', 'label' => 'الإقفال والتوقيف', 'url_path' => 'operations/closures'],
                    ],
                ],
            ],
        ],
        [
            'id' => 'system',
            'label' => 'إدارة النظام',
            'icon' => 'users',
            'items' => [
                ['id' => 'system-users', 'label' => 'بيانات المستخدمين', 'icon' => 'users', 'url_path' => 'system/users'],
                ['id' => 'system-activity-logs', 'label' => 'سجل النشاط', 'icon' => 'file-text', 'url_path' => 'system/activity-logs'],
                ['id' => 'system-backup', 'label' => 'النسخ الاحتياطي', 'icon' => 'database', 'url_path' => 'system/backup'],
            ],
        ],
    ];
}
