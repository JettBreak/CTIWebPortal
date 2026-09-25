/**
 * Constants/Globals Initializer
 *
 * @author		Franz S. Evangelista
 * @copyright	Copyright (c) 2005 - 2011, Coreware Technologies, Inc.
 * @since		Version 1.0
 */

var requests = [],
    oTable = [],
    CONTENT = '#form',
    WRAPPER = '#wrapper',
    DATATABLE = '.dataTable',
	DTLENGTHMENU = [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']], // -1 = 'All'
    TABCONTENT = '.tab_content',
    TABS = 'ul.tabs li',
    TABSActive = 'ul.tabs li.active',
    MSGBOX = '#messageBox',
    DIALOG = '#modalDialog',
    DTPICKER = '.datePicker',
    intervals = null,
	ref = null,
	vCodes = [],
    TIMEOUT = 60 * 60 * 1000;//1 min
