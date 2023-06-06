<?php
// Call PHP Script from javascript

$mydata = 'Variables declaration in PHP';

?>


<!doctype html>
<html xmlns="http://www.w3.org/1999/html">

<script src="../vendor/jquery/jquery.min.js"></script>
<script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../vendor/jquery-easing/jquery.easing.min.js"></script>

<head>
    <meta charset="utf-8">
    <title>PHP & Javascript</title>
    <div class="row">
        <div class="col-sm-12">
            <button type="button" id="BtnData"
                    name="BtnData"
                    class="btn btn-primary mb-3">
                แสดงข้อมูล
            </button>
        </div>
    </div>


    <script type="text/javascript">

        $("#BtnData").click(function () {

            alert("<?php echo $mydata?>");

        });

    </script>


</head>

<body>
</body>
</html>