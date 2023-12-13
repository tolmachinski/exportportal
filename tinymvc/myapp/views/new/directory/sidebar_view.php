<script src="<?php echo fileModificationTime('public/plug/hidemaxlistitem-1-3-4/hideMaxListItem-min.js'); ?>"></script>

<script type="text/javascript">
$(document).ready(function(){

	function selectSubcategory(firstCat){
		var $CatSelect = $('#category-select');
		var $IndSelect = $('#industry-select');

		if(firstCat){
			$CatSelect.find('optgroup').hide().end()
				.prop('selectedIndex',0);
		}

		$IndSelect.find('option:selected').each(function(){
			var $thisSelected = $(this);
			var idSelected = $thisSelected.val();
			if(idSelected){
				var $optGroup = $CatSelect.find('#'+idSelected);
				$optGroup.siblings().hide();
				$CatSelect.find('option').first().after($optGroup);
				$optGroup.show();
			}else{
				$CatSelect.find('optgroup').show();
			}
		});
	}

	$("#industry-select").change(function(){
		selectSubcategory(1);
	});

	selectSubcategory(0);

	$('#search_directory_form').on('submit', function(e){
		e.preventDefault();
		var $form = $(this);
		if (!$($form).validationEngine('validate')) {
			return false;
		}
		var link = '<?php echo $search_form_link;?>';

		var form_action = [];
		var form_get = [];
		if (link.indexOf("?") >= 0){
			link = link.split('?');
			form_action.push(link[0]);
			form_get.push(link[1]);
		} else{
			form_action.push(link);
		}

		$.each($form.find('.type_url'), function( index, form_element ) {
			if(form_element.value != ''){
				form_action.push(form_element.name+'/'+form_element.value);
			}
		});

		form_action = form_action.join('/');
		var val = $form.find('input[name="keywords"]').val();
		var keywords = val.replace(/(\s)+/g,"$1");
		if(keywords != '' && keywords != '+'){
			form_get.push('keywords='+keywords);
		}

		if(form_get.length > 0){
			form_action += '?'+form_get.join('&');
		}

		window.location = form_action;
	});

	$('.minfo-sidebar-box__list').hideMaxListItems({
		'max': 10,
	});
});
</script>

<?php if(!empty($search_params)){?>
<h3 class="minfo-sidebar-ttl">
	<span class="minfo-sidebar-ttl__txt">Active Filters</span>
</h3>

<div class="minfo-sidebar-box">
	<div class="minfo-sidebar-box__desc">
		<ul class="minfo-sidebar-params">
			<?php foreach($search_params as $item){?>
				<li class="minfo-sidebar-params__item">
					<div class="minfo-sidebar-params__ttl">
						<div class="minfo-sidebar-params__name"><?php echo $item['param']?>:</div>
					</div>

					<ul class="minfo-sidebar-params__sub">
						<li class="minfo-sidebar-params__sub-item">
							<div class="minfo-sidebar-params__sub-ttl"><?php echo $item['title']?></div>
							<a class="minfo-sidebar-params__sub-close ep-icon ep-icon_remove-stroke" href="<?php echo $item['link'];?>"></a>
						</li>
					</ul>
				</li>
			<?php } ?>
            <li>
                <a class="btn btn-light btn-block txt-blue2" href="<?php echo __SITE_URL; ?>directory/all">Clear all</a>
            </li>
		</ul>
	</div>
</div>
<?php }?>

<h3 class="minfo-sidebar-ttl">
	<span class="minfo-sidebar-ttl__txt">Search</span>
</h3>

<div class="minfo-sidebar-box">
	<div class="minfo-sidebar-box__desc">
		<form id="search_directory_form" class="validengine_search minfo-form mb-0">
			<input class="validate[required, minSize[2]] minfo-form__input2" type="text" name="keywords" maxlength="50" placeholder="Keywords" value="<?php echo $keywords?>">
			<select class="minfo-form__input2 type_url" name="country">
				<option value="">Choose country</option>
				<?php
					$port_countries = array_map(function($country){
						$country['country_url'] = strForURL($country['country'] . ' ' . $country['id']);

						return $country;
					}, $search_countries);
				?>

				<?php echo getCountrySelectOptions($port_countries, id_from_link($country_selected), array('value' => 'country_url', 'include_default_option' => false));?>
			</select>
			<select class="minfo-form__input2 type_url" name="type" >
				<option value="">Choose type</option>
				<?php foreach($types as $type){?>
					<?php $type_url = strForURL($type['name_type'].' '.$type['id_type']);?>
					<option value="<?php echo $type_url; ?>" <?php echo selected($type_url, $type_selected);?>><?php echo $type['name_type']; ?></option>
				<?php }?>
			</select>
			<select class="minfo-form__input2 type_url" name="industry" id="industry-select">
				<option value="">Choose industry</option>
				<?php foreach($industries as $industry){?>
					<?php $industry_url = strForURL($industry['name'].' '.$industry['category_id']);?>
					<option value="<?php echo $industry_url; ?>" <?php echo selected($industry_url , $industry_selected);?>><?php echo $industry['name']; ?></option>
				<?php }?>
			</select>
			<select class="minfo-form__input2 type_url" name="category" id="category-select">
				<option value="">Choose category</option>
				<?php foreach($industries as $industry){?>
					<optgroup id="<?php echo strForURL($industry['name']).'-'.$industry['category_id']?>" label="<?php echo $industry['name']?>">
						<?php if(!empty($industry['subcats'])){?>
							<?php foreach($industry['subcats'] as $category){?>
								<?php $category_url = strForURL($category['name'].' '.$category['category_id']);?>
								<option value="<?php echo $category_url;?>" <?php echo selected($category_url , $category_selected);?>><?php echo $category['name']?></option>
							<?php } ?>
						<?php } else{?>
							<?php $category_url = strForURL($industry['name'].' '.$industry['category_id']);?>
							<option value="<?php echo $category_url;?>" <?php echo selected($category_url, $category_selected);?>><?php echo $industry['name']?></option>
						<?php }?>
					</optgroup>
				<?php } ?>
			</select>
			<button class="btn btn-dark btn-block minfo-form__btn2" type="submit">Search</button>
		</form>
	</div>
