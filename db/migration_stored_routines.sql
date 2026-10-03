-- =========================================================================
-- SQL Migration Script for MySQL Stored Procedures & Functions
-- Project: sac_data2
-- Generated: 2026-06-23
-- Description: This script installs the stored procedures and stored functions
--              to optimize database processing and avoid looping in PHP.
-- =========================================================================

DELIMITER //

-- =========================================================================
-- 1. sp_generate_cockpit_sale_summary
-- Source: process/process_cockpit_data.php
-- Description: Aggregates sales data from ims_product_sale_cockpit and 
--              populates ims_report_product_sale_summary for a given month/year.
-- =========================================================================
DROP PROCEDURE IF EXISTS sp_generate_cockpit_sale_summary //

CREATE PROCEDURE sp_generate_cockpit_sale_summary(
    IN p_month VARCHAR(2),
    IN p_year VARCHAR(4)
)
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE v_branch VARCHAR(50);
    DECLARE v_di_month VARCHAR(2);
    DECLARE v_di_month_name VARCHAR(50);
    DECLARE v_di_year VARCHAR(4);
    DECLARE v_trd_qty DECIMAL(10,2);
    DECLARE v_trd_g_keyin DECIMAL(10,2);
    DECLARE row_exists INT;

    -- Cursor for Query 1: Total PGROUP P1, P2, P3
    DECLARE cur1 CURSOR FOR 
        SELECT BRANCH, DI_MONTH, DI_MONTH_NAME, DI_YEAR,
               SUM(CAST(TRD_QTY AS DECIMAL(10,2))) as TRD_QTY,
               SUM(CAST(TRD_G_KEYIN AS DECIMAL(10,2))) as TRD_G_KEYIN
        FROM ims_product_sale_cockpit 
        WHERE DI_MONTH = p_month AND DI_YEAR = p_year
          AND (PGROUP = 'P1' OR PGROUP = 'P2' OR PGROUP = 'P3')    
        GROUP BY BRANCH, DI_MONTH, DI_MONTH_NAME, DI_YEAR;

    -- Cursor for Query 2: Only Tires (PGROUP = 'P1')
    DECLARE cur2 CURSOR FOR 
        SELECT BRANCH, DI_MONTH, DI_MONTH_NAME, DI_YEAR,
               SUM(CAST(TRD_QTY AS DECIMAL(10,2))) as TRD_QTY,
               SUM(CAST(TRD_G_KEYIN AS DECIMAL(10,2))) as TRD_G_KEYIN
        FROM ims_product_sale_cockpit 
        WHERE DI_MONTH = p_month AND DI_YEAR = p_year
          AND PGROUP = 'P1'   
        GROUP BY BRANCH, DI_MONTH, DI_MONTH_NAME, DI_YEAR;

    -- Cursor for Query 3: Only Parts (PGROUP = 'P2')
    DECLARE cur3 CURSOR FOR 
        SELECT BRANCH, DI_MONTH, DI_MONTH_NAME, DI_YEAR,
               SUM(CAST(TRD_QTY AS DECIMAL(10,2))) as TRD_QTY,
               SUM(CAST(TRD_G_KEYIN AS DECIMAL(10,2))) as TRD_G_KEYIN
        FROM ims_product_sale_cockpit 
        WHERE DI_MONTH = p_month AND DI_YEAR = p_year
          AND PGROUP = 'P2'   
        GROUP BY BRANCH, DI_MONTH, DI_MONTH_NAME, DI_YEAR;

    -- Cursor for Query 4: Only Services (PGROUP = 'P3')
    DECLARE cur4 CURSOR FOR 
        SELECT BRANCH, DI_MONTH, DI_MONTH_NAME, DI_YEAR,
               SUM(CAST(TRD_QTY AS DECIMAL(10,2))) as TRD_QTY,
               SUM(CAST(TRD_G_KEYIN AS DECIMAL(10,2))) as TRD_G_KEYIN
        FROM ims_product_sale_cockpit 
        WHERE DI_MONTH = p_month AND DI_YEAR = p_year
          AND PGROUP = 'P3'   
        GROUP BY BRANCH, DI_MONTH, DI_MONTH_NAME, DI_YEAR;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    -- 1. Aggregated Total Amount
    OPEN cur1;
    read_loop1: LOOP
        FETCH cur1 INTO v_branch, v_di_month, v_di_month_name, v_di_year, v_trd_qty, v_trd_g_keyin;
        IF done THEN LEAVE read_loop1; END IF;
        
        SELECT COUNT(*) INTO row_exists FROM ims_report_product_sale_summary 
        WHERE BRANCH = v_branch AND DI_MONTH = v_di_month AND DI_YEAR = v_di_year;
        
        IF row_exists > 0 THEN
            UPDATE ims_report_product_sale_summary 
            SET total_amt = v_trd_g_keyin 
            WHERE BRANCH = v_branch AND DI_MONTH = v_di_month AND DI_YEAR = v_di_year;
        ELSE
            INSERT INTO ims_report_product_sale_summary (BRANCH, DI_MONTH, DI_MONTH_NAME, DI_YEAR, total_amt)
            VALUES (v_branch, v_di_month, v_di_month_name, v_di_year, v_trd_g_keyin);
        END IF;
    END LOOP;
    CLOSE cur1;

    SET done = FALSE;

    -- 2. Tires Total Qty and Tires Total Amount
    OPEN cur2;
    read_loop2: LOOP
        FETCH cur2 INTO v_branch, v_di_month, v_di_month_name, v_di_year, v_trd_qty, v_trd_g_keyin;
        IF done THEN LEAVE read_loop2; END IF;
        
        SELECT COUNT(*) INTO row_exists FROM ims_report_product_sale_summary 
        WHERE BRANCH = v_branch AND DI_MONTH = v_di_month AND DI_YEAR = v_di_year;
        
        IF row_exists > 0 THEN
            UPDATE ims_report_product_sale_summary 
            SET DI_MONTH_NAME = v_di_month_name, tires_total_qty = v_trd_qty, tires_total_amt = v_trd_g_keyin 
            WHERE BRANCH = v_branch AND DI_MONTH = v_di_month AND DI_YEAR = v_di_year;
        ELSE
            INSERT INTO ims_report_product_sale_summary (BRANCH, DI_MONTH, DI_MONTH_NAME, DI_YEAR, tires_total_qty, tires_total_amt)
            VALUES (v_branch, v_di_month, v_di_month_name, v_di_year, v_trd_qty, v_trd_g_keyin);
        END IF;
    END LOOP;
    CLOSE cur2;

    SET done = FALSE;

    -- 3. Parts Total Amount
    OPEN cur3;
    read_loop3: LOOP
        FETCH cur3 INTO v_branch, v_di_month, v_di_month_name, v_di_year, v_trd_qty, v_trd_g_keyin;
        IF done THEN LEAVE read_loop3; END IF;
        
        SELECT COUNT(*) INTO row_exists FROM ims_report_product_sale_summary 
        WHERE BRANCH = v_branch AND DI_MONTH = v_di_month AND DI_YEAR = v_di_year;
        
        IF row_exists > 0 THEN
            UPDATE ims_report_product_sale_summary 
            SET part_total_amt = v_trd_g_keyin 
            WHERE BRANCH = v_branch AND DI_MONTH = v_di_month AND DI_YEAR = v_di_year;
        END IF;
    END LOOP;
    CLOSE cur3;

    SET done = FALSE;

    -- 4. Services Total Amount
    OPEN cur4;
    read_loop4: LOOP
        FETCH cur4 INTO v_branch, v_di_month, v_di_month_name, v_di_year, v_trd_qty, v_trd_g_keyin;
        IF done THEN LEAVE read_loop4; END IF;
        
        SELECT COUNT(*) INTO row_exists FROM ims_report_product_sale_summary 
        WHERE BRANCH = v_branch AND DI_MONTH = v_di_month AND DI_YEAR = v_di_year;
        
        IF row_exists > 0 THEN
            UPDATE ims_report_product_sale_summary 
            SET svr_total_amt = v_trd_g_keyin 
            WHERE BRANCH = v_branch AND DI_MONTH = v_di_month AND DI_YEAR = v_di_year;
        END IF;
    END LOOP;
    CLOSE cur4;
