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
 * Javascript to initialise the Assessments Overview section.
 * Reimplementation using Highcharts as Chart.JS didn't give
 * us quite significant features e.g. accessibility, keyboard
 * navigation. This was left to the developer to implement, which
 * proved quite challenging in the end.
 *
 * @module     block_newgu_spdetails/assessmentsoverview
 * @author     Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @copyright  2024 University of Glasgow
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

"use strict";

import * as Log from 'core/log';
import * as ajax from 'core/ajax';
import {getStrings} from 'core/str';
import {exception as displayException} from 'core/notification';
import Templates from 'core/templates';
import sortTable from 'block_newgu_spdetails/sorting';

const Selectors = {
    SUMMARY_BLOCK: '#assessmentSummaryContainer',
    COURSECONTENTS_BLOCK: '#courseTab-container',
    ASSESSMENTSDUE_BLOCK: '#assessmentsDue-container',
    ASSESSMENTSDUE_CONTENTS: '#assessmentsdue_content'
};

/**
 * @method fetchAssessmentsOverview
 */
const fetchAssessmentsOverview = () => {
    let tempPanel = document.querySelector(Selectors.SUMMARY_BLOCK);

    tempPanel.insertAdjacentHTML("afterbegin", "<div class='loader d-flex justify-content-center'>\n" +
        "<div class='spinner-border' role='status'><span class='hidden'>Loading...</span></div></div>");

    ajax.call([{
        methodname: 'block_newgu_spdetails_get_assessmentsummary',
        args: {},
    }])[0].done(function(response) {
        document.querySelector('.loader').remove();
        let tobe_submitted = response[0].tobe_sub;
        let overdue = response[0].overdue;
        let submitted = response[0].sub_assess;
        let graded = response[0].assess_marked;
        tempPanel.insertAdjacentHTML("afterbegin", "<figure><div id='assessmentSummaryChart' width='400' height='300'" +
            " aria-live='assertive' aria-atomic='true' aria-label='Assessments overview. A chart displaying assessments to be" +
            " submitted, overdue, submitted and graded.'></div></figure>");

        // Set specific colours/fonts/weights etc for the Highcharts config object.
        let tmpFontColour = '#000';
        let backgroundColour = '#FFFFFF';
        let tooltipBackgroundColour = '#FFFFFF';
        let tooltipFontColour = '';
        let labelFontSize = '0.7em';
        let labelDistance = -28;
        const requiredStrings = [
            {key: 'status_text_tobesubmitted', component: 'block_newgu_spdetails'},
            {key: 'status_text_overdue', component: 'block_newgu_spdetails'},
            {key: 'status_text_submitted', component: 'block_newgu_spdetails'},
            {key: 'status_text_graded', component: 'block_newgu_spdetails'}
        ];
        let status_text_tobesubmitted = '';
        let status_text_overdue = '';
        let status_text_submitted = '';
        let status_text_graded = '';
        getStrings(requiredStrings).then((result) => {
            status_text_tobesubmitted = result[0];
            status_text_overdue = result[1];
            status_text_submitted = result[2];
            status_text_graded = result[3];
            return;
        }).catch((err) => {
            Log.debug(err);
            return;
        });
        // Check for the contrast setting
        if (document.querySelector('.hillhead40-night')) {
            tmpFontColour = '#95B7E6';
            backgroundColour = '#274163';
            tooltipBackgroundColour = '#132030';
            tooltipFontColour = '#95B7E6';
        }
        if (document.querySelector('.hillhead40-contrast-wb')) {
            tmpFontColour = '#eee';
            backgroundColour = '#000000';
            tooltipBackgroundColour = '#000000';
            tooltipFontColour = '#FFFFFF';
        }
        if (document.querySelector('.hillhead40-contrast-yb')) {
            tmpFontColour = '#ee6';
            backgroundColour = '#000000';
            tooltipBackgroundColour = '#000000';
            tooltipFontColour = '#ee6';
        }
        if (document.querySelector('.hillhead40-contrast-by')) {
        }
        if (document.querySelector('.hillhead40-contrast-wg')) {
            tmpFontColour = '#eee';
        }
        // Check for the font setting
        let tmpFontFamily = "'Hillhead', 'Ubuntu', 'Trebuchet MS', 'Arial', sans-serif";
        let tmpFontSize = 20;
        if (document.querySelector('.hillhead40-font-modern')) {
            tmpFontFamily = "'Helvetica Neue', 'Helvetica', 'Arial', sans-serif";
        }
        if (document.querySelector('.hillhead40-font-classic')) {
            tmpFontFamily = "'Palatino', 'Times New Roman', serif";
        }
        if (document.querySelector('.hillhead40-font-comic')) {
            tmpFontFamily = "'Hillhead Comic', 'Chalkboard', 'Comic Sans', 'Comic Sans MS', sans-serif";
        }
        if (document.querySelector('.hillhead40-font-mono')) {
            tmpFontFamily = "'Hillhead Mono', 'Menlo', 'Courier New', monospace";
        }
        if (document.querySelector('.hillhead40-font-dyslexic')) {
            tmpFontFamily = "'OpenDyslexic', 'Helvetica', 'Arial', sans-serif";
        }
        // Check for the size setting
        if (document.querySelector('.hillhead40-size-120')) {
            tmpFontSize = 'large';
            labelFontSize = 'large';
            labelDistance = -33;
        }
        if (document.querySelector('.hillhead40-size-140')) {
            tmpFontSize = 'x-large';
            labelFontSize = 'x-large';
            labelDistance = -29;
        }
        if (document.querySelector('.hillhead40-size-160')) {
            tmpFontSize = 'xx-large';
            labelFontSize = 'xx-large';
            labelDistance = -31;
        }
        if (document.querySelector('.hillhead40-size-180')) {
            tmpFontSize = 'xxx-large';
            labelFontSize = 'xxx-large';
            labelDistance = 0;
        }
        // Check for the bold setting
        let tmpFontWeight = 'normal';
        if (document.querySelector('.hillhead40-bold')) {
            tmpFontWeight = 'bolder';
        }
        // Check for the spacing setting
        let tmpLineHeight = '';
        if (document.querySelector('.hillhead40-spacing')) {
            tmpLineHeight = '2rem';
        }

        // We can hook into require.js, which is dead handy.
        require.config({
            packages: [{
                name: 'highcharts',
                main: 'highcharts'
            }],
            paths: {
                'highcharts': 'https://code.highcharts.com'
            }
        });
        require([
            'highcharts',
            'highcharts/modules/exporting',
            'highcharts/modules/accessibility'
        ], function (Highcharts) {
            Highcharts.chart('assessmentSummaryChart', {
                chart: {
                    type: 'pie',
                    marginRight: 150,
                    height: 300,
                    backgroundColor: backgroundColour,
                    style: {
                        fontFamily: tmpFontFamily,
                        fontWeight: tmpFontWeight,
                        fontSize: tmpFontSize,
                        lineHeight: tmpLineHeight
                    }
                },
                title: {
                    text: ''
                },
                accessibility: {
                    description: 'This is the Assessments overview chart. It displays your assessments that need to be submitted' +
                    ', are overdue, have been submitted, or have been graded.',
                },
                legend: {
                    align: 'right',
                    verticalAlign: 'middle',
                    layout: 'vertical',
                    symbolRadius: 5,
                    symbolHeight: 20,
                    symbolWidth: 20,
                    itemStyle: {
                        color: tmpFontColour,
                        fontWeight: tmpFontWeight,
                    },
                    itemHoverStyle: {
                        color: tmpFontColour,
                        textDecoration: 'underline',
                    },
                    events: {
                        itemClick: function (e) {
                            // This prevents the strikethrough and segment from being removed from the pie.
                            e.preventDefault();
                            let index = e.legendItem.index;
                            viewAssessmentsOverviewByChartType(index);
                        }
                    }
                },
                plotOptions: {
                    series: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        borderRadius: 8,
                        dataLabels: [{
                            enabled: true,
                            format: '{y}',
                            style: {
                                fontSize: labelFontSize
                            },
                            distance: labelDistance
                        }],
                        showInLegend: true,
                        events: {
                            click: function (e) {
                                let index = e.point.index;
                                viewAssessmentsOverviewByChartType(index);
                            }
                        }
                    }
                },
                tooltip: {
                    backgroundColor: tooltipBackgroundColour,
                    style: {
                        color: tooltipFontColour
                    },
                    format: '<span style="color:{color}">\u25CF</span>{key}: <b>{y}</b><br/>',
                    shared: true
                },
                series: [{
                    innerSize: '50%',
                    data: [{
                        name: status_text_tobesubmitted,
                        y: tobe_submitted,
                        color: 'rgba(255,153,0)',
                    }, {
                        name: status_text_overdue,
                        y: overdue,
                        color: 'rgba(255,0,0)',
                    }, {
                        name: status_text_submitted,
                        y: submitted,
                        color: 'rgba(0,153,0)',
                    }, {
                        name: status_text_graded,
                        y: graded,
                        color: 'rgba(129,187,255)',
                    }]
                }]
            });
        });

    }).fail(function(err) {
        document.querySelector('.loader').remove();
        tempPanel.insertAdjacentHTML("afterbegin", "<div class='d-flex justify-content-center'>\n" +
            err.message + "</div>");
        Log.debug(err);
    });
};

