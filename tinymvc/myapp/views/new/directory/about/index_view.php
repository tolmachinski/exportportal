<script type="text/javascript" src="<?php echo fileModificationTime('public/plug/jquery-datatables-1-10-12/jquery.dataTables.min.js');?>"></script>

<script>
var dtCompanyAboutBlocks;

$(document).ready(function(){
	dtCompanyAboutBlocks = $('#dt-about-blocks').dataTable({
		"bProcessing": true,
		"bServerSide": true,
		"sAjaxSource": "<?php echo __SITE_URL; ?>company_about/ajax_about_list_dt",
		"aoColumnDefs": [
			{"sClass": "vam", "aTargets": ['title_dt'], "mData": "title_dt"},
			{"sClass": "w-100 vam", "aTargets": ['date_added_dt'], "mData": "date_added_dt"},
			{"sClass": "w-100 vam", "aTargets": ['date_updated_dt'], "mData": "date_updated_dt"},
			{"sClass": "w-50 tac vam dt-actions", "aTargets": ['actions_dt'], "mData": "actions_dt", "bSortable": false}
		],
		"sDom": 'rt<"bottom"lp><"clear">',
		"fnServerData": function(sSource, aoData, fnCallback) {
			$.ajax({
				"dataType": 'json',
				"type": "POST",
				"url": sSource,
				"data": aoData,
				"success": function(data, textStatus, jqXHR) {
					if (data.mess_type == 'error'){
						systemMessages(data.message, data.mess_type);
					}

					fnCallback(data, textStatus, jqXHR);
				}
			});
		},
		"fnDrawCallback": function(oSettings) {
			hideDTbottom(this);
		}
	});
});

var delete_block = function(obj){
	var $this = $(obj);
	var services = [];
	services.push($this.data('block'));

	$.ajax({
		type: 'POST',
		url: '<?php echo __SITE_URL?>seller_about/ajax_about_operation/delete_blocks',
		data: { services : services},
		beforeSend: function(){  },
		dataType: 'json',
		success: function(data){
			systemMessages( data.message, data.mess_type );

			if(data.mess_type == 'success'){
				dtCompanyAboutBlocks.fnDraw();
			}
		}
	});
}

function callbackAddAboutBlock(resp){
	dtCompanyAboutBlocks.fnDraw();
}

function callbackEditAboutBlock(resp){
	dtCompanyAboutBlocks.fnDraw();
}

function callbackEditStandartAboutBlock(resp){
	var $block = $('#block_'+resp.update_block);
	$block.find('.change-text').html('Completed');
	$block.find('.change-action').html('Edit');
	$block.find('.display-n').removeClass('display-n');
}
</script>

