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
 * Course index cm component.
 *
 * This component is used to control specific course modules interactions like drag and drop.
 *
 * @module     core_courseformat/local/courseindex/cm
 * @class      core_courseformat/local/courseindex/cm
 * @copyright  2021 Ferran Recio <ferran@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import DndCmItem from 'core_courseformat/local/courseeditor/dndcmitem';
import Templates from 'core/templates';
import Prefetch from 'core/prefetch';
import Config from 'core/config';

// Prefetch the completion icons template.
const completionTemplate = 'core_courseformat/local/courseindex/cmcompletion';
Prefetch.prefetchTemplate(completionTemplate);

export default class Component extends DndCmItem {

    /**
     * Constructor hook.
     */
    create() {
        // Optional component name for debugging.
        this.name = 'courseindex_cm';
        // Default query selectors.
        this.selectors = {
            CM_NAME: `[data-for='cm_name']`,
            CM_COMPLETION: `[data-for='cm_completion']`,
        };
        // Default classes to toggle on refresh.
        this.classes = {
            CMHIDDEN: 'dimmed',
            LOCKED: 'editinprogress',
            RESTRICTIONS: 'rectrictions',
            PAGEITEM: 'pageitem',
        };
        // We need our id to watch specific events.
        this.id = this.element.dataset.id;
    }

    /**
     * Static method to create a component instance form the mustache template.
     *
     * @param {element|string} target the DOM main element or its ID
     * @param {object} selectors optional css selector overrides
     * @return {Component}
     */
    static init(target, selectors) {
        return new Component({
            element: document.getElementById(target),
            selectors,
        });
    }

    /**
     * Initial state ready method.
     *
     * @param {Object} state the course state.
     */
    stateReady(state) {
        this.configDragDrop(this.id);
        // Refresh completion icon.
        this._refreshCompletion({
            state,
            element: state.cm.get(this.id),
        });
        // Check if this we are displaying this activity id page.
        if (Config.contextid != Config.courseContextId && Config.contextInstanceId == this.id) {
            this.element.classList.add(this.classes.PAGEITEM);
        }
    }

    /**
     * Component watchers.
     *
     * @returns {Array} of watchers
     */
    getWatchers() {
        return [
            {watch: `cm[${this.id}]:deleted`, handler: this.remove},
            {watch: `cm[${this.id}]:updated`, handler: this._refreshCm},
            {watch: `cm[${this.id}].completionstate:updated`, handler: this._refreshCompletion},
        ];
    }

    /**
     * Update a course index cm using the state information.
     *
     * @param {object} param
     * @param {Object} param.element details the update details.
     */
    _refreshCm({element}) {
        // Update classes.
        this.element.classList.toggle(this.classes.CMHIDDEN, !element.visible);
        this.getElement(this.selectors.CM_NAME).innerHTML = element.name;
        this.element.classList.toggle(this.classes.DRAGGING, element.dragging ?? false);
        this.element.classList.toggle(this.classes.LOCKED, element.locked ?? false);
        this.element.classList.toggle(this.classes.RESTRICTIONS, element.hascmrectrictions ?? false);
        this.locked = element.locked;
    }

    /**
     * Update the activity completion icon.
     *
     * @param {Object} details the update details
     * @param {Object} details.state the state data
     * @param {Object} details.element the element data
     */
    async _refreshCompletion({state, element}) {
        // No completion icons are displayed in edit mode.
        if (this.reactive.isEditing || !element.istrackeduser) {
            return;
        }
        // Check if the completion value has changed.
        const completionElement = this.getElement(this.selectors.CM_COMPLETION);
        if (completionElement.dataset.value == element.completionstate) {
            return;
        }

        // Collect section information from the state.
        const exporter = this.reactive.getExporter();
        const data = exporter.cmCompletion(state, element);

        try {
            const {html, js} = await Templates.renderForPromise(completionTemplate, data);
            Templates.replaceNode(completionElement, html, js);
        } catch (error) {
            throw error;
        }
    }
}
