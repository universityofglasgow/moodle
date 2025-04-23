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
 * Tiny Link configuration.
 *
 * @module      tiny_echo360/configuration
 * @copyright   2023 Echo360 Inc.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {echo360ButtonShortName} from 'tiny_echo360/common';
import {addToolbarButtons} from 'editor_tiny/utils';

const configureMenu = (menu) => {
    menu.insert.items = `${echo360ButtonShortName} ${menu.insert.items}`;

    return menu;
};

export const configure = (instanceConfig) => {
    // Update the instance configuration to add the Link option to the menus and toolbars.
    return {
        menu: configureMenu(instanceConfig.menu),
        toolbar: addToolbarButtons(instanceConfig.toolbar, 'content', [echo360ButtonShortName]),
    };
};
