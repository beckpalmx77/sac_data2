<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');


if ($_POST["action"] === 'GET_DATA') {

    $id = $_POST["id"];

    $return_arr = array();

    $sql_get = "SELECT * FROM m_take_target WHERE id = " . $id;
    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array("id" => $result['id'],
            "target_id" => $result['target_id'],
            "target_name" => $result['target_name'],
            "target_qty" => $result['target_qty'],
            "TAKE_NAME" => $result['TAKE_NAME'],
            "DI_YEAR" => $result['DI_YEAR'],
            "DI_MONTH" => $result['DI_MONTH'],
            "SKU_CAT" => $result['SKU_CAT'],
            "BRAND" => $result['BRAND'],
            "status" => $result['status']);
    }

    echo json_encode($return_arr);

}

if ($_POST["action"] === 'SEARCH') {

    if ($_POST["target_name"] !== '') {

        $target_name = $_POST["target_name"];
        $sql_find = "SELECT * FROM m_take_target WHERE target_name = '" . $target_name . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            echo 2;
        } else {
            echo 1;
        }
    }
}

if ($_POST["action"] === 'ADD') {
    if (!empty($_POST["target_qty"])) {

        // รับข้อมูลจาก $_POST
        $DI_YEAR = $_POST["DI_YEAR"];
        $DI_MONTH = $_POST["DI_MONTH"];
        $TAKE_NAME = $_POST["TAKE_NAME"];
        $SKU_CAT = $_POST["SKU_CAT"];
        $BRAND = $_POST["BRAND"];
        $target_qty = $_POST["target_qty"];
        $status = $_POST["status"];

        $TAKE_NAME = isset($_POST['TAKE_NAME']) ? $_POST['TAKE_NAME'] : [];

        // กำหนดค่า target_id
        $target_id = $SKU_CAT . "-" . $BRAND . "-" . $DI_YEAR . "-" . sprintf('%02s', $DI_MONTH) . "-" . $TAKE_NAME;
        /*
                $myfile = fopen("a-param.txt", "w") or die("Unable to open file!");
                fwrite($myfile, $DI_YEAR  . " | " . $DI_MONTH . " | " . $target_id );
                fclose($myfile);
        */
        // ตรวจสอบว่ามี target_name ซ้ำหรือไม่
        $sql_find = "SELECT COUNT(*) FROM m_take_target WHERE target_id = :target_id";
        $stmt = $conn->prepare($sql_find);
        $stmt->bindParam(':target_id', $target_id, PDO::PARAM_STR);
        $stmt->execute();
        $nRows = $stmt->fetchColumn();

        if ($nRows > 0) {
            echo $dup; // ข้อความแสดงเมื่อข้อมูลซ้ำ
        } else {
            // เตรียมคำสั่ง SQL สำหรับการ INSERT
            $sql = "INSERT INTO m_take_target (target_id,target_name,TAKE_NAME, DI_YEAR, DI_MONTH, SKU_CAT, BRAND, target_qty, status) 
                    VALUES (:target_id,:target_name, :TAKE_NAME,:DI_YEAR, :DI_MONTH, :SKU_CAT, :BRAND, :target_qty, :status)";
            $query = $conn->prepare($sql);

            // ผูกค่าตัวแปรกับฟิลด์ในฐานข้อมูล
            $query->bindParam(':target_id', $target_id, PDO::PARAM_STR);
            $query->bindParam(':target_name', $target_id, PDO::PARAM_STR);
            $query->bindParam(':TAKE_NAME', $TAKE_NAME, PDO::PARAM_STR);
            $query->bindParam(':DI_YEAR', $DI_YEAR, PDO::PARAM_STR);
            $query->bindParam(':DI_MONTH', $DI_MONTH, PDO::PARAM_STR);
            $query->bindParam(':SKU_CAT', $SKU_CAT, PDO::PARAM_STR);
            $query->bindParam(':BRAND', $BRAND, PDO::PARAM_STR);
            $query->bindParam(':target_qty', $target_qty, PDO::PARAM_STR);
            $query->bindParam(':status', $status, PDO::PARAM_STR);

            // ทำการบันทึกข้อมูลลงในฐานข้อมูล
            $query->execute();
            $lastInsertId = $conn->lastInsertId();

            // ตรวจสอบสถานะการบันทึกข้อมูล
            if ($lastInsertId) {
                echo $save_success; // ข้อความแสดงเมื่อบันทึกสำเร็จ
            } else {
                echo $error; // ข้อความแสดงเมื่อบันทึกล้มเหลว
            }
        }
    }
}

