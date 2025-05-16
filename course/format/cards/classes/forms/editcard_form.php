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
 * Moodle form for editing a section
 *
 * @package     format_cards
 * @copyright   2024 University of Essex
 * @author      John Maydew <jdmayd@essex.ac.uk>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_cards\forms;

use coding_exception;
use editsection_form;
use lang_string;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once("$CFG->libdir/formslib.php");
require_once("$CFG->dirroot/course/editsection_form.php");

/**
 * Moodle form for editing a section
 *
 * @package     format_cards
 * @copyright   2024 University of Essex
 * @author      John Maydew <jdmayd@essex.ac.uk>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class editcard_form extends editsection_form {

    /**
     * Expands the editsection_form by adding an image editing section to the end
     *
     * @return void
     * @throws coding_exception
     */
    public function definition(): void {
        parent::definition();

        $form = $this->_form;

        $form->addElement('header', 'cardimage', get_string('editcard', 'format_cards'));
        $form->setExpanded('cardimage');

        $form->addElement(
            'filemanager',
            'image',
            get_string('image', 'format_cards'),
            null,
            [
                'subdirs' => 0,
                'maxfiles' => 1,
                'accepted_types' => [ 'web_image' ],
            ]
        );

        if (array_key_exists('image', $this->_customdata)) {
            $form->setDefault('image', $this->_customdata['image']);
        }
    }
}
