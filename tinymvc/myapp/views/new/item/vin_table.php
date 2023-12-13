<table class="vin-table table table-bordered">
	<?php foreach ($vin_info as $attr){?>
		<tr>
            <td class="vin-name"><?php echo $attr['name'];?></td>
            <td class="vin-value"><?php echo $attr['value'];?></td>
        </tr>
	<?php }?>
</table>