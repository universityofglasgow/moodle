<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * This file defines the settings form for the quiz essaydownload report.
 *
 * @package   quiz_essaydownload
 * @copyright 2024 Philipp E. Imhof
 * @author    Philipp E. Imhof
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


/**
 * Quiz essaydownload report settings form.
 *
 * @copyright 2024 Philipp E. Imhof
 * @author    Philipp E. Imhof
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->libdir . '/formslib.php');

/**
 * Class defining the form for a {@see quiz_essaydownload_report}.
 *
 * @package   quiz_essaydownload
 * @copyright 2024 Philipp E. Imhof
 * @author    Philipp E. Imhof
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quiz_essaydownload_form extends moodleform {
    /**
     * Form definition.
     *
     * @return void
     */
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('header', 'generaloptions', get_string('generaloptions', 'quiz_essaydownload'));
        $this->standard_preference_fields($mform);

        $mform->addElement('header', 'pdfoptions', get_string('pdfoptions', 'quiz_essaydownload'));
        $this->pdf_layout_fields($mform);
        $mform->closeHeaderBefore('download');

        $mform->addElement('submit', 'download', get_string('download'));
    }

    /**
     * Add the preference fields that we offer.
     *
     * @param MoodleQuickForm $mform the form
     * @return void
     */
    protected function standard_preference_fields(MoodleQuickForm $mform) {
        $mform->addElement(
            'select',
            'groupby',
            get_string('groupby', 'quiz_essaydownload'),
            [
                'byattempt' => get_string('byattempt', 'quiz_essaydownload'),
                'byquestion' => get_string('byquestion', 'quiz_essaydownload'),
            ]
        );
        $mform->setType('groupby', PARAM_ALPHA);
        $mform->addHelpButton('groupby', 'groupby', 'quiz_essaydownload');

        $mform->addElement(
            'advcheckbox',
            'flatarchive',
            '',
            get_string('useflatarchive', 'quiz_essaydownload')
        );
        $mform->addHelpButton('flatarchive', 'useflatarchive', 'quiz_essaydownload');

        $mform->addElement(
            'advcheckbox',
            'allinone',
            '',
            get_string('allinone', 'quiz_essaydownload')
        );
        $mform->addHelpButton('allinone', 'allinone', 'quiz_essaydownload');
        $mform->disabledIf('allinone', 'groupby', 'neq', 'byattempt');
        $mform->disabledIf('allinone', 'flatarchive');
        $mform->disabledIf('allinone', 'fileformat', 'neq', 'pdf');

        if (quiz_report_can_filter_only_graded($this->_customdata['quiz'])) {
            $gradingmethod = quiz_get_grading_option_name($this->_customdata['quiz']->grademethod);
            $mform->addElement(
                'advcheckbox',
                'onlyone',
                get_string('limitattempts', 'quiz_essaydownload'),
                get_string('onlyone', 'quiz_essaydownload', $gradingmethod)
            );
            $mform->addHelpButton('onlyone', 'onlyone', 'quiz_essaydownload');
        }

        $mform->addElement(
            'select',
            'nameordering',
            get_string('nameordering', 'quiz_essaydownload'),
            [
                'lastfirst' => get_string('lastfirst', 'quiz_essaydownload'),
                'firstlast' => get_string('firstlast', 'quiz_essaydownload'),
            ]
        );
        $mform->setType('nameordering', PARAM_ALPHA);

        $mform->addElement(
            'advcheckbox',
            'attachments',
            get_string('attachments', 'quiz_essaydownload'),
            get_string('includeattachments', 'quiz_essaydownload')
        );
        $mform->addHelpButton('attachments', 'includeattachments', 'quiz_essaydownload');

        $mform->addElement(
            'advcheckbox',
            'questiontext',
            get_string('questiontext', 'question'),
            get_string('includequestiontext', 'quiz_essaydownload')
        );
        $mform->addHelpButton('questiontext', 'includequestiontext', 'quiz_essaydownload');

        $mform->addElement(
            'advcheckbox',
            'includestats',
            get_string('statistics', 'quiz_essaydownload'),
            get_string('includestats', 'quiz_essaydownload')
        );
        $mform->addHelpButton('includestats', 'includestats', 'quiz_essaydownload');

        $mform->addElement('select', 'fileformat', get_string('fileformat', 'quiz_essaydownload'), [
            'txt' => get_string('fileformattxt', 'quiz_essaydownload'),
            'pdf' => get_string('fileformatpdf', 'quiz_essaydownload'),
        ]);
        $mform->setType('fileformat', PARAM_ALPHA);
        $mform->setDefault('fileformat', 'pdf');
        $mform->addHelpButton('fileformat', 'fileformat', 'quiz_essaydownload');

        $mform->addElement('select', 'source', get_string('source', 'quiz_essaydownload'), [
            'plain' => get_string('sourcesummary', 'quiz_essaydownload'),
            'html' => get_string('sourceoriginal', 'quiz_essaydownload'),
        ]);
        $mform->disabledIf('source', 'fileformat', 'neq', 'pdf');
        $mform->setType('source', PARAM_ALPHA);
        $mform->setDefault('source', 'html');
        $mform->addHelpButton('source', 'source', 'quiz_essaydownload');

        $mform->addElement(
            'advcheckbox',
            'shortennames',
            get_string('troubleshooting', 'quiz_essaydownload'),
            get_string('shortennames', 'quiz_essaydownload')
        );
        $mform->addHelpButton('shortennames', 'shortennames', 'quiz_essaydownload');
        $mform->addElement(
            'advcheckbox',
            'fixremfontsize',
            '',
            get_string('fixremfontsize', 'quiz_essaydownload')
        );
        $mform->disabledIf('fixremfontsize', 'fileformat', 'neq', 'pdf');
        $mform->disabledIf('fixremfontsize', 'source', 'neq', 'html');
        $mform->addHelpButton('fixremfontsize', 'fixremfontsize', 'quiz_essaydownload');
        $mform->addElement(
            'advcheckbox',
            'forceqtsummary',
            '',
            get_string('forceqtsummary', 'quiz_essaydownload')
        );
        $mform->disabledIf('forceqtsummary', 'fileformat', 'neq', 'pdf');
        $mform->disabledIf('forceqtsummary', 'source', 'neq', 'html');
        $mform->disabledIf('forceqtsummary', 'questiontext');
        $mform->addHelpButton('forceqtsummary', 'forceqtsummary', 'quiz_essaydownload');
    }

    /**
     * Fields to configure the PDF layout.
     *
     * @param MoodleQuickForm $mform the form
     * @return void
     */
    protected function pdf_layout_fields(MoodleQuickForm $mform) {
        $mform->addElement('select', 'pageformat', get_string('page', 'quiz_essaydownload'), [
            'a4' => get_string('pagea4', 'quiz_essaydownload'),
            'letter' => get_string('pageletter', 'quiz_essaydownload'),
        ]);
        $mform->setType('pageformat', PARAM_ALPHANUM);
        $mform->setDefault('pageformat', 'a4');
        $mform->disabledIf('pageformat', 'fileformat', 'neq', 'pdf');

        $margingroup = [];
        $margingroup[] = $mform->createElement('text', 'marginleft', '', ['size' => 3]);
        $mform->setType('marginleft', PARAM_INT);
        $margingroup[] = $mform->createElement('text', 'marginright', '', ['size' => 3]);
        $mform->setType('marginright', PARAM_INT);
        $margingroup[] = $mform->createElement('text', 'margintop', '', ['size' => 3]);
        $mform->setType('margintop', PARAM_INT);
        $margingroup[] = $mform->createElement('text', 'marginbottom', '', ['size' => 3]);
        $mform->setType('marginbottom', PARAM_INT);
        $mform->addGroup($margingroup, 'margingroup', get_string('margins', 'quiz_essaydownload'), ' ', false);
        $mform->disabledIf('margingroup', 'fileformat', 'neq', 'pdf');

        $mform->addElement(
            'advcheckbox',
            'includefooter',
            get_string('footer', 'quiz_essaydownload'),
            get_string('includefooter', 'quiz_essaydownload')
        );
        $mform->disabledIf('includefooter', 'fileformat', 'neq', 'pdf');

        $mform->addElement('select', 'linespacing', get_string('linespacing', 'quiz_essaydownload'), [
            '1' => get_string('linesingle', 'quiz_essaydownload'),
            '1.5' => get_string('lineoneandhalf', 'quiz_essaydownload'),
            '2' => get_string('linedouble', 'quiz_essaydownload'),
        ]);
        $mform->setType('linespacing', PARAM_FLOAT);
        $mform->disabledIf('linespacing', 'fileformat', 'neq', 'pdf');

        $mform->addElement('select', 'font', get_string('font', 'quiz_essaydownload'), [
            'sans' => get_string('fontsans', 'quiz_essaydownload'),
            'serif' => get_string('fontserif', 'quiz_essaydownload'),
            'mono' => get_string('fontmono', 'quiz_essaydownload'),
        ]);
        $mform->setType('font', PARAM_ALPHA);
        $mform->setDefault('font', 'serif');
        $mform->disabledIf('font', 'fileformat', 'neq', 'pdf');
        $mform->addHelpButton('font', 'font', 'quiz_essaydownload');

        $mform->addElement('text', 'fontsize', get_string('fontsize', 'quiz_essaydownload'), ['size' => 3]);
        $mform->setType('fontsize', PARAM_INT);
        $mform->disabledIf('fontsize', 'fileformat', 'neq', 'pdf');
        $mform->addHelpButton('fontsize', 'fontsize', 'quiz_essaydownload');
    }

    /**
     * Validation of our settings form, e. g. font size or page margins.
     *
     * @param array $data submitted data in form ['fieldname' => value]
     * @param array $files array of uploaded files ['element_name' => tmp_file_path]
     * @return array errors in form ['element_name' => 'error message'] or [] if no errors
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        // No further validation to be done if using plain text format.
        if ($data['fileformat'] === 'txt') {
            return $errors;
        }

        $margins = [$data['marginleft'], $data['marginright'], $data['margintop'], $data['marginbottom']];
        foreach ($margins as $margin) {
            if ($margin > 80 || $margin < 0) {
                $errors['margingroup'] = get_string('errormargin', 'quiz_essaydownload');
            }
        }

        if ($data['fontsize'] > 50 || $data['fontsize'] < 6) {
            $errors['fontsize'] = get_string('errorfontsize', 'quiz_essaydownload');
        }

        return $errors;
    }
}
