<?php if (!empty($channel)) {?>
    <h3 class="minfo-sidebar-ttl">
        <span class="minfo-sidebar-ttl__txt">Active Filters</span>
    </h3>

    <div class="minfo-sidebar-box">
        <div class="minfo-sidebar-box__desc">
            <ul class="minfo-sidebar-params">
                <li class="minfo-sidebar-params__item">
                    <div class="minfo-sidebar-params__ttl">
                        <div class="minfo-sidebar-params__name">Channel:</div>
                    </div>

                    <ul class="minfo-sidebar-params__sub">
                        <li class="minfo-sidebar-params__sub-item">
                            <div class="minfo-sidebar-params__sub-ttl"><?php echo $channel;?></div>
                            <a class="minfo-sidebar-params__sub-close ep-icon ep-icon_remove-stroke" href="<?php echo $reset_channel_link;?>"></a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a class="btn btn-light btn-block txt-blue2" href="<?php echo $reset_channel_link;?>">Clear all</a>
                </li>
            </ul>
        </div>
    </div>
<?php }?>
<?php views()->display('new/partial_sidebar_search_view');?>