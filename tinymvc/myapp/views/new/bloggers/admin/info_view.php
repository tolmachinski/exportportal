<div class="js-modal-flex wr-modal-flex inputs-40">
    <div class="modal-flex__content w-700">
        <div class="row">
            <div class="col-12 mt-20">
                <div class="img-b tac pull-left mr-10 w-55 h-40 relative-b">
                    <img class="mw-55 mh-40 img-position-center" src="<?php echo $info['applicant_photo']; ?>" alt="<?php echo $info['applicant_fullname']; ?>"/>
                </div>
                <div class="text-b pull-left">
                    <div class="top-b lh-20 clearfix">
                        <strong>
                            <?php echo cleanOutput($info['applicant_fullname']); ?>
                        </strong>
                    </div>
                    <div class="top-b lh-20 clearfix">
                        <a href="mailto:<?php echo cleanOutput($info['applicant_email']); ?>">
                            <?php echo cleanOutput($info['applicant_email']); ?>
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-12 mt-20">
                <label class="input-label">Country</label>
                <div class="form-control h-100pr"><?php echo cleanOutput($info["applicant_country"]);?></div>
            </div>
            <div class="col-12 mt-20">
                <label class="input-label">About</label>
                <div class="form-control h-auto"><?php echo !empty($info["applicant_about"]) ? cleanOutput($info["applicant_about"]) : '-'; ?></div>
            </div>
            <div class="col-12 mt-20">
                <label class="input-label">Strengths</label>
                <div class="form-control h-auto"><?php echo !empty($info["applicant_strengths"]) ? cleanOutput($info["applicant_strengths"]) : '-'; ?></div>
            </div>
            <div class="col-12 mt-20">
                <label class="input-label">Hobbies</label>
                <div class="form-control h-auto"><?php echo !empty($info["applicant_hobbies"]) ? cleanOutput($info["applicant_hobbies"]) : '-'; ?></div>
            </div>
            <div class="col-12 mt-20">
                <label class="input-label">Work example(s)</label>
                <?php if(!empty($info["applicant_work_example_link"])) { ?>
                    <div class="form-control h-auto flex-display flex-ai--c flex-jc--sb">
                        <span id="work-example-link"><?php echo cleanOutput($info["applicant_work_example_link"]); ?></span>
                        <a class="btn btn-sm btn-primary btn-clipboard-copy" data-clipboard-target="#work-example-link">
                            <i class="ep-icon ep-icon_file-copy "></i>
                        </a>
                    </div>
                <?php } else { ?>
                    <div class="form-control h-100pr">-</div>
                <?php } ?>
            </div>
            <div class="col-12 mt-20">
                <label class="input-label">Portfolio</label>
                <?php if(!empty($info["applicant_portfolio_link"])) { ?>
                    <div class="form-control h-auto flex-display flex-ai--c flex-jc--sb">
                        <span id="portfolio-link"><?php echo cleanOutput($info["applicant_portfolio_link"]); ?></span>
                        <a class="btn btn-sm btn-primary btn-clipboard-copy" data-clipboard-target="#portfolio-link">
                            <i class="ep-icon ep-icon_file-copy "></i>
                        </a>
                    </div>
                <?php } else { ?>
                    <div class="form-control h-100pr">-</div>
                <?php } ?>
            </div>
            <div class="col-12 mt-20">
                <label class="input-label">Has interview opportunity</label>
                <div class="form-control h-100pr">
                    <?php echo (bool) $info["applicant_has_interview_opportunity"] ? 'Yes' : 'No'; ?>
                </div>
            </div>
            <div class="col-12 mt-20">
                <label class="input-label">Has interview experience</label>
                <div class="form-control h-100pr">
                    <?php echo (bool) $info["applicant_has_interview_experience"] ? 'Yes' : 'No'; ?>
                </div>
            </div>
            <div class="col-12 mt-20">
                <label class="input-label">Social media pages</label>
                <div class="form-control h-auto">
                    <?php if(!empty($info["applicant_media_pages"])) { ?>
                        <?php foreach ($info['applicant_media_pages'] as $service_key => $service) { ?>
                            <div class="h-auto mt-5 mb-5">
                                <i class="input-label-icon ep-icon ep-icon_<?php echo $service_key; ?> ep-icon_<?php echo $service_key; ?>-square fs-18 mr-10"></i>
                                <a href="<?php echo cleanOutput($service['url']); ?>" target="_blank"><?php echo cleanOutput($service['url']); ?></a>
                            </div>
                        <?php } ?>
                    <?php } else { ?>
                        -
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-flex__btns h-60"></div>
</div>
<script type="application/javascript">
    $(function() {
        var clipboard = new ClipboardJS('.btn-clipboard-copy');
        clipboard.on('success', function(e) {
            systemMessages("Link has been copied to clipboard", 'success');
        });
    });
</script>
