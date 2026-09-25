/**
 * Core Modal Dialogs
 *
 * @author		Franz S. Evangelista
 * @copyright	Copyright (c) 2005 - 2011, Coreware Technologies, Inc.
 * @since		Version 1.0
 */

// --------------------------------------------------------------------

/**
 * Auto Reposition
 *
 */
$(function () {
    $.ui.dialog.prototype.options.autoReposition = true;
    $(window).resize(centerAllDialog);
});

function centerAllDialog()
{
	$('.ui-dialog-content:visible').each(function () {
		var dialog = $(this).data('dialog');
		if (dialog.options.autoReposition) {
			dialog.option('position', dialog.options.position);
		}
	});
}
// --------------------------------------------------------------------

/**
 * Message Box v2
 *
 * Displays jQuery modal dialog. Overrides default alert() box
 *
 * @param	string	dialog message
 * @param	string	dialog title (if blank ? 'Information')
 * @param	object
 * @return	void
 */
function messageBoxV2(msg, title, buttons) {
	//set default title
    if (!title) {
        var title = 'Information';
    }
	if (!buttons) {
		buttons = {
			OK: function () {
				$(this).dialog('close');
			}
		};
	}
    if ($(MSGBOX).length > 0) {
        $(MSGBOX).html(msg).dialog('option', {
			title: title,
			buttons: buttons
		});
		centerAllDialog();
		$('.ui-dialog-buttonset:last button:first').focus();
    } else {
        $('<div id="' + MSGBOX.substring(1) + '">' + msg + '</div>').dialog({
            title: title,
            show: 'fade',
            hide: 'fade',
            resizable: false,
            draggable: false,
            buttons: buttons,
			width: 'auto',
            close: function () {
				//abortAJAXRequests();
                $(this).remove();
            }
        });
    }
}

// --------------------------------------------------------------------

/**
 * Message Box
 *
 * Displays jQuery modal dialog. Overrides default alert() box
 *
 * @param	string	dialog message
 * @param	string	dialog title (if blank ? 'Information')
 * @param	string	dialog type = (normal = default, confirm)
 * @param	func	dialog ok button function
 * @return	void
 */
function messageBox(msg, title, type, func) {
    var btn = {};
	//set default title
    if (!title) {
        var title = 'Information'
    }
    btn['OK'] = function () {
        if ($.isFunction(func)) {
            func.apply()
        } else {
            $(this).dialog('close')
        }
    };
    if (type === 'confirm') {
        btn['Cancel'] = function () {
            $(this).dialog('close');
            //return false;
        }
    }
    if ($(MSGBOX).length > 0) {
        $(MSGBOX).html(msg).dialog('option', {
			title: title,
			buttons: btn
		});
		
		centerAllDialog();
		$('.ui-dialog-buttonset:last button:first').focus();
    } else {
        $('<div id="' + MSGBOX.substring(1) + '">' + msg + '</div>').dialog({
            title: title,
            show: 'fade',
            hide: 'fade',
            resizable: false,
            draggable: false,
            buttons: btn,
			width: 'auto',
            close: function () {
				//abortAJAXRequests();
                $(this).remove()
            }
        });
    }
}

// --------------------------------------------------------------------

/**
 * Wait Message
 *
 * Displays jQuery wait dialog
 *
 * @param	string	dialog message
 * @param	string	dialog title (default = 'Please wait')
 * @return	void
 */
function waitMessage(msg, title) {
    var loading = '<center><img src="images/connect.gif" width="42" height="32" alt="loading" title="loading" /><br />' + msg + '</center>';
    //set default title
	if (!title) {
        var title = 'Please wait'
    }
	
    if ($(MSGBOX).length > 0) {
        $(MSGBOX).html(loading).dialog('option', {
			title: title,
			buttons: null,
			width: 'auto'
		}).css('max-width', '400px');
		centerAllDialog();
		$('.ui-dialog-buttonset:last button:first').focus();
    } else {
        $('<div id="' + MSGBOX.substring(1) + '">' + loading + '</div>').dialog({
            title: title,
            show: 'fade',
            hide: 'fade',
            resizable: false,
            draggable: false,
            buttons: null,
			width: 250,
            close: function () {
				//abortAJAXRequests();
                $(this).remove();
            }
        });
    }
}

// --------------------------------------------------------------------

/**
 * Modal Dialog
 *
 * Displays jQuery modal dialog
 *
 * @param	string	dialog page
 * @param	string	dialog title (default = 'Information')
 * @param	object	dialog buttons
 * @param	int		width
 * @param	int		height
 * @return	void
 */
function modalDialog(page, title, btn, width, height) {
    var loading = '<center><img src="images/connect.gif" alt="loading" class="loading"></center>';
	//set default params
    if (!title) {
        var title = 'Information'
    }
    if (!width) {
        var width = 200
    }
	if (!height) {
        var height = 'auto'
    }
	//end
	
	if ($(DIALOG).length === 0) {
		$('<div id="' + DIALOG.substring(1) + '">' + loading + '</div>').dialog({
			create: function () {
				$(this).load(page, function () {
					centerAllDialog();
				});
			},
			title: title,
			show: 'fade',
			hide: 'fade',
			modal: false,
			resizable: false,
			draggable: false,
			buttons: btn,
			width: width,
			height: height,
			beforeClose: function () {
				//oTable = null;
				//$(DIALOG).dialog('close');
				$(DIALOG + ' form').validationEngine('hide');
			},
			close: function () {
				$(this).remove();
			}
		});
	}
}

// --------------------------------------------------------------------

/**
 * PDF Viewer
 *
 * reports PDF viewer
 *
 * @param	string	dialog page
 * @param	string	dialog title (default = 'Please wait')
 * @return	void
 */
function pdfViewer(page, title) {
    var loading = '<center><img src="images/connect.gif" alt="loading" class="loading"></center>';
	//set default params
    if (!title) {
        var title = 'Please wait'
    }
	if ($(DIALOG).length === 0) {
		$('<div id="' + DIALOG.substring(1) + '">' + loading + '</div>').dialog({
			create: function () {
				$(this).load(page, function () {
					$(this).dialog('option', 'position', 'center')
				})
			},
			title: title,
			show: 'fade',
			//hide: 'fade',
			modal: false,
			resizable: false,
			draggable: false,
			buttons: {
				'OK': function () {
					$(this).dialog('close');
				}
			},
			width: 270,
			height: 170,
			close: function () {
			   $(this).remove();
			}
		});
	}
}