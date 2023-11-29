<?php

/**
 * Network MBIM settings view.
 *
 * @category   apps
 * @package    network_mbim
 * @subpackage views
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
// Load dependencies
///////////////////////////////////////////////////////////////////////////////
$this->lang->load('base');
$this->lang->load('network');
$this->lang->load('network_mbim');

use \clearos\apps\base\Shell as Shell;

clearos_load_library('base/Shell');

///////////////////////////////////////////////////////////////////////////////
// Form type handling
///////////////////////////////////////////////////////////////////////////////
if ($form_type === 'edit') {
    $read_only = FALSE;
    $buttons = array(
        form_submit_update('submit'),
        anchor_cancel('/app/network_mbim/settings')
    );
} else {
    $read_only = TRUE;
    $buttons = array(
        anchor_edit('/app/network_mbim/settings/edit')
    );
}

///////////////////////////////////////////////////////////////////////////////
// Form
///////////////////////////////////////////////////////////////////////////////
echo form_open('network_mbim/settings/edit');
echo form_header(lang('network_mbim_settings'));
echo field_dropdown('device', $devices, $device, lang('network_mbim_device'), $read_only);

if ($form_type === 'edit') {
    echo field_input('apn', $apn, lang('network_mbim_apn'), $read_only);
    echo field_input('apn_user', $apn_user, lang('network_mbim_apn_user'), $read_only);
    echo field_input('apn_pass', $apn_pass, lang('network_mbim_apn_pass'), $read_only);
    echo field_toggle_enable_disable('proxy', $proxy, lang('network_mbim_proxy'), TRUE);
}

echo field_button_set($buttons);
echo form_footer();
echo form_close();


if ($form_type !== 'edit') {
//    echo form_open('network_mbim/modem_status');
//    echo form_header(lang('network_mbim_modem_status'));
//    echo field_input('modem_status', $modem_status, lang('network_mbim_modem_status'), TRUE);
//    echo form_footer();
//    echo form_close();

    ///////////////////////////////////////////////////////////////////////////////
    // Anchors.
    ///////////////////////////////////////////////////////////////////////////////
    $anchors = anchor_multi(
        $query_modem,
        lang('network_mbim_get_data_modem')
    );
    $headers = array(
        lang('system_report_item'),
        lang('system_report_value')
    );

    $rows = array();
    foreach ($data_modem as $id => $entry) {
        $exp = explode(':', $entry);
        $row['details'] = array ($exp[0], $exp[1]);
        $rows[] = $row;
    }

    $options['no_action'] = TRUE;
    $options['sort'] = FALSE;
    $options['default_rows'] = 25;

    echo summary_table(
        lang('network_mbim_data_from_modem'),
        $anchors,
        $headers,
        $rows,
        $options
    );
}
