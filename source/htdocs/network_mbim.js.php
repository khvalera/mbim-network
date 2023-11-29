<?php

/**
 * Network MBIM ajax helper.
 *
 * @category   apps
 * @package    network_mbim
 * @subpackage javascript
 * @author     Khomenko V.V. <khvalera@ukr.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       https://github.com/khvalera/network_mbim
 */

///////////////////////////////////////////////////////////////////////////////
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
///////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////
// B O O T S T R A P
///////////////////////////////////////////////////////////////////////////////

$bootstrap = getenv('CLEAROS_BOOTSTRAP') ? getenv('CLEAROS_BOOTSTRAP') : '/usr/clearos/framework/shared';
require_once $bootstrap . '/bootstrap.php';

///////////////////////////////////////////////////////////////////////////////
// T R A N S L A T I O N S
///////////////////////////////////////////////////////////////////////////////

clearos_load_language('base');
clearos_load_language('network');

///////////////////////////////////////////////////////////////////////////////
// J A V A S C R I P T
///////////////////////////////////////////////////////////////////////////////

header('Content-Type:application/x-javascript');
?>

$(document).ready(function() {

    // Translations
    //-------------

    lang_yes = '<?php echo lang("base_yes"); ?>';
    lang_no = '<?php echo lang("base_no"); ?>';
    lang_save = '<?php echo lang("base_save"); ?>';
    lang_close = '<?php echo lang("base_close"); ?>';
    lang_unknown = '<?php echo lang("base_unknown"); ?>';
    lang_megabits_per_second = '<?php echo lang("base_megabits_per_second"); ?>';
    lang_kilobits_per_second = '<?php echo lang("base_kilobits_per_second"); ?>';
    lang_offline = '<?php echo lang("network_offline"); ?>';
    lang_waiting = '<?php echo lang("base_waiting"); ?>';
    lang_warning = '<?php echo lang("base_warning"); ?>';
    lang_connected = '<?php echo lang("network_connected"); ?>';
    lang_dns_failed = '<?php echo lang("network_dns_lookup_failed"); ?>';
    lang_dns_passed = '<?php echo lang("network_dns_lookup_passed"); ?>';
    lang_dns_lookup = '<?php echo lang("network_dns_lookup"); ?>';
    lang_run_speed_test = '<?php echo lang("network_run_speed_test"); ?>';
    lang_speed_test = '<?php echo lang("network_speed_test"); ?>';
    lang_test_again = '<?php echo lang("network_test_again"); ?>';
    lang_result_saved = '<?php echo lang("network_speed_test_results_saved"); ?>';

    // Defaults
    //---------

    $('#modem_status_field').hide();

    // Sidebar report
    //---------------

    if ($('#network_status_label').length != 0) {
        getDnsStatusInfo();
    }

    // DNS details and interfaces
    //---------------------------

    if (($('#dns_auto_text').length != 0) || ($('#network_summary').length != 0))
        getAllNetworkInfo();
});

/**
 * Ajax call to get network information for all interfaces
 */

function getAllNetworkInfo() {

    $.ajax({
        url: '/app/network/get_all_info',
        method: 'GET',
        dataType: 'json',
        success : function(payload) {
            showAllNetworkInfo(payload);
            window.setTimeout(getAllNetworkInfo, 3000);
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            window.setTimeout(getAllNetworkInfo, 3000);
        }
    });
}

/**
 * Ajax call for network status information.
 */

function getDnsStatusInfo() {

    $.ajax({
        url: '/app/network/get_dns_status_info',
        method: 'GET',
        dataType: 'json',
        success : function(payload) {
            showDnsStatusInfo(payload);
            window.setTimeout(getDnsStatusInfo, 3000);
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            window.setTimeout(getDnsStatusInfo, 3000);
        }
    });
}

/**
 * Updates network information (IP, link) for all interfaces
 */

function showAllNetworkInfo(payload) {

    // DNS server details
    //-------------------

    if (payload['dns_servers'].length == 0) {
        $('#dns_auto_text').html('<span class="theme-loading-small">' + lang_waiting + '</span>');
        $('#dns_auto_field').show();
        $('#dns0_field').hide();
        $('#dns1_field').hide();
    } else if (payload['dns_servers'].length == 1) {
        $('#dns_auto_text').html('');
        $('#dns_auto_field').hide();
        $('#dns0_field').show();
        $('#dns1_field').hide();
    } else {
        $('#dns_auto_text').html('');
        $('#dns_auto_field').hide();
        $('#dns0_field').show();
        $('#dns1_field').show();
    }

    for (dns_index in payload['dns_servers']) {
        var dns_html_index = dns_index + 1;
        $('#dns' + dns_index + '_text').html(payload['dns_servers'][dns_index]);
    }
}

/**
 * Shows DNS status information.
 */

function showDnsStatusInfo(payload) {

    var dns_status_message = '';

    if (payload['dns_status'] == 'online') {
        dns_status_message = '<span class=\'theme-text-good-status\'>' + lang_connected + '</span>';

        // Are we in wizard?
        if ($('#dns_test_message').length != 0) {
            $('#dns_test_message_container').replaceWith(clearos_infobox_success(lang_success, lang_dns_passed));
            clearos_modal_infobox_close('wizard_next_showstopper');
            $('#wizard_next_showstopper').remove();
            // hack to remove modal backdrop
            $('.modal-backdrop').remove();
        }
    } else {
        dns_status_message = '<span class=\'theme-text-bad-status\'>' + lang_offline + '</span>';

        if ($('#dns_test_message').length != 0) {
            $('#dns_test_message_container').replaceWith(clearos_infobox_warning(lang_warning, lang_dns_failed));
            $('#dns_edit_anchor').show();
        }
    }

    $('#dns_status_text').html(dns_status_message);
}

/**
 * Shows network status information.
 */

function showNetworkStatusInfo(payload) {

console.log('wtf');
    var connection_status_message = '';
    var gateway_status_message = '';

    if (payload['connection_status'] == 'online')
        connection_status_message = '<span class=\'theme-text-good-status\'>' + lang_connected + '</span>';
    else if (payload['connection_status'] == 'online_no_dns')
        connection_status_message = '<span class=\'theme-text-good-status\'>' + lang_connected + '</span>';
    else
        connection_status_message = '<span class=\'theme-text-bad-status\'>' + lang_offline + '</span>';

    if (payload['gateway_status'] == 'online')
        gateway_status_message = '<span class=\'theme-text-good-status\'>' + lang_connected + '</span>';
    else
        gateway_status_message = '<span class=\'theme-text-bad-status\'>' + lang_offline + '</span>';

    $('#network_status').html(connection_status_message);
    $('#gateway_status').html(gateway_status_message);
}

// vim: ts=4 syntax=javascript