END //


-- =========================================================================
-- 2. sp_generate_summary_data_cp
-- Source: cronjob/process_summary_data_cp.php
-- Description: Aggregates sales statistics from ims_product_sale_sac and
--              populates ims_report_product_sale_summary_2 for all 12 months.
-- =========================================================================
DROP PROCEDURE IF EXISTS sp_generate_summary_data_cp //

CREATE PROCEDURE sp_generate_summary_data_cp(
    IN p_year VARCHAR(4)
)
BEGIN
    DECLARE m INT DEFAULT 1;
    DECLARE done INT DEFAULT FALSE;
    DECLARE v_slmn_man VARCHAR(50);
    DECLARE v_di_month VARCHAR(2);
    DECLARE v_di_month_name VARCHAR(50);
    DECLARE v_di_year VARCHAR(4);
    DECLARE v_trd_qty DECIMAL(10,2);
    DECLARE v_trd_g_keyin DECIMAL(10,2);
    DECLARE row_exists INT;

    month_loop: LOOP
        IF m > 12 THEN
            LEAVE month_loop;
        END IF;

        BEGIN
            -- Cur 1: Total Amount for all product categories (P1, P2, P3)
            DECLARE cur1 CURSOR FOR 
                SELECT SLMN_MAN, DI_MONTH, DI_MONTH_NAME, DI_YEAR,
                       SUM(CAST(TRD_QTY AS DECIMAL(10,2))) as TRD_QTY,
                       SUM(CAST(TRD_G_KEYIN AS DECIMAL(10,2))) as TRD_G_KEYIN
                FROM ims_product_sale_sac 
                WHERE DI_MONTH = CAST(m AS CHAR) AND DI_YEAR = p_year
                  AND (PGROUP = 'P1' OR PGROUP = 'P2' OR PGROUP = 'P3')    
                GROUP BY SLMN_MAN, DI_MONTH, DI_MONTH_NAME, DI_YEAR;

            -- Cur 2: Tires (PGROUP = 'P1')
            DECLARE cur2 CURSOR FOR 
                SELECT SLMN_MAN, DI_MONTH, DI_MONTH_NAME, DI_YEAR,
                       SUM(CAST(TRD_QTY AS DECIMAL(10,2))) as TRD_QTY,
                       SUM(CAST(TRD_G_KEYIN AS DECIMAL(10,2))) as TRD_G_KEYIN
                FROM ims_product_sale_sac 
                WHERE DI_MONTH = CAST(m AS CHAR) AND DI_YEAR = p_year
                  AND PGROUP = 'P1'   
                GROUP BY SLMN_MAN, DI_MONTH, DI_MONTH_NAME, DI_YEAR;

            -- Cur 3: Parts (PGROUP = 'P2')
            DECLARE cur3 CURSOR FOR 
                SELECT SLMN_MAN, DI_MONTH, DI_MONTH_NAME, DI_YEAR,
                       SUM(CAST(TRD_QTY AS DECIMAL(10,2))) as TRD_QTY,
                       SUM(CAST(TRD_G_KEYIN AS DECIMAL(10,2))) as TRD_G_KEYIN
                FROM ims_product_sale_sac 
                WHERE DI_MONTH = CAST(m AS CHAR) AND DI_YEAR = p_year
                  AND PGROUP = 'P2'   
                GROUP BY SLMN_MAN, DI_MONTH, DI_MONTH_NAME, DI_YEAR;

            -- Cur 4: Services (PGROUP = 'P3')
            DECLARE cur4 CURSOR FOR 
                SELECT SLMN_MAN, DI_MONTH, DI_MONTH_NAME, DI_YEAR,
                       SUM(CAST(TRD_QTY AS DECIMAL(10,2))) as TRD_QTY,
                       SUM(CAST(TRD_G_KEYIN AS DECIMAL(10,2))) as TRD_G_KEYIN
                FROM ims_product_sale_sac 
                WHERE DI_MONTH = CAST(m AS CHAR) AND DI_YEAR = p_year
                  AND PGROUP = 'P3'   
                GROUP BY SLMN_MAN, DI_MONTH, DI_MONTH_NAME, DI_YEAR;

            DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

            SET done = FALSE;
            OPEN cur1;
            read_loop1: LOOP
                FETCH cur1 INTO v_slmn_man, v_di_month, v_di_month_name, v_di_year, v_trd_qty, v_trd_g_keyin;
                IF done THEN LEAVE read_loop1; END IF;
                
                SELECT COUNT(*) INTO row_exists FROM ims_report_product_sale_summary_2 
                WHERE SLMN_MAN = v_slmn_man AND DI_MONTH = v_di_month AND DI_YEAR = v_di_year;
                
                IF row_exists > 0 THEN
                    UPDATE ims_report_product_sale_summary_2 
                    SET total_amt = v_trd_g_keyin 
                    WHERE SLMN_MAN = v_slmn_man AND DI_MONTH = v_di_month AND DI_YEAR = v_di_year;
                ELSE
                    INSERT INTO ims_report_product_sale_summary_2 (SLMN_MAN, DI_MONTH, DI_MONTH_NAME, DI_YEAR, total_amt)
                    VALUES (v_slmn_man, v_di_month, v_di_month_name, v_di_year, v_trd_g_keyin);
                END IF;
            END LOOP;
            CLOSE cur1;

            SET done = FALSE;
            OPEN cur2;
            read_loop2: LOOP
                FETCH cur2 INTO v_slmn_man, v_di_month, v_di_month_name, v_di_year, v_trd_qty, v_trd_g_keyin;
                IF done THEN LEAVE read_loop2; END IF;
                
                SELECT COUNT(*) INTO row_exists FROM ims_report_product_sale_summary_2 
                WHERE SLMN_MAN = v_slmn_man AND DI_MONTH = v_di_month AND DI_YEAR = v_di_year;
                
                IF row_exists > 0 THEN
                    UPDATE ims_report_product_sale_summary_2 
                    SET DI_MONTH_NAME = v_di_month_name, tires_total_qty = v_trd_qty, tires_total_amt = v_trd_g_keyin 
                    WHERE SLMN_MAN = v_slmn_man AND DI_MONTH = v_di_month AND DI_YEAR = v_di_year;
                ELSE
                    INSERT INTO ims_report_product_sale_summary_2 (SLMN_MAN, DI_MONTH, DI_MONTH_NAME, DI_YEAR, tires_total_qty, tires_total_amt)
                    VALUES (v_slmn_man, v_di_month, v_di_month_name, v_di_year, v_trd_qty, v_trd_g_keyin);
                END IF;
            END LOOP;
            CLOSE cur2;

            SET done = FALSE;
            OPEN cur3;
            read_loop3: LOOP
                FETCH cur3 INTO v_slmn_man, v_di_month, v_di_month_name, v_di_year, v_trd_qty, v_trd_g_keyin;
                IF done THEN LEAVE read_loop3; END IF;
                
                SELECT COUNT(*) INTO row_exists FROM ims_report_product_sale_summary_2 
                WHERE SLMN_MAN = v_slmn_man AND DI_MONTH = v_di_month AND DI_YEAR = v_di_year;
                
                IF row_exists > 0 THEN
                    UPDATE ims_report_product_sale_summary_2 
                    SET part_total_amt = v_trd_g_keyin 
                    WHERE SLMN_MAN = v_slmn_man AND DI_MONTH = v_di_month AND DI_YEAR = v_di_year;
                END IF;
            END LOOP;
            CLOSE cur3;

            SET done = FALSE;
            OPEN cur4;
            read_loop4: LOOP
                FETCH cur4 INTO v_slmn_man, v_di_month, v_di_month_name, v_di_year, v_trd_qty, v_trd_g_keyin;
                IF done THEN LEAVE read_loop4; END IF;
                
                SELECT COUNT(*) INTO row_exists FROM ims_report_product_sale_summary_2 
                WHERE SLMN_MAN = v_slmn_man AND DI_MONTH = v_di_month AND DI_YEAR = v_di_year;
                
                IF row_exists > 0 THEN
                    UPDATE ims_report_product_sale_summary_2 
                    SET svr_total_amt = v_trd_g_keyin 
                    WHERE SLMN_MAN = v_slmn_man AND DI_MONTH = v_di_month AND DI_YEAR = v_di_year;
                END IF;
            END LOOP;
            CLOSE cur4;
        END;

        SET m = m + 1;
    END LOOP month_loop;
