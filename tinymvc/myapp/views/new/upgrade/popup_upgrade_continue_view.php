<div class="wr-modal-flex inputs-40">
	<form class="modal-flex__form validateModal" data-callback="confirm_start_upgrade">
		<div class="modal-flex__content">
            <?php if(!empty($aditional_documents)){?>
                <label class="input-label input-label--required">Documents needed</label>
                <div class="ep-middle-text">
                    Export Portal assures that all the documents you upload to the site are used solely for verification process, and will not be used for any other purposes.
                </div>
                <table class="main-data-table mt-15 mb-15">
                    <tbody>
                        <?php foreach($aditional_documents as $document){?>
                            <tr>
                                <td class="vam">
                                    <a class="ep-icon ep-icon_info info-dialog" data-content="#info-dialog__document-<?php echo (int) $document['id_document'];?>-details" title="What is: <?php echo cleanOutput($document['document_title']);?>?" data-title="What is: <?php echo cleanOutput($document['document_title']);?>?"></a>
                                    <?php echo cleanOutput($document['document_title']);?>
                                    <div class="display-n" id="info-dialog__document-<?php echo (int) $document['id_document'];?>-details">
                                        <?php echo cleanOutput($document['document_description']);?>
                                    </div>
                                </td>
                            </tr>
                        <?php }?>
                    </tbody>
                </table>
            <?php }?>

            <label class="input-label input-label--required">Package / Period</label>
            <label class="input-label custom-radio">
                <input checked type="radio"/>
                <span class="custom-radio__text">$<?php echo get_price($group_package['price'], false);?>/per <?php echo $group_package['full'];?></span>
            </label>
		</div>
		<div class="modal-flex__btns">
            <div class="modal-flex__btns-left">
                <button class="btn btn-dark call-function" data-callback="closeFancyBox" type="button">Cancel</button>
            </div>
            <div class="modal-flex__btns-right">
                <button class="btn btn-success" type="submit">Confirm</button>
            </div>
		</div>
	</form>
</div>
