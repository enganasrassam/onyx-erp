-- إضافة الأعمدة الناقصة لجدول accounts (مطابق لنظام أونكس)
-- يُشغّل بعد استيراد schema.sql الأساسي

USE onyx_erp;

-- ٥ طبيعة الحساب (مدين/دائن)
ALTER TABLE `accounts` ADD COLUMN IF NOT EXISTS `nature` ENUM('debit','credit') NULL AFTER `account_type`;

-- ٦ نوع التقرير (ميزانية / أرباح وخسائر)
ALTER TABLE `accounts` ADD COLUMN IF NOT EXISTS `report_type` ENUM('balance_sheet','income_statement') NULL AFTER `nature`;

-- ٧ نوع الحساب التحليلي
ALTER TABLE `accounts` ADD COLUMN IF NOT EXISTS `analytical_type` ENUM('cash','bank','customer','supplier','employee_advance','employee_custody','general') NULL AFTER `report_type`;

-- ٨ رمز العملة (ربط متعدد — نستخدم حقل نصي لتبسيط)
ALTER TABLE `accounts` ADD COLUMN IF NOT EXISTS `currency_ids` VARCHAR(255) NULL AFTER `analytical_type`;

-- تحديث الحسابات الموجودة بطبيعة ونوع تقرير
UPDATE `accounts` SET `nature` = 'debit' WHERE `account_type` IN ('asset','expense');
UPDATE `accounts` SET `nature` = 'credit' WHERE `account_type` IN ('liability','equity','revenue');
UPDATE `accounts` SET `report_type` = 'balance_sheet' WHERE `account_type` IN ('asset','liability','equity');
UPDATE `accounts` SET `report_type` = 'income_statement' WHERE `account_type` IN ('revenue','expense');

-- ربط الحسابات التحليلية
UPDATE `accounts` SET `analytical_type` = 'cash' WHERE `code` = '1101';
UPDATE `accounts` SET `analytical_type` = 'bank' WHERE `code` = '1102';
UPDATE `accounts` SET `analytical_type` = 'customer' WHERE `code` = '1103';
UPDATE `accounts` SET `analytical_type` = 'general' WHERE `code` = '1104';
UPDATE `accounts` SET `analytical_type` = 'supplier' WHERE `code` = '2101';
