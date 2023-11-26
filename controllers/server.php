<?php

/**
 * Network MBIM daemon controller.
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
// B O O T S T R A P
///////////////////////////////////////////////////////////////////////////////

$bootstrap = getenv('CLEAROS_BOOTSTRAP') ? getenv('CLEAROS_BOOTSTRAP') : '/usr/clearos/framework/shared';
require_once $bootstrap . '/bootstrap.php';

///////////////////////////////////////////////////////////////////////////////
// D E P E N D E N C I E S
///////////////////////////////////////////////////////////////////////////////

require clearos_app_base('base') . '/controllers/daemon.php';

use \clearos\apps\base\Shell as Shell;

clearos_load_library('base/Shell');

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * OpenSSH server daemon controller.
 *
 * @category   apps
 * @package    ssh-server
 * @subpackage controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/ssh_server/
 */
class Server extends Daemon {
    const COMMAND_SYSTEMCTL = '/usr/bin/systemctl';
    const STATUS_BUSY = 'busy';
    const STATUS_RUNNING = 'running';
    const STATUS_STARTING = 'starting';
    const STATUS_STOPPED = 'stopped';
    const STATUS_STOPPING = 'stopping';
    const STATUS_RESTARTING = 'restarting';
    const STATUS_DEAD = 'dead';

    // OpenSSH server constructor.
    function __construct() {
        parent::__construct('network-mbim', 'network_mbim');
    }

    /**
     * Status.
     * @return view
     */
    function status() {
        header('Cache-Control: no-cache, must-revalidate');
        header('Content-type: application/json');

        $options['validate_exit_code'] = FALSE;
        $shell = new Shell();
        $exit_code = $shell->execute(self::COMMAND_SYSTEMCTL, "status network-mbim.service", FALSE, $options);

        if ($exit_code !== 0)
            $status['status'] = self::STATUS_STOPPED;
        else
            $status['status'] = self::STATUS_RUNNING;

        echo json_encode($status);
    }

    /**
     * Start.
     * @return view
     */
    function start() {
        try {
            $options['stdin'] = "use_popen";
            $options['background'] = $background;

            $shell = new Shell();
            $shell->execute(self::COMMAND_SYSTEMCTL, "start network-mbim.service", TRUE, $options);

        } catch (Exception $e) {
            // Keep going
        }
    }

    /**
     * Stop.
     * @return view
     */
    function stop() {
        try {
            $options['stdin'] = "use_popen";
            $options['background'] = $background;

            $shell = new Shell();
            $shell->execute(self::COMMAND_SYSTEMCTL, "stop network-mbim.service", TRUE, $options);

        } catch (Exception $e) {
            // Keep going
        }
    }
}
