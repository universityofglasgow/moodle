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
 * Cards format plugin library and callbacks
 *
 * @package     format_cards
 * @copyright   2024 University of Essex
 * @author      John Maydew <jdmayd@essex.ac.uk>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

use core\notification;
use core\output\inplace_editable;
use format_cards\forms\editcard_form;
use format_cards\section_break;

require_once("$CFG->dirroot/course/format/topics/lib.php");

define('FORMAT_CARDS_FILEAREA_IMAGE', 'image');
define('FORMAT_CARDS_USEDEFAULT', 0);
define('FORMAT_CARDS_SECTION0_COURSEPAGE', 1);
define('FORMAT_CARDS_SECTION0_ALLPAGES', 2);
define('FORMAT_CARDS_ORIENTATION_VERTICAL', 1);
define('FORMAT_CARDS_ORIENTATION_HORIZONTAL', 2);
define('FORMAT_CARDS_ORIENTATION_SQUARE', 3);
define('FORMAT_CARDS_SHOWSUMMARY_SHOW', 1);
define('FORMAT_CARDS_SHOWSUMMARY_HIDE', 2);
define('FORMAT_CARDS_SHOWPROGRESS_SHOW', 1);
define('FORMAT_CARDS_SHOWPROGRESS_HIDE', 2);
define('FORMAT_CARDS_PROGRESSFORMAT_COUNT', 1);
define('FORMAT_CARDS_PROGRESSFORMAT_PERCENTAGE', 2);
define('FORMAT_CARDS_SECTIONNAVIGATION_NONE', 1);
define('FORMAT_CARDS_SECTIONNAVIGATION_TOP', 2);
define('FORMAT_CARDS_SECTIONNAVIGATION_BOTTOM', 3);
define('FORMAT_CARDS_SECTIONNAVIGATION_BOTH', 4);
define('FORMAT_CARDS_SECTIONNAVIGATIONHOME_HIDE', '1');
define('FORMAT_CARDS_SECTIONNAVIGATIONHOME_SHOW', '2');
define('FORMAT_CARDS_SUBSECTIONS_AS_CARDS', 1);
define('FORMAT_CARDS_SUBSECTIONS_AS_ACTIVITIES', 2);

