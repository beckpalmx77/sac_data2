<?php
include 'config/connect_db.php';
try {
    echo "Adding idx_di_ref to ims_document_bill...\n";
    $conn->exec("ALTER TABLE ims_document_bill ADD INDEX idx_di_ref (DI_REF)");
    echo "idx_di_ref added successfully.\n";
    
    echo "Adding idx_di_active to ims_document_bill...\n";
    $conn->exec("ALTER TABLE ims_document_bill ADD INDEX idx_di_active (DI_ACTIVE)");
    echo "idx_di_active added successfully.\n";
    
    echo "Adding idx_tpa_refer_ref to ims_document_bill_load...\n";
    $conn->exec("ALTER TABLE ims_document_bill_load ADD INDEX idx_tpa_refer_ref (TPA_REFER_REF)");
    echo "idx_tpa_refer_ref added successfully.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
