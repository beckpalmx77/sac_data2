
<?php

//$tab = array("UTF-8", "ASCII", "Windows-1252", "ISO-8859-15", "ISO-8859-1", "ISO-8859-6", "CP1256");

$tab = array("windows-874" , "windows-874");

$chain = "";

$my_string = "ทดสอบ Test";


foreach ($tab as $i)

{

    foreach ($tab as $j)

    {

        $chain .= " $i$j ".iconv($i, $j, $my_string) . "<br>";

    }

}



echo $chain;

