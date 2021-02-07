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
 * @copyright  2020 Accenture
 * @author     Franco Louie Magpusao <franco.l.magpusao@accenture.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['core/ajax'], function(Ajax) {
    const loadCurrentAssessments = () => {
        var currentTab = document.getElementById('current_tab');
        var pastTab = document.getElementById('past_tab');

        currentTab.classList.add('active');
        pastTab.classList.remove('active');
        var activetab = 'current';
        var page = 0;
        var sortby = 'course';
        var sortorder = 'asc';

        loadAssessments(activetab, page, sortby, sortorder);
    }

    const loadPastAssessments = () => {
        var currentTab = document.getElementById('current_tab');
        var pastTab = document.getElementById('past_tab');

        currentTab.classList.remove('active');
        pastTab.classList.add('active');
        var activetab = 'past';
        var page = 0;
        var sortby = 'course';
        var sortorder = 'asc';

        loadAssessments(activetab, page, sortby, sortorder);
    }

    const onClickListeners = (event) => {
        var currentTab = document.getElementById('current_tab');
        var pastTab = document.getElementById('past_tab');
        switch(event.target) {
            case currentTab:
                loadCurrentAssessments();
                break;
            case pastTab:
                loadPastAssessments();
                break;
            default:
                break;
        }
    }
    
    const loadAssessments = (activetab, page, sortby, sortorder) => {
        var tabContent = document.getElementById('assessments_details_contents');
        var promise = Ajax.call([{
            methodname: 'block_gu_spdetails_retrieve_assessments',
            args: {
                activetab: activetab,
                page: page,
                sortby: sortby,
                sortorder: sortorder
            },
        }]);
        promise[0].done(function(response) {
            tabContent.innerHTML = response.result;
            onClickPageLink();
        }).fail(function(response) {
            console.log(response);
        });
    }

    const onClickPageLink = () => {
        var pageLinks = document.querySelectorAll('#assessments_details_contents .page-item a.page-link');
        for (var i=0; i<pageLinks.length; i++) {
            var pageLink = pageLinks[i];

            pageLink.addEventListener('click', event => {
                event.preventDefault();
                var url = new URL(event.target.getAttribute('href'));
                var params = new URLSearchParams(url.search);
                var activetab = params.get('activetab');
                var page = params.get('page');
                var sortby = 'course';
                var sortorder = 'asc';

                loadAssessments(activetab, page, sortby, sortorder);
            });
        }
    }

    return {
        init: function() {                
            const ASSESSMENTS = document.querySelector('.assessments-details-container');
            if(ASSESSMENTS) {
                loadCurrentAssessments();
                ASSESSMENTS.addEventListener('click', onClickListeners);
            }
        }
    };
});