const viewAssessmentsOverviewByChartType = function(index) {
    window.console.log('viewAssessmentsOverviewByChartType called with:', index);
    const chartType = index;

    let containerBlock = document.querySelector(Selectors.COURSECONTENTS_BLOCK);
    if (containerBlock) {
        if (containerBlock.checkVisibility()) {
            containerBlock.classList.add('hidden-container');
        }
    }

    let assessmentsDueBlock = document.querySelector(Selectors.ASSESSMENTSDUE_BLOCK);
    let assessmentsDueContents = document.querySelector(Selectors.ASSESSMENTSDUE_CONTENTS);

    if (assessmentsDueBlock.children.length > 0) {
        assessmentsDueContents.innerHTML = '';
    }

    assessmentsDueBlock.classList.remove('hidden-container');

    assessmentsDueContents.insertAdjacentHTML("afterbegin", "<div class='loader d-flex justify-content-center'>\n" +
        "<div class='spinner-border' role='status'><span class='hidden'>Loading...</span></div></div>");

    ajax.call([{
        methodname: 'block_newgu_spdetails_get_assessmentsummarybytype',
        args: {
            charttype: chartType
        },
    }])[0].done(function(response) {
        document.querySelector('.loader').remove();
        let assessmentdata = JSON.parse(response.result);
        Templates.renderForPromise('block_newgu_spdetails/assessmentsdue', {data: assessmentdata})
        .then(({html, js}) => {
            Templates.appendNodeContents(assessmentsDueContents, html, js);
            returnToAssessmentsHandler();
            let sortColumns = document.querySelectorAll('#assessment_data_table .th-sortable');
            sortingEventHandler(sortColumns);
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

            assessmentsDueContents.prepend(errorContainer);
        }
    });
};

/**
 * Function to bind click handlers to row headers.
 * @param {*} rows
 */
const sortingEventHandler = (rows) => {
    if (rows.length > 0) {
        rows.forEach((element) => {
            element.addEventListener('click', () => sortTable(element.cellIndex, element.getAttribute('data-sortby'),
            'assessment_data_table'));
        });
    }
};

/**
 * @method returnToAssessmentsHandler
 */
const returnToAssessmentsHandler = () => {
    if (document.querySelector('#assessments-due-return')) {
        document.querySelector('#assessments-due-return').addEventListener('click', () => {
            let containerBlock = document.querySelector(Selectors.COURSECONTENTS_BLOCK);
            let assessmentsDueBlock = document.querySelector(Selectors.ASSESSMENTSDUE_BLOCK);
            assessmentsDueBlock.classList.add('hidden-container');
            containerBlock.classList.remove('hidden-container');
        });

        document.querySelector('#assessments-due-return').addEventListener('keyup', function(event) {
            let element = document.activeElement;
            if (event.keyCode === 13 && element.hasAttribute('tabindex')) {
                event.preventDefault();
                element.click();
            }
        });
    }
};

/**
 * @constructor
 */
export const init = () => {
    fetchAssessmentsOverview();
};