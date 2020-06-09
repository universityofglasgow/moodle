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
 * Grade item rule.
 *
 * @package    block_xp
 * @author     Frédéric Massart <fred@branchup.tech>
 * @copyright  2019 Frédéric Massart
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'core/templates', 'core/str', 'block_xp/dialogue-base'], function($, Templates, Str, DialogueBase) {
    var SELECTOR_WRAPPER = '.block-xp-filters';
    var SELECTOR_WIDGET = '.local_xp-grade-item-rule-widget';
    var SELECTOR_WIDGET_TRIGGER = '.local_xp-grade-item-rule-widget button';
    var SELECTOR_RESOURCE_SELECTOR_WRAPPER = '.local_xp-grade-item-selector-widget';

    /**
     * The dialogue.
     *
     * @param {Object} [initWithCourse] The course to initialise with.
     * @param {Boolean} [canChangeCourse] The course to initialise with.
     */
    function Dialogue(initWithCourse, canChangeCourse) {
        this.initWithCourse = initWithCourse || null;
        this.canChangeCourse = canChangeCourse;
        DialogueBase.prototype.constructor.apply(this, []);
    }
    Dialogue.prototype = Object.create(DialogueBase.prototype);
    Dialogue.prototype.constructor = Dialogue;

    /**
     * Render.
     *
     * @return {Promise} The promise.
     */
    Dialogue.prototype._render = function() {
        var initWithCourseJson = JSON.stringify(this.initWithCourse);
        return Str.get_string('gradeitemselector', 'local_xp').then(
            function(title) {
                return Templates.render('local_xp/grade-item-selector', {
                    canchangecourse: this.canChangeCourse,
                    initwithcoursejson: initWithCourseJson
                }).then(
                    function(html, js) {
                        this.setTitle(title);
                        this._setDialogueContent(html);
                        Templates.runTemplateJS(js);
                        this.center();
                        this.find(SELECTOR_RESOURCE_SELECTOR_WRAPPER).on(
                            'grade-item-selected',
                            function(e, resource) {
                                this.trigger('grade-item-selected', resource);
                                this.close();
                            }.bind(this)
                        );
                    }.bind(this)
                );
            }.bind(this)
        );
    };

    /**
     * Initialise the widgets.
     *
     * @param {Object} [initWithCourse] The course to initialise with.
     * @param {Boolean} [canChangeCourse] The course to initialise with.
     */
    function init(initWithCourse, canChangeCourse) {
        $(SELECTOR_WRAPPER).on('click', SELECTOR_WIDGET_TRIGGER, function(e) {
            e.preventDefault();
            var node = $(e.target).closest(SELECTOR_WIDGET);
            if (!node) {
                return;
            }

            var d = new Dialogue(initWithCourse, canChangeCourse);
            d.on('grade-item-selected', function(e, resource) {
                var gradeitem = resource.gradeitem;
                var course = resource.course;
                var strIdentifier = canChangeCourse ? 'rulegradeitemdescwithcourse' : 'rulegradeitemdesc';
                node.find('.grade-item-rule-itemid').val(gradeitem.id);
                node.find('.grade-item-selected').text(
                    M.util.get_string(strIdentifier, 'local_xp', {
                        gradeitemname: gradeitem.name,
                        coursename: course.shortname
                    })
                );
                node.addClass('has-grade-item');
            });
            d.show();
        });
    }

    return {
        init: init
    };
});
