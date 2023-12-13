<?php tmvc::instance()->controller->view->display('new/item/scripts_compare'); ?>

<div class="table-default__main-container pt-50">
    <div class="info-alert-b <?php if (empty($info)) { ?>d-none<?php } ?>">
        <i class="ep-icon ep-icon_info-stroke"></i>
        <span>There are no products selected for comparison.</span>
    </div>
    <?php if (!empty($items)) { ?>
    <div class="table-default__header">
        <div class="table-header__first-col">
            <div class="table-header__dropdown btn-block">
                <a class="btn btn-light btn-block drop-next" title="<?php echo $category_menu['name'];?>" id="category-<?php echo $category_menu['category_id'];?>">
                    <i class="ep-icon ep-icon_categories"></i>
                    <span><?php echo $category_menu['name'];?></span>
                    <i class="ep-icon ep-icon_arrow-down fs-10"></i>
                </a>
                <div class="dropdown-drop">
                    <ul class="dropdown-drop__list">
                        <?php echo $categories; ?>
                    </ul>
                </div>
            </div>
        </div>
        <div class="table-header__column-container">
            <div class="table-header__column-hidden"></div>
            <div class="table-header__overlay-left"></div>
            <div class="table-header__overlay-right"></div>
            <div class="table-header__column-block">
                <?php foreach ($items as $item) {
                    $company = $companies[$item['id_seller']];
                    $company_info = array(
                        'name_company' => $company['company_info'],
                        'type_company' => $company['type_company'],
                        'id_company' => $company['id_company'],
                        'index_name' => $company['index_name']
                    );
                    ?>
                    <div class="table-header__column <?php echo 'category-' . implode(' category-', $c_parents[$item['id_cat']])?>" style="display:<?php echo (in_array($item['id_cat'], $main_categories) ? 'block': 'none')?>">
                        <div class="expand-column">
                            <div class="expand-column__image image-card3">
                                <span class="link">
                                    <?php
                                        $item_img_link = getDisplayImageLink(array('{ID}' => $item['id'], '{FILE_NAME}' => $item['photo_name']), 'items.main', array( 'thumb_size' => 3 ));
                                    ?>
                                    <img
                                        class="image"
                                        src="<?php echo $item_img_link;?>"
                                        alt="<?php echo $item['title'];?>"
                                    >
                                </span>
                                <?php if ($item['discount']){ ?>
                                    <div class="expand-column__discount">- <?php echo $item['discount']; ?>%</div>
                                <?php } ?>
                                <div class="expand-column__delete" data-id="item-<?php echo $item["id"]; ?>" data-category="category-<?php echo $item['id_cat'];?>" title="Delete this column">
                                    <i class="ep-icon ep-icon_remove-stroke "></i>
                                </div>
                            </div>
                            <div class="expand-column__content">
                                <a class="expand-column__name" href="<?php echo makeItemUrl($item['id'], $item['title']); ?>"><?php echo $item['title'];?></a>

                                <div class="expand-column__price-row mt-5">
                                    <div class="expand-column__price products__price-new"><?php echo get_price($item['final_price']);?></div>
                                    <div class="dropdown dropleft">
                                        <?php if ($item['discount']) { ?>
                                            <div class="expand-column__price products__price-old"><?php echo get_price($item['price'])?></div>
                                        <?php } ?>
                                    </div>
                                </div>


                                <div class="expand-column__country mt-5 fs-12 lh-24">
                                    <div class="expand-column__flag">
                                        <img
                                            width="24"
                                            height="24"
                                            class="image"
                                            src="<?php echo getCountryFlag($item['country_name']);?>"
                                            alt="<?php echo $item['country_name'];?>"
                                        >
                                    </div>
                                    <?php echo $item['country_name'];?>
                                </div>
                            </div>
                            <div class="expand-column__container">
                                <div class="expand-column__sliding">
                                    <div class="expand-column__sold">
                                        <span>Sold by</span>
                                        <div class="expand-column__sold-line"></div>
                                    </div>
                                    <div class="expand-column__sold-block">
                                        <a href="<?php echo getCompanyURL($company_info);?>" class="expand-column__company"><?php echo $company['name_company']?></a>
                                        <div class="expand-column__certification-row">
                                            <div class="<?php echo userGroupNameColor($company['user_group_name']);?>">
                                                <?php echo $company['user_group_name']?>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="expand-column__links">
                                        <a class="i-compare dn-md_i call-function" data-callback="compare_item" data-item="<?php echo $item['id'];?>" href="#">
                                            <i class="ep-icon ep-icon_balance "></i>
                                            <span>Compare</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>

    <?php $menu_list = array(); $items_lists = array();
    if (!empty($attrs)) {
        $items_td=array();
        foreach ($attrs as $key=>$attr) {
            $class = array();
            foreach ($items as $kk => $item) {
                if (isset($items_attr_vals[$item['id']][$key])) {
                    $class[] = 'item-'.$item['id'];
                    $items_lists[$item['id']][$item['id_cat']][] = '<div class="table-default__row item-'.$item['id'].'" style="display: none"><p>'.implode(',',$items_attr_vals[$item['id']][$key]).'</p></div>';
                } else {
                    $items_lists[$item['id']][$item['id_cat']][] = '<div class="table-default__row item-'.$item['id'].'" style="display: none"> &mdash; </div>';
                }
            }

            $attr_translations = !empty($attr['translation_data']) ? array_filter(json_decode($attr['translation_data'], true)) : array();
            $attr_name = (__SITE_LANG != 'en' && !empty($attr_translations[__SITE_LANG])) ? $attr_translations[__SITE_LANG]['attr_name'] : $attr['attr_name'];
            $menu_list[] =  '<div class="table-default__row '.implode(' ',$class).'" style="display: none">'
                            .$attr_name.
                            '</div>';
        }
    }

    if (!empty($user_attrs)) {
        $class = array();
        foreach ($items as $item) {
            $class[] = 'item-'.$item['id'];
            if (isset($user_attrs[$item['id']])) {
                $items_lists[$item['id']][$item['id_cat']][] = '<div class="table-default__row item-' . $item['id'] . '" style="display: none"><div>' . implode('',$user_attrs[$item['id']]) . '</div></div>';
            } else {
                $items_lists[$item['id']][$item['id_cat']][] = '<div class="table-default__row item-' . $item['id'] . '" style="display: none">&mdash;</div>';
            }
        }
        $menu_list[] = '<div class="table-default__row '.implode(' ',$class).'" style="display: none" data-second="true">Additional properties</div>';
    }

    if(empty($attrs) && empty($user_attrs)){
        $class = array();
        foreach ($items as $item) {
            $class[] = 'item-'.$item['id'];
            $items_lists[$item['id']][$item['id_cat']][] = '<div class="table-default__row item-' . $item['id'] . ' Wrap" style="display: none">&nbsp;</div>';
        }

        $menu_list[] = '<div class="table-default__row '.implode(' ',$class).'" style="display: none">No options for compare</div>';
    }
    ?>

    <div class="table-default">
        <div class="table-default__column--fixed">
            <?php echo implode(' ', $menu_list); ?>
        </div>
        <div class="table-default__column-container">
            <div class="table-default__column-block">
                <?php foreach ($items_lists as $item_id => $elements) { ?>
                    <?php foreach ($elements as $category => $element) { ?>
                        <div id="item-<?php echo $item_id;?>" class="table-default__column <?php echo 'category-' . implode(' category-', $c_parents[$category])?>" style="display:<?php echo (in_array($category, $main_categories) ? 'block': 'none')?>">
                            <?php echo implode(' ', $element);?>
                        </div>
                    <?php } ?>
                <?php } ?>
            </div>
        </div>
    </div>
    <?php } ?>
</div>
