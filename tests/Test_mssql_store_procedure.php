<?php
/* Connect to the local server using Windows Authentication and
specify the AdventureWorks database as the database in use. */
$serverName = "(local)";
$connectionInfo = array( "Database"=>"AdventureWorks");
$conn = sqlsrv_connect( $serverName, $connectionInfo);
if( $conn === false )
{
    echo "Could not connect.\n";
    die( print_r( sqlsrv_errors(), true));
}

/* Drop the stored procedure if it already exists. */
$tsql_dropSP = "IF OBJECT_ID('SubtractVacationHours', 'P') IS NOT NULL  
                DROP PROCEDURE SubtractVacationHours";
$stmt1 = sqlsrv_query( $conn, $tsql_dropSP);
if( $stmt1 === false )
{
    echo "Error in executing statement 1.\n";
    die( print_r( sqlsrv_errors(), true));
}

/* Create the stored procedure. */
$tsql_createSP = "CREATE PROCEDURE SubtractVacationHours  
                        @EmployeeID int,  
                        @VacationHrs smallint OUTPUT  
                  AS  
                  UPDATE HumanResources.Employee  
                  SET VacationHours = VacationHours - @VacationHrs  
                  WHERE EmployeeID = @EmployeeID;  
                  SET @VacationHrs = (SELECT VacationHours  
                                      FROM HumanResources.Employee  
                                      WHERE EmployeeID = @EmployeeID)";

$stmt2 = sqlsrv_query( $conn, $tsql_createSP);
if( $stmt2 === false )
{
    echo "Error in executing statement 2.\n";
    die( print_r( sqlsrv_errors(), true));
}




/*--------- The next few steps call the stored procedure. ---------*/

/* Define the Transact-SQL query. Use question marks (?) in place of
the parameters to be passed to the stored procedure */
$tsql_callSP = "{call SubtractVacationHours( ?, ?)}";

/* Define the parameter array. By default, the first parameter is an
INPUT parameter. The second parameter is specified as an INOUT
parameter. Initializing $vacationHrs to 8 sets the returned PHPTYPE to
integer. To ensure data type integrity, output parameters should be
initialized before calling the stored procedure, or the desired
PHPTYPE should be specified in the $params array.*/




$employeeId = 4;
$vacationHrs = 8;
$params = array(
    array($employeeId, SQLSRV_PARAM_IN),
    array(&$vacationHrs, SQLSRV_PARAM_INOUT)
);

/* Execute the query. */
$stmt3 = sqlsrv_query( $conn, $tsql_callSP, $params);
if( $stmt3 === false )
{
    echo "Error in executing statement 3.\n";
    die( print_r( sqlsrv_errors(), true));
}

/* Display the value of the output parameter $vacationHrs. */

sqlsrv_next_result($stmt3);
echo "Remaining vacation hours: ".$vacationHrs;

/*Free the statement and connection resources. */

sqlsrv_free_stmt( $stmt1);
sqlsrv_free_stmt( $stmt2);
sqlsrv_free_stmt( $stmt3);
sqlsrv_close( $conn);
?>

<?php
$serverName = "(local)";
$connectionInfo = array("Database"=>"testDB");
$conn = sqlsrv_connect($serverName, $connectionInfo);
if ($conn === false) {
    echo "Could not connect.\n";
    die(print_r(sqlsrv_errors(), true));
}

// Assume the stored procedure spTestProcedure exists, which retrieves a bigint value of some large number
// e.g. 9223372036854
$bigintOut = 0;
$outSql = "{CALL spTestProcedure (?)}";
$stmt = sqlsrv_prepare($conn, $outSql, array(array(&$bigintOut, SQLSRV_PARAM_INOUT, null, SQLSRV_SQLTYPE_BIGINT)));
sqlsrv_execute($stmt);
echo "$bigintOut\n";   // Expect 9223372036854

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);

?>
