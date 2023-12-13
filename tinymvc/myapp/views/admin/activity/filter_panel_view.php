<div class="wr-filter-admin-panel">
    <div class="filter-admin-panel">
		<div class="title-b">Filter panel</div>
        <table>
            <tr>
				<td>Date</td>
				<td>
					<div class="input-group">
						<input class="form-control dt_filter filter-datetime" type="text" data-title="Date from" name="date_from" placeholder="From" value="<?php echo !empty($filters['date_from']) ? $filters['date_from'] : ''; ?>" readonly>
						<div class="input-group-addon">-</div>
						<input class="form-control dt_filter filter-datetime" type="text" data-title="Date to" name="date_to" placeholder="To" value="<?php echo !empty($filters['date_to']) ? $filters['date_to'] : ''; ?>" readonly>
					</div>
				</td>
			</tr>
            <tr>
                <td>Initiator name</td>
                <td>
                    <input class="form-control dt_filter" type="text" data-title="Initiator name" name="initiator_name" placeholder="Initiator name">
                </td>
            </tr>
            <tr>
                <td>Initiator email</td>
                <td>
                    <input class="form-control dt_filter" type="text" data-title="Initiator email" name="initiator_email" placeholder="Initiator email">
                </td>
            </tr>
            <tr>
                <td>Resource type</td>
                <td>
                    <select class="dt_filter" data-title="Resource type" name="resource_type" id="filter--resource-type" data-toggle="#filter--resource-name">
                        <option data-default="true" value="">All types</option>
                        <?php foreach($resource_types as $resource_type_id => $resource_type_name) { ?>
                            <option value="<?php echo $resource_type_id; ?>" <?php echo isset($filters['resource_type']) ? selected($filters['resource_type'], $resource_type_id): ''; ?>>
                                <?php echo cleanOutput($resource_type_name); ?>
                            </option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Resource name</td>
                <td>
                    <input class="form-control dt_filter" type="text" data-title="Resource name" name="resource_name" placeholder="Resource name" disabled id="filter--resource-name">
                </td>
            </tr>
            <tr>
                <td>Operation type</td>
                <td>
                    <select class="dt_filter" data-title="Operation type" name="operation_type">
                        <option data-default="true" value="">All types</option>
                        <?php foreach($operation_types as $operation_type_id => $operation_type_name) { ?>
                            <option value="<?php echo $operation_type_id; ?>"><?php echo cleanOutput($operation_type_name); ?></option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Log level</td>
                <td>
                    <select class="dt_filter" data-title="Log level" name="level">
                        <option data-default="true" value="">All levels</option>
                        <?php foreach($log_levels as $level => $level_name){ ?>
                            <option value="<?php echo $level; ?>">
                                <?php echo mb_strtoupper(cleanOutput($level_name)); ?>
                            </option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Is viewed</td>
                <td>
                    <select class="dt_filter" data-title="Is viewed" name="viewed">
                        <option data-default="true" value="">All</option>
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                </td>
            </tr>
        </table>
        <?php if(!empty($filters['resource'])) { ?>
            <a class="display-n dt_filter" data-value="<?php echo$filters['resource']['id'];?>" data-name="resource" data-title="Resource" data-value-text="<?php echo $filters['resource']['title'] ;?>"></a>
        <?php } ?>
        <?php if(!empty($filters['initiator'])){ ?>
            <a class="display-n dt_filter" data-value="<?php echo $filters['initiator']['idu'];?>" data-name="initiator" data-title="Initiator" data-value-text="<?php echo $filters['initiator']['fname'] . ' ' . $filters['initiator']['lname'];?>"></a>
        <?php } ?>
		<div class="wr-filter-list clearfix mt-10"></div>
    </div>
    <div class="btn-display ">
		<div class="i-block"><i class="ep-icon ep-icon_filter"></i></div>
		<span>&laquo;</span>
	</div>

	<div class="wr-hidden"></div>
</div>
<script type="application/javascript">
    $(function(){
        $(".filter-admin-panel").find(".filter-datetime" ).datepicker();
    });
</script>