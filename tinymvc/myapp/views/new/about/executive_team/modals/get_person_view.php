<div class="teammate-profile">
    <div class="teammate-profile__sidebar">
        <div class="teammate-profile__image">
            <img class="image" src="<?php echo $person['imageUrl'];?>" alt="User <?php echo $person['name_person'] ?>">
        </div>
        <a class="btn btn-light btn-block teammate-profile__button mt-30" href="tel:<?php echo $person['tel_person'] ?>">
            <i class="ep-icon ep-icon_phone mr-10"></i><?php echo $person['tel_person'] ?>
        </a>
        <a class="btn btn-light btn-block teammate-profile__button mt-15 <?php echo logged_in() ? 'fancybox.ajax fancyboxValidateModal' : 'js-require-logged-systmess';?>" href="<?php echo __SITE_URL . 'our_team/ourteam_popups/email/' . $person['id_person'];?>" data-title="Email <?php echo $person['name_person'] ?>">
            <i class="ep-icon ep-icon_envelope2 mr-10"></i>Email <?php echo $person['name_person'] ?>
        </a>
    </div>
    <div class="teammate-profile__content">
        <h2 class="teammate-profile__name"><?php echo $person['name_person'] ?></h2>
        <div class="teammate-profile__position"><?php echo $person['post_person'] ?></div>
        <div class="ep-middle-text">
            <p><?php echo $person['description'] ?></p>
        </div>
    </div>
</div>
