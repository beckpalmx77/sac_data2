<?php
include '../config/connect_db.php';
include '../config/config_rabbit.inc';
include '../util/send_message.php';
include '../config/lang.php';

echo "Time in Bangkok\n";
$date2 = new DateTime();
$date2->setTimezone(new DateTimeZone('Asia/Bangkok'));
echo $date2->format(DateTime::RFC1123) . "\n";

$sql_read_data = "select ims_document_bill.* , b.DI_REF AS BILL_DI_REF , b.DI_DATE AS BILL_DI_DATE
, b.TPA_REFER_REF , b.TPA_REFER_DATE , b.ARD_BIL_DA  AS BILL_ARD_BIL_DA 
, b.ARD_DUE_DA AS BILL_ARD_DUE_DA
, b.ARD_A_SV , b.ARD_A_VAT  , b.ARD_A_AMT 
from ims_document_bill
left join ims_document_bill_load b on b.TPA_REFER_REF = ims_document_bill.DI_REF   
WHERE PAYMENT_STATUS = 'N' AND  SEND_ALERT_BILL <> 'Y' AND DATEDIFF(STR_TO_DATE(BILL_NOTE_DATE, '%d/%m/%Y'),NOW())  = " . $bill_alert_days;

echo $sql_read_data . "\n\r";

$stmt = $conn->prepare($sql_read_data);
$stmt->execute();
$bills = $stmt->fetchAll();

foreach ($bills as $bill) {

    $sql = " UPDATE ims_document_bill SET SEND_ALERT_BILL = 'Y' 
             WHERE id = :id ";
    $query = $conn->prepare($sql);
    $query->bindParam(':id', $bill["id"], PDO::PARAM_STR);
    $query->execute();

    $sToken = "UyToa0gKmU0BvMsh5nTIAjKIaooXxoUAO1CASWnbIES";
    $sMessage = "แจ้งเตือนการวางบิล เลขที่เอกสาร = " . $bill["DI_REF"]
        . "\n\r" . "ชื่อลูกค้า : " . $bill["AR_NAME"]
        . "\n\r" . "ผู้รับผิดชอบ : " . $bill["SLMN_NAME"]
        . "\n\r" . "ต้องวางบิลวันที่ี : " . $bill["BILL_NOTE_DATE"] ;

    echo $sMessage;
    sendLineNotify($sMessage, $sToken);

}