/**
 * Course format main class
 *
 * @package     format_cards
 * @copyright   2024 University of Essex
 * @author      John Maydew <jdmayd@essex.ac.uk>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class format_cards extends format_topics {

    /**
     * @var null|int
     */
    protected ?int $forcecoursedisplay = null;

    /**
     * Cards format allows you to indent course modules
     *
     * @return bool
     */
    #[\Override]
    public function uses_indentation(): bool {
        return true;
    }

    /**
     * Display the course in single page mode when we're on the course main page.
     * Once we're viewing an individual section, display it
     *
     * @return int
     * @throws dml_exception
     */
    #[\Override]
    public function get_course_display(): int {
        global $PAGE;

        // In editing mode we're displaying all sections anyway.
        // If we return SINGLEPAGE then sections are also collapsible.
        if ($this->show_editor()) {
            return COURSE_DISPLAY_SINGLEPAGE;
        }

        // So, this is gnarly, but in order to re-use existing code we need to be able to change the output of
        // get_course_display based on the current section that's being rendered, not just the current section we're
        // looking at. This way we can make sure section #0 renders properly -- with subsections displayed as collapsible sections
        // but *without* section #0 itself being collapsible.
        if (is_int($this->forcecoursedisplay)) {
            return $this->forcecoursedisplay;
        }

        // On the course section pages, whether we're displaying singlepage or multipage depends
        // on whether subsections are rendered as cards or not.
        if ($PAGE->pagetype === 'course-section' || $PAGE->pagetype === 'course-view-section-cards') {
            return $this->get_format_option('subsectionsascards') === FORMAT_CARDS_SUBSECTIONS_AS_CARDS
                ? COURSE_DISPLAY_MULTIPAGE
                : COURSE_DISPLAY_SINGLEPAGE;
        }

        return COURSE_DISPLAY_MULTIPAGE;
    }

    /**
     * Force the output of get_course_display
     *
     * @param int|null $value
     * @return void
     */
    public function set_forced_course_display(?int $value): void {
        $this->forcecoursedisplay = $value;
    }

    /**
     * Gets a list of user options for this course format
     *
     * @param bool $foreditform
     * @return array|array[]|false
     * @throws coding_exception
     * @throws dml_exception
     */
    #[\Override]
    public function course_format_options($foreditform = false) {
        $options = parent::course_format_options($foreditform);

        $defaults = get_config('format_cards');

        // We always show one section per page.
        $options['coursedisplay']['element_type'] = 'hidden';
        $options['coursedisplay']['default'] = COURSE_DISPLAY_MULTIPAGE;

        $createselect = function (string $name, array $options, int $default, bool $hashelp = false): array {
            $option = [
                'default' => FORMAT_CARDS_USEDEFAULT,
                'type' => PARAM_INT,
                'label' => new lang_string("form:course:$name", 'format_cards'),
                'element_type' => 'select',
                'element_attributes' => [
                    array_merge(
                        [
                            FORMAT_CARDS_USEDEFAULT => new lang_string(
                                'form:course:usedefault',
                                'format_cards',
                                $options[$default]),
                        ],
                        $options
                    ),
                ],
            ];

            if ($hashelp) {
                $option['help'] = "form:course:$name";
                $option['help_component'] = 'format_cards';
            }

            return $option;
        };

        $section0options = [
            FORMAT_CARDS_SECTION0_COURSEPAGE => new lang_string('form:course:section0:coursepage', 'format_cards'),
            FORMAT_CARDS_SECTION0_ALLPAGES => new lang_string('form:course:section0:allpages', 'format_cards'),
        ];

        $options['section0'] = $createselect('section0', $section0options, $defaults->section0, true);

        $sectionnavigationoptions = [
            FORMAT_CARDS_SECTIONNAVIGATION_NONE => new lang_string('form:course:sectionnavigation:none', 'format_cards'),
            FORMAT_CARDS_SECTIONNAVIGATION_TOP => new lang_string('form:course:sectionnavigation:top', 'format_cards'),
            FORMAT_CARDS_SECTIONNAVIGATION_BOTTOM => new lang_string('form:course:sectionnavigation:bottom', 'format_cards'),
            FORMAT_CARDS_SECTIONNAVIGATION_BOTH => new lang_string('form:course:sectionnavigation:both', 'format_cards'),
        ];

        $options['sectionnavigation'] = $createselect('sectionnavigation', $sectionnavigationoptions, $defaults->sectionnavigation);

        $sectionnavigationhomeoptions = [
            FORMAT_CARDS_SECTIONNAVIGATIONHOME_HIDE =>
                new lang_string('form:course:sectionnavigationhome:hide', 'format_cards'),
            FORMAT_CARDS_SECTIONNAVIGATIONHOME_SHOW =>
                new lang_string('form:course:sectionnavigationhome:show', 'format_cards'),
        ];
        $options['sectionnavigationhome'] = $createselect(
            'sectionnavigationhome',
            $sectionnavigationhomeoptions,
            $defaults->sectionnavigationhome
        );

        $orientationoptions = [
            FORMAT_CARDS_ORIENTATION_VERTICAL => new lang_string('form:course:cardorientation:vertical', 'format_cards'),
            FORMAT_CARDS_ORIENTATION_HORIZONTAL => new lang_string('form:course:cardorientation:horizontal', 'format_cards'),
            FORMAT_CARDS_ORIENTATION_SQUARE => new lang_string('form:course:cardorientation:square', 'format_cards'),
        ];

        $options['cardorientation'] = $createselect('cardorientation', $orientationoptions, $defaults->cardorientation);

        $summaryoptions = [
            FORMAT_CARDS_SHOWSUMMARY_SHOW => new lang_string('form:course:showsummary:show', 'format_cards'),
            FORMAT_CARDS_SHOWSUMMARY_HIDE => new lang_string('form:course:showsummary:hide', 'format_cards'),
        ];

        $options['showsummary'] = $createselect('showsummary', $summaryoptions, $defaults->showsummary);

        $showprogressoptions = [
            FORMAT_CARDS_SHOWPROGRESS_SHOW => new lang_string('form:course:showprogress:show', 'format_cards'),
            FORMAT_CARDS_SHOWPROGRESS_HIDE => new lang_string('form:course:showprogress:hide', 'format_cards'),
        ];

        $options['showprogress'] = $createselect('showprogress', $showprogressoptions, $defaults->showprogress);

        $progressformatoptions = [
            FORMAT_CARDS_PROGRESSFORMAT_COUNT => new lang_string('form:course:progressformat:count', 'format_cards'),
            FORMAT_CARDS_PROGRESSFORMAT_PERCENTAGE => new lang_string('form:course:progressformat:percentage', 'format_cards'),
        ];

        $options['progressformat'] = $createselect('progressformat', $progressformatoptions, $defaults->progressformat);

        $subsectionoptions = [
            FORMAT_CARDS_SUBSECTIONS_AS_CARDS => new lang_string('form:course:subsectionsascards:cards', 'format_cards'),
            FORMAT_CARDS_SUBSECTIONS_AS_ACTIVITIES => new lang_string('form:course:subsectionsascards:activity', 'format_cards'),
        ];

        $options['subsectionsascards'] = $createselect('subsectionsascards', $subsectionoptions, $defaults->subsectionsascards);

        return $options;
    }

    /**
     * Users should be able to specify per-section whether the summary is visible or not
     *
     * @param bool $foreditform
     * @return array
     * @throws dml_exception
     */
    #[\Override]
    public function section_format_options($foreditform = false) {
        $options = parent::section_format_options($foreditform);

        $defaultshowsummary = $this->get_format_option('showsummary');
        $summaryoptions = [
            FORMAT_CARDS_SHOWSUMMARY_SHOW => new lang_string('form:course:showsummary:show', 'format_cards'),
            FORMAT_CARDS_SHOWSUMMARY_HIDE => new lang_string('form:course:showsummary:hide', 'format_cards'),
        ];

        $options['showsummary'] = [
            'default' => FORMAT_CARDS_USEDEFAULT,
            'type' => PARAM_INT,
            'label' => new lang_string('form:course:showsummary', 'format_cards'),
            'element_type' => 'select',
            'element_attributes' => [
                array_merge(
                    [
                        FORMAT_CARDS_USEDEFAULT => new lang_string(
                            'form:course:usedefault',
                            'format_cards',
                            $summaryoptions[$defaultshowsummary]
                        ),
                    ],
                    $summaryoptions
                ),
            ],
        ];

        $options['sectionbreak'] = [
            'default' => false,
            'type' => PARAM_BOOL,
            'label' => new lang_string('section:break', 'format_cards'),
            'element_type' => 'hidden',
        ];

        $options['sectionbreaktitle'] = [
            'default' => '',
            'type' => PARAM_TEXT,
            'label' => new lang_string('section:break', 'format_cards'),
            'element_type' => 'hidden',
        ];

        return $options;
    }

    /**
     * Modify the edit section form to include controls for editing
     * the image for a section
     *
     * @param string $action
     * @param array $customdata
     * @return editcard_form
     */
    #[\Override]
    public function editsection_form($action, $customdata = []): editcard_form {
        if (!array_key_exists('course', $customdata)) {
            $customdata['course'] = $this->get_course();
        }

        $draftimageid = file_get_submitted_draft_itemid('image');
        file_prepare_draft_area(
            $draftimageid,
            context_course::instance($this->get_courseid())->id,
            'format_cards',
            FORMAT_CARDS_FILEAREA_IMAGE,
            $customdata['cs']->id
        );

        $customdata['image'] = $draftimageid;

        return new editcard_form($action, $customdata);
    }

    /**
     * When the section form is changed, make sure any uploaded
     * images are saved properly
     *
     * @param stdClass|array $data Return value from moodleform::get_data() or array with data
     * @return bool True if changes were made
     * @throws coding_exception
     */
    #[\Override]
    public function update_section_format_options($data): bool {
        $changes = parent::update_section_format_options($data);

        // Make sure we don't accidentally clobber any existing saved images if we get here
        // from inplace_editable.
        if (!array_key_exists('image', $data)) {
            return $changes;
        }

        file_save_draft_area_files(
            $data['image'],
            context_course::instance($this->get_courseid())->id,
            'format_cards',
            FORMAT_CARDS_FILEAREA_IMAGE,
            $data['id']
        );

        // Try and resize the image. It's no big deal if this fails -- we still
        // have the image, it'll just affect page load times.
        try {
            $this->resize_card_image($data['id']);
        } catch (moodle_exception $e) {
            notification::add(
                get_string('editimage:resizefailed', 'format_cards'),
                notification::WARNING
            );
        }

        return $changes;
    }

    /**
     * Updates a section break title
     *
     * @param stdClass $section Section to update
     * @param string $itemtype The item type
     * @param string $newvalue New item value
     * @return inplace_editable
     * @throws \core_external\restricted_context_exception
     * @throws invalid_parameter_exception
     * @throws required_capability_exception
     */
    #[\Override]
    public function inplace_editable_update_section_name($section, $itemtype, $newvalue): inplace_editable {
        global $CFG;

        if ($itemtype === 'sectionbreak') {

            $context = context_course::instance($section->course);

            // The external_api class is in a different place in Moodle 4.1.
            if ($CFG->version < 2023042400) {
                require_once("$CFG->libdir/externallib.php");
                \external_api::validate_context($context);
            } else {
                \core_external\external_api::validate_context($context);
            }

            require_capability('moodle/course:update', $context);

            $newtitle = clean_param($newvalue, PARAM_TEXT);

            $break = section_break::get_break_for_section($section);
            if (strval($break->get('name')) !== strval($newtitle)) {
                $break->set('name', $newtitle);
                $break->save();

                // Reset the break cache if the name changes.
                $cache = cache::make_from_params(
                    cache_store::MODE_APPLICATION,
                    'format_cards',
                    'section_breaks'
                );
                $cache->delete($section->course);
            }

            return $this->inplace_editable_render_section_break($section, true);
        }

        return parent::inplace_editable_update_section_name($section, $itemtype, $newvalue);
    }

    /**
     * Renders a section break as an inplace editable
     *
     * @param stdClass $section Section to update break in
     * @param bool|null $editable Whether the break should be editable
     * @return inplace_editable
     */
    public function inplace_editable_render_section_break($section, ?bool $editable = null): inplace_editable {

        if ($editable === null) {
            $editable = $this->show_editor([ 'moodle/course:update' ]);
        }

        $sectionbreak = section_break::get_break_for_section($section);
        $break = $sectionbreak->get('name');
        $display = $break;
        if (empty($break) && $editable) {
            $display = get_string('section:break:marker', 'format_cards');
        }

        return new inplace_editable(
            'format_cards',
            'sectionbreak',
            $section->id,
            $editable,
            $display,
            $break,
            new lang_string('section:break:edit', 'format_cards'),
            new lang_string('section:break', 'format_cards')
        );
    }

    /**
     * When a section is deleted successfully, make sure we also delete
     * the card image
     *
     * @param int|stdClass|section_info $section
     * @param bool $forcedeleteifnotempty
     * @return bool
     * @throws coding_exception
     * @throws dml_exception
     */
    #[\Override]
    public function delete_section($section, $forcedeleteifnotempty = false): bool {
        global $DB;

        if (!is_object($section)) {
            $section = $DB->get_record('course_sections',
                [
                    'course' => $this->get_courseid(),
                    'section' => $section,
                ]);
        }

        $filestorage = get_file_storage();
        $context = context_course::instance($this->get_courseid());
        $images = $filestorage->get_area_files(
            $context->id,
            'format_cards',
            FORMAT_CARDS_FILEAREA_IMAGE,
            $section->id
        );

        foreach ($images as $image) {
            $image->delete();
        }

        return parent::delete_section($section, $forcedeleteifnotempty);
    }


    /**
     * Append the "importgridimages" checkbox directly to the form.
     * We do this rather than using {@see self::course_format_options()} to prevent "importgridimages" being saved
     * as an actual option.
     *
     * @param MoodleQuickForm $mform The form
     * @param bool $forsection
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     */
    #[\Override]
    public function create_edit_form_elements(&$mform, $forsection = false): array {
        $elements = parent::create_edit_form_elements($mform, $forsection);

        if ($this->course_has_grid_images() && !$forsection) {
            $elements[] = $mform->addElement(
                'checkbox',
                'importgridimages',
                get_string('form:course:importgridimages', 'format_cards')
            );
            $mform->addHelpButton('importgridimages', 'form:course:importgridimages', 'format_cards');
        }

        $defaultshowprogress = get_config('format_cards', 'showprogress');
        $hiddenvalues = [ FORMAT_CARDS_SHOWPROGRESS_HIDE ];

        if ($defaultshowprogress == FORMAT_CARDS_SHOWPROGRESS_HIDE) {
            $hiddenvalues[] = FORMAT_CARDS_USEDEFAULT;
        }
        $mform->hideIf('progressformat', 'showprogress', 'in', $hiddenvalues);

        return $elements;
    }

    /**
     * Update course format options from form data.
     * Primarily just calls the parent method to do the actual saving, but additionally
     * imports images from format_grid if selected
     *
     * @param stdClass|array $data Data from update form
     * @param stdClass $oldcourse Contains information about the course pre-update
     * @return bool
     * @throws coding_exception
     * @throws dml_exception
     * @throws ddl_exception
     */
    #[\Override]
    public function update_course_format_options($data, $oldcourse = null): bool {
        global $DB;

        $changes = parent::update_course_format_options($data, $oldcourse);

        if (empty($data->importgridimages) || !$this->course_has_grid_images()) {
            return $changes;
        }

        // Importgridimages isn't an actual format option. We set it to false here
        // so it doesn't get saved into the database.
        $data->importgridimages = false;

        $manager = $DB->get_manager();

        $gridimages = $manager->table_exists('format_grid_icon')
            ? $DB->get_records('format_grid_icon', [ 'courseid' => $this->courseid ])
            : $DB->get_records('format_grid_image', [ 'courseid' => $this->courseid ]);

        $storage = get_file_storage();
        $context = context_course::instance($this->courseid);
        $existingimages = $storage->get_area_files(
            $context->id,
            'format_cards',
            FORMAT_CARDS_FILEAREA_IMAGE,
            false,
            'itemid, filepath, filename',
            false
        );

        $added = 0;
        $hasdisplayedresizeerror = false;

        foreach ($gridimages as $gridimage) {
            if (!$gridimage->image) {
                continue;
            }

            // New course format db layout for format_grid.
            if (object_property_exists($gridimage, 'contenthash')) {
                $gridimagefiles = $storage->get_area_files(
                    $context->id,
                    'format_grid',
                    'sectionimage',
                    $gridimage->sectionid,
                    'itemid, filepath, filename',
                    false
                );

                if (empty($gridimagefiles)) {
                    debugging("No images for section {$gridimage->sectionid}");
                    continue;
                }

                $gridimagefile = reset($gridimagefiles);
            } else {
                $gridimagefile = $storage->get_file(
                    $context->id,
                    'course',
                    'section',
                    $gridimage->sectionid,
                    '/gridimage/',
                    "{$gridimage->displayedimageindex}_$gridimage->image"
                );
            }

            if (!$gridimagefile) {
                debugging("Couldn't get grid format image {$gridimage->displayedimageindex}_$gridimage->image", DEBUG_DEVELOPER);
                continue;
            }

            try {
                $newfile = $storage->create_file_from_storedfile([
                    'contextid' => $context->id,
                    'component' => 'format_cards',
                    'filearea' => FORMAT_CARDS_FILEAREA_IMAGE,
                    'itemid' => $gridimage->sectionid,
                    'filepath' => '/',
                ], $gridimagefile);

                $added++;
                $changes = true;
            } catch (file_exception $e) {
                continue;
            }

            if (!$newfile) {
                debugging("Failed to create new format_cards image from grid for section $gridimage->sectionid", DEBUG_DEVELOPER);
                continue;
            }

            $existingsectionimages = array_filter($existingimages, function($file) use ($gridimage) {
                return $gridimage->sectionid == $file->get_itemid();
            });

            try {
                $this->resize_card_image($gridimage->sectionid);
            } catch (moodle_exception $e) {
                if (!$hasdisplayedresizeerror) {
                    notification::add(get_string('editimage:resizefailed', 'format_cards'), notification::WARNING);
                    $hasdisplayedresizeerror = true;
                }
            }

            foreach ($existingsectionimages as $existingsectionimage) {
                $existingsectionimage->delete();
            }

        }

        notification::add(
            get_string('editimage:imported', 'format_cards', $added),
            notification::SUCCESS
        );

        return $changes;
    }

    /**
     * Fetch a format option from the settings. If it's one of the options that can have an admin provided default,
     * use that unless it's been overridden for this course
     *
     * @param string $name Option key
     * @param null|int|section_info|stdClass $section The section this option applies to, or 0 for the whole course
     * @return mixed The option's value
     * @throws dml_exception
     */
    public function get_format_option(string $name, $section = null) {
        $options = $this->get_format_options($section);
        $defaults = get_config('format_cards');

        if (array_key_exists($name, $options)) {
            $value = $options[$name];
        } else {
            $value = $defaults->$name;
        }

        if (!object_property_exists($defaults, $name)) {
            return $value;
        }

        if ($value != FORMAT_CARDS_USEDEFAULT) {
            return $value;
        }

        if (!is_null($section)) {
            $coursedefaults = (object) $this->get_format_options();

            if (!object_property_exists($coursedefaults, $name)) {
                return $defaults->$name;
            }

            if ($coursedefaults->$name != FORMAT_CARDS_USEDEFAULT) {
                return $coursedefaults->$name;
            }
        }

        return $defaults->$name;
    }

    /**
     * Returns the default display name for a section which doesn't have
     * a user defined title
     *
     * @param stdClass|section_info $section The section
     * @return lang_string|string A default name for the given section
     * @throws coding_exception
     */
    #[\Override]
    public function get_default_section_name($section) {
        $default = parent::get_default_section_name($section);

        if (!empty(trim($default))) {
            return $default;
        }

        return get_string('section:default', 'format_cards', $section->section);
    }

    /**
     * format_topics::get_view_url() returns the course URL with an HTML anchor pointing to the selected
     * section, if there is one. format_cards always uses single section per page mode, so we always want
     * a URL with the section number as a query parameter
     *
     * @param null|int|stdClass|section_info $section The selected section
     * @param array $options
     * @return moodle_url
     * @throws moodle_exception
     */
    #[\Override]
    public function get_view_url($section, $options = []): moodle_url {
        global $CFG;

        // Try and get the section's position in the course.
        if (array_key_exists('sr', $options) && is_int($options['sr'])) {
            $sectionno = $options['sr'];
        } else if (is_object($section)) {
            $sectionno = $section->section;
        } else {
            $sectionno = $section;
        }

        // If there's no section number, the URL can only be the base course URL.
        if (empty($sectionno)) {
            return new moodle_url('/course/view.php', [ 'id' => $this->get_course()->id ]);
        }

        // We've followed this link from a navigation menu, so display the individual section page.
        if (!empty($options['navigation']) || array_key_exists('sr', $options)) {

            // Moodle < 4.4 doesn't use the /course/section.php URL's.
            if ($CFG->version < 2024042200) {
                return new moodle_url(
                    '/course/view.php',
                    [ 'id' => $this->get_course()->id, 'section' => $sectionno ]
                );
            }

            $sectioninfo = $this->get_section($sectionno);
            return new moodle_url('/course/section.php', [ 'id' => $sectioninfo->id ]);
        }

        return new moodle_url(
            '/course/view.php',
            [ 'id' => $this->get_course()->id, 'section' => $sectionno ]
        );
    }

    /**
     * Shim to support get_sectionnum in Moodle < 4.4
     *
     * @return int|null
     */
    public function get_sectionnum(): ?int {
        global $CFG;

        return $CFG->version < 2024042200
            ? $this->get_section_number()
            : parent::get_sectionnum();
    }

    /**
     * If this course previously used format_grid, check to see if there are any images
     * that we might want to import
     *
     * @return bool True if the course had images for format_grid sections
     * @throws dml_exception
     * @throws ddl_exception
     */
    public function course_has_grid_images(): bool {
        global $DB;

        // Definitely no images if format_grid isn't installed.
        if (!in_array('grid', get_sorted_course_formats())) {
            return false;
        }

        $course = $this->get_course();
        if (!$course) {
            return false;
        }

        if (!isset($course->id)) {
            return false;
        }

        $manager = $DB->get_manager();

        if ($manager->table_exists('format_grid_icon')) {
            return $DB->record_exists('format_grid_icon', [ 'courseid' => $course->id ]);
        }

        return $DB->record_exists('format_grid_image', [ 'courseid' => $course->id ]);
    }

    /**
     * Checks if the course has one or more section images
     *
     * @return bool True if the course has one or more images
     * @throws dml_exception
     */
    public function course_has_card_images(): bool {
        global $DB;

        $course = $this->get_course();
        $context = context_course::instance($course->id);

        return $DB->record_exists(
            'files',
            [
                'component' => 'format_cards',
                'filearea' => FORMAT_CARDS_FILEAREA_IMAGE,
                'contextid' => $context->id,
            ]
        );
    }

    /**
     * Attempt to resize the image uploaded for a card
     *
     * @param int|stdClass $section Section ID or class
     * @return void
     * @throws coding_exception
     * @throws file_exception
     * @throws moodle_exception
     * @throws stored_file_creation_exception
     */
    public function resize_card_image($section): void {
        global $CFG;

        require_once("$CFG->libdir/gdlib.php");

        if (is_object($section)) {
            $section = $section->id;
        }

        $course = $this->get_course();
        $context = context_course::instance($course->id);
        $storage = get_file_storage();

        // First, grab the file.
        $images = $storage->get_area_files(
            $context->id,
            'format_cards',
            FORMAT_CARDS_FILEAREA_IMAGE,
            $section,
            'itemid, filepath, filename',
            false
        );

        if (empty($originalimage)) {
            return;
        }

        /** @var stored_file $originalimage */
        $originalimage = reset($images);

        $tempfilepath = $originalimage->copy_content_to_temp('format_cards', 'sectionimage_');

        $resized = resize_image($tempfilepath, null, 500, false);

        if (!$resized) {
            throw new moodle_exception('failedtoresize', 'format_cards');
        }

        $originalimage->delete();

        try {
            $storage->create_file_from_string(
                [
                    'contextid' => $originalimage->get_contextid(),
                    'component' => $originalimage->get_component(),
                    'filearea' => $originalimage->get_filearea(),
                    'itemid' => $originalimage->get_itemid(),
                    'filepath' => $originalimage->get_filepath(),
                    'filename' => $originalimage->get_filename(),
                ], $resized
            );
            $originalimage->delete();
        } finally {
            unlink($tempfilepath);
        }
    }
}

