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
 * Course index main component.
 *
 * @module     format_cards/cards
 * @class      format_cards/cards
 * @copyright  2024 University of Essex
 * @author     John Maydew <jdmayd@essex.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

export default class Component {

    /**
     * Constructor hook.
     *
     * @param {Object} descriptor the component descriptor
     */
    constructor(descriptor) {
        this.root = descriptor;
    }

    /**
     * Set up the component. At the moment, this just modifies the click event for the availability popover
     */
    setup() {

        const availabilityBadges = this.root.querySelectorAll(".section-availability");

        for (let badge of availabilityBadges) {
            badge.addEventListener('click', function(event) {
                event.preventDefault();
                event.stopPropagation();
            });
        }
    }

    /**
     * Static method to create a component instance form the mustache template.
     *
     * @param {string} target the DOM main element or its ID
     * @return {Component}
     */
    static init(target) {
        const component = new Component(document.getElementById(target));

        component.setup();

        return component;
    }
}
