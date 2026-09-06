/* نظام أونكس ERP — JavaScript الرئيسي */

// تبديل القوائم الفرعية في الشريط الجانبي (النظام الجديد)
function onyxToggle(id, hdr) {
    var el = document.getElementById(id);
    if (el) {
        el.classList.toggle('open');
        hdr.classList.toggle('open');
    }
}

// تبديل القوائم الفرعية (النظام القديم — للتوافق)
function toggleSubmenu(button) {
    button.classList.toggle('expanded');
    var submenu = button.nextElementSibling;
    if (submenu && submenu.classList.contains('sidebar-submenu')) {
        submenu.classList.toggle('open');
    }
}

// فلترة القائمة الجانبية بالبحث
function filterMenu(query) {
    var nav = document.getElementById('sidebar-nav');
    if (!nav) return;

    query = query.trim().toLowerCase();

    if (!query) {
        var items = nav.querySelectorAll('li');
        items.forEach(function(item) { item.style.display = ''; });
        var submenus = nav.querySelectorAll('.sidebar-submenu');
        submenus.forEach(function(s) { s.classList.remove('open'); });
        var parents = nav.querySelectorAll('.sidebar-parent');
        parents.forEach(function(p) { p.classList.remove('expanded'); });
        return;
    }

    var items = nav.querySelectorAll('li');
    items.forEach(function(item) { item.style.display = 'none'; });

    items.forEach(function(item) {
        var link = item.querySelector('a.sidebar-item, button.sidebar-item');
        if (!link) return;
        var text = link.textContent.toLowerCase();
        if (text.includes(query)) {
            item.style.display = '';
            var parent = item.parentElement;
            while (parent && parent !== nav) {
                if (parent.tagName === 'LI') parent.style.display = '';
                if (parent.classList && parent.classList.contains('sidebar-submenu')) {
                    parent.classList.add('open');
                }
                var prev = parent.previousElementSibling;
                if (prev && prev.classList && prev.classList.contains('sidebar-parent')) {
                    prev.classList.add('expanded');
                }
                parent = parent.parentElement;
            }
        }
    });
}

// تأكيد الحذف
function confirmDelete(message) {
    return confirm(message || 'هل أنت متأكد من الحذف؟');
}

// نسخ النص
function copyText(text) {
    navigator.clipboard.writeText(text).then(() => {
        showToast('تم النسخ', 'success');
    });
}

// إظهار رسالة toast
function showToast(message, type) {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} position-fixed`;
    toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 250px;';
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

// إخفاء التنبيهات تلقائيًا بعد 5 ثوانٍ
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        document.querySelectorAll('.alert-dismissible').forEach(alert => {
            if (window.bootstrap) {
                const bs = bootstrap.Alert.getOrCreateInstance(alert);
                bs.close();
            }
        });
    }, 5000);

    // تفعيل tooltips
    if (window.bootstrap) {
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
            new bootstrap.Tooltip(el);
        });
    }
});

// تنسيق الأرقام في الحقول
function formatNumberInput(input) {
    const value = parseFloat(input.value) || 0;
    input.value = value.toFixed(2);
}

// حساب الإجماليات في الجداول
function calculateTotals() {
    document.querySelectorAll('[data-calc-total]').forEach(container => {
        const rows = container.querySelectorAll('tbody tr');
        let total = 0;
        rows.forEach(row => {
            const amountCell = row.querySelector('[data-amount]');
            if (amountCell) {
                total += parseFloat(amountCell.dataset.amount) || 0;
            }
        });
        const totalEl = container.querySelector('[data-total]');
        if (totalEl) totalEl.textContent = total.toLocaleString('en-US', {minimumFractionDigits: 2});
    });
}

// طباعة
function printPage() {
    window.print();
}
