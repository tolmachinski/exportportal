<script src="<?php echo fileModificationTime('public/plug/hidemaxlistitem-1-3-4/hideMaxListItem-min.js'); ?>"></script>
<script>
$(function(){
	$('#hideMaxListItemsTest').hideMaxListItems({
		'max': intval('<?php echo config('popular_topics_sidebar_visible_limit', 5);?>'),
	});
})
</script>

<?php views()->display('new/partial_sidebar_search_view', $partial_search_params);?>

<h3 class="minfo-sidebar-ttl">
    <span class="minfo-sidebar-ttl__txt">Popular topics</span>
</h3>
<div class="minfo-sidebar-box">
    <div class="minfo-sidebar-box__desc">
        <ul class="hide-max-list minfo-sidebar-box__list" id="hideMaxListItemsTest" <?php echo addQaUniqueIdentifier('page__topics-detail__popular-topics_list') ?>>
            <?php foreach($topics as $topic_item){?>
            <li class="minfo-sidebar-box__list-item">
                <a class="minfo-sidebar-box__list-link w-160" <?php echo addQaUniqueIdentifier('page__topics-detail__popular-topics_link') ?> <?php echo equals($topic['id_topic'],$topic_item['id_topic'], 'active')?>" href="<?php echo __SITE_URL.'topics/detail/'.strForUrl($topic_item['title_topic']).'/'.$topic_item['id_topic'] ?>">
                    <?php echo $topic_item['title_topic'];?>
                </a>
            </li>
            <?php }?>
        </ul>
    </div>
</div>

<?php views()->display('new/subscribe/subscribe_view'); ?>
