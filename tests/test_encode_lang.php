<?

$message = "รับทำเว็บ รับเขียนเว็บ เรียนเขียนโปรแกรม";

$tis620 = iconv("utf-8", "tis-620", $message );
$utf8 = iconv("tis-620", "utf-8", $tis620 );

echo "Page charset=utf-8";
echo "<br/>";
echo "Convert from UTF-8 to TIS-620 = ".$tis620;
echo "<br/>";
echo "Convert from TIS-620 to UTF-8 = ".$utf8;

?>

