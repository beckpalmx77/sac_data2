<?php
$total = 110;
$y = 100;
for ($x=0;$x<=9;$x++) {
    if (($x*2) + $y === $total) {
        echo "x = " . $x;
    }
}