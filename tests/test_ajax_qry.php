<?php

// Handle AJAX request (start)
if( isset($_POST['ajax']) && isset($_POST['checked']) ){
    $checked_arr = $_POST['checked'];

    echo json_encode($checked_arr);
    exit;
}
// Handle AJAX request (end)
?>

<!doctype html>
<html>
<head>
    <title>How to Handle AJAX request on the Same Page – PHP</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body >

<form method='post' action>
    <p>Checked the checkboxes and click the button.</p>
    <input type='checkbox' value='JavaScript' > JavaScript <br/>
    <input type='checkbox' value='PHP' > PHP <br/>
    <input type='checkbox' value='jQuery' > jQuery <br/>
    <input type='checkbox' value='AJAX' > AJAX <br/>
    <input type='button' value='click' id='but'>
    <div id='response'></div>
</form>

<!-- Script -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
    $(document).ready(function(){
        $('#but').click(function(){

            let checkarr = [];
            $("input[type=checkbox]:checked").each(function(index,element){
                checkarr.push($(element).val());
            });

            if(checkarr.length > 0){
                $.ajax({
                    type: 'post',
                    data: {ajax: 1,checked: checkarr},
                    dataType: 'json',
                    success: function(response){
                        $('#response').text('response : ' + JSON.stringify(response) );
                    }
                });
            }

        });
    });
</script>
</body>
</html>