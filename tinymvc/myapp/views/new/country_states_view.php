    <option value=""><?php echo $placeholder_text;?></option>
    <?php foreach($states as $state){?>
        <option data-name="<?php echo $state['state']?>" value="<?php echo $state['id']?>" <?php if(!empty($selected_state)) echo selected($state['id'],$selected_state);?>><?php echo $state['state']?></option>
    <?php }?>
