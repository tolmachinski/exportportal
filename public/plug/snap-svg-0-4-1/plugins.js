Snap.plugin( function( Snap, Element, Paper, global ) {

    Element.prototype.getCenter = function() {
        var bbox = this.getBBox();
        return {x: bbox.cx, y:bbox.cy};
    };
    Element.prototype.getSize = function() {
        var bbox = this.getBBox();
        return {w: bbox.width, h:bbox.height};
    };
    Element.prototype.getPos = function() {
        var bbox = this.getBBox();
        return {x: bbox.x, y:bbox.y};
    };
    Element.prototype.getTransformRelative = function(relativeObj, type, absolute, xadjust, yadjust) {
        var movex = 0;
        var movey = 0;
        switch (type) {
            case "center":
                var c = relativeObj.getCenter();
                var elpos = this.getPos();
                var elsize = this.getSize();
                var movex = c.x - (elsize.w / 2);
                var movey = c.y - (elsize.h / 2);

                movex = (elpos.x > movex ? 0 - (elpos.x - movex) : movex - elpos.x);
                movey = (elpos.y > movey ? 0 - (elpos.y - movey) : movey - elpos.y);
                break;
            case "topleft":
                var movepos = relativeObj.getPos();
                var elpos = this.getPos();

                movex = (elpos.x > movepos.x ? 0 - (elpos.x - movepos.x) : movepos.x - elpos.x);
                movey = (elpos.y > movepos.y ? 0 - (elpos.y - movepos.y) : movepos.y - elpos.y);
                break;
            case "bottomleft":
                var movepos = relativeObj.getBBox();
                var elpos = this.getPos();

                movex = (elpos.x > movepos.x ? 0 - (elpos.x - movepos.x) : movepos.x - elpos.x);
                movey = (elpos.y > movepos.y2 ? 0 - (elpos.y - movepos.y2) : movepos.y2 - elpos.y);
                break;
            case "topright":
                var movepos = relativeObj.getPos();
                var rsize = relativeObj.getSize();
                var elsize = this.getSize();
                var elpos = this.getPos();

                movex = (elpos.x > movepos.x ? 0 - (elpos.x - movepos.x) : movepos.x - elpos.x);
                movey = (elpos.y > movepos.y ? 0 - (elpos.y - movepos.y) : movepos.y - elpos.y);
                movex += rsize.w - elsize.w;
                break;
            case "bottomright":
                var movepos = relativeObj.getBBox();
                var rsize = relativeObj.getSize();
                var elsize = this.getSize();
                var elpos = this.getPos();

                movex = (elpos.x > movepos.x2 ? 0 - (elpos.x - movepos.x2) : movepos.x2 - elpos.x);
                movey = (elpos.y > movepos.y2 ? 0 - (elpos.y - movepos.y2) : movepos.y2 - elpos.y);
                break;
            case "topcenter":
                var c = relativeObj.getCenter();
                var rpos = relativeObj.getPos();
                var elpos = this.getPos();
                var elsize = this.getSize();
                var movex = c.x - (elsize.w / 2);

                movex = (elpos.x > movex ? 0 - (elpos.x - movex) : movex - elpos.x);
                movey = (elpos.y > rpos.y ? 0 - (elpos.y - rpos.y) : rpos.y - elpos.y);
                break;
            case "bottomcenter":
                var c = relativeObj.getCenter();
                var rpos = relativeObj.getBBox();
                var elpos = this.getPos();
                var elsize = this.getSize();
                var movex = c.x - (elsize.w / 2);

                movex = (elpos.x > movex ? 0 - (elpos.x - movex) : movex - elpos.x);
                movey = (elpos.y > rpos.y2 ? 0 - (elpos.y - rpos.y2) : rpos.y2 - elpos.y);
                break;
            case "leftcenter":
                var c = relativeObj.getCenter();
                var rpos = relativeObj.getPos();
                var elpos = this.getPos();
                var elsize = this.getSize();
                var movey = c.y - (elsize.h / 2);

                movex = (elpos.x > rpos.x ? 0 - (elpos.x - rpos.x) : rpos.x - elpos.x);
                movey = (elpos.y > movey ? 0 - (elpos.y - movey) : movey - elpos.y);
                break;
            case "rightcenter":
                var c = relativeObj.getCenter();
                var rbox = relativeObj.getBBox();
                var elpos = this.getPos();
                var elsize = this.getSize();
                var movey = c.y - (elsize.h / 2);

                movex = (elpos.x > rbox.x2 ? 0 - (elpos.x - rbox.x2) : rbox.x2 - elpos.x);
                movey = (elpos.y > movey ? 0 - (elpos.y - movey) : movey - elpos.y);
                break;
            default:
                console.log("ERROR: Unknown transform type in getTransformRelative!");
                break;
        }

        if (typeof(xadjust) === 'undefined') xadjust = 0;
        if (typeof(yadjust) === 'undefined') yadjust = 0;
        movex = movex + xadjust;
        movey = movey + yadjust;

		return ({'t': (absolute ? "T"+movex+","+movey : "t"+movex+","+movey),'x':movex, 'y':movey});
    };
});


Snap.plugin(function (Snap, Element, Paper, glob) {
 Paper.prototype.multitext = function (x, y, txt, max_width, attributes) {

	var svg = Snap();
	var abc = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
	var temp = svg.text(0, 0, abc);
	temp.attr(attributes);
	var letter_width = temp.getBBox().width / abc.length;
	svg.remove();

	var words = txt.split(" ");
	var width_so_far = 0, current_line=0, lines=[''];
	for (var i = 0; i < words.length; i++) {

	   var l = words[i].length;
	   if (width_so_far + (l * letter_width) > max_width) {
		  lines.push('');
		  current_line++;
		  width_so_far = 0;
	   }
	   width_so_far += l * letter_width;
	   lines[current_line] += words[i] + " ";
	}

	var t = this.text(x,y,lines).attr(attributes);
	t.selectAll("tspan:nth-child(n+2)").attr({
	   dy: "1.2em",
	   x: x
	});
	return t;
 };
});

function describeArc(x, y, radius, startAngle, endAngle){

	var start = polarToCartesian(x, y, radius, endAngle);
	var end = polarToCartesian(x, y, radius, startAngle);

	var largeArcFlag = endAngle - startAngle <= 180 ? "0" : "1";

	var d = [
		"M", start.x, start.y,
		"A", radius, radius, 0, largeArcFlag, 0, end.x, end.y
	].join(" ");

	return d;
}

function polarToCartesian(centerX, centerY, radius, angleInDegrees) {
  var angleInRadians = (angleInDegrees-90) * Math.PI / 180.0;

  return {
	x: centerX + (radius * Math.cos(angleInRadians)),
	y: centerY + (radius * Math.sin(angleInRadians))
  };
}
