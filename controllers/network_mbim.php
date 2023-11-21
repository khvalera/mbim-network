<?php

/**
 * Network MBIM controller.
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
// C L A S S
///////////////////////////////////////////////////////////////////////////////
class Network_mbim extends ClearOS_Controller {
    /**
     * Network MBIM default controller.
     * @return view
     */
    function index() {
        // Load dependencies
        //------------------
        $this->lang->load('network_mbim');

        // Load views
        //-----------
        $views = array('network_mbim/server', 'network_mbim/settings');

        $this->page->view_forms($views, lang('network_mbim_app_name'));
    }
}
