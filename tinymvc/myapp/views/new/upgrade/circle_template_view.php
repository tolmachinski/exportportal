<script src="<?php echo fileModificationTime('public/plug/snap-svg-0-4-1/snap.svg.js');?>"></script>
<script src="<?php echo fileModificationTime('public/plug/snap-svg-0-4-1/plugins.js');?>"></script>
<script>
$('head base').remove();

$(document).ready(function () {
	//svg template
	var paper_temp = Snap("#svg-circle-template");
	var path_temp = Snap.path;
	var colorBlue_temp = "#f5f5f5";
	var colorBlueDark_temp = "#f5f5f5";
	var gradientBlue_temp = paper_temp.gradient("l(0%, 0%, 100%, 100%)" + colorBlueDark_temp + "-" + colorBlue_temp).attr({ id: 'gradient-blue'});

	var circleCenterX_temp = $('.svg-circle-template-wr').width()/2;
	var circleCenterY_temp = $('.svg-circle-template-wr').height()/2;
	var circleStroke_temp = 135;
	var startRadius_temp = 0;

	<?php if($type == 8){?>
		var circleRadius_temp = 250;
		var moveCircleY_temp = Math.abs(10 * (Math.sin(67.5 * Math.PI / 180.0)/Math.sin(90 * Math.PI / 180.0)));
		var moveCircleX_temp = Math.abs(Math.sqrt(10 * 10 - moveCircleY_temp * moveCircleY_temp));
		var radiusPlus_temp = 45;

		var circleSegmentsArray = [
			{
				'moveCircleX': +moveCircleX_temp,
				'moveCircleY': -moveCircleY_temp,
			},
			{
				'moveCircleX': +moveCircleY_temp,
				'moveCircleY': -moveCircleX_temp,
			},
			{
				'moveCircleX': +moveCircleY_temp,
				'moveCircleY': +moveCircleX_temp,
			},
			{
				'moveCircleX': +moveCircleX_temp,
				'moveCircleY': +moveCircleY_temp,
			},
			{
				'moveCircleX': -moveCircleX_temp,
				'moveCircleY': +moveCircleY_temp,
			},
			{
				'moveCircleX': -moveCircleY_temp,
				'moveCircleY': +moveCircleX_temp,
			},
			{
				'moveCircleX': -moveCircleY_temp,
				'moveCircleY': -moveCircleX_temp,
			},
			{
				'moveCircleX': -moveCircleX_temp,
				'moveCircleY': -moveCircleY_temp,
			}
		];
	<?php }else if($type == 4){?>
		var circleRadius_temp = 255;
		var moveCircleY_temp = Math.abs(5 * (Math.sin(135 * Math.PI / 180.0)/Math.sin(90 * Math.PI / 180.0)));
		var moveCircleX_temp = Math.abs(Math.sqrt(5 * 5 - moveCircleY_temp * moveCircleY_temp));
		var radiusPlus_temp = 90;

		var circleSegmentsArray = [
				{
					'moveCircleX': +moveCircleX_temp,
					'moveCircleY': -moveCircleY_temp,
				},
				{
					'moveCircleX': +moveCircleY_temp,
					'moveCircleY': +moveCircleX_temp,
				},
				{
					'moveCircleX': -moveCircleX_temp,
					'moveCircleY': +moveCircleY_temp,
				},
				{
					'moveCircleX': -moveCircleY_temp,
					'moveCircleY': -moveCircleX_temp,
				}
		];
	<?php }?>

	createCircleSegment_temp(circleSegmentsArray);
	upload_circle_img_center_temp();

	function createCircleSegment_temp(circleSegmentsArray){
		for(var key in circleSegmentsArray) {
			var segment = paper_temp.path(
				describeArc(circleCenterX_temp + circleSegmentsArray[key].moveCircleX,
						circleCenterY_temp + circleSegmentsArray[key].moveCircleY,
						circleRadius_temp,
						startRadius_temp,
						startRadius_temp += radiusPlus_temp))
				.attr({
					fill: 'none',
					// stroke: gradientBlue_temp,
					stroke: '#f5f5f5',
					strokeWidth: circleStroke_temp
				})
				.addClass('circle-segment');
			paper_temp.g(segment).addClass('circle-group');

		};
	}

	function upload_circle_img_center_temp(){
		var imageEp = paper_temp.image('/public/img/upgrade_page/svg/circle-img-ep.png', circleCenterX_temp-180, circleCenterY_temp-180, 360, 360)
			.attr({id: 'pattern-img-center'});
		var imageEpCircle = paper_temp.circle(circleCenterX_temp, circleCenterY_temp, 185).attr({
			fill: '#fff',
			stroke: '#fff',
			'stroke-width': 200
		})

		paper_temp.g(imageEp).attr({
			mask: imageEpCircle
		}).attr({'id' : 'logo-center'});
	}
	//END svg template
});
</script>
<div class="container-fluid bg-white content-dashboard">
	<div class="svg-circle-template-wr"><svg id="svg-circle-template" x="0px" y="0px" width="100%" height="980" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 1920 980"></svg></div>
</div>
