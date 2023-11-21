<?php
/**
 * Network MBIM settings controller.
 * @category   apps
 * @package    mbim_network
 * @subpackage controllers
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
// D E P E N D E N C I E S
///////////////////////////////////////////////////////////////////////////////

use \Exception as Exception;

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////
class Settings extends ClearOS_Controller {
    /**
     * SSH server settings controller.
     * @return view
     */
    function index() {
        $this->_view_edit('view');
    }

    /**
     * SSH server settings controller.
     * @return view
     */
    function edit() {
        $this->_view_edit('edit');
    }

    /**
     * Common edit/view controller.
     * @param string $form_type form type
     * @return view View
     */
    function _view_edit($form_type) {

        // Load dependencies
        //------------------
        $this->lang->load('base');
        $this->load->library('network_mbim/MBIM');

        // Set validation rules
        //---------------------
        $this->form_validation->set_policy('proxy', 'network_mbim/MBIM', 'validate_proxy', TRUE);

        $form_ok = $this->form_validation->run();
        // Handle form submit
        //-------------------
        if ($this->input->post('submit') && $form_ok) {
            try {
                $this->mbim->set_device($this->input->post('device'));
                $this->mbim->set_apn($this->input->post('apn'));
                $this->mbim->set_apn_user($this->input->post('apn_user'));
                $this->mbim->set_apn_pass($this->input->post('apn_pass'));
                $this->mbim->set_proxy($this->input->post('proxy'));
                $this->mbim->reset(TRUE);
                $this->page->set_status_updated();
                redirect('/network_mbim');
            } catch (Engine_Exception $e) {
                $this->page->view_exception($e);
                return;
            }
        }
        // Load view data
        //---------------
        try {
             $query_modem = array (
                'network_mbim?query=query-device-caps' => lang('network_mbim_query_device_caps'),
                'network_mbim?query=query-subscriber-ready-status' => lang('network_mbim_query_subscriber_ready_status'),
                'network_mbim?query=query-radio-state' => lang('network_mbim_query_radio_state'),
                'network_mbim?query=query-device-services' => lang('network_mbim_query_device_services'),
                'network_mbim?query=query-pin-state' => lang('network_mbim_query_pin_state'),
                'network_mbim?query=query-home-provider' => lang('network_mbim_query_home_provider'),
                'network_mbim?query=query-preferred-providers' => lang('network_mbim_query_preferred_providers'),
                'network_mbim?query=query-registration-state' => lang('network_mbim_query_registration_state'),
                'network_mbim?query=query-signal-state' => lang('network_mbim_query_signal_state'),
                'network_mbim?query=query-packet-service-state' => lang('network_mbim_query_packet_service_state'),
                'network_mbim?query=query-connection-state' => lang('network_mbim_query_connection_state'),
                'network_mbim?query=query-ip-configuration' => lang('network_mbim_query_ip_configuration'),
                'network_mbim?query=query-packet-statistics' => lang('network_mbim_query_packet_statistics'),
                'network_mbim?query=phonebook-read-all' => lang('network_mbim_phonebook_read_all')
            );

            $data['form_type']  = $form_type;
            $data['devices']    = $this->mbim->get_devices();
            $data['device']     = $this->mbim->get_device();
            $data['apn']        = $this->mbim->get_apn();
            $data['apn_user']   = $this->mbim->get_apn_user();
            $data['apn_pass']   = $this->mbim->get_apn_pass();
            $data['proxy']      = $this->mbim->get_proxy();
            $data['query_modem']= $query_modem;
            $data['data_modem'] = $this->mbim->get_data_modem($query_modem);
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }
        // Load views
        //-----------
        $this->page->view_form('network_mbim/settings', $data, lang('base_settings'));
    }
}
