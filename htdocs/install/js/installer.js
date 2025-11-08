/**
 * ImpressCMS Installer JavaScript
 *
 * Handles installer UI interactions
 *
 * @copyright The ImpressCMS Project http://www.impresscms.org/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 */

jQuery(function ($) {
    'use strict';

    // Initialize stylesheet toggle
    $.stylesheetInit();

    // Theme toggle button
    $('#toggler').bind('click', function (e) {
        $.stylesheetToggle();
        return false;
    });

    // Help button toggle
    $('#help_button').click(function () {
        if ($('div.xoform-help').is(':hidden')) {
            $('div.xoform-help').slideDown('slow');
        } else {
            $('div.xoform-help').slideUp('slow');
        }
    });

    // Page down button
    $('#pagedown').click(function () {
        $.scrollTo('max', 1500);
    });
});

