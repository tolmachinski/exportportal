<script>
$(document).ready(function(){

	$('#nav-menu-items').find('.nav-link:first').trigger('click');
	$('#js-dropdown-mobile-help-search').find('.dropdown-item:first').trigger('click');

	$('#nav-menu-items a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
		$('#js-dropdown-mobile-help-search').find('.dropdown-item[href="'+$(e.target).attr('href')+'"]').trigger('click');
	})

});

function jsDropdownMobile($this){
	var tab = $this.attr('href');
	var text = $this.find('.txt').text();
	var count = $this.find('.count').text();
	var $tabForClick = $('#nav-menu-items').find('a[href="'+tab+'"]');

	if(!$tabForClick.hasClass('active')){
		$tabForClick.trigger('click');
	}

	$this.addClass('active').siblings().removeClass('active');
	var $dropdownToggle = $this.closest('.dropdown').find('.dropdown-toggle');
	$dropdownToggle.find('.txt').text(text);
	$dropdownToggle.find('.count').text(count);
}
</script>
<link rel="stylesheet" type="text/css" media="screen" href="<?php echo fileModificationTime('public/css/questions-list-styles.css'); ?>" />

<div class="help-search-wrapper">

	<ul id="nav-menu-items" class="nav nav-tabs nav--borders hide-767" role="tablist">
		<?php if ($faq_count) {?>
			<li class="nav-item">
				<a
                    class="nav-link"
                    href="#tab-faq"
                    aria-controls="title"
                    role="tab"
                    data-toggle="tab"
                    <?php echo addQaUniqueIdentifier('page__help-search__faq-tab'); ?>
                >
                    FAQ <span <?php echo addQaUniqueIdentifier('page__help-search__counter'); ?>>(<?php echo $faq_count ?>)</span>
                </a>
			</li>
		<?php }?>
		<?php if ($topics_count) {?>
			<li class="nav-item">
				<a
                    class="nav-link"
                    href="#tab-topics"
                    aria-controls="title"
                    role="tab"
                    data-toggle="tab"
                    <?php echo addQaUniqueIdentifier('page__help-search__topics-tab'); ?>
                >
                    Topics <span <?php echo addQaUniqueIdentifier('page__help-search__counter'); ?>>(<?php echo $topics_count ?>)</span>
                </a>
			</li>
		<?php }?>
		<?php if ($questions_count) {?>
			<li class="nav-item">
				<a
                    class="nav-link"
                    href="#tab-questions"
                    aria-controls="title"
                    role="tab"
                    data-toggle="tab"
                    <?php echo addQaUniqueIdentifier('page__help-search__questions-tab'); ?>
                >
                    Community help <span <?php echo addQaUniqueIdentifier('page__help-search__counter'); ?>>(<?php echo $questions_count ?>)</span>
                </a>
			</li>
		<?php }?>
	</ul>

	<div id="js-dropdown-mobile-help-search" class="dropdown hide-mn-767">
		<a class="btn btn-block btn-light dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
			<span class="txt">Select</span>
			<span class="count" <?php echo addQaUniqueIdentifier('page__help-search__counter'); ?>></span>
			<i class="ep-icon ep-icon_arrow-down fs-10"></i>
		</a>

		<div class="dropdown-menu">
			<?php if ($faq_count) {?>
				<a class="dropdown-item pl-45 call-function" href="#tab-faq" data-callback="jsDropdownMobile" <?php echo addQaUniqueIdentifier('page__help-search__faq-tab'); ?>>
					<span class="txt">FAQ</span>
					<span class="count" <?php echo addQaUniqueIdentifier('page__help-search__counter'); ?>><?php echo $faq_count ?></span>
				</a>
			<?php }?>
			<?php if ($topics_count) {?>
				<a class="dropdown-item pl-45 call-function" href="#tab-topics" data-callback="jsDropdownMobile" <?php echo addQaUniqueIdentifier('page__help-search__topics-tab'); ?>>
					<span class="txt">Topics</span>
					<span class="count" <?php echo addQaUniqueIdentifier('page__help-search__counter'); ?>><?php echo $topics_count ?></span>
				</a>
			<?php }?>
			<?php if ($questions_count) {?>
				<a class="dropdown-item pl-45 call-function" href="#tab-questions" data-callback="jsDropdownMobile" <?php echo addQaUniqueIdentifier('page__help-search__questions-tab'); ?>>
					<span class="txt">Community help</span>
					<span class="count" <?php echo addQaUniqueIdentifier('page__help-search__counter'); ?>><?php echo $questions_count ?></span>
				</a>
			<?php }?>
		</div>
	</div>

	<div class="tab-content tab-content--borders">
		<?php if ($faq_count) {?>
			<div role="tabpanel" class="tab-pane fade" id="tab-faq">
				<?php views()->display('new/faq/partial_list_view');?>

				<?php if ($faq_count > $limit_count) {?>
					<a class="btn btn-light btn-block txt-blue2 mt-50" href="<?php echo __SITE_URL . 'faq/?keywords=' . $keywords ?>">
						<?php echo translate('help_view_all_found') . ' ' . translate('help_faqs') . ' <span ' . addQaUniqueIdentifier('page__help-search__counter') . '>(' . $faq_count . ')</span>';?>
					</a>
				<?php }?>
			</div>
		<?php }?>

		<?php if ($topics_count) {?>
			<div role="tabpanel" class="tab-pane fade" id="tab-topics">
					<div class="row">
						<?php views()->display('new/topics/topics_list_view');?>
					</div>

				<?php if ($topics_count > $limit_count) {?>
					<a class="btn btn-light btn-block txt-blue2 mt-50" href="<?php echo __SITE_URL . 'topics/help?keywords=' . $keywords;?>">
						<?php echo translate('help_view_all_found') . ' ' . translate('help_topics') . ' <span ' . addQaUniqueIdentifier('page__help-search__counter') . '>(' . $topics_count . ')</span>';?>
					</a>
				<?php }?>
			</div>
		<?php }?>

		<?php if ($questions_count) {?>
			<div role="tabpanel" class="tab-pane fade" id="tab-questions">
				<ul class="questions">
					<?php views()->display('new/questions/item_question_view', ['questions' => $questions, 'isHelpPage' => true]);?>
				</ul>

				<?php if ($questions_count > $limit_count) {?>
					<a class="btn btn-light btn-block txt-blue2 mt-50" href="<?php echo __SITE_URL . 'questions?keywords=' . $keywords;?>">
						<?php echo translate('help_view_all_found') . ' ' . translate('help_questions') . ' <span ' . addQaUniqueIdentifier('page__help-search__counter') . '>(' . $questions_count . ')</span>'?>
					</a>
				<?php }?>
			</div>
		<?php }?>
	</div>
</div>
