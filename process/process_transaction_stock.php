<?php
session_start();
error_reporting(0);
date_default_timezone_set("Asia/Bangkok");
include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');


if ($_POST["action"] === 'GET_STOCK_PROCESS') {

        $sql_find = "SELECT * FROM ims_order_detail approve_status = 'Y'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {

        }

}

