<div class="wr-filter-admin-panel">
	<div class="filter-admin-panel" >
		<div class="title-b">Filter panel</div>
		<table>
        <tr>
            <tr>
				<td>Search by page URL</td>
				<td>
					<input class="dt_filter" type="text" data-title="Search by page URL" name="page_url" placeholder="Page URL">
				</td>
			</tr>
            <td>Used in module</td>
				<td>
					<select class="dt_filter" data-title="Used in module" name="module" id="modules-fitler" data-pages-url="<?php echo $pages_url; ?>">
						<option data-default="true" value="">All modules</option>
						<?php foreach($modules as $module){ ?>
							<option value="<?php echo $module['id_module'];?>"><?php echo cleanOutput($module['name_module']);?></option>
						<?php } ?>
					</select>
				</td>
			</tr>
            <td>Used on page</td>
				<td>
					<select class="dt_filter" data-title="Used on page" name="page" id="pages-filter">
						<option data-default="true" value="">All pages</option>
                        <?php foreach($pages as $page){ ?>
							<option value="<?php echo $page['id_page'];?>"><?php echo cleanOutput($page['page_name']);?></option>
						<?php } ?>
					</select>
				</td>
			</tr>
            <tr>
                <td>By Tag</td>
				<td>
					<select class="dt_filter" data-title="Tag" name="tag" id="tags-filter">
						<option data-default="true" value="">All tags</option>
                        <?php foreach($tags as $tag){ ?>
							<option value="<?php echo $tag['id'];?>"><?php echo cleanOutput($tag['name']);?></option>
						<?php } ?>
					</select>
				</td>
			</tr>
            <tr>
				<td>Translated in</td>
				<td>
					<select class="dt_filter" data-title="Translated in" name="lang">
						<option data-default="true" value="">All languages</option>
                        <?php foreach($languages as $lang){ ?>
                            <?php if('en' === $lang['lang_iso2']) { ?>
                                <?php continue; ?>
                            <?php } ?>
							<option value="<?php echo $lang['lang_iso2'];?>"><?php echo $lang['lang_name'];?></option>
						<?php } ?>
					</select>
				</td>
			</tr>
			<tr>
				<td>Not translated in</td>
				<td>
					<select class="dt_filter" data-title="Not translated in" name="not_lang">
						<option data-default="true" value="">All languages</option>
                        <?php foreach($languages as $lang){ ?>
                            <?php if('en' === $lang['lang_iso2']) { ?>
                                <?php continue; ?>
                            <?php } ?>
							<option value="<?php echo $lang['lang_iso2'];?>"><?php echo $lang['lang_name'];?></option>
						<?php } ?>
					</select>
				</td>
            </tr>
			<tr>
				<td>Need check in</td>
				<td>
					<select class="dt_filter" data-title="Need check in" name="need_review_lang">
						<option data-default="true" value="">All languages</option>
                        <?php foreach($languages as $lang){ ?>
                            <?php if('en' === $lang['lang_iso2']) { ?>
                                <?php continue; ?>
                            <?php } ?>
							<option value="<?php echo $lang['lang_iso2'];?>"><?php echo $lang['lang_name'];?></option>
						<?php } ?>
					</select>
				</td>
			</tr>
            <tr>
                <td>
                    Reviewed
                </td>
                <td>
                    <select class="dt_filter" data-title="Have been reviewed" name="is_reviewed">
                        <option data-default="true" value="">All</option>
                        <option data-default="true" value="1">Yes</option>
                        <option data-default="true" value="0">No</option>
                    </select>
                </td>
            </tr>
			<tr>
				<td>English updated</td>
				<td>
					<div class="form-group mb-0">
						<div class="input-group">
							<input type="text" class="form-control dt_filter date-picker" name="translation_updated_from" data-title="English updated from" placeholder="From">
							<div class="input-group-addon">-</div>
							<input type="text" class="form-control dt_filter date-picker" name="translation_updated_to" data-title="English updated to" placeholder="To">
						</div>
					</div>
				</td>
			</tr>
			<tr>
				<td>Translation key</td>
				<td>
					<input class="dt_filter" type="text" data-title="Translation key" name="translation_key" placeholder="Translation key">
				</td>
			</tr>
			<tr>
				<td>Search</td>
				<td>
					<input class="dt_filter" type="text" data-title="Search for" name="keywords" placeholder="Search for ...">
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
		$( ".date-picker" ).datepicker();

        var pagesFilter = $('#pages-filter');
        var moduleFilter = $('#modules-fitler');

        moduleFilter.on('change', function() {
            var self = $(this);
            var id = self.val() || null;
            var url = self.data('pages-url') || null;
            var onSuccess = function(response){
                if('success' === response.mess_type) {
                    var placeholder = pagesFilter.find('option').first();

                    pagesFilter.empty()
                    pagesFilter.append(placeholder);
                    response.data.forEach(function(page){
                        pagesFilter.append(
                            $('<option>')
                                .attr('value', page.id)
                                .text(page.name)
                        )
                    });
                    pagesFilter.val('');
                    pagesFilter.trigger('change');
                } else {
                    systemMessages(response.message, 'message-' + response.mess_type);
                }
            };

            if(null === url) {
                return;
            }
            if(null !== id) {
                url = url + '/'+ id;
            }

            $.post(url, null, null, 'json').done(onSuccess).fail(onRequestError);
        });
	})
</script>
