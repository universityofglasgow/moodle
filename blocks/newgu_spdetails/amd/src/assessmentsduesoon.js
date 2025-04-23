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
 * JavaScript to initialise the Assessments due soon section.
 * We're using Chart.js v3.8.0 at present.
 *
 * @module     block_newgu_spdetails/assessmentsduesoon
 * @author     Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @copyright  2024 University of Glasgow
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

"use strict";

import * as Log from 'core/log';
import * as ajax from 'core/ajax';
import {Chart, BarController} from 'core/chartjs';
import {exception as displayException} from 'core/notification';
import Templates from 'core/templates';
import sortTable from 'block_newgu_spdetails/sorting';

const Selectors = {
    DUESOON_BLOCK: '#assessmentsDueSoonContainer',
    COURSECONTENTS_BLOCK: '#courseTab-container',
    ASSESSMENTSDUE_BLOCK: '#assessmentsDue-container',
    ASSESSMENTSDUE_CONTENTS: '#assessmentsdue_content'
};

const viewAssessmentsDueByChartType = function(chartItem, legendItem) {
    const chartType = ((legendItem) ? legendItem.datasetIndex : chartItem);

    let containerBlock = document.querySelector(Selectors.COURSECONTENTS_BLOCK);
    if (containerBlock.checkVisibility()) {
        containerBlock.classList.add('hidden-container');
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
        methodname: 'block_newgu_spdetails_get_assessmentsduebytype',
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
 * @method fetchAssessmentsDueSoon
 */
const fetchAssessmentsDueSoon = () => {
    Chart.register(BarController);
    let tempPanel = document.querySelector(Selectors.DUESOON_BLOCK);

    tempPanel.insertAdjacentHTML("afterbegin", "<div class='loader d-flex justify-content-center'>\n" +
        "<div class='spinner-border' role='status'><span class='hidden'>Loading...</span></div></div>");

    ajax.call([{
        methodname: 'block_newgu_spdetails_get_assessmentsduesoon',
        args: {},
    }])[0].done(function(response) {
        document.querySelector('.loader').remove();
        tempPanel.insertAdjacentHTML("afterbegin", "<canvas id='assessmentsDueSoonChart'\n" +
            " width='400' height='200' aria-label='Assessments Due Soon. A chart displaying" +
            " assessments that are due in the next 24 hours, 7 days, or month.' role='img' tabindex='0'>\n" +
            "<p>The 'Assessments due in the next' chart displays information about assessments that are due" +
            " in the next 24 hours, 7 days and 1 calendar month. Links to further information about these" +
            " assessments also form part of this chart.</p>\n" +
            "</canvas>");

        // This is lame, but we have no other way (in Chart.js) but to target these elements when using the accessibility tool.
        let tmpFontColour = '#000';
        let gridColour = '#eee';
        let gridLabelTextColour = '';
        // Check for the contrast setting
        if (document.querySelector('.hillhead40-night')) {
            tmpFontColour = '#95B7E6';
            gridColour = '#95B7E6';
            gridLabelTextColour = '#95B7E6';
            document.querySelector('.alert.alert-info a').style.color='#95B7E6';
        }
        if (document.querySelector('.hillhead40-contrast-wb')) {
            tmpFontColour = '#eee';
            gridLabelTextColour = '#eee';
            document.querySelector('.alert.alert-info a').style.color='#eee';
        }
        if (document.querySelector('.hillhead40-contrast-yb')) {
            tmpFontColour = '#ee6';
            gridColour = '#ee6';
            gridLabelTextColour = '#ee6';
            document.querySelector('.alert.alert-info a').style.color='#ee6';
        }
        if (document.querySelector('.hillhead40-contrast-by')) {
            gridColour = '#000';
            document.querySelector('.alert.alert-info a').style.color='#000';
        }
        if (document.querySelector('.hillhead40-contrast-wg')) {
            tmpFontColour = '#eee';
            gridLabelTextColour = '#eee';
            document.querySelector('.alert.alert-info a').style.color='#eee';
        }
        if (document.querySelector('.hillhead40-contrast-br')) {
            gridColour = '#000';
            document.querySelector('.alert.alert-info a').style.color='#000';
        }
        if (document.querySelector('.hillhead40-contrast-bb')) {
            gridColour = '#000';
            document.querySelector('.alert.alert-info a').style.color='#000';
        }
        if (document.querySelector('.hillhead40-contrast-bw')) {
            gridColour = '#000';
            document.querySelector('.alert.alert-info a').style.color='#000';
        }
        // Check for the font setting
        let tmpFontFamily = "'Hillhead', 'Ubuntu', 'Trebuchet MS', 'Arial', sans-serif";
        let tmpFontSize = 12;
        let tmpLegendSize = 20;
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
            tmpLegendSize = '25%';
        }
        if (document.querySelector('.hillhead40-size-140')) {
            tmpFontSize = '25%';
            tmpLegendSize = '30%';
        }
        if (document.querySelector('.hillhead40-size-160')) {
            tmpFontSize = '30%';
            tmpLegendSize = '35%';
        }
        if (document.querySelector('.hillhead40-size-180')) {
            tmpFontSize = '35%';
            tmpLegendSize = '40%';
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

        const data = [
            {
                labeltitle: `24 hours:`,
                value: response[0]['24hours']
            },
            {
                labeltitle: `7 days:`,
                value: response[0].week
            },
            {
                labeltitle: `month:`,
                value: response[0].month
            }
        ];

        const ctx = document.getElementById('assessmentsDueSoonChart');
        const chart = new Chart(ctx, {
                type: 'bar',
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    onHover: (event, chartElement) => {
                        event.native.target.style.cursor = chartElement[0] ? 'pointer' : 'default';
                    },
                    scales: {
                        x: {
                            suggestedMin: 1,
                            suggestedMax: 10,
                            grid: {
                                color: gridColour,
                            },
                            ticks: {
                                color: gridLabelTextColour,
                                font: {
                                    size: tmpFontSize,
                                    family: tmpFontFamily,
                                    weight: tmpFontWeight,
                                    lineHeight: tmpLineHeight,
                                }
                            }
                        },
                        y: {
                            grid: {
                                color: gridColour,
                            },
                            ticks: {
                                color: gridLabelTextColour,
                                font: {
                                    size: tmpFontSize,
                                    family: tmpFontFamily,
                                    weight: tmpFontWeight,
                                    lineHeight: tmpLineHeight,
                                }
                            }
                        },
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            onClick: viewAssessmentsDueByChartType,
                            onHover: (event) => {
                                event.native.target.style.cursor = 'pointer';
                            },
                            onLeave: (event) => {
                                event.native.target.style.cursor = 'default';
                            },
                            labels: {
                                usePointStyle: true,
                                font: {
                                    size: tmpLegendSize,
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
                },
                data: {
                    labels: data.map(row => row.labeltitle),
                    datasets: [{
                        data: data.map(row => row.value),
                        indexAxis: 'y',
                        backgroundColor: [
                            'rgba(255,0,0,0.6)',
                            'rgba(255,153,0,0.6)',
                            'rgba(0,153,0,0.6)'
                        ],
                        borderColor: [
                            'rgba(255,0,0)',
                            'rgba(255,153,0)',
                            'rgba(0,153,0)'
                        ],
                        borderWidth: 1,
                        hoverOffset: 4
                    }]
                }
            }
        );

        const canvas = document.getElementById('assessmentsDueSoonChart');
        canvas.onclick = (evt) => {
            const points = chart.getElementsAtEventForMode(
                evt,
                'nearest',
                {intersect: true},
                true
              );
              if (points.length === 0) {
                return;
              }
              viewAssessmentsDueByChartType(points[0].index);
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
    fetchAssessmentsDueSoon();
};