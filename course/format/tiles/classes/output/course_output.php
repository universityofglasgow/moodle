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
 * Tiles course format, main course output class to prepare data for mustache templates
 *
 * @package format_tiles
 * @copyright 2018 David Watson {@link http://evolutioncode.uk}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace format_tiles\output;

use format_tiles\tile_photo;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot .'/course/format/lib.php');
require_once("$CFG->libdir/resourcelib.php");  // To import RESOURCELIB_DISPLAY_POPUP.

/**
 * Tiles course format, main course output class to prepare data for mustache templates
 * @copyright 2018 David Watson
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_output implements \renderable, \templatable {

    /**
     * Course object for this class
     * @var \stdClass
     */
    private $course;
    /**
     * Whether this class is called from AJAX
     * @var bool
     */
    private $fromajax;
    /**
     * The section number of the section we want to display
     * @var int
     */
    private $sectionnum;
    /**
     * The course renderer object
     * @var \renderer_base
     */
    private $courserenderer;
    /**
     * Array of display names to be used at the top of sub tiles depending
     * on resource type of the module.
     * e.g. 'mod/lti' => 'External Tool' 'mod/resource','xls' = "Spreadsheet'
     * @var array
     */
    private $resourcedisplaynames;

    /**
     * Names of the modules for which modal windows should be used e.g. 'page'
     * @var array of resources and modules
     */
    private $usemodalsforcoursemodules;

    /**
     * User's device type e.g. DEVICE_TYPE_MOBILE ('mobile')
     * @var string
     */
    private $devicetype;

    /**
     * The course format.
     * @var
     */
    private $format;

    /**
     * @var \course_modinfo|null
     */
    private $modinfo;

    /**
     * @var bool
     */
    private $isediting;

    /**
     * @var bool
     */
    private $canviewhidden;

    /**
     * @var \context_course
     */
    private $coursecontext;

    /**
     * @var \completion_info
     */
    private $completioninfo;

    /**
     * @var bool
     */
    private $completionenabled;

    /**
     * @var mixed
     */
    public $courseformatoptions;

    /**
     * Are we showing activity completion conditions (Moodle 3.11+).
     * @var bool
     */
    private $showcompletionconditions;

    /**
     * course_output constructor.
     * @param \stdClass $course the course object.
     * @param bool $fromajax Whether we are rendering for AJAX request.
     * @param int $sectionnum the id of the current section
     * @param \renderer_base|null $courserenderer
     */
    public function __construct($course, $fromajax = false, $sectionnum = null, \renderer_base $courserenderer = null) {
        $this->course = $course;
        $this->fromajax = $fromajax;
        $this->sectionnum = $sectionnum;
        if (!$fromajax) {
            $this->courserenderer = $courserenderer;
        }
        $this->devicetype = \core_useragent::get_device_type();
        $this->usemodalsforcoursemodules = format_tiles_allowed_modal_modules();
        $this->format = course_get_format($this->course);
        $this->modinfo = get_fast_modinfo($this->course);

        // TODO this class is no longer used if the user is editing.  To be removed.
        $this->isediting = false;
        $this->coursecontext = \context_course::instance($this->course->id);
        $this->canviewhidden = has_capability('moodle/course:viewhiddensections', $this->coursecontext);
        if ($this->course->enablecompletion && !isguestuser()) {
            $this->completioninfo = new \completion_info($this->course);
        }
        $this->completionenabled = $course->enablecompletion && !isguestuser();
        $this->courseformatoptions = $this->get_course_format_options($this->fromajax);
        $this->showcompletionconditions = isset($course->showcompletionconditions) && $course->showcompletionconditions;
    }

    /**
     * Export the course data for the mustache template.
     * @param \renderer_base $output
     * @return array|\stdClass
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function export_for_template(\renderer_base $output) {
        global $PAGE;
        if (!$this->courserenderer) {
            $this->courserenderer = $output;
        }
        if ($this->fromajax) {
            try {
                // Set current URL and force bootstrap_renderer to initiate moodle page.
                $PAGE->set_url(new \moodle_url('/course/view.php', ['id' => $this->course->id]));
                $output->header();
                $PAGE->start_collecting_javascript_requirements();
            } catch (\Exception $e) {
                debugging('Could not start collecing JS requirements');
            }

        }
        $data = $this->get_basic_data();
        $data = $this->append_section_zero_data($data, $output);
        // We have assembled the "common data" needed for both single and multiple section pages.
        // Now we can go off and get the specific data for the single or multiple page as required.
        if ($this->sectionnum !== null) {
            // We are outputting a single section page.
            if ($this->sectionnum == 0) {
                return $this->append_section_zero_data($data, $output);
            } else {
                return $this->append_single_section_page_data($output, $data);
            }
        } else {
            // We are outputting multi section page.
            return $this->append_multi_section_page_data($data);
        }
    }

    /**
     * Get the basic data required to render (required whatever we are doing).
     * @return array data
     * @throws \coding_exception
     * @throws \dml_exception
     */
    private function get_basic_data() {
        global $SESSION, $USER;
        $data = [];
        $data['canedit'] = has_capability('moodle/course:update', $this->coursecontext);
        $data['canviewhidden'] = $this->canviewhidden;
        $data['courseid'] = $this->course->id;
        $data['completionenabled'] = $this->completionenabled;
        $data['istrackeduser'] = $this->completionenabled && $this->completioninfo->is_tracked_user($USER->id);
        $data['from_ajax'] = $this->fromajax;
        $data['ismobile'] = $this->devicetype == \core_useragent::DEVICETYPE_MOBILE;
        if (isset($SESSION->format_tiles_jssuccessfullyused)) {
            // If this flag is set, user is being shown JS versions of pages.
            // Allow them to cancel the session var if they have no JS.
            $data['showJScancelLink'] = 1;
        } else {
            $data['showJScancelLink'] = 0;
        }
        $data['editing'] = $this->isediting;
        $data['sesskey'] = sesskey();
        $data['showinitialpageloadingicon'] = format_tiles_width_template_data($this->course->id)['hidetilesinitially'];
        $data['jsnavadminallowed'] = get_config('format_tiles', 'usejavascriptnav');
        $data['jsnavuserenabled'] = !get_user_preferences('format_tiles_stopjsnav');
        $data['usingjsnav'] = $data['jsnavadminallowed'] && $data['jsnavuserenabled'];

        $data['useSubtiles'] = get_config('format_tiles', 'allowsubtilesview') && $this->courseformatoptions['courseusesubtiles'];
        $data['usetooltips'] = get_config('format_tiles', 'usetooltips');

        foreach ($this->courseformatoptions as $k => $v) {
            $data[$k] = $v;
        }
        // RTL support for nav arrows direction (Arabic/ Hebrew).
        $data['is-rtl'] = right_to_left();

        return $data;
    }

    /**
     * Temporary function for Moodle 4.0 upgrade - todo to be replaced.
     * @param object $section
     * @return string
     */
    private function temp_format_summary_text($section) {
        $summarytext = file_rewrite_pluginfile_urls($section->summary, 'pluginfile.php',
            $this->coursecontext->id, 'course', 'section', $section->id);

        $options = new \stdClass();
        $options->noclean = true;
        $options->overflowdiv = true;
        return format_text($summarytext, $section->summaryformat, $options);
    }

    /**
     * Temporary function for Moodle 4.0 upgrade - todo to be replaced.
     * @param object $section
     * @return string|bool
     * @throws \coding_exception
     */
    private function temp_section_activity_summary($section) {
        $widgetclass = $this->format->get_output_classname('content\\section\\cmsummary');
        $widget = new $widgetclass($this->format, $section);
        return $this->courserenderer->render($widget);
    }

    /**
     * Temporary function for Moodle 4.0 upgrade - todo to be replaced.
     * @param object $section
     * @return bool|string
     * @throws \coding_exception
     */
    private function temp_section_availability_message($section) {
        $widgetclass = $this->format->get_output_classname('content\\section\\availability');
        $widget = new $widgetclass($this->format, $section);
        return $this->courserenderer->render($widget);
    }

    /**
     * Temporary function for Moodle 4.0 upgrade - todo to be replaced.
     * @param object $mod
     * @return bool|string
     * @throws \coding_exception
     */
    private function temp_course_section_cm_availability($mod) {
        $availabilityclass = $this->format->get_output_classname('content\\cm\\availability');
        $availability = new $availabilityclass(
            $this->format,
            $mod->get_section_info(),
            $mod,
        );
        return $this->courserenderer->render($availability);
    }

    /**
     * Append the data we need to render section zero.
     * @param [] $data
     * @param \renderer_base $output
     * @return array
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    private function append_section_zero_data($data, $output) {
        $seczero = $this->modinfo->get_section_info(0);
        $coursemods = $this->section_course_mods($seczero, $output);
        $data['section_zero']['summary'] = self::temp_format_summary_text($seczero);
        $data['section_zero']['content']['course_modules'] = $coursemods->mods;
        $data['section_zero']['jsfooter'] = $coursemods->jsfooter;
        $data['section_zero']['secid'] = $this->modinfo->get_section_info(0)->id;
        $data['section_zero']['is_section_zero'] = true;
        $data['section_zero']['tileid'] = 0;
        $data['section_zero']['visible'] = true;

        // Only show section zero if we need it.
        $data['section_zero_show'] = 0;
        if ($this->sectionnum == 0 || get_config('format_tiles', 'showseczerocoursewide')) {
            // We only want to show section zero if we are on the landing page, or admin has said we should show it course wide.
            if ($seczero->summary || !empty($data['section_zero']['content']['course_modules'])) {
                // We do have something to show, so need to show it.
                $data['section_zero_show'] = 1;
            }
        }
        if ($this->courseformatoptions['courseusesubtiles'] && $this->courseformatoptions['usesubtilesseczero']) {
            $data['section_zero']['useSubtiles'] = 1;
        } else {
            $data['section_zero']['useSubtiles'] = 0;
        }
        return $data;
    }

    /**
     * Get the course format options (how depends on where we are calling from).
     * @param bool $fromajax is this request from AJAX.
     * @return array
     */
    private function get_course_format_options($fromajax) {
        // Custom course settings not in course object if called from AJAX, so make sure we get them.
        $options = [
            'defaulttileicon', 'basecolour', 'courseusesubtiles', 'courseshowtileprogress',
            'displayfilterbar', 'usesubtilesseczero', 'courseusebarforheadings'
        ];
        $data = [];
        if (!$fromajax) {
            foreach ($options as $option) {
                if (isset($this->course->$option)) {
                    $data[$option] = $this->course->$option;
                }
            }
        } else {
            $data = $this->format->get_format_options();
        }
        if (!get_config('format_tiles', 'allowsubtilesview')) {
            $data['courseusesubtiles'] = 0;
        }
        return $data;
    }

    /**
     * Take the "common data" supplied as the $data argument, and build on it
     * with data which is specific to single section pages, then return
     * the amalgamated data
     * @param \renderer_base $output the renderer for this format
     * @param array $data the common data
     * @return array the amalgamated data
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    private function append_single_section_page_data($output, $data) {
        // If we have nothing to output, don't.
        if (!($thissection = $this->modinfo->get_section_info($this->sectionnum))) {
            // This section doesn't exist.
            debugging('Unknown course section ' . $this->sectionnum, DEBUG_DEVELOPER);
            return $data;
        }
        if (!$thissection->uservisible) {
            // Can't view this section - in that case the template will just render 'Not available' and nothing else.
            $data['hidden_section'] = true;
            return $data;
        }

        // Data for the requested section page.
        $data['title'] = format_string(get_section_name($this->course, $thissection->section));
        if (get_config('format_tiles', 'enablelinebreakfilter')) {
            // No need to line break here as we have plenty of room, so remove the char by passing true.
            $data['title'] = $this->apply_linebreak_filter($data['title'], true);
        }
        $data['summary'] = self::temp_format_summary_text($thissection);
        $data['tileid'] = $thissection->section;
        $data['secid'] = $thissection->id;
        $data['tileicon'] = $thissection->tileicon;

        // If photo tile backgrounds are allowed by site admin, prepare the image for this section.
        if (get_config('format_tiles', 'allowphototiles')) {
            $tilephoto = new tile_photo($this->course->id, $thissection->id);
            $tilephotourl = $tilephoto->get_image_url();

            $data['phototileinlinestyle'] = 'style = "background-image: url(' . $tilephotourl . ');"';
            $data['hastilephoto'] = $tilephotourl ? 1 : 0;
            $data['phototileurl'] = $tilephotourl;
            $data['phototileediturl'] = new \moodle_url(
                '/course/format/tiles/editimage.php',
                array('courseid' => $this->course->id, 'sectionid' => $thissection->id)
            );
        }

        // Include completion help icon HTML.
        if ($this->completioninfo) {
            $data['completion_help'] = true;
        }

        // The list of activities on the page (HTML).
        $coursemods = $this->section_course_mods($thissection, $output);
        $data['course_modules'] = $coursemods->mods;
        $data['jsfooter'] = $coursemods->jsfooter;

        // If lots of content in this section, we include nav arrows again at bottom of page.
        // But otherwise not as looks odd when no content.
        $longsectionlength = 10000;
        if (strlen('single_sec_content') > $longsectionlength) {
            $data['single_sec_content_is_long'] = true;
        }
        if (!$data['usingjsnav']) {
            $previousnext = $this->get_previous_next_section_numbers($thissection->section);
            $data['previous_tile_id'] = $previousnext['previous'];
            $data['next_tile_id'] = $previousnext['next'];
        }

        $data['visible'] = $thissection->visible;
        // If user can view hidden items, include the explanation as to why an item is hidden.
        if ($this->canviewhidden) {
            $data['availabilitymessage'] = self::temp_section_availability_message($thissection);
        }
        return $data;
    }

    /**
     * Take the "common data" supplied as the $data argument, and build on it
     * with data which is specific to multiple section pages, then return
     * the amalgamated data
     * @param array $data the common data
     * @return array the amalgamated data
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    private function append_multi_section_page_data($data) {
        $data['is_multi_section'] = true;

        // If using completion tracking, get the data.
        if ($this->completionenabled) {
            $data['overall_progress']['num_complete'] = 0;
            $data['overall_progress']['num_out_of'] = 0;
        }
        $data['hasNoSections'] = true;

        // Before we start the section loop. get key vars for photo tiles ready.
        $allowedphototiles = get_config('format_tiles', 'allowphototiles');
        $usingphotoaltstyle = get_config('format_tiles', 'phototilesaltstyle');
        if ($allowedphototiles) {
            $data['allowphototiles'] = 1;
            $data['showprogressphototiles'] = get_config('format_tiles', 'showprogresssphototiles');
            $phototileids = tile_photo::get_photo_tile_ids($this->course->id);
            $phototileextraclasses = 'phototile';
            if ($usingphotoaltstyle) {
                $phototileextraclasses .= ' altstyle';
                $data['usingaltstyle'] = 1;
            }
        }
        $maxallowedsections = $this->format->get_max_sections();
        $sectioncountwarningissued = false;

        $previoustiletitle = '';
        $countincludedsections = 0;
        $uselinebreakfilter = get_config('format_tiles', 'enablelinebreakfilter');
        foreach ($this->modinfo->get_section_info_all() as $sectionnum => $section) {
            // Show the section if the user is permitted to access it, OR if it's not available
            // but there is some available info text which explains the reason & should display,
            // OR it is hidden but the course has a setting to display hidden sections as unavilable.

            // If we have sections with numbers greater than the max allowed, do not show them unless teacher.
            // (Showing more to editors allows editor to fix them).
            if ($countincludedsections > $maxallowedsections) {
                if (!$data['canedit']) {
                    // Do not show them to students at all.
                    break;
                } else {
                    if (!$sectioncountwarningissued) {
                        $a = new \stdClass();
                        $a->max = $maxallowedsections;
                        $a->tilename = $previoustiletitle;
                        $button = \format_tiles\course_section_manager::get_schedule_button($this->course->id);
                        \core\notification::error(get_string('coursetoomanysections', 'format_tiles', $a) . $button);
                        $sectioncountwarningissued = true;
                    }
                    if ($countincludedsections > $maxallowedsections * 2) {
                        // Even if the user is editing, if we have a *very* large number of sections, we only show 2 x that number.
                        $data['showsectioncountwarning'] = true;
                        break;
                    }
                }
            }

            $isphototile = $allowedphototiles && in_array($section->id, $phototileids);
            $showsection = $section->uservisible ||
                ($section->visible && !$section->available && !empty($section->availableinfo));
            if ($sectionnum != 0 && $showsection) {
                if ($uselinebreakfilter) {
                    $title = $this->apply_linebreak_filter($this->truncate_title(get_section_name($this->course, $sectionnum)));
                } else {
                    $title = format_string($this->truncate_title(get_section_name($this->course, $sectionnum)));
                }
                if ($allowedphototiles && $usingphotoaltstyle && $isphototile) {
                    // Replace the last space with &nbsp; to avoid having one word on the last line of the tile title.
                    $title = preg_replace('/\s(\S*)$/', '&nbsp;$1', $title);
                }

                $longtitlelength = 65;

                $newtile = array(
                    'tileid' => $section->section,
                    'secid' => $section->id,
                    'title' => $title,
                    'tileicon' => $section->tileicon,
                    'current' => course_get_format($this->course)->is_section_current($section),
                    'hidden' => !$section->visible,
                    'visible' => $section->visible,
                    'restricted' => !($section->available),
                    'userclickable' => $section->available || $section->uservisible,
                    'activity_summary' => self::temp_section_activity_summary($section),
                    'titleclass' => strlen($title) >= $longtitlelength ? ' longtitle' : '',
                    'progress' => false,
                    'isactive' => $this->course->marker == $section->section,
                    'extraclasses' => ''
                );

                // If photo tile backgrounds are allowed by site admin, prepare them for this tile.
                if ($isphototile) {
                    $tilephoto = new tile_photo($this->course->id, $section->id);
                    $tilephotourl = $tilephoto->get_image_url();

                    $newtile['extraclasses'] .= $phototileextraclasses;
                    $newtile['phototileinlinestyle'] = 'style = "background-image: url(' . $tilephotourl . ');"';
                    $newtile['hastilephoto'] = $tilephotourl ? 1 : 0;
                    $newtile['phototileurl'] = $tilephotourl;
                    $newtile['phototileediturl'] = new \moodle_url(
                        '/course/format/tiles/editimage.php',
                        array('courseid' => $this->course->id, 'sectionid' => $section->id)
                    );
                }

                // Include completion tracking data for each tile (if used).
                if ($section->visible && $this->completionenabled) {
                    if (isset($this->modinfo->sections[$sectionnum])) {
                        $completionthistile = $this->section_progress($this->modinfo->sections[$sectionnum], $this->modinfo->cms);
                        // Keep track of overall progress so we can show this too - add this tile's completion to the totals.
                        $data['overall_progress']['num_out_of'] += $completionthistile['outof'];
                        $data['overall_progress']['num_complete'] += $completionthistile['completed'];

                        // We only add the tile values to the individual tile if courseshowtileprogress is true.
                        // (Otherwise we only retain overall completion as above, not for each tile).
                        if ($this->courseformatoptions['courseshowtileprogress']) {
                            $showaspercent = $this->courseformatoptions['courseshowtileprogress'] == 2;
                            $newtile['progress'] = $this->completion_indicator(
                                $completionthistile['completed'],
                                $completionthistile['outof'],
                                $showaspercent,
                                false
                            );
                        }
                    }
                }

                // If item is restricted, user needs to know why.
                $newtile['availabilitymessage'] = $section->availableinfo || !$section->visible
                    ? self::temp_section_availability_message($section) : '';
                if ($this->courseformatoptions['displayfilterbar'] == FORMAT_TILES_FILTERBAR_OUTCOMES
                    || $this->courseformatoptions['displayfilterbar'] == FORMAT_TILES_FILTERBAR_BOTH) {
                    $newtile['tileoutcomeid'] = $section->tileoutcomeid;
                }
                // See below about when "hide add cm control" is true.
                $newtile['hideaddcmcontrol'] = false;
                $newtile['single_sec_add_cm_control_html'] = $this->courserenderer->course_section_add_cm_control(
                    $this->course, $section->section, 0
                );

                $newtile['is_expanded'] = false;

                // Finally add tile we constructed to the array.
                $data['tiles'][] = $newtile;
                $previoustiletitle = $title;
            } else if ($sectionnum == 0) {
                // Add in section zero completion data to overall completion count.
                if ($section->visible && $this->completionenabled) {
                    if (isset($this->modinfo->sections[$sectionnum])) {
                        $completionthistile = $this->section_progress($this->modinfo->sections[$sectionnum], $this->modinfo->cms);
                        // Keep track of overall progress so we can show this too - add this tile's completion to the totals.
                        $data['overall_progress']['num_out_of'] += $completionthistile['outof'];
                        $data['overall_progress']['num_complete'] += $completionthistile['completed'];
                    }
                }
            }
            $countincludedsections++;
        }

        // Now the filter buttons (if used).
        $data['has_filter_buttons'] = false;
        if ($this->courseformatoptions['displayfilterbar']) {
            $firstidoutcomebuttons = 1;
            if ($this->courseformatoptions['displayfilterbar'] == FORMAT_TILES_FILTERBAR_NUMBERS
                || $this->courseformatoptions['displayfilterbar'] == FORMAT_TILES_FILTERBAR_BOTH) {
                $data['fiternumberedbuttons'] = $this->get_filter_numbered_buttons_data($data['tiles']);
                if (count($data['fiternumberedbuttons']) > 0) {
                    $firstidoutcomebuttons = count($data['fiternumberedbuttons']) + 1;
                    $data['has_filter_buttons'] = true;
                }
            }
            if ($this->courseformatoptions['displayfilterbar'] == FORMAT_TILES_FILTERBAR_OUTCOMES
                || $this->courseformatoptions['displayfilterbar'] == FORMAT_TILES_FILTERBAR_BOTH) {
                $outcomes = course_get_format($this->course)->format_tiles_get_course_outcomes($this->course->id);
                $data['fiteroutcomebuttons'] = $this->get_filter_outcome_buttons_data(
                    $data['tiles'], $outcomes, $firstidoutcomebuttons
                );
                if (count($data['fiternumberedbuttons']) > 0) {
                    $data['has_filter_buttons'] = true;
                }
            }
        }
        $data['section_zero_add_cm_control_html'] = $this->courserenderer->course_section_add_cm_control($this->course, 0, 0);
        if ($this->completionenabled && $data['overall_progress']['num_out_of'] > 0) {
            if (get_config('format_tiles', 'showoverallprogress')) {
                $data['overall_progress_indicator'] = $this->completion_indicator(
                    $data['overall_progress']['num_complete'],
                    $data['overall_progress']['num_out_of'],
                    true,
                    true
                );
                $data['overall_progress_indicator']['tileid'] = 0;
            }
        }
        return $data;
    }

    /**
     * Count the number of course modules with completion tracking activated
     * in this section, and the number which the student has completed
     * Exclude labels if we are using sub tiles, as these are not checkable
     * Also exclude items the user cannot see e.g. restricted
     * @param array $sectioncmids the ids of course modules to count
     * @param array $coursecms the course module objects for this course
     * @return array with the completion data x items complete out of y
     */
    public function section_progress($sectioncmids, $coursecms) {
        $completed = 0;
        $outof = 0;
        foreach ($sectioncmids as $cmid) {
            $thismod = $coursecms[$cmid];
            if ($thismod->uservisible && !$thismod->deletioninprogress) {
                if ($this->completioninfo->is_enabled($thismod) != COMPLETION_TRACKING_NONE) {
                    $outof++;
                    $completiondata = $this->completioninfo->get_data($thismod, true);
                    if ($completiondata->completionstate == COMPLETION_COMPLETE ||
                        $completiondata->completionstate == COMPLETION_COMPLETE_PASS
                    ) {
                        $completed++;
                    }
                }
            }
        }
        return array('completed' => $completed, 'outof' => $outof);
    }

    /**
     * Get the details of the filter buttons to be displayed at the top of this course
     * where the teacher has selected to use numbered filter buttons e.g. button 1 might
     * filter to tiles 1-3, button 2 to tiles 4-6 etc
     * @see get_button_map() which calls this function
     * @param array $tiles the tiles which relate to filters
     * @return array the button details
     */
    private function get_filter_numbered_buttons_data($tiles) {
        $numberoftiles = count($tiles);
        if ($numberoftiles == 0) {
            return array();
        }

        // Find out the number to use for each tile from its title e.g. "1 Introduction" filters to "1".
        $tilenumbers = [];
        foreach ($tiles as $tile) {
            if ($statednum = $this->get_stated_tile_num($tile)) {
                $tilenumbers[$statednum] = $tile['tileid'];
            }
        }
        ksort($tilenumbers);

        // Break the tiles down into chunks - one chunk per button.

        if ($numberoftiles <= 15) {
            $tilesperbutton = 3;
        } else if ($numberoftiles <= 30) {
            $tilesperbutton = 4;
        } else {
            $tilesperbutton = 6;
        }

        $buttons = array_chunk($tilenumbers, $tilesperbutton, true);

        // Now populate each button and map the tile details to it.
        $buttonmap = [];
        $buttonid = 1;
        foreach ($buttons as $button => $tilesthisbutton) {
            if (!empty($tiles)) {
                $tilestatednumers = array_keys($tilesthisbutton);
                if ($tilestatednumers[0] == end($tilestatednumers)) {
                    $title = $tilestatednumers[0];
                } else {
                    $title = $tilestatednumers[0] . '-' . end($tilestatednumers);
                }
                $buttonmap[] = array(
                    'id' => 'filterbutton' . $buttonid,
                    'title' => $title,
                    'sections' => json_encode(array_values($tilesthisbutton)),
                    'buttonnum' => $buttonid
                );
            }
            $buttonid++;
        }
        return $buttonmap;
    }

    /**
     * Get the details of the filter buttons to be displayed at the top of this course
     * where the teacher has selected to use OUTCOME filter buttons e.g. button 1 might
     * filter to outcome 1, button 2 to outcome 2 etc
     * @param array $tiles the tiles output object showing the outcome ID for each tile
     * @param array $outcomenames the course outcome names to display
     * @param int $firstbuttonid first button id so it follows on from last one
     * @see get_filter_numbered_buttons()
     * @return array the button details
     */
    private function get_filter_outcome_buttons_data($tiles, $outcomenames, $firstbuttonid = 1) {
        $outcomebuttons = [];
        if ($outcomenames) {
            // Build array showing, for each outcome, which sections of the course use it.
            $outcomesections = [];
            foreach ($tiles as $index => $tile) {
                if (isset($tile['tileoutcomeid']) && $tile['tileoutcomeid']) {
                    // This tile has an outcome attached, so add it to the array of tiles for that outcome.
                    $outcomesections[$tile['tileoutcomeid']][] = $tile['tileid'];
                }
            }

            // For each outcome found on tiles, add its outcome name and all tiles found for it to return array.
            $buttonid = $firstbuttonid;
            foreach ($outcomesections as $outcomeid => $outcomesectionsthisoutcome) {
                if (array_key_exists($outcomeid, $outcomenames)) {
                    $outcomebuttons[] = array(
                        'id' => 'filterbutton' . $buttonid,
                        'title' => $outcomenames[$outcomeid],
                        'sections' => json_encode(array_values($outcomesectionsthisoutcome)),
                    );
                }
                $buttonid++;
            }
        }
        return $outcomebuttons;
    }

    /**
     * Get the number which the author has stated for this tile so that it can
     * be used for filter buttons.  e.g. "1 Introduction" or "Week 1 Introduction" give
     * a filtering number of 1
     *
     * @param array $tile the tile output data
     * @return string HTML to output.
     */
    private function get_stated_tile_num($tile) {
        if (!$tile['title']) {
            return $tile['tileid'];
        } else {
            // If title for example starts "16.2" or "16)" treat it as "16".
            $title = str_replace(')', ' ', str_replace('.', ' ', $tile['title']));
            $title = explode(' ', $title);
            for ($i = 0; $i <= count($title) - 1; $i++) {
                // Iterate through each word in the title and see if it's a number - if it is, we have what we want.
                $statednumber = preg_replace('/[^0-9]/', '', $title[$i]);
                if ($statednumber && ctype_digit($statednumber)) {
                    return intval($statednumber);
                }
            }
        }
        return null;
    }

    /**
     * Take a title (e.g. from a section) and truncate it if too big for sub tile
     * @param string $title to truncated
     * @return string truncated
     */
    private function truncate_title($title) {
        $maxtitlelength = 75;
        if (strlen($title) >= $maxtitlelength) {
            $lastspace = strripos(substr($title, 0, $maxtitlelength), ' ');
            $title = substr($title, 0, $lastspace) . ' ...';
        }
        return trim($title);
    }

    /**
     * Watch for the word joiner character '&#8288;' in very long tile titles.
     * When encountered on a tile title, this char is changed to '- ' to allow the text to wrap.
     * This is useful on tiles with long words in the title (e.g. German language).
     * @param string $text
     * @param bool $remove if we want just to remove the flag (no need to line break), pass true.
     * @return string
     */
    private function apply_linebreak_filter(string $text, $remove = false) {
        $zerowidthspace = '&#8288;';
        $maxwidthfortilechars = 15;
        if (!$remove && strlen($text) > $maxwidthfortilechars) {
            // If the title is long, we want to line break with a -, so replace the zero width space with hyphen space.
            return format_string(str_replace($zerowidthspace, '- ', $text));
        } else {
            // If the title is short, we don't need to line break so delete the flag.
            return format_string(str_replace($zerowidthspace, '', $text));
        }
    }

    /**
     * Gets the data (context) to be used with the activityinstance template
     * @param object $section the section object we want content for
     * @param \renderer_base $output
     * @see \cm_info for full detail of $mod instance variables
     * @see \core_completion\manager::get_activities() which covers similar ground
     * @see \core_course_renderer::course_section_cm_completion() which covers similar ground
     * In the snap theme, course_renderer::course_section_cm_list_item() covers similar ground
     * @return object
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    private function section_course_mods($section, $output): object {
        global $PAGE;
        $result = (object)['mods' => [], 'jsfooter' => ''];
        if (!isset($section->section)) {
            debugging("section->section is not set", DEBUG_DEVELOPER);
        }
        if (!isset($this->modinfo->sections[$section->section]) || !$cmids = $this->modinfo->sections[$section->section]) {
            // There are no CMs for the section (i.e. section is empty) so we silently return.
            return $result;
        }
        if (empty($cmids)) {
            // There are no CMs for the section (i.e. section is empty) so we silently return.
            return $result;
        }

        $previouswaslabel = false;
        $includejsfooter = false; // See comment below.
        foreach ($cmids as $index => $cmid) {
            $mod = $this->modinfo->get_cm($cmid);
            if ($mod->deletioninprogress) {
                continue;
            }
            $moduledata = $this->course_module_data(
                $mod,
                $section,
                $previouswaslabel,
                $index == 0,
                $output
            );

            if (!empty($moduledata)) {
                $result->mods[] = $moduledata;
                $previouswaslabel = $mod->has_custom_cmlist_item();
            }
            if ($this->fromajax && $mod->has_custom_cmlist_item()) {
                // If we are being called from a web service, JS may be added to the page as individual modules are rendered.
                // E.g. mod_unilabel templates contain {{#js}} helper tags, processed by \core\output\mustache_javascript_helper.
                // These need to be added to the page, so that content added to the DOM by JS works correctly.
                // We only need to use this where the module displays inline i.e. $mod->has_custom_cmlist_item() == true.
                // Using same approach as in \core_external::get_fragment().
                $includejsfooter = true;
            }
        }

        // See comment above where $includejsfooter is defined.
        if ($includejsfooter) {
            try {
                $result->jsfooter = $PAGE->requires->get_end_code();
            } catch (\Exception $e) {
                debugging('Could not get end code');
            }
        }
        return $result;
    }

    /**
     * Assemble and return the data to render a single course module.
     * @param \cm_info $mod
     * @param object $section
     * @param bool $previouswaslabel
     * @param bool $isfirst
     * @param \renderer_base $output
     * @return array
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    private function course_module_data($mod, $section, $previouswaslabel, $isfirst, $output) {
        global $PAGE, $CFG, $DB, $USER;
        $displayoptions = [];
        $obj = new \core_courseformat\output\local\content\section\cmitem($this->format, $section, $mod, $displayoptions);
        $moduleobject = (array)$obj->export_for_template($output);
        if ($this->canviewhidden) {
            $moduleobject['uservisible'] = true;
            $moduleobject['clickable'] = true;
        } else if (!$mod->uservisible && $mod->is_visible_on_course_page() && $mod->availableinfo && $mod->visible) {
            // Activity is not available, not hidden from course page and has availability info.
            // So it is actually visible on the course page (with availability info and without a link).
            $moduleobject['uservisible'] = true;
            $moduleobject['clickable'] = false;
        } else {
            $moduleobject['uservisible'] = $mod->is_visible_on_course_page();
            $moduleobject['clickable'] = $mod->uservisible;
        }
        // From Moodle 3.11 onwards, we may have extra completion conditions info to display under activities.
        if (class_exists('\core\activity_dates') && isset($this->showcompletionconditions)
            && $this->showcompletionconditions) {
            $activitydates = \core\activity_dates::get_dates_for_module($mod, $USER->id);
            $completiondetails = \core_completion\cm_completion_details::get_instance(
                $mod, $USER->id, $this->showcompletionconditions
            );
            if ($completiondetails->has_completion() || !empty($activitydates)) {
                // No need to render the activity information when there's no completion info and activity dates to show.
                $activityinfo = new \core_course\output\activity_information($mod, $completiondetails, $activitydates);
                $moduleobject['activityinformation'] = $activityinfo->export_for_template($output);
            }
        }

        // We check that the stealth function exists in case we are running in Totara or earlier Moodle, where it doesn't.
        $isstealth = method_exists($mod, 'is_stealth') && $mod->is_stealth();
        if (!$moduleobject['uservisible'] || $mod->deletioninprogress || (!$this->canviewhidden && $isstealth)) {
            return [];
        }
        // If the module isn't available, or we are a teacher (can view hidden activities) get availability info.
        if (!$mod->available || $this->canviewhidden) {
            $moduleobject['availabilitymessage'] = self::temp_course_section_cm_availability($mod);
        }
        $moduleobject['available'] = $mod->available;
        $moduleobject['cmid'] = $mod->id;
        $moduleobject['activityname'] = $mod->get_formatted_name();
        $moduleobject['modname'] = $mod->modname;
        $moduleobject['iconurl'] = $mod->get_icon_url()->out(true);
        $moduleobject['url'] = $mod->url;
        $moduleobject['visible'] = $mod->visible;
        $moduleobject['launchtype'] = 'standard';
        $moduleobject['content'] = $mod->get_formatted_content(array('overflowdiv' => true, 'noclean' => true));
        if (!$this->courseformatoptions['courseusesubtiles'] && $mod->indent) {
            $moduleobject['indentlevel'] = $mod->indent;
        }

        // We set this here, with the value from the last loop, before updating it in the next block.
        // So that we can use it again on the next loop.
        $moduleobject['previouswaslabel'] = $previouswaslabel;
        $treataslabel = $mod->has_custom_cmlist_item();
        if ($treataslabel) {
            $moduleobject['is_label'] = true;
            $moduleobject['long_label'] = strlen($mod->content) > 300 ? 1 : 0;
            if ($isfirst && !$previouswaslabel && $this->courseformatoptions['courseusesubtiles']) {
                $moduleobject['hasSpacersBefore'] = 1;
            }
        }

        if (isset($mod->instance)) {
            $moduleobject['modinstance'] = $mod->instance;
        }
        $moduleobject['modResourceType'] = $this->get_resource_filetype($mod);
        $moduleobject['modnameDisplay'] = $this->mod_displayname($mod->modname, $moduleobject['modResourceType']);

        // Specific handling for embedded resource items (e.g. PDFs)  as allowed by site admin.
        if ($mod->modname == 'resource') {
            if (in_array($moduleobject['modResourceType'], $this->usemodalsforcoursemodules['resources'])) {
                $moduleobject['isEmbeddedResource'] = 1;
                $moduleobject['launchtype'] = 'resource-modal';
                $moduleobject['pluginfileUrl'] = $this->plugin_file_url($mod);
            } else {
                // We are not using modal, so add the standard moodle onclick event to the link to launch pop up if appropriate.
                if ($mod->onclick) {
                    $moduleobject['onclick'] = htmlspecialchars_decode($mod->onclick, ENT_QUOTES);
                    $moduleobject['launchtype'] = 'standard';
                }
            }
        }

        // Issue 67 handling for LTI set to open in new window.
        if ($mod->onclick == 'lti' && $mod->onclick) {
            $moduleobject['onclick'] = htmlspecialchars_decode($mod->onclick, ENT_QUOTES);
            $moduleobject['launchtype'] = 'standard';
        }

        // Specific handling for embedded course module items (e.g. page) as allowed by site admin.
        if (in_array($mod->modname, $this->usemodalsforcoursemodules['modules'])) {
            $moduleobject['isEmbeddedModule'] = 1;
            $moduleobject['launchtype'] = 'module-modal';
        }
        $moduleobject['showdescription'] =
            isset($mod->showdescription) && !$treataslabel ? $mod->showdescription : 0;
        if ($moduleobject['showdescription']) {
            // The reason we need 'noclean' arg here is that otherwise youtube etc iframes will be stripped out.
            $moduleobject['description'] = $mod->get_formatted_content(array('overflowdiv' => true, 'noclean' => true));
        }
        $moduleobject['extraclasses'] = $mod->extraclasses;
        $moduleobject['afterlink'] = $mod->afterlink;
        if ($isstealth) {
            $moduleobject['extraclasses'] .= ' stealth';
            $moduleobject['stealth'] = 1;
        } else if (
            (!$mod->visible && !$mod->visibleold)
            || !$mod->available
            || !$section->visible
        ) {
            $moduleobject['extraclasses'] .= ' dimmed';
        }
        if ($mod->completion == COMPLETION_TRACKING_MANUAL) {
            $moduleobject['extraclasses'] .= " completion-enabled completion-manual";
        } else if ($mod->completion == COMPLETION_VIEW_REQUIRED) {
            // Auto completion with a view required.
            $moduleobject['extraclasses'] .= " completion-enabled completion-view";
        } else if ($mod->completion == COMPLETION_TRACKING_AUTOMATIC) {
            // Auto completion with no view required (e.g. grade required).
            $moduleobject['extraclasses'] .= " completion-enabled completion-auto";
        }

        if ($mod->modname == 'folder') {
            // Folders set to display inline will not work this theme.
            // This is not a very elegant solution, but it will ensure that the URL is correctly shown.
            // If the user is editing it will change the format of the folder.
            // It will show on a separate page, and alert the editing user as to what it has done.
            $moduleobject['url'] = new \moodle_url('/mod/folder/view.php', array('id' => $mod->id));
            if ($PAGE->user_is_editing()) {
                $folder = $DB->get_record('folder', array('id' => $mod->instance));
                if ($folder->display == FOLDER_DISPLAY_INLINE) {
                    $DB->set_field('folder', 'display', FOLDER_DISPLAY_PAGE, array('id' => $folder->id));
                    \core\notification::info(
                        get_string('folderdisplayerror', 'format_tiles', $moduleobject['url']->out())
                    );
                    rebuild_course_cache($mod->course, true);
                }
            }
        }

        if ($mod->modname == 'url') {
            $url = $DB->get_record('url', array('id' => $mod->instance), '*', MUST_EXIST);
            $usemodalsforurl = in_array('url', $this->usemodalsforcoursemodules['resources']);
            $modifiedvideourl = $this->check_modify_embedded_url($url->externalurl);
            if ($url->display == RESOURCELIB_DISPLAY_POPUP || $url->display == RESOURCELIB_DISPLAY_NEW) {
                if ($mod->onclick) {
                    $moduleobject['onclick'] = $mod->onclick;
                    $moduleobject['launchtype'] = 'standard';
                } else {
                    $moduleobject['pluginfileUrl'] = $url->externalurl;
                    $moduleobject['extraclasses'] .= ' urlpopup';
                    $moduleobject['launchtype'] = 'urlpopup';
                }
            } else if ($url->display == RESOURCELIB_DISPLAY_EMBED) {
                // We need a secondary URL to show under the embed window so users can click it if embed doesn't work.
                // We will also use it to redirect mobile users to YouTube or wherever since embed wont work well for them.
                $moduleobject['secondaryurl'] = $url->externalurl;
                if ($usemodalsforurl) {
                    if ($modifiedvideourl) {
                        $moduleobject['pluginfileUrl'] = $modifiedvideourl;
                    } else {
                        $moduleobject['pluginfileUrl'] = $url->externalurl;
                    }
                    $moduleobject['launchtype'] = 'url-modal';
                }
            } else if ($url->display == RESOURCELIB_DISPLAY_AUTO) {
                require_once("$CFG->dirroot/mod/url/locallib.php");
                // TODO modify this later to treat embed as launch modal.
                $treataspopup = [
                    RESOURCELIB_DISPLAY_EMBED,
                    RESOURCELIB_DISPLAY_FRAME,
                    RESOURCELIB_DISPLAY_NEW,
                    RESOURCELIB_DISPLAY_POPUP
                ];
                $displaytype = url_get_final_display_type($url);
                if (in_array($displaytype, $treataspopup)) {
                    $moduleobject['pluginfileUrl'] = $url->externalurl;
                    $moduleobject['extraclasses'] .= ' urlpopup';
                }
            }

            if ($modifiedvideourl) {
                // Even though it's really a URL activity, display it as "video" activity with video icon.
                if ($this->courseformatoptions['courseusesubtiles']) {
                    $moduleobject['extraclasses'] .= ' video';
                    $moduleobject['modnameDisplay'] = get_string('displaytitle_mod_mp4', 'format_tiles');
                } else {
                    $moduleobject['iconurl'] = $output->image_url('play-circle-solid', 'format_tiles');
                }
            }
        }

        if (
            ($mod->modname === 'url' || $mod->modname === 'resource')
            && $this->devicetype != \core_useragent::DEVICETYPE_TABLET
            && $this->devicetype != \core_useragent::DEVICETYPE_MOBILE
        ) {
            // If the non JS link is used, it redirects from /mod/xxx/view.php to external or pluginURL.
            $moduleobject['url'] .= '&redirect=1';
        }

        // Now completion information for the individual course module.
        $completion = $mod->completion && $this->completioninfo && $this->completioninfo->is_enabled($mod) && $mod->available;
        if ($completion) {
            // Add completion icon to the course module if appropriate.
            $moduleobject['hascompletion'] = true;
            $completiondata = $this->completioninfo->get_data($mod, true);
            $moduleobject['completionstate'] = $completiondata->completionstate;
            $moduleobject['iscomplete'] = $completiondata->completionstate
                && $completiondata->completionstate !== COMPLETION_COMPLETE_FAIL;
            $moduleobject['completionstateInverse'] = $completiondata->completionstate == 1 ? 0 : 1;
            if ($mod->completion == COMPLETION_TRACKING_MANUAL) {
                $moduleobject['completionIsManual'] = 1;
                switch ($completiondata->completionstate) {
                    case COMPLETION_INCOMPLETE:
                        $moduleobject['completionstring'] = get_string('togglecompletionincomplete', 'format_tiles');
                        break;
                    case COMPLETION_COMPLETE:
                        $moduleobject['completionstring'] = get_string('togglecompletioncomplete', 'format_tiles');
                        break;
                }
            } else { // Automatic.
                switch ($completiondata->completionstate) {
                    case COMPLETION_INCOMPLETE:
                        $moduleobject['completionstring'] = get_string('complete-n-auto', 'format_tiles');
                        break;
                    case COMPLETION_COMPLETE:
                        $moduleobject['completionstring'] = get_string('complete-y-auto', 'format_tiles');
                        break;
                    case COMPLETION_COMPLETE_PASS:
                        $moduleobject['completionstring'] = get_string('completion-pass', 'core_completion', $mod->name);
                        break;
                    case COMPLETION_COMPLETE_FAIL:
                        $moduleobject['completionstring'] = get_string('completion-fail', 'core_completion', $mod->name);
                        $moduleobject['isfail'] = 1;
                        break;
                }
            }
        }
        return $moduleobject;
    }

    /**
     * Get resource file type e.g. 'doc' from the icon URL e.g. 'document-24.png'
     * Not ideal but we already have icon name so it's efficient
     * Adapted from Snap theme
     * @see mod_displayname() which gets the display name for the type
     *
     * @param \cm_info $mod the mod info object we are checking
     * @return string the type e.g. 'doc'
     */
    private function get_resource_filetype(\cm_info $mod) {
        if ($mod->modname === 'resource') {
            $fs = get_file_storage();
            $files = $fs->get_area_files($mod->context->id, 'mod_resource', 'content');
            $extensions = array(
                'powerpoint' => 'ppt',
                'document' => 'doc',
                'spreadsheet' => 'xls',
                'archive' => 'zip',
                'application/pdf' => 'pdf',
                'mp3' => 'mp3',
                'mpeg' => 'mp4',
                'image/jpeg' => 'jpeg',
                'text/plain' => 'txt',
                'text/html' => 'html'
            );
            foreach ($files as $file) {
                if ($file->get_filesize() && $mimetype = $file->get_mimetype()) {
                    if (in_array($mimetype, array_keys($extensions))) {
                        return $extensions[$mimetype];
                    }
                }
            }
        }
        return '';
    }

    /**
     * Adapted from mod/resource/view.php
     * @param \cm_info $cm the course module object
     * @return string url for file
     * @throws \coding_exception
     * @throws \dml_exception
     */
    private function plugin_file_url($cm) {
        global $DB, $CFG;
        $context = \context_module::instance($cm->id);
        $resource = $DB->get_record('resource', array('id' => $cm->instance), '*', MUST_EXIST);
        $fs = get_file_storage();
        $files = $fs->get_area_files(
            $context->id, 'mod_resource', 'content', 0, 'sortorder DESC, id ASC', false
        );
        if (count($files) >= 1 ) {
            $file = reset($files);
            unset($files);
            $resource->mainfile = $file->get_filename();
            return $CFG->wwwroot . '/pluginfile.php/' . $context->id . '/mod_resource/content/'
                . $resource->revision . $file->get_filepath() . rawurlencode($file->get_filename());
        }
        return '';
    }

    /**
     * Get the display name for each module or resource type
     * from the modname, to be displayed at the top of each tile
     * e.g. 'mod/lti' => 'External Tool' 'mod/resource','xls' = "Spreadsheet'
     * Once we have it , store it in instance var e.g. to avoid repeated check of 'pdf'
     * @param string $modname the name of the module e.g. 'resource'
     * @param string|null $resourcetype if this is a resource, the specific type eg. 'xls' or 'pdf'
     * @return string to be displayed on tile
     * @see get_resource_filetype()
     * @throws \coding_exception
     */
    private function mod_displayname($modname, $resourcetype = null) {
        if ($modname == 'resource') {
            if (isset($this->resourcedisplaynames[$resourcetype])) {
                return $this->resourcedisplaynames[$resourcetype];
            } else if (get_string_manager()->string_exists('displaytitle_mod_' . $resourcetype, 'format_tiles')) {
                $str = get_string('displaytitle_mod_' . $resourcetype, 'format_tiles');
                $this->resourcedisplaynames[$resourcetype] = $str;
                return $str;
            } else {
                $str = get_string('other', 'format_tiles');
                $this->resourcedisplaynames[$resourcetype] = $str;
                return $str;
            }
        } else {
            return get_string('modulename', 'mod_' . $modname);
        }
    }

    /**
     * For the legacy navigation arrows, establish the section number of the next and previous sections.
     * @param int $currentsectionnum the section number of the section we are in.
     * @return array previous and next section numbers.
     */
    private function get_previous_next_section_numbers(int $currentsectionnum): array {
        $visiblesectionnums = [];
        $currentsectionarrayindex = -1;
        foreach ($this->modinfo->get_section_info_all() as $section) {
            if ($section->section == 0) {
                continue;
            }
            if ($section->uservisible) {
                $visiblesectionnums[] = $section->section;
                if ($section->section <= $currentsectionnum) {
                    $currentsectionarrayindex++;
                }
            }
        }

        // If $currentsectionarrayindex is zero, this means we are on the first available section so there is no "previous".
        $previous = $currentsectionarrayindex == 0 ? 0 : $visiblesectionnums[$currentsectionarrayindex - 1];

        // If there is no item at the next index, there is no "next" (so set next to zero).
        $next = $visiblesectionnums[$currentsectionarrayindex + 1] ?? 0;

        return array('previous' => $previous, 'next' => $next);
    }

    /**
     * Prepare the data required to render a progress indicator (.e. 2/3 items complete)
     * to be shown on the tile or as an overall course progress indicator
     * @param int $numcomplete how many items are complete
     * @param int $numoutof how many items are available for completion
     * @param boolean $aspercent should we show the indicator as a percentage or numeric
     * @param boolean $isoverall whether this is an overall course completion indicator
     * @return array data for output template
     */
    public function completion_indicator($numcomplete, $numoutof, $aspercent, $isoverall) {
        $percentcomplete = $numoutof == 0 ? 0 : round(($numcomplete / $numoutof) * 100, 0);
        $progressdata = array(
            'numComplete' => $numcomplete,
            'numOutOf' => $numoutof,
            'percent' => $percentcomplete,
            'isComplete' => $numcomplete > 0 && $numcomplete == $numoutof ? 1 : 0,
            'isOverall' => $isoverall,
        );
        if ($aspercent) {
            // Percent in circle.
            $progressdata['showAsPercent'] = true;
            $circumference = 106.8;
            $progressdata['percentCircumf'] = $circumference;
            $progressdata['percentOffset'] = round(((100 - $percentcomplete) / 100) * $circumference, 0);
        }
        $progressdata['isSingleDigit'] = $percentcomplete < 10; // Position single digit in centre of circle.
        return $progressdata;
    }

    /**
     * If the URL is a YouTube or Vimeo URL etc, make some adjustments for embedding.
     * Teacher probably used standard watch URL so fix it if so.
     * @param string $url
     * @return string|boolean string the URL if it was en embed video URL, false if not.
     */
    private function check_modify_embedded_url(string $url) {
        // Youtube.
        $matches = null;
        $pattern = '/^(http(s)??\:\/\/)?(www\.)?((youtube\.com\/watch\?v=)|(youtu.be\/))([a-zA-Z0-9\-_]+)(\?t=[0-9]+)*$/';
        preg_match($pattern, $url, $matches);
        if ($matches && isset($matches[7])) {
            if (isset($matches[8])) {
                $starttime = filter_var($matches[8], FILTER_SANITIZE_NUMBER_INT);
                if ($starttime) {
                    return "https://www.youtube.com/embed/{$matches[7]}?start=$starttime";
                }
            }
            return "https://www.youtube.com/embed/{$matches[7]}";
        }

        // Vimeo.
        $matches = null;
        $pattern = '/^(https?:\/\/)?(www.)?(player.)?vimeo.com\/([a-z]*\/)*([0-9]{6,11})([?]?.*)$/';
        preg_match($pattern, $url, $matches);
        if ($matches && isset($matches[5])) {
            if (isset($matches[6])) {
                return "https://player.vimeo.com/video/{$matches[5]}{$matches[6]}";
            }
            return "https://player.vimeo.com/video/{$matches[5]}";
        }

        return false;
    }
}
