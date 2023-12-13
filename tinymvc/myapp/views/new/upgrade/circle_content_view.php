<script src="<?php echo fileModificationTime('public/plug/snap-svg-0-4-1/snap.svg.js');?>"></script>
<script src="<?php echo fileModificationTime('public/plug/snap-svg-0-4-1/plugins.js');?>"></script>
<script>
$('head base').remove();

$(document).ready(function () {
	var paper = Snap('#svg-circle');
	var path = Snap.path;

	var colorBlue = "#2181F8";
	var colorBlueDark = "#000";
    var colorLightBlue = "#2181F8";

	var circleCenterX = $(".svg-circle-template-wr").width()/2;
	var circleCenterY = $(".svg-circle-template-wr").height()/2;
	var blocksArray = <?php echo json_encode($arrayContent)?>;

	//svg template
	var linkCircleTemplate = "/public/img/upgrade_page/svg/new/circle-template8.svg";

	if(blocksArray.length <= 4)
		linkCircleTemplate = "/public/img/upgrade_page/svg/new/circle-template4.svg";

	Snap.load(linkCircleTemplate, function (f) {
		paper.add(f.select('#logo-center'));
		paper.select('defs').add(f.select('defs').selectAll('filter, linearGradient, pattern, mask'));
		paper.add(f.selectAll('.circle-group'));

		var circleGroup = paper.selectAll('.circle-group');
		for(var key in circleGroup.items) {
			if(blocksArray[key] !== undefined){
				//load img circle
				var img = paper.image('https://www.exportportal.com/public/img/upgrade_page/svg/'+blocksArray[key].imageCircle, 0, 0, 370, 370)
					.pattern(0, 0, 2, 2).attr({patternUnits: '', viewBox: ''});

				var circle = paper.circle(circleCenterX, circleCenterY, 185).attr({
					fill: img,
					"pointer-events": "none",
					opacity: 0,
					stroke: 'none',
					"stroke-width": '1px'
				}).addClass('circle-img');

				circleGroup.items[key].add(circle);
				//END load img circle

				//load icon in segment
				loadIcon(blocksArray[key], circleGroup.items[key]);
				//END load icon in segment
			}
		};
	});

	function loadIcon(blocksArray, circleGroup){
		Snap.load("/public/img/upgrade_page/svg/new/"+blocksArray.iconName+".svg", function (f) {

			var iconInfo = f.select("path");
			iconInfo = paper.path(iconInfo.attr('d')).attr({
				width: 40,
				height: 40,
				fill: "#2f2f2f",
				stroke: "none",
				"pointer-events": "none",
				strokeWidth: 0
			}).addClass('circle-segment__icon');


			if( blocksArray.iconName !== 'exima1'){
				var coord = iconInfo.getTransformRelative(
					circleGroup.select('.circle-segment'),
					'center',
					false,
					0 + blocksArray.moveIconX,
					0 + blocksArray.moveIconY
				);

				iconInfo.transform(coord.t);
				circleGroup.add(iconInfo);
				createDottedLine(circleGroup, blocksArray, iconInfo);
			}else{
				loadIconExima(blocksArray, iconInfo, circleGroup);
			}

		});
	}

	function loadIconExima(blocksArray, iconInfo1, circleGroup){
		var coord = iconInfo1.getTransformRelative(
				circleGroup.select('.circle-segment'),
				'center',
				true,
				-10-blocksArray.moveIconX,
				0+blocksArray.moveIconY
		);

		Snap.load("/public/img/upgrade_page/svg/new/exima2.svg", function (f) {
			var iconInfo2 = f.select("path");
			iconInfo2 = paper.path(iconInfo2.attr('d')).attr({
				width: 40,
				height: 40,
				fill: "#fff",
				stroke: "none",
                zIndex: 5,
				"pointer-events": "none",
				strokeWidth: 0
			});

			var coord = iconInfo2.getTransformRelative(
					circleGroup.select('.circle-segment'),
					'center',
					true,
					9-blocksArray.moveIconX,
					0+blocksArray.moveIconY
			);
			iconInfo2.transform(coord.t);

			circleGroup.add(iconInfo1,iconInfo2);
			createDottedLine(circleGroup, blocksArray, iconInfo1);
		});
        
        iconInfo1.transform(coord.t).attr({ fill: "#ffaf00" });
	}

	function createDottedLine(circleGroup, blocksArray, icon){
		var grCr = paper.g();
		var dottedLine = blocksArray.dottedLine;
		var circlePos;

		switch (dottedLine.pos) {
			case 'top':
				circlePos = 'topcenter';
			break;
			case 'bottom':
				circlePos = 'bottomcenter';
			break;
			case 'right':
				circlePos = 'rightcenter';
			break;
			case 'left':
				circlePos = 'leftcenter';
			break;
		}

		var coord = grCr.getTransformRelative(
			icon,
			circlePos,
			true,
			0+dottedLine.x,
			0+dottedLine.y
		);

		var x = coord.x, y = coord.y;

		//line vertical
		switch (dottedLine.directionVLine+dottedLine.directionHLine) {
			case 'topleft':
				for(i = 0; i < 9; i++){
					grCr.add(paper.circle(x,y,2.5).attr({fill: colorLightBlue, opacity: 0}));
					x-=3;
					y-=10;
				}
			break;
			case 'topright':
				for(i = 0; i < 9; i++){
					grCr.add(paper.circle(x,y,2.5).attr({fill: colorLightBlue, opacity: 0}));
					x+=3;
					y-=10;
				}
			break;
			case 'bottomleft':
				for(i = 0; i < 9; i++){
					grCr.add(paper.circle(x,y,2.5).attr({fill: colorLightBlue, opacity: 0}));
					x-=3;
					y+=10;
				}
			break;
			case 'bottomright':
				for(i = 0; i < 9; i++){
					grCr.add(paper.circle(x,y,2.5).attr({fill: colorLightBlue, opacity: 0}));
					x+=3;
					y+=10;
				}
			break;
		}

		//line horizontal
		switch (dottedLine.directionHLine) {
			case 'left':
				for(i = 0; i < 5; i++){
					grCr.add(paper.circle(x,y,2.5).attr({fill: colorLightBlue, opacity: 0}));
					x-=10;
				}
				x-=5;
			break;
			case 'right':
				for(i = 0; i < 5; i++){
					grCr.add(paper.circle(x,y,2.5).attr({fill: colorLightBlue, opacity: 0}));
					x+=10;
				}

				x+=5;
			break;
		}

		grCr.add(paper.circle(x,y,7).attr({fill: colorLightBlue, opacity: 0})).attr({"pointer-events": "none"}).addClass('circle-line');
		circleGroup.add(grCr);

		createTextBlock(circleGroup, x, y, blocksArray.text);
	}

	function createTextBlock(circleGroup, x, y, text){

		var attributes = { "font-size": "16px", 'fill': '#888888' };
		var textGr;
		y += 8;

		switch(text.pos){
			case 'right':
				x += 20;
				var ttl = paper.text(x, y, text.ttl).attr({"font-size": "18px", "font-weight": "bold", 'fill': colorBlueDark});
			break;
			case 'left':
				x -= 20;
				attributes = ({"font-size": "16px", "text-anchor": 'end', 'fill': '#888888'});
				var ttl = paper.text(x, y, text.ttl).attr({"font-size": "18px", "font-weight": "bold", "text-anchor": 'end', 'fill': colorBlueDark});
			break;
		}

		y += 30;

		var txt = paper.multitext(x, y,
				text.txt,
				435,
				attributes );
		textGr = paper.g(ttl, txt).attr({"pointer-events": "none", 'opacity': 1.0}).addClass('circle-text');
		circleGroup.add(textGr);
	}
	//END svg template
});
</script>
<div class="container-fluid bg-white content-dashboard">
	<div class="svg-circle-template-wr"><svg id="svg-circle" x="0px" y="0px" width="100%" height="980" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 1920 980"></svg></div>
</div>
