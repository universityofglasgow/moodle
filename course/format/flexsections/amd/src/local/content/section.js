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

import Section from 'core_courseformat/local/content/section';

/**
 * Course section format component.
 *
 * @module     format_flexsections/local/content/section
 * @copyright  2022 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
export default class extends Section {
    // Extends course/format/amd/src/local/content/section.js
    // Extends course/format/amd/src/local/courseeditor/dndsection.js

    /**
     * Initial state ready method.
     *
     * @param {Object} state the initial state
     */
    stateReady(state) {
        this.configState(state);
        // Overrides the parent method and removes drag&drop initialisation.
        // Drag & drop of sections is not currently supported in format_flexsections.
    }
}