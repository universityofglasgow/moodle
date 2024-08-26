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
 * Javascript to insert the beta notification as we can't do this via the rendering mechanism.
 *
 * @module     block_newgu_spdetails/betanotification
 * @author     Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @copyright  2024 University of Glasgow
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

"use strict";

import {get_string as getString} from 'core/str';

const Selectors = {
    PAGE_HEADER_CONTENT: '#page-header>div',
};

/**
 * @method insertBetaNotification
 */
const insertBetaNotification = () => {
    let tempPanel = document.querySelector(Selectors.PAGE_HEADER_CONTENT);
    getString('beta_notification', 'block_newgu_spdetails').then((str) => tempPanel.insertAdjacentHTML("afterend",
        "<div class='alert alert-info'>" + str + "</div>"));
};

/**
 * @constructor
 */
export const init = () => {
    insertBetaNotification();
};