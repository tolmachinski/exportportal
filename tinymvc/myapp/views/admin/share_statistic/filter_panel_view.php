<div class="wr-filter-admin-panel">
    <div class="filter-admin-panel w-350">
        <div class="title-b">Filter panel</div>
        <table>
			<tr>
				<td>Shared Date</td>
				<td>
					<div class="input-group">
						<input class="form-control dt_filter" type="text" data-title="Shared from" name="created_from" placeholder="From" readonly>
						<div class="input-group-addon">-</div>
						<input class="form-control dt_filter" type="text" data-title="Shared to" name="created_to" placeholder="To" readonly>
					</div>
				</td>
			</tr>
            <tr>
				<td>Type</td>
				<td>
                    <?php
                        $types = ['company','item'];
                    ?>
                    <select
                        class="dt_filter"
                        data-title="Type"
                        name="type"
                    >
                        <option data-default="true" value="">All types</option>
                        <?php foreach($types as $typesItem){?>
                            <option
                                value="<?php echo $typesItem; ?>"
                            ><?php echo $typesItem; ?></option>
                        <?php }?>
                    </select>
                </td>
			</tr>
            <tr>
				<td>Types sharing</td>
				<td>
                    <?php
                        $typesSharing = ['facebook','twitter','linkedin','pinterest','share this','email this'];
                    ?>
                    <select
                        class="dt_filter"
                        data-title="Type sharing"
                        name="type_sharing"
                    >
                        <option data-default="true" value="">All types sharing</option>
                        <?php foreach($typesSharing as $typesSharingItem){?>
                            <option
                                value="<?php echo $typesSharingItem; ?>"
                            ><?php echo $typesSharingItem; ?></option>
                        <?php }?>
                    </select>
                </td>
			</tr>
        </table>
        <div class="wr-filter-list clearfix mt-10 "></div>
    </div>
    <div class="btn-display ">
        <div class="i-block"><i class="ep-icon ep-icon_filter"></i></div>
        <span>&laquo;</span>
    </div>

    <div class="wr-hidden"></div>
</div>

<script>
$(document).ready(function() {
	$(".filter-admin-panel").find("input[name^=created_]" ).datepicker();
});
</script>
