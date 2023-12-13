<?php if(!isset($webpackData)){?>
    <link rel="stylesheet" type="text/css" media="screen" href="<?php echo fileModificationTime('public/css/terms.css');?>" />
<?php }?>

<?php if(!isset($terms_in_modal)){?>
<div class="mobile-links">

    <a class="btn btn-primary btn-panel-left fancyboxSidebar fancybox" data-title="Menu" href="#main-flex-card__fixed-right">
        <i class="ep-icon ep-icon_items"></i>
        Menu
    </a>

</div>
<?php }?>

<div class="terms-tinymce-text <?php if(isset($cookie_policy_modal)){?>terms-tinymce-text--modal<?php }?>">
	<?php echo $terms_info['text_block'];?>
</div>
