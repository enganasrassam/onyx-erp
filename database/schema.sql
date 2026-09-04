-- ============================================================
-- نظام أونكس ERP — قاعدة البيانات الكاملة
-- الإصدار 8.0 — مطابق لكتاب الاونكس للطالب 2024
-- ============================================================

CREATE DATABASE IF NOT EXISTS onyx_erp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE onyx_erp;

SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- المرحلة 1: التهيئة
-- ============================================================

-- المستخدمون والجلسات
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(255) UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(255) NOT NULL,
  `role` ENUM('admin','accountant','viewer') DEFAULT 'viewer',
  `active` TINYINT(1) DEFAULT 1,
  `last_login_at` DATETIME NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `token` VARCHAR(64) NOT NULL UNIQUE,
  `ip_address` VARCHAR(45),
  `expires_at` DATETIME NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

DROP TABLE IF EXISTS `activity_logs`;
CREATE TABLE `activity_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NULL,
  `action` VARCHAR(100) NOT NULL,
  `screen` VARCHAR(100),
  `details` TEXT,
  `ip_address` VARCHAR(45),
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- المتغيرات العامة
DROP TABLE IF EXISTS `system_variables`;
CREATE TABLE `system_variables` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `key_name` VARCHAR(100) NOT NULL UNIQUE,
  `value` TEXT,
  `category` ENUM('general','accounts','inventory','suppliers','customers') DEFAULT 'general',
  `description_ar` VARCHAR(500),
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- الفترات المحاسبية
DROP TABLE IF EXISTS `fiscal_years`;
CREATE TABLE `fiscal_years` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `year_name` VARCHAR(20) NOT NULL UNIQUE,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `period_type` ENUM('monthly','quarterly','yearly') DEFAULT 'monthly',
  `periods_count` INT DEFAULT 12,
  `active` TINYINT(1) DEFAULT 1,
  `closed` TINYINT(1) DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

DROP TABLE IF EXISTS `fiscal_periods`;
CREATE TABLE `fiscal_periods` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `fiscal_year_id` INT NOT NULL,
  `period_number` INT NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `status` ENUM('open','closed','locked') DEFAULT 'open',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`fiscal_year_id`) REFERENCES `fiscal_years`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `uniq_period` (`fiscal_year_id`, `period_number`)
) ENGINE=InnoDB;

-- العملات
DROP TABLE IF EXISTS `currencies`;
CREATE TABLE `currencies` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(10) NOT NULL UNIQUE,
  `name_ar` VARCHAR(100) NOT NULL,
  `name_en` VARCHAR(100),
  `symbol` VARCHAR(10),
  `exchange_rate` DECIMAL(15,6) DEFAULT 1.000000,
  `is_base` TINYINT(1) DEFAULT 0,
  `is_foreign` TINYINT(1) DEFAULT 0,
  `active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- الجغرافيا
DROP TABLE IF EXISTS `countries`;
CREATE TABLE `countries` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(10) NOT NULL UNIQUE,
  `name_ar` VARCHAR(100) NOT NULL,
  `name_en` VARCHAR(100),
  `active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

DROP TABLE IF EXISTS `regions`;
CREATE TABLE `regions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `country_id` INT NOT NULL,
  `code` VARCHAR(20) NOT NULL,
  `name_ar` VARCHAR(100) NOT NULL,
  `active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`country_id`) REFERENCES `countries`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `uniq_region` (`country_id`, `code`)
) ENGINE=InnoDB;

DROP TABLE IF EXISTS `governorates`;
CREATE TABLE `governorates` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `region_id` INT NOT NULL,
  `code` VARCHAR(20) NOT NULL,
  `name_ar` VARCHAR(100) NOT NULL,
  `active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`region_id`) REFERENCES `regions`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `uniq_gov` (`region_id`, `code`)
) ENGINE=InnoDB;

DROP TABLE IF EXISTS `cities`;
CREATE TABLE `cities` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `governorate_id` INT NOT NULL,
  `code` VARCHAR(20) NOT NULL,
  `name_ar` VARCHAR(100) NOT NULL,
  `active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`governorate_id`) REFERENCES `governorates`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `uniq_city` (`governorate_id`, `code`)
) ENGINE=InnoDB;

DROP TABLE IF EXISTS `districts`;
CREATE TABLE `districts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `city_id` INT NOT NULL,
  `code` VARCHAR(20) NOT NULL,
  `name_ar` VARCHAR(100) NOT NULL,
  `active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`city_id`) REFERENCES `cities`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `uniq_dist` (`city_id`, `code`)
) ENGINE=InnoDB;

-- الشركة والفروع
DROP TABLE IF EXISTS `companies`;
CREATE TABLE `companies` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name_ar` VARCHAR(255) NOT NULL,
  `name_en` VARCHAR(255),
  `tax_number` VARCHAR(50),
  `commercial_reg` VARCHAR(50),
  `address` VARCHAR(500),
  `phone` VARCHAR(50),
  `email` VARCHAR(255),
  `website` VARCHAR(255),
  `logo_url` VARCHAR(500),
  `base_currency_id` INT,
  `fiscal_year_start_month` INT DEFAULT 1,
  `active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`base_currency_id`) REFERENCES `currencies`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

DROP TABLE IF EXISTS `branches`;
CREATE TABLE `branches` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `company_id` INT NOT NULL,
  `code` VARCHAR(20) NOT NULL,
  `name_ar` VARCHAR(255) NOT NULL,
  `name_en` VARCHAR(255),
  `address` VARCHAR(500),
  `phone` VARCHAR(50),
  `manager` VARCHAR(255),
  `active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `uniq_branch` (`company_id`, `code`)
) ENGINE=InnoDB;

-- الدليل المحاسبي
DROP TABLE IF EXISTS `accounts`;
CREATE TABLE `accounts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(50) NOT NULL UNIQUE,
  `name_ar` VARCHAR(255) NOT NULL,
  `name_en` VARCHAR(255),
  `parent_id` INT NULL,
  `account_type` ENUM('asset','liability','equity','revenue','expense') NOT NULL,
  `level` INT DEFAULT 1,
  `is_detail` TINYINT(1) DEFAULT 0,
  `balance` DECIMAL(15,2) DEFAULT 0,
  `opening_balance` DECIMAL(15,2) DEFAULT 0,
  `active` TINYINT(1) DEFAULT 1,
  `notes` TEXT,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`parent_id`) REFERENCES `accounts`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- مراكز التكلفة
DROP TABLE IF EXISTS `cost_centers`;
CREATE TABLE `cost_centers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(50) NOT NULL UNIQUE,
  `name_ar` VARCHAR(255) NOT NULL,
  `name_en` VARCHAR(255),
  `parent_id` INT NULL,
  `level` INT DEFAULT 1,
  `active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`parent_id`) REFERENCES `cost_centers`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- وحدات القياس
DROP TABLE IF EXISTS `units`;
CREATE TABLE `units` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name_ar` VARCHAR(50) NOT NULL UNIQUE,
  `name_en` VARCHAR(50),
  `base_unit` TINYINT(1) DEFAULT 0,
  `factor` DECIMAL(10,4) DEFAULT 1.0000,
  `active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- مستويات التسعيرة
DROP TABLE IF EXISTS `price_levels`;
CREATE TABLE `price_levels` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `level` INT NOT NULL UNIQUE,
  `name_ar` VARCHAR(100) NOT NULL,
  `name_en` VARCHAR(100),
  `default_discount` DECIMAL(5,2) DEFAULT 0,
  `active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- أنواع التوريد والصرف والتحويل
DROP TABLE IF EXISTS `supply_types`;
CREATE TABLE `supply_types` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(20) NOT NULL UNIQUE,
  `name_ar` VARCHAR(100) NOT NULL,
  `name_en` VARCHAR(100),
  `active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

DROP TABLE IF EXISTS `discharge_types`;
CREATE TABLE `discharge_types` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(20) NOT NULL UNIQUE,
  `name_ar` VARCHAR(100) NOT NULL,
  `name_en` VARCHAR(100),
  `active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

DROP TABLE IF EXISTS `transfer_types`;
CREATE TABLE `transfer_types` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(20) NOT NULL UNIQUE,
  `name_ar` VARCHAR(100) NOT NULL,
  `name_en` VARCHAR(100),
  `active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- مجموعات الموردين والعملاء
DROP TABLE IF EXISTS `supplier_groups`;
CREATE TABLE `supplier_groups` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(20) NOT NULL UNIQUE,
  `name_ar` VARCHAR(255) NOT NULL,
  `name_en` VARCHAR(255),
  `parent_id` INT NULL,
  `active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`parent_id`) REFERENCES `supplier_groups`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB;

DROP TABLE IF EXISTS `suppliers`;
CREATE TABLE `suppliers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(20) NOT NULL UNIQUE,
  `name_ar` VARCHAR(255) NOT NULL,
  `name_en` VARCHAR(255),
  `group_id` INT,
  `tax_number` VARCHAR(50),
  `phone` VARCHAR(50),
  `email` VARCHAR(255),
  `address` VARCHAR(500),
  `contact_person` VARCHAR(255),
  `opening_balance` DECIMAL(15,2) DEFAULT 0,
  `active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`group_id`) REFERENCES `supplier_groups`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

DROP TABLE IF EXISTS `customer_groups`;
CREATE TABLE `customer_groups` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(20) NOT NULL UNIQUE,
  `name_ar` VARCHAR(255) NOT NULL,
  `name_en` VARCHAR(255),
  `parent_id` INT NULL,
  `active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`parent_id`) REFERENCES `customer_groups`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB;

DROP TABLE IF EXISTS `customers`;
CREATE TABLE `customers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(20) NOT NULL UNIQUE,
  `name_ar` VARCHAR(255) NOT NULL,
  `name_en` VARCHAR(255),
  `group_id` INT,
  `tax_number` VARCHAR(50),
  `phone` VARCHAR(50),
  `email` VARCHAR(255),
  `address` VARCHAR(500),
  `contact_person` VARCHAR(255),
  `opening_balance` DECIMAL(15,2) DEFAULT 0,
  `credit_limit` DECIMAL(15,2) DEFAULT 0,
  `active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`group_id`) REFERENCES `customer_groups`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- المرحلة 2: المدخلات
-- ============================================================

-- الهيكل الإداري
DROP TABLE IF EXISTS `admin_structures`;
CREATE TABLE `admin_structures` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(20) NOT NULL UNIQUE,
  `name_ar` VARCHAR(255) NOT NULL,
  `parent_id` INT NULL,
  `level` INT DEFAULT 1,
  `notes` TEXT,
  `active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`parent_id`) REFERENCES `admin_structures`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- الموظفون
DROP TABLE IF EXISTS `employees`;
CREATE TABLE `employees` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `employee_number` VARCHAR(20) NOT NULL UNIQUE,
  `branch_id` INT,
  `first_name` VARCHAR(100) NOT NULL,
  `last_name` VARCHAR(100) NOT NULL,
  `job_title` VARCHAR(255),
  `admin_structure_id` INT,
  `hire_date` DATE,
  `national_id` VARCHAR(50),
  `phone` VARCHAR(50),
  `email` VARCHAR(255),
  `address` VARCHAR(500),
  `salary` DECIMAL(15,2) DEFAULT 0,
  `currency_id` INT,
  `account_id` INT,
  `status` ENUM('active','suspended','terminated') DEFAULT 'active',
  `notes` TEXT,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`admin_structure_id`) REFERENCES `admin_structures`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`currency_id`) REFERENCES `currencies`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`account_id`) REFERENCES `accounts`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- الحسابات الوسيطة