<div class="container-center dashboard-container">
	<div class="dashboard-line">
		<h1 class="dashboard-line__ttl">About Your Company</h1>
	</div>

	<div class="info-alert-b mt-15">
		<i class="ep-icon ep-icon_info-stroke"></i>
		<span>
			Share the information about your company in each section. These sections are visible on your "About us" profile page. It helps you to boost your brand awareness and positively impact your marketing efforts, consumer perception, and revenue!
		</span>
	</div>

	<table class="main-data-table" id="standart_block_dt">
		<thead>
			<tr>
				<th class="vat">Section</th>
				<th class="w-150 tac">Status</th>
				<th class="w-90 tac"></th>
			</tr>
		</thead>
		<tbody class="tabMessage">
			<tr id="block_about_us">
				<td>About Us</td>
				<td class="change-text tac">
					<?php echo (!empty($about_page['text_about_us']))?'Completed':'<i class="ep-icon ep-icon_minus-stroke"></i>';?>
				</td>
				<td class="tac dt-actions">
					<div class="dropdown">
						<a class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="#">
							<i class="ep-icon ep-icon_menu-circles"></i>
						</a>

						<div class="dropdown-menu dropdown-menu-right">
							<a class="dropdown-item fancybox.ajax fancyboxValidateModal" data-title="Edit block 'About Us'" title="Edit block 'About Us'" href="<?php echo __SITE_URL;?>seller_about/popup_forms/edit_block/about">
								<i class="ep-icon ep-icon_pencil"></i>
								<?php if(!empty($about_page['text_about_us'])){?>
									<span class="txt change-action">Edit</span>
								<?php }else{?>
									<span class="txt change-action">Add</span>
								<?php }?>
							</a>
							<a class="dropdown-item fancybox fancybox.ajax <?php if(empty($about_page['text_about_us'])){?>display-n<?php }?>" data-title="Preview" title="Preview" href="<?php echo __SITE_URL;?>seller_about/popup_forms/view_text_block/about_us">
								<i class="ep-icon ep-icon_info-stroke"></i><span class="txt">Preview</span>
							</a>
							<a class="dropdown-item <?php if(empty($about_page['text_about_us'])){?>display-n<?php }?>" title="View on page" href="<?php echo getMyCompanyURL();?>/about#text_about_us">
								<i class="ep-icon ep-icon_link"></i><span class="txt">View on page</span>
							</a>
						</div>
					</div>
				</td>
			</tr>
			<tr id="block_history">
				<td>History</td>
				<td class="change-text tac">
					<?php echo (!empty($about_page['text_history']))?'Completed':'<i class="ep-icon ep-icon_minus-stroke"></i>';?>
				</td>
				<td class="tac dt-actions">
					<div class="dropdown">
						<a class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="#">
							<i class="ep-icon ep-icon_menu-circles"></i>
						</a>

						<div class="dropdown-menu dropdown-menu-right">
							<a class="dropdown-item fancybox.ajax fancyboxValidateModal" data-title="Edit block 'History'" title="Edit block 'History'" href="<?php echo __SITE_URL;?>seller_about/popup_forms/edit_block/history">
								<i class="ep-icon ep-icon_pencil"></i>
								<?php if(!empty($about_page['text_history'])){?>
									<span class="txt change-action">Edit</span>
								<?php }else{?>
									<span class="txt change-action">Add</span>
								<?php }?>
							</a>
							<a class="dropdown-item fancybox fancybox.ajax <?php if(empty($about_page['text_history'])){?>display-n<?php }?>" data-title="Preview" title="Preview" href="<?php echo __SITE_URL;?>seller_about/popup_forms/view_text_block/history">
								<i class="ep-icon ep-icon_info-stroke"></i><span class="txt">Preview</span>
							</a>
							<a class="dropdown-item <?php if(empty($about_page['text_history'])){?>display-n<?php }?>" title="View on page" href="<?php echo getMyCompanyURL();?>/about#text_history">
								<i class="ep-icon ep-icon_link"></i><span class="txt">View on page</span>
							</a>
						</div>
					</div>
				</td>
			</tr>
			<tr id="block_what_we_sell">
				<td>Main products lines / services</td>
				<td class="change-text tac">
					<?php echo (!empty($about_page['text_what_we_sell']))?'Completed':'<i class="ep-icon ep-icon_minus-stroke"></i>';?>
				</td>
				<td class="tac dt-actions">
					<div class="dropdown">
						<a class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="#">
							<i class="ep-icon ep-icon_menu-circles"></i>
						</a>

						<div class="dropdown-menu dropdown-menu-right">
							<a class="dropdown-item fancybox.ajax fancyboxValidateModal" data-title="Edit block 'Main products lines / services'" title="Edit block 'Main products lines / services'" href="<?php echo __SITE_URL;?>seller_about/popup_forms/edit_block/we_sell">
								<i class="ep-icon ep-icon_pencil"></i>
								<?php if(!empty($about_page['text_what_we_sell'])){?>
									<span class="txt change-action">Edit</span>
								<?php }else{?>
									<span class="txt change-action">Add</span>
								<?php }?>
							</a>
							<a class="dropdown-item fancybox fancybox.ajax <?php if(empty($about_page['text_what_we_sell'])){?>display-n<?php }?>" data-title="Preview" title="Preview" href="<?php echo __SITE_URL;?>seller_about/popup_forms/view_text_block/what_we_sell">
								<i class="ep-icon ep-icon_info-stroke"></i><span class="txt">Preview</span>
							</a>
							<a class="dropdown-item <?php if(empty($about_page['text_what_we_sell'])){?>display-n<?php }?>" title="View on page" href="<?php echo getMyCompanyURL();?>/about#text_what_we_sell">
								<i class="ep-icon ep-icon_link"></i><span class="txt">View on page</span>
							</a>
						</div>
					</div>
				</td>
			</tr>
			<tr id="block_research_develop_abilities">
				<td>Research and develop abilities</td>
				<td class="change-text tac">
					<?php echo (!empty($about_page['text_research_develop_abilities']))?'Completed':'<i class="ep-icon ep-icon_minus-stroke"></i>';?>
				</td>
				<td class="tac dt-actions">
					<div class="dropdown">
						<a class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="#">
							<i class="ep-icon ep-icon_menu-circles"></i>
						</a>

						<div class="dropdown-menu dropdown-menu-right">
							<a class="dropdown-item fancybox.ajax fancyboxValidateModal" data-title="Edit block 'Research and develop abilities'" title="Edit block 'Research and develop abilities'" href="<?php echo __SITE_URL;?>seller_about/popup_forms/edit_block/research_develop_abilities">
								<i class="ep-icon ep-icon_pencil"></i>
								<?php if(!empty($about_page['text_research_develop_abilities'])){?>
									<span class="txt change-action">Edit</span>
								<?php }else{?>
									<span class="txt change-action">Add</span>
								<?php }?>
							</a>
							<a class="dropdown-item fancybox fancybox.ajax <?php if(empty($about_page['text_research_develop_abilities'])){?>display-n<?php }?>" data-title="Preview" title="Preview" href="<?php echo __SITE_URL;?>seller_about/popup_forms/view_text_block/research_develop_abilities">
								<i class="ep-icon ep-icon_info-stroke"></i><span class="txt">Preview</span>
							</a>
							<a class="dropdown-item <?php if(empty($about_page['text_research_develop_abilities'])){?>display-n<?php }?>" title="View on page" href="<?php echo getMyCompanyURL();?>/about#text_research_develop_abilities">
								<i class="ep-icon ep-icon_link"></i><span class="txt">View on page</span>
							</a>
						</div>
					</div>
				</td>
			</tr>
			<tr id="block_development_expansion_plans">
				<td>Company development / expansion plans</td>
				<td class="change-text tac">
					<?php echo (!empty($about_page['text_development_expansion_plans']))?'Completed':'<i class="ep-icon ep-icon_minus-stroke"></i>';?>
				</td>
				<td class="tac dt-actions">
					<div class="dropdown">
						<a class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="#">
							<i class="ep-icon ep-icon_menu-circles"></i>
						</a>

						<div class="dropdown-menu dropdown-menu-right">
							<a class="dropdown-item fancybox.ajax fancyboxValidateModal" data-title="Edit block 'Company development / expansion plans'" title="Edit block 'Company development / expansion plans'" href="<?php echo __SITE_URL;?>seller_about/popup_forms/edit_block/development_expansion_plans">
								<i class="ep-icon ep-icon_pencil"></i>
								<?php if(!empty($about_page['text_development_expansion_plans'])){?>
									<span class="txt change-action">Edit</span>
								<?php }else{?>
									<span class="txt change-action">Add</span>
								<?php }?>
							</a>
							<a class="dropdown-item fancybox fancybox.ajax <?php if(empty($about_page['text_development_expansion_plans'])){?>display-n<?php }?>" data-title="Preview" title="Preview" href="<?php echo __SITE_URL;?>seller_about/popup_forms/view_text_block/development_expansion_plans">
								<i class="ep-icon ep-icon_info-stroke"></i><span class="txt">Preview</span>
							</a>
							<a class="dropdown-item <?php if(empty($about_page['text_development_expansion_plans'])){?>display-n<?php }?>" title="View on page" href="<?php echo getMyCompanyURL();?>/about#text_development_expansion_plans">
								<i class="ep-icon ep-icon_link"></i><span class="txt">View on page</span>
							</a>
						</div>
					</div>
				</td>
			</tr>
			<?php if(group_session() == 6){?>
				<tr id="block_prod_process_management">
					<td>Production process management</td>
					<td class="change-text tac">
						<?php echo (!empty($about_page['text_prod_process_management']))?'Completed':'<i class="ep-icon ep-icon_minus-stroke"></i>';?>
					</td>
					<td class="tac dt-actions">
						<div class="dropdown">
							<a class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="#">
								<i class="ep-icon ep-icon_menu-circles"></i>
							</a>

							<div class="dropdown-menu dropdown-menu-right">
								<a class="dropdown-item fancybox.ajax fancyboxValidateModal" data-title="Edit block 'Production process management'" title="Edit block 'Production process management'" href="<?php echo __SITE_URL;?>seller_about/popup_forms/edit_block/prod_process_management">
									<i class="ep-icon ep-icon_pencil"></i>
									<?php if(!empty($about_page['text_prod_process_management'])){?>
										<span class="txt change-action">Edit</span>
									<?php }else{?>
										<span class="txt change-action">Add</span>
									<?php }?>
								</a>
								<a class="dropdown-item fancybox fancybox.ajax <?php if(empty($about_page['text_prod_process_management'])){?>display-n<?php }?>" data-title="Preview" title="Preview" href="<?php echo __SITE_URL;?>seller_about/popup_forms/view_text_block/prod_process_management">
									<i class="ep-icon ep-icon_info-stroke"></i><span class="txt">Preview</span>
								</a>
								<a class="dropdown-item <?php if(empty($about_page['text_prod_process_management'])){?>display-n<?php }?>" title="View on page" href="<?php echo getMyCompanyURL();?>/about#text_prod_process_management">
									<i class="ep-icon ep-icon_link"></i><span class="txt">View on page</span>
								</a>
							</div>
						</div>
					</td>
				</tr>
				<tr id="block_production_flow">
					<td>Production flow</td>
					<td class="change-text tac">
						<?php echo (!empty($about_page['text_production_flow']))?'Completed':'<i class="ep-icon ep-icon_minus-stroke"></i>';?>
					</td>
					<td class="tac dt-actions">
						<div class="dropdown">
							<a class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="#">
								<i class="ep-icon ep-icon_menu-circles"></i>
							</a>

							<div class="dropdown-menu dropdown-menu-right">
								<a class="dropdown-item fancybox.ajax fancyboxValidateModal" data-title="Edit block 'Production flow'" title="Edit block 'Production flow'" href="<?php echo __SITE_URL;?>seller_about/popup_forms/edit_block/production_flow">
									<i class="ep-icon ep-icon_pencil"></i>
									<?php if(!empty($about_page['text_production_flow'])){?>
										<span class="txt change-action">Edit</span>
									<?php }else{?>
										<span class="txt change-action">Add</span>
									<?php }?>
								</a>
								<a class="dropdown-item fancybox fancybox.ajax <?php if(empty($about_page['text_production_flow'])){?>display-n<?php }?>" data-title="Preview" title="Preview" href="<?php echo __SITE_URL;?>seller_about/popup_forms/view_text_block/production_flow">
									<i class="ep-icon ep-icon_info-stroke"></i><span class="txt">Preview</span>
								</a>
								<a class="dropdown-item <?php if(empty($about_page['text_production_flow'])){?>display-n<?php }?>" title="View on page" href="<?php echo getMyCompanyURL();?>/about#text_production_flow">
									<i class="ep-icon ep-icon_link"></i><span class="txt">View on page</span>
								</a>
							</div>
						</div>
					</td>
				</tr>
			<?php }?>
		</tbody>
	</table>

	<div class="dashboard-line mt-50">
		<h1 class="dashboard-line__ttl">Additional Information</h1>

		<div class="dashboard-line__actions">
			<a class="btn btn-primary pl-20 pr-20 fancyboxValidateModal fancybox.ajax" href="<?php echo __SITE_URL;?>seller_about/popup_forms/add_about_block" data-title="Add new about block">
				<i class="ep-icon ep-icon_plus-circle fs-20"></i>
				<span class="dn-m-min">Add block</span>
			</a>
		</div>
	</div>

	<div class="info-alert-b mt-15">
        <i class="ep-icon ep-icon_info-stroke"></i>
        <span>
			If you could not list all of the details about your company in the above section, then you can do so in the following table.
        </span>
    </div>

	<table class="main-data-table" id="dt-about-blocks">
		<thead>
			<tr>
				<th class="title_dt">Section</th>
				<th class="date_added_dt">Added</th>
				<th class="date_updated_dt">Updated</th>
				<th class="actions_dt"></th>
			</tr>
		</thead>
		<tbody class="tabMessage"></tbody>
	</table>
</div>

