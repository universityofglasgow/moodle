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
 * Reimplementing this using Highcharts.
 *
 * @module     block_newgu_spdetails/assessmentsduesoon-v2
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
    DUESOON_CARD: '#assessments-due',
    DUESOON_BLOCK: '#assessmentsDueSoonContainer',
    COURSECONTENTS_BLOCK: '#courseTab-container',
    ASSESSMENTSDUE_BLOCK: '#assessmentsDue-container',
    ASSESSMENTSDUE_CONTENTS: '#assessmentsdue_content'
};

/**
 * @method fetchAssessmentsDueSoon - The main method of this script.
 *
 * Making this an async function to allow getStrings() to return correctly.
 * Previously, the string variables weren't getting assigned in time and
 * would not appear as expected on the chart.
 */
async function fetchAssessmentsDueSoon() {
    // Get the language specific strings first off.
    const requiredStrings = [
        {key: 'loading_text', component: 'block_newgu_spdetails'},
        {key: 'chart_24hrs', component: 'block_newgu_spdetails'},
        {key: 'chart_7days', component: 'block_newgu_spdetails'},
        {key: 'chart_14days', component: 'block_newgu_spdetails'},
        {key: 'chart_1mth', component: 'block_newgu_spdetails'},
        {key: 'chart_count', component: 'block_newgu_spdetails'},
        {key: 'duesoon_aria_label_text', component: 'block_newgu_spdetails'},
        {key: 'duesoon_accessibility_description', component: 'block_newgu_spdetails'},
        {key: 'duesoon_tooltip_preamble', component: 'block_newgu_spdetails'}
    ];
    let loading_text = '';
    let chart_24hrs = '';
    let chart_7days = '';
    let chart_14days = '';
    let chart_1mth = '';
    let chart_count = '';
    let aria_label_text = '';
    let accessibility_description = '';
    let duesoon_tooltip_preamble = '';

    await getStrings(requiredStrings).then((result) => {
        loading_text = result[0];
        chart_24hrs = result[1];
        chart_7days = result[2];
        chart_14days = result[3];
        chart_1mth = result[4];
        chart_count = result[5];
        aria_label_text = result[6];
        accessibility_description = result[7];
        duesoon_tooltip_preamble = result[8];
        return;
    }).catch((err) => {
        Log.debug(err);
        return;
    });

    let tempPanel = document.querySelector(Selectors.DUESOON_BLOCK);

    tempPanel.insertAdjacentHTML("afterbegin", "<div class='loader d-flex justify-content-center'>\n" +
        "<div class='spinner-border' role='status'><span class='hidden'>" + loading_text + "...</span></div></div>");

    ajax.call([{
        methodname: 'block_newgu_spdetails_get_assessmentsduesoon',
        args: {},
    }])[0].done(function(response) {
        document.querySelector('.loader').remove();
        let duein24hours = response[0]['duein24hours'];
        let duein7days = response[0]['duein7days'];
        let duein14days = response[0]['duein14days'];
        let duein1month = response[0]['duein1month'];

        // Set specific colours/fonts/weights etc for the Highcharts config object.
        let backgroundColour = '#FFFFFF';
        let tmpFontColour = '#000';
        let labelFontSize = '0.7em';
        let tooltipBackgroundColour = '#FFFFFF';
        let tooltipFontColour = '';
        // Check for the contrast setting
        if (document.querySelector('.hillhead40-night')) {
            tmpFontColour = '#95B7E6';
            backgroundColour = '#274163';
            tooltipBackgroundColour = '#132030';
            tooltipFontColour = '#95B7E6';
            document.querySelector('.alert.alert-info a').style.color='#95B7E6';
        }
        if (document.querySelector('.hillhead40-contrast-wb')) {
            tmpFontColour = '#eee';
            backgroundColour = '#000000';
            tooltipBackgroundColour = '#000000';
            tooltipFontColour = '#FFFFFF';
            document.querySelector('.alert.alert-info a').style.color='#eee';
        }
        if (document.querySelector('.hillhead40-contrast-yb')) {
            tmpFontColour = '#ee6';
            backgroundColour = '#000000';
            tooltipBackgroundColour = '#000000';
            tooltipFontColour = '#ee6';
            document.querySelector('.alert.alert-info a').style.color='#ee6';
        }
        if (document.querySelector('.hillhead40-contrast-by')) {
            document.querySelector('.alert.alert-info a').style.color='#000';
            backgroundColour = '#ee6';
            tooltipBackgroundColour = '#ee6';
        }
        if (document.querySelector('.hillhead40-contrast-wg')) {
            tmpFontColour = '#eee';
            backgroundColour = '#666';
            tooltipBackgroundColour = '#666';
            tooltipFontColour = '#eee';
            document.querySelector('.alert.alert-info a').style.color='#eee';
        }
        if (document.querySelector('.hillhead40-contrast-br')) {
            backgroundColour = '#EEB9B9';
            tooltipBackgroundColour = '#EEB9B9';
            document.querySelector('.alert.alert-info a').style.color='#000';
        }
        if (document.querySelector('.hillhead40-contrast-bb')) {
            backgroundColour = '#B9D9EE';
            tooltipBackgroundColour = '#B9D9EE';
            document.querySelector('.alert.alert-info a').style.color='#000';
        }
        if (document.querySelector('.hillhead40-contrast-bw')) {
            backgroundColour = '#F6F6F6';
            tooltipBackgroundColour = '#F6F6F6';
            document.querySelector('.alert.alert-info a').style.color='#000';
        }
        // Check for the font setting
        let tmpFontFamily = "'Hillhead', 'Ubuntu', 'Trebuchet MS', 'Arial', sans-serif";
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
        // Check for the size setting. We also further control the chart dimensions here.
        let tmpFontSize = 20;
        let tmpWidth = 400;
        let tmpHeight = 300;
        let tmpCardRem = '33rem';
        if (document.querySelector('.hillhead40-size-120')) {
            tmpFontSize = 'large';
            tmpWidth = 500;
            tmpHeight = 400;
            tmpCardRem = '70rem';
        }
        if (document.querySelector('.hillhead40-size-140')) {
            tmpFontSize = 'x-large';
            tmpWidth = 600;
            tmpHeight = 500;
            tmpCardRem = '70rem';
        }
        if (document.querySelector('.hillhead40-size-160')) {
            tmpFontSize = 'xx-large';
            tmpWidth = 700;
            tmpHeight = 600;
            tmpCardRem = '70rem';
        }
        if (document.querySelector('.hillhead40-size-180')) {
            tmpFontSize = 'xxx-large';
            tmpWidth = 800;
            tmpHeight = 700;
            tmpCardRem = '70rem';
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

        // Set the width/height of the card (container) and chart.
        let tempCard = document.querySelector(Selectors.DUESOON_CARD);
        tempCard.style.width = tmpCardRem;

        tempPanel.insertAdjacentHTML("afterbegin", "<figure><div id='assessmentsDueSoonChart' width='" + tmpWidth +
            "' height='" + tmpHeight + "'" +
            " aria-live='assertive' aria-atomic='true' aria-label='" + aria_label_text + "'></div></figure>");

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
            'highcharts/modules/no-data-to-display',
            'highcharts/modules/accessibility'
        ], function (Highcharts) {
            Highcharts.chart('assessmentsDueSoonChart', {
                chart: {
                    type: 'bar',
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
                credits: {
                    enabled: false
                },
                accessibility: {
                    description: accessibility_description,
                },
                legend: {
                    align: 'center',
                    verticalAlign: 'top',
                    layout: 'horizontal',
                    symbolRadius: 5,
                    symbolHeight: 20,
                    symbolWidth: 20,
                    itemStyle: {
                        color: tmpFontColour,
                        fontWeight: tmpFontWeight,
                        fontSize: tmpFontSize,
                    },
                    itemHoverStyle: {
                        color: tmpFontColour,
                        textDecoration: 'underline',
                    },
                    events: {
                        itemClick: function (e) {
                            // This prevents the strikethrough and column from being removed from the chart.
                            e.preventDefault();
                            let index = e.legendItem.index;
                            viewAssessmentsDueByChartType(index);
                        }
                    }
                },
                plotOptions: {
                    series: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        pointWidth: 50,
                        borderRadius: 8,
                        dataLabels: [{
                            enabled: true,
                            format: '{y}',
                            style: {
                                fontSize: labelFontSize,
                            },
                            x: -20
                        }],
                        showInLegend: true,
                        events: {
                            click: function (event) {
                                // Prevent the column from greying out when clicked.
                                let index = event.point.category;
                                viewAssessmentsDueByChartType(index);
                            }
                        },
                        states: {
                            select: {
                                color: ''
                            }
                        },
                    }
                },
                xAxis: {
                    type: 'category',
                    gridLineWidth: 1,
                    gridLineColor: tmpFontColour,
                    labels: {
                        style: {
                            color: tmpFontColour
                        }
                    }
                },
                yAxis: {
                    title: {
                        text: chart_count,
                    },
                    tickInterval: 1,
                    gridLineWidth: 1,
                    gridLineColor: tmpFontColour,
                    labels: {
                        style: {
                            color: tmpFontColour
                        }
                    }
                },
                tooltip: {
                    backgroundColor: tooltipBackgroundColour,
                    style: {
                        color: tooltipFontColour
                    },
                    format: '<span style="color:{color}">\u25CF</span>' + duesoon_tooltip_preamble
                    + '{key}: <b>{y}</b><br/>',
                    shared: true
                },
                series: [{
                    data: [{
                        name: chart_24hrs,
                        y: duein24hours
                    }],
                    color: 'rgba(255, 49, 49, 1)',
                    name: chart_24hrs,
                }, {
                    data: [{
                        name: chart_7days,
                        y: duein7days
                    }],
                    color: 'rgba(255, 222, 89, 1)',
                    name: chart_7days
                }, {
                    data: [{
                        name: chart_14days,
                        y: duein14days
                    }],
                    color: 'rgba(255, 145, 77, 1)',
                    name: chart_14days
                }, {
                    data: [{
                        name: chart_1mth,
                        y: duein1month
                    }],
                    color: 'rgba(0, 191, 99, 1)',
                    name: chart_1mth
                }]
            });
        });
    }).fail(function(err) {
        document.querySelector('.loader').remove();
        tempPanel.insertAdjacentHTML("afterbegin", "<div class='d-flex justify-content-center'>\n" +
            err.message + "</div>");
        Log.debug(err);
    });
}

/**
 * @method viewAssessmentsDueByChartType Click through to the relevant chart type.
 * @param {*} index
 */
const viewAssessmentsDueByChartType = function(index) {
    const chartType = index;

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
            assessmentsDueContents.scrollIntoView({behavior: "smooth"});
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
 * @method sortingEventHandler Function to bind click handlers to row headers.
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
 * @method returnToAssessmentsHandler Bind a click handler to the page element.
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
    fetchAssessmentsDueSoon();
};