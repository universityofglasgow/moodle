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
 * Javascript to initialise the Student Dashboard - Assessments Details block
 *
 * @package    block_gu_spdetails
 * @copyright  2021 Accenture
 * @author     Franco Louie Magpusao <franco.l.magpusao@accenture.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['core/ajax'], function(Ajax) {
    const onClickListeners = function(event) {
        var currentTab = document.getElementById('current_tab');
        var pastTab = document.getElementById('past_tab');

        var sortByCourse = document.getElementById('sortby_course');
        var sortByDate = document.getElementById('sortby_date');
        var sortByStartDate = document.getElementById('sortby_startdate');
        var sortByEndDate = document.getElementById('sortby_enddate');
        var isPageClicked = false;

        switch(event.target) {
            case currentTab:
                var activetab = 'current';
                var page = 0;
                var sortby = 'coursetitle';
                var sortorder = 'asc';

                currentTab.classList.add('active');
                pastTab.classList.remove('active');

                loadAssessments(activetab, page, sortby, sortorder, isPageClicked);
                break;
            case pastTab:
                var activetab = 'past';
                var page = 0;
                var sortby = 'coursetitle';
                var sortorder = 'asc';

                currentTab.classList.remove('active');
                pastTab.classList.add('active');

                loadAssessments(activetab, page, sortby, sortorder, isPageClicked);
                break;
            case sortByCourse:
                if(currentTab.classList.contains('active')) {
                    var activetab = 'current';
                }else{
                    var activetab = 'past';
                }

                var page = 0;
                var sortby = 'coursetitle';
                if(sortByCourse.getAttribute('data-value') == 'asc') {
                    sortorder = 'desc';
                    sortByCourse.setAttribute('data-value', 'desc');
                    sortByCourse.classList.add('th-sort-desc');
                    sortByCourse.classList.remove('th-sort-asc');
                }else{
                    sortorder = 'asc';
                    sortByCourse.setAttribute('data-value', 'asc');
                    sortByCourse.classList.add('th-sort-asc');
                    sortByCourse.classList.remove('th-sort-desc');
                }
                loadAssessments(activetab, page, sortby, sortorder, isPageClicked);
                break;
            case sortByDate:
                var activetab = 'current';
                var page = 0;
                var sortby = 'duedate';
                if(sortByDate.getAttribute('data-value') == 'asc') {
                    sortorder = 'desc';
                    sortByDate.setAttribute('data-value', 'desc');
                    sortByDate.classList.add('th-sort-desc');
                    sortByDate.classList.remove('th-sort-asc');
                }else{
                    sortorder = 'asc';
                    sortByDate.setAttribute('data-value', 'asc');
                    sortByDate.classList.add('th-sort-asc');
                    sortByDate.classList.remove('th-sort-desc');
                }
                loadAssessments(activetab, page, sortby, sortorder, isPageClicked);
                break;
            case sortByStartDate:
                var activetab = 'past';
                var page = 0;
                var sortby = 'startdate';
                if(sortByStartDate.getAttribute('data-value') == 'asc') {
                    sortorder = 'desc';
                    sortByStartDate.setAttribute('data-value', 'desc');
                    sortByStartDate.classList.add('th-sort-desc');
                    sortByStartDate.classList.remove('th-sort-asc');
                }else{
                    sortorder = 'asc';
                    sortByStartDate.setAttribute('data-value', 'asc');
                    sortByStartDate.classList.add('th-sort-asc');
                    sortByStartDate.classList.remove('th-sort-desc');
                }
                loadAssessments(activetab, page, sortby, sortorder, isPageClicked);
                break;
            case sortByEndDate:
                var activetab = 'past';
                var page = 0;
                var sortby = 'enddate';
                if(sortByEndDate.getAttribute('data-value') == 'asc') {
                    sortorder = 'desc';
                    sortByEndDate.setAttribute('data-value', 'desc');
                    sortByEndDate.classList.add('th-sort-desc');
                    sortByEndDate.classList.remove('th-sort-asc');
                }else{
                    sortorder = 'asc';
                    sortByEndDate.setAttribute('data-value', 'asc');
                    sortByEndDate.classList.add('th-sort-asc');
                    sortByEndDate.classList.remove('th-sort-desc');
                }
                loadAssessments(activetab, page, sortby, sortorder, isPageClicked);
                break;
            default:
                break;
        }
    }

    const onChangeListeners = function(event) {
        var currentSelectSort = document.getElementById('menu_current_assessments_sortby');
        var pastSelectSort = document.getElementById('menu_past_assessments_sortby');
        var sortByCourse = document.getElementById('sortby_course');
        var sortByDate = document.getElementById('sortby_date');
        var sortByStartDate = document.getElementById('sortby_startdate');
        var sortByEndDate = document.getElementById('sortby_enddate');
        var isPageClicked = false;

        switch(event.target) {
            case currentSelectSort:
                var activetab = 'current';
                var page = 0;
                var sortby = currentSelectSort.value;
                var sortorder = 'asc';

                if(currentSelectSort.value === 'coursetitle') {
                    sortByCourse.classList.add('th-sort-asc');
                    sortByCourse.classList.remove('th-sort-desc');
                    sortByCourse.setAttribute('data-value', 'asc');
                }else{
                    sortByDate.classList.add('th-sort-asc');
                    sortByDate.classList.remove('th-sort-desc');
                    sortByDate.setAttribute('data-value', 'asc');
                }

                loadAssessments(activetab, page, sortby, sortorder, isPageClicked);
                break;
            case pastSelectSort:
                var activetab = 'past';
                var page = 0;
                var sortby = pastSelectSort.value;
                var sortorder = 'asc';

                if(pastSelectSort.value === 'coursetitle') {
                    sortByCourse.classList.add('th-sort-asc');
                    sortByCourse.classList.remove('th-sort-desc');
                    sortByCourse.setAttribute('data-value', 'asc');
                }else if(pastSelectSort.value === 'startdate') {
                    sortByStartDate.classList.add('th-sort-asc');
                    sortByStartDate.classList.remove('th-sort-desc');
                    sortByStartDate.setAttribute('data-value', 'asc');
                }else{
                    sortByEndDate.classList.add('th-sort-asc');
                    sortByEndDate.classList.remove('th-sort-desc');
                    sortByEndDate.setAttribute('data-value', 'asc');
                }

                loadAssessments(activetab, page, sortby, sortorder, isPageClicked);
                break;
            default:
                break;
        }
    }

    const loadAssessments = function(activetab, page, sortby, sortorder, isPageClicked, subcategory = null) {
        var blockElement = document.querySelector('.block_gu_spdetails');
        var assessmentContainer = document.getElementById('assessments-container');
        var subcategoryContainer = document.getElementById('subcategory-container');
        var blockContainer = subcategory === null ? assessmentContainer : subcategoryContainer;
        var tabContent = subcategory === null ? document.getElementById('assessments_details_contents') : document.getElementById('subcategory_details_contents');

        if (subcategory === null) {
            assessmentContainer.classList.remove('hidden-container');
            subcategoryContainer.classList.add('hidden-container');
        } else {
            assessmentContainer.classList.add('hidden-container');
            subcategoryContainer.classList.remove('hidden-container');
        }

        var promise = Ajax.call([{
            methodname: 'block_gu_spdetails_retrieve_assessments',
            args: {
                activetab: activetab,
                page: page,
                sortby: sortby,
                sortorder: sortorder,
                subcategory: subcategory
            },
        }]);
        promise[0].done(function(response) {
            tabContent.innerHTML = response.result;
            var subCategories = document.querySelectorAll('.subcategory-row');
            onClickSubcategory(subCategories);
            onClickPageLink(subcategory);
            sortingStatus(sortby, sortorder);
            if(isPageClicked) {
                blockElement.scrollIntoView();
            }
            if(subcategory !== null) {
                blockElement.scrollIntoView();
                if(sortorder === 'asc') {
                    document.getElementById('sortby_date_subcategory').classList.add('th-sort-asc');
                    document.getElementById('sortby_date_subcategory').classList.remove('th-sort-desc');
                    document.getElementById('sortby_date_subcategory').setAttribute('data-value', 'asc');
                }else{
                    document.getElementById('sortby_date_subcategory').classList.add('th-sort-desc');
                    document.getElementById('sortby_date_subcategory').classList.remove('th-sort-asc');
                    document.getElementById('sortby_date_subcategory').setAttribute('data-value', 'desc');
                }
                blockElement.addEventListener("keyup", function(event) {
                    element = document.activeElement;
                    if (event.keyCode === 13 && element.hasAttribute('tabindex')) {
                    event.preventDefault();
                    element.click();
                    }
                });
                document.getElementById('sortby_date_subcategory').addEventListener('click', () => {
                    var activetab = 'current';
                    var page = 0;
                    var sortby = 'duedate';
                    var sortorder = ''
                    if(document.getElementById('sortby_date_subcategory').getAttribute('data-value') == 'asc') {
                        sortorder = 'desc';
                        document.getElementById('sortby_date_subcategory').setAttribute('data-value', 'desc');
                        document.getElementById('sortby_date_subcategory').classList.add('th-sort-desc');
                        document.getElementById('sortby_date_subcategory').classList.remove('th-sort-asc');
                    }else{
                        sortorder = 'asc';
                        document.getElementById('sortby_date_subcategory').setAttribute('data-value', 'asc');
                        document.getElementById('sortby_date_subcategory').classList.add('th-sort-asc');
                        document.getElementById('sortby_date_subcategory').classList.remove('th-sort-desc');
                    }
                    loadAssessments(activetab, page, sortby, sortorder, isPageClicked, subcategory);
                })
            }
        }).fail(function(response) {
            if(response) {
                var errorContainer = document.createElement('div');
                errorContainer.classList.add('alert', 'alert-danger');

                if(response.hasOwnProperty('message')) {
                    var errorMsg = document.createElement('p');

                    errorMsg.innerHTML = response.message;
                    errorContainer.appendChild(errorMsg);
                    errorMsg.classList.add('errormessage');
                }

                if(response.hasOwnProperty('moreinfourl')) {
                    var errorLinkContainer = document.createElement('p');
                    var errorLink = document.createElement('a');

                    errorLink.setAttribute('href', response.moreinfourl);
                    errorLink.setAttribute('target', '_blank');
                    errorLink.innerHTML = 'More information about this error';
                    errorContainer.appendChild(errorLinkContainer);
                    errorLinkContainer.appendChild(errorLink);
                    errorLinkContainer.classList.add('errorcode');
                }

                blockContainer.prepend(errorContainer);
            }
        });
    }

    const showSubcategoryDetails = (object) => {
        id = object.getAttribute('data-id');
        subname = object.getAttribute('data-name');
        course = object.getAttribute('data-course');
        grade = object.getAttribute('data-grade');
        weight = object.getAttribute('data-weight');

        if(id !== null){
            loadAssessments('current', 0, 'duedate', 'asc', false, id);
            document.getElementById('subcategory-details-course').innerHTML = course;
            document.getElementById('subcategory-details-weight').innerHTML = weight;
            document.getElementById('subcategory-details-grade').innerHTML = grade;
            document.getElementById('subcategory-details-name').innerHTML = subname;
            document.getElementById('subcategory-return-assessment').addEventListener('click', () => {
                document.getElementById('assessments-container').classList.remove('hidden-container');
                document.getElementById('subcategory-container').classList.add('hidden-container');
                document.getElementById('subcategory_details_contents').innerHTML = "";
            });
        }
    }

    const sortingStatus = function(sortby, sortorder) {
        var sortByCourse = document.getElementById('sortby_course');
        var sortByDate = document.getElementById('sortby_date');
        var sortByStartDate = document.getElementById('sortby_startdate');
        var sortByEndDate = document.getElementById('sortby_enddate');

        switch(sortby) {
            case 'coursetitle':
                if(sortByCourse) {
                    if(sortorder === 'asc') {
                        sortByCourse.classList.add('th-sort-asc');
                        sortByCourse.classList.remove('th-sort-desc');
                        sortByCourse.setAttribute('data-value', 'asc');
                    }else{
                        sortByCourse.classList.add('th-sort-desc');
                        sortByCourse.classList.remove('th-sort-asc');
                        sortByCourse.setAttribute('data-value', 'desc');
                    }
                }
                break;
            case 'duedate':
                if(sortByDate) {
                    if(sortorder === 'asc') {
                        sortByDate.classList.add('th-sort-asc');
                        sortByDate.classList.remove('th-sort-desc');
                        sortByDate.setAttribute('data-value', 'asc');
                    }else{
                        sortByDate.classList.add('th-sort-desc');
                        sortByDate.classList.remove('th-sort-asc');
                        sortByDate.setAttribute('data-value', 'desc');
                    }
                }
                break;
            case 'startdate':
                if(sortByStartDate) {
                    if(sortorder === 'asc') {
                        sortByStartDate.classList.add('th-sort-asc');
                        sortByStartDate.classList.remove('th-sort-desc');
                        sortByStartDate.setAttribute('data-value', 'asc');
                    }else{
                        sortByStartDate.classList.add('th-sort-desc');
                        sortByStartDate.classList.remove('th-sort-asc');
                        sortByStartDate.setAttribute('data-value', 'desc');
                    }
                }
                break;
            case 'enddate':
                if(sortByEndDate) {
                    if(sortorder === 'asc') {
                        sortByEndDate.classList.add('th-sort-asc');
                        sortByEndDate.classList.remove('th-sort-desc');
                        sortByEndDate.setAttribute('data-value', 'asc');
                    }else{
                        sortByEndDate.classList.add('th-sort-desc');
                        sortByEndDate.classList.remove('th-sort-asc');
                        sortByEndDate.setAttribute('data-value', 'desc');
                    }
                }
                break;
            default:
                break;
        }
    }

    const onClickPageLink = function(subcategory) {
        var pageLinks = subcategory !== null ? document.querySelectorAll('#subcategory_details_contents .page-item a.page-link')
                                             : document.querySelectorAll('#assessments_details_contents .page-item a.page-link');

        pageLinks.forEach(function(item) {
            if(item.hasAttribute('href') && item.getAttribute('href') !== '#') {
                var url = new URL(item.getAttribute('href'));
                var params = new URLSearchParams(url.search);
                var activetab = params.get('activetab');
                var page = params.get('page');
                var sortby = params.get('sortby');
                var sortorder = params.get('sortorder');
                var isPageClicked = true;
                item.addEventListener('click', function(event) {
                    event.preventDefault();
                    loadAssessments(activetab, page, sortby, sortorder, isPageClicked, subcategory);
                });
            }else{
                item.removeAttribute('href');
            }
        });
    }

    const onClickSubcategory = (rows) => {
        if(rows.length > 0){
            rows.forEach( (element) => {
                element.addEventListener('click', () => showSubcategoryDetails(element));
            })
        }
    }

    return {
        init: function() {
            const ASSESSMENTS = document.querySelector('.assessments-details-container');
            if(ASSESSMENTS) {
                var currentTab = document.getElementById('current_tab');
                var pastTab = document.getElementById('past_tab');
                var activetab = 'current';
                var page = 0;
                var sortby = 'coursetitle';
                var sortorder = 'asc';
                var isPageClicked = false;

                currentTab.classList.add('active');
                pastTab.classList.remove('active');

                loadAssessments(activetab, page, sortby, sortorder, isPageClicked);
                ASSESSMENTS.addEventListener('change', onChangeListeners);
                ASSESSMENTS.addEventListener('click', onClickListeners);
                ASSESSMENTS.addEventListener("keyup", function(event) {
                    element = document.activeElement;
                    if (event.keyCode === 13 && element.hasAttribute('tabindex')) {
                    event.preventDefault();
                    element.click();
                    }
                });
            }
        }
    };
});
