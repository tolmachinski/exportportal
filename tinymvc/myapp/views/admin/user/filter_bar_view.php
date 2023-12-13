<div class="wr-filter-admin-panel">
    <div class="filter-admin-panel" >
        <div class="title-b">Filter panel</div>
        <table class="w-100pr">
            <tr>
                <td>Search</td>
                <td>
                    <div class="form-group mb-0">
						<div class="input-group">
							<input <?php echo addQaUniqueIdentifier("admin-users__filter-panel__search-input")?> type="text" name="search" class="dt_filter form-control" value=""  data-title="Search" placeholder="Keywords">
							<span class="input-group-btn">
								<a <?php echo addQaUniqueIdentifier("admin-users__filter-panel__search-apply-button")?> class="dt-filter-apply dt-filter-apply-buttons btn btn-primary mr-0 mb-0"><i class="ep-icon ep-icon_magnifier "></i></a>
							</span>
						</div>
					</div>
                </td>
            </tr>
			<tr>
				<td>User ID</td>
				<td>
					<div class="form-group mb-0">
						<div class="input-group">
							<input <?php echo addQaUniqueIdentifier("admin-users__filter-panel__user-id-input")?> type="text" name="id_user" class="dt_filter form-control" value="<?php echo !empty($filters['user']['value']) ? $filters['user']['value'] : null; ?>"  data-title="User ID" placeholder="User ID">
							<span class="input-group-btn">
								<a <?php echo addQaUniqueIdentifier("admin-users__filter-panel__user-id-apply-button")?> class="dt-filter-apply dt-filter-apply-buttons btn btn-primary mr-0 mb-0"><i class="ep-icon ep-icon_magnifier "></i></a>
							</span>
						</div>
					</div>
				</td>
			</tr>
            <tr>
                <td>Is verified</td>
                <td>
                    <select class="form-control dt_filter" data-title="Is verified" name="is_verified" data-type="select">
                        <option value="" data-default="true">All</option>
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Status</td>
                <td>
                    <select <?php echo addQaUniqueIdentifier("admin-users__filter-panel__status-select")?> class="form-control dt_filter" data-title="Status" name="status" data-type="select" id="statuses">
                        <option value="" data-default="true">All statuses</option>
                        <option value="new">New</option>
                        <option value="pending">Pending</option>
                        <option value="active">Activated</option>
                        <option value="restricted">Restricted</option>
                        <option value="blocked">Blocked</option>
                        <option value="deleted">Deleted</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Email Status</td>
                <td>
                    <select class="form-control dt_filter" data-title="Email status" name="email_status" data-type="select" id="email_statuses">
                        <option value="" data-default="true">All statuses</option>
                        <option value="Ok">Ok</option>
                        <option value="Unknown">Unknown</option>
                        <option value="Bad">Bad</option>
                    </select>
                </td>
            </tr>
			<tr>
				<td><?php echo translate('ep_administration_demo_real_users_text'); ?></td>
				<td>
					<select class="dt_filter" data-title="Demo" name="fake_user">
						<option value="" data-default="true">All</option>
						<option value="1"><?php echo translate('ep_administration_demo_users_text'); ?></option>
						<option value="0"><?php echo translate('ep_administration_real_users_text'); ?></option>
					</select>
				</td>
			</tr>
            <tr>
                <td>Is Model</td>
                <td>
                    <select class="form-control dt_filter" data-title="Is Model" name="is_model" data-type="select">
                        <option value="" data-default="true">All</option>
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>OnLine/OffLine</td>
                <td>
                    <select class="form-control dt_filter" data-title="Status" name="online"  data-type="select" id="online">
                        <option value="" data-default="true">All</option>
                        <option value="1">Online</option>
                        <option value="0">Offline</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>In CRM</td>
                <td>
                    <select class="form-control dt_filter" data-title="In CRM" name="in_crm">
                        <option value="" data-default="true">All</option>
                        <option value="1">YES</option>
                        <option value="0">NO</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>CRM contact ID</td>
                <td>
                    <div class="form-group mb-0">
						<div class="input-group">
							<input type="text" name="crm_contact_id" class="dt_filter form-control" value=""  data-title="Contact ID" placeholder="Contact ID">
							<span class="input-group-btn">
								<a class="dt-filter-apply dt-filter-apply-buttons btn btn-primary mr-0 mb-0"><i class="ep-icon ep-icon_magnifier "></i></a>
							</span>
						</div>
					</div>
                </td>
            </tr>
            <tr>
                <td>Total items</td>
                <td>
                    <div class="input-group">
						<input class="form-control dt_filter" type="text" name="statistic_items_total_from" data-title="Total items from" placeholder="From">
						<div class="input-group-addon">-</div>
						<input class="form-control dt_filter" type="text" name="statistic_items_total_to" data-title="Total items to" placeholder="To">
					</div>
                </td>
            </tr>
            <tr>
                <td>Last document <br>upload date</td>
                <td>
                    <div class="input-group">
						<input class="form-control js-datepicker-filter dt_filter" type="text" name="document_upload_date_from" data-title="Last upload document date from" placeholder="From">
						<div class="input-group-addon">-</div>
						<input class="form-control js-datepicker-filter dt_filter" type="text" name="document_upload_date_to" data-title="Last upload document date to" placeholder="To">
					</div>
                </td>
            </tr>
            <tr>
                <td>Last resend date</td>
                <td>
                    <div class="input-group">
						<input class="form-control dt_filter" type="text" name="resend_date_from" data-title="Last resend date from" placeholder="From">
						<div class="input-group-addon">-</div>
						<input class="form-control dt_filter" type="text" name="resend_date_to" data-title="Last resend date to" placeholder="To">
					</div>
                </td>
            </tr>
            <tr>
                <td>Registered</td>
                <td>
                    <div class="input-group">
						<input class="form-control dt_filter" type="text" name="reg_date_from" data-title="Registered from" placeholder="From">
						<div class="input-group-addon">-</div>
						<input class="form-control dt_filter" type="text" name="reg_date_to" data-title="Registered to" placeholder="To">
					</div>
                </td>
            </tr>
            <tr>
                <td>Register information</td>
                <td>
                    <select class="form-control dt_filter" data-title="Register information" name="reg_info"  data-type="select">
                        <option value="" data-default="true">All</option>
                        <option value="campaign">Campaign</option>
                        <option value="brand_ambassador">Brand ambassador</option>
                    </select>
                    <div id="campaign-list" class="mt-10" style="display:none;">
                        <select name="campaign" class="form-control dt_filter" data-title="Campaign">
                            <option value="">Select campaign</option>
                            <?php foreach($campaigns as $campaign){?>
                                <option value="<?php echo $campaign['id_campaign'];?>"><?php echo $campaign['campaign_name'];?></option>
                            <?php }?>
                        </select>
                    </div>
                    <div id="brand_ambassador-list" class="mt-10" style="display:none;">
                        <select name="brand_ambassador" class="form-control dt_filter" data-title="Brand ambassador">
                            <option value="">Select Brand ambassador</option>
                            <?php foreach($cr_users as $cr_user){?>
                                <option value="<?php echo $cr_user['idu'];?>"><?php echo $cr_user['fname'] .' '.$cr_user['lname'];?></option>
                            <?php }?>
                        </select>
                    </div>
                </td>
            </tr>
            <tr>
                <td>Last activity</td>
                <td>
                    <div class="input-group">
						<input class="form-control dt_filter" type="text" name="activity_date_from" data-title="Activity from" placeholder="From">
						<div class="input-group-addon">-</div>
						<input class="form-control dt_filter" type="text" name="activity_date_to" data-title="Activity to" placeholder="To">
					</div>
                </td>
            </tr>

            <?php if (!empty($industries)) { ?>
                <tr>
                    <td>Buyer's <br>Industries of interest</td>
                    <td>
                        <select class="form-control dt_filter" data-title="Industries of interest" name="industry" multiple>
                            <?php foreach($industries as $industry) { ?>
                                <option value="<?php echo $industry['category_id']?>"><?php echo $industry['name'] ?></option>
                                <?php
                                    $_industries[] = array(
                                        'id' => $industry['category_id'],
                                        'name' => $industry['name']
                                    );
                                ?>
                            <?php } ?>
                        </select>
                    </td>
                </tr>
                <script>
                    var $industries_select = null;
                    var $_industries = <?php echo json_encode($_industries);?>;
                    function get_industries(){
                        $('select[name="industry"]').html('');

                        var options = [];
                        $.each($_industries, function(index, $_industry){
                            options.push('<option value="'+$_industry.id+'">'+$_industry.name+'</option>');
                        });

                        $industries_select.html(options.join(''));
                        $industries_select.select2("destroy");
                        init_industries();
                    }

                    function init_industries(){
                        $industries_select.select2({
                            width: '220px',
                            multiple: true,
                            placeholder: "Select industries",
                            minimumResultsForSearch: 2,
                        });
                    }

                    $(function(){
                        $industries_select = $('select[name="industry"]');
                        init_industries();
                    });
                </script>
            <?php } ?>

            <?php if (!isset($filter_country)) { ?>
                <?php if (!empty($continents)) { ?>
                    <tr>
                        <td>Continents</td>
                        <td>
                            <select class="form-control dt_filter" data-title="Continent" name="continent">
                                <option value="" data-default="true">Select Continent</option>
                                <?php foreach($continents as $continent){?>
                                    <option value="<?php echo $continent['id_continent']?>"><?php echo $continent['name_continent']?></option>
                                <?php }?>
                            </select>
                        </td>
                    </tr>
                <?php } ?>
                <?php if (!empty($list_country)) { ?>
                    <tr>
                        <td>Country</td>
                        <td>
                            <?php $_all_countries = array();?>
                            <select class="form-control dt_filter" data-title="Country" name="country" multiple>
                                <?php foreach($list_country as $country){?>
                                    <option value="<?php echo $country['id']?>" data-continent="<?php echo $country['id_continent']?>"><?php echo $country['country']?></option>
                                    <?php
                                        $_countries[] = array(
                                            'id' => $country['id'],
                                            'name' => $country['country'],
                                            'continent' => $country['id_continent']
                                        );
                                    ?>
                                <?php }?>
                            </select>

                            <script>
                                var $countries = null;
                                var $_countries = <?php echo json_encode($_countries);?>;

                                function countries_init(){
                                    $countries.select2({
                                        width: '220px',
                                        multiple: true,
                                        placeholder: "Select countries",
                                        minimumResultsForSearch: 2,
                                    });
                                }

                                function get_countries(continent){
                                    $('select[name="country"]').html('');
                                    var options = [];

                                    continent = (continent != undefined && continent != '')?continent:0;

                                    if(continent > 0){
                                        $.each($_countries, function(index, $_country){
                                            if($_country.continent == continent){
                                                options.push('<option value="'+$_country.id+'">'+$_country.name+'</option>');
                                            }
                                        });
                                    } else{
                                        $.each($_countries, function(index, $_country){
                                            options.push('<option value="'+$_country.id+'">'+$_country.name+'</option>');
                                        });
                                    }

                                    $('select[name="country"]').html(options.join(''));
                                    $countries.select2("destroy");
                                    countries_init();
                                }

                                $(function(){
                                    $countries = $('select[name="country"]');
                                    countries_init();

                                    $('select[name="continent"]').on('change', function() {
                                        var id_continent = $(this).val();
                                        if(id_continent != ''){
                                            get_countries(id_continent);
                                        } else{
                                            get_countries(0);
                                        }
                                    });
                                });
                            </script>
                        </td>
                    </tr>
                <?php } ?>
            <?php } ?>

            <tr>
                <td>Location status</td>
                <td>
                    <select class="form-control dt_filter" data-title="Location status" name="location_completion">
                        <option data-default="true">N/A</option>
                        <option value="1">Completed</option>
                        <option value="0">Incompleted</option>
                    </select>
                </td>
            </tr>

            <tr>
                <td>By company name</td>
                <td>
                    <input type="text"
                           name="search_by_company"
                           class="dt_filter form-control"
                           value=""
                           data-title="Search by company name"
                           placeholder="Search by company">
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
            <tr>
                <td>Restricted at</td>
                <td>
                    <div class="input-group">
						<input class="form-control dt_filter js-datepicker-filter" type="text" name="restricted_from" data-title="Restricted from" placeholder="From">
						<div class="input-group-addon">-</div>
						<input class="form-control dt_filter js-datepicker-filter" type="text" name="restricted_to" data-title="Restricted to" placeholder="To">
					</div>
                </td>
            </tr>
            <tr>
                <td>Blocked at</td>
                <td>
                    <div class="input-group">
						<input class="form-control dt_filter js-datepicker-filter" type="text" name="blocked_from" data-title="Blocked from" placeholder="From">
						<div class="input-group-addon">-</div>
						<input class="form-control dt_filter js-datepicker-filter" type="text" name="blocked_to" data-title="Blocked to" placeholder="To">
					</div>
                </td>
            </tr>
        </table>
        <div class="wr-filter-list clearfix mt-10 "></div>
    </div>
    <div class="btn-display" <?php echo addQaUniqueIdentifier("admin-users-verification__filters-open-close-button")?>>
        <div class="i-block"><i class="ep-icon ep-icon_filter"></i></div>
        <span>&laquo;</span>
    </div>
    <div class="wr-hidden" style="display: none;"></div>
</div>
<script>
$(document).ready(function(){
    $('.js-datepicker-filter, input[name="resend_date_from"],input[name="resend_date_to"],input[name="reg_date_from"],input[name="reg_date_to"],input[name="activity_date_from"],input[name="activity_date_to"]').datepicker();
})
</script>
