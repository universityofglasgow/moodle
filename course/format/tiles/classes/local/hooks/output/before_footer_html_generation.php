<?php
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

namespace format_tiles\local\hooks\output;

/**
 * Allows plugins to add any elements to the page before footer.
 *
 * @package   format_tiles
 * @copyright 2024 David Watson {@link http://evolutioncode.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class before_footer_html_generation {

    /**
     * Callback to add pre-footer elements.
     *
     * @param \core\hook\output\before_footer_html_generation $hook
     */
    public static function callback(\core\hook\output\before_footer_html_generation $hook): void {
        global $PAGE;
        if (($PAGE->course->format ?? null) !== 'tiles') {
            // This is called on every page so check that we are in a tiles course first.
            return;
        }
        try {
            $editing = $PAGE->user_is_editing();
            $oncourseviewpage = $PAGE->pagetype == 'course-view-tiles';

            // On a mod/view.php page we may need JS to ensure that any clicks on course index menu launch modals where appropriate.
            $modviewpageneedsjs = false;
            $allowedmodals = null;

            if (get_config('format_tiles', 'usecourseindex')) {
                $allowedmodals = \format_tiles\local\util::allowed_modal_modules();
                if (!empty($allowedmodals['resources'] || !empty($allowedmodals['modules']))) {
                    // On /mod/xxx/view.php or course/view.php page passing in cmid, may need to launch modal JS.
                    // This is because the course index needs the JS.  So get details.
                    $matches = [];
                    preg_match('/^mod-([a-z]+)-view$/', $PAGE->pagetype, $matches);
                    $modviewpageneedsjs = (bool)($matches[1] ?? null);
                }
            }

            if (($oncourseviewpage && !$editing) || $modviewpageneedsjs) {
                $allowedmodals = $allowedmodals === null ? \format_tiles\local\util::allowed_modal_modules() : $allowedmodals;

                // Course module modals.
                $launchmodalcmid = null;
                if (!empty($allowedmodals['resources'] || !empty($allowedmodals['modules']))) {
                    // If we are on course/view.php, get details.
                    $launchmodalcmid = ($oncourseviewpage && !$editing) ? optional_param('cmid', null, PARAM_INT) : null;
                    if ($launchmodalcmid) {
                        // Need to check if this cm allowed a modal.
                        $modalallowed =
                            \format_tiles\local\util::get_course_mod_info($PAGE->course->id, $launchmodalcmid)->modalallowed ?? false;
                        if (!$modalallowed) {
                            $launchmodalcmid = null;
                        }
                    }
                }
                $PAGE->requires->js_call_amd(
                    'format_tiles/course_mod_modal', 'init',
                    [$PAGE->course->id, false, $PAGE->pagetype, $launchmodalcmid]
                );
            }

            // Add our JS config HTML.
            // Avoid doing so if the header has not been printed.
            // (The caveat is because some plugins e.g. mod/customcert/view.php when sending a PDF file may trigger this function).
            if ($PAGE->state === \moodle_page::STATE_IN_BODY) {
                $jsconfig = \format_tiles\output\course_output::get_js_config_data($PAGE->course->id, $allowedmodals ?? []);
                $renderer = $PAGE->get_renderer('format_tiles');
                $hook->add_html($renderer->render_from_template('format_tiles/js-config', ['tiles_js_config' => $jsconfig]));
            }

        } catch (\Exception $e) {
            debugging("Could not prepare format_tiles footer data: " . $e->getMessage(), DEBUG_DEVELOPER);
        }
        return;
    }
}
