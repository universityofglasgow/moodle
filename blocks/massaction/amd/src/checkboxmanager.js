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
 * Checkbox manager amd module: Adds checkboxes to the activities for selecting and
 * generates a data structure of the activities and checkboxes.
 *
 * @module     block_massaction/checkboxmanager
 * @copyright  2022 ISB Bayern
 * @author     Philipp Memmel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Templates from 'core/templates';
import {exception as displayException} from 'core/notification';
import {cssIds, constants, usedMoodleCssClasses} from './massactionblock';
import {getCurrentCourseEditor} from 'core_courseformat/courseeditor';
import events from 'core_course/events';

let localStateUpdating = false;
let sectionsChanged = false;
let sections = [];
let moduleNames = [];

/* A registry of checkbox IDs, of the format:
 *  'section_number' => [{'moduleId'   : <module-ID>,
 *                       'boxId'       : <checkbox_id>}]
 */
const sectionBoxes = {};

/**
 * The checkbox manager takes a given 'sections' data structure object and inserts a checkbox for each of the given
 * course modules in this data object into the DOM.
 * The checkbox manager returns another data object containing the ids of the added checkboxes.
 * @param {[]} sectionsRestricted the sections which are restrected for the course format
 */
export const initCheckboxManager = sectionsRestricted => {
    const courseEditor = getCurrentCourseEditor();

    const eventsToListen = {
        SECTION_UPDATED: 'section:updated',
        CHANGE_FINISHED: 'transaction:end'
    };

    courseEditor.stateManager.target.addEventListener(events.stateChanged, (event) => {
        if (event.detail.action === eventsToListen.SECTION_UPDATED) {
            // Listen to section updated events. We do not want to immediately react to the event, but wait for
            // everything to finish updating.
            sectionsChanged = true;
        }
        if (event.detail.action === eventsToListen.CHANGE_FINISHED) {
            // Before every change to the state there is a transaction:start event. After the change is being commited,
            // we receive an transaction:end event. That is the point we want to react to changes of the state.
            rebuildLocalState(sectionsRestricted);
        }
    });
    // Trigger rendering of sections dropdowns a first time.
    sectionsChanged = true;
    // Get initial state.
    rebuildLocalState(sectionsRestricted);
};

/**
 * This method rebuilds the local state maintained in this module based on the course editor state.
 *
 * It will be called whenever a change to the courseeditor state is being detected.
 * @param {[]} sectionsRestricted the sections which are restrected for the course format
 */
const rebuildLocalState = sectionsRestricted => {
    if (localStateUpdating) {
        return;
    }
    localStateUpdating = true;
    const courseEditor = getCurrentCourseEditor();

    // First we rebuild our data structures depending on the course editor state.
    sections = [];
    for (const prop of Object.getOwnPropertyNames(sectionBoxes)) {
        delete sectionBoxes[prop];
    }
    // The section map object is being sorted by section id. We have to sort after order in this course.
    sections = [...courseEditor.stateManager.state.section.values()].sort((a, b) => a.number > b.number ? 1 : -1);
    moduleNames = [...courseEditor.stateManager.state.cm.values()];

    // Now we use the new information to rebuild dropdowns and re-apply checkboxes.
    const sectionsUnfiltered = sections;
    sections = filterVisibleSections(sections);
    updateSelectionAndMoveToDropdowns(sections, sectionsUnfiltered, sectionsRestricted);
    addCheckboxes();
    localStateUpdating = false;
};

/**
 * Returns the currently selected module ids.
 *
 * @returns {[]} Array of module ids currently being selected
 */
export const getSelectedModIds = () => {
    const moduleIds = [];
    for (let sectionNumber in sectionBoxes) {
        for (let i = 0; i < sectionBoxes[sectionNumber].length; i++) {
            const checkbox = document.getElementById(sectionBoxes[sectionNumber][i].boxId);
            if (checkbox.checked) {
                moduleIds.push(sectionBoxes[sectionNumber][i].moduleId);
            }
        }
    }
    return moduleIds;
};

/**
 * Select all module checkboxes in section(s).
 *
 * @param {boolean} value the checked value to set the checkboxes to
 * @param {string} sectionNumber the section number of the section which all modules should be checked/unchecked. Use "all" to
 *  select/deselect modules in all sections.
 */
export const setSectionSelection = (value, sectionNumber) => {
    const boxIds = [];

    if (typeof sectionNumber !== 'undefined' && sectionNumber === constants.SECTION_SELECT_DESCRIPTION_VALUE) {
        // Description placeholder has been selected, do nothing.
        return;
    } else if (typeof sectionNumber !== 'undefined' && sectionNumber === constants.SECTION_NUMBER_ALL_PLACEHOLDER) {
        // See if we are toggling all sections.
        for (const sectionId in sectionBoxes) {
            for (let j = 0; j < sectionBoxes[sectionId].length; j++) {
                boxIds.push(sectionBoxes[sectionId][j].boxId);
            }
        }
    } else {
        // We select all boxes of the given section.
        sectionBoxes[sectionNumber].forEach(box => boxIds.push(box.boxId));
    }
    // Un/check the boxes.
    for (let i = 0; i < boxIds.length; i++) {
        document.getElementById(boxIds[i]).checked = value;
    }
    // Reset dropdown to standard placeholder so we trigger a change event when selecting a section, then deselecting
    // everything and again select the same section.
    document.getElementById(cssIds.SECTION_SELECT).value = constants.SECTION_SELECT_DESCRIPTION_VALUE;
};

/**
 * Add checkboxes to all sections.
 */
const addCheckboxes = () => {
    sections.forEach(section => {
        sectionBoxes[section.number] = [];
        const moduleIds = section.cmlist;
        if (moduleIds && moduleIds.length > 0 && moduleIds[0] !== '') {
            const moduleNamesFiltered = moduleNames.filter(modinfo => moduleIds.includes(modinfo.id.toString()));
            moduleNamesFiltered.forEach(modinfo => {
                addCheckboxToModule(section.number, modinfo.id.toString(), modinfo.name);
            });
        }
    });
};

/**
 * Add a checkbox to a module element
 *
 * @param {number} sectionNumber number of the section of the current course module
 * @param {number} moduleId id of the current course module
 * @param {string} moduleName name of the course module specified by moduleId
 */
const addCheckboxToModule = (sectionNumber, moduleId, moduleName) => {
    const boxId = cssIds.BOX_ID_PREFIX + moduleId;
    let moduleElement = document.getElementById(usedMoodleCssClasses.MODULE_ID_PREFIX + moduleId)
        .querySelector(usedMoodleCssClasses.ACTIVITY_ITEM);
    // This additional class is only needed when we are using a legacy (pre moodle 4.0) course format.
    let additionalCssClass;
    if (!moduleElement) {
        // Should only happen in legacy formats (pre moodle 4.0).
        moduleElement = document.getElementById(usedMoodleCssClasses.MODULE_ID_PREFIX + moduleId);
        additionalCssClass = 'block-massaction-checkbox-legacy';
    }

    // Avoid creating duplicate checkboxes.
    if (document.getElementById(boxId) === null) {
        // Add the checkbox.
        const checkBoxElement = document.createElement('input');
        checkBoxElement.type = 'checkbox';
        checkBoxElement.className = cssIds.CHECKBOX_CLASS;
        if (additionalCssClass) {
            checkBoxElement.classList.add(additionalCssClass);
        }
        checkBoxElement.id = boxId;

        if (moduleElement !== null) {
            const checkboxDescription = moduleName + constants.CHECKBOX_DESCRIPTION_SUFFIX;
            checkBoxElement.ariaLabel = checkboxDescription;
            checkBoxElement.name = checkboxDescription;
            // Finally add the created checkbox element.
            moduleElement.insertBefore(checkBoxElement, moduleElement.firstChild);
        }
    }

    // Add the newly created checkbox to our data structure.
    sectionBoxes[sectionNumber].push({
        'moduleId': moduleId,
        'boxId': boxId,
    });
};

/**
 * Filter the sections data object depending on the visibility of the course modules contained in
 * the data object. This is neccessary, because some course formats only show specific section(s)
 * in editing mode.
 *
 * @param {[]} sections the sections data object
 * @returns {[]} the filtered sections object
 */
const filterVisibleSections = (sections) => {
    // Filter all sections with modules which no checkboxes have been created for.
    // This case should only occur in course formats where some sections are hidden.
    return sections.filter(section => section.cmlist.length !== 0)
        .filter(section => section.cmlist
            .every(moduleid => document.getElementById(usedMoodleCssClasses.MODULE_ID_PREFIX + moduleid) !== null));
};

/**
 * Update the selection, moveto and duplicateto dropdowns of the massaction block according to the
 * previously filtered sections.
 *
 * This method also has to be called whenever there is a module change event (moving around, adding file by Drag&Drop etc.).
 *
 * @param {[]} sections the sections object filtered before by {@link filterVisibleSections}
 * @param {[]} sectionsUnfiltered the same data object as 'sections', but still containing all sections
 * @param {[]} sectionsRestricted the sections which are restrected for the course format
 * no matter if containing modules or are visible in the current course format or not
 */
const updateSelectionAndMoveToDropdowns = (sections, sectionsUnfiltered, sectionsRestricted) => {
    if (sectionsChanged) {
        Templates.renderForPromise('block_massaction/section_select', {'sections': sectionsUnfiltered})
            .then(({html, js}) => {
                Templates.replaceNode('#' + cssIds.SECTION_SELECT, html, js);
                disableInvisibleAndEmptySections(sections);
                // Re-register event listener.
                document.getElementById(cssIds.SECTION_SELECT).addEventListener('change',
                    (event) => setSectionSelection(true, event.target.value), false);
                return true;
            })
            .catch(ex => displayException(ex));

        Templates.renderForPromise('block_massaction/moveto_select', {'sections': sectionsUnfiltered})
            .then(({html, js}) => {
                Templates.replaceNode('#' + cssIds.MOVETO_SELECT, html, js);
                disableRestrictedSections(cssIds.MOVETO_SELECT, sectionsRestricted);
                return true;
            })
            .catch(ex => displayException(ex));

        Templates.renderForPromise('block_massaction/duplicateto_select', {'sections': sectionsUnfiltered})
            .then(({html, js}) => {
                Templates.replaceNode('#' + cssIds.DUPLICATETO_SELECT, html, js);
                disableRestrictedSections(cssIds.DUPLICATETO_SELECT, sectionsRestricted);
                return true;
            })
            .catch(ex => displayException(ex));
    } else {
        // If there has not been an event about a section change we do not have to rebuild the sections dropdowns.
        // However there is a chance an section is being emptied or not empty anymore due to drag&dropping of modules.
        // So we have to recalculate if we have to enable/disable the sections.
        disableInvisibleAndEmptySections(sections);
    }
    // Reset the flag.
    sectionsChanged = false;
};

/**
 * Sets the disabled/enabled status of sections in the section select dropdown:
 * Enabled if section is visible and contains modules.
 * Disabled if section is not visible or doesn't contain any modules.
 *
 * @param {[]} sections the section data structure
 */
const disableInvisibleAndEmptySections = (sections) => {
    Array.prototype.forEach.call(document.getElementById(cssIds.SECTION_SELECT).options, option => {
        // Disable every element which doesn't have a visible section, except the placeholder ('description').
        if (option.value !== constants.SECTION_SELECT_DESCRIPTION_VALUE
                && !sections.some(section => parseInt(option.value) === section.number)) {
            option.disabled = true;
        } else {
            option.disabled = false;
        }
    });
};

/**
 * Sets the disabled/enabled status of sections in the section select dropdown
 *  by sectionsRestricted param
 *
 * @param {string} elementId elementId to apply the restriction
 * @param {[]} sectionsRestricted the sections which are restrected for the course format
 */
const disableRestrictedSections = (elementId, sectionsRestricted) => {
    Array.prototype.forEach.call(document.getElementById(elementId).options, option => {
        // Disable every element which is in the sectionsRestricted list.
        if (sectionsRestricted.includes(parseInt(option.value))) {
            option.disabled = true;
        } else {
            option.disabled = false;
        }
    });
};
