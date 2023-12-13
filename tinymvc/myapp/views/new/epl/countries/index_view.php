<script>
	$(document).ready(function(){
        let checkbox = $('.js-worldwide');

        checkbox.on('change', () => {
            if(checkbox.prop('checked')) {
                $('.js-select-countries').hide();
                $('.js-submit-worldwide').show();
            } else {
                $('.js-select-countries').show();
                $('.js-submit-worldwide').hide();
            }
		});
	});

	function submit_worldwide($this){
		var $wrWorldwide = $this.closest('.js-select-worldwide');

		$.ajax({
			type: 'POST',
			url: __current_sub_domain_url + 'shipping_countries/ajax_countries_operations/save_countries',
			data: {'worldwide' : 1},
			dataType: 'JSON',
			beforeSend: function(){
				$this.addClass('disabled');
				showLoader($wrWorldwide, 'Sending...', 'fixed');
			},
			success: function(resp){
				hideLoader($wrWorldwide);
				$this.removeClass('disabled');
				systemMessages( resp.message, resp.mess_type );
			}
		});
	}

	function edit_countries($form){

		if ($form.find("[name='countriesSelected[]']:checked").length == 0) {
			systemMessages('<?php echo translate('systmess_error_no_shipping_countries_selected', null, true);?>', 'warning');
			return;
		}

		var $wrform = $form.closest('.js-select-countries');
		var fdata = $form.serialize();

		$.ajax({
			type: 'POST',
			url: __current_sub_domain_url + 'shipping_countries/ajax_countries_operations/save_countries',
			data: fdata,
			dataType: 'JSON',
			beforeSend: function(){
				showLoader($wrform, 'Sending...', 'fixed');
				$form.find('button[type=submit]').addClass('disabled');
			},
			success: function(resp){
				hideLoader($wrform);
				systemMessages( resp.message, resp.mess_type );
				$form.find('button[type=submit]').removeClass('disabled');

				if(resp.mess_type == 'success'){

				}
			}
		});
	}
</script>

<div class="container-center dashboard-container inputs-40">
	<div class="dashboard-line">
		<h1 class="dashboard-line__ttl">
			Select the locations you work with.
		</h1>
	</div>

	<div class="row">
		<div class="js-select-worldwide col-12 col-md-6">
			<label class="input-label">Select worldwide</label>

			<div class="info-alert-b mb-15">
				<i class="ep-icon ep-icon_info-stroke"></i>
				<span>If you don't choose any country, your company will not be seen on the order list offered to customers for selection.</span>
			</div>

			<div class="flex-display flex-jc--sb flex-ai--c h-40">
				<label class="custom-checkbox" <?php echo addQaUniqueIdentifier('ff-select-location__worldwide-checkbox');?>>
					<input class="js-worldwide" type="checkbox" <?php echo checked($worldwide, 1);?>> <span class="custom-checkbox__text">Worldwide</span>
				</label>
				<button class="js-submit-worldwide btn btn-primary call-function" data-callback="submit_worldwide" type="button" <?php echo $worldwide ? '' : 'style="display: none;"';?> <?php echo addQaUniqueIdentifier('ff-select-location__worldwide-save-btn');?>>Save</button>
			</div>
		</div>

		<div class="js-select-countries col-12 col-md-6" <?php echo $worldwide ? 'style="display: none;"' : '';?>>
			<label class="input-label">Select countries</label>

			<div class="info-alert-b mb-15">
				<i class="ep-icon ep-icon_info-stroke"></i>
				<span>
					This section lists all the countries out of your shipping area and
					lists all the countries you work with.
				</span>
			</div>

			<form class="validengine" data-callback="edit_countries">
				<?php views()->display('new/epl/countries/multiple_select_view'); ?>
				<button class="btn btn-primary pull-right mt-15" type="submit" <?php echo addQaUniqueIdentifier('ff-select-location__select-countries-save-btn');?>>Save</button>
			</form>
		</div>
	</div>
</div>
