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

import * as BetaNotification from 'block_newgu_spdetails/betanotification';
import * as AssessmentsOverview from 'block_newgu_spdetails/assessmentsoverview';
import * as AssessmentsDueSoon from 'block_newgu_spdetails/assessmentsduesoon-v2';
import * as CourseTabs from 'block_newgu_spdetails/coursetabs';

/**
 * Initialise the Student Dashboard.
 */
export const init = () => {
    // Insert the Beta notification as we can't do this via the rendering mechanism.
    BetaNotification.init();
    // Initialise the assessment overview section.
    AssessmentsOverview.init();
    // Initialise the assessments due soon section
    AssessmentsDueSoon.init();
    // Initialise the assessment tabs section.
    CourseTabs.init();
};