DROP TABLE IF EXISTS `intermediary_accounts`;
CREATE TABLE `intermediary_accounts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(20) NOT NULL UNIQUE,
  `name_ar` VARCHAR(255) NOT NULL,
  `type` ENUM('currency_diff','missing_items','payment_notes','receipt_notes','fraction_diff','cost_diff','commission','commission_num','other') NOT NULL,
  `linked_account_id` INT,
  `description_ar` TEXT,
  `active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`linked_account_id`) REFERENCES `accounts`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- الصناديق
DROP TABLE IF EXISTS `cash_boxes`;
CREATE TABLE `cash_boxes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(20) NOT NULL UNIQUE,
  `name_ar` VARCHAR(255) NOT NULL,
  `name_en` VARCHAR(255),
  `operation_type` ENUM('receipt','payment','both') DEFAULT 'both',
  `branch_id` INT,
  `account_id` INT,
  `sequence` INT DEFAULT 1,
  `active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`account_id`) REFERENCES `accounts`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

DROP TABLE IF EXISTS `cash_box_currencies`;
CREATE TABLE `cash_box_currencies` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `cash_box_id` INT NOT NULL,
  `currency_id` INT NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`cash_box_id`) REFERENCES `cash_boxes`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`currency_id`) REFERENCES `currencies`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `uniq_cb_curr` (`cash_box_id`, `currency_id`)
) ENGINE=InnoDB;

-- البنوك
DROP TABLE IF EXISTS `banks`;
CREATE TABLE `banks` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(20) NOT NULL UNIQUE,
  `name_ar` VARCHAR(255) NOT NULL,
  `name_en` VARCHAR(255),
  `branch_id` INT,
  `account_id` INT,
  `receipt_sequence` VARCHAR(50),
  `sequence` INT DEFAULT 1,
  `active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`account_id`) REFERENCES `accounts`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

DROP TABLE IF EXISTS `bank_currencies`;
CREATE TABLE `bank_currencies` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `bank_id` INT NOT NULL,
  `currency_id` INT NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`bank_id`) REFERENCES `banks`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`currency_id`) REFERENCES `currencies`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `uniq_bk_curr` (`bank_id`, `currency_id`)
) ENGINE=InnoDB;

-- الأرصدة الافتتاحية
DROP TABLE IF EXISTS `opening_balances`;
CREATE TABLE `opening_balances` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `account_id` INT NOT NULL,
  `currency_id` INT NOT NULL,
  `debit_local` DECIMAL(15,2) DEFAULT 0,
  `credit_local` DECIMAL(15,2) DEFAULT 0,
  `debit_foreign` DECIMAL(15,2) DEFAULT 0,
  `credit_foreign` DECIMAL(15,2) DEFAULT 0,
  `fiscal_year_id` INT,
  `notes` TEXT,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`account_id`) REFERENCES `accounts`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`currency_id`) REFERENCES `currencies`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `uniq_ob` (`account_id`, `currency_id`)
) ENGINE=InnoDB;

-- المجموعة الرئيسية للأصناف
DROP TABLE IF EXISTS `inventory_main_groups`;
CREATE TABLE `inventory_main_groups` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(20) NOT NULL UNIQUE,
  `name_ar` VARCHAR(255) NOT NULL,
  `name_en` VARCHAR(255),
  `item_code_prefix` VARCHAR(20),
  `active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- مجموعات المخازن
DROP TABLE IF EXISTS `warehouse_groups`;
CREATE TABLE `warehouse_groups` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(20) NOT NULL UNIQUE,
  `name_ar` VARCHAR(255) NOT NULL,
  `name_en` VARCHAR(255),
  `main_group_id` INT,
  `active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`main_group_id`) REFERENCES `inventory_main_groups`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- المخازن
DROP TABLE IF EXISTS `warehouses`;
CREATE TABLE `warehouses` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(20) NOT NULL UNIQUE,
  `name_ar` VARCHAR(255) NOT NULL,
  `name_en` VARCHAR(255),
  `group_id` INT,
  `branch_id` INT,
  `transfer_account_id` INT,
  `active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`group_id`) REFERENCES `warehouse_groups`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`transfer_account_id`) REFERENCES `accounts`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- الأصناف
DROP TABLE IF EXISTS `items`;
CREATE TABLE `items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(50) NOT NULL UNIQUE,
  `barcode` VARCHAR(100),
  `name_ar` VARCHAR(255) NOT NULL,
  `name_en` VARCHAR(255),
  `description` TEXT,
  `main_group_id` INT,
  `base_unit_id` INT,
  `item_type` ENUM('simple','composite','attached') DEFAULT 'simple',
  `inventory_method` ENUM('fifo','lifo','weighted_average') DEFAULT 'weighted_average',
  `inventory_account_id` INT,
  `sales_account_id` INT,
  `cost_account_id` INT,
  `purchase_account_id` INT,
  `active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`main_group_id`) REFERENCES `inventory_main_groups`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`base_unit_id`) REFERENCES `units`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`inventory_account_id`) REFERENCES `accounts`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`sales_account_id`) REFERENCES `accounts`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`cost_account_id`) REFERENCES `accounts`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`purchase_account_id`) REFERENCES `accounts`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- وحدات الصنف
DROP TABLE IF EXISTS `item_units`;
CREATE TABLE `item_units` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `item_id` INT NOT NULL,
  `unit_id` INT NOT NULL,
  `factor` DECIMAL(10,4) DEFAULT 1.0000,
  `barcode` VARCHAR(100),
  `is_default` TINYINT(1) DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`item_id`) REFERENCES `items`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`unit_id`) REFERENCES `units`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `uniq_iu` (`item_id`, `unit_id`)
) ENGINE=InnoDB;

-- تسعيرة الصنف
DROP TABLE IF EXISTS `item_prices`;
CREATE TABLE `item_prices` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `item_id` INT NOT NULL,
  `price_level_id` INT,
  `unit_id` INT,
  `currency_id` INT,
  `purchase_price` DECIMAL(15,2) DEFAULT 0,
  `wholesale_price` DECIMAL(15,2) DEFAULT 0,
  `retail_price` DECIMAL(15,2) DEFAULT 0,
  `min_price` DECIMAL(15,2) DEFAULT 0,
  `discount_pct` DECIMAL(5,2) DEFAULT 0,
  `effective_date` DATE,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`item_id`) REFERENCES `items`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`price_level_id`) REFERENCES `price_levels`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`unit_id`) REFERENCES `units`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`currency_id`) REFERENCES `currencies`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- المخزون الافتتاحي
DROP TABLE IF EXISTS `item_stocks`;
CREATE TABLE `item_stocks` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `item_id` INT NOT NULL,
  `warehouse_id` INT NOT NULL,
  `quantity` DECIMAL(15,4) DEFAULT 0,
  `unit_cost` DECIMAL(15,4) DEFAULT 0,
  `total_cost` DECIMAL(15,2) DEFAULT 0,
  `type` ENUM('opening','current') DEFAULT 'opening',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`item_id`) REFERENCES `items`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `uniq_stock` (`item_id`, `warehouse_id`, `type`)
) ENGINE=InnoDB;

-- الأصناف المركبة (BOM)
DROP TABLE IF EXISTS `item_components`;
CREATE TABLE `item_components` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `parent_item_id` INT NOT NULL,
  `component_item_id` INT NOT NULL,
  `quantity` DECIMAL(15,4) DEFAULT 1,
  `unit_id` INT,
  `notes` TEXT,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`parent_item_id`) REFERENCES `items`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`component_item_id`) REFERENCES `items`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`unit_id`) REFERENCES `units`(`id`) ON DELETE SET NULL,
  UNIQUE KEY `uniq_comp` (`parent_item_id`, `component_item_id`)
) ENGINE=InnoDB;

-- ============================================================
-- المرحلة 3: العمليات
-- ============================================================

-- السندات (صرف/قبض)
DROP TABLE IF EXISTS `vouchers`;
CREATE TABLE `vouchers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `voucher_number` VARCHAR(50) NOT NULL UNIQUE,
  `type` ENUM('payment','receipt') NOT NULL,
  `method` ENUM('cash','bank') NOT NULL,
  `branch_id` INT,
  `cash_box_id` INT,
  `bank_id` INT,
  `currency_id` INT NOT NULL,
  `exchange_rate` DECIMAL(15,6) DEFAULT 1.000000,
  `voucher_date` DATE NOT NULL,
  `due_date` DATE,
  `amount_local` DECIMAL(15,2) DEFAULT 0,
  `amount_foreign` DECIMAL(15,2) DEFAULT 0,
  `cheque_number` VARCHAR(50),
  `cheque_method` ENUM('voucher_date','due_date','notes_auto','manual'),
  `status` ENUM('draft','reviewed','posted','cancelled') DEFAULT 'draft',
  `notes` TEXT,
  `created_by` INT,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`cash_box_id`) REFERENCES `cash_boxes`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`bank_id`) REFERENCES `banks`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`currency_id`) REFERENCES `currencies`(`id`),
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_voucher_type` (`type`, `method`),
  INDEX `idx_voucher_date` (`voucher_date`)
) ENGINE=InnoDB;

DROP TABLE IF EXISTS `voucher_lines`;
CREATE TABLE `voucher_lines` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `voucher_id` INT NOT NULL,
  `line_number` INT NOT NULL,
  `account_id` INT NOT NULL,
  `debit_local` DECIMAL(15,2) DEFAULT 0,
  `credit_local` DECIMAL(15,2) DEFAULT 0,
  `debit_foreign` DECIMAL(15,2) DEFAULT 0,
  `credit_foreign` DECIMAL(15,2) DEFAULT 0,
  `description` VARCHAR(500),
  `cost_center_id` INT,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`voucher_id`) REFERENCES `vouchers`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`account_id`) REFERENCES `accounts`(`id`),
  FOREIGN KEY (`cost_center_id`) REFERENCES `cost_centers`(`id`) ON DELETE SET NULL,
  INDEX `idx_vl_voucher` (`voucher_id`),
  INDEX `idx_vl_account` (`account_id`)
) ENGINE=InnoDB;

