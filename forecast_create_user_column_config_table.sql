-- =================================================================
-- SQL Command Script: สร้างตาราง ims_user_column_config
-- วัตถุประสงค์: สำหรับเก็บบันทึกการตั้งค่า ซ่อน/แสดง คอลัมน์ DataTables ของผู้ใช้แต่ละคน (User Column Preferences)
-- ระบบ: Tires Plan Forecast System (SAC Data)
-- =================================================================

CREATE TABLE IF NOT EXISTS `ims_user_column_config` (
    `id` INT AUTO_INCREMENT PRIMARY KEY COMMENT 'รหัสลำดับรายการ (Primary Key)',
    `user_id` VARCHAR(100) NOT NULL COMMENT 'รหัสบัญชีผู้ใช้ หรือ ชื่อผู้ใช้ที่ล็อกอิน (alogin)',
    `page_name` VARCHAR(100) NOT NULL DEFAULT 'forecast_plan_display_realtime' COMMENT 'ชื่อหน้าจอระบบ',
    `column_settings` TEXT NOT NULL COMMENT 'ข้อมูลการตั้งค่าซ่อน/แสดงคอลัมน์ในรูปแบบ JSON',
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'วันเวลาที่อัปเดตข้อมูลล่าสุด',
    UNIQUE KEY `idx_user_page` (`user_id`, `page_name`),
    INDEX `idx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='ตารางเก็บบันทึกการตั้งค่าซ่อน/แสดง คอลัมน์ตารางของผู้ใช้งานรายบุคคล';
