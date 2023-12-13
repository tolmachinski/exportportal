<script src="<?php echo fileModificationTime('public/plug/snap-svg-0-4-1/snap.svg.js');?>"></script>
<script src="<?php echo fileModificationTime('public/plug/snap-svg-0-4-1/plugins.js');?>"></script>
<script>
    //IMPORTANT for work svg fill in safari
    $('head base').remove();

    $(function() {
        var gradientBlue = __files_url + "public/img/upgrade_page/svg/new/circle-content8.svg";
        if(gradientBlue != undefined){
            var paper = Snap("#svg-circle");
            var path = Snap.path;
            var colorGray = "#f5f5f5";
            var colorWhite = "#ffffff";
            var colorBlack = "#000000";
            var colorBlue = "#2181F8";
            var colorBlueDark = "#2181F8";
            var gradientGreen = paper.gradient("l(0%, 0%, 100%, 100%)#2181F8-#2181F8").attr({ id: 'gradient-green'});
            var gradientBlue;


            Snap.load(gradientBlue, function (f) {
                gradientBlue = f.select('#gradient-blue');

                paper.add(f.select('#logo-center'));
                paper.select('defs').add(f.select('defs').selectAll('filter, linearGradient, pattern, mask'));
                paper.add(f.selectAll('.circle-group'));

                var circleGroup = paper.selectAll('.circle-group');

                for(var key in circleGroup.items) {
                    //show dotted line
                    clickElement(circleGroup.items[key], circleGroup);
                    //END show dotted line
                };

            });

            function clickElement(el, circleGroup){

                el.click(function () {
                    for(var key in circleGroup.items) {
                        var $this = circleGroup.items[key];
                        var $icon = $this.select('.circle-segment__icon:not(.circle-segment__icon-not-change)');
                        if($icon){
                            $icon.attr({ fill: colorBlack });
                        }
                        $this.select('.circle-segment').attr({ stroke: colorGray });
                        $this.select('.circle-img').animate({opacity: 0}, 300, mina.easeinout);
                        actionDottedline($this.select('.circle-line').selectAll('circle'), $this.select('.circle-text'), 'hide');
                    }

                    el.select('.circle-segment').attr({ stroke: colorBlue });
                    var $icon = el.select('.circle-segment__icon:not(.circle-segment__icon-not-change)');
                    if($icon){
                        $icon.attr({ fill: colorWhite });
                    }
                    el.select('.circle-img').animate({opacity: 1}, 300, mina.easeinout);

                    setTimeout(function(){
                        actionDottedline(el.select('.circle-line').selectAll('circle'), el.select('.circle-text'), 'show');
                    },200);
                });
            }

            function actionDottedline(elems, elText, type){
                var dotted = elems.items;
                var last = dotted.length-1;

                for(var key in dotted) {
                    timeActionDottedline(dotted[key], key, last, elText, type);
                }
            }

            function timeActionDottedline(el,key, last, elText, type){
                if(type == 'show'){
                    setTimeout(function(){
                        el.attr({opacity: 1});

                        if(key == last)
                            elText.animate({opacity: 1}, 200);
                    },25*key);
                }else{
                    setTimeout(function(){
                        el.attr({opacity: 0});

                        if(key == last)
                            elText.animate({opacity: 1.0}, 200);
                    },26*key);
                }
            }
        }
    });
</script>

<h2 class="upgrade-large-title"><?php echo translate('upgrade_benefits_title_1'); ?> <strong class="upgrade-large-title__subtitle"><?php echo translate('upgrade_benefits_title_2'); ?></strong></h2>

<div class="upgrade-circle">
    <div
        id="svg-circle-wr"
    >
        <svg
            id="svg-circle"
            x="0px"
            y="0px"
            width="100%"
            style="min-height: 600px;"
            version="1.1"
            xmlns="http://www.w3.org/2000/svg"
            xmlns:xlink="http://www.w3.org/1999/xlink"
            viewBox="0 0 1920 980"
        ></svg>
    </div>
</div>

<?php views()->display('new/upgrade/upgrade_membership_view');?>
