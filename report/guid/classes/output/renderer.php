<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Version details.
 *
 * @package    report
 * @subpackage guid
 * @copyright  2012 Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_guid\output;

defined('MOODLE_INTERNAL') || die;

use plugin_renderer_base;
use moodle_url;

class renderer extends plugin_renderer_base {

    protected $config;

    /**
     * Set the config settings
     * @param object $config
     */
    public function set_guid_config($config) {
        $this->config = $config;
    }

    /**
     * Display links on main page
     */
    public function mainlinks() {
        $uploadlink = new moodle_url('/report/guid/upload.php');
        echo '<div class="form-group">';
        echo '    <a class="btn btn-primary" href="' . $uploadlink . '">' . get_string('uploadguid', 'report_guid') . '</a>';
        echo '</div>';
    }

    /**
     * Display ldap error and footer
     * @param string $message
     */
    public function ldap_error($message) {
        global $OUTPUT;

        echo '<div class="alert alert-danger">' . $message . '</div>';
        echo $OUTPUT->footer();
    }

    /**
     * Confirm deletion of user
     * @param object $user
     */
    public function confirmdelete($user) {

        $fullname = fullname($user, true);

        $optionsyes = [
            'delete' => $user->id,
            'confirm' => md5($user->id),
            'sesskey' => sesskey(),
        ];
        $deleteurl = new moodle_url('/report/guid/index.php', $optionsyes);
        $cancelurl = new moodle_url('/report/guid/index.php');
        $deletebutton = new \single_button($deleteurl, get_string('delete'), 'post');

        echo $this->confirm(get_string('deletecheckfull', '', "'$fullname'"), $deletebutton, $cancelurl);
    }

    /**
     * Display status note for course upload output
     * @param string $message language string
     * @param string $class Bootstrap alert/label class suffix (e.g., info, warning)
     * @param boolean $eol add end of line characters
     * @param string $a get_string extra
     */
    public function courseuploadnote($message, $class, $eol=false, $a = '') {
        $str = get_string($message, 'report_guid', $a);
        echo '&nbsp;<span class="label label-' . $class . '">' . $str . '</span>';
        if ($eol) {
            echo "<br />";
        }
    }

    /**
     * Render LDAP long listing
     * @param object $ldaplist
     */
    public function render_ldaplist(ldaplist $ldaplist) {
        return $this->render_from_template('report_guid/ldaplist', $ldaplist->export_for_template($this));
    }

    /**
     * Render single user report
     * @param object $single
     */
    public function render_single(single $single) {
        return $this->render_from_template('report_guid/single', $single->export_for_template($this));
    }
}

