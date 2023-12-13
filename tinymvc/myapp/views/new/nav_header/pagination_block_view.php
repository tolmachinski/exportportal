<div class="epuser-pagination-from d-none d-sm-flex">
    Page
</div>
<?php

    $data_pagination = array('js-action' => 'saved:pagination');

    if(!empty($status)){
        $data_pagination['status'] = $status;
    }

    if(!empty($type)){
        $data_pagination['type'] = $type;
    }

    echo get_pagination_html(array(
        'first_last_text' => array('first' => '<i class="ep-icon ep-icon_arrows-left"></i>', 'last' => '<i class="ep-icon ep-icon_arrows-right"></i>'),
        'prev_next_text' => array('prev' => '<i class="ep-icon ep-icon_arrow-left"></i>', 'next' => '<i class="ep-icon ep-icon_arrow-right"></i>'),
        'prev_next' => true,
        'first_last' => false,
        'dots' => false,
        'count_total' => $count_total,
        'per_page' => $per_page,
        'cur_page' => $cur_page,
        'parent_element_classes' => 'epuser-pagination',
        'children_element_classes' => 'call-action',
        'data' => $data_pagination,
        'visible_pages' => 1
    ));
?>
<div class="epuser-pagination-from d-none d-sm-flex pl-0">
    <span>of</span> <?php echo ceil($count_total / $per_page);?>
</div>
