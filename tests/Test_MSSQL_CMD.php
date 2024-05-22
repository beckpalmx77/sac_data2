<?php
//Connect MSSQL
$serverName = 'localhost';
$userName = 'sa';
$userPassword = 'sadmin';
$dbName = 'SAC';


try{
$conn = new PDO("sqlsrv:server=$serverName ; Database = $dbName", $userName, $userPassword);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch(Exception $e){
die(print_r($e->getMessage()));
}

$qry = "
DECLARE @TableName VARCHAR(255);
DECLARE @sql NVARCHAR(500);
DECLARE @fillfactor INT;
SET @fillfactor = 80
DECLARE TableCursor CURSOR FOR
SELECT OBJECT_SCHEMA_NAME([object_id])+'.'+name AS TableName
FROM sys.tables
OPEN TableCursor
FETCH NEXT FROM TableCursor INTO @TableName
WHILE @@FETCH_STATUS = 0
BEGIN
SET @sql = 'ALTER INDEX ALL ON ' + @TableName + ' REBUILD WITH (FILLFACTOR = ' + CONVERT(VARCHAR(3),@fillfactor) + ')'
EXEC (@sql)
FETCH NEXT FROM TableCursor INTO @TableName
END
CLOSE TableCursor;
DEALLOCATE TableCursor;

";

$getRes = $conn->prepare($qry);
$getRes->execute();



//การ query และแสดงข้อมูล จัดเรียงตามฟิวด์ field1 แบบมากไปน้อย เริ่มที่เรคคอร์ดที่ 0-100
/*
$query = " SELECT * FROM ADDRBOOK
ORDER BY ADDB_KEY DESC ";
$getRes = $conn->prepare($query);
$getRes->execute();

while($row = $getRes->fetch( PDO::FETCH_ASSOC ))
{
echo $row['ADDB_COMPANY']." | ";
echo $row['ADDB_PROVINCE']."\n\r";

}


USE SAC;
GO
ALTER DATABASE SAC
SET RECOVERY SIMPLE;
GO
 SELECT file_id, name FROM sys.database_files;
 GO
DBCC SHRINKFILE (2, TRUNCATEONLY);

*/