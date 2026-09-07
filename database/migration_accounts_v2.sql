-- تصحيح بنية جدول accounts لتطابق نظام أونكس بالضبط
-- حقل النوع = رئيسي/فرعي (وليس asset/liability)
-- حقل account_type يُستخرج من نوع التقرير + الطبيعة

USE onyx_erp;

-- إضافة حقل "النوع" (رئيسي/فرعي) — مطابق لأونكس
ALTER TABLE `accounts` ADD COLUMN IF NOT EXISTS `account_nature` ENUM('main','sub') DEFAULT 'sub' AFTER `account_type`;

-- تحديث الحسابات الموجودة
-- المستوى 1 = رئيسي، الباقي = فرعي
UPDATE `accounts` SET `account_nature` = 'main' WHERE `level` = 1;
UPDATE `accounts` SET `account_nature` = 'sub' WHERE `level` > 1;
