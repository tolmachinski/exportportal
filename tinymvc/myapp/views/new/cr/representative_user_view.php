<div class="ambassador-block">
    <div class="ambassador-block__wr">
        <div class="ambassador-block__img">
            <img src="<?php echo getDisplayImageLink(array('{ID}' => $cr_user['idu'], '{FILE_NAME}' => $cr_user['user_photo']), 'users.main', array( 'thumb_size' => 1, 'no_image_group' => $cr_user['user_group'] ));?>" alt="<?php echo $cr_user['user_name'];?>">
        </div>
        
        <a class="ambassador-block__name" href="<?php echo get_dynamic_url('country_representative/'.strForUrl($cr_user['user_name'].' '.$cr_user['idu']));?>" target="_blank"><?php echo $cr_user['user_name'];?></a>
        
        <div class="ambassador-block__position"><?php echo $cr_user['user_city'];?></div>

        <?php if($cr_user['logged']){?>
            <div class="ambassador-block__status">
                <i class="ep-icon ep-icon_circle txt-green"></i>
                Online
            </div>
        <?php }else{?>
            <div class="ambassador-block__status">
                <i class="ep-icon ep-icon_circle txt-red"></i>
                Offline
            </div>
        <?php }?>

        <div class="dropdown">
            <a class="dropdown-toggle fs-18 txt-medium" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Connect
            </a>

            <div class="dropdown-menu dropdowntop-menu-center">
                <span class="dropdown-item">
                    <span class="txt-gray">Email:</span> <?php echo antispambot($cr_user['email'], 'd-inline-flex flex-jc--fe'); ?>
                </span>
                <?php $user_contacts = json_decode($cr_user['user_contacts'], true);?>
                <?php if(!empty($user_contacts)){?>
                    <?php foreach($user_contacts as $user_contact_item){?>
                        <span class="dropdown-item">
                            <span class="txt-gray"><?php echo $user_contact_item['name'];?>:</span> <?php echo antispambot($user_contact_item['value'], 'd-inline-flex flex-jc--fe');?>
                        </span>
                    <?php }?>
                <?php }?>
            </div>
        </div>
    </div>
</div>