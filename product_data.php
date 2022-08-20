<?php
header("Content-type:application/json; charset=UTF-8");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
// โค้ดไฟล์ dbconnect.php ดูได้ที่ http://niik.in/que_2398_5642

$mysqli = new mysqli("localhost", "ชื่อ user","รหัสผ่าน","ชื่อ database");
/* check connection */
if ($mysqli->connect_errno) {
    printf("Connect failed: %s\n", $mysqli->connect_error);
    exit();
}
if(!$mysqli->set_charset("utf8")) {
    printf("Error loading character set utf8: %s\n", $mysqli->error);
    exit();
}

$month=array(
    "Jan",
    "Feb",
    "Mar",
    "Apr",
    "May",
    "Jun",
    "Jul",
    "Aug",
    "Sep",
    "Oct",
    "Nov",
    "Dec"
);
// สร้างฟังก์ชั่น หายอดจำนวนที่ขายได้รวม ในแต่ละเดือน ของสินค้าใดๆ
// โดยจะสิ่งชื่อสินค้า และปี เข้าไปเพื่อตรวจสอบ และสร้างค่าตัวแปร array
// ชุดข้อมูล
function getData($chk,$val,$year){
    global $mysqli;
    $arr_data=array();
    // คำสั่ง sql เปลี่ยนไปตามความเหมาะสม ขึ้นกับว่าเป็นข้อมูลประเภทไหน
    // และต้องการใช้ข้อมูลในลักษณะใด ในที่นี้ เป็นการหายอดรวม ของสินค้า
    // แต่ละรายการ ในแต่ละเดือน ของปี ที่ส่งค่าตัวแปรมา

    if ($chk===1) {
        $where = " WHERE PGROUP = 'P1' AND BRN_NAME='" . $val . "'";
    } else {
        $where = " WHERE PGROUP = 'P1' AND BRN_NAME NOT IN ('BS','FS','DT','LLIT') ";
    }

    $sql = "
        SELECT 
        SUM(TRD_G_KEYIN) as TRD_G_KEYIN 
        FROM ims_product_sale_cockpit " . $where
        . "AND DI_YEAR LIKE '".$year."%'
        GROUP BY DI_MONTH,DI_YEAR 
    ";
    
    $result = $mysqli->query($sql);
    if($result && $result->num_rows > 0){
        while($row = $result->fetch_assoc()){
            $arr_data[] = $row['TRD_G_KEYIN'];
        }
    }
    return $arr_data;  // ส่งค่ากลับชุด array ข้อมูล
}

// สร้างชุด array ข้อมูลของสินค้า A ปี เป็นตัวแปร $_GET['year'] ที่เราส่งมาในที่นี้คือปี 2014
$col_A=getData(1,'BS',$_POST['year']); // สร้างชุด array ข้อมูลของสินค้า A
$col_B=getData(1,'FS',$_POST['year']); // สร้างชุด array ข้อมูลของสินค้า B
$col_C=getData(1,'DT',$_POST['year']); // สร้างชุด array ข้อมูลของสินค้า C
$col_D=getData(1,'LLIT',$_POST['year']); // สร้างชุด array ข้อมูลของสินค้า D
$col_E=getData(0,'E',$_POST['year']); // สร้างชุด array ข้อมูลของสินค้า E
// กำหนดตัวแปร $i ไว้อ้างอิง key ของชุดข้อมูล array
$i=0;
$sql = "
    SELECT 
    sale_id 
    FROM tbl_sale 
    GROUP BY DATE_FORMAT( DATE,  '%Y-%m-01' ) 
";
// การ query จะใช้ group by เดียวกัน เพื่อให้ key ของข้อมูล array ตรงและสัมพันธ์กัน
$result = $mysqli->query($sql);
if($result && $result->num_rows > 0){
    while($row = $result->fetch_assoc()){
        $json_data[]=array(
            $month[$i],  // สร้างข้อมูลแถวที่สองขึั้นไป คอลัมน์แรก อันนี้คือ เดือนย่อ
            intval($col_A[$i]),  // สร้างข้อมูลแถวที่สองขึั้นไป คอลัมน์ที่สอง ข้อมูลยอดรวมของ สินค้า A
            intval($col_B[$i]),  // สร้างข้อมูลแถวที่สองขึั้นไป คอลัมน์ที่สาม ข้อมูลยอดรวมของ สินค้า B
            intval($col_C[$i]),  // สร้างข้อมูลแถวที่สองขึั้นไป คอลัมน์ที่สาม ข้อมูลยอดรวมของ สินค้า C
            intval($col_D[$i]),  // สร้างข้อมูลแถวที่สองขึั้นไป คอลัมน์ที่สาม ข้อมูลยอดรวมของ สินค้า D
            intval($col_E[$i])  // สร้างข้อมูลแถวที่สองขึั้นไป คอลัมน์ที่สี่ ข้อมูลยอดรวมของ สินค้า E
        );
        $i++; // เพื่ม key ของตัวแปร arrray
    }
}
// ใส่ชุดข้อมูลแถวแรกเข้าไปในตัวแปร array
array_unshift($json_data,array("Month","BS","สินค้า B","สินค้า C"));
// แปลง array เป็นรูปแบบ json string
if(isset($json_data)){
    $json= json_encode($json_data);
    if(isset($_GET['callback']) && $_GET['callback']!=""){
        echo $_GET['callback']."(".$json.");";
    }else{
        echo $json;
    }
}


?>