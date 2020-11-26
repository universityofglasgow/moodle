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
 * Grade item selector.
 *
 * @package    local_xp
 * @copyright  2019 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'core/ajax', 'block_xp/course-resource-selector', 'local_xp/grade-item-resource-selector'], function(
    $,
    Ajax,
    CourseResourceSelector,
    GradeItemResourceSelector
) {
    var lastUsedCourse = null;

    /**
     * Initialise the module.
     *
     * @param {Jquery|String} selector The selector.
     * @param {Object} [initWithCourse] The course to initialise with.
     */
    function init(selector, initWithCourse) {
        initWithCourse = initWithCourse || lastUsedCourse;
        var container = $(selector);
        var currentCourse = null;
        var searchResultsContents = container.find('.search-result-contents');
        var courseSearchField = container.find('.search-term-course');
        var giSearchField = container.find('.search-term-grade-item');

        var cs = new CourseResourceSelector(searchResultsContents, courseSearchField);
        cs.onResourceSelected(function(e, resource) {
            if (!resource._iscourse) {
                return;
            }
            selectCourse(resource.course);
        });

        var gis = new GradeItemResourceSelector(searchResultsContents, giSearchField);
        gis.onResourceSelected(function(e, resource) {
            if (!resource._isgradeitem) {
                return;
            }
            container.trigger('grade-item-selected', {
                gradeitem: resource.gradeitem,
                course: currentCourse
            });
        });

        container.find('.course-selection-change').on('click', function() {
            reset();
        });

        /**
         * Reset.
         */
        function reset() {
            courseSearchField.val('');
            giSearchField.val('');
            cs.clear();
            container.find('.cm-search').hide();
            container.find('.course-search .course-not-selected').show();
            container.find('.course-search .course-selected').hide();
            currentCourse = null;
            courseSearchField.focus();
        }

        /**
         * Select a course.
         *
         * @param {Object} course The course.
         */
        function selectCourse(course) {
            currentCourse = course;
            lastUsedCourse = course;
            container.find('.course-search .course-selected span').text(course.fullname);
            container.find('.course-search .course-selected').show();
            container.find('.course-search .course-not-selected').hide();
            container.find('.cm-search').show();
            cs.clear();

            gis.initForCourse(course.id);
            giSearchField.val('');
            giSearchField.focus();
        }

        if (initWithCourse) {
            selectCourse(initWithCourse);
        } else {
            reset();
        }
    }

    return {
        init: init
    };
});
