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
 * Behat data generator for format_cards.
 *
 * @package    format_cards
 * @copyright  2024 University of Essex
 * @author     John Maydew {@email jdmayd@essex.ac.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class behat_format_cards extends behat_base {

    /**
     * The summary field was renamed to description in Moodle 4.4.
     *
     * @When I set the section description to :value
     * @param string $value
     * @return void
     * @throws Exception
     */
    public function i_set_the_section_description_to(string $value): void {
        try {
            $this->execute('behat_forms::i_set_the_field_to', [ 'Summary', $value ]);
        } catch (Exception $e) {
            $this->execute('behat_forms::i_set_the_field_to', [ 'Description', $value ]);
        }
    }

    /**
     * The "section" named selector in Moodle 4.4 can handle "Section <num>", where num is the sectionnum. Prior
     * versions expect the section selector to provide you the full section name, which is tricky as the default
     * section name has changed from "Card <num>" to "New section". It's easier to ensure sections have the right name.
     *
     * @Given /^"([^"]+)" section (\d+) is named "([^"]+)"$/
     * @param string $courseidentifier
     * @param string $sectionnum
     * @param string $name
     * @return void
     * @throws Exception
     */
    public function section_is_named(string $courseidentifier, string $sectionnum, string $name): void {
        global $DB;

        $courseid = $this->get_course_id($courseidentifier);

        if (is_null($courseid)) {
            throw new Exception("Couldn't find course \"$courseidentifier\"");
        }

        $record = $DB->get_record('course_sections', [ 'course' => $courseid, 'section' => intval($sectionnum) ]);
        $record->name = $name;
        $DB->update_record('course_sections', $record);
    }

    /**
     * Add the "section card" named selector
     *
     * @return behat_component_named_selector[]
     */
    public static function get_exact_named_selectors(): array {
        return [
            new behat_component_named_selector('card', [

                // Search for a card by its section title.
                "//li[contains(@class, 'dashboard-card')][@data-for='section']".
                "[.//h3[@class='card-title'][contains(., %locator%)]]",

                // Or search for a card by its position.
                "//li[contains(@class, 'dashboard-card')][@data-for='section'][@data-sectionid=%locator%]",
            ]),
        ];
    }

    /**
     * Backport behat_course::resolve_page_instance_url from Moodle 4.4+ so we can use it when running
     * behat tests against earlier versions of Moodle
     *
     * Deprecate when we drop support for Moodle 4.3
     *
     * @param string $type
     * @param string $identifier
     * @return moodle_url
     * @throws moodle_exception
     * @throws Exception
     */
    protected function resolve_page_instance_url(string $type, string $identifier): moodle_url {

        try {
            $context = behat_context_helper::get('behat_course');
            return $context->resolve_page_instance_url($type, $identifier);
        } catch (Exception $e) {
            $type = strtolower($type);
            switch ($type) {
                case 'section':
                    $identifiers = explode('>', $identifier);
                    $identifiers = array_map('trim', $identifiers);
                    if (count($identifiers) < 2) {
                        throw new Exception("The specified section $identifier is not valid and should be coursename > section.");
                    }
                    [$courseidentifier, $sectionidentifier] = $identifiers;

                    $section = $this->get_section_and_course_by_id($courseidentifier, $sectionidentifier);
                    if (!$section) {
                        // If section is not found by name, search it by section number.
                        $sectionno = preg_replace("/^section (\d+)$/i", '$1', $sectionidentifier);
                        $section = $this->get_section_and_course_by_sectionnum($courseidentifier, (int) $sectionno);
                    }
                    if (!$section) {
                        throw new Exception("The specified section $identifier does not exist.");
                    }
                    return new moodle_url(
                        '/course/view.php',
                        [ 'id' => $section->course, 'sectionid' => $section->id ]
                    );
                default:
                    throw new Exception('Unrecognised core page type "' . $type . '."');
            }
        }
    }

    /**
     * Get the section id from an identifier.
     *
     * The section name and summary are checked.
     *
     * @param string $courseidentifier
     * @param string $sectionidentifier
     * @return section_info|null section info or null if not found.
     */
    protected function get_section_and_course_by_id(string $courseidentifier, string $sectionidentifier): ?section_info {
        $courseid = $this->get_course_id($courseidentifier);
        if (!$courseid) {
            return null;
        }
        $courseformat = course_get_format($courseid);
        $sections = $courseformat->get_sections();
        foreach ($sections as $section) {
            $sectionfullname = $courseformat->get_section_name($section);
            if ($section->name == $sectionidentifier
                || $sectionfullname == $sectionidentifier
            ) {
                return $section;
            }
        }
        return null;
    }

    /**
     * Get the section id from a courseid and a sectionnum.
     *
     * @param string $courseidentifier Course identifier.
     * @param int $sectionnum          Section number
     * @return section_info|null section info or null if not found.
     * @throws moodle_exception
     */
    protected function get_section_and_course_by_sectionnum(string $courseidentifier, int $sectionnum): ?section_info {
        $courseid = $this->get_course_id($courseidentifier);
        if (!$courseid) {
            return null;
        }
        $courseformat = course_get_format($courseid);
        return $courseformat->get_section($sectionnum);
    }
}