if ($_POST["action"] === 'UPDATE') {

    if (!empty($_POST["target_qty"])) {

        // รับข้อมูลจาก $_POST
        $id = $_POST["id"];
        $target_id = $_POST["target_id"];
        $DI_YEAR = $_POST["DI_YEAR"];
        $DI_MONTH = $_POST["DI_MONTH"];
        $TAKE_NAME = $_POST["TAKE_NAME"];
        $SKU_CAT = $_POST["SKU_CAT"];
        $BRAND = $_POST["BRAND"];
        $target_qty = $_POST["target_qty"];
        $status = $_POST["status"];

        $TAKE_NAME = isset($_POST['TAKE_NAME']) ? $_POST['TAKE_NAME'] : [];

        // ตรวจสอบว่ามี target_id ในฐานข้อมูลหรือไม่
        $sql_find = "SELECT COUNT(*) FROM m_take_target WHERE target_id = :target_id";
        $stmt = $conn->prepare($sql_find);
        $stmt->bindParam(':target_id', $target_id, PDO::PARAM_STR);
        $stmt->execute();
        $nRows = $stmt->fetchColumn();

        // ถ้าพบข้อมูลที่ตรงกับ target_id จะทำการอัปเดตข้อมูล //
        if ($nRows > 0) {
            $sql_update = "UPDATE m_take_target 
                           SET target_qty = :target_qty,TAKE_NAME = :TAKE_NAME,status = :status
                           WHERE id = :id";
            $query = $conn->prepare($sql_update);

            // ผูกค่าตัวแปรกับฟิลด์ในฐานข้อมูล
            $query->bindParam(':target_qty', $target_qty, PDO::PARAM_STR);
            $query->bindParam(':TAKE_NAME', $TAKE_NAME, PDO::PARAM_STR);
            $query->bindParam(':status', $status, PDO::PARAM_STR);
            $query->bindParam(':id', $id, PDO::PARAM_STR);

            // ทำการอัปเดตข้อมูลลงในฐานข้อมูล
            if ($query->execute()) {
                echo $save_success; // ข้อความแสดงเมื่อบันทึกสำเร็จ
            } else {
                echo $error; // ข้อความแสดงเมื่อบันทึกล้มเหลว
            }
        } else {
            echo "ไม่พบข้อมูลที่ต้องการอัปเดต"; // ข้อความแสดงเมื่อไม่พบข้อมูลที่ต้องการ
        }
    }
}