/**
 * Allow for the section name to be edited in-place
 *
 * @param string $itemtype
 * @param int $itemid
 * @param mixed $newvalue
 * @return inplace_editable|void
 * @throws dml_exception
 */
function format_cards_inplace_editable($itemtype, $itemid, $newvalue) {
    global $DB, $CFG;
    require_once("$CFG->dirroot/course/lib.php");

    if (!in_array($itemtype, ['sectionname', 'sectionnamenl', 'sectionbreak'])) {
        return;
    }

    $section = $DB->get_record_sql(
        'SELECT s.* FROM {course_sections} s JOIN {course} c ON s.course = c.id WHERE s.id = ? AND c.format = ?',
        [$itemid, 'cards'], MUST_EXIST);
    return course_get_format($section->course)->inplace_editable_update_section_name($section, $itemtype, $newvalue);
}

/**
 * Serves files for format_cards
 *
 * @param stdClass $course
 * @param stdClass|null $coursemodule
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options
 * @return void
 * @throws coding_exception
 * @throws moodle_exception
 */
function format_cards_pluginfile(stdClass $course,
                                 ?stdClass $coursemodule,
                                 context $context,
                                 string $filearea,
                                 array $args,
                                 $forcedownload,
                                 array $options = []) {
    if ($context->contextlevel != CONTEXT_COURSE && $context->contextlevel != CONTEXT_SYSTEM) {
        send_file_not_found();
    }

    if ($filearea != FORMAT_CARDS_FILEAREA_IMAGE) {
        send_file_not_found();
    }

    $itemid = array_shift($args);

    $filename = array_pop($args);
    if (!$args) {
        $filepath = '/';
    } else {
        $filepath = '/' . implode('/', $args) . '/';
    }

    $filestorage = get_file_storage();
    $file = $filestorage->get_file($context->id, 'format_cards', $filearea, $itemid, $filepath, $filename);
    send_stored_file($file, 86400, 0, $forcedownload, $options);
}
