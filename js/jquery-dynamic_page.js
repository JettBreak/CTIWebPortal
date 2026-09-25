/**
 * Core AJAX Page JS
 *
 * @author		Franz S. Evangelista
 * @copyright	Copyright (c) 2005 - 2011, Coreware Technologies, Inc.
 * @since		Version 1.0
 */

$(function () {
    $('nav a, #menu a').live('click', function () {
        if ($(MSGBOX).length) {
			$(MSGBOX).dialog('close');
		}
        if ((window.location.hash === this.hash) && ($(WRAPPER).is(':hidden') === true)) {
            $(WRAPPER).fadeIn();
        }
    });
    $(window).bind('hashchange', function () {
        //$(DIALOG).dialog('close');
        //clearInterval(intervals);
        //abortAJAXRequests();
        //oTable = null;
        /*if ($('form').length) {
            $(this).validationEngine('hideAll')
        }*/
        var hash = null;
		var baseW = 0;
        var baseH = 0;
        $(WRAPPER).height($(WRAPPER).height());
        baseH = $(WRAPPER).height() - $(WRAPPER).height();
        $(WRAPPER).width($(WRAPPER).width());
        baseW = $(WRAPPER).width() - $(WRAPPER).width();
        hash = window.location.hash.substring(1);
        if (hash) {
            requests.push($.ajax({
                type: 'GET',
                url: hash,
                //cache: true,
                beforeSend: function () {
					clearInterval(intervals);
					clearInterval(ref);
					abortAJAXRequests();
					oTable = [];
					$(MSGBOX).dialog('close');
					$(DIALOG).dialog('option', 'hide', null); //disable fade out
					$(DIALOG).dialog('close');
					$(DTPICKER).datepicker('hide');
					if ($('form').length) {
						$(this).validationEngine('hideAll')
					}
                    $(CONTENT).html('<h1>Loading</h1><div id="content"><div alt="loading" title="loading" class="loader">&nbsp;</div></div>').fadeIn();
                },
                error: function (jqxhr, b, c) {
                    var errormsg;
                    switch (jqxhr.status) {
                    case 404:
                        errormsg = 'Page not found';
                        break;
                    case 500:
                        errormsg = 'Internal server error';
                        break
                    }
                    if (jqxhr.status !== 0) {
                        $(CONTENT).html('<h1>' + jqxhr.status + ' Error</h1><div id="content">An error has occured: ' + errormsg + '</div>').fadeIn()
                    }
                },
                success: function (data) {
                    try {
                        var data = $.parseJSON(data)
                    } catch (e) {}
                    if (data.auth === false) {
                        $(CONTENT).html('<h1>Access Denied</h1><div id="content">' + data['message'] + '</div><script>setTimeout(logOut, 2500)</script> ').hide()
                    } else {
						$(CONTENT).html(data).hide();
                        //$(CONTENT).html('<h1>&nbsp;</h1>').show;
                    }
                    if ($(WRAPPER).is(':hidden') === true) {
						//$(WRAPPER).width('auto')
                        $(WRAPPER).show();
                    }
                    $(WRAPPER).stop().animate({
                        width: baseW + $(CONTENT).width() + 'px',
                        height: baseH + $(CONTENT).height() + 'px'
                    }, {
                        queue: false,
                        duration: 300,
                        complete: function () {
							//$(CONTENT).html(data).hide();
                            $(CONTENT).fadeIn(function () {
								if (oTable !== []) {
									//unbind double click event (table rows) 
    								$(DATATABLE).find('tbody tr').die('dblclick');
									
									if (!$.isArray(oTable)) {
										oTable.fnAdjustColumnSizing();
									}
									
								}
							});
							//oTable.fnAdjustColumnSizing();
                            $(WRAPPER).height('auto');
                        }
                    });
                },
                complete: function () {
                    if (window.location.hash !== '#welcome') {
                        var panel = $('.panel');
                        var menu = $('#floatMenu');
                        menu.animate({
                            left: -menu.width(),
                        }, {
                            duration: 500,
                            queue: false
                        });
                        panel.attr('title', 'Show Panel');
                        panel.toggle(function () {
                            menu.animate({
                                left: 0
                            }, {
                                duration: 500,
                                queue: false
                            });
                            panel.attr('title', 'Hide Panel')
                        }, function () {
                            menu.animate({
                                left: -menu.width()
                            }, {
                                duration: 500,
                                queue: false
                            });
                            panel.attr('title', 'Show Panel')
                        });
                    }
					
                }
            }))
        }
        return false
    });
    $(window).trigger('hashchange');
});