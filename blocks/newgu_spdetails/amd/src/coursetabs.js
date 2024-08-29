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
 * Javascript to initialise the Course Tabs section
 *
 * @module     block_newgu_spdetails/coursetabs
 * @author     Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @copyright  2023 University of Glasgow
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

"use strict";

import * as Log from 'core/log';
import * as Ajax from "../../../../lib/amd/src/ajax";
import {exception as displayException} from 'core/notification';
import Templates from 'core/templates';
import sortTable from 'block_newgu_spdetails/sorting';

const initCourseTabs = () => {

    let activetab = 'current';
    let page = 0;
    let sortby = 'shortname';
    let sortorder = 'asc';
    let subcatId = null;
    let activeTab = sessionStorage.getItem('activeTab');
    let activeCategoryId = sessionStorage.getItem('activeCategoryId');
    // MGU-971 - last minute CR - throw in a course filter!
    let coursefilter = sessionStorage.getItem('coursefilter') !== null ? sessionStorage.getItem('coursefilter') : 'creditcourses';

    // Account for returning to the page, or reloading.
    if (activeTab) {
        activetab = activeTab;
        let currentTab = document.querySelector('#current_tab');
        let pastTab = document.querySelector('#past_tab');

        switch (activetab) {
            case 'current':
                currentTab.classList.add('active');
                pastTab.classList.remove('active');
                break;
            case 'past':
                currentTab.classList.remove('active');
                pastTab.classList.add('active');
                break;
            default:
                break;
        }
    }

    if (activeCategoryId) {
        subcatId = activeCategoryId;
    }

    // Load the assessments for the "current" tab to begin with...
    loadAssessments(activetab, page, sortby, sortorder, subcatId, coursefilter);

    const triggerTabList = document.querySelectorAll('#courses-Tab button');

    // Bind our event listeners.
    triggerTabList.forEach(triggerEl => {
        triggerEl.addEventListener('click', handleTabChange);
        triggerEl.addEventListener('keyup', function(event) {
            let element = document.activeElement;
            if (event.keyCode === 13 && element.hasAttribute('tabindex')) {
                event.preventDefault();
                element.click();
            }
        });
    });
};

const loadAssessments = function(activetab, page, sortby, sortorder, subcategory = null, coursefilter = 'creditcourses') {
    let containerBlock = document.querySelector('#course_contents_container');

    let whichTemplate = subcategory === null ? 'coursecategory' : 'coursesubcategory';

    if (containerBlock.children.length > 0) {
        containerBlock.innerHTML = '';
    }

    containerBlock.insertAdjacentHTML("afterbegin", "<div class='loader d-flex justify-content-center'>\n" +
    "<div class='spinner-border m-5' role='status'><span class='hidden'>Loading...</span></div></div>");

    let promise = Ajax.call([{
        methodname: 'block_newgu_spdetails_get_assessments',
        args: {
            activetab: activetab,
            page: page,
            sortby: sortby,
            sortorder: sortorder,
            subcategory: subcategory,
            coursefilter: coursefilter
        }
    }]);
    promise[0].done(function(response) {
        document.querySelector('.loader').remove();
        let coursedata = JSON.parse(response.result);
        // MGU-971 - jam in the selected course filter option
        coursedata[`${coursefilter}`] = true;
        Templates.renderForPromise('block_newgu_spdetails/' + whichTemplate, {data: coursedata})
        .then(({html, js}) => {
            Templates.appendNodeContents(containerBlock, html, js);
            showPastCourseNotification(activetab);
            hideStatusColumn(activetab);
            let subCategories = document.querySelectorAll('.subcategory-row');
            let sortColumns = document.querySelectorAll('#category_table .th-sortable');
            subCategoryEventHandler(subCategories);
            subCategoryReturnHandler(coursedata.parent);
            sortingEventHandler(sortColumns);
            courseFilterEventHandler(activetab, page, sortby, sortorder, subcategory);

            // So we can allow returning to the last item correctly...
            sessionStorage.setItem('activeTab', activetab);
            if (subcategory) {
                sessionStorage.setItem('activeCategoryId', subcategory);
                document.querySelector('#courseNav-container').classList.add('hidden-container');
            } else {
                sessionStorage.removeItem('activeCategoryId');
                document.querySelector('#courseNav-container').classList.remove('hidden-container');
            }
            return true;
        }).catch((error) => displayException(error));
    }).fail(function(response) {
        if (response) {
            document.querySelector('.loader').remove();
            let errorContainer = document.createElement('div');
            errorContainer.classList.add('alert', 'alert-danger');

            if (response.hasOwnProperty('message')) {
                let errorMsg = document.createElement('p');

                errorMsg.innerHTML = response.message;
                errorContainer.appendChild(errorMsg);
                errorMsg.classList.add('errormessage');
                Log.debug(errorMsg);
            }

            if (response.hasOwnProperty('moreinfourl')) {
                let errorLinkContainer = document.createElement('p');
                let errorLink = document.createElement('a');

                errorLink.setAttribute('href', response.moreinfourl);
                errorLink.setAttribute('target', '_blank');
                errorLink.innerHTML = 'More information about this error';
                errorContainer.appendChild(errorLinkContainer);
                errorLinkContainer.appendChild(errorLink);
                errorLinkContainer.classList.add('errorcode');
            }

            containerBlock.prepend(errorContainer);
        }
    });
};

const hideStatusColumn = (activetab) => {
    if (activetab == 'past') {
        if (document.querySelector('#sortby_status')) {
            document.querySelector('#sortby_status').classList.add('hidden');
        }
    }
};