-- القيود اليومية
DROP TABLE IF EXISTS `daily_entries`;
CREATE TABLE `daily_entries` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `entry_number` VARCHAR(50) NOT NULL UNIQUE,
  `branch_id` INT,
  `entry_date` DATE NOT NULL,
  `source` ENUM('manual','voucher','invoice','inventory') DEFAULT 'manual',
  `source_id` INT,
  `description` TEXT,
  `total_debit` DECIMAL(15,2) DEFAULT 0,
  `total_credit` DECIMAL(15,2) DEFAULT 0,
  `status` ENUM('draft','posted','cancelled','reversed') DEFAULT 'draft',
  `is_reversed` TINYINT(1) DEFAULT 0,
  `fiscal_period_id` INT,
  `created_by` INT,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_de_date` (`entry_date`),
  INDEX `idx_de_status` (`status`)
) ENGINE=InnoDB;

DROP TABLE IF EXISTS `daily_entry_lines`;
CREATE TABLE `daily_entry_lines` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `daily_entry_id` INT NOT NULL,
  `line_number` INT NOT NULL,
  `account_id` INT NOT NULL,
  `debit_local` DECIMAL(15,2) DEFAULT 0,
  `credit_local` DECIMAL(15,2) DEFAULT 0,
  `debit_foreign` DECIMAL(15,2) DEFAULT 0,
  `credit_foreign` DECIMAL(15,2) DEFAULT 0,
  `description` VARCHAR(500),
  `cost_center_id` INT,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`daily_entry_id`) REFERENCES `daily_entries`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`account_id`) REFERENCES `accounts`(`id`),
  FOREIGN KEY (`cost_center_id`) REFERENCES `cost_centers`(`id`) ON DELETE SET NULL,
  INDEX `idx_del_entry` (`daily_entry_id`),
  INDEX `idx_del_account` (`account_id`)
) ENGINE=InnoDB;

-- أوامر التوريد والصرف المخزني
DROP TABLE IF EXISTS `inventory_orders`;
CREATE TABLE `inventory_orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_number` VARCHAR(50) NOT NULL UNIQUE,
  `type` ENUM('supply','discharge') NOT NULL,
  `order_type_id` INT,
  `warehouse_id` INT NOT NULL,
  `branch_id` INT,
  `order_date` DATE NOT NULL,
  `party_type` ENUM('supplier','customer','employee','account'),
  `party_id` INT,
  `party_name` VARCHAR(255),
  `inventory_account_id` INT,
  `total_quantity` DECIMAL(15,4) DEFAULT 0,
  `total_cost` DECIMAL(15,2) DEFAULT 0,
  `status` ENUM('draft','posted','cancelled') DEFAULT 'draft',
  `notes` TEXT,
  `created_by` INT,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses`(`id`),
  FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_io_type_date` (`type`, `order_date`),
  INDEX `idx_io_warehouse` (`warehouse_id`)
) ENGINE=InnoDB;

DROP TABLE IF EXISTS `inventory_order_lines`;
CREATE TABLE `inventory_order_lines` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `inventory_order_id` INT NOT NULL,
  `line_number` INT NOT NULL,
  `item_id` INT NOT NULL,
  `unit_id` INT,
  `quantity` DECIMAL(15,4) DEFAULT 0,
  `unit_cost` DECIMAL(15,4) DEFAULT 0,
  `total_cost` DECIMAL(15,2) DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`inventory_order_id`) REFERENCES `inventory_orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`item_id`) REFERENCES `items`(`id`),
  FOREIGN KEY (`unit_id`) REFERENCES `units`(`id`) ON DELETE SET NULL,
  INDEX `idx_iol_order` (`inventory_order_id`),
  INDEX `idx_iol_item` (`item_id`)
) ENGINE=InnoDB;

-- التحويلات المخزنية
DROP TABLE IF EXISTS `inventory_transfers`;
CREATE TABLE `inventory_transfers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `transfer_number` VARCHAR(50) NOT NULL UNIQUE,
  `type` ENUM('transfer','receipt') NOT NULL,
  `from_warehouse_id` INT,
  `to_warehouse_id` INT,
  `transfer_date` DATE NOT NULL,
  `branch_id` INT,
  `total_quantity` DECIMAL(15,4) DEFAULT 0,
  `total_cost` DECIMAL(15,2) DEFAULT 0,
  `status` ENUM('draft','posted','cancelled') DEFAULT 'draft',
  `notes` TEXT,
  `created_by` INT,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`from_warehouse_id`) REFERENCES `warehouses`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`to_warehouse_id`) REFERENCES `warehouses`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_it_date` (`transfer_date`)
) ENGINE=InnoDB;

DROP TABLE IF EXISTS `inventory_transfer_lines`;
CREATE TABLE `inventory_transfer_lines` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `transfer_id` INT NOT NULL,
  `line_number` INT NOT NULL,
  `item_id` INT NOT NULL,
  `unit_id` INT,
  `quantity` DECIMAL(15,4) DEFAULT 0,
  `unit_cost` DECIMAL(15,4) DEFAULT 0,
  `total_cost` DECIMAL(15,2) DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`transfer_id`) REFERENCES `inventory_transfers`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`item_id`) REFERENCES `items`(`id`),
  FOREIGN KEY (`unit_id`) REFERENCES `units`(`id`) ON DELETE SET NULL,
  INDEX `idx_itl_transfer` (`transfer_id`)
) ENGINE=InnoDB;

-- تسوية المخزون
DROP TABLE IF EXISTS `inventory_adjustments`;
CREATE TABLE `inventory_adjustments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `adjustment_number` VARCHAR(50) NOT NULL UNIQUE,
  `warehouse_id` INT NOT NULL,
  `adjustment_date` DATE NOT NULL,
  `branch_id` INT,
  `type` ENUM('increase','decrease') NOT NULL,
  `total_quantity` DECIMAL(15,4) DEFAULT 0,
  `total_cost` DECIMAL(15,2) DEFAULT 0,
  `adjustment_account_id` INT,
  `status` ENUM('draft','posted','cancelled') DEFAULT 'draft',
  `notes` TEXT,
  `created_by` INT,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses`(`id`),
  FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_ia_date` (`adjustment_date`)
) ENGINE=InnoDB;

DROP TABLE IF EXISTS `inventory_adjustment_lines`;
CREATE TABLE `inventory_adjustment_lines` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `adjustment_id` INT NOT NULL,
  `line_number` INT NOT NULL,
  `item_id` INT NOT NULL,
  `unit_id` INT,
  `book_quantity` DECIMAL(15,4) DEFAULT 0,
  `actual_quantity` DECIMAL(15,4) DEFAULT 0,
  `difference` DECIMAL(15,4) DEFAULT 0,
  `unit_cost` DECIMAL(15,4) DEFAULT 0,
  `total_cost` DECIMAL(15,2) DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`adjustment_id`) REFERENCES `inventory_adjustments`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`item_id`) REFERENCES `items`(`id`),
  FOREIGN KEY (`unit_id`) REFERENCES `units`(`id`) ON DELETE SET NULL,
  INDEX `idx_ial_adj` (`adjustment_id`)
) ENGINE=InnoDB;

-- الفواتير (مشتريات/مبيعات/مردودات)
DROP TABLE IF EXISTS `invoices`;
CREATE TABLE `invoices` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `invoice_number` VARCHAR(50) NOT NULL UNIQUE,
  `type` ENUM('purchase','purchase_return','sales','sales_return','purchase_foreign') NOT NULL,
  `party_type` ENUM('supplier','customer') NOT NULL,
  `party_id` INT NOT NULL,
  `party_name` VARCHAR(255),
  `branch_id` INT,
  `cash_box_id` INT,
  `bank_id` INT,
  `warehouse_id` INT,
  `currency_id` INT NOT NULL,
  `exchange_rate` DECIMAL(15,6) DEFAULT 1.000000,
  `invoice_date` DATE NOT NULL,
  `due_date` DATE,
  `payment_method` ENUM('cash','bank','credit') DEFAULT 'credit',
  `subtotal` DECIMAL(15,2) DEFAULT 0,
  `discount` DECIMAL(15,2) DEFAULT 0,
  `discount_pct` DECIMAL(5,2) DEFAULT 0,
  `tax_pct` DECIMAL(5,2) DEFAULT 0,
  `tax_amount` DECIMAL(15,2) DEFAULT 0,
  `additional_costs` DECIMAL(15,2) DEFAULT 0,
  `total_local` DECIMAL(15,2) DEFAULT 0,
  `total_foreign` DECIMAL(15,2) DEFAULT 0,
  `paid_amount` DECIMAL(15,2) DEFAULT 0,
  `status` ENUM('draft','posted','cancelled','paid','partial') DEFAULT 'draft',
  `notes` TEXT,
  `created_by` INT,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`cash_box_id`) REFERENCES `cash_boxes`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`bank_id`) REFERENCES `banks`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`currency_id`) REFERENCES `currencies`(`id`),
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_inv_type_date` (`type`, `invoice_date`),
  INDEX `idx_inv_party` (`party_type`, `party_id`)
) ENGINE=InnoDB;

DROP TABLE IF EXISTS `invoice_lines`;
CREATE TABLE `invoice_lines` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `invoice_id` INT NOT NULL,
  `line_number` INT NOT NULL,
  `item_id` INT NOT NULL,
  `quantity` DECIMAL(15,4) DEFAULT 0,
  `unit_id` INT,
  `unit_price` DECIMAL(15,2) DEFAULT 0,
  `discount_pct` DECIMAL(5,2) DEFAULT 0,
  `discount_amount` DECIMAL(15,2) DEFAULT 0,
  `total` DECIMAL(15,2) DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`invoice_id`) REFERENCES `invoices`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`item_id`) REFERENCES `items`(`id`),
  FOREIGN KEY (`unit_id`) REFERENCES `units`(`id`) ON DELETE SET NULL,
  INDEX `idx_il_invoice` (`invoice_id`),
  INDEX `idx_il_item` (`item_id`)
) ENGINE=InnoDB;

-- سجل المراجعات
DROP TABLE IF EXISTS `document_reviews`;
CREATE TABLE `document_reviews` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `doc_type` ENUM('voucher','daily_entry','inventory_order','transfer','adjustment','invoice') NOT NULL,
  `doc_id` INT NOT NULL,
  `action` ENUM('review','post','unpost','cancel','reverse','mark_paid') NOT NULL,
  `reviewed_by` INT,
  `reviewed_at` DATETIME,
  `notes` TEXT,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`reviewed_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_dr_doc` (`doc_type`, `doc_id`),
  INDEX `idx_dr_id` (`doc_id`)
) ENGINE=InnoDB;

