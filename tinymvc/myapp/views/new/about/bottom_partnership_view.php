<?php $partner_email = config('partner_email');?>

<div class="info-block footer-connect partnership-info-block">
   
   <div class="info-block__info">
       <div class="info-block__title"><?php echo translate('about_us_become_partner_block_title');?></div>
       <p class="info-block__text"><?php echo translate('about_us_become_partner_block_text');?></p>
       <a class="btn btn-outline-dark" href="mailto:<?php echo $partner_email;?>"><?php echo translate('about_us_become_partner_block_email_btn') . ' ' . $partner_email;?></a>
   </div>
   
   <div class="info-block__image">
       <img class="image" src="<?php echo __IMG_URL . 'public/img/footers-info-pages/handshake-meeting.jpg';?>" alt="Trade Partners"> 
   </div>
   
</div>