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
 * Select annotator.
 *
 * Adds annotation as per the value chosen in a select field.
 *
 * @copyright  2022 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define([], function () {

    /**
     * Init.
     * @param {fieldId} fieldId The field ID.
     * @param {Array} data The data being an array of objects.
     */
    function init(fieldId, data) {
        const node = document.getElementById(fieldId);
        if (!node) {
            return;
        }
        node.style.width = 'auto';

        const wrapper = document.createElement('div');
        wrapper.className = 'block_xp';
        wrapper.style.display = 'inline-block';

        const container = document.createElement('div');
        container.className = 'xp-ml-2';
        wrapper.appendChild(container);

        /**
         * Updator.
         *
         * @param {String} value The value.
         */
        function updator(value) {
            value = value || '';
            var item = data.find(function(item) {
                return item.value == value;
            });

            container.childNodes.forEach(function(child) {
                child.remove();
            });
            if (!item || !item.annotation) {
                return;
            }

            container.innerHTML = item.annotation;
        }

        node.parentNode.appendChild(wrapper);
        node.addEventListener('change', function(e) {
            updator(e.target.value);
        });

        updator(node.value);
    }

    /**
     * Init with JSON.
     *
     * @param {String} fieldId The field ID.
     * @param {String} jsonSelector The JSON selector.
     */
    function initWithJson(fieldId, jsonSelector) {
        try {
            const node = document.querySelector(jsonSelector);
            const data = node ? JSON.parse(node.textContent) : null;
            if (!Array.isArray(data)) {
                throw new Error('That\'s a bit strange.');
            }
            init(fieldId, data);
        } catch (err) {
            // Nothing.
        }
    }

    return {
        'init': init,
        'initWithJson': initWithJson
    };
});