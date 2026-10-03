-- =========================================================================
-- SQL Migration Script: modi_struc.sql
-- Description: Safely adds indexes only if they do not already exist,
--              preventing "Duplicate key name" errors in MySQL.
-- Date: 2026-06-30
-- =========================================================================

DELIMITER //

-- Create temporary helper procedure to add index conditionally
DROP PROCEDURE IF EXISTS sp_add_index_if_not_exists //

CREATE PROCEDURE sp_add_index_if_not_exists(
    IN p_table_name VARCHAR(128),
    IN p_index_name VARCHAR(128),
    IN p_column_list VARCHAR(255)
)
BEGIN
    DECLARE v_exists INT;
    
    -- Check if index already exists on the table in the current database
    SELECT COUNT(*) INTO v_exists
    FROM information_schema.statistics
    WHERE table_schema = DATABASE()
      AND table_name = p_table_name
      AND index_name = p_index_name;
      
    -- If index does not exist, create it
    IF v_exists = 0 THEN
        SET @sql_cmd = CONCAT('ALTER TABLE ', p_table_name, ' ADD INDEX ', p_index_name, ' (', p_column_list, ')');
        PREPARE stmt FROM @sql_cmd;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
        SELECT CONCAT('Index ', p_index_name, ' added successfully to ', p_table_name) AS result;
    ELSE
        SELECT CONCAT('Index ', p_index_name, ' already exists on ', p_table_name) AS result;
    END IF;
END //

DELIMITER ;

-- -------------------------------------------------------------------------
-- Execute Conditional Index Creation
-- -------------------------------------------------------------------------

-- 1. Import Data Sale SAC Screen
CALL sp_add_index_if_not_exists('ims_data_sale_sac_all', 'idx_seq_record', 'seq_record');

-- 2. Dashboard Admin / Manage Product Sale SAC Screen
CALL sp_add_index_if_not_exists('ims_product_sale_sac', 'idx_di_date', 'DI_DATE');
CALL sp_add_index_if_not_exists('ims_product_sale_sac', 'idx_year_month', 'DI_YEAR, DI_MONTH');
CALL sp_add_index_if_not_exists('ims_product_sale_sac', 'idx_pgroup_year', 'PGROUP, DI_YEAR');
CALL sp_add_index_if_not_exists('ims_product_sale_sac', 'idx_di_ref', 'DI_REF');
CALL sp_add_index_if_not_exists('ims_product_sale_sac', 'idx_ar_code', 'AR_CODE');
CALL sp_add_index_if_not_exists('ims_product_sale_sac', 'idx_sku_code', 'SKU_CODE');

-- 3. Document Due Date Screen
CALL sp_add_index_if_not_exists('ims_document_bill', 'idx_di_ref', 'DI_REF');
CALL sp_add_index_if_not_exists('ims_document_bill', 'idx_di_active', 'DI_ACTIVE');
CALL sp_add_index_if_not_exists('ims_document_bill_load', 'idx_tpa_refer_ref', 'TPA_REFER_REF');

-- 4. Stock Movement Screen
CALL sp_add_index_if_not_exists('wh_product_master', 'idx_product_id', 'product_id');
CALL sp_add_index_if_not_exists('ims_user', 'idx_user_id', 'user_id');
CALL sp_add_index_if_not_exists('wh_stock_movement', 'idx_product_id', 'product_id');
CALL sp_add_index_if_not_exists('wh_stock_movement', 'idx_create_by', 'create_by');

-- -------------------------------------------------------------------------
-- Cleanup Helper Procedure
-- -------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS sp_add_index_if_not_exists;
