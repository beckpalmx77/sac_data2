<!DOCTYPE html>
<html lang="th">

<?php
include('../includes/Header.php');
?>

<style>
    body {
        font-family: 'Prompt', sans-serif;
    }
</style>

<style type="text/css">
    .toggleeye {
        float: right;
        margin-right: 6px;
        margin-top: -20px;
        position: relative;
        z-index: 2;
        color: darkgrey;
    }
</style>


<script>

    $(document).ready(function () {
        let username = '<?php if (isset($_COOKIE["username"])) {
            echo $_COOKIE["username"];
        } ?>';
        let password = '<?php if (isset($_COOKIE["password"])) {
            echo $_COOKIE["password"];
        } ?>';
        let remember_chk = '<?php echo $_COOKIE["remember_chk"]?>';

        $("#username").val(username);
        $("#password").val(password);

        if (remember_chk === "check") {
            $("#remember").prop('checked', true); // Checked
        }

    });

</script>

<script>
    $(document).ready(function () {
        $("button").click(function () {
            let username = $("#username").val();
            let password = $("#password").val();
            let remember = "";

            if ($("#remember").prop("checked")) {
                remember = $("#remember").val();
            }

            if (username != "" && password != "") {
                $.ajax
                ({
                    type: 'post',
                    url: 'login_process.php',
                    data: {
                        username: username,
                        password: password,
                        remember: remember,
                    },
                    success: function (response) {
                        if (response !== "0") {
                            window.location.href = response;
                        } else {
                            alert("เข้าระบบไม่ถูกต้อง กรุณาลองใหม่อีกครั้ง");
                            window.location.href = "login.php";
                        }
                    }
                });
            } else {
                alert("Please Fill All The Details");
            }

            return false;
        });
    });

</script>


<script type='text/javascript'>
    $(document).ready(function () {
        $('#togglePassword').click(function () {
            //alert($(this).is(':checked'));
            $('#password').attr('type') === 'password' ? $('#password').attr('type', 'text') : $('#password').attr('type', 'password');
        });
    });
</script>


<body class="bg-gradient-login">


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="author" content="สงวนออโต้คาร์ | SANGUAN AUTO CAR">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="description" content="สงวนออโต้คาร์ | SANGUAN AUTO CAR">
    <link rel="icon" href="img/favicon.ico" type="image/x-icon">
    <link href="img/logo/Logo-01.png" rel="icon">
    <title>สงวนออโต้คาร์ | SANGUAN AUTO CAR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BmbxuPwQa2lc/FVzBcNJ7UAyJxM6wuqIj61tLrc4wSX0szH/Ev+nYRRuWlolflfl" crossorigin="anonymous">
</head>

<body>
<section class="h-100">
    <div class="container h-100">
        <div class="row justify-content-sm-center h-100">
            <div class="col-xxl-4 col-xl-5 col-lg-5 col-md-7 col-sm-9">
                <div class="text-center my-5">
                    <img src="img/logo/logo text-01.png" alt="logo" width="300">
                </div>
                <div class="card shadow-lg">
                    <div class="card-body p-5">
                        <h1 class="fs-4 card-title fw-bold mb-4">Login</h1>
                        <form method="POST" class="needs-validation" novalidate="" autocomplete="off">
                            <div class="mb-3">
                                <!--label class="mb-2 text-muted" for="email">E-Mail Address</label-->
                                <input id="username" type="text" class="form-control" name="username" value="" required autofocus>
                                <div class="invalid-feedback">
                                    Email is invalid
                                </div>
                            </div>

                            <div class="mb-3">
                                <!--div class="mb-2 w-100">
                                    <label class="text-muted" for="password">Password</label>
                                </div-->
                                <input id="password" type="password" class="form-control" name="password" required>
                                <div class="invalid-feedback">
                                    Password is required
                                </div>
                            </div>

                            <div class="d-flex align-items-center">
                                <div class="form-check">
                                    <!--input type="checkbox" name="remember" id="remember" class="form-check-input">
                                    <label for="remember" class="form-check-label">Remember Me</label-->
                                </div>
                                <button type="submit" class="btn btn-primary ms-auto">
                                    Login
                                </button>
                            </div>
                        </form>
                    </div>

                </div>
                <div class="text-center mt-5 text-muted">
                    Copyright &copy; <?php echo date("Y");?> &mdash; สงวนออโต้คาร์ | SAC
                </div>
            </div>
        </div>
    </div>
</section>

<script src="js/login.js"></script>
</body>
</html>


</body>
</html>