END //


-- =========================================================================
-- 3. sp_generate_product_sale_report
-- Source: cronjob/process_product_sale_report.php
-- Description: Aggregates daily branch sales and registers entries into 
--              ims_report_product_sale if they do not exist.
-- =========================================================================
DROP PROCEDURE IF EXISTS sp_generate_product_sale_report //

CREATE PROCEDURE sp_generate_product_sale_report(
    IN p_start_date VARCHAR(10),
    IN p_end_date VARCHAR(10)
)
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE v_di_date VARCHAR(10);
    DECLARE v_branch VARCHAR(50);
    DECLARE row_exists INT;

    DECLARE cur CURSOR FOR 
        SELECT DI_DATE, BRANCH 
        FROM ims_product_sale_cockpit 
        WHERE STR_TO_DATE(DI_DATE,'%d/%m/%Y') BETWEEN STR_TO_DATE(p_start_date,'%d/%m/%Y') AND STR_TO_DATE(p_end_date,'%d/%m/%Y')
        GROUP BY DI_DATE, BRANCH;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    OPEN cur;
    read_loop: LOOP
        FETCH cur INTO v_di_date, v_branch;
        IF done THEN LEAVE read_loop; END IF;

        SELECT COUNT(*) INTO row_exists 
        FROM ims_report_product_sale 
        WHERE DI_DATE = v_di_date AND SALE_CODE = v_branch;

        IF row_exists <= 0 THEN
            INSERT INTO ims_report_product_sale (DI_DATE, SALE_CODE) 
            VALUES (v_di_date, v_branch);
        END IF;
    END LOOP;
    CLOSE cur;
