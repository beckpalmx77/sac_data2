<?php






$product_id_detail = "";
$sql_data = " SELECT SKU_CODE,SKU_NAME,WH_CODE,WH_NAME,WL_CODE,WL_NAME,sum(CAST(QTY AS DECIMAL(10,2))) as  QTY FROM v_stock_movement "
        . " WHERE SKU_CODE = '" . $product_id_detail . "'"
        . " GROUP BY SKU_CODE,SKU_NAME,WH_CODE,WH_NAME,WL_CODE,WL_NAME "
        . " HAVING sum(CAST(QTY AS DECIMAL(10,2))) > 0 ";
