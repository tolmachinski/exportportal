<div class="wr-filter-admin-panel">
	<div class="filter-admin-panel" >
		<div class="title-b">Filter panel</div>

		<table>
			<tr>
				<td>User status</td>
				<td>
					<select class="form-control dt_filter" data-title="User status" name="user_status" data-type="select" id="statuses">
						<option value="" data-default="true">All statuses</option>
						<option value="new">New</option>
						<option value="pending">Pending</option>
						<option value="active">Active</option>
						<option value="deleted">Deleted</option>
					</select>
				</td>
			</tr>
			<tr>
				<td><?php echo translate('ep_administration_demo_real_users_text'); ?></td>
				<td>
					<select class="dt_filter" data-title="<?php echo translate('ep_administration_demo_real_users_text'); ?>" name="fake_user">
						<option value="" data-default="true">All</option>
						<option value="1"><?php echo translate('ep_administration_demo_users_text'); ?></option>
						<option value="0"><?php echo translate('ep_administration_real_users_text'); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<td>By accreditation:</td>
				<td>
					<select class="dt_filter" data-title="Accreditation" name="accreditation">
						<option value="" data-default="true">All</option>
						<option value="0">No</option>
						<option value="1">Yes</option>
						<option value="2">Process</option>
					</select>
				</td>
			</tr>
			<tr>
				<td>By company type:</td>
				<td>
					<select class="dt_filter" data-title="Type" name="type_company">
						<option value="" data-default="true">All</option>
						<option value="1" data-value-text="Company" selected>Company</option>
						<option value="2">Branch</option>
					</select>
				</td>
			</tr>
			<tr>
				<td>By featured status:</td>
				<td>
					<select class="dt_filter" data-title="Featured" name="featured">
						<option value="" data-default="true">All</option>
						<option value="1">Yes</option>
						<option value="0">No</option>
					</select>
				</td>
			</tr>
			<tr>
				<td>Can be featured</td>
				<td>
					<select class="dt_filter" data-title="Can be featured" name="be_featured">
						<option value="" data-default="true">All</option>
						<option value="1">Yes</option>
					</select>
				</td>
			</tr>
			<tr>
				<td>By company visibility:</td>
				<td>
					<select class="dt_filter" data-title="Visibility" name="visibility_company">
						<option value="" data-default="true">All</option>
						<option value="1">Visible</option>
						<option value="2">Invisible</option>
					</select>
				</td>
			</tr>
			<tr>
				<td>By company locked:</td>
				<td>
					<select class="dt_filter" data-title="Visibility" name="blocked">
						<option value="" data-default="true">All</option>
						<option value="1">Locked</option>
						<option value="0">Unlocked</option>
					</select>
				</td>
			</tr>
			<tr>
				<td>By type:</td>
				<td>
					<select class="dt_filter" data-title="Type" name="type">
						<option value="" data-default="true">Choose type</option>
						<?php foreach($type_search as $item){
								$id_t = strForURL($item['name_type'].' '.$item['id_type']);
						?>
							<option  value="<?php echo $item['id_type']; ?>" data-id="<?php echo $id_t; ?>" <?php echo selected($id_t, $type_selected);?>><?php echo $item['name_type']; ?></option>
						<?php }?>
					</select>
				</td>
			</tr>
			<tr>
				<td>By industry:</td>
				<td>
					<select class="dt_filter" data-title="Industry" name="industry" id="industry-select">
						<option value="" data-default="true">Choose industry</option>
						<?php foreach($industry_search as $industry_id => $industry) { ?>
							<option value="<?php echo $industry_id; ?>"><?php echo $industry; ?></option>
						<?php }?>
					</select>
				</td>
			</tr>
			<tr>
				<td>By category:</td>
				<td>
					<select class="dt_filter display-n" data-title="Category" name="category" id="category-select">
						<option value="" data-default="true">Choose category</option>
                        <?php foreach($category_search as $industry_id => $industy_group){ ?>
                            <?php if(isset($industry_search[$industry_id])) { ?>
                                <optgroup id="<?php echo $industry_id; ?>" label="<?php echo $industry_search[$industry_id]; ?>" style="display:none">
                                    <?php foreach($industy_group as $category){?>
                                        <option value="<?php echo $category['id_category']; ?>" style="display:none"><?php echo $category['name']?></option>
                                    <?php } ?>
                                </optgroup>
                            <?php } ?>
						<?php } ?>
					</select>
				</td>
			</tr>
			<tr>
				<td>Add date</td>
				<td>
                    <div class="input-group">
						<input class="form-control dt_filter" type="text" data-title="Add date from" name="start_date" id="start_date" placeholder="From" readonly>
						<div class="input-group-addon">-</div>
						<input class="form-control dt_filter" type="text" data-title="Add date to" name="finish_date" id="finish_date" placeholder="To" readonly>
					</div>
				</td>
			</tr>

			<tr>
				<td>Search by</td>
				<td>
					<input class="keywords dt_filter" type="text" data-title="Search for" name="keywords" maxlength="50" id="keywords" placeholder="Keywords">
				</td>
			</tr>

			<tr>
				<td>Search by name</td>
				<td>
					<input class="keywords dt_filter" type="text" data-title="Search for name" name="company_name" placeholder="Company name">
				</td>
            </tr>

            <tr>
                <td>By username or <br> email</td>
                <td>
                    <input type="text"
                           name="search_by_username_email"
                           class="dt_filter form-control"
                           value=""
                           data-title="Search by username or email"
                           placeholder="Search by username or email">
                </td>
            </tr>

            <tr>
                <td>By item name</td>
                <td>
                    <input type="text"
                           name="search_by_item"
                           class="dt_filter form-control"
                           value=""
                           data-title="Search by item name"
                           placeholder="Search by item">
                </td>
            </tr>
		</table>
		<div class="wr-filter-list clearfix mt-10"></div>
	</div>

	<div class="btn-display ">
		<div class="i-block"><i class="ep-icon ep-icon_filter"></i></div>
		<span>&laquo;</span>
	</div>

	<div class="wr-hidden"></div>
</div>

<script>
	$(document).ready(function() {
		$("#start_date, #finish_date").datepicker();
	})
</script>