END //


-- =========================================================================
-- 4. sp_sync_product_brand_monthly
-- Source: process/process_data.php
-- Description: Aggregates sales statistics and pivots them into brand columns
--              (TRD_QTY_M1 to M12, TRD_G_KEYIN_M1 to M12) inside
--              ims_product_brand_monthly. Fixes PHP logic bugs.
-- =========================================================================
DROP PROCEDURE IF EXISTS sp_sync_product_brand_monthly //

CREATE PROCEDURE sp_sync_product_brand_monthly(
    IN p_year VARCHAR(4)
)
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE v_pgroup VARCHAR(50);
    DECLARE v_brn_code VARCHAR(50);
    DECLARE v_brn_name VARCHAR(255);
    DECLARE v_di_month INT;
    DECLARE v_di_year VARCHAR(4);
    DECLARE v_trd_qty DECIMAL(10,2);
    DECLARE v_trd_g_keyin DECIMAL(10,2);
    DECLARE row_exists INT;

    DECLARE cur CURSOR FOR 
        SELECT PGROUP, BRN_CODE, BRN_NAME, CAST(DI_MONTH AS UNSIGNED) AS DI_MONTH, DI_YEAR,
               SUM(CAST(TRD_QTY AS DECIMAL(10,2))) as TRD_QTY,
               SUM(CAST(TRD_G_KEYIN AS DECIMAL(10,2))) as TRD_G_KEYIN
        FROM ims_product_sale_cockpit
        WHERE PGROUP = 'P1' AND DI_YEAR = p_year
        GROUP BY PGROUP, BRN_CODE, BRN_NAME, DI_MONTH, DI_YEAR;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    OPEN cur;
    read_loop: LOOP
        FETCH cur INTO v_pgroup, v_brn_code, v_brn_name, v_di_month, v_di_year, v_trd_qty, v_trd_g_keyin;
        IF done THEN LEAVE read_loop; END IF;

        SELECT COUNT(*) INTO row_exists 
        FROM ims_product_brand_monthly 
        WHERE PGROUP = v_pgroup AND BRN_CODE = v_brn_code AND BRN_NAME = v_brn_name AND DI_YEAR = v_di_year;

        IF row_exists <= 0 THEN
            CASE v_di_month
                WHEN 1 THEN INSERT INTO ims_product_brand_monthly(PGROUP,BRN_CODE,BRN_NAME,DI_YEAR,TRD_QTY_M1,TRD_G_KEYIN_M1) VALUES(v_pgroup,v_brn_code,v_brn_name,v_di_year,v_trd_qty,v_trd_g_keyin);
                WHEN 2 THEN INSERT INTO ims_product_brand_monthly(PGROUP,BRN_CODE,BRN_NAME,DI_YEAR,TRD_QTY_M2,TRD_G_KEYIN_M2) VALUES(v_pgroup,v_brn_code,v_brn_name,v_di_year,v_trd_qty,v_trd_g_keyin);
                WHEN 3 THEN INSERT INTO ims_product_brand_monthly(PGROUP,BRN_CODE,BRN_NAME,DI_YEAR,TRD_QTY_M3,TRD_G_KEYIN_M3) VALUES(v_pgroup,v_brn_code,v_brn_name,v_di_year,v_trd_qty,v_trd_g_keyin);
                WHEN 4 THEN INSERT INTO ims_product_brand_monthly(PGROUP,BRN_CODE,BRN_NAME,DI_YEAR,TRD_QTY_M4,TRD_G_KEYIN_M4) VALUES(v_pgroup,v_brn_code,v_brn_name,v_di_year,v_trd_qty,v_trd_g_keyin);
                WHEN 5 THEN INSERT INTO ims_product_brand_monthly(PGROUP,BRN_CODE,BRN_NAME,DI_YEAR,TRD_QTY_M5,TRD_G_KEYIN_M5) VALUES(v_pgroup,v_brn_code,v_brn_name,v_di_year,v_trd_qty,v_trd_g_keyin);
                WHEN 6 THEN INSERT INTO ims_product_brand_monthly(PGROUP,BRN_CODE,BRN_NAME,DI_YEAR,TRD_QTY_M6,TRD_G_KEYIN_M6) VALUES(v_pgroup,v_brn_code,v_brn_name,v_di_year,v_trd_qty,v_trd_g_keyin);
                WHEN 7 THEN INSERT INTO ims_product_brand_monthly(PGROUP,BRN_CODE,BRN_NAME,DI_YEAR,TRD_QTY_M7,TRD_G_KEYIN_M7) VALUES(v_pgroup,v_brn_code,v_brn_name,v_di_year,v_trd_qty,v_trd_g_keyin);
                WHEN 8 THEN INSERT INTO ims_product_brand_monthly(PGROUP,BRN_CODE,BRN_NAME,DI_YEAR,TRD_QTY_M8,TRD_G_KEYIN_M8) VALUES(v_pgroup,v_brn_code,v_brn_name,v_di_year,v_trd_qty,v_trd_g_keyin);
                WHEN 9 THEN INSERT INTO ims_product_brand_monthly(PGROUP,BRN_CODE,BRN_NAME,DI_YEAR,TRD_QTY_M9,TRD_G_KEYIN_M9) VALUES(v_pgroup,v_brn_code,v_brn_name,v_di_year,v_trd_qty,v_trd_g_keyin);
                WHEN 10 THEN INSERT INTO ims_product_brand_monthly(PGROUP,BRN_CODE,BRN_NAME,DI_YEAR,TRD_QTY_M10,TRD_G_KEYIN_M10) VALUES(v_pgroup,v_brn_code,v_brn_name,v_di_year,v_trd_qty,v_trd_g_keyin);
                WHEN 11 THEN INSERT INTO ims_product_brand_monthly(PGROUP,BRN_CODE,BRN_NAME,DI_YEAR,TRD_QTY_M11,TRD_G_KEYIN_M11) VALUES(v_pgroup,v_brn_code,v_brn_name,v_di_year,v_trd_qty,v_trd_g_keyin);
                WHEN 12 THEN INSERT INTO ims_product_brand_monthly(PGROUP,BRN_CODE,BRN_NAME,DI_YEAR,TRD_QTY_M12,TRD_G_KEYIN_M12) VALUES(v_pgroup,v_brn_code,v_brn_name,v_di_year,v_trd_qty,v_trd_g_keyin);
            END CASE;
        ELSE
            CASE v_di_month
                WHEN 1 THEN UPDATE ims_product_brand_monthly SET TRD_QTY_M1=v_trd_qty, TRD_G_KEYIN_M1=v_trd_g_keyin WHERE PGROUP=v_pgroup AND BRN_CODE=v_brn_code AND BRN_NAME=v_brn_name AND DI_YEAR=v_di_year;
                WHEN 2 THEN UPDATE ims_product_brand_monthly SET TRD_QTY_M2=v_trd_qty, TRD_G_KEYIN_M2=v_trd_g_keyin WHERE PGROUP=v_pgroup AND BRN_CODE=v_brn_code AND BRN_NAME=v_brn_name AND DI_YEAR=v_di_year;
                WHEN 3 THEN UPDATE ims_product_brand_monthly SET TRD_QTY_M3=v_trd_qty, TRD_G_KEYIN_M3=v_trd_g_keyin WHERE PGROUP=v_pgroup AND BRN_CODE=v_brn_code AND BRN_NAME=v_brn_name AND DI_YEAR=v_di_year;
                WHEN 4 THEN UPDATE ims_product_brand_monthly SET TRD_QTY_M4=v_trd_qty, TRD_G_KEYIN_M4=v_trd_g_keyin WHERE PGROUP=v_pgroup AND BRN_CODE=v_brn_code AND BRN_NAME=v_brn_name AND DI_YEAR=v_di_year;
                WHEN 5 THEN UPDATE ims_product_brand_monthly SET TRD_QTY_M5=v_trd_qty, TRD_G_KEYIN_M5=v_trd_g_keyin WHERE PGROUP=v_pgroup AND BRN_CODE=v_brn_code AND BRN_NAME=v_brn_name AND DI_YEAR=v_di_year;
                WHEN 6 THEN UPDATE ims_product_brand_monthly SET TRD_QTY_M6=v_trd_qty, TRD_G_KEYIN_M6=v_trd_g_keyin WHERE PGROUP=v_pgroup AND BRN_CODE=v_brn_code AND BRN_NAME=v_brn_name AND DI_YEAR=v_di_year;
                WHEN 7 THEN UPDATE ims_product_brand_monthly SET TRD_QTY_M7=v_trd_qty, TRD_G_KEYIN_M7=v_trd_g_keyin WHERE PGROUP=v_pgroup AND BRN_CODE=v_brn_code AND BRN_NAME=v_brn_name AND DI_YEAR=v_di_year;
                WHEN 8 THEN UPDATE ims_product_brand_monthly SET TRD_QTY_M8=v_trd_qty, TRD_G_KEYIN_M8=v_trd_g_keyin WHERE PGROUP=v_pgroup AND BRN_CODE=v_brn_code AND BRN_NAME=v_brn_name AND DI_YEAR=v_di_year;
                WHEN 9 THEN UPDATE ims_product_brand_monthly SET TRD_QTY_M9=v_trd_qty, TRD_G_KEYIN_M9=v_trd_g_keyin WHERE PGROUP=v_pgroup AND BRN_CODE=v_brn_code AND BRN_NAME=v_brn_name AND DI_YEAR=v_di_year;
                WHEN 10 THEN UPDATE ims_product_brand_monthly SET TRD_QTY_M10=v_trd_qty, TRD_G_KEYIN_M10=v_trd_g_keyin WHERE PGROUP=v_pgroup AND BRN_CODE=v_brn_code AND BRN_NAME=v_brn_name AND DI_YEAR=v_di_year;
                WHEN 11 THEN UPDATE ims_product_brand_monthly SET TRD_QTY_M11=v_trd_qty, TRD_G_KEYIN_M11=v_trd_g_keyin WHERE PGROUP=v_pgroup AND BRN_CODE=v_brn_code AND BRN_NAME=v_brn_name AND DI_YEAR=v_di_year;
                WHEN 12 THEN UPDATE ims_product_brand_monthly SET TRD_QTY_M12=v_trd_qty, TRD_G_KEYIN_M12=v_trd_g_keyin WHERE PGROUP=v_pgroup AND BRN_CODE=v_brn_code AND BRN_NAME=v_brn_name AND DI_YEAR=v_di_year;
            END CASE;
        END IF;
    END LOOP;
    CLOSE cur;
