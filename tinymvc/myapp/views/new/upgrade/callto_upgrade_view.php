<script>
    var scrollToBlock = function(btn){
        var $this = $(btn);
        var block = $this.data('block');
        scrollToElement(block, -1);
    };

    <?php if($callModal > 0){?>
        $(function(){
            setTimeout(function(){
                var $callUpgrad = $('.js-call-upgrade-modal');
                if($callUpgrad.length){
                    $callUpgrad.first().trigger('click');
                }
            }, 300);
        });
    <?php }?>
</script>

<?php views()->display('new/upgrade/header_become_view');?>

<div class="content-upgrade">
    <?php views()->display('new/upgrade/packages_view');?>
    <?php views()->display('new/upgrade/upgrade_circle_view');?>
</div>
