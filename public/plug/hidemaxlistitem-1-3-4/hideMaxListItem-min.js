// Hide Max List Items - v1.34 - Minified
(function ($) {
    $.fn.extend({
		hideMaxListItems: function (options) {
			var defaults = {
				max: 5,
                moreText: 'Show More',
		        lessText: 'Show Less',
                moreHTML: '<div class="maxlist-more mt-20"><button class="btn btn-light btn-block" type="button"></button></div>'
			};
			var options = $.extend(defaults, options);

			return this.each(function () {
				var op = options;
				var totalListItems = $(this).children().length;
				if ((totalListItems > 0) && (totalListItems > op.max)) {
					$(this).children().each(function (index) {
						if ((index + 1) > op.max) {
							$(this).hide(0);
							$(this).addClass("maxlist-hidden")
						}
					});
					var howManyMore = totalListItems - op.max;
					var newMoreText = op.moreText;
					var newLessText = op.lessText;
					if (howManyMore > 0) {
						newMoreText = newMoreText.replace("[COUNT]", howManyMore);
						newLessText = newLessText.replace("[COUNT]", howManyMore)
					}
					$(this).after(op.moreHTML);
					$(this).next(".maxlist-more").find("button").text(newMoreText);
					$(this).next(".maxlist-more").find("button").click(function (e) {
						var listElements = $(this).parent().prev().children();
						listElements = listElements.slice(op.max);
						if ($(this).text() == newMoreText) {
							$(this).text(newLessText);
							var i = 0;
								$(listElements).show()
						} else {
							$(this).text(newMoreText);
							var i = listElements.length - 1;
								$(listElements).hide()
						}
						e.preventDefault()
					})
				}
			})
		}
	})
})(jQuery);