-- الإقفال والتوقيف
DROP TABLE IF EXISTS `period_closures`;
CREATE TABLE `period_closures` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `fiscal_year_id` INT NOT NULL,
  `period_number` INT NOT NULL,
  `closure_date` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `closure_type` ENUM('monthly_suspension','monthly_close','yearly_close') NOT NULL,
  `closed_by` INT,
  `notes` TEXT,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`fiscal_year_id`) REFERENCES `fiscal_years`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`closed_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  UNIQUE KEY `uniq_closure` (`fiscal_year_id`, `period_number`, `closure_type`)
) ENGINE=InnoDB;

-- ============================================================
-- بيانات أولية
-- ============================================================

-- مدير النظام (admin / admin123 — مشفّر بـ password_hash)
INSERT INTO `users` (`username`, `email`, `password_hash`, `full_name`, `role`, `active`) VALUES
('admin', 'admin@onyx.local', '$2y$10$N9qO8r3Kq5lJ2vXm4pY0HeY0XrHbWgZr5LpXgVq5mYkQv5lJ2vXmW', 'مدير النظام', 'admin', 1);

-- المتغيرات العامة
INSERT INTO `system_variables` (`key_name`, `value`, `category`, `description_ar`) VALUES
('report_branding_by_user', 'false', 'general', 'ترويسة التقارير حسب المستخدم'),
('use_document_review', 'true', 'general', 'استخدام نظام مراجعة الوثائق'),
('show_report_search_engine', 'true', 'general', 'عرض محرك بحث التقارير'),
('calendar_type', 'gregorian', 'general', 'نوع التقويم (هجري/ميلادي)'),
('use_foreign_currencies', 'true', 'general', 'استخدام العملات الأجنبية'),
('use_cost_centers', 'optional', 'general', 'استخدام مراكز التكلفة'),
('account_sublevel', '1', 'accounts', 'رتبة الحساب الفرعي'),
('credit_debit_sequencing', 'default', 'accounts', 'تسلسل الحسابات المدينة والدائنة'),
('use_inventory_variables', 'true', 'inventory', 'تفعيل متغيرات المخزون'),
('use_item_extras', 'false', 'inventory', 'تفعيل الأصناف الملحقة'),
('auto_item_numbering', 'false', 'inventory', 'تفعيل التسلسل الآلي للأصناف'),
('multi_currency_inventory', 'false', 'inventory', 'تحديد عملة المخزون'),
('supplier_variables', 'true', 'suppliers', 'تفعيل متغيرات نظام الموردين'),
('customer_variables', 'true', 'customers', 'تفعيل متغيرات نظام العملاء');

-- العملات
INSERT INTO `currencies` (`code`, `name_ar`, `name_en`, `symbol`, `exchange_rate`, `is_base`, `is_foreign`, `active`) VALUES
('YER', 'ريال يمني', 'Yemeni Rial', '﷼', 1.000000, 1, 0, 1),
('USD', 'دولار أمريكي', 'US Dollar', '$', 530.000000, 0, 1, 1),
('SAR', 'ريال سعودي', 'Saudi Riyal', 'ر.س', 141.000000, 0, 1, 1);

-- الشركة والفرع
INSERT INTO `companies` (`name_ar`, `name_en`, `tax_number`, `commercial_reg`, `address`, `phone`, `email`, `fiscal_year_start_month`, `active`) VALUES
('شركة الأونكس التجارية', 'Onyx Trading Co.', '000000000000000', 'CR-0001', 'صنعاء - اليمن', '+967 1 000 000', 'info@onyx.local', 1, 1);

SET @company_id = LAST_INSERT_ID();

INSERT INTO `branches` (`company_id`, `code`, `name_ar`, `name_en`, `address`, `phone`, `manager`, `active`) VALUES
(@company_id, '001', 'الفرع الرئيسي', 'Main Branch', 'صنعاء - شارع حدة', '+967 1 000 000', 'مدير الفرع', 1);

-- اليمن
INSERT INTO `countries` (`code`, `name_ar`, `name_en`, `active`) VALUES
('YE', 'اليمن', 'Yemen', 1);
SET @country_id = LAST_INSERT_ID();

INSERT INTO `regions` (`country_id`, `code`, `name_ar`, `active`) VALUES
(@country_id, 'NORTH', 'الإقليم الشمالي', 1);
SET @region_id = LAST_INSERT_ID();

INSERT INTO `governorates` (`region_id`, `code`, `name_ar`, `active`) VALUES
(@region_id, 'SA', 'صنعاء', 1),
(@region_id, 'AD', 'عدن', 1),
(@region_id, 'TA', 'تعز', 1),
(@region_id, 'HD', 'الحديدة', 1);

-- الدليل المحاسبي الافتراضي
INSERT INTO `accounts` (`code`, `name_ar`, `account_type`, `level`, `is_detail`, `active`) VALUES
('1', 'الأصول', 'asset', 1, 0, 1),
('2', 'الخصوم', 'liability', 1, 0, 1),
('3', 'حقوق الملكية', 'equity', 1, 0, 1),
('4', 'الإيرادات', 'revenue', 1, 0, 1),
('5', 'المصروفات', 'expense', 1, 0, 1);

SET @acc1 = (SELECT id FROM accounts WHERE code = '1');
SET @acc2 = (SELECT id FROM accounts WHERE code = '2');
SET @acc3 = (SELECT id FROM accounts WHERE code = '3');
SET @acc4 = (SELECT id FROM accounts WHERE code = '4');
SET @acc5 = (SELECT id FROM accounts WHERE code = '5');

INSERT INTO `accounts` (`code`, `name_ar`, `parent_id`, `account_type`, `level`, `is_detail`, `active`) VALUES
('11', 'الأصول المتداولة', @acc1, 'asset', 2, 0, 1),
('12', 'الأصول الثابتة', @acc1, 'asset', 2, 0, 1),
('21', 'الخصوم المتداولة', @acc2, 'liability', 2, 0, 1),
('22', 'الخصوم طويلة الأجل', @acc2, 'liability', 2, 0, 1);

SET @acc11 = (SELECT id FROM accounts WHERE code = '11');
SET @acc21 = (SELECT id FROM accounts WHERE code = '21');

INSERT INTO `accounts` (`code`, `name_ar`, `parent_id`, `account_type`, `level`, `is_detail`, `active`) VALUES
('1101', 'الصندوق', @acc11, 'asset', 3, 1, 1),
('1102', 'البنك', @acc11, 'asset', 3, 1, 1),
('1103', 'العملاء', @acc11, 'asset', 3, 1, 1),
('1104', 'المخزون', @acc11, 'asset', 3, 1, 1),
('2101', 'الموردون', @acc21, 'liability', 3, 1, 1),
('2102', 'مصروفات مستحقة', @acc21, 'liability', 3, 1, 1),
('3101', 'رأس المال', @acc3, 'equity', 3, 1, 1),
('3102', 'الأرباح المرحلة', @acc3, 'equity', 3, 1, 1),
('4101', 'إيرادات المبيعات', @acc4, 'revenue', 3, 1, 1),
('4102', 'إيرادات أخرى', @acc4, 'revenue', 3, 1, 1),
('5101', 'مصروفات الرواتب', @acc5, 'expense', 3, 1, 1),
('5102', 'مصروفات الإيجار', @acc5, 'expense', 3, 1, 1),
('5103', 'مصروفات الكهرباء', @acc5, 'expense', 3, 1, 1);

-- مراكز التكلفة
INSERT INTO `cost_centers` (`code`, `name_ar`, `level`, `active`) VALUES
('00', 'المركز الرئيسي', 1, 1);
SET @cc_id = (SELECT id FROM cost_centers WHERE code = '00');
INSERT INTO `cost_centers` (`code`, `name_ar`, `parent_id`, `level`, `active`) VALUES
('01', 'قسم المبيعات', @cc_id, 2, 1),
('02', 'قسم المشتريات', @cc_id, 2, 1);

-- وحدات القياس
INSERT INTO `units` (`name_ar`, `name_en`, `base_unit`, `factor`, `active`) VALUES
('حبة', 'Piece', 1, 1.0000, 1),
('باكت', 'Pack', 0, 12.0000, 1),
('كرتون', 'Carton', 0, 144.0000, 1),
('كيلو', 'Kilogram', 0, 1.0000, 1),
('لتر', 'Liter', 0, 1.0000, 1),
('متر', 'Meter', 0, 1.0000, 1);

-- مستويات التسعيرة
INSERT INTO `price_levels` (`level`, `name_ar`, `name_en`, `default_discount`, `active`) VALUES
(1, 'مستوى 1', 'Level 1', 0.00, 1),
(2, 'مستوى 2', 'Level 2', 5.00, 1),
(3, 'مستوى 3', 'Level 3', 10.00, 1),
(4, 'مستوى 4', 'Level 4', 15.00, 1);

-- أنواع التوريد/الصرف/التحويل
INSERT INTO `supply_types` (`code`, `name_ar`, `name_en`, `active`) VALUES
('ST-01', 'توريد مخزني', 'Stock Supply', 1),
('ST-02', 'توريد نقدي', 'Cash Supply', 1),
('ST-03', 'توريد آجل', 'Credit Supply', 1);

INSERT INTO `discharge_types` (`code`, `name_ar`, `name_en`, `active`) VALUES
('DT-01', 'صرف مخزني', 'Stock Discharge', 1),
('DT-02', 'صرف نقدي', 'Cash Discharge', 1),
('DT-03', 'صرف آجل', 'Credit Discharge', 1),
('DT-04', 'صرف كمية', 'Quantity Discharge', 1);

INSERT INTO `transfer_types` (`code`, `name_ar`, `name_en`, `active`) VALUES
('TT-01', 'تحويل مخزني', 'Stock Transfer', 1),
('TT-02', 'استلام مخزني', 'Stock Receipt', 1);

-- مجموعات الموردين والعملاء
INSERT INTO `supplier_groups` (`code`, `name_ar`, `active`) VALUES ('SG-00', 'موردون عام', 1);
INSERT INTO `customer_groups` (`code`, `name_ar`, `active`) VALUES ('CG-00', 'عملاء عام', 1);

-- السنة المالية 2024
INSERT INTO `fiscal_years` (`year_name`, `start_date`, `end_date`, `period_type`, `periods_count`, `active`, `closed`) VALUES
('2024', '2024-01-01', '2024-12-31', 'monthly', 12, 1, 0);
SET @fy_id = LAST_INSERT_ID();

INSERT INTO `fiscal_periods` (`fiscal_year_id`, `period_number`, `name`, `start_date`, `end_date`, `status`) VALUES
(@fy_id, 1, 'يناير 2024', '2024-01-01', '2024-01-31', 'open'),
(@fy_id, 2, 'فبراير 2024', '2024-02-01', '2024-02-29', 'open'),
(@fy_id, 3, 'مارس 2024', '2024-03-01', '2024-03-31', 'open'),
(@fy_id, 4, 'أبريل 2024', '2024-04-01', '2024-04-30', 'open'),
(@fy_id, 5, 'مايو 2024', '2024-05-01', '2024-05-31', 'open'),
(@fy_id, 6, 'يونيو 2024', '2024-06-01', '2024-06-30', 'open'),
(@fy_id, 7, 'يوليو 2024', '2024-07-01', '2024-07-31', 'open'),
(@fy_id, 8, 'أغسطس 2024', '2024-08-01', '2024-08-31', 'open'),
(@fy_id, 9, 'سبتمبر 2024', '2024-09-01', '2024-09-30', 'open'),
(@fy_id, 10, 'أكتوبر 2024', '2024-10-01', '2024-10-31', 'open'),
(@fy_id, 11, 'نوفمبر 2024', '2024-11-01', '2024-11-30', 'open'),
(@fy_id, 12, 'ديسمبر 2024', '2024-12-01', '2024-12-31', 'open');

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- ملاحظة: كلمة مرور المدير الافتراضية هي admin123
-- لتحديثها يدويًا بعد الاستيراد، نفّذ:
-- UPDATE users SET password_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' WHERE username = 'admin';
-- (الهاش أعلاه = password_hash('admin123', PASSWORD_DEFAULT))
-- ============================================================
