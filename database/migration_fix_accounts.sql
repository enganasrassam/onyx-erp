-- تحديث الحسابات الموجودة لتكون تفصيلية (is_detail = 1)
-- في نظام أونكس كل حساب فرعي يقبل الأرصدة والقيود
USE onyx_erp;

-- جعل كل الحسابات التي لها مستوى > 1 تفصيلية
UPDATE accounts SET is_detail = 1 WHERE level > 1;

-- جعل كل الحسابات الفرعية تفصيلية
UPDATE accounts SET is_detail = 1 WHERE account_nature = 'sub';

-- إذا لم يوجد account_nature، حدّث بناءً على المستوى
UPDATE accounts SET account_nature = 'main' WHERE level = 1 AND (account_nature IS NULL OR account_nature = '');
UPDATE accounts SET account_nature = 'sub' WHERE level > 1 AND (account_nature IS NULL OR account_nature = '');

-- إذا لم يوجد is_detail، حدّث بناءً على account_nature
UPDATE accounts SET is_detail = 1 WHERE account_nature = 'sub' AND is_detail = 0;
