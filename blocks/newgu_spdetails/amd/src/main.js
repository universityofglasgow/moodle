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
 * Javascript to initialise the Student Dashboard.
 *
 * @module     block_newgu_spdetails/main
 * @author     Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @copyright  2023 University of Glasgow
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import * as AssessmentSummary from 'block_newgu_spdetails/assessmentsummary';
import * as AssessmentsDueSoon from 'block_newgu_spdetails/assessmentsduesoon';
import * as CourseTabs from 'block_newgu_spdetails/coursetabs';

/**
 * Initialise the Student Dashboard.
 */
export const init = () => {
    // Initialise the assessment summary section.
    AssessmentSummary.init();
    // Initialise the assessments due soon section
    AssessmentsDueSoon.init();
    // Initialise the assessment tabs section.
    CourseTabs.init();
};