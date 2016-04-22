<?php
// This file is part of The Bootstrap 3 Moodle theme
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

defined('MOODLE_INTERNAL') || die();

/**
 * Renderers to align Moodle's HTML with that expected by Bootstrap/gu28
 *
 * @package    theme_gu28
 * @copyright  2012
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class theme_gu28_core_renderer extends theme_bootstrap_core_renderer {

    public function navbar() {
        $breadcrumbreplace = isset($this->page->theme->settings->breadcrumbreplace) ? $this->page->theme->settings->breadcrumbreplace : '';
        $search = array();
        $replace = array();
        if ($breadcrumbreplace) {
            $pairs = explode(PHP_EOL, $breadcrumbreplace);
            for ($i=0; $i<count($pairs); $i+=2) {
                $search[] = trim($pairs[$i]);
                $replace[] = trim($pairs[$i+1]);
            }
        }
        $items = $this->page->navbar->get_items();
        if (empty($items)) { // MDL-46107
            return '';
        }
        $breadcrumbs = '';
        foreach ($items as $item) {
            $item->hideicon = true;
            if ($search) {
                $item->text = str_replace($search, $replace, $item->text);
            }
            $breadcrumbs .= '<li>'.$this->render($item).'</li>';
        }
        return "<ol class=breadcrumb>$breadcrumbs</ol>";
    }

}
