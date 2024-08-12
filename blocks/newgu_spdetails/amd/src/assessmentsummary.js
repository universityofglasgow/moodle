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
 * Javascript to initialise the Assessment Summary section
 *
 * @module     block_newgu_spdetails/assessmentsummary
 * @author     Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @copyright  2023 University of Glasgow
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

"use strict";

import * as Log from 'core/log';
import * as ajax from 'core/ajax';
import {Chart, DoughnutController} from 'core/chartjs';
import {exception as displayException} from 'core/notification';
import Templates from 'core/templates';
import sortTable from 'block_newgu_spdetails/sorting';

const Selectors = {
    SUMMARY_BLOCK: '#assessmentSummaryContainer',
    COURSECONTENTS_BLOCK: '#courseTab-container',
    ASSESSMENTSDUE_BLOCK: '#assessmentsDue-container',
    ASSESSMENTSDUE_CONTENTS: '#assessmentsdue_content'
};

const viewAssessmentSummaryByChartType = function(event, legendItem, legend) {
    Log.debug('viewAssessmentSummaryByChartType called');
    // We don't want this firing from the main Dashboard page.
    if (!document.querySelector('#student_dashboard')) {
        const chartType = ((legendItem) ? legendItem.index : legend);

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
 * @method fetchAssessmentSummary
 */
const fetchAssessmentSummary = () => {
    Chart.register(DoughnutController);
    let tempPanel = document.querySelector(Selectors.SUMMARY_BLOCK);

    tempPanel.insertAdjacentHTML("afterbegin", "<div class='loader d-flex justify-content-center'>\n" +
        "<div class='spinner-border' role='status'><span class='hidden'>Loading...</span></div></div>");

    ajax.call([{
        methodname: 'block_newgu_spdetails_get_assessmentsummary',
        args: {},
    }])[0].done(function(response) {
        document.querySelector('.loader').remove();
        // With the 'block' now being a link in the top nav, users can still add this,
        // either in the side drawer or to the main dashboard. Check and set the position
        // of the legend accordingly.
        let legendPosition = 'right';
        if (document.querySelector('#block-region-side-pre')) {
            if (document.querySelector('#block-region-side-pre').querySelector('.block_newgu_spdetails')) {
                legendPosition = 'bottom';
            }
        }

        // This is lame, but we have no other way (in Chart.js) but to target these elements when using the accessibility tool.
        let tmpFontColour = '#000';
        // Check for the contrast setting
        if (document.querySelector('.hillhead40-night')) {
            tmpFontColour = '#95B7E6';
            document.querySelector('.alert.alert-info a').style.color='#95B7E6';
        }
        if (document.querySelector('.hillhead40-contrast-wb')) {
            tmpFontColour = '#eee';
            document.querySelector('.alert.alert-info a').style.color='#eee';
        }
        if (document.querySelector('.hillhead40-contrast-yb')) {
            tmpFontColour = '#ee6';
            document.querySelector('.alert.alert-info a').style.color='#ee6';
        }
        if (document.querySelector('.hillhead40-contrast-by')) {
            document.querySelector('.alert.alert-info a').style.color='#000';
        }
        if (document.querySelector('.hillhead40-contrast-wg')) {
            tmpFontColour = '#eee';
            document.querySelector('.alert.alert-info a').style.color='#eee';
        }
        if (document.querySelector('.hillhead40-contrast-br')) {
            document.querySelector('.alert.alert-info a').style.color='#000';
        }
        if (document.querySelector('.hillhead40-contrast-bb')) {
            document.querySelector('.alert.alert-info a').style.color='#000';
        }
        if (document.querySelector('.hillhead40-contrast-bw')) {
            document.querySelector('.alert.alert-info a').style.color='#000';
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
            tmpFontSize = '20%';
        }
        if (document.querySelector('.hillhead40-size-140')) {
            tmpFontSize = '25%';
        }
        if (document.querySelector('.hillhead40-size-160')) {
            tmpFontSize = '30%';
        }
        if (document.querySelector('.hillhead40-size-180')) {
            tmpFontSize = '35%';
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

        tempPanel.insertAdjacentHTML("afterbegin", "<canvas id='assessmentSummaryChart'\n" +
            " width='400' height='300' aria-label='Assessment Summary chart data' role='graphics-object'>\n" +
            "<p>The &lt;canvas&gt; element appears to be unsupported in your browser.</p>\n" +
            "</canvas>");

        const data = [
            {
                labeltitle: `To be submitted`,
                value: response[0].tobe_sub
            },
            {
                labeltitle: `Overdue`,
                value: response[0].overdue
            },
            {
                labeltitle: `Submitted`,
                value: response[0].sub_assess
            },
            {
                labeltitle: `Graded`,
                value: response[0].assess_marked
            },
        ];


        const ctw = document.getElementById('assessmentSummaryChart');
        const chart = new Chart(
            ctw,
            {
                type: 'doughnut',
                options: {
                    responsive: true,
                    onHover: (event, chartElement) => {
                        if (!document.querySelector('#student_dashboard')) {
                            event.native.target.style.cursor = chartElement[0] ? 'pointer' : 'default';
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: legendPosition,
                            onClick: (event, legendItem, legend) => {
                                if (!document.querySelector('#student_dashboard')) {
                                    viewAssessmentSummaryByChartType(event, legendItem, legend);
                                }
                            },
                            onHover: (event) => {
                                if (!document.querySelector('#student_dashboard')) {
                                    event.native.target.style.cursor = 'pointer';
                                }
                            },
                            onLeave: (event) => {
                                event.native.target.style.cursor = 'default';
                            },
                            labels: {
                                usePointStyle: true,
                                font: {
                                    size: tmpFontSize,
                                    family: tmpFontFamily,
                                    weight: tmpFontWeight,
                                    lineHeight: tmpLineHeight,
                                },
                                generateLabels: (chart) => {
                                    const datasets = chart.data.datasets;
                                    return datasets[0].data.map((data, i) => ({
                                        text: `${chart.data.labels[i]} ${data}`,
                                        borderRadius: 0,
                                        datasetIndex: i,
                                        fillStyle: datasets[0].backgroundColor[i],
                                        fontColor: tmpFontColour,
                                        hidden: false,
                                        lineCap: '',
                                        lineDash: [],
                                        lineDashOffset: 0,
                                        lineJoin: '',
                                        lineWidth: 0,
                                        strokeStyle: datasets[0].backgroundColor[i],
                                        pointStyle: 'rectRounded',
                                        rotation: 0,
                                        index: i
                                    }));
                                }
                            },
                        }
                    },
                    radius: '100%',
                    maintainAspectRatio: false
                },
                data: {
                    labels: data.map(row => row.labeltitle),
                    datasets: [{
                        data: data.map(row => row.value),
                        backgroundColor: [
                            'rgba(255,153,0)',
                            'rgba(255,0,0)',
                            'rgba(0,153,0)',
                            'rgba(129,187,255)'
                        ],
                        hoverOffset: 4
                    }]
                }
            }
        );

        const canvas = document.getElementById('assessmentSummaryChart');
        canvas.onclick = (evt) => {
            const points = chart.getElementsAtEventForMode(evt, 'nearest', {intersect: true}, true);

            if (points.length) {
                const firstPoint = points[0];
                viewAssessmentSummaryByChartType(evt, null, firstPoint.index);
            }
          };

    }).fail(function(err) {
        document.querySelector('.loader').remove();
        tempPanel.insertAdjacentHTML("afterbegin", "<div class='d-flex justify-content-center'>\n" +
            err.message + "</div>");
        Log.debug(err);
    });
};

/**
 * @constructor
 */
export const init = () => {
    fetchAssessmentSummary();
};