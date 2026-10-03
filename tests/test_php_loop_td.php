<table id="example" class="display table table-striped table-bordered"
       cellspacing="0" width="100%">
    <thead>
    <tr>
        <th>สินค้า</th>
        <th>ดอกยาง</th>
        <th>สินค้า</th>
        <th>สินค้า</th>
<?php

for ($x = 1; $x <= 31; $x++) {

        echo "<td align=\"left\"><p class=\"text-center\">" . $x  ;


        echo "  <th>Cust.</th>
                <th>Take/Sale</th>
                <th>Stock</th>
                <th>Date In</th>
                <th>Qty Need</th>";

}
?>
        <th>ยอดรวม</th>
        </tr>

    </thead>
</table>




<td align="left"><p
            class="text-center"><?php echo htmlentities($row_data['1_CUST']); ?></p>
</td>