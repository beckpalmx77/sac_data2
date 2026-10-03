<?php
include('config/connect_db.php');

try {
    echo "=== Optimization & Menu Registration ===\n";

    // 1. Add indexes to ims_data_sale_sac_all if not exists
    $indexes = [
        'idx_sale_name' => 'SALE_NAME',
        'idx_take_name' => 'TAKE_NAME',
        'idx_ar_name'   => 'AR_NAME(100)',
        'idx_di_year'   => 'DI_YEAR',
        'idx_di_month'  => 'DI_MONTH',
        'idx_sku_cat'   => 'SKU_CAT'
    ];

    foreach ($indexes as $idx_name => $col) {
        $stmt = $conn->query("SHOW INDEX FROM ims_data_sale_sac_all WHERE Key_name = '$idx_name'");
        if (!$stmt->fetch()) {
            $conn->exec("ALTER TABLE ims_data_sale_sac_all ADD INDEX $idx_name ($col)");
            echo "Added index '$idx_name' on ims_data_sale_sac_all.\n";
        } else {
            echo "Index '$idx_name' already exists.\n";
        }
    }

    // 2. Check menu_sub table
    $stmt_menu = $conn->query("SHOW TABLES LIKE 'menu_sub'");
    if ($stmt_menu->fetch()) {
        $check_sub = $conn->prepare("SELECT * FROM menu_sub WHERE link = 'display_top_store_sales'");
        $check_sub->execute();
        if (!$check_sub->fetch()) {
            // Find main menu for sales
            $stmt_main = $conn->query("SELECT main_menu_id FROM menu_main WHERE label LIKE '%ขาย%' OR label LIKE '%รายงาน%' LIMIT 1");
            $main_row = $stmt_main->fetch(PDO::FETCH_ASSOC);
            $main_menu_id = $main_row ? $main_row['main_menu_id'] : 'M004';

            // Generate sub_menu_id
            $stmt_max = $conn->query("SELECT MAX(id) as max_id FROM menu_sub");
            $max_id = (int)$stmt_max->fetchColumn() + 1;
            $sub_menu_id = 'S' . $max_id;

            $sql_ins = "INSERT INTO menu_sub (sub_menu_id, main_menu_id, label, label_en, link, target, icon, sort, privilege) 
                        VALUES (:sub_id, :main_id, 'ร้านค้าที่มียอดขายสูงสุด', 'Top Store Sales', 'display_top_store_sales', '', 'fa fa-trophy', :sort, 'User')";
            $stmt_ins = $conn->prepare($sql_ins);
            $stmt_ins->execute([
                ':sub_id' => $sub_menu_id,
                ':main_id' => $main_menu_id,
                ':sort' => $max_id
            ]);
            echo "Successfully added menu_sub entry for 'display_top_store_sales' (ID: $sub_menu_id).\n";

            // Update ims_permission to append $sub_menu_id and $main_menu_id for all roles
            $stmt_perm = $conn->query("SELECT id, sub_menu, main_menu FROM ims_permission");
            $perms = $stmt_perm->fetchAll(PDO::FETCH_ASSOC);
            foreach ($perms as $p) {
                $sub_arr = array_filter(explode(',', $p['sub_menu']));
                $main_arr = array_filter(explode(',', $p['main_menu']));
                if (!in_array($sub_menu_id, $sub_arr)) {
                    $sub_arr[] = $sub_menu_id;
                }
                if (!in_array($main_menu_id, $main_arr)) {
                    $main_arr[] = $main_menu_id;
                }
                $new_sub = implode(',', array_unique($sub_arr));
                $new_main = implode(',', array_unique($main_arr));
                $conn->exec("UPDATE ims_permission SET sub_menu = '$new_sub', main_menu = '$new_main' WHERE id = " . $p['id']);
            }
            echo "Updated permissions for all roles to include the new menu item.\n";
        } else {
            echo "Menu entry 'display_top_store_sales' already registered.\n";
        }
    }

    echo "=== Completed Successfully ===\n";

} catch (Exception $e) {
    echo "Notice/Error: " . $e->getMessage() . "\n";
}