</div>

<?php if(!empty($types)){?>
	<h3 class="minfo-sidebar-ttl">
		<span class="minfo-sidebar-ttl__txt">By Type</span>
	</h3>

	<div class="minfo-sidebar-box">
		<div class="minfo-sidebar-box__desc">
			<ul class="minfo-sidebar-box__list">
				<?php foreach($types as $type){?>
					<li class="minfo-sidebar-box__list-item">
						<a class="minfo-sidebar-box__list-link" href="<?php echo replace_dynamic_uri(strForURL($type['name_type'].' '.$type['id_type']), $links_tpl[$directory_uri_components['type']]);?>">
							<?php echo $type['name_type'];?>
						</a>
						<span class="minfo-sidebar-box__list-counter"><?php echo $type['counter'];?></span>
					</li>
				<?php }?>
			</ul>
		</div>
	</div>
<?php }?>

<?php if(!empty($countries)){?>
    <h3 class="minfo-sidebar-ttl">
        <span class="minfo-sidebar-ttl__txt">Country</span>
    </h3>

    <div class="minfo-sidebar-box">
        <div class="minfo-sidebar-box__desc">
            <ul class="hide-max-list minfo-sidebar-box__list">
                <?php foreach($countries as $country){?>
                    <li class="minfo-sidebar-box__list-item">
                        <a class="minfo-sidebar-box__list-link w-160" href="<?php echo replace_dynamic_uri(strForURL($country['country'].' '.$country['id_country']), $links_tpl[$directory_uri_components['country']]);?>">
                            <?php echo $country['country']?>
                        </a>

                        <span class="minfo-sidebar-box__list-counter"><?php echo $country['counter']?></span>
                    </li>
                <?php } ?>
            </ul>
        </div>
    </div>
<?php } ?>

<?php if(!empty($industries)){?>
    <h3 class="minfo-sidebar-ttl">
        <span class="minfo-sidebar-ttl__txt">Industry</span>
    </h3>

    <div class="minfo-sidebar-box">
        <div class="minfo-sidebar-box__desc">
            <ul class="hide-max-list minfo-sidebar-box__list">
                <?php foreach($industries as $industry){?>
                    <li class="minfo-sidebar-box__list-item">
                        <a class="minfo-sidebar-box__list-link w-160" href="<?php echo replace_dynamic_uri(strForURL($industry['name'].' '.$industry['category_id']), $links_tpl[$directory_uri_components['industry']]);?>">
                            <?php echo $industry['name'];?>
                        </a>
						<span class="minfo-sidebar-box__list-counter"><?php echo (int) $industry['directory_count'];?></span>
                    </li>
                <?php } ?>
            </ul>
        </div>
    </div>

	<?php if(!empty($industry_selected)){?>
		<?php $categories = $industries[id_from_link($industry_selected)]['subcats'];?>
		<?php if(!empty($categories)){?>
			<h3 class="minfo-sidebar-ttl">
				<span class="minfo-sidebar-ttl__txt">By Categories</span>
			</h3>

			<div class="minfo-sidebar-box">
				<div class="minfo-sidebar-box__desc">
					<ul class="minfo-sidebar-box__list">
						<?php foreach($categories as $category){?>
							<li class="minfo-sidebar-box__list-item">
								<a class="minfo-sidebar-box__list-link" href="<?php echo replace_dynamic_uri(strForURL($category['name'].' '.$category['category_id']), $links_tpl[$directory_uri_components['category']]);?>">
									<?php echo $category['name']; ?>
								</a>
								<span class="minfo-sidebar-box__list-counter"><?php echo (int) $category['directory_count'];?></span>
							</li>
						<?php }?>
					</ul>
				</div>
			</div>
		<?php }?>
	<?php }?>
<?php } ?>
