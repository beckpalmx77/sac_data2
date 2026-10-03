<?php
/**
 * Demo Script: Examples of calling MySQL Stored Procedures and Stored Functions in PHP
 * This file illustrates how to use the newly installed routines.
 */

// Show error messages
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 1. Establish database connection (fetches $conn PDO object)
include('config/connect_db.php');

echo "=== DEMO: CALLING STORED PROCEDURES & FUNCTIONS ===\n\n";

try {
    
    // =========================================================================
    // EXAMPLE 1: Calling a Stored Procedure with multiple parameters
    // Target: sp_generate_cockpit_sale_summary(p_month, p_year)
    // =========================================================================
    echo "1. Calling sp_generate_cockpit_sale_summary...\n";
    
    $month = '06';
    $year  = '2026';
    
    // Use CALL statement
    $sql_proc1 = "CALL sp_generate_cockpit_sale_summary(:month, :year)";
    $stmt1 = $conn->prepare($sql_proc1);
    $stmt1->bindParam(':month', $month, PDO::PARAM_STR);
    $stmt1->bindParam(':year', $year, PDO::PARAM_STR);
    
    $stmt1->execute();
    
    // Note: Stored Procedures can return result sets or run silently. 
    // This one runs updates/inserts internally, so we just verify execution.
    echo "Successfully executed cockpit sales summary for Month: $month, Year: $year.\n\n";
    
    // IMPORTANT: When calling stored procedures in PDO, you should close the active cursor
    // to allow subsequent queries to execute, especially if they have nested select blocks.
    $stmt1->closeCursor();


    // =========================================================================
    // EXAMPLE 2: Calling a Stored Procedure with a single parameter
    // Target: sp_sync_product_brand_monthly(p_year)
    // =========================================================================
    echo "2. Calling sp_sync_product_brand_monthly...\n";
    
    $sync_year = '2026';
    
    $sql_proc2 = "CALL sp_sync_product_brand_monthly(:year)";
    $stmt2 = $conn->prepare($sql_proc2);
    $stmt2->bindParam(':year', $sync_year, PDO::PARAM_STR);
    $stmt2->execute();
    
    echo "Successfully pivoted and synchronized brand sales monthly totals for Year: $sync_year.\n\n";
    $stmt2->closeCursor();


    // =========================================================================
    // EXAMPLE 3: Calling a Stored Function in a SELECT query
    // Target: fn_calculate_gift_voucher(total_qty, v_standard, v_tier1, v_tier2)
    // =========================================================================
    echo "3. Calling Stored Function fn_calculate_gift_voucher...\n";
    
    $total_qty  = 450; // Falls under Tier 1 (301-600)
    $v_standard = 10.00;
    $v_tier1    = 15.00;
    $v_tier2    = 20.00;
    
    // Stored Functions are used inside standard SELECT queries
    $sql_func1 = "SELECT fn_calculate_gift_voucher(:qty, :std, :t1, :t2) AS estimated_amount";
    $stmt3 = $conn->prepare($sql_func1);
    $stmt3->bindParam(':qty', $total_qty);
    $stmt3->bindParam(':std', $v_standard);
    $stmt3->bindParam(':t1', $v_tier1);
    $stmt3->bindParam(':t2', $v_tier2);
    
    $stmt3->execute();
    $result_voucher = $stmt3->fetch(PDO::FETCH_ASSOC);
    
    echo "Qty: $total_qty -> Estimated Gift Voucher: " . $result_voucher['estimated_amount'] . " Baht.\n\n";
    $stmt3->closeCursor();


    // =========================================================================
    // EXAMPLE 4: Generating sequential document ID using Stored Function
    // Target: fn_generate_stock_doc_id(record_type, doc_user_id, doc_date)
    // =========================================================================
    echo "4. Calling Stored Function fn_generate_stock_doc_id...\n";
    
    $record_type = '-'; // Outgoing transaction (Prefix: WO-)
    $user_id     = 'DM04';
    $doc_date    = date("d-m-Y");
    
    $sql_func2 = "SELECT fn_generate_stock_doc_id(:record_type, :user_id, :doc_date) AS doc_id";
    $stmt4 = $conn->prepare($sql_func2);
    $stmt4->bindParam(':record_type', $record_type, PDO::PARAM_STR);
    $stmt4->bindParam(':user_id', $user_id, PDO::PARAM_STR);
    $stmt4->bindParam(':doc_date', $doc_date, PDO::PARAM_STR);
    
    $stmt4->execute();
    $result_doc = $stmt4->fetch(PDO::FETCH_ASSOC);
    
    echo "Generated Document Code: " . $result_doc['doc_id'] . "\n\n";
    $stmt4->closeCursor();

} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
}
