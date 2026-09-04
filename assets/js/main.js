/* نظام أونكس ERP — JavaScript الرئيسي */

// تبديل القوائم الفرعية في الشريط الجانبي
function toggleSubmenu(button) {
    const submenu = button.nextElementSibling;
    const chevron = button.querySelector('.chevron');
    if (submenu) {
        submenu.classList.toggle('hidden');
        if (chevron) chevron.classList.toggle('expanded');
    }
}

// فلترة القائمة الجانبية بالبحث
function filterMenu(query) {
    const nav = document.getElementById('sidebar-nav');
    if (!nav) return;

    query = query.trim().toLowerCase();
    const items = nav.querySelectorAll('li');

    if (!query) {
        // إعادة عرض الكل
        items.forEach(item => {
            item.style.display = '';
            const sub = item.querySelector('ul');
            if (sub) sub.classList.add('hidden');
        });
        return;
    }

    items.forEach(item => {
        const link = item.querySelector('a, button');
        if (!link) return;
        const text = link.textContent.toLowerCase();
        const matches = text.includes(query);

        if (matches) {
            item.style.display = '';
            // إظهار الآباء
            let parent = item.parentElement;
            while (parent && parent !== nav) {
                if (parent.tagName === 'LI') parent.style.display = '';
                if (parent.tagName === 'UL') parent.classList.remove('hidden');
                parent = parent.parentElement;
            }
        } else {
            item.style.display = 'none';
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
