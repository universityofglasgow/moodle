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
 * Grid Format - A topics based format that uses a grid of user selectable images to popup a light box of the section.
 *
 * @package    course/format
 * @subpackage grid
 * @copyright  &copy; 2012+ G J Barnard in respect to modifications of standard topics format.
 * @author     G J Barnard - gjbarnard at gmail dot com, about.me/gjbarnard and {@link http://moodle.org/user/profile.php?id=442195}
 * @author     Based on code originally written by Paul Krix and Julian Ridden.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/format/lib.php'); // For format_base.

class format_grid extends format_base {

    // CONTRIB-4099:....
    // Width constants - 128, 192, 210, 256, 320, 384, 448, 512, 576, 640, 704 and 768:...
    private static $imagecontainerwidths = array(128 => '128', 192 => '192', 210 => '210', 256 => '256', 320 => '320',
        384 => '384', 448 => '448', 512 => '512', 576 => '576', 640 => '640', 704 => '704', 768 => '768');
    // Ratio constants - 3-2, 3-1, 3-3, 2-3, 1-3, 4-3 and 3-4:...
    private static $imagecontainerratios = array(
        1 => '3-2', 2 => '3-1', 3 => '3-3', 4 => '2-3', 5 => '1-3', 6 => '4-3', 7 => '3-4');
    // Border width constants - 1 to 10:....
    private static $borderwidths = array(1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5', 6 => '6', 7 => '7', 8 => '8',
        9 => '9', 10 => '10');
    /* Image holder height and new activity position for all on the basis that once calculated the majority of courses
      will be the same. */
    private static $currentwidth = 210;
    private static $currentratio = 1; // 3-2.
    private static $currentborderwidth = 3;
    private static $currentheight = 140;
    private static $activitymargintop = 101;
    private static $activitymarginleft = 1118;
    private $settings;

    /**
     * Creates a new instance of class
     *
     * Please use {@link course_get_format($courseorid)} to get an instance of the format class
     *
     * @param string $format
     * @param int $courseid
     * @return format_grid
     */
    protected function __construct($format, $courseid) {
        if ($courseid === 0) {
            global $COURSE;
            $courseid = $COURSE->id;  // Save lots of global $COURSE as we will never be the site course.
        }
        parent::__construct($format, $courseid);
    }

    /**
     * Returns the default section name for the format.
     *
     * @param stdClass $section Section object from database or just field course_sections section
     * @return string The default value for the section name.
     */
    public function get_default_section_name($section) {
        /* Follow the same logic so that this method is supported.  The MDL-51610 enchancement refactored things,
          but that is not appropriate for us. */
        return $this->get_section_name($section);
    }

    /**
     * Prevents ability to change a static variable outside of the class.
     * @return array Array of imagecontainer widths.
     */
    public static function get_image_container_widths() {
        return self::$imagecontainerwidths;
    }

    /**
     * Gets the default image container width.
     * @return int Default image container width.
     */
    public static function get_default_image_container_width() {
        return 210;
    }

    /**
     * Prevents ability to change a static variable outside of the class.
     * @return array Array of image container ratios.
     */
    public static function get_image_container_ratios() {
        return self::$imagecontainerratios;
    }

    /**
     * Gets the default image container ratio.
     * @return int Default image container ratio.
     */
    public static function get_default_image_container_ratio() {
        return 1; // Ratio of '3-2'.
    }

    /**
     * Gets the default image resize method.
     * @return int Default image resize method.
     */
    public static function get_default_image_resize_method() {
        return 1; // Scale.
    }

    /**
     * Gets the default border colour.
     * @return string Default border colour.
     */
    public static function get_default_border_colour() {
        return '#dddddd';
    }

    /**
     * Prevents ability to change a static variable outside of the class.
     * @return array Array of border widths.
     */
    public static function get_border_widths() {
        return self::$borderwidths;
    }

    /**
     * Gets the default border width.
     * @return int Default border width.
     */
    public static function get_default_border_width() {
        return 3; // Pixels.
    }

    /**
     * Gets the default border width.
     * @return int Default border width.
     */
    public static function get_default_border_radius() {
        return 2; // On.
    }

    /**
     * Gets the default image container background colour.
     * @return string Default image container background colour.
     */
    public static function get_default_image_container_background_colour() {
        return '#f1f2f2';
    }

    /**
     * Gets the default current selected section colour.
     * @return string Default current selected section colour.
     */
    public static function get_default_current_selected_section_colour() {
        return '#8E66FF';
    }

    /**
     * Gets the default current selected image container text colour.
     * @return string Default current selected image container text colour.
     */
    public static function get_default_current_selected_image_container_text_colour() {
        return '#3b53ad';
    }

    /**
     * Gets the default current selected image container colour.
     * @return string Default current selected image container colour.
     */
    public static function get_default_current_selected_image_container_colour() {
        return '#ffc540';
    }

    /**
     * Gets the default hide section title.
     * @return int Default default hide section title.
     */
    public static function get_default_hide_section_title() {
        return 1; // No.
    }

    /**
     * Gets the default section title grid length max option.
     * @return int Default default section title grid length max option.
     */
    public static function get_default_section_title_grid_length_max_option() {
        return 0; // No truncation.
    }

    /**
     * Gets the default section title box position.
     * @return int Default default section title box position.
     */
    public static function get_default_section_title_box_position() {
        return 2; // Outside.
    }

    /**
     * Gets the default section title box inside position.
     * @return int Default default section title box inside position.
     */
    public static function get_default_section_title_box_inside_position() {
        return 1; // Top.
    }

    /**
     * Gets the default show section title summary.
     * @return int Default default show section title summary.
     */
    public static function get_default_show_section_title_summary() {
        return 2; // Yes.
    }

    /**
     * Gets the default set show section title summary position.
     * @return int Default default set show section title summary position.
     */
    public static function get_default_set_show_section_title_summary_position() {
        return 1; // Top.
    }

    /**
     * Gets the default section title inside text colour.
     * @return string Default default section title inside text colour.
     */
    public static function get_default_section_title_inside_title_text_colour() {
        return '#000000';
    }

    /**
     * Gets the default section title inside background colour.
     * @return string Default default section title inside background colour.
     */
    public static function get_default_section_title_inside_title_background_colour() {
        return '#ffffff';
    }

    /**
     * Gets the displayed image path for storage of the displayed image.
     * @return string The path.
     */
    public static function get_image_path() {
        return '/gridimage/';
    }

    public static function get_maximum_image_width() {
        return 768;
    }

    /**
     * Returns the format's settings and gets them if they do not exist.
     * @return array The settings as an array.
     */
    public function get_settings() {
        if (empty($this->settings) == true) {
            $this->settings = $this->get_format_options();
        }
        return $this->settings;
    }

    /**
     * Returns the mapped value of the 'setshowsectiontitlesummaryposition' setting.
     * @return string One of 'top', 'bottom', 'left' or 'right'.
     */
    public function get_set_show_section_title_summary_position() {
        $settings = $this->get_settings();
        $returnvalue = 'top';

        switch($settings['setshowsectiontitlesummaryposition']) {
            case 1:
                $returnvalue = 'top';
                break;
            case 2:
                $returnvalue = 'bottom';
                break;
            case 3:
                $returnvalue = 'left';
                break;
            case 4:
                $returnvalue = 'right';
                break;
        }

        return $returnvalue;
    }

    /**
     * Gets the name for the provided section.
     *
     * @param stdClass $section The section.
     * @return string The section name.
     */
    public function get_section_name($section) {
        $section = $this->get_section($section);
        if (!empty($section->name)) {
            return format_string($section->name, true, array('context' => context_course::instance($this->courseid)));
        } if ($section->section == 0) {
            return get_string('topic0', 'format_grid');
        } else {
            return get_string('topic', 'format_grid') . ' ' . $section->section;
        }
    }

    /**
     * Indicates this format uses sections.
     *
     * @return bool Returns true
     */
    public function uses_sections() {
        return true;
    }

    /**
     * The URL to use for the specified course (with section)
     *
     * @param int|stdClass $section Section object from database or just field course_sections.section
     *     if omitted the course view page is returned
     * @param array $options options for view URL. At the moment core uses:
     *     'navigation' (bool) if true and section has no separate page, the function returns null
     *     'sr' (int) used by multipage formats to specify to which section to return
     * @return null|moodle_url
     */
    public function get_view_url($section, $options = array()) {
        $course = $this->get_course();
        $url = new moodle_url('/course/view.php', array('id' => $course->id));

        $sr = null;
        if (array_key_exists('sr', $options)) {
            $sr = $options['sr'];
        }
        if (is_object($section)) {
            $sectionno = $section->section;
        } else {
            $sectionno = $section;
        }
        if ($sectionno !== null) {
            if ($sr !== null) {
                if ($sr) {
                    $usercoursedisplay = COURSE_DISPLAY_MULTIPAGE;
                    $sectionno = $sr;
                } else {
                    $usercoursedisplay = COURSE_DISPLAY_SINGLEPAGE;
                }
            } else {
                $usercoursedisplay = $course->coursedisplay;
            }
            $topic0attop = $this->get_summary_visibility($course->id)->showsummary == 1;
            if ($sectionno != 0 && $usercoursedisplay == COURSE_DISPLAY_MULTIPAGE) {
                $url->param('section', $sectionno);
            } else if ($sectionno == 0 && $usercoursedisplay == COURSE_DISPLAY_MULTIPAGE && (!$topic0attop)) {
                $url->param('section', $sectionno);
            } else {
                global $CFG;
                if (empty($CFG->linkcoursesections) && !empty($options['navigation'])) {
                    return null;
                }
                $url->set_anchor('section-' . $sectionno);
            }
        }
        return $url;
    }

    /**
     * Returns the information about the ajax support in the given source format
     *
     * The returned object's property (boolean)capable indicates that
     * the course format supports Moodle course ajax features.
     * The property (array)testedbrowsers can be used as a parameter for {@link ajaxenabled()}.
     *
     * @return stdClass
     */
    public function supports_ajax() {
        $ajaxsupport = new stdClass();
        $ajaxsupport->capable = true;
        return $ajaxsupport;
    }

    /**
     * Custom action after section has been moved in AJAX mode
     *
     * Used in course/rest.php
     *
     * @return array This will be passed in ajax respose
     */
    public function ajax_section_move() {
        global $PAGE;
        $titles = array();
        $course = $this->get_course();
        $modinfo = get_fast_modinfo($course);
        $renderer = $this->get_renderer($PAGE);
        if ($renderer && ($sections = $modinfo->get_section_info_all())) {
            foreach ($sections as $number => $section) {
                $titles[$number] = $renderer->section_title($section, $course);
            }
        }
        return array('sectiontitles' => $titles, 'action' => 'move');
    }

    /**
     * Returns the list of blocks to be automatically added for the newly created course
     *
     * @return array of default blocks, must contain two keys BLOCK_POS_LEFT and BLOCK_POS_RIGHT
     *     each of values is an array of block names (for left and right side columns)
     */
    public function get_default_blocks() {
        return array(
            BLOCK_POS_LEFT => array(),
            BLOCK_POS_RIGHT => array('search_forums', 'news_items', 'calendar_upcoming', 'recent_activity')
        );
    }

    /**
     * Definitions of the additional options that this course format uses for the course.
     *
     * @param bool $foreditform
     * @return array of options
     */
    public function course_format_options($foreditform = false) {
        static $courseformatoptions = false;

        if ($courseformatoptions === false) {
            /* Note: Because 'admin_setting_configcolourpicker' in 'settings.php' needs to use a prefixing '#'
              this needs to be stripped off here if it's there for the format's specific colour picker. */
            $defaults = $this->get_course_format_colour_defaults();

            $courseconfig = get_config('moodlecourse');
            $courseformatoptions = array(
                'numsections' => array(
                    'default' => $courseconfig->numsections,
                    'type' => PARAM_INT
                ),
                'hiddensections' => array(
                    'default' => $courseconfig->hiddensections,
                    'type' => PARAM_INT
                ),
                'coursedisplay' => array(
                    'default' => $courseconfig->coursedisplay,
                    'type' => PARAM_INT
                ),
                'imagecontainerwidth' => array(
                    'default' => get_config('format_grid', 'defaultimagecontainerwidth'),
                    'type' => PARAM_INT
                ),
                'imagecontainerratio' => array(
                    'default' => get_config('format_grid', 'defaultimagecontainerratio'),
                    'type' => PARAM_ALPHANUM
                ),
                'imageresizemethod' => array(
                    'default' => get_config('format_grid', 'defaultimageresizemethod'),
                    'type' => PARAM_INT
                ),
                'bordercolour' => array(
                    'default' => $defaults['defaultbordercolour'],
                    'type' => PARAM_ALPHANUM
                ),
                'borderwidth' => array(
                    'default' => get_config('format_grid', 'defaultborderwidth'),
                    'type' => PARAM_INT
                ),
                'borderradius' => array(
                    'default' => get_config('format_grid', 'defaultborderradius'),
                    'type' => PARAM_INT
                ),
                'imagecontainerbackgroundcolour' => array(
                    'default' => $defaults['defaultimagecontainerbackgroundcolour'],
                    'type' => PARAM_ALPHANUM
                ),
                'currentselectedsectioncolour' => array(
                    'default' => $defaults['defaultcurrentselectedsectioncolour'],
                    'type' => PARAM_ALPHANUM
                ),
                'currentselectedimagecontainertextcolour' => array(
                    'default' => $defaults['defaultcurrentselectedimagecontainertextcolour'],
                    'type' => PARAM_ALPHANUM
                ),
                'currentselectedimagecontainercolour' => array(
                    'default' => $defaults['defaultcurrentselectedimagecontainercolour'],
                    'type' => PARAM_ALPHANUM
                ),
                'hidesectiontitle' => array(
                    'default' => get_config('format_grid', 'defaulthidesectiontitle'),
                    'type' => PARAM_INT
                ),
                'sectiontitlegridlengthmaxoption' => array(
                    'default' => get_config('format_grid', 'defaultsectiontitlegridlengthmaxoption'),
                    'type' => PARAM_INT
                ),
                'sectiontitleboxposition' => array(
                    'default' => get_config('format_grid', 'defaultsectiontitleboxposition'),
                    'type' => PARAM_INT
                ),
                'sectiontitleboxinsideposition' => array(
                    'default' => get_config('format_grid', 'defaultsectiontitleboxinsideposition'),
                    'type' => PARAM_INT
                ),
                'showsectiontitlesummary' => array(
                    'default' => get_config('format_grid', 'defaultshowsectiontitlesummary'),
                    'type' => PARAM_INT
                ),
                'setshowsectiontitlesummaryposition' => array(
                    'default' => get_config('format_grid', 'defaultsetshowsectiontitlesummaryposition'),
                    'type' => PARAM_INT
                ),
                'sectiontitleinsidetitletextcolour' => array(
                    'default' => $defaults['defaultsectiontitleinsidetitletextcolour'],
                    'type' => PARAM_ALPHANUM
                ),
                'sectiontitleinsidetitlebackgroundcolour' => array(
                    'default' => $defaults['defaultsectiontitleinsidetitlebackgroundcolour'],
                    'type' => PARAM_ALPHANUM
                ),
                'newactivity' => array(
                    'default' => get_config('format_grid', 'defaultnewactivity'),
                    'type' => PARAM_INT
                ),
                'fitsectioncontainertowindow' => array(
                    'default' => get_config('format_grid', 'defaultfitsectioncontainertowindow'),
                    'type' => PARAM_INT
                ),
                'greyouthidden' => array(
                    'default' => get_config('format_grid', 'defaultgreyouthidden'),
                    'type' => PARAM_INT
                )
            );
        }
        if ($foreditform && !isset($courseformatoptions['coursedisplay']['label'])) {
            /* Note: Because 'admin_setting_configcolourpicker' in 'settings.php' needs to use a prefixing '#'
              this needs to be stripped off here if it's there for the format's specific colour picker. */
            $defaults = $this->get_course_format_colour_defaults();

            $coursecontext = context_course::instance($this->courseid);

            $courseconfig = get_config('moodlecourse');
            $sectionmenu = array();
            for ($i = 0; $i <= $courseconfig->maxsections; $i++) {
                $sectionmenu[$i] = "$i";
            }
            $courseformatoptionsedit = array(
                'numsections' => array(
                    'label' => new lang_string('numbersections', 'format_grid'),
                    'element_type' => 'select',
                    'element_attributes' => array($sectionmenu),
                ),
                'hiddensections' => array(
                    'label' => new lang_string('hiddensections'),
                    'help' => 'hiddensections',
                    'help_component' => 'moodle',
                    'element_type' => 'select',
                    'element_attributes' => array(
                        array(
                            0 => new lang_string('hiddensectionscollapsed'),
                            1 => new lang_string('hiddensectionsinvisible')
                        )
                    ),
                ),
                'coursedisplay' => array(
                    'label' => new lang_string('coursedisplay'),
                    'element_type' => 'select',
                    'element_attributes' => array(
                        array(
                            COURSE_DISPLAY_SINGLEPAGE => new lang_string('coursedisplay_single'),
                            COURSE_DISPLAY_MULTIPAGE => new lang_string('coursedisplay_multi')
                        )
                    ),
                    'help' => 'coursedisplay',
                    'help_component' => 'moodle',
                )
            );
            if (has_capability('format/grid:changeimagecontainersize', $coursecontext)) {
                $courseformatoptionsedit['imagecontainerwidth'] = array(
                    'label' => new lang_string('setimagecontainerwidth', 'format_grid'),
                    'help' => 'setimagecontainerwidth',
                    'help_component' => 'format_grid',
                    'element_type' => 'select',
                    'element_attributes' => array(self::$imagecontainerwidths)
                );
                $courseformatoptionsedit['imagecontainerratio'] = array(
                    'label' => new lang_string('setimagecontainerratio', 'format_grid'),
                    'help' => 'setimagecontainerratio',
                    'help_component' => 'format_grid',
                    'element_type' => 'select',
                    'element_attributes' => array(self::$imagecontainerratios)
                );
            } else {
                $courseformatoptionsedit['imagecontainerwidth'] = array('label' => get_config(
                            'format_grid', 'defaultimagecontainerwidth'), 'element_type' => 'hidden');
                $courseformatoptionsedit['imagecontainerratio'] = array('label' => get_config(
                            'format_grid', 'defaultimagecontainerratio'), 'element_type' => 'hidden');
            }

            if (has_capability('format/grid:changeimageresizemethod', $coursecontext)) {
                $courseformatoptionsedit['imageresizemethod'] = array(
                    'label' => new lang_string('setimageresizemethod', 'format_grid'),
                    'help' => 'setimageresizemethod',
                    'help_component' => 'format_grid',
                    'element_type' => 'select',
                    'element_attributes' => array(
                        array(
                            1 => new lang_string('scale', 'format_grid'), // Scale.
                            2 => new lang_string('crop', 'format_grid')   // Crop.
                        )
                    )
                );
            } else {
                $courseformatoptionsedit['imageresizemethod'] = array('label' => get_config(
                            'format_grid', 'defaultimageresizemethod'), 'element_type' => 'hidden');
            }

            if (has_capability('format/grid:changeimagecontainerstyle', $coursecontext)) {
                $courseformatoptionsedit['bordercolour'] = array(
                    'label' => new lang_string('setbordercolour', 'format_grid'),
                    'help' => 'setbordercolour',
                    'help_component' => 'format_grid',
                    'element_type' => 'gfcolourpopup',
                    'element_attributes' => array(
                        array('tabindex' => -1, 'value' => get_config('format_grid', 'defaultbordercolour'))
                    )
                );

                $courseformatoptionsedit['borderwidth'] = array(
                    'label' => new lang_string('setborderwidth', 'format_grid'),
                    'help' => 'setborderwidth',
                    'help_component' => 'format_grid',
                    'element_type' => 'select',
                    'element_attributes' => array(self::$borderwidths)
                );

                $courseformatoptionsedit['borderradius'] = array(
                    'label' => new lang_string('setborderradius', 'format_grid'),
                    'help' => 'setborderradius',
                    'help_component' => 'format_grid',
                    'element_type' => 'select',
                    'element_attributes' => array(
                        array(
                            1 => new lang_string('off', 'format_grid'),
                            2 => new lang_string('on', 'format_grid'))
                    )
                );

                $courseformatoptionsedit['imagecontainerbackgroundcolour'] = array(
                    'label' => new lang_string('setimagecontainerbackgroundcolour', 'format_grid'),
                    'help' => 'setimagecontainerbackgroundcolour',
                    'help_component' => 'format_grid',
                    'element_type' => 'gfcolourpopup',
                    'element_attributes' => array(
                        array('tabindex' => -1, 'value' => get_config('format_grid', 'defaultimagecontainerbackgroundcolour'))
                    )
                );

                $courseformatoptionsedit['currentselectedsectioncolour'] = array(
                    'label' => new lang_string('setcurrentselectedsectioncolour', 'format_grid'),
                    'help' => 'setcurrentselectedsectioncolour',
                    'help_component' => 'format_grid',
                    'element_type' => 'gfcolourpopup',
                    'element_attributes' => array(
                        array('tabindex' => -1, 'value' => get_config('format_grid', 'defaultcurrentselectedsectioncolour'))
                    )
                );

                $courseformatoptionsedit['currentselectedimagecontainertextcolour'] = array(
                    'label' => new lang_string('setcurrentselectedimagecontainertextcolour', 'format_grid'),
                    'help' => 'setcurrentselectedimagecontainertextcolour',
                    'help_component' => 'format_grid',
                    'element_type' => 'gfcolourpopup',
                    'element_attributes' => array(
                        array('tabindex' => -1, 'value' => get_config('format_grid', 'defaultcurrentselectedimagecontainertextcolour'))
                    )
                );

                $courseformatoptionsedit['currentselectedimagecontainercolour'] = array(
                    'label' => new lang_string('setcurrentselectedimagecontainercolour', 'format_grid'),
                    'help' => 'setcurrentselectedimagecontainercolour',
                    'help_component' => 'format_grid',
                    'element_type' => 'gfcolourpopup',
                    'element_attributes' => array(
                        array('tabindex' => -1, 'value' => get_config('format_grid', 'defaultcurrentselectedimagecontainercolour'))
                    )
                );
            } else {
                $courseformatoptionsedit['bordercolour'] = array('label' => $defaults['defaultbordercolour'],
                    'element_type' => 'hidden');
                $courseformatoptionsedit['borderwidth'] = array('label' => get_config('format_grid', 'defaultborderwidth'),
                    'element_type' => 'hidden');
                $courseformatoptionsedit['borderradius'] = array('label' => get_config('format_grid', 'defaultborderradius'),
                    'element_type' => 'hidden');
                $courseformatoptionsedit['imagecontainerbackgroundcolour'] = array(
                    'label' => $defaults['defaultimagecontainerbackgroundcolour'], 'element_type' => 'hidden');
                $courseformatoptionsedit['currentselectedsectioncolour'] = array(
                    'label' => $defaults['defaultcurrentselectedsectioncolour'], 'element_type' => 'hidden');
                $courseformatoptionsedit['currentselectedimagecontainertextcolour'] = array(
                    'label' => $defaults['defaultcurrentselectedimagecontainertextcolour'], 'element_type' => 'hidden');
                $courseformatoptionsedit['currentselectedimagecontainercolour'] = array(
                    'label' => $defaults['defaultcurrentselectedimagecontainercolour'], 'element_type' => 'hidden');
            }

            if (has_capability('format/grid:changesectiontitleoptions', $coursecontext)) {
                $courseformatoptionsedit['hidesectiontitle'] = array(
                    'label' => new lang_string('hidesectiontitle', 'format_grid'),
                    'element_type' => 'select',
                    'element_attributes' => array(
                        array(
                            1 => new lang_string('no'), // No.
                            2 => new lang_string('yes') // Yes.
                        )
                    ),
                    'help' => 'hidesectiontitle',
                    'help_component' => 'format_grid'
                );
                $courseformatoptionsedit['sectiontitlegridlengthmaxoption'] = array(
                    'label' => new lang_string('sectiontitlegridlengthmaxoption', 'format_grid'),
                    'element_type' => 'text',
                    'element_attributes' => array('size' => 3),
                    'help' => 'sectiontitlegridlengthmaxoption',
                    'help_component' => 'format_grid'
                );
                $courseformatoptionsedit['sectiontitleboxposition'] = array(
                    'label' => new lang_string('sectiontitleboxposition', 'format_grid'),
                    'element_type' => 'select',
                    'element_attributes' => array(
                        array(
                            1 => new lang_string('sectiontitleboxpositioninside', 'format_grid'),
                            2 => new lang_string('sectiontitleboxpositionoutside', 'format_grid')
                        )
                    ),
                    'help' => 'sectiontitleboxposition',
                    'help_component' => 'format_grid'
                );
                $courseformatoptionsedit['sectiontitleboxinsideposition'] = array(
                    'label' => new lang_string('sectiontitleboxinsideposition', 'format_grid'),
                    'element_type' => 'select',
                    'element_attributes' => array(
                        array(
                            1 => new lang_string('sectiontitleboxinsidepositiontop', 'format_grid'),
                            2 => new lang_string('sectiontitleboxinsidepositionmiddle', 'format_grid'),
                            3 => new lang_string('sectiontitleboxinsidepositionbottom', 'format_grid')
                        )
                    ),
                    'help' => 'sectiontitleboxposition',
                    'help_component' => 'format_grid'
                );
                $courseformatoptionsedit['showsectiontitlesummary'] = array(
                    'label' => new lang_string('showsectiontitlesummary', 'format_grid'),
                    'element_type' => 'select',
                    'element_attributes' => array(
                        array(
                            1 => new lang_string('no'), // No.
                            2 => new lang_string('yes') // Yes.
                        )
                    ),
                    'help' => 'showsectiontitlesummary',
                    'help_component' => 'format_grid'
                );
                $courseformatoptionsedit['setshowsectiontitlesummaryposition'] = array(
                    'label' => new lang_string('setshowsectiontitlesummaryposition', 'format_grid'),
                    'element_type' => 'select',
                    'element_attributes' => array(
                        array(
                            1 => new lang_string('top', 'format_grid'),
                            2 => new lang_string('bottom', 'format_grid'),
                            3 => new lang_string('left', 'format_grid'),
                            4 => new lang_string('right', 'format_grid')
                        )
                    ),
                    'help' => 'setshowsectiontitlesummaryposition',
                    'help_component' => 'format_grid'
                );
                $courseformatoptionsedit['sectiontitleinsidetitletextcolour'] = array(
                    'label' => new lang_string('sectiontitleinsidetitletextcolour', 'format_grid'),
                    'help' => 'sectiontitleinsidetitletextcolour',
                    'help_component' => 'format_grid',
                    'element_type' => 'gfcolourpopup',
                    'element_attributes' => array(
                        array('tabindex' => -1, 'value' => get_config('format_grid', 'defaultsectiontitleinsidetitletextcolour'))
                    )
                );
                $courseformatoptionsedit['sectiontitleinsidetitlebackgroundcolour'] = array(
                    'label' => new lang_string('sectiontitleinsidetitlebackgroundcolour', 'format_grid'),
                    'help' => 'sectiontitleinsidetitlebackgroundcolour',
                    'help_component' => 'format_grid',
                    'element_type' => 'gfcolourpopup',
                    'element_attributes' => array(
                        array('tabindex' => -1, 'value' => get_config('format_grid', 'defaultsectiontitleinsidetitlebackgroundcolour'))
                    )
                );
            } else {
                $courseformatoptionsedit['hidesectiontitle'] = array('label' => get_config('format_grid', 'defaulthidesectiontitle'),
                    'element_type' => 'hidden');
                $courseformatoptionsedit['sectiontitlegridlengthmaxoption'] = array('label' => get_config('format_grid', 'defaultsectiontitlegridlengthmaxoption'),
                    'element_type' => 'hidden');
                $courseformatoptionsedit['sectiontitleboxposition'] = array('label' => get_config('format_grid', 'defaultsectiontitleboxposition'),
                    'element_type' => 'hidden');
                $courseformatoptionsedit['sectiontitleboxinsideposition'] = array(
                    'label' => get_config('format_grid', 'defaultsectiontitleboxinsideposition'), 'element_type' => 'hidden');
                $courseformatoptionsedit['showsectiontitlesummary'] = array(
                    'label' => get_config('format_grid', 'defaultshowsectiontitlesummary'), 'element_type' => 'hidden');
                $courseformatoptionsedit['setshowsectiontitlesummaryposition'] = array(
                    'label' => get_config('format_grid', 'defaultsetshowsectiontitlesummaryposition'), 'element_type' => 'hidden');
                $courseformatoptionsedit['sectiontitleinsidetitletextcolour'] = array(
                    'label' => $defaults['defaultsectiontitleinsidetitletextcolour'], 'element_type' => 'hidden');
                $courseformatoptionsedit['sectiontitleinsidetitlebackgroundcolour'] = array(
                    'label' => $defaults['defaultsectiontitleinsidetitlebackgroundcolour'], 'element_type' => 'hidden');
            }

            $courseformatoptionsedit['newactivity'] = array(
                'label' => new lang_string('setnewactivity', 'format_grid'),
                'element_type' => 'select',
                'element_attributes' => array(
                    array(
                        1 => new lang_string('no'), // No.
                        2 => new lang_string('yes')   // Yes.
                    )
                ),
                'help' => 'setnewactivity',
                'help_component' => 'format_grid'
            );

            $courseformatoptionsedit['fitsectioncontainertowindow'] = array(
                'label' => new lang_string('setfitsectioncontainertowindow', 'format_grid'),
                'help' => 'setfitsectioncontainertowindow',
                'help_component' => 'format_grid',
                'element_type' => 'select',
                'element_attributes' => array(
                    array(
                        1 => new lang_string('no'), // No.
                        2 => new lang_string('yes')   // Yes.
                    )
                )
            );

            $courseformatoptionsedit['greyouthidden'] = array(
                'label' => new lang_string('greyouthidden', 'format_grid'),
                'help' => 'greyouthidden',
                'help_component' => 'format_grid',
                'element_type' => 'select',
                'element_attributes' => array(
                    array(
                        1 => new lang_string('no'), // No.
                        2 => new lang_string('yes')   // Yes.
                    )
                )
            );

            $courseformatoptions = array_merge_recursive($courseformatoptions, $courseformatoptionsedit);
        }
        return $courseformatoptions;
    }

    protected function get_course_format_colour_defaults() {
        $defaults = array();
        $defaults['defaultbordercolour'] = get_config('format_grid', 'defaultbordercolour');
        if ($defaults['defaultbordercolour'][0] == '#') {
            $defaults['defaultbordercolour'] = substr($defaults['defaultbordercolour'][0], 1);
        }
        $defaults['defaultimagecontainerbackgroundcolour'] = get_config('format_grid', 'defaultimagecontainerbackgroundcolour');
        if ($defaults['defaultimagecontainerbackgroundcolour'][0] == '#') {
            $defaults['defaultimagecontainerbackgroundcolour'] = substr($defaults['defaultimagecontainerbackgroundcolour'], 1);
        }
        $defaults['defaultcurrentselectedsectioncolour'] = get_config('format_grid', 'defaultcurrentselectedsectioncolour');
        if ($defaults['defaultcurrentselectedsectioncolour'][0] == '#') {
            $defaults['defaultcurrentselectedsectioncolour'] = substr($defaults['defaultcurrentselectedsectioncolour'], 1);
        }
        $defaults['defaultcurrentselectedimagecontainertextcolour'] = get_config('format_grid', 'defaultcurrentselectedimagecontainertextcolour');
        if ($defaults['defaultcurrentselectedimagecontainertextcolour'][0] == '#') {
            $defaults['defaultcurrentselectedimagecontainertextcolour'] = substr($defaults['defaultcurrentselectedimagecontainertextcolour'], 1);
        }
        $defaults['defaultcurrentselectedimagecontainercolour'] = get_config('format_grid', 'defaultcurrentselectedimagecontainercolour');
        if ($defaults['defaultcurrentselectedimagecontainercolour'][0] == '#') {
            $defaults['defaultcurrentselectedimagecontainercolour'] = substr($defaults['defaultcurrentselectedimagecontainercolour'], 1);
        }
        $defaults['defaultsectiontitleinsidetitletextcolour'] = get_config('format_grid', 'defaultsectiontitleinsidetitletextcolour');
        if ($defaults['defaultsectiontitleinsidetitletextcolour'][0] == '#') {
            $defaults['defaultsectiontitleinsidetitletextcolour'] = substr($defaults['defaultsectiontitleinsidetitletextcolour'], 1);
        }
        $defaults['defaultsectiontitleinsidetitlebackgroundcolour'] = get_config('format_grid', 'defaultsectiontitleinsidetitlebackgroundcolour');
        if ($defaults['defaultsectiontitleinsidetitlebackgroundcolour'][0] == '#') {
            $defaults['defaultsectiontitleinsidetitlebackgroundcolour'] = substr($defaults['defaultsectiontitleinsidetitlebackgroundcolour'], 1);
        }
        return $defaults;
    }

    /**
     * Adds format options elements to the course/section edit form.
     *
     * This function is called from {@link course_edit_form::definition_after_data()}.
     *
     * @param MoodleQuickForm $mform form the elements are added to.
     * @param bool $forsection 'true' if this is a section edit form, 'false' if this is course edit form.
     * @return array array of references to the added form elements.
     */
    public function create_edit_form_elements(&$mform, $forsection = false) {
        global $CFG, $OUTPUT;
        MoodleQuickForm::registerElementType('gfcolourpopup', "$CFG->dirroot/course/format/grid/js/gf_colourpopup.php",
                'MoodleQuickForm_gfcolourpopup');

        $elements = parent::create_edit_form_elements($mform, $forsection);
        if ($forsection == false) {
            global $USER;
            /*
              Increase the number of sections combo box values if the user has increased the number of sections
              using the imagecontainer on the course page beyond course 'maxsections' or course 'maxsections' has been
              reduced below the number of sections already set for the course on the site administration course
              defaults page.  This is so that the number of sections is not reduced leaving unintended orphaned
              activities / resources.
             */
            $maxsections = get_config('moodlecourse', 'maxsections');
            $numsections = $mform->getElementValue('numsections');
            $numsections = $numsections[0];
            if ($numsections > $maxsections) {
                $element = $mform->getElement('numsections');
                for ($i = $maxsections + 1; $i <= $numsections; $i++) {
                    $element->addOption("$i", $i);
                }
            }

            $coursecontext = context_course::instance($this->courseid);

            $changeimagecontainersize = has_capability('format/grid:changeimagecontainersize', $coursecontext);
            $changeimageresizemethod = has_capability('format/grid:changeimageresizemethod', $coursecontext);
            $changeimagecontainerstyle = has_capability('format/grid:changeimagecontainerstyle', $coursecontext);
            $changesectiontitleoptions = has_capability('format/grid:changesectiontitleoptions', $coursecontext);
            $resetall = is_siteadmin($USER); // Site admins only.

            $elements[] = $mform->addElement('header', 'gfreset', get_string('gfreset', 'format_grid'));
            $mform->addHelpButton('gfreset', 'gfreset', 'format_grid', '', true);

            $resetelements = array();

            if (($changeimagecontainersize) ||
               ($changeimageresizemethod) ||
               ($changeimagecontainerstyle) ||
               ($changesectiontitleoptions)) {

                if ($changeimagecontainersize) {
                    $checkboxname = get_string('resetimagecontainersize', 'format_grid') .
                            $OUTPUT->help_icon('resetimagecontainersize', 'format_grid');
                    $resetelements[] = & $mform->createElement('checkbox', 'resetimagecontainersize', '', $checkboxname);
                }

                if ($changeimageresizemethod) {
                    $checkboxname = get_string('resetimageresizemethod', 'format_grid') .
                            $OUTPUT->help_icon('resetimageresizemethod', 'format_grid');
                    $resetelements[] = & $mform->createElement('checkbox', 'resetimageresizemethod', '', $checkboxname);
                }

                if ($changeimagecontainerstyle) {
                    $checkboxname = get_string('resetimagecontainerstyle', 'format_grid') .
                            $OUTPUT->help_icon('resetimagecontainerstyle', 'format_grid');
                    $resetelements[] = & $mform->createElement('checkbox', 'resetimagecontainerstyle', '', $checkboxname);
                }

                if ($changesectiontitleoptions) {
                    $checkboxname = get_string('resetsectiontitleoptions', 'format_grid') .
                            $OUTPUT->help_icon('resetsectiontitleoptions', 'format_grid');
                    $resetelements[] = & $mform->createElement('checkbox', 'resetsectiontitleoptions', '', $checkboxname);
                }
            }

            $checkboxname = get_string('resetnewactivity', 'format_grid') .
                    $OUTPUT->help_icon('resetnewactivity', 'format_grid');
            $resetelements[] = & $mform->createElement('checkbox', 'resetnewactivity', '', $checkboxname);

            $checkboxname = get_string('resetfitpopup', 'format_grid') .
                    $OUTPUT->help_icon('resetfitpopup', 'format_grid');
            $resetelements[] = & $mform->createElement('checkbox', 'resetfitpopup', '', $checkboxname);

            $elements[] = $mform->addGroup($resetelements, 'resetgroup', get_string('resetgrp', 'format_grid'), null, false);

            if ($resetall) {
                $resetallelements = array();

                $checkboxname = get_string('resetallimagecontainersize', 'format_grid') .
                        $OUTPUT->help_icon('resetallimagecontainersize', 'format_grid');
                $resetallelements[] = & $mform->createElement('checkbox', 'resetallimagecontainersize', '', $checkboxname);

                $checkboxname = get_string('resetallimageresizemethod', 'format_grid') .
                        $OUTPUT->help_icon('resetallimageresizemethod', 'format_grid');
                $resetallelements[] = & $mform->createElement('checkbox', 'resetallimageresizemethod', '', $checkboxname);

                $checkboxname = get_string('resetallimagecontainerstyle', 'format_grid') .
                        $OUTPUT->help_icon('resetallimagecontainerstyle', 'format_grid');
                $resetallelements[] = & $mform->createElement('checkbox', 'resetallimagecontainerstyle', '', $checkboxname);

                $checkboxname = get_string('resetallsectiontitleoptions', 'format_grid') .
                        $OUTPUT->help_icon('resetallsectiontitleoptions', 'format_grid');
                $resetallelements[] = & $mform->createElement('checkbox', 'resetallsectiontitleoptions', '', $checkboxname);

                $checkboxname = get_string('resetallnewactivity', 'format_grid') .
                        $OUTPUT->help_icon('resetallnewactivity', 'format_grid');
                $resetallelements[] = & $mform->createElement('checkbox', 'resetallnewactivity', '', $checkboxname);

                $checkboxname = get_string('resetallfitpopup', 'format_grid') .
                        $OUTPUT->help_icon('resetallfitpopup', 'format_grid');
                $resetallelements[] = & $mform->createElement('checkbox', 'resetallfitpopup', '', $checkboxname);

                $elements[] = $mform->addGroup($resetallelements, 'resetallgroup', get_string('resetallgrp', 'format_grid'), null,
                        false);
            }
        }

        return $elements;
    }

    /**
     * Override if you need to perform some extra validation of the format options
     *
     * @param array $data array of ("fieldname"=>value) of submitted data
     * @param array $files array of uploaded files "element_name"=>tmp_file_path
     * @param array $errors errors already discovered in edit form validation
     * @return array of "element_name"=>"error_description" if there are errors,
     *         or an empty array if everything is OK.
     *         Do not repeat errors from $errors param here
     */
    public function edit_form_validation($data, $files, $errors) {
        $retr = array();

        if ($this->validate_colour($data['bordercolour']) === false) {
            $retr['bordercolour'] = get_string('colourrule', 'format_grid');
        }
        if ($this->validate_colour($data['imagecontainerbackgroundcolour']) === false) {
            $retr['imagecontainerbackgroundcolour'] = get_string('colourrule', 'format_grid');
        }
        if ($this->validate_colour($data['currentselectedsectioncolour']) === false) {
            $retr['currentselectedsectioncolour'] = get_string('colourrule', 'format_grid');
        }
        if ($this->validate_colour($data['currentselectedimagecontainercolour']) === false) {
            $retr['currentselectedimagecontainercolour'] = get_string('colourrule', 'format_grid');
        }
        if ($data['sectiontitlegridlengthmaxoption'] < 0) {
            $retr['sectiontitlegridlengthmaxoption'] = get_string('sectiontitlegridlengthmaxoptionrule', 'format_grid');
        }
        if ($this->validate_colour($data['sectiontitleinsidetitletextcolour']) === false) {
            $retr['sectiontitleinsidetitletextcolour'] = get_string('colourrule', 'format_grid');
        }
        if ($this->validate_colour($data['sectiontitleinsidetitlebackgroundcolour']) === false) {
            $retr['sectiontitleinsidetitlebackgroundcolour'] = get_string('colourrule', 'format_grid');
        }
        return $retr;
    }

    /**
     * Validates the colour that was entered by the user.
     * Borrowed from 'admin_setting_configcolourpicker' in '/lib/adminlib.php'.
     *
     * I'm not completely happy with this solution as would rather embed in the colour
     * picker code in the form, however I find this area rather fraut and I hear that
     * Dan Poltawski (via MDL-42270) will be re-writing the forms lib so hopefully more
     * developer friendly.
     *
     * Note: Colour names removed, but might consider putting them back in if asked, but
     *       at the moment that would require quite a few changes and coping with existing
     *       settings.  Either convert the names to hex or allow them as valid values and
     *       fix the colour picker code and the CSS code in 'format.php' for the setting.
     *
     * Colour name to hex on: http://www.w3schools.com/cssref/css_colornames.asp.
     *
     * @param string $data the colour string to validate.
     * @return true|false
     */
    private function validate_colour($data) {
        if (preg_match('/^#?([[:xdigit:]]{3}){1,2}$/', $data)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Updates format options for a course
     *
     * In case if course format was changed to 'Grid', we try to copy options
     * 'coursedisplay', 'numsections' and 'hiddensections' from the previous format.
     * If previous course format did not have 'numsections' option, we populate it with the
     * current number of sections.  The layout and colour defaults will come from 'course_format_options'.
     *
     * @param stdClass|array $data return value from {@link moodleform::get_data()} or array with data.
     * @param stdClass $oldcourse if this function is called from {@link update_course()}
     *     this object contains information about the course before update.
     * @return bool whether there were any changes to the options values.
     */
    public function update_course_format_options($data, $oldcourse = null) {
        global $DB; // MDL-37976.
        /*
         * Notes: Using 'unset' to really ensure that the reset form elements never get into the database.
         *        This has to be done here so that the reset occurs after we have done updates such that the
         *        reset itself is not seen as an update.
         */
        $resetimagecontainersize = false;
        $resetimageresizemethod = false;
        $resetimagecontainerstyle = false;
        $resetsectiontitleoptions = false;
        $resetnewactivity = false;
        $resetfitpopup = false;
        $resetallimagecontainersize = false;
        $resetallimageresizemethod = false;
        $resetallimagecontainerstyle = false;
        $resetallsectiontitleoptions = false;
        $resetallnewactivity = false;
        $resetallfitpopup = false;
        $resetgreyouthidden = false;
        if (isset($data->resetimagecontainersize) == true) {
            $resetimagecontainersize = true;
            unset($data->resetimagecontainersize);
        }
        if (isset($data->resetimageresizemethod) == true) {
            $resetimageresizemethod = true;
            unset($data->resetimageresizemethod);
        }
        if (isset($data->resetimagecontainerstyle) == true) {
            $resetimagecontainerstyle = true;
            unset($data->resetimagecontainerstyle);
        }
        if (isset($data->resetsectiontitleoptions) == true) {
            $resetsectiontitleoptions = true;
            unset($data->resetsectiontitleoptions);
        }
        if (isset($data->resetnewactivity) == true) {
            $resetnewactivity = true;
            unset($data->resetnewactivity);
        }
        if (isset($data->resetfitpopup) == true) {
            $resetfitpopup = true;
            unset($data->resetfitpopup);
        }
        if (isset($data->resetallimagecontainersize) == true) {
            $resetallimagecontainersize = true;
            unset($data->resetallimagecontainersize);
        }
        if (isset($data->resetallimageresizemethod) == true) {
            $resetallimageresizemethod = true;
            unset($data->resetallimageresizemethod);
        }
        if (isset($data->resetallimagecontainerstyle) == true) {
            $resetallimagecontainerstyle = true;
            unset($data->resetallimagecontainerstyle);
        }
        if (isset($data->resetallsectiontitleoptions) == true) {
            $resetallsectiontitleoptions = true;
            unset($data->resetallsectiontitleoptions);
        }
        if (isset($data->resetallnewactivity) == true) {
            $resetallnewactivity = true;
            unset($data->resetallnewactivity);
        }
        if (isset($data->resetallfitpopup) == true) {
            $resetfitpopup = true;
            unset($data->resetallfitpopup);
        }
        if (isset($data->resetgreyouthidden) == true) {
            $resetgreyouthidden = true;
            unset($data->resetgreyouthidden);
        }

        $settings = $this->get_settings();
        $changedisplayedimages = false;
        if (isset($data->imagecontainerwidth)) {
            // We are have the CONTRIB-4099 options and this is not from a pre-CONTRIB-4099 backup file.
            if (((!(($resetimagecontainersize) || ($resetallimagecontainersize))) &&
                    (($settings['imagecontainerwidth'] != $data->imagecontainerwidth) ||
                     ($settings['imagecontainerratio'] != $data->imagecontainerratio))) ||
                    ((!(($resetimageresizemethod) || ($resetallimageresizemethod))) &&
                    ($settings['imageresizemethod'] != $data->imageresizemethod))) {
                /* Detect now and action later as 'setup_displayed_image' when called from 'update_displayed_images()' will need to
                  use the new values. */
                $changedisplayedimages = true;
            }
        }

        $data = (array) $data;
        if ($oldcourse !== null) {
            $oldcourse = (array) $oldcourse;
            $options = $this->course_format_options();

            foreach ($options as $key => $unused) {
                if (!array_key_exists($key, $data)) {
                    if (array_key_exists($key, $oldcourse)) {
                        $data[$key] = $oldcourse[$key];
                    } else if ($key === 'numsections') {
                        /* If previous format does not have the field 'numsections'
                          and $data['numsections'] is not set, we fill it with the
                          maximum section number from the DB. */
                        $maxsection = $DB->get_field_sql(
                                'SELECT max(section) from {course_sections} WHERE course = ?', array($this->courseid)
                        );
                        if ($maxsection) {
                            // If there are no sections, or just default 0-section, 'numsections' will be set to default.
                            $data['numsections'] = $maxsection;
                        }
                    }
                }
            }
        }
        $changes = $this->update_format_options($data);

        if ($changes && array_key_exists('numsections', $data)) {
            // If the numsections was decreased, try to completely delete the orphaned sections (unless they are not empty).
            $numsections = (int) $data['numsections'];
            $maxsection = $DB->get_field_sql('SELECT max(section) from {course_sections}
                        WHERE course = ?', array($this->courseid));
            for ($sectionnum = $maxsection; $sectionnum > $numsections; $sectionnum--) {
                if (!$this->delete_section($sectionnum, false)) {
                    break;
                }
            }
        }

        // Now we can change the displayed images if needed.
        if ($changedisplayedimages) {
            $this->settings = null; // Invalidate as changed.
            $settings = $this->get_settings();

            $this->update_displayed_images($this->courseid, $this, $settings, true);
        }

        // Now we can do the reset.
        if (($resetallimagecontainersize) ||
            ($resetallimageresizemethod) ||
            ($resetallimagecontainerstyle) ||
            ($resetallsectiontitleoptions) ||
            ($resetallnewactivity) ||
            ($resetallfitpopup)) {
            $this->reset_grid_setting(0, $resetallimagecontainersize, $resetallimageresizemethod, $resetallimagecontainerstyle,
                    $resetallsectiontitleoptions, $resetallnewactivity, $resetallfitpopup);
            $changes = true;
        } else if (
            ($resetimagecontainersize) ||
            ($resetimageresizemethod) ||
            ($resetimagecontainerstyle) ||
            ($resetsectiontitleoptions) ||
            ($resetnewactivity) ||
            ($resetfitpopup)) {
            $this->reset_grid_setting($this->courseid, $resetimagecontainersize, $resetimageresizemethod, $resetimagecontainerstyle,
                    $resetsectiontitleoptions, $resetnewactivity, $resetfitpopup);
            $changes = true;
        }

        return $changes;
    }

    /**
     * Deletes a section
     *
     * Do not call this function directly, instead call {@link course_delete_section()}
     *
     * @param int|stdClass|section_info $section
     * @param bool $forcedeleteifnotempty if set to false section will not be deleted if it has modules in it.
     * @return bool whether section was deleted
     */
    public function delete_section($section, $forcedeleteifnotempty = false) {
        if (!$this->uses_sections()) {
            // Not possible to delete section if sections are not used.
            return false;
        }
        if (!is_object($section)) {
            global $DB;
            $section = $DB->get_record('course_sections', array('course' => $this->get_courseid(), 'section' => $section),
                'id,section,sequence,summary');
        }
        if (!$section || !$section->section) {
            // Not possible to delete 0-section.
            return false;
        }

        if (!$forcedeleteifnotempty && (!empty($section->sequence) || !empty($section->summary))) {
            return false;
        }
        if (parent::delete_section($section, $forcedeleteifnotempty)) {
            $coursecontext = context_course::instance($this->courseid);
            $this->delete_image($section->id, $coursecontext->id);
            return true;
        }
        return false;
    }

    /**
     * Whether this format allows to delete sections
     *
     * Do not call this function directly, instead use {@link course_can_delete_section()}
     *
     * @param int|stdClass|section_info $section
     * @return bool
     */
    public function can_delete_section($section) {
        return true;
    }

    /**
     * Updates format options for a section
     *
     * Section id is expected in $data->id (or $data['id'])
     * If $data does not contain property with the option name, the option will not be updated
     *
     * @param stdClass|array $data return value from {@link moodleform::get_data()} or array with data
     * @return bool whether there were any changes to the options values
     */
    public function update_section_format_options($data) {
        $data = (array) $data;

        // Resets the displayed image because changing the section name / details deletes the file.
        // See CONTRIB-4784.
        global $DB;
        $DB->set_field('format_grid_icon', 'displayedimageindex', 0, array('sectionid' => $data['id']));

        return parent::update_section_format_options($data);
    }

    /**
     * Resets the format setting to the default.
     * @param int $courseid If not 0, then a specific course to reset.
     * @param int $imagecontainersizereset If true, reset the layout to the default in the settings for the format.
     * @param int $imageresizemethodreset If true, reset the image resize method to the default in the settings for the format.
     * @param int $imagecontainerstylereset If true, reset the colour to the default in the settings for the format.
     * @param int $sectiontitleoptionsreset If true, reset the section title options to the default in the settings for the format.
     * @param int $newactivityreset If true, reset the new activity to the default in the settings for the format.
     * @param int $fitpopupreset If true, reset the fit popup to the default in the settings for the format.
     */
    public function reset_grid_setting($courseid, $imagecontainersizereset, $imageresizemethodreset, $imagecontainerstylereset,
            $sectiontitleoptionsreset, $newactivityreset, $fitpopupreset) {
        global $DB, $USER;

        $coursecontext = context_course::instance($this->courseid);

        if ($courseid == 0) {
            $records = $DB->get_records('course', array('format' => $this->format), '', 'id');
        } else {
            $records = $DB->get_records('course', array('id' => $courseid, 'format' => $this->format), '', 'id');
        }

        $resetallifall = ((is_siteadmin($USER)) || ($courseid != 0)); // Will be true if reset all capability or a single course.

        $updatedata = array();
        $updateimagecontainersize = false;
        $updateimageresizemethod = false;
        $updateimagecontainerstyle = false;
        $updatesectiontitleoptions = false;
        $updatenewactivity = false;
        $updatefitpopup = false;
        if ($imagecontainersizereset && has_capability('format/grid:changeimagecontainersize', $coursecontext) && $resetallifall) {
            $updatedata['imagecontainerwidth'] = get_config('format_grid', 'defaultimagecontainerwidth');
            $updatedata['imagecontainerratio'] = get_config('format_grid', 'defaultimagecontainerratio');
            $updateimagecontainersize = true;
        }
        if ($imageresizemethodreset && has_capability('format/grid:changeimageresizemethod', $coursecontext) && $resetallifall) {
            $updatedata['imageresizemethod'] = get_config('format_grid', 'defaultimageresizemethod');
            $updateimageresizemethod = true;
        }
        if ($imagecontainerstylereset && has_capability('format/grid:changeimagecontainerstyle', $coursecontext) && $resetallifall) {
            $updatedata['bordercolour'] = get_config('format_grid', 'defaultbordercolour');
            $updatedata['borderwidth'] = get_config('format_grid', 'defaultborderwidth');
            $updatedata['borderradius'] = get_config('format_grid', 'defaultborderradius');
            $updatedata['imagecontainerbackgroundcolour'] = get_config('format_grid', 'defaultimagecontainerbackgroundcolour');
            $updatedata['currentselectedsectioncolour'] = get_config('format_grid', 'defaultcurrentselectedsectioncolour');
            $updatedata['currentselectedimagecontainertextcolour'] = get_config('format_grid',
                    'defaultcurrentselectedimagecontainertextcolour');
            $updatedata['currentselectedimagecontainercolour'] = get_config('format_grid',
                    'defaultcurrentselectedimagecontainercolour');
            $updateimagecontainerstyle = true;
        }
        if ($sectiontitleoptionsreset && has_capability('format/grid:changesectiontitleoptions', $coursecontext) && $resetallifall) {
            $updatedata['hidesectiontitle'] = get_config('format_grid', 'defaulthidesectiontitle');
            $updatedata['sectiontitlegridlengthmaxoption'] = get_config('format_grid', 'defaultsectiontitlegridlengthmaxoption');
            $updatedata['sectiontitleboxposition'] = get_config('format_grid', 'defaultsectiontitleboxposition');
            $updatedata['sectiontitleboxinsideposition'] = get_config('format_grid', 'defaultsectiontitleboxinsideposition');
            $updatedata['showsectiontitlesummary'] = get_config('format_grid', 'defaultshowsectiontitlesummary');
            $updatedata['setshowsectiontitlesummaryposition'] = get_config('format_grid', 'defaultsetshowsectiontitlesummaryposition');
            $updatedata['sectiontitleinsidetitletextcolour'] = get_config('format_grid', 'defaultsectiontitleinsidetitletextcolour');
            $updatedata['sectiontitleinsidetitlebackgroundcolour'] = get_config('format_grid', 'defaultsectiontitleinsidetitlebackgroundcolour');
            $updatesectiontitleoptions = true;
        }
        if ($newactivityreset && $resetallifall) {
            $updatedata['newactivity'] = get_config('format_grid', 'defaultnewactivity');
            $updatenewactivity = true;
        }
        if ($fitpopupreset && $resetallifall) {
            $updatedata['fitsectioncontainertowindow'] = get_config('format_grid', 'defaultfitsectioncontainertowindow');
            $updatefitpopup = true;
        }

        foreach ($records as $record) {
            if (($updateimagecontainersize) ||
                ($updateimageresizemethod) ||
                ($updateimagecontainerstyle) ||
                ($updatesectiontitleoptions) ||
                ($updatenewactivity) ||
                ($updatefitpopup)) {
                $ourcourseid = $this->courseid;
                $this->courseid = $record->id;
                if (($updateimagecontainersize) || ($updateimageresizemethod)) {
                    $courseformat = null;
                    if ($ourcourseid !== $this->courseid) {
                        $courseformat = course_get_format($this->courseid);
                        $currentsettings = $courseformat->get_settings();
                    } else {
                        $currentsettings = $this->get_settings();
                        $courseformat = $this;
                    }

                    if (($updateimagecontainersize) &&
                            (($currentsettings['imagecontainerwidth'] != $updatedata['imagecontainerwidth']) ||
                            ($currentsettings['imagecontainerratio'] != $updatedata['imagecontainerratio']))) {
                        $performimagecontainersize = true; // Variable $updatedata will be correct.
                    } else {
                        // If image resize method needs to operate so use current settings.
                        $updatedata['imagecontainerwidth'] = $currentsettings['imagecontainerwidth'];
                        $updatedata['imagecontainerratio'] = $currentsettings['imagecontainerratio'];
                        $performimagecontainersize = false;
                    }

                    if (($updateimageresizemethod) &&
                            ($currentsettings['imageresizemethod'] != $updatedata['imageresizemethod'])) {
                        $performimageresizemethod = true; // Variable $updatedata will be correct.
                    } else {
                        // If image container size needs to operate so use current setting.
                        $updatedata['imageresizemethod'] = $currentsettings['imageresizemethod'];
                        $performimageresizemethod = false;
                    }

                    if (($performimagecontainersize) || ($performimageresizemethod)) {
                        // No need to get the settings as parsing the updated ones, but do need to invalidate them.
                        $courseformat->settings = null;
                        $courseformat->update_displayed_images($record->id, $courseformat, $updatedata, false);
                    }
                }
                $this->update_format_options($updatedata);
                $this->courseid = $ourcourseid;
            }
        }
    }

    // Grid specific methods...
    /**
     * Returns a moodle_url to a grid format file.
     * @param string $url The URL to append.
     * @param array $params URL parameters.
     * @return moodle_url The created URL.
     */
    public function grid_moodle_url($url, array $params = null) {
        return new moodle_url('/course/format/grid/' . $url, $params);
    }

    /**
     * Gets the grid image entries for the given course.
     * @param int $courseid The course id to use.
     * @returns bool|array The records or false if the course id is 0 or the request failed.
     */
    public function get_images($courseid) {
        global $DB;

        if (!$courseid) {
            return false;
        }

        if (!$sectionimagecontainers = $DB->get_records('format_grid_icon', array('courseid' => $courseid), '',
                'sectionid, image, displayedimageindex')) {
            $sectionimagecontainers = false;
        }
        return $sectionimagecontainers;
    }

    /**
     * Gets the grid image entry for the given course and section.  If an entry cannot be found then one is created
     * and returned.  If the course id is set to the default then it is updated to the one supplied as the value
     * will be accurate.
     * @param int $courseid The course id to use.
     * @param int $sectionid The section id to use.
     * @returns bool|array The record or false if the course id is 0 or section id is 0 or the request failed.
     */
    public function get_image($courseid, $sectionid) {
        global $DB;

        if ((!$courseid) || (!$sectionid)) {
            return false;
        }

        if (!$sectionimage = $DB->get_record('format_grid_icon', array('sectionid' => $sectionid))) {

            $newimagecontainer = new stdClass();
            $newimagecontainer->sectionid = $sectionid;
            $newimagecontainer->courseid = $courseid;
            $newimagecontainer->displayedimageindex = 0;

            if (!$newimagecontainer->id = $DB->insert_record('format_grid_icon', $newimagecontainer, true)) {
                throw new moodle_exception('invalidrecordid', 'format_grid', '',
                'Could not create image container.  Grid format database is not ready.' .
                '  An admin must visit the notifications section.');
            }
            $sectionimage = $newimagecontainer;
        } else if ($sectionimage->courseid == 1) { // 1 is the default and is the 'site' course so cannot be the Grid format.
            // Note: Using a double equals in the test and not a triple as the latter does not work for some reason.
            /* Course id is the default and needs to be set correctly.  This can happen with data created by versions prior to
              13/7/2012. */
            $DB->set_field('format_grid_icon', 'courseid', $courseid, array('sectionid' => $sectionid));
            $sectionimage->courseid = $courseid;
        }
        return $sectionimage;
    }

    /**
     * Get summary visibility, if it doesn't exist create it.
     * The summary visibility is if section 0 is displayed (1) or in the grid(0).
     * @param int $courseid The course id.
     * @return stdClass The summary visibility for the course or false if not found.
     * @throws moodle_exception If cannot add a new record to the 'format_grid_summary' table.
     */
    public function get_summary_visibility($courseid) {
        global $DB;
        if (!$summarystatus = $DB->get_record('format_grid_summary', array('courseid' => $courseid))) {
            $newstatus = new stdClass();
            $newstatus->courseid = $courseid;
            $newstatus->showsummary = 1;

            if (!$newstatus->id = $DB->insert_record('format_grid_summary', $newstatus)) {
                throw new moodle_exception('invalidrecordid', 'format_grid', '',
                'Could not set summary status. Grid format database is not ready. An admin must visit the notifications section.');
            }
            $summarystatus = $newstatus;

            /* Technically this only happens once when the course is created, so we can use it to set the
             * course format options for the first time.  This so that the defaults are set upon creation
             * and therefore do not have to detect when they change in the global site settings.  Which
             * cannot be detected and therefore the icons would look odd.  So here they are set and set once
             * until course settings are reset or changed.
             */
            $this->update_course_format_options($this->get_settings());
        }
        return $summarystatus;
    }

    // CONTRIB-4099 methods:....
    /**
     * Calculates the image container properties so that it can be rendered correctly.
     * @param int $imagecontainerwidth The width of the container.
     * @param int $imagecontainerratio The ratio array index of the container, see: $imagecontainerratios.
     * @param int $borderwidth The width of the border.
     * @return array with the key => value of 'height' for the container and 'margin-top' and 'margin-left' for the new activity
     *               image.
     */
    public function calculate_image_container_properties($imagecontainerwidth, $imagecontainerratio, $borderwidth) {

        if (($imagecontainerwidth !== self::$currentwidth) || ($imagecontainerratio !== self::$currentratio) ||
                ($borderwidth !== self::$currentborderwidth)) {
            $height = $this->calculate_height($imagecontainerwidth, $imagecontainerratio);
            // This is: margin-top = image holder height - ( image height - border width)).
            // This is: margin-left = (image holder width - image width) + border width.

            $result = array(
                'height' => $height,
                'margin-top' => $height - (42 - $borderwidth),
                'margin-left' => ($imagecontainerwidth - 95) + $borderwidth
            );

            // Store:...
            self::$currentwidth = $imagecontainerwidth;
            self::$currentratio = $imagecontainerratio;
            self::$currentborderwidth = $borderwidth;
            self::$currentheight = $result['height'];
            self::$activitymargintop = $result['margin-top'];
            self::$activitymarginleft = $result['margin-left'];
        } else {
            $result = array(
                'height' => self::$currentheight,
                'margin-top' => self::$activitymargintop,
                'margin-left' => self::$activitymarginleft
            );
        }

        return $result;
    }

    /**
     * Gets the image container properties given the settings.
     * @param array $settings Must have integer keys for 'imagecontainerwidth' and 'imagecontainerratio'.
     * @return array with the key => value of 'height' and 'width' for the container.
     */
    private function get_displayed_image_container_properties($settings) {
        return array('height' => $this->calculate_height($settings['imagecontainerwidth'], $settings['imagecontainerratio']),
            'width' => $settings['imagecontainerwidth']);
    }

    /**
     * Calculates height given the width and ratio.
     * @param int $width.
     * @param int $ratio.
     * @return int Height.
     */
    private function calculate_height($width, $ratio) {
        $basewidth = $width;

        switch ($ratio) {
            // Ratios 1 => '3-2', 2 => '3-1', 3 => '3-3', 4 => '2-3', 5 => '1-3', 6 => '4-3', 7 => '3-4'.
            case 1: // 3-2.
            case 2: // 3-1.
            case 3: // 3-3.
            case 7: // 3-4.
                $basewidth = $width / 3;
                break;
            case 4: // 2-3.
                $basewidth = $width / 1;
                break;
            case 5: // 1-3.
                $basewidth = $width;
                break;
            case 6: // 4-3.
                $basewidth = $width / 4;
                break;
        }

        $height = $basewidth;
        switch ($ratio) {
            // Ratios 1 => '3-2', 2 => '3-1', 3 => '3-3', 4 => '2-3', 5 => '1-3', 6 => '4-3', 7 => '3-4'.
            case 2: // 3-1.
                $height = $basewidth;
                break;
            case 1: // 3-2.
                $height = $basewidth * 2;
                break;
            case 3: // 3-3.
            case 4: // 2-3.
            case 5: // 1-3.
            case 6: // 4-3.
                $height = $basewidth * 3;
                break;
            case 7: // 3-4.
                $height = $basewidth * 4;
                break;
        }

        return round($height);
    }

    public function create_original_image_record($contextid, $sectionid, $filename) {
        $created = time();
        $storedfilerecord = array(
            'contextid' => $contextid,
            'component' => 'course',
            'filearea' => 'section',
            'itemid' => $sectionid,
            'filepath' => '/',
            // CONTRIB-5001 - Avoid clashes with the same image in the section summary by using a different name.
            'filename' => 'goi_' . $filename, // goi = tla = grid original image.
            'timecreated' => $created,
            'timemodified' => $created);

        return $storedfilerecord;
    }

    public function create_section_image($tempfile, $storedfilerecord, $sectionimage) {
        global $DB, $CFG;
        require_once($CFG->libdir . '/gdlib.php');

        $fs = get_file_storage();

        try {
            $convertsuccess = true;

            // Ensure the right quality setting...
            $mime = $tempfile->get_mimetype();

            $imageinfo = $tempfile->get_imageinfo();
            $imagemaxwidth = self::get_maximum_image_width();
            if ($imageinfo['width'] > $imagemaxwidth) { // Maximum width as defined in lib.php.
                // Recalculate height:...
                $ratio = $imagemaxwidth / $imageinfo['width'];
                $imageinfo['height'] = $imageinfo['height'] * $ratio; // Maintain image's aspect ratio.
                // Set width:...
                $imageinfo['width'] = $imagemaxwidth;
            }

            $storedfilerecord['mimetype'] = $mime;

            // Note: It appears that this works with transparent Gif's to, so simplifying.
            $tmproot = make_temp_directory('gridformaticon');
            $tmpfilepath = $tmproot . '/' . $tempfile->get_contenthash();
            $tempfile->copy_content_to($tmpfilepath);

            $data = generate_image_thumbnail($tmpfilepath, $imageinfo['width'], $imageinfo['height']);
            if (!empty($data)) {
                $fs->create_file_from_string($storedfilerecord, $data);
            } else {
                $convertsuccess = false;
            }
            unlink($tmpfilepath);

            $tempfile->delete();
            unset($tempfile);

            if ($convertsuccess == true) {
                $DB->set_field('format_grid_icon', 'image', $storedfilerecord['filename'],
                        array('sectionid' => $storedfilerecord['itemid']));

                // Set up the displayed image:...
                $sectionimage->newimage = $storedfilerecord['filename'];
                $this->setup_displayed_image($sectionimage, $storedfilerecord['contextid'], $this->get_settings());
            } else {
                print_error('imagecannotbeused', 'format_grid', $CFG->wwwroot . "/course/view.php?id=" . $this->courseid);
            }
        } catch (Exception $e) {
            if (isset($tempfile)) {
                $tempfile->delete();
                unset($tempfile);
            }
            print('Grid Format Image Exception:...');
            debugging($e->getMessage());
        }
    }

    /**
     * Set up the displayed image.
     * @param array $sectionimage Section information from its row in the 'format_grid_icon' table.
     * @param array $contextid The context id to which the image relates.
     * @param array $settings The course settings to apply.
     * @return array The updated $sectionimage data.
     */
    public function setup_displayed_image($sectionimage, $contextid, $settings) {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/repository/lib.php');
        require_once($CFG->libdir . '/gdlib.php');

        // Set up the displayed image:...
        $fs = get_file_storage();
        if ($imagecontainerpathfile = $fs->get_file($contextid, 'course', 'section', $sectionimage->sectionid, '/',
                $sectionimage->newimage)) {
            $gridimagepath = $this->get_image_path();
            $convertsuccess = true;
            $mime = $imagecontainerpathfile->get_mimetype();

            // Updated image.
            $sectionimage->displayedimageindex++;
            $created = time();
            $displayedimagefilerecord = array(
                'contextid' => $contextid,
                'component' => 'course',
                'filearea' => 'section',
                'itemid' => $sectionimage->sectionid,
                'filepath' => $gridimagepath,
                'filename' => $sectionimage->displayedimageindex . '_' . $sectionimage->newimage,
                'timecreated' => $created,
                'timemodified' => $created,
                'mimetype' => $mime);

            $displayedimageinfo = $this->get_displayed_image_container_properties($settings);

            $tmproot = make_temp_directory('gridformatdisplayedimagecontainer');
            $tmpfilepath = $tmproot . '/' . $imagecontainerpathfile->get_contenthash();
            $imagecontainerpathfile->copy_content_to($tmpfilepath);

            if ($settings['imageresizemethod'] == 1) {
                $crop = false;
            } else {
                $crop = true;
            }
            $data = self::generate_image($tmpfilepath, $displayedimageinfo['width'], $displayedimageinfo['height'], $crop);
            if (!empty($data)) {
                if ($fs->file_exists($displayedimagefilerecord['contextid'], $displayedimagefilerecord['component'],
                                $displayedimagefilerecord['filearea'], $displayedimagefilerecord['itemid'],
                                $displayedimagefilerecord['filepath'], $displayedimagefilerecord['filename'])) {
                    /* This can happen with previous CONTRIB-4099 versions where it was possible for the backup file to
                      have the 'gridimage' files too.  Therefore without this, then 'create_file_from_string' below will
                      baulk as the file already exists.   Unfortunately has to be here as the restore mechanism restores
                      the grid format data for the database and then the files.  And the Grid code is called at the 'data'
                      stage. */
                    if ($oldfile = $fs->get_file($displayedimagefilerecord['contextid'], $displayedimagefilerecord['component'],
                            $displayedimagefilerecord['filearea'], $displayedimagefilerecord['itemid'],
                            $displayedimagefilerecord['filepath'], $displayedimagefilerecord['filename'])) {
                        // Delete old file.
                        $oldfile->delete();
                    }
                }
                $fs->create_file_from_string($displayedimagefilerecord, $data);
            } else {
                $convertsuccess = false;
            }
            unlink($tmpfilepath);

            if ($convertsuccess == true) {
                // Now safe to delete old file if it exists.
                if ($oldfile = $fs->get_file($contextid, 'course', 'section', $sectionimage->sectionid, $gridimagepath,
                        ($sectionimage->displayedimageindex - 1) . '_' . $sectionimage->image)) {
                    $oldfile->delete();
                }
                $DB->set_field('format_grid_icon', 'displayedimageindex', $sectionimage->displayedimageindex,
                        array('sectionid' => $sectionimage->sectionid));
            } else {
                print_error('cannotconvertuploadedimagetodisplayedimage', 'format_grid',
                        $CFG->wwwroot . "/course/view.php?id=" . $this->courseid);
            }
        } else {
            $DB->set_field('format_grid_icon', 'image', null, array('sectionid' => $sectionimage->sectionid));
        }

        return $sectionimage;  // So that the caller can know the new value of displayedimageindex.
    }

    public function delete_image($sectionid, $contextid) {
        $sectionimage = $this->get_image($this->courseid, $sectionid);
        if ($sectionimage) {
            global $DB;
            if (!empty($sectionimage->image)) {
                $fs = get_file_storage();

                // Delete the image.
                if ($file = $fs->get_file($contextid, 'course', 'section', $sectionid, '/', $sectionimage->image)) {
                    $file->delete();
                    $DB->set_field('format_grid_icon', 'image', null, array('sectionid' => $sectionimage->sectionid));
                    // Delete the displayed image.
                    $gridimagepath = $this->get_image_path();
                    if ($file = $fs->get_file($contextid, 'course', 'section', $sectionid, $gridimagepath,
                            $sectionimage->displayedimageindex . '_' . $sectionimage->image)) {
                        $file->delete();
                    }
                }
            }
            $DB->delete_records("format_grid_icon", array('courseid' => $this->courseid,
                'sectionid' => $sectionimage->sectionid));
        }
    }

    public function delete_images() {
        $sectionimages = $this->get_images($this->courseid);

        if (is_array($sectionimages)) {
            global $DB;
            $coursecontext = context_course::instance($this->courseid);
            $fs = get_file_storage();
            $gridimagepath = $this->get_image_path();

            foreach ($sectionimages as $sectionimage) {
                // Delete the image if there is one.
                if (!empty($sectionimage->image)) {
                    if ($file = $fs->get_file($coursecontext->id, 'course', 'section', $sectionimage->sectionid, '/',
                            $sectionimage->image)) {
                        $file->delete();
                        // Delete the displayed image.
                        if ($file = $fs->get_file($coursecontext->id, 'course', 'section', $sectionimage->sectionid, $gridimagepath,
                                $sectionimage->displayedimageindex . '_' . $sectionimage->image)) {
                            $file->delete();
                        }
                    }
                }
            }
            $DB->delete_records("format_grid_icon", array('courseid' => $this->courseid));
        }
    }

    public function delete_displayed_images() {
        $sectionimages = $this->get_images($this->courseid);

        if (is_array($sectionimages)) {
            global $DB;

            $coursecontext = context_course::instance($this->courseid);
            $fs = get_file_storage();
            $gridimagepath = $this->get_image_path();
            $t = $DB->start_delegated_transaction();

            foreach ($sectionimages as $sectionimage) {
                // Delete the displayed image.
                if ($file = $fs->get_file($coursecontext->id, 'course', 'section', $sectionimage->sectionid, $gridimagepath,
                        $sectionimage->displayedimageindex . '_' . $sectionimage->image)) {
                    $file->delete();
                    $DB->set_field('format_grid_icon', 'displayedimageindex', 0, array('sectionid' => $sectionimage->sectionid));
                }
            }
            $t->allow_commit();
        }
    }

    /**
     * Updates the displayed images because the settings have changed.
     * @param int $courseid The course id.
     * @param int $us The instance of format_grid to use.
     * @param array $settings The settings to use.
     * @param int $ignorenorecords True we should not worry about no records existing, possibly down to a restore of a course.
     */
    private function update_displayed_images($courseid, $us, $settings, $ignorenorecords) {
        global $DB;

        $sectionimages = $us->get_images($courseid);
        if (is_array($sectionimages)) {
            $coursecontext = context_course::instance($courseid);

            $t = $DB->start_delegated_transaction();
            foreach ($sectionimages as $sectionimage) {
                if ($sectionimage->displayedimageindex > 0) {
                    $sectionimage->newimage = $sectionimage->image;
                    $sectionimage = $us->setup_displayed_image($sectionimage, $coursecontext->id, $settings);
                }
            }
            $t->allow_commit();
        } else if (!$ignorenorecords) { // Only report error if it's ok not to have records.
            print_error('cannotgetimagesforcourse', 'format_grid', '', null, "update_displayed_images - Course id: " . $courseid);
        }
    }

    /**
     * Generates a thumbnail for the given image
     *
     * If the GD library has at least version 2 and PNG support is available, the returned data
     * is the content of a transparent PNG file containing the thumbnail. Otherwise, the function
     * returns contents of a JPEG file with black background containing the thumbnail.
     *
     * @param string $filepath the full path to the original image file
     * @param int $requestedwidth the width of the requested image.
     * @param int $requestedheight the height of the requested image.
     * @param bool false = scale, true = crop.
     * @return string|bool false if a problem occurs or the image data.
     */
    private static function generate_image($filepath, $requestedwidth, $requestedheight, $crop) {
        if (empty($filepath) or empty($requestedwidth) or empty($requestedheight)) {
            return false;
        }

        $imageinfo = getimagesize($filepath);

        if (empty($imageinfo)) {
            return false;
        }

        $originalwidth = $imageinfo[0];
        $originalheight = $imageinfo[1];

        if (empty($originalwidth) or empty($originalheight)) {
            return false;
        }

        $original = imagecreatefromstring(file_get_contents($filepath));

        if (function_exists('imagepng')) {
            $imagefnc = 'imagepng';
            $filters = PNG_NO_FILTER;
            $quality = 1;
        } else if (function_exists('imagejpeg')) {
            $imagefnc = 'imagejpeg';
            $filters = null;
            $quality = 90;
        } else {
            debugging('Neither JPEG nor PNG are supported at this server, please fix the system configuration' .
                    ' to have the GD PHP extension installed.');
            return false;
        }

        $width = $requestedwidth;
        $height = $requestedheight;

        // Note: Code transformed from original 'resizeAndCrop' in 'imagelib.php' in the Moodle 1.9 version.
        if ($crop) {
            $ratio = $width / $height;
            $originalratio = $originalwidth / $originalheight;
            if ($originalratio < $ratio) {
                // Change the supplied height - 'resizeToWidth'.
                $ratio = $width / $originalwidth;
                $height = $originalheight * $ratio;
                $cropheight = true;
            } else {
                // Change the supplied width - 'resizeToHeight'.
                $ratio = $height / $originalheight;
                $width = $originalwidth * $ratio;
                $cropheight = false;
            }
        }

        if (function_exists('imagecreatetruecolor')) {
            $tempimage = imagecreatetruecolor($width, $height);
            if ($imagefnc === 'imagepng') {
                imagealphablending($tempimage, false);
                imagefill($tempimage, 0, 0, imagecolorallocatealpha($tempimage, 0, 0, 0, 127));
                imagesavealpha($tempimage, true);
            }
        } else {
            $tempimage = imagecreate($width, $height);
        }

        if ($crop) {
            // First step, resize.
            imagecopybicubic($tempimage, $original, 0, 0, 0, 0, $width, $height, $originalwidth, $originalheight);
            imagedestroy($original);
            $original = $tempimage;

            // Second step, crop.
            if ($cropheight) {
                // Reset after change for resizeToWidth.
                $height = $requestedheight;
                // This is 'cropCenterHeight'.
                $width = imagesx($original);
                $srcoffset = (imagesy($original) / 2) - ($height / 2);
            } else {
                // Reset after change for resizeToHeight.
                $width = $requestedwidth;
                // This is 'cropCenterWidth'.
                $height = imagesy($original);
                $srcoffset = (imagesx($original) / 2) - ($width / 2);
            }

            if (function_exists('imagecreatetruecolor')) {
                $finalimage = imagecreatetruecolor($width, $height);
                if ($imagefnc === 'imagepng') {
                    imagealphablending($finalimage, false);
                    imagefill($finalimage, 0, 0, imagecolorallocatealpha($tempimage, 0, 0, 0, 127));
                    imagesavealpha($finalimage, true);
                }
            } else {
                $finalimage = imagecreate($width, $height);
            }

            if ($cropheight) {
                // This is 'cropCenterHeight'.
                imagecopybicubic($finalimage, $original, 0, 0, 0, $srcoffset, $width, $height, $width, $height);
            } else {
                // This is 'cropCenterWidth'.
                imagecopybicubic($finalimage, $original, 0, 0, $srcoffset, 0, $width, $height, $width, $height);
            }
        } else {
            $finalimage = $tempimage;
            $ratio = min($width / $originalwidth, $height / $originalheight);

            if ($ratio < 1) {
                $targetwidth = floor($originalwidth * $ratio);
                $targetheight = floor($originalheight * $ratio);
            } else {
                // Do not enlarge the original file if it is smaller than the requested thumbnail size.
                $targetwidth = $originalwidth;
                $targetheight = $originalheight;
            }

            $dstx = floor(($width - $targetwidth) / 2);
            $dsty = floor(($height - $targetheight) / 2);

            imagecopybicubic($finalimage, $original, $dstx, $dsty, 0, 0, $targetwidth, $targetheight, $originalwidth,
                    $originalheight);
        }

        ob_start();
        if (!$imagefnc($finalimage, null, $quality, $filters)) {
            ob_end_clean();
            return false;
        }
        $data = ob_get_clean();

        imagedestroy($original);
        imagedestroy($finalimage);

        return $data;
    }

    /**
     * Returns a new instance of us so that specialised methods can be called.
     * @param int $courseid The course id of the course.
     * @return format_grid object.
     */
    public static function get_instance($courseid) {
        return new format_grid('grid', $courseid);
    }

    /**
     * Prepares the templateable object to display section name.
     *
     * @param \section_info|\stdClass $section
     * @param bool $linkifneeded
     * @param bool $editable
     * @param null|lang_string|string $edithint
     * @param null|lang_string|string $editlabel
     * @return \core\output\inplace_editable
     */
    public function inplace_editable_render_section_name($section, $linkifneeded = true, $editable = null, $edithint = null,
            $editlabel = null) {
        if (empty($edithint)) {
            $edithint = new lang_string('editsectionname', 'format_grid');
        }
        if (empty($editlabel)) {
            $title = $this->get_section_name($section);
            $editlabel = new lang_string('newsectionname', 'format_grid', $title);
        }
        return parent::inplace_editable_render_section_name($section, $linkifneeded, $editable, $edithint, $editlabel);
    }

}

/**
 * Implements callback inplace_editable() allowing to edit values in-place.
 *
 * @param string $itemtype
 * @param int $itemid
 * @param mixed $newvalue
 * @return \core\output\inplace_editable
 */
function format_grid_inplace_editable($itemtype, $itemid, $newvalue) {
    global $CFG;
    require_once($CFG->dirroot . '/course/lib.php');
    if ($itemtype === 'sectionname' || $itemtype === 'sectionnamenl') {
        global $DB;
        $section = $DB->get_record_sql(
                'SELECT s.* FROM {course_sections} s JOIN {course} c ON s.course = c.id WHERE s.id = ? AND c.format = ?',
                array($itemid, 'grid'), MUST_EXIST);
        return course_get_format($section->course)->inplace_editable_update_section_name($section, $itemtype, $newvalue);
    }
}

/**
 * Indicates this format uses sections.
 *
 * @return bool Returns true
 */
function callback_grid_uses_sections() {
    return true;
}

/**
 * Used to display the course structure for a course where format=grid
 *
 * This is called automatically by {@link load_course()} if the current course
 * format = weeks.
 *
 * @param array $path An array of keys to the course node in the navigation
 * @param stdClass $modinfo The mod info object for the current course
 * @return bool Returns true
 */
function callback_grid_load_content(&$navigation, $course, $coursenode) {
    return $navigation->load_generic_course_sections($course, $coursenode, 'grid');
}

/**
 * The string that is used to describe a section of the course
 * e.g. Topic, Week...
 *
 * @return string
 */
function callback_grid_definition() {
    return get_string('topic', 'format_grid');
}

/**
 * Deletes the settings entry for the given course upon course deletion.
 */
function format_grid_delete_course($courseid) {
    global $DB;

    /* Delete any images associated with the course.
      Done this way so will work if the course has
      been a grid format course in the past even if
      it is not now. */
    $courseformat = format_grid::get_instance($courseid);
    $courseformat->delete_images();
    unset($courseformat);  // Destruct.

    $DB->delete_records("format_grid_summary", array("courseid" => $courseid));
}
