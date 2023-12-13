<div class="d-none d-block col-xl-2 bd-toc">
    <ul class="nav nav--bg flex-d--c">
        <?php foreach($elements as $element){?>
            <li class="nav-item">
                <span class="nav-link"><?php echo $element['name'];?></span>
                <ul class="nav flex-d--c">
                <?php foreach($element['elements'] as $element_key => $element_item){?>
                    <li class="nav-item"><a class="nav-link" href="#<?php echo $element_item['id'];?>"><?php echo $element_item['title'];?></a></li>
                <?php }?>
                </ul>
            </li>
        <?php }?>        
    </ul>
</div>