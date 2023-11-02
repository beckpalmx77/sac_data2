<?php
session_start();
error_reporting(0);
include('../config/connect_db.php');
include('../config/lang.php');


if ($_SESSION['alogin'] != '') {
    $_SESSION['alogin'] = '';
}


$username = $_POST['username'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
$remember = $_POST['remember'];

$dashboard_page = "bill";

$sql = "SELECT *,pm.dashboard_page as dashboard_page,slt.SLT_CODE,slt.SLT_NAME FROM ims_user  
        left join ims_slteam slt  on slt.SLT_KEY = ims_user.manage_team_id         
        left join ims_permission pm on pm.permission_id = ims_user.account_type WHERE email=:username ";

$query = $conn->prepare($sql);
$query->bindParam(':username', $username, PDO::PARAM_STR);
$query->execute();
$results = $query->fetchAll(PDO::FETCH_OBJ);

if ($query->rowCount() == 1) {
    foreach ($results as $result) {
        if (password_verify($_POST['password'], $result->password)) {
            $_SESSION['alogin'] = $result->email;
            $_SESSION['user_id'] = $result->user_id;
            $_SESSION['login_id'] = $result->id;
            $_SESSION['username'] = $result->email;
            $_SESSION['first_name'] = $result->first_name;
            $_SESSION['last_name'] = $result->last_name;
            $_SESSION['email'] = $result->email;
            $_SESSION['account_type'] = $result->account_type;
            $_SESSION['user_picture'] = $result->picture;
            $_SESSION['lang'] = $result->lang;
            $_SESSION['permission_price'] = $result->permission_price;
            $_SESSION['company'] = $result->company;
            $_SESSION['SLT_CODE'] = $result->SLT_CODE;
            $_SESSION['SLT_NAME'] = $result->SLT_NAME;
            $_SESSION['manage_team_id'] = $result->manage_team_id;
            //$_SESSION['dashboard_page'] = $result->dashboard_page . ".php";
            //$_SESSION['dashboard_page'] = $result->dashboard_page;
            $_SESSION['dashboard_page'] = $dashboard_page;
            $_SESSION['system_name'] = $system_name;


            if ($remember == "on") { // ถ้าติ๊กถูก Login ตลอดไป ให้ทำการสร้าง cookie
                setcookie("username", $_POST["username"], time() + (86400 * 30), "/");
                setcookie("password", $_POST["password"], time() + (86400 * 30), "/");
                setcookie("remember_chk", "check", time() + (86400 * 30), "/");
            } else {
                setcookie("username", "");
                setcookie("password", "");
                setcookie("remember_chk", "");
            }
            //echo $result->dashboard_page . ".php";
            //echo $result->dashboard_page;
            echo $dashboard_page;

        } else {
            echo 0;
        }
    }
}
