
/** apycom menu ****************/
jQuery(function () {
    var $ = jQuery;
    $.fn.retarder = function (delay, method) {
        var node = this;
        if (node.length) {
            if (node[0]._timer_) {
                clearTimeout(node[0]._timer_);
            }
            node[0]._timer_ = setTimeout(function () {
                method(node);
            }, delay);
        }
        return this;
    };
    //$("#menu").addClass("js-active");
    $("ul div", "#menu").css("visibility", "hidden");
    $(".menu > li", "#menu").hover(function () {
        var ul = $("div:first", this);
        if (ul.length) {
            if (!ul[0].hei) {
                ul[0].hei = ul.height();
            }
            ul.css({
                height: 20,
                overflow: "hidden"
            }).retarder(1, function (i) {
                i.css("visibility", "visible").animate({
                    height: ul[0].hei
                }, {
                    duration: 500,
                    complete: function () {
                        ul.css("overflow", "visible");
                    }
                });
            });
        }
    }, function () {
        var ul = $("div:first", this);
		if (ul.length) {
			var css = {
				visibility: "hidden",
				height: ul[0].hei
			};
			ul.stop().css("overflow", "hidden").retarder(50, function (i) {
				i.animate({
					height: 0
				}, {
					duration: 100,
					complete: function () {
						$(this).css(css);
					}
				});
			});
		}
    });
    /*$("ul ul li", "#menu").hover(function () {
        var ul = $("div:first", this);
        if (ul.length) {
            if (!ul[0].wid) {
                ul[0].wid = ul.width();
            }
            ul.css({
                width: 0,
                overflow: "hidden"
            }).retarder(100, function (i) {
                i.css("visibility", "visible").animate({
                    width: ul[0].wid
                }, {
                    duration: 500,
                    complete: function () {
                        ul.css("overflow", "visible");
                    }
                });
            });
        }
    });*/
    /*var links = $(".menu>li>a, .menu>li>a span", "#menu").css({
        background: "none"
    });*/
	if ($.browser.msie && $.browser.version.substr(0, 1) == '6') {
        $('ul ul a', '#menu').css({
            color: 'rgb(255,255,255)'
        }).hover(function () {
            $(this).find('span').animate({
                color: 'rgb(7,179,225)'
            });
        }, function () {
            $(this).find('span').animate({
                color: 'rgb(255,255,255)'
            });
        });
    } else {
        $('ul ul a', '#menu').css({
            color: 'rgb(255,255,255)'
        }).hover(function () {
			
            $(this).find('span').animate({
                color: 'rgb(7,179,225)'
            }, 300);
			
        }, function () {
		
            $(this).find('span').animate({
                color: 'rgb(255,255,255)'
            }, 200);
			
        });
    }
});