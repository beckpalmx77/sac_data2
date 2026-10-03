<?php

//fetch.php

include('database_connection.php');

$column = array('CustomerName', 'Gender', 'Address', 'City', 'PostalCode', 'Country');

$query = "SELECT * FROM tbl_customer ";

if(isset($_POST['search']['value']))
{
    $query .= '
 WHERE CustomerName LIKE "%'.$_POST['search']['value'].'%" 
 OR Gender LIKE "%'.$_POST['search']['value'].'%" 
 OR Address LIKE "%'.$_POST['search']['value'].'%" 
 OR City LIKE "%'.$_POST['search']['value'].'%" 
 OR PostalCode LIKE "%'.$_POST['search']['value'].'%" 
 OR Country LIKE "%'.$_POST['search']['value'].'%" 
 ';
}

if(isset($_POST['order']))
{
    $query .= 'ORDER BY '.$column[$_POST['order']['0']['column']].' '.$_POST['order']['0']['dir'].' ';
}
else
{
    $query .= 'ORDER BY CustomerID DESC ';
}

$query1 = '';

if($_POST['length'] != -1)
{
    $query1 = 'LIMIT ' . $_POST['start'] . ', ' . $_POST['length'];
}

$statement = $connect->prepare($query);

$statement->execute();

$number_filter_row = $statement->rowCount();

$statement = $connect->prepare($query . $query1);

$statement->execute();

$result = $statement->fetchAll();

$data = array();

foreach($result as $row)
{
    $sub_array = array();
    $sub_array[] = $row['CustomerName'];
    $sub_array[] = $row['Gender'];
    $sub_array[] = $row['Address'];
    $sub_array[] = $row['City'];
    $sub_array[] = $row['PostalCode'];
    $sub_array[] = $row['Country'];
    $data[] = $sub_array;
}

function count_all_data($connect)
{
    $query = "SELECT * FROM tbl_customer";
    $statement = $connect->prepare($query);
    $statement->execute();
    return $statement->rowCount();
}

$output = array(
    'draw'    => intval($_POST['draw']),
    'recordsTotal'  => count_all_data($connect),
    'recordsFiltered' => $number_filter_row,
    'data'    => $data
);

echo json_encode($output);

?>

