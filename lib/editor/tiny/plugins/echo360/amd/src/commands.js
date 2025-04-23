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

import {get_string as getString} from 'core/str';
import {component, echo360ButtonShortName, icon} from 'tiny_echo360/common';
import {handleAction} from 'tiny_echo360/ui';
import {toggleActiveState} from 'tiny_echo360/link';
import {getButtonImage} from 'editor_tiny/utils';

/**
 * Tiny Echo360 commands.
 *
 * @module      tiny_echo360/commands
 * @copyright   2023 Echo360 Inc.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

export const getSetup = async() => {
    const [
        echo360ButtonText,
        buttonImage,
    ] = await Promise.all([
        getString('browse', component),
        getButtonImage('icon', component),
    ]);

    return (editor) => {
        // Register the H5P Icon.
        editor.ui.registry.addIcon(icon, buttonImage.html);

        // Register Link button.
        editor.ui.registry.addToggleButton(echo360ButtonShortName, {
            icon: icon,
            tooltip: echo360ButtonText,
            onAction: () => {
                handleAction(editor);
            },
            onSetup: toggleActiveState(editor),
        });

        // Register the Link menu item.
        editor.ui.registry.addMenuItem(echo360ButtonShortName, {
            icon: icon,
            shortcut: 'Meta+E',
            text: echo360ButtonText,
            onAction: () => {
                handleAction(editor);
            },
        });

        // Register shortcut.
        editor.shortcuts.add('Meta+E', 'Shortcut for embed Echo360 video', () => {
            handleAction(editor);
        });
    };
};
