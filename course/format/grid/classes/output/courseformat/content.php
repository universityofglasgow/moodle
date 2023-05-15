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
 * Grid Format.
 *
 * @package   format_grid
 * @copyright 2020 Ferran Recio <ferran@moodle.com>
 * @copyright &copy; 2022-onwards G J Barnard.
 * @author    G J Barnard - gjbarnard at gmail dot com and {@link http://moodle.org/user/profile.php?id=442195}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_grid\output\courseformat;

use core_courseformat\output\local\content as content_base;
use stdClass;

/**
 * Base class to render a course content.
 *
 * @package   format_grid
 * @copyright 2020 Ferran Recio <ferran@moodle.com>
 * @copyright 2022 G J Barnard - gjbarnard at gmail dot com and {@link http://moodle.org/user/profile.php?id=442195}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class content extends content_base {

    private $sectioncompletionpercentage = array();
    private $sectioncompletionmarkup = array();
    private $sectioncompletioncalculated = array();

    /**
     * @var bool Grid format does not add section after each topic.
     *
     * The responsible for the buttons is core_courseformat\output\local\content\section.
     */
    protected $hasaddsection = false;

    public function get_template_name(\renderer_base $renderer): string {
        return 'format_grid/local/content';
    }

    /**
     * Export this data so it can be used as the context for a mustache template (core/inplace_editable).
     *
     * @param renderer_base $output typically, the renderer that's calling this function
     * @return stdClass data context for a Mustache template
     */
    public function export_for_template(\renderer_base $output) {
        global $DB, $PAGE;
        $format = $this->format;
        $editing = $PAGE->user_is_editing();

        $data = (object)[
            'title' => $format->page_title(),
            'format' => $format->get_format(),
            'sectionreturn' => 0,
        ];

        $singlesection = $this->format->get_section_number();
        $sections = $this->export_sections($output);
        $initialsection = '';
        $course = $format->get_course();
        $currentsectionid = 0;

        if ($editing) {
            $data->coursesettings = new \moodle_url('/course/edit.php', array('id' => $course->id));
        }

        if (!empty($sections)) {
            // Most formats uses section 0 as a separate section so we remove from the list.
            $initialsection = array_shift($sections);
            if (!$singlesection) {
                $data->initialsection = $initialsection;
            }
            if (($editing) || ($singlesection)) { // This triggers the display of the standard list of section(s).
                $data->sections = $sections;
            }
            if (!empty($course->marker)) {
                foreach ($sections as $section) {
                    if ($section->num == $course->marker) {
                        $currentsectionid = $section->id;
                        break;
                    }
                }
            }
        }

        // The single section format has extra navigation.
        if ($singlesection) {
            $sectionnavigation = new $this->sectionnavigationclass($format, $singlesection);
            $data->sectionnavigation = $sectionnavigation->export_for_template($output);

            $sectionselector = new $this->sectionselectorclass($format, $sectionnavigation);
            $data->sectionselector = $sectionselector->export_for_template($output);
            $data->hasnavigation = true;
            $data->singlesection = array_shift($data->sections);
            $data->sectionreturn = $singlesection;
            $data->maincoursepage = new \moodle_url('/course/view.php', array('id' => $course->id));
        } else {
            $coursesettings = $format->get_settings();
            $toolbox = \format_grid\toolbox::get_instance();
            $coursesectionimages = $DB->get_records('format_grid_image', array('courseid' => $course->id));
            if (!empty($coursesectionimages)) {
                $fs = get_file_storage();
                $coursecontext = \context_course::instance($course->id);
                foreach ($coursesectionimages as $coursesectionimage) {
                    $replacement = $toolbox->check_displayed_image($coursesectionimage, $course->id, $coursecontext->id,
                        $coursesectionimage->sectionid, $format, $fs);
                    if (!empty($replacement)) {
                        $coursesectionimages[$coursesectionimage->id] = $replacement;
                    }
                }
            }

            // Popup.
            if (!$editing) {
                $data->popup = false;
                if ((!empty($coursesettings['popup'])) && ($coursesettings['popup'] == 2)) {
                    $data->popup = true;
                    $data->popupsections = array();
                    $potentialpopupsections = array();
                    foreach ($sections as $section) {
                        $potentialpopupsections[$section->id] = $section;
                    }
                }
            }

            // Suitable array.
            $sectionimages = array();
            foreach ($coursesectionimages as $coursesectionimage) {
                $sectionimages[$coursesectionimage->sectionid] = $coursesectionimage;
            }

            // Now iterate over the sections.
            $data->gridsections = array();
            $sectionsforgrid = $this->get_grid_sections($output, $coursesettings);
            $iswebp = (get_config('format_grid', 'defaultdisplayedimagefiletype') == 2);

            $completionshown = false;
            $headerimages = false;
            if ($editing) {
                $datasectionmap = array();
                foreach ($data->sections as $datasectionkey => $datasection) {
                    $datasectionmap[$datasection->id] = $datasectionkey;
                }
            }
            foreach ($sectionsforgrid as $section) {
                // Do we have an image?
                if ((array_key_exists($section->id, $sectionimages)) && ($sectionimages[$section->id]->displayedimagestate >= 1)) {
                    $sectionimages[$section->id]->imageuri = $toolbox->get_displayed_image_uri(
                        $sectionimages[$section->id], $coursecontext->id, $section->id, $iswebp);
                } else {
                    // No.
                    $sectionimages[$section->id] = new stdClass;
                    $sectionimages[$section->id]->generatedimageuri = $output->get_generated_image_for_id($section->id);
                }
                // Number.
                $sectionimages[$section->id]->number = $section->num;

                // Alt text.
                $sectionformatoptions = $format->get_format_options($section);
                $sectionimages[$section->id]->imagealttext = $sectionformatoptions['sectionimagealttext'];

                // Current section?
                if ((!empty($currentsectionid)) && ($currentsectionid == $section->id)) {
                    $sectionimages[$section->id]->currentsection = true;
                }

                if ($editing) {
                    if (!empty($data->sections[$section->num])) {
                        // Add the image to the section content.
                        $data->sections[$datasectionmap[$section->id]]->gridimage = $sectionimages[$section->id];
                        $headerimages = true;
                    }
                } else {
                    // Section link.
                    $sectionimages[$section->id]->sectionurl = new \moodle_url(
                        '/course/view.php',
                        array('id' => $course->id, 'section' => $section->num)
                    );
                    $sectionimages[$section->id]->sectionurl = $sectionimages[$section->id]->sectionurl->out(false);

                    // Section name.
                    $sectionimages[$section->id]->sectionname = $section->name;

                    // Section break.
                    if ($sectionformatoptions['sectionbreak'] == 2) { // Yes.
                        $sectionimages[$section->id]->sectionbreak = true;
                        if (!empty ($sectionformatoptions['sectionbreakheading'])) {
                            // Note:  As a PARAM_TEXT, then does need to be passed through 'format_string' for multi-lang or not?
                            $sectionimages[$section->id]->sectionbreakheading = format_text(
                                $sectionformatoptions['sectionbreakheading'],
                                FORMAT_HTML
                            );
                        }
                    }

                    // Completion?
                    if (!empty($section->sectioncompletionmarkup)) {
                        $sectionimages[$section->id]->sectioncompletionmarkup = $section->sectioncompletionmarkup;
                        $completionshown = true;
                    }

                    // For the template.
                    $data->gridsections[] = $sectionimages[$section->id];
                    if ($data->popup) {
                        $data->popupsections[] = $potentialpopupsections[$section->id];
                    }
                }
            }

            $data->hasgridsections = (!empty($data->gridsections)) ? true : false;
            if ($data->hasgridsections) {
                $data->coursestyles = $toolbox->get_displayed_image_container_properties($coursesettings);
                if ((!empty($coursesettings['showcompletion'])) && ($coursesettings['showcompletion'] == 2) && ($completionshown)) {
                    $data->showcompletion = true;
                }
            }

            if ($headerimages) {
                $data->hasheaderimages = true;
                $coursesettings['imagecontainerwidth'] = 144;
                $data->coursestyles = $toolbox->get_displayed_image_container_properties($coursesettings);
            }
        }

        if ($this->hasaddsection) {
            $addsection = new $this->addsectionclass($format);
            $data->numsections = $addsection->export_for_template($output);
        }

        return $data;
    }

    /**
     * Export sections array data.
     *
     * @param renderer_base $output typically, the renderer that's calling this method.
     * @param array $settings The settings for the format.
     *
     * @return array data context for a mustache template
     */
    protected function get_grid_sections(\renderer_base $output, $settings): array {

        $format = $this->format;
        $course = $format->get_course();
        $modinfo = $this->format->get_modinfo();

        // Generate section list.
        $sections = [];
        $numsections = $format->get_last_section_number();
        $sectioninfos = $modinfo->get_section_info_all();
        // Get rid of section 0.
        if (!empty($sectioninfos)) {
            array_shift($sectioninfos);
        }
        foreach ($sectioninfos as $thissection) {
            // The course/view.php check the section existence but the output can be called from other parts so we need to check it.
            if (!$thissection) {
                throw new \moodle_exception('unknowncoursesection', 'error', '',
                    get_string('unknowncoursesection', 'error',
                        course_get_url($course).' - '.format_string($course->fullname))
                    );
            }

            if ($thissection->section > $numsections) {
                continue;
            }

            if (!$format->is_section_visible($thissection)) {
                continue;
            }

            $section = new stdClass;
            $section->id = $thissection->id;
            $section->num = $thissection->section;
            $section->name = $output->section_title_without_link($thissection, $course);
            if ((!empty($settings['showcompletion'])) && ($settings['showcompletion'] == 2)) {
                $this->calculate_section_activity_completion($thissection, $course, $modinfo, $output);
                if (!empty($this->sectioncompletionmarkup[$thissection->section])) {
                    $section->sectioncompletionmarkup = $this->sectioncompletionmarkup[$thissection->section];
                }
            }
            $sections[] = $section;
        }

        return $sections;
    }

    /**
     * Calculate and generate the markup for completion of the activities in a section.
     *
     * @param stdClass $section The course_section.
     * @param stdClass $course the course.
     * @param stdClass $modinfo the course module information.
     * @param renderer_base $output typically, the renderer that's calling this method.
     */
    protected function calculate_section_activity_completion($section, $course, $modinfo, \renderer_base $output) {
        if (empty($this->sectioncompletioncalculated[$section->section])) {
            $this->sectioncompletionmarkup[$section->section] = '';
            if (empty($modinfo->sections[$section->section])) {
                $this->sectioncompletioncalculated[$section->section] = true;
                return;
            }

            // Generate array with count of activities in this section.
            $total = 0;
            $complete = 0;
            $cancomplete = isloggedin() && !isguestuser();
            $asectionisavailable = false;
            if ($cancomplete) {
                $completioninfo = new \completion_info($course);
                foreach ($modinfo->sections[$section->section] as $cmid) {
                    $thismod = $modinfo->cms[$cmid];

                    if ($thismod->available) {
                        $asectionisavailable = true;
                        if ($completioninfo->is_enabled($thismod) != COMPLETION_TRACKING_NONE) {
                            $total++;
                            $completiondata = $completioninfo->get_data($thismod, true);
                            if ($completiondata->completionstate == COMPLETION_COMPLETE ||
                                $completiondata->completionstate == COMPLETION_COMPLETE_PASS) {
                                $complete++;
                            }
                        }
                    }
                }
            }

            if ((!$asectionisavailable) || (!$cancomplete)) {
                // No sections or no completion.
                $this->sectioncompletioncalculated[$section->section] = true;
                return;
            }

            // Output section completion data.
            if ($total > 0) {
                $percentage = round(($complete / $total) * 100);
                $this->sectioncompletionpercentage[$section->section] = $percentage;

                $data = new \stdClass();
                $data->percentagevalue = $this->sectioncompletionpercentage[$section->section];
                if ($data->percentagevalue < 11) {
                    $data->percentagecolour = 'low';
                } else if ($data->percentagevalue < 90) {
                    $data->percentagecolour = 'middle';
                } else {
                    $data->percentagecolour = 'high';
                }
                if ($data->percentagevalue < 1) {
                    $data->percentagequarter = 0;
                } else if ($data->percentagevalue < 26) {
                    $data->percentagequarter = 1;
                } else if ($data->percentagevalue < 51) {
                    $data->percentagequarter = 2;
                } else if ($data->percentagevalue < 76) {
                    $data->percentagequarter = 3;
                } else {
                    $data->percentagequarter = 4;
                }
                $this->sectioncompletionmarkup[$section->section] = $output->render_from_template('format_grid/grid_completion', $data);
            }

            $this->sectioncompletioncalculated[$section->section] = true;
        }
    }
}
