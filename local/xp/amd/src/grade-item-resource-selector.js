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

define(['jquery', 'core/ajax', 'core/str', 'core/notification', 'block_xp/throttler', 'block_xp/resource-selector'],
    function($, Ajax, Str, Notification, Throttler, ResourceSelector) {

    var lastCourseId = null;
    var lastResources = [];

    /**
     * Save local cache.
     *
     * @param {Number} courseId The course ID.
     * @param {Array} resources The resources.
     */
    function saveLocalCache(courseId, resources) {
        lastCourseId = courseId;
        lastResources = resources;
    }

    /**
     * Grade item selector.
     *
     * @param {String|jQuery} container The container of the contents.
     * @param {jQuery} searchTermFieldNode The input field in which the use searches.
     */
    function GradeItemResourceSelector(container, searchTermFieldNode) {
        ResourceSelector.prototype.constructor.apply(this, [container, this.filterFunction.bind(this), searchTermFieldNode]);
        this.resources = lastResources;
        this.courseId = lastCourseId;
        this.throttler = new Throttler(200);
        this.setMinChars(0);
    }

    GradeItemResourceSelector.prototype = Object.create(ResourceSelector.prototype);
    GradeItemResourceSelector.prototype.constructor = GradeItemResourceSelector;

    GradeItemResourceSelector.prototype.initForCourse = function(courseId) {
        if (this.courseId == courseId) {
            // The course has not changed, display the contents right away.
            this.displayResults(this.resources);
            return;
        }

        this.displaySearching();
        this.courseId = courseId;
        this.fetchAllForCourse(courseId)
            .then(
                function(resources) {
                    if (this.courseId != courseId) {
                        // We switched course in the meantime, ignore.
                        return;
                    }
                    this.resources = resources;
                    this.displayResults(this.resources);
                    saveLocalCache(this.courseId, this.resources);
                }.bind(this)
            )
            .fail(function() {
                lastCourseId = null;
                lastResources = [];
                this.resources = [];
                this.displayEmptyResults();
            }.bind(this));
    };

    GradeItemResourceSelector.prototype.fetchAllForCourse = function(courseId) {
        var searchargs = {
            courseid: courseId,
            query: '*'
        };

        var calls = [
            {
                methodname: 'local_xp_search_grade_items',
                args: searchargs
            }
        ];

        return Ajax.call(calls)[0].then(function(results) {
            // Initially, we used get_strings with the params, but because of MDL-67434,
            // we're better off making sure that the strings are in cache, and then calling
            // the synchronous method.
            return Str.get_strings([
                {
                    component: 'local_xp',
                    key: 'categoryn',
                    param: ''
                },
                {
                    component: 'local_xp',
                    key: 'maxn',
                    param: ''
                },
            ]).then(() => {
                return results.map(gradeitem => {
                    var catpath = gradeitem.categories.map(c => c.name).join(' > ');
                    return {
                        _isgradeitem: true,
                        subname: `${M.util.get_string('maxn', 'local_xp', gradeitem.max)}` +
                                 `${gradeitem.categories.length ? ` - ${catpath}` : ''}`,
                        name: gradeitem.name,
                        gradeitem: gradeitem
                    };
                });
            });
        }).fail(err => {
            Notification.exception(err);
            return [];
        });
    };

    GradeItemResourceSelector.prototype.filterFunction = function(term) {
        term = (term || '').toLowerCase();
        if (!term) {
            return this.resources;
        }
        return this.resources.filter(function(gi) {
            return gi.name.toLowerCase().indexOf(term) > -1;
        });
    };

    return GradeItemResourceSelector;
});
