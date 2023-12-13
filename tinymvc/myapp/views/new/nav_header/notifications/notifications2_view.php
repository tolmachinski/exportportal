<?php
    echo dispatchDynamicFragmentInCompatMode(
        "popup:notification",
        asset('public/plug/js/notifications/notifications.js', 'legacy')
    );
?>

<div id="js-epuser-notifications2" class="epuser-subline-wr inputs-40">
	<?php
		tmvc::instance()->controller->view->display('new/nav_header/notifications/notifications2_block_view');
	?>
</div>
