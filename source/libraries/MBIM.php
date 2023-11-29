<?php

/**
 * MBIM class.
 *
 * @category   apps
 * @package    network-mbim
 * @subpackage libraries
 * @author     Khomenko V.V. <khvalera@ukr.net>
 * @copyright  2011-2014 ClearFoundation
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       https://github.com/khvalera/network-mbim
 */

///////////////////////////////////////////////////////////////////////////////
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU Lesser General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Lesser General Public License for more details.
//
// You should have received a copy of the GNU Lesser General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
///////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////
// N A M E S P A C E
///////////////////////////////////////////////////////////////////////////////

namespace clearos\apps\network_mbim;

///////////////////////////////////////////////////////////////////////////////
// B O O T S T R A P
///////////////////////////////////////////////////////////////////////////////

$bootstrap = getenv('CLEAROS_BOOTSTRAP') ? getenv('CLEAROS_BOOTSTRAP') : '/usr/clearos/framework/shared';
require_once $bootstrap . '/bootstrap.php';

///////////////////////////////////////////////////////////////////////////////
// T R A N S L A T I O N S
///////////////////////////////////////////////////////////////////////////////

clearos_load_language('network_mbim');

///////////////////////////////////////////////////////////////////////////////
// D E P E N D E N C I E S
///////////////////////////////////////////////////////////////////////////////

// Classes
//--------

use \clearos\apps\base\Configuration_File as Configuration_File;
use \clearos\apps\base\Daemon as Daemon;
use \clearos\apps\base\File as File;
use \clearos\apps\network\Network_Utils as Network_Utils;
use \clearos\apps\base\Shell as Shell;
use \clearos\apps\base\Engine_Exception as Engine_Exception;

clearos_load_library('base/Configuration_File');
clearos_load_library('base/Daemon');
clearos_load_library('base/File');
clearos_load_library('network/Network_Utils');
clearos_load_library('base/Shell');
clearos_load_library('base/Engine');

// Exceptions
//-----------
use \clearos\apps\base\Validation_Exception as Validation_Exception;

clearos_load_library('base/Validation_Exception');

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////
class MBIM extends Daemon {
    ///////////////////////////////////////////////////////////////////////////////
    // C O N S T A N T S
    ///////////////////////////////////////////////////////////////////////////////
    const FILE_CONFIG      = '/etc/mbim-network.conf';
    const DEFAULT_DEV      = '';
    const DEFAULT_APN      = 'internet';
    const DEFAULT_APN_USER = '';
    const DEFAULT_APN_PASS = '';
    const DEFAULT_PROXY    = FALSE;

    ///////////////////////////////////////////////////////////////////////////////
    // V A R I A B L E S
    ///////////////////////////////////////////////////////////////////////////////

    protected $is_loaded = FALSE;
    protected $config = array();
    protected $current_device = '';
    ///////////////////////////////////////////////////////////////////////////////
    // M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /* MBIM constructor. */
    public function __construct() {
        clearos_profile(__METHOD__, __LINE__);

        parent::__construct('network-mbim');
    }

    /*******************************
     * Returns an array with the results of the executed command.
     * @return Array
     * @throws Engine_Exception
    *******************************/
    private function get_mbimcli($args) {
        $options['validate_exit_code'] = FALSE;
        $shell = new Shell();
        $exitcode = $shell -> execute('/bin/mbimcli', $args, TRUE, $options);
        if ($exitcode != 0) {
            $errstr = $shell->get_last_output_line();
            $output = $shell->get_output();
            $options['buttons'] = array(anchor_custom('/app/network_mbim', lang('base_ok')));
            echo infobox_highlight(
               lang('network_mbim_error_data_modem'),
               $errstr,
               $options
            );
        } else
            $output = $shell->get_output();

        return $output;
    }

    /*******************************
     * Returns an array with the results of the executed command.
     * @return Array
     * @throws Engine_Exception
    *******************************/
    public function get_data_modem($query_modem) {
        $current_device = $this -> current_device;
        if ( isset( $current_device )) {
           $query = str_replace( '_', '-', isset( $_GET['query'] ) ? $_GET['query'] : array());
           if ( $query !== Array()) {
              if ( array_key_exists('network_mbim?query='.$query, $query_modem)) {
                  $output = $this -> get_mbimcli("--device=/dev/$current_device -p --$query");
                  unset($output[0]);
              }
           }
        }
        return $output;
    }

    /*******************************
     * Returns a list of files by patternan.
     * @return Array
     * @throws Engine_Exception
    *******************************/
    private function scan_directory($directory, $pattern) {
       if (!file_exists($directory) || !($dh = opendir($directory)))
            return array();

        $files = array();
        while (($file = readdir($dh)) !== FALSE) {
            if (!preg_match($pattern, $file))
                continue;
            $files[$file] = $file;
        }

        closedir($dh);
        ksort($files);
        return $files;
    }

    /*******************************
     * Returns an array of devices.
     * @return String
     * @throws Engine_Exception
    *******************************/
    public function get_devices() {
        $devices = $this -> scan_directory('/dev', '/^cdc-wdm.*/');

        return $devices;
    }

    /*******************************
     * Returns DEVICE.
     * @return String
     * @throws Engine_Exception
    *******************************/
    public function get_device() {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this -> is_loaded)
            $this -> _load_config();

        if (isset($this -> config['DEV'])) {
            $this -> current_device = $this -> config['DEV'];
            return $this -> current_device;
        } else {
            $this -> current_device = self::DEFAULT_DEV;
            return $this -> current_device;
        }
    }

    /*******************************
     * Returns APN.
     * @return String
     * @throws Engine_Exception
    *******************************/
    public function get_apn() {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this -> is_loaded)
            $this -> _load_config();

        if (isset($this -> config['APN']))
            return $this -> config['APN'];
        else
            return self::DEFAULT_APN;
    }

    /*******************************
     * Returns APN_USER.
     * @return String
     * @throws Engine_Exception
    *******************************/
    public function get_apn_user() {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this -> is_loaded)
            $this -> _load_config();

        if (isset($this -> config['APN_USER']))
            return $this -> config['APN_USER'];
        else
            return self::DEFAULT_APN_USER;
    }

    /*******************************
     * Returns APN_PASS.
     * @return String
     * @throws Engine_Exception
    *******************************/
    public function get_apn_pass() {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this -> is_loaded)
            $this -> _load_config();

        if (isset($this -> config['APN_PASS']))
            return $this -> config['APN_PASS'];
        else
            return self::DEFAULT_APN_PASS;
    }

    /*******************************
     * Returns PROXY.
     * @return "yes" or "no"
     * @throws Engine_Exception
    *******************************/
    public function get_proxy() {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this -> is_loaded)
            $this -> _load_config();

        if (isset($this -> config['PROXY']))
            return $this -> _get_boolean($this -> config['PROXY']);
        else
            return self::DEFAULT_PROXY;
    }

    /*******************************
     * Sets Device.
     * @param string $apn Device
     * @return void
     * @throws Engine_Exception, Validation_Exception
    *******************************/
    public function set_device($device) {
        clearos_profile(__METHOD__, __LINE__);

        $this -> _set_parameter('DEV', $device);
    }

    /*******************************
     * Sets APN.
     * @param string $apn APN
     * @return void
     * @throws Engine_Exception, Validation_Exception
    *******************************/
    public function set_apn($apn) {
        clearos_profile(__METHOD__, __LINE__);

        $this -> _set_parameter('APN', $apn);
    }

    /*******************************
     * Sets APN_USER.
     * @param string $apn_user APN_USER
     * @return void
     * @throws Engine_Exception, Validation_Exception
    *******************************/
    public function set_apn_user($apn_user) {
        clearos_profile(__METHOD__, __LINE__);

        $this -> _set_parameter('APN_USER', $apn_user);
    }

    /*******************************
     * Sets APN_PASS.
     * @param string $apn_pass APN_PASS
     * @return void
     * @throws Engine_Exception, Validation_Exception
    *******************************/
    public function set_apn_pass($apn_pass) {
        clearos_profile(__METHOD__, __LINE__);

        $this -> _set_parameter('APN_PASS', $apn_pass);
    }

    /*******************************
     * Sets the PROXY value.
     * @param boolean $proxy proxy
     * @return void
     * @throws Engine_Exception, Validation_Exception
    *******************************/
    public function set_proxy($proxy) {
        clearos_profile(__METHOD__, __LINE__);

        Validation_Exception::is_valid($this -> validate_proxy($proxy));

        $proxy_value = ($proxy) ? 'yes' : 'no';

        $this -> _set_parameter('PROXY', $proxy_value);
    }

    ///////////////////////////////////////////////////////////////////////////////
    // V A L I D A T I O N
    ///////////////////////////////////////////////////////////////////////////////

    /*******************************
     * Validates PROXY.
     * @param boolean $proxy $proxy
     * @return string error message if proxy is invalid
    *******************************/
    public function validate_proxy($proxy) {
        clearos_profile(__METHOD__, __LINE__);

        if (! clearos_is_valid_boolean($proxy))
            return lang('network_mbim_proxy_invalid');
    }

    ///////////////////////////////////////////////////////////////////////////////
    // P R I V A T E  M E T H O D S 
    ///////////////////////////////////////////////////////////////////////////////

    /*******************************
     * Converts configuration file booleans.
     * @param string $boolean_text configuration file booleans
     * @return boolean boolean for given text
    *******************************/
    protected function _get_boolean($boolean_text) {
        clearos_profile(__METHOD__, __LINE__);

        if (preg_match('/yes/i', $boolean_text))
            return TRUE;
        else if (preg_match('/no/i', $boolean_text))
            return FALSE;
        else
            throw new Validation_Exception(lang('base_file_parse_error'));
    }

    /*******************************
     * Loads configuration files.
     * @access private
     * @return void
     * @throws Engine_Exception
    *******************************/
    protected function _load_config() {
        clearos_profile(__METHOD__, __LINE__);

        $config_file = new Configuration_File(self::FILE_CONFIG);
        $this->config = $config_file->load();
        $this->is_loaded = TRUE;
    }

    /*******************************
     * Sets a parameter in the config file.
     * @param string $key   name of the key in the config file
     * @param string $value value for the key
     * @access private
     * @return void
     * @throws Engine_Exception
    *******************************/
    function _set_parameter($key, $value) {
        clearos_profile(__METHOD__, __LINE__);

        try {
            $file = new File(self::FILE_CONFIG, TRUE);

            if (!$file->exists())
                $file->create('webconfig', 'webconfig', '0644');

            $match = $file->replace_lines("/^$key\s*=\s*/", "$key=$value\n");

            if (!$match)
                $file->add_lines("$key=$value\n");
        } catch (Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e), CLEAROS_ERROR);
        }
        $this->is_loaded = FALSE;
    }
}
