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
 * Course rule.
 *
 * @package    local_xp
 * @author     Frédéric Massart <fred@branchup.tech>
 * @copyright  2018 Frédéric Massart
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'core/templates', 'core/ajax', 'core/str', 'block_xp/dialogue-base'], function(
    $,
    Templates,
    Ajax,
    Str,
    DialogueBase
) {
    var SELECTOR_WRAPPER = '.block-xp-filters';
    var SELECTOR_WIDGET = '.local_xp-course-rule-widget';
    var SELECTOR_WIDGET_TRIGGER = '.local_xp-course-rule-widget button';
    var SELECTOR_RESOURCE_SELECTOR_WRAPPER = '.local_xp-course-selector-widget';

    /**
     * The dialogue.
     */
    function Dialogue() {
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
        return Str.get_string('courseselector', 'local_xp').then(
            function(title) {
                return Templates.render('local_xp/course-selector', {}).then(
                    function(html, js) {
                        this.setTitle(title);
                        this._setDialogueContent(html);
                        Templates.runTemplateJS(js);
                        this.center();
                        this.find(SELECTOR_RESOURCE_SELECTOR_WRAPPER).on(
                            'course-selected',
                            function(e, course) {
                                this.trigger('course-selected', course);
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
     */
    function init() {
        $(SELECTOR_WRAPPER).on('click', SELECTOR_WIDGET_TRIGGER, function(e) {
            e.preventDefault();
            var node = $(e.target).closest(SELECTOR_WIDGET);
            if (!node) {
                return;
            }

            var d = new Dialogue();
            d.on('course-selected', function(e, course) {
                node.find('.course-rule-courseid').val(course.id);
                node.find('.course-selected').text(M.util.get_string('rulecoursedesc', 'local_xp', course.shortname));
                node.addClass('has-course');
            });
            d.show();
        });
    }

    return {
        init: init
    };
});
