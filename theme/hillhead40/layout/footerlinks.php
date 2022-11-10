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
 * Brief Description
 *
 * More indepth description.
 *
 * @package
 * @copyright  2022 Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$footerLinks = [
    'University Website' => 'https://www.gla.ac.uk',
    'Moodle Mobile App' => tool_mobile_create_app_download_url(),
    'Moodle Inspector' => 'https://moodleinspector.gla.ac.uk',
    'Accessibility' => 'https://www.gla.ac.uk/legal/accessibility/statements/moodle',
    'Privacy and Cookies' => 'https://www.gla.ac.uk/legal/privacy/',
];

$footerLinkText = '';

foreach ($footerLinks as $name=>$link) {
    $footerLinkText .= '<li><a href="'.$link.'">'.$name.'</a></li>';
}

$footerLinkText.= '<li class="tool_usertours-resettourcontainer"></li><li>'.page_doc_link('Help with this page').'</li>';