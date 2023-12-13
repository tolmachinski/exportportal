<script>
    function callbackEditB2bBlock(resp){
        $('#block_'+resp.update_block).html(resp.text_block);
    }
</script>

<div class="container-center dashboard-container">
    <div class="dashboard-line">
        <h1 class="dashboard-line__ttl">My b2b blocks</h1>
    </div>

	<div class="info-alert-b mb-15">
		<i class="ep-icon ep-icon_info-stroke"></i> 
		<span><?php echo translate('b2b_tabs_description'); ?></span>
	</div>

    <table class="main-data-table" id="dtPartnersList">
        <thead>
            <tr>
                <th class="vat w-250">Title</th>
                <th>Content</th>
                <th class="w-90 tac"></th>
            </tr>
        </thead>
        <tbody class="tabMessage">
            <tr>
                <td>About</td>
                <td id="block_about"><?php echo $seller_b2b['about']?></td>
                <td class="tac">
                    <a class="ep-icon ep-icon_pencil fancybox.ajax fancyboxValidateModal" data-title="Edit block 'About'" title="Edit block 'About'" href="<?php echo __SITE_URL;?>seller_b2b/popup_forms/edit_block/about"></a>
                </td>
            </tr>
            <tr>
                <td>Meeting</td>
                <td id="block_meeting"><?php echo $seller_b2b['meeting']?></td>
                <td class="tac">
                    <a class="ep-icon ep-icon_pencil fancybox.ajax fancyboxValidateModal" data-title="Edit block 'Meeting'" title="Edit block 'Meeting'" href="<?php echo __SITE_URL;?>seller_b2b/popup_forms/edit_block/meeting"></a>
                </td>
            </tr>
            <tr>
                <td>Phone</td>
                <td id="block_phone"><?php echo $seller_b2b['phone']?></td>
                <td class="tac">
                    <a class="ep-icon ep-icon_pencil fancybox.ajax fancyboxValidateModal" data-title="Edit block 'Phone'" title="Edit block 'Phone'" href="<?php echo __SITE_URL;?>seller_b2b/popup_forms/edit_block/phone"></a>
                </td>
            </tr>
            <tr>
                <td>Meeting else</td>
                <td id="block_meeting_else"><?php echo $seller_b2b['meeting_else']?></td>
                <td class="tac">
                    <a class="ep-icon ep-icon_pencil fancybox.ajax fancyboxValidateModal" data-title="Edit block 'Meeting else'" title="Edit block 'Meeting else'" href="<?php echo __SITE_URL;?>seller_b2b/popup_forms/edit_block/meeting_else"></a>
                </td>
            </tr>
            <tr>
                <td>Purchase order</td>
                <td id="block_purchase_order"><?php echo $seller_b2b['purchase_order']?></td>
                <td class="tac">
                    <a class="ep-icon ep-icon_pencil fancybox.ajax fancyboxValidateModal" data-title="Edit block 'Purchase order'" title="Edit block 'Purchase order'" href="<?php echo __SITE_URL;?>seller_b2b/popup_forms/edit_block/purchase_order"></a>
                </td>
            </tr>
            <tr>
                <td>Special order</td>
                <td id="block_special_order"><?php echo $seller_b2b['special_order']?></td>
                <td class="tac">
                    <a class="ep-icon ep-icon_pencil fancybox.ajax fancyboxValidateModal" data-title="Edit block 'Special order'" title="Edit block 'Special order'" href="<?php echo __SITE_URL;?>seller_b2b/popup_forms/edit_block/special_order"></a>
                </td>
            </tr>
        </tbody>
    </table>
</div>