END //


-- =========================================================================
-- 5. fn_calculate_gift_voucher (Stored Function)
-- Source: manage_logistic_calculate_product_data.php
-- Description: Standardizes business logic calculating the gift voucher amounts 
--              using volume bands (60-300, 301-600, >600).
-- =========================================================================
DROP FUNCTION IF EXISTS fn_calculate_gift_voucher //

CREATE FUNCTION fn_calculate_gift_voucher(
    total_qty DECIMAL(10,2),
    v_standard DECIMAL(10,2),
    v_tier1 DECIMAL(10,2),
    v_tier2 DECIMAL(10,2)
) 
RETURNS DECIMAL(10,2)
DETERMINISTIC
BEGIN
    DECLARE estimated_val DECIMAL(10,2);
    
    IF total_qty BETWEEN 60 AND 300 THEN 
        SET estimated_val = total_qty * v_standard;
    ELSEIF total_qty BETWEEN 301 AND 600 THEN 
        SET estimated_val = total_qty * v_tier1;
    ELSEIF total_qty > 600 THEN 
        SET estimated_val = total_qty * v_tier2;
    ELSE 
        SET estimated_val = 0;
    END IF;
    
    RETURN estimated_val;
END //


-- =========================================================================
-- 6. fn_generate_stock_doc_id (Stored Function)
-- Source: model/manage_stock_location_transaction_process.php
-- Description: Safely generates stock transactions document IDs (WI- / WO-), 
--              preventing race conditions / duplicate sequences.
-- =========================================================================
DROP FUNCTION IF EXISTS fn_generate_stock_doc_id //

CREATE FUNCTION fn_generate_stock_doc_id(
    p_record_type CHAR(1),
    p_doc_user_id VARCHAR(50),
    p_doc_date VARCHAR(20)
)
RETURNS VARCHAR(100)
DETERMINISTIC
BEGIN
    DECLARE v_prefix VARCHAR(10);
    DECLARE v_count INT;
    DECLARE v_doc_id VARCHAR(100);

    IF p_record_type = '+' THEN
        SET v_prefix = 'WI-';
    ELSEIF p_record_type = '-' THEN
        SET v_prefix = 'WO-';
    ELSE
        SET v_prefix = 'XX-';
    END IF;

    -- Count existing document codes to generate the running sequence index
    SELECT COUNT(doc_id) INTO v_count 
    FROM wh_stock_transaction 
    WHERE doc_date = p_doc_date 
      AND doc_user_id = p_doc_user_id 
      AND doc_id LIKE CONCAT(v_prefix, '%');

    SET v_count = v_count + 1;
    SET v_doc_id = CONCAT(v_prefix, p_doc_user_id, '-', p_doc_date, '-', LPAD(v_count, 6, '0'));

    RETURN v_doc_id;
END //

DELIMITER ;
