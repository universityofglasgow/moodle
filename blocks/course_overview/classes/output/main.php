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

/**
 * Main class for course listing
 *
 * @package    block_course_overview
 * @copyright  2017 Howard Miller <howardsmiller@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_overview\output;

defined('MOODLE_INTERNAL') || die;

use renderable;
use renderer_base;
use templatable;

/**
 * Class contains data for course_overview
 *
 * @copyright  2017 Howard Miller <howardsmiller@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class main implements renderable, templatable {

    private $config;

    private $tabs;

    private $isediting;

    private $selectedtab;

    private $sortorder;

    private $favourites;

    /**
     * Constructor
     * @param object $config block configuration
     * @param array $tabs data for favourites/courses
     * @param boolean $isediting
     * @param string $selectedtab
     * @param int $sortorder
     * @param array list of favourites
     */
    public function __construct($config, $tabs, $isediting, $selectedtab, $sortorder, $favourites) {
        $this->config = $config;
        $this->tabs = $tabs;
        $this->isediting = $isediting;
        $this->selectedtab = $selectedtab;
        $this->sortorder = $sortorder;
        $this->favourites = $favourites;
    }

    /**
     * Get course data into suitable construct
     * @param \renderer_base $output
     * @param boolean $favtab (is this favourites tab)
     * @param object $tab data for tab
     * @return array of courses
     */
    private function process_tab($output, $favtab, $tab) {

        // Add extra info (and make zero indexed).
        $courselist = [];
        foreach ($tab->sortedcourses as $course) {
            $course->link = new \moodle_url('/course/view.php', array('id' => $course->id));
            $course->categories = implode(' / ', $this->categories($course->category));
            if (in_array($course->id, $this->favourites)) {
                $course->favouritelink = new \moodle_url('/my', array('unfavourite' => $course->id));
                $course->favouriteicon = 'fa-star';
                $course->favouritealt = get_string('unfavourite', 'block_course_overview');
            } else {
                $course->favouritelink = new \moodle_url('/my', array('favourite' => $course->id));
                $course->favouriteicon = 'fa-star-o';
                $course->favouritealt = get_string('makefavourite', 'block_course_overview');
            }
            if (!empty($tab->overviews[$course->id])) {
                $course->hasoverviews = true;
                $overviews = array();
                foreach ($tab->overviews[$course->id] as $activity => $overviewtext) {
                    $overview = new \stdClass;
                    $overview->coursename = $course->fullname;
                    $overview->visible = $course->visible;
                    $overview->activity = $activity;
                    $overview->text = str_replace('p-y-1', '', $overviewtext);
                    $description = get_string('activityoverview', 'block_course_overview',
                        get_string('pluginname', 'mod_' . $activity));
                    $overviewid = $activity . '_' . $course->id;
                    $overview->overviewid = $overviewid;
                    $overview->icon = $output->pix_icon('icon', $description, 'mod_' . $activity);
                    $overviews[] = $overview;
                }
                $course->overviews = $overviews;
            } else {
                $course->hasoverviews = false;
            }
            $courselist[] = $course;
        }

        return $courselist;
    }

    /**
     * Get (if required) category string for course
     * @param int $id course's category id
     * @return string category path
     */
    private function categories($id) {
        $categories = array();

        if ($this->config->showcategories != BLOCKS_COURSE_OVERVIEW_SHOWCATEGORIES_NONE) {

            // List category parent or categories path here.
            $currentcategory = \coursecat::get($id, IGNORE_MISSING);
            if ($currentcategory !== null) {
                if ($this->config->showcategories == BLOCKS_COURSE_OVERVIEW_SHOWCATEGORIES_FULL_PATH) {
                    foreach ($currentcategory->get_parents() as $categoryid) {
                        $category = \coursecat::get($categoryid, IGNORE_MISSING);
                        if ($category !== null) {
                            $categories[] = $category->get_formatted_name();
                        }
                    }
                }
                $categories[] = $currentcategory->get_formatted_name();
            }
        }

        return $categories;
    }

    /**
     * Build select for course list reorder
     * @param object $output
     * @return string html
     */
    private function reorder_select(renderer_base $output) {

        $options = [
            BLOCKS_COURSE_OVERVIEW_REORDER_NONE => get_string('reordernone', 'block_course_overview'),
            BLOCKS_COURSE_OVERVIEW_REORDER_FULLNAME => get_string('reorderfullname', 'block_course_overview'),
            BLOCKS_COURSE_OVERVIEW_REORDER_SHORTNAME => get_string('reordershortname', 'block_course_overview'),
            BLOCKS_COURSE_OVERVIEW_REORDER_ID => get_string('reorderid', 'block_course_overview'),
        ];

        // Courses reorder select.
        $select = $output->single_select(
            new \moodle_url('/my', array('sesskey' => sesskey())),
            'sortorder',
            $options,
            $this->sortorder,
            null
        );

        return $select;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \renderer_base $output
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {

        // Generate array for tabs 0=favs, 1=courses.
        $tabs = array(
            0 => (object) [
                'tab' => 'favourites',
                'show' => $this->selectedtab == 'favourites' ? 'show active' : '',
                'data' => $this->process_tab($output, true, $this->tabs['favourites']),
            ],
            1 => (object) [
                'tab' => 'courses',
                'show' => $this->selectedtab == 'courses' ? 'show active' : '',
                'data' => $this->process_tab($output, false, $this->tabs['courses']),
                ],
        );

        return [
            'tabs' => $tabs,
            'isediting' => $this->isediting,
            'help' => $output->help_icon('help', 'block_course_overview', true),
            'viewingfavourites' => $this->selectedtab == 'favourites',
            'select' => $this->reorder_select($output),
        ];
    }

}