if ($_POST["action"] === 'DELETE') {

    $id = $_POST["id"];

    $sql_find = "SELECT * FROM m_take_target WHERE id = " . $id;
    $nRows = $conn->query($sql_find)->fetchColumn();
    if ($nRows > 0) {
        try {
            $sql = "DELETE FROM m_take_target WHERE id = " . $id;
            $query = $conn->prepare($sql);
            $query->execute();
            echo $del_success;
        } catch (Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}

if ($_POST["action"] === 'GET_TAKE_TARGET') {

    ## อ่านค่า input ที่เกี่ยวข้องกับการจัดการข้อมูล
    $draw = $_POST['draw'];
    $row = $_POST['start'];
    $rowperpage = $_POST['length']; // จำนวนแถวที่แสดงต่อหน้า
    $columnIndex = $_POST['order'][0]['column']; // ดัชนีของคอลัมน์
    $columnName = $_POST['columns'][$columnIndex]['data']; // ชื่อคอลัมน์
    $columnSortOrder = $_POST['order'][0]['dir']; // ลำดับการจัดเรียง (asc หรือ desc)
    $searchValue = $_POST['search']['value']; // ค่าค้นหา

    $searchArray = array();

    ## สร้าง query ค้นหาถ้ามีค่าค้นหา
    $searchQuery = "";
    if (!empty($searchValue)) {
        $searchQuery = " AND (target_id LIKE :target_id OR target_name LIKE :target_name OR TAKE_NAME LIKE :TAKE_NAME 
            OR SKU_CAT LIKE :SKU_CAT OR BRAND LIKE :BRAND 
            OR DI_YEAR LIKE :DI_YEAR OR DI_MONTH LIKE :DI_MONTH) ";
        $searchArray = array(
            ':target_id' => "%$searchValue%",
            ':target_name' => "%$searchValue%",
            ':TAKE_NAME' => "%$searchValue%",
            ':SKU_CAT' => "%$searchValue%",
            ':BRAND' => "%$searchValue%",
            ':DI_YEAR' => "%$searchValue%",
            ':DI_MONTH' => "%$searchValue%"
        );
    }

    ## จำนวนเรคอร์ดทั้งหมดก่อนการกรอง
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM m_take_target");
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

    ## จำนวนเรคอร์ดทั้งหมดหลังการกรอง
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM m_take_target WHERE 1 " . $searchQuery);
    $stmt->execute($searchArray);
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

    ## ดึงข้อมูล
    $stmt = $conn->prepare("SELECT * FROM m_take_target WHERE 1 " . $searchQuery
        . " ORDER BY " . $columnName . " " . $columnSortOrder . " LIMIT :offset, :limit");

    // ผูกค่าของตัวแปรค้นหา
    foreach ($searchArray as $key => $search) {
        $stmt->bindValue($key, $search, PDO::PARAM_STR);
    }

    // กำหนดค่า limit และ offset
    $stmt->bindValue(':offset', (int)$row, PDO::PARAM_INT);
    $stmt->bindValue(':limit', (int)$rowperpage, PDO::PARAM_INT);
    $stmt->execute();
    $empRecords = $stmt->fetchAll();

    $data = array();

    // สร้างข้อมูลเพื่อตอบกลับสำหรับแต่ละแถวที่ดึงมา
    foreach ($empRecords as $row) {
        if ($_POST['sub_action'] === "GET_MASTER") {
            $data[] = array(
                "id" => $row['id'],
                "target_id" => $row['target_id'],
                "target_name" => $row['target_name'],
                "DI_YEAR" => $row['DI_YEAR'],
                "DI_MONTH" => $row['DI_MONTH'],
                "TAKE_NAME" => $row['TAKE_NAME'],
                "SKU_CAT" => $row['SKU_CAT'],
                "BRAND" => $row['BRAND'],
                "target_qty" => $row['target_qty'],
                "target_money" => $row['target_money'],
                "target_percent" => $row['target_percent'],
                "target_effect" => $row['target_effect'],
                "update" => "<button type='button' name='update' id='" . $row['id'] . "' class='btn btn-info btn-xs update' data-toggle='tooltip' title='Update'>Update</button>",
                "delete" => "<button type='button' name='delete' id='" . $row['id'] . "' class='btn btn-danger btn-xs delete' data-toggle='tooltip' title='Delete'>Delete</button>",
                "status" => $row['status'] === 'Active' ? "<div class='text-success'>" . $row['status'] . "</div>" : "<div class='text-muted'> " . $row['status'] . "</div>"
            );
        } else {
            $data[] = array(
                "id" => $row['id'],
                "target_id" => $row['target_id'],
                "target_name" => $row['target_name'],
                "select" => "<button type='button' name='select' id='" . $row['target_id'] . "@" . $row['target_name'] . "' class='btn btn-outline-success btn-xs select' data-toggle='tooltip' title='Select'>select <i class='fa fa-check' aria-hidden='true'></i></button>"
            );
        }
    }

    ## สร้างผลลัพธ์ตอบกลับ
    $response = array(
        "draw" => intval($draw),
        "iTotalRecords" => $totalRecords,
        "iTotalDisplayRecords" => $totalRecordwithFilter,
        "aaData" => $data
    );

    echo json_encode($response);
}

