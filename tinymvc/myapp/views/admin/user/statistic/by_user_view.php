<table class="data table-striped table-bordered w-100pr vam-table">
	<thead>
		<tr>
			<th class="w-200 tac">Statistics</th>
			<th class="w-50 tac">Count</th>
			<th class="w-200 tac">Statistics</th>
			<th class="w-50 tac">Count</th>
		</tr>
	</thead>
	<tbody>
		<tr>
		<?php $k = 1; $j = false; $tr_class = array('odd', 'even');
		foreach ($statistics as $stat => $counter) { ?>
			<td><?php echo $statistic_names[$stat]['Comment']?></td>
			<td class="sorting_1 tac"><?php echo $counter ;?></td>
		<?php
			if($k%2)
				echo '';
			else {
				$j = !$j; $class = $tr_class[intval($j)];
				echo '</tr><tr class="' . $class . '">';
			}
			$k++;
		} ?>
	</tr>
	</tbody>
</table>