const showPastCourseNotification = (activetab) => {
    if (activetab == 'past') {
        let containerDiv = document.createElement('div');
        containerDiv.classList.add('border', 'rounded', 'my-2', 'p-2');
        let div = document.createElement('div');
        let strong = document.createElement('strong');
        div.classList.add('alert', 'alert-info', 'm-0', 'text-center');
        strong.append('Past provisional assessment grades will be displayed only for Academic Year 2024/25 onwards.' +
            ' Any before this, can be accessed from the MyCourses tab on Moodle or your individual course Moodle page.');
        div.appendChild(strong);
        containerDiv.appendChild(div);
        document.querySelector("#course_contents_container").prepend(containerDiv);
    }
};

const subCategoryEventHandler = (rows) => {
    if (rows.length > 0) {
        rows.forEach((element) => {
            element.addEventListener('click', () => showSubcategoryDetails(element));
        });
    }
};

const showSubcategoryDetails = (object) => {
    let id = object.parentElement.getAttribute('data-id');
    if (id !== null) {
        document.querySelector('#courseNav-container').classList.add('hidden-container');
        let currentTab = document.querySelector('#current_tab');
        let activetab = '';
        if (currentTab.classList.contains('active')) {
            activetab = 'current';
        } else {
            activetab = 'past';
        }
        // MGU-971 - last minute CR - throw in a course filter!
        let coursefilter = sessionStorage.getItem('coursefilter') !== null ?
            sessionStorage.getItem('coursefilter') : 'creditcourses';
        // Ordering by DueDate by default....
        loadAssessments(activetab, 0, 'duedate', 'asc', id, coursefilter);
    }
};

const subCategoryReturnHandler = (id) => {
    // The 'return to...' element won't exist on the page at the top most level.
    if (document.querySelector('#subcategory-return-assessment')) {
        document.querySelector('#subcategory-return-assessment').addEventListener('click', () => {
            // We now want to reload the previous level, using the previous id...
            // In order to display all courses, we pass null back to loadAssessments.
            if (id == 0 || id === null) {
                id = null;
                document.querySelector('#courseNav-container').classList.remove('hidden-container');
            }
            let currentTab = document.querySelector('#current_tab');
            let activetab = '';
            if (currentTab.classList.contains('active')) {
                activetab = 'current';
            } else {
                activetab = 'past';
            }
            // MGU-971 - last minute CR - throw in a course filter!
            let coursefilter = sessionStorage.getItem('coursefilter') !== null ?
                sessionStorage.getItem('coursefilter') : 'creditcourses';
            loadAssessments(activetab, 0, 'shortname', 'asc', id, coursefilter);
        });

        document.querySelector('#subcategory-return-assessment').addEventListener('keyup', function(event) {
            let element = document.activeElement;
            if (event.keyCode === 13 && element.hasAttribute('tabindex')) {
                event.preventDefault();
                element.click();
            }
        });
    }
};

/**
 * Function to bind click handlers to row headers.
 * @param {*} rows
 */
const sortingEventHandler = (rows) => {
    if (rows.length > 0) {
        rows.forEach((element) => {
            element.addEventListener('click', () => sortTable(element.cellIndex, element.getAttribute('data-sortby'),
            'category_table'));
        });
    }
};

/**
 * Function to now allow filtering courses on the category name, i.e. summative/formative.
 * @see https://uofglasgow.atlassian.net/browse/MGU-971 for further insight.
 * @param {*} activetab
 * @param {*} page
 * @param {*} sortby
 * @param {*} sortorder
 * @param {*} subcatId
 */
const courseFilterEventHandler = (activetab, page, sortby, sortorder, subcatId) => {
    window.console.log('courseFilterEventHandler called');
    let selector = document.querySelectorAll('#course_contents_container [data-region="filter"] ul li');
    selector.forEach((element) => {
        element.addEventListener('click', (e) => {
            window.console.log('e:', e);
            const option = e.target;

            if (option.classList.contains('active')) {
                // If it's already active then we don't need to do anything.
                return;
            }
            // Remove the previous value.
            sessionStorage.removeItem('coursefilter');
            const coursefilter = option.getAttribute('data-value');
            sessionStorage.setItem('coursefilter', coursefilter);

            loadAssessments(activetab, page, sortby, sortorder, subcatId, coursefilter);

            // This stops the page from jumping back to the top.
            e.preventDefault();
        });
    });

    selector.forEach((element) => {
        element.addEventListener('keyup', function(event) {
            let element = document.activeElement;
            if (event.keyCode === 13) {
                event.preventDefault();
                element.click();
            }
        });
    });
};

/**
 * Function to bind events to tabs.
 *
 * @param {object} event
 */
const handleTabChange = function(event) {
    event.preventDefault();

    let currentTab = document.querySelector('#current_tab');
    let pastTab = document.querySelector('#past_tab');
    let activetab = '';
    let page = 0;
    let sortby = 'shortname';
    let sortorder = 'asc';

    switch (event.target) {
        case currentTab:
            activetab = 'current';
            currentTab.classList.add('active');
            pastTab.classList.remove('active');
            break;
        case pastTab:
            activetab = 'past';
            currentTab.classList.remove('active');
            pastTab.classList.add('active');
            break;
        default:
            break;
    }
    // MGU-971 - last minute CR - throw in a course filter!
    let coursefilter = sessionStorage.getItem('coursefilter') !== null ?
    sessionStorage.getItem('coursefilter') : 'creditcourses';
    loadAssessments(activetab, page, sortby, sortorder, null, coursefilter);
};

/**
 * @constructor
 */
export const init = () => {
    initCourseTabs();
};