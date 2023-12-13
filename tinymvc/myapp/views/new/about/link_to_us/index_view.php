<script type="text/javascript" src="<?php echo fileModificationTime('public/plug/clipboard-2-0-1/clipboard.js');?>"></script>

<div class="mobile-links">
    <a class="btn btn-primary btn-panel-left fancyboxSidebar fancybox" data-title="Menu" href="#about-flex-card__fixed-left">
        <i class="ep-icon ep-icon_items"></i>
        <?php echo translate('label_menu');?>
    </a>

    <a class="btn btn-primary btn-panel-left fancyboxSidebar fancybox" data-title="Sidebar" href="#main-flex-card__fixed-right">
        <i class="ep-icon ep-icon_items"></i>
        <?php echo translate('label_sidebar');?>
    </a>
</div>

<div class="partnership">
    <div class="link_to_us">

        <div class="link_to_us__text-block link_to_us__text-block__head">
            <h2 class="link_to_us__title"><?php echo translate('about_us_link_to_us_header') ?></h2>
            <p><?php echo translate('about_us_link_to_us_block_1');?></p>
        </div>

        <div class="row">
            <div class="col-12 col-lg-6">
                <label class="input-label"><?php echo translate('about_us_link_to_us_label_banner_type') ?></label>
                <select id="banner-type" class="minfo-form__input2" name="category">
                    <?php foreach ($banner_types as $type) {?>
                        <option value="<?php echo $type['id'];?>"><?php echo $type['type_name'];?></option>
                    <?php }?>
                </select>
            </div>
            <div class="col-12 col-lg-6">
                <label class="input-label"><?php echo translate('about_us_link_to_us_label_template') ?></label>
                <?php foreach ($banners_by_type as $type => $banners) {?>
                    <select id="banner-templates-<?php echo $type ?>" class="minfo-form__input2 display-n __banner-templates" name="category">
                        <?php foreach ($banners as $banner) {?>
                            <option value="<?php echo $banner['id'];?>"><?php echo $banner['name'];?></option>
                        <?php }?>
                    </select>
                <?php }?>
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-lg-4">
                <label class="input-label"><?php echo translate('about_us_link_to_us_label_width');?></label>
                <input type="text" class="__banner-option validate[required]" data-option="width" name="subject" placeholder="Choose width">
            </div>
            <div class="col-12 col-lg-4">
                <label class="input-label"><?php echo translate('about_us_link_to_us_label_height');?></label>
                <input type="text" class="__banner-option validate[required]" data-option="height" name="subject" placeholder="Choose height">
            </div>
            <div class="col-12 col-lg-4">
                <label class="input-label"><?php echo translate('about_us_link_to_us_label_bgcolor');?></label>
                <input type="text" class="__banner-option validate[required]" data-option="background-color" name="subject" placeholder="Choose color">
            </div>
        </div>

        <ul class="nav nav-tabs nav--borders" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" href="#banner-preview" aria-controls="title_1" role="tab" data-toggle="tab"><?php echo translate('about_us_link_to_us_label_preview');?></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#banner-code" aria-controls="title_2" role="tab" data-toggle="tab"><?php echo translate('about_us_link_to_us_label_code');?></a>
            </li>
        </ul>

        <div class="tab-content tab-content--borders">
            <div role="tabpanel" class="tab-pane fade show active" id="banner-preview">
                <?php foreach ($banners_list as $banner) {?>
                    <div id="preview-banner-<?php echo $banner['id'];?>" class="display-n">
                        <div class="ep-link-to-us"><?php echo $banner['html_banner'];?></div>
                    </div>
                <?php }?>
            </div>
            <div role="tabpanel" class="tab-pane fade" id="banner-code">
                <div class="tabpanel-code-background">
                    <?php foreach ($banners_list as $banner) {?>
                        <code id="code-banner-<?php echo $banner['id'];?>" class="tabpanel-code display-n">
                            &lt;div class="ep-link-to-us" style=""&gt;
                                <?php echo htmlspecialchars($banner['html_banner']) ?>
                            &lt;/div&gt;
                        </code>
                    <?php }?>
                </div>
                <div>
                    <button id="clipboard-copy-code" class="btn btn-primary w-230 mt-10" data-clipboard-action="copy" data-clipboard-target="#banner-code:not(.display-n)">
                        <?php echo translate('about_us_link_to_us_code_tab_copy_code_btn');?>
                    </button>
                </div>
            </div>
        </div>

        <div class="link_to_us__text-block link_to_us__text-block__foot">
            <h2 class="link_to_us__title"><?php echo translate('about_us_members_protect_block_2_header');?></h2>
            <p><?php echo translate('about_us_members_protect_block_2');?></p>
        </div>

    </div>
</div>

<script>
    $(function() {
        var active_type = $('#banner-type').children('option:selected').val();
        var active_banner = $('#banner-templates-' + active_type).children('option:selected').val();

        $('#banner-templates-' + active_type).removeClass('display-n');
        $('#preview-banner-' + active_banner).removeClass('display-n');
        $('#code-banner-' + active_banner).removeClass('display-n');

        var clipboard = new ClipboardJS('#clipboard-copy-code');


        $('#banner-type').on('change', function() {
            var new_type = $(this).children('option:selected').val();

            $('#banner-templates-' + active_type).addClass('display-n');
            $('#banner-templates-' + new_type).removeClass('display-n').trigger('change');

            active_type = new_type;
        });

        $('.__banner-templates').on('change', function() {
            var new_banner = $(this).children('option:selected').val();

            $('#preview-banner-' + active_banner).addClass('display-n');
            $('#preview-banner-' + new_banner).removeClass('display-n');

            $('#code-banner-' + active_banner).addClass('display-n');
            $('#code-banner-' + new_banner).removeClass('display-n');

            active_banner = new_banner;
        });

        $('.__banner-option').on('change', function() {
            $this = $(this);

            $('#preview-banner-' + active_banner).find('div').css($this.data('option'), $this.val());
            $('#code-banner-' + active_banner).text($('#preview-banner-' + active_banner).html());
        });
    });
</script>