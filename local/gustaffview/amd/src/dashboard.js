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
 * This script serves to allow viewing the Student Dashboard as a teacher/
 * non-editing teacher. Selecting a student will update the UI with the
 * results of all assessments and their status. Pagination and sorting are
 * also available, however, some modifications have been made to accommodate
 * the plugin's shortcomings.
 *
 * @module     local_gustaffview/dashboard
 * @author     Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @copyright  2023 University of Glasgow
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

"use strict";

import * as Log from 'core/log';

const Selectors = {
    DASHBOARD_BLOCK: '#tmpContainer'
};

/**
 * @param {obj} event
 * @constructor
 */
const UpdateDashboard = (event) => {
    let studentid;
    let params;

    if (event.target.selectedOptions !== undefined) {
        studentid = event.target.selectedOptions[0].value;
    } else {
        studentid = document.querySelector('#selectstudent').selectedOptions[0].value;
    }

    let urlParams = new URLSearchParams(window.location.search);
    let courseid = urlParams.get('courseid');
    let tempPanel = document.querySelector(Selectors.DASHBOARD_BLOCK);
    tempPanel.innerHTML = '';

    if (courseid > 0 && studentid > 0) {

        tempPanel.insertAdjacentHTML("afterbegin","<div class='loader d-flex justify-content-center'>\n" +
            "<div class='spinner-border text-primary' role='status'><span class='hidden'>Loading...</span></div></div>");

        params = '?courseid=' + courseid + '&studentid=' + studentid;

        if (event.target.dataset.ts) {
            params += '&ts=' + event.target.dataset.ts;
        }

        if (event.target.dataset.tdr) {
            params += '&tdr=' + event.target.dataset.tdr;
        }

        if (event.target.dataset.page) {
            params += '&page=' + event.target.dataset.page;
        }

        // I'm supposed to be using Moodle's webservice here, however,
        // that involves either hooking into the other plugin's code,
        // writing a service from scratch (which, if it's only meant to
        // send JSON data back requires additional templating, classes
        // etc) or, to get this working, do things this way, for now of course.
        fetch('dashboard_panel.php' + params, {
            method: "GET",
            mode: "cors",
            cache: "no-cache",
            credentials: "same-origin",
            headers: {
                "Content-Type": "text/plain",
            },
            redirect: "follow",
            referrerPolicy: "no-referrer"
        })
        .then((response) => {
            if (!response.ok) {
                throw new Error(`HTTP error, status = ${response.status}`);
            }
            return response.text();
        })
        .then((html) => {
            document.querySelector('.loader').remove();
            let tempContainer = document.querySelector(Selectors.DASHBOARD_BLOCK);
            tempContainer.innerHTML = html;

            // Search for the sortable headings, bind a change event which
            // calls UpdateDashboard again, passing in the thing to sort.
            // Create a temp store for anything that's been sorted. This will
            // be used when iterating through the pagination nodes and adding
            // the thing to be sorted to that also.
            let sort_item = '';
            let sort_direction;
            let tmpHeaders = document.querySelectorAll(".header>a");
            tmpHeaders.forEach(header => {
                header.addEventListener('click', UpdateDashboard);
                if (header.children.length > 0) {
                    sort_item = header.children[0].dataset.ts;
                    sort_direction = header.children[0].dataset.tdr;
                }
            });

            // Same again, this time for the pagination...
            let tmpPagination = document.querySelectorAll(".pagination>li>a:not([aria-current='page'])");
            tmpPagination.forEach(pageLink => {
                pageLink.href = "#";
                let pageNumber = (pageLink.parentElement.dataset.pageNumber > 0) ? pageLink.parentElement.dataset.pageNumber-1 : 0;
                pageLink.setAttribute('data-page', pageNumber);
                pageLink.addEventListener('click', function(event){
                    event.target.dataset.page = pageNumber;
                    if (sort_item !== '') {
                        event.target.dataset.ts = sort_item;
                        // We need to reverse this here, as it screws up and sorts everything
                        // in the opposite direction otherwise. The approach taken for this is
                        // only suitable for 1 page of results - it's not been coded (well)
                        // to work over multiple pages :-(
                        event.target.dataset.tdr = ((sort_direction === "3") ? "4" : "3");
                    }
                    UpdateDashboard(event);
                });
            });
        }).catch((error) => {
            document.querySelector(Selectors.DASHBOARD_BLOCK).innerHTML = '<div class="alert alert-danger" ' +
            'role="alert">Something went wrong.</div>';
            Log.debug(`Error: ${error.message}`);
        });
    }
};

const registerEventListeners = () => {
    document.querySelector('#selectstudent').addEventListener('change', UpdateDashboard);
   // If the page is refreshed, trigger the change handler again...
   if (document.querySelector('#selectstudent').selectedOptions[0].value > 0) {
       document.querySelector('#selectstudent').dispatchEvent(new Event("change"));
   }
};

export const init = () => {
    registerEventListeners();
};