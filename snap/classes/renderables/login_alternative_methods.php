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
 * Alternative login methods renderable.
 * @author    gthomas2
 * @copyright Copyright (c) 2016 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_snap\renderables;

defined('MOODLE_INTERNAL') || die();

class login_alternative_methods implements \renderable {

    public $potentialidps = [];

    public function __construct() {
        global $CFG, $SESSION, $OUTPUT;

        // Get all alternative login methods and add to potentialipds array.
        $authsequence = get_enabled_auth_plugins(true);
        $potentialidps = [];
        foreach ($authsequence as $authname) {
            if (isset($SESSION->snapwantsurl)) {
                $urltogo = $SESSION->snapwantsurl;
            } else {
                $urltogo = $CFG->wwwroot.'/';
            }
            unset($SESSION->snapwantsurl);

            $authplugin = get_auth_plugin($authname);
            $potentialidps = array_merge($potentialidps, $authplugin->loginpage_idp_list($urltogo));
        }

        if (!empty($potentialidps)) {
            foreach ($potentialidps as $idp) {
                $this->potentialidps[] = (object) [
                    'url' => $idp['url']->out(),
                    'name' => $idp['name'],
                    'icon' => $OUTPUT->image_url($idp['icon']->pix)
                ];
            }
        }
    }

}
