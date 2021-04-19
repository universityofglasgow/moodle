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
 * @package     qtype_kprime
 * @author      Amr Hourani (amr.hourani@id.ethz.ch)
 * @author      Martin Hanusch (martin.hanusch@let.ethz.ch)
 * @author      Jürgen Zimmer (juergen.zimmer@edaktik.at)
 * @author      Andreas Hruska (andreas.hruska@edaktik.at)
 * @copyright   2016 ETHZ {@link http://ethz.ch/}
 * @copyright   2014 eDaktik GmbH {@link http://www.edaktik.at}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/edit_question_form.php');
require_once($CFG->dirroot . '/question/type/kprime/lib.php');
require_once($CFG->dirroot . '/question/engine/bank.php');

/**
 * Kprime editing form definition.
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_kprime_edit_form extends question_edit_form {

    private $numberofrows;

    private $numberofcolumns;

    /**
     * (non-PHPdoc).
     *
     * @see myquestion_edit_form::qtype()
     */
    public function qtype() {
        return 'kprime';
    }

    /**
     * Build the form definition.
     *
     * This adds all the form fields that the default question type supports.
     * If your question type does not support all these fields, then you can
     * override this method and remove the ones you don't want with $mform->removeElement().
     */
    protected function definition() {
        global $COURSE, $CFG, $DB;

        $qtype = $this->qtype();
        $langfile = "qtype_$qtype";

        $mform = $this->_form;

        // Standard fields at the start of the form.
        $mform->addElement('header', 'categoryheader', get_string('category', 'question'));

        if (!isset($this->question->id)) {
            if (!empty($this->question->formoptions->mustbeusable)) {
                $contexts = $this->contexts->having_add_and_use();
            } else {
                $contexts = $this->contexts->having_cap('moodle/question:add');
            }

            // Adding question.
            $mform->addElement('questioncategory', 'category', get_string('category', 'question'),
                    array('contexts' => $contexts
                    ));
        } else if (!($this->question->formoptions->canmove ||
                 $this->question->formoptions->cansaveasnew)) {
            // Editing question with no permission to move from category.
            $mform->addElement('questioncategory', 'category', get_string('category', 'question'),
                    array('contexts' => array($this->categorycontext
                    )
                    ));
            $mform->addElement('hidden', 'usecurrentcat', 1);
            $mform->setType('usecurrentcat', PARAM_BOOL);
            $mform->setConstant('usecurrentcat', 1);
        } else if (isset($this->question->formoptions->movecontext)) {
            // Moving question to another context.
            $mform->addElement('questioncategory', 'categorymoveto',
                    get_string('category', 'question'),
                    array('contexts' => $this->contexts->having_cap('moodle/question:add')
                    ));
            $mform->addElement('hidden', 'usecurrentcat', 1);
            $mform->setType('usecurrentcat', PARAM_BOOL);
            $mform->setConstant('usecurrentcat', 1);
        } else {
            // Editing question with permission to move from category or save as new q.
            $currentgrp = array();
            $currentgrp[0] = $mform->createElement('questioncategory', 'category',
                    get_string('categorycurrent', 'question'),
                    array('contexts' => array($this->categorycontext
                    )
                    ));
            if ($this->question->formoptions->canedit || $this->question->formoptions->cansaveasnew) {
                // Not move only form.
                $currentgrp[1] = $mform->createElement('checkbox', 'usecurrentcat', '',
                        get_string('categorycurrentuse', 'question'));
                $mform->setDefault('usecurrentcat', 1);
            }
            $currentgrp[0]->freeze();
            $currentgrp[0]->setPersistantFreeze(false);
            $mform->addGroup($currentgrp, 'currentgrp', get_string('categorycurrent', 'question'),
                    null, false);

            $mform->addElement('questioncategory', 'categorymoveto',
                    get_string('categorymoveto', 'question'),
                    array('contexts' => array($this->categorycontext
                    )
                    ));
            if ($this->question->formoptions->canedit || $this->question->formoptions->cansaveasnew) {
                // Not move only form.
                $mform->disabledIf('categorymoveto', 'usecurrentcat', 'checked');
            }
        }

        $mform->addElement('header', 'generalheader', get_string('general', 'form'));
        $mform->addElement('text', 'name', get_string('tasktitle', 'qtype_kprime'),
                array('size' => 50, 'maxlength' => 255
                ));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $mform->addElement('text', 'defaultmark', get_string('maxpoints', 'qtype_kprime'),
                array('size' => 7
                ));
        $mform->setType('defaultmark', PARAM_FLOAT);
        $mform->setDefault('defaultmark', 1);
        $mform->addRule('defaultmark', null, 'required', null, 'client');

        $mform->addElement('editor', 'questiontext', get_string('stem', 'qtype_kprime'),
                array('rows' => 15
                ), $this->editoroptions);
        $mform->setType('questiontext', PARAM_RAW);
        $mform->addRule('questiontext', null, 'required', null, 'client');
        $mform->setDefault('questiontext',
                array('text' => get_string('enterstemhere', 'qtype_kprime')
                ));

        $mform->addElement('editor', 'generalfeedback', get_string('generalfeedback', 'question'),
                array('rows' => 10
                ), $this->editoroptions);
        $mform->setType('generalfeedback', PARAM_RAW);
        $mform->addHelpButton('generalfeedback', 'generalfeedback', 'qtype_kprime');

        // Any questiontype specific fields.
        $this->definition_inner($mform);

        // TAGS - See API 3 https://docs.moodle.org/dev/Tag_API_3_Specification
        if (class_exists('core_tag_tag')) { // Started from moodle 3.1 but we dev for 2.6+.
            if (core_tag_tag::is_enabled('core_question', 'question')) {
                $mform->addElement('header', 'tagshdr', get_string('tags', 'tag'));
                $mform->addElement('tags', 'tags', get_string('tags'),
                        array('itemtype' => 'question', 'component' => 'core_question'
                        ));
            }
        }

        $this->add_interactive_settings(true, true);

        if (!empty($this->question->id)) {
            $mform->addElement('header', 'createdmodifiedheader',
                    get_string('createdmodifiedheader', 'question'));
            $a = new stdClass();
            if (!empty($this->question->createdby)) {
                $a->time = userdate($this->question->timecreated);
                $a->user = fullname(
                        $DB->get_record('user',
                                array('id' => $this->question->createdby
                                )));
            } else {
                $a->time = get_string('unknown', 'question');
                $a->user = get_string('unknown', 'question');
            }
            $mform->addElement('static', 'created', get_string('created', 'question'),
                    get_string('byandon', 'question', $a));
            if (!empty($this->question->modifiedby)) {
                $a = new stdClass();
                $a->time = userdate($this->question->timemodified);
                $a->user = fullname(
                        $DB->get_record('user',
                                array('id' => $this->question->modifiedby
                                )));
                $mform->addElement('static', 'modified', get_string('modified', 'question'),
                        get_string('byandon', 'question', $a));
            }
        }
        // Save and Keep Editing and Preview (if possible)
        // LMDL-133.
        global $PAGE;
        $buttonarray = array();
        $buttonarray[] = $mform->createElement('submit', 'updatebutton',
                get_string('savechangesandcontinueediting', 'question'));
        if ($this->can_preview()) {
            $previewlink = $PAGE->get_renderer('core_question')->question_preview_link(
                    $this->question->id, $this->context, true);
            $buttonarray[] = $mform->createElement('static', 'previewlink', '', $previewlink);
        }

        $mform->addGroup($buttonarray, 'updatebuttonar', '', array(' '
        ), false);
        $mform->closeHeaderBefore('updatebuttonar');

        if ((!empty($this->question->id)) && (!($this->question->formoptions->canedit ||
                 $this->question->formoptions->cansaveasnew))) {
            $mform->hardFreezeAllVisibleExcept(
                    array('categorymoveto', 'buttonar', 'currentgrp'
                    ));
        }

        $this->add_hidden_fields();
        $this->add_action_buttons();
    }

    /**
     * Adds question-type specific form fields.
     *
     * @param object $mform the form being built.
     */
    protected function definition_inner($mform) {
        $kprimeconfig = get_config('qtype_kprime');
        if (isset($this->question->options->rows) && count($this->question->options->rows) > 0) {
            $this->numberofrows = count($this->question->options->rows);
        } else {
            $this->numberofrows = QTYPE_KPRIME_NUMBER_OF_OPTIONS;
        }
        if (isset($this->question->options->columns) && count($this->question->options->columns) > 0) {
            $this->numberofcolumns = count($this->question->options->columns);
        } else {
            $this->numberofcolumns = QTYPE_KPRIME_NUMBER_OF_RESPONSES;
        }
        $this->editoroptions['changeformat'] = 1;
        $mform->addElement('hidden', 'numberofrows', $this->numberofrows);
        $mform->setType('numberofrows', PARAM_INT);
        $mform->addElement('hidden', 'numberofcolumns', $this->numberofcolumns);
        $mform->setType('numberofcolumns', PARAM_INT);

        $mform->addElement('header', 'scoringmethodheader',
                get_string('scoringmethod', 'qtype_kprime'));
        // Add the scoring method radio buttons.
        $attributes = array();
        $scoringbuttons = array();
        $scoringbuttons[] = &$mform->createElement('radio', 'scoringmethod', '',
                get_string('scoringkprime', 'qtype_kprime'), 'kprime', $attributes);
        $scoringbuttons[] = &$mform->createElement('radio', 'scoringmethod', '',
                get_string('scoringkprimeonezero', 'qtype_kprime'), 'kprimeonezero', $attributes);
        $scoringbuttons[] = &$mform->createElement('radio', 'scoringmethod', '',
                get_string('scoringsubpoints', 'qtype_kprime'), 'subpoints', $attributes);
        $mform->addGroup($scoringbuttons, 'radiogroupscoring',
                get_string('scoringmethod', 'qtype_kprime'), array(' <br/> '
                ), false);
        $mform->addHelpButton('radiogroupscoring', 'scoringmethod', 'qtype_kprime');
        $mform->setDefault('scoringmethod', 'kprime');

        // Add the shuffleanswers checkbox.
        $mform->addElement('advcheckbox', 'shuffleanswers',
                get_string('shuffleanswers', 'qtype_kprime'), null, null,
                array(0, 1
                ));
        $mform->addHelpButton('shuffleanswers', 'shuffleanswers', 'qtype_kprime');

        $mform->addElement('header', 'optionsandfeedbackheader',
                get_string('optionsandfeedback', 'qtype_kprime'));

        // Add the response text fields.
        $responses = array();
        for ($i = 1; $i <= $this->numberofcolumns; ++$i) {
            $label = '';
            if ($i == 1) {
                $label = get_string('responsetexts', 'qtype_kprime');
            }
            $mform->addElement('text', 'responsetext_' . $i, $label,
                    array('size' => 6
                    ));
            $mform->setType('responsetext_' . $i, PARAM_TEXT);
            $mform->addRule('responsetext_' . $i, null, 'required', null, 'client');

            if ($this->numberofcolumns == 2) {
                $mform->setDefault('responsetext_' . $i,
                        get_string('responsetext' . $i, 'qtype_kprime'));
            }
        }

        $responsetexts = array();
        if (isset($this->question->options->columns) && !empty($this->question->options->columns)) {
            foreach ($this->question->options->columns as $key => $column) {
                $responsetexts[] = format_text($column->responsetext, FORMAT_HTML);
            }
        } else {
            $responsetexts[] = get_string('responsetext1', 'qtype_kprime');
            $responsetexts[] = get_string('responsetext2', 'qtype_kprime');
        }

        // Add an option text editor, response radio buttons and a feedback editor for each option.
        for ($i = 1; $i <= $this->numberofrows; ++$i) {
            // Add the option editor.
            $mform->addElement('html', '<br/><br/>');
            $mform->addElement('html', '<div class="optionbox">'); // Open div.optionbox.
            $mform->addElement('html', '<div class="option_question">'); // Open div.option_question.
            $mform->addElement('html', '<div class="optionandresponses">'); // Open div.optionbox.
            $mform->addElement('html', '<div class="optiontext">'); // Open div.optiontext.

            $mform->addElement('editor', 'option_' . $i, get_string('optionno', 'qtype_kprime', $i), array('rows' => 8
            ), $this->editoroptions);
            $mform->setDefault('option_' . $i,
                    array('text' => get_string('enteroptionhere', 'qtype_kprime')
                    ));
            $mform->setType('option_' . $i, PARAM_RAW);
            $mform->addRule('option_' . $i, null, 'required', null, 'client');

            $mform->addElement('html', '</div>'); // Close div.optiontext.
            $mform->addElement('html', '</div>'); // Close div.optionsandresponses.
            // Add the feedback text editor in a new line.
            $mform->addElement('html', '<div class="feedbacktext">'); // Open div.feedbacktext.
            $mform->addElement('editor', 'feedback_' . $i, get_string('feedbackforoption', 'qtype_kprime', $i),
                    array('rows' => 2, 'placeholder' => ''
                    ), $this->editoroptions);
            $mform->setType('feedback_' . $i, PARAM_RAW);

            $mform->addElement('html', '</div>'); // Close div.feedbacktext.
            $mform->addElement('html', '</div>'); // Close div.option_question.

            $mform->addElement('html', '<div class="option_answer">');
            // Add the radio buttons for responses.
            $mform->addElement('html', '<div class="responses">'); // Open div.responses.
            $attributes = array();
            $radiobuttons = array();
            for ($j = 1; $j <= $this->numberofcolumns; ++$j) {
                if (array_key_exists($j - 1, $responsetexts)) {
                    $radiobuttons[] = &$mform->createElement('radio', 'weightbutton_' . $i, '',
                            $responsetexts[$j - 1], $j, $attributes);
                } else {
                    $radiobuttons[] = &$mform->createElement('radio', 'weightbutton_' . $i, '', '',
                            $j, $attributes);
                }
            }
            $mform->addGroup($radiobuttons, 'weightsarray_' . $i, '', array('<br/>'), false);
            $mform->setDefault('weightbutton_' . $i, 1);

            $mform->addElement('html', '</div>'); // Close div.responses.
            $mform->addElement('html', '</div>'); // Close div.option_answer.
            $mform->addElement('html', '</div>'); // Close div.optionbox.
        }

        $mform->addElement('hidden', 'qtype');
        $mform->setType('qtype', PARAM_ALPHA);
        $mform->addElement('hidden', 'makecopy');
        $mform->setType('makecopy', PARAM_ALPHA);
        $this->add_hidden_fields();
    }

    protected function get_hint_fields($withclearwrong = false, $withshownumpartscorrect = false) {
        list($repeated, $repeatedoptions) = parent::get_hint_fields($withclearwrong, $withshownumpartscorrect);
        $repeatedoptions['hintclearwrong']['disabledif'] = array('single', 'eq', 1);
        $repeatedoptions['hintshownumcorrect']['disabledif'] = array('single', 'eq', 1);
        return array($repeated, $repeatedoptions);
    }

    /**
     * (non-PHPdoc).
     *
     * @see question_edit_form::data_preprocessing()
     */
    protected function data_preprocessing($question) {
        $question = parent::data_preprocessing($question);
        $question = $this->data_preprocessing_hints($question, true, true);

        if (isset($question->options)) {
            $question->shuffleanswers = $question->options->shuffleanswers;
            $question->scoringmethod = $question->options->scoringmethod;
            $question->rows = $question->options->rows;
            $question->columns = $question->options->columns;
            $question->numberofrows = count($question->rows);
            $question->numberofcolumns = count($question->columns);
        }

        if (isset($this->question->id)) {
            $key = 1;
            foreach ($question->options->rows as $row) {
                // Restore all images in the option text.
                $draftid = file_get_submitted_draft_itemid('option_' . $key);
                $question->{'option_' . $key}['text'] = file_prepare_draft_area($draftid,
                        $this->context->id, 'qtype_kprime', 'optiontext',
                        !empty($row->id) ? (int) $row->id : null, $this->fileoptions,
                        $row->optiontext);
                $question->{'option_' . $key}['itemid'] = $draftid;

                // Now do the same for the feedback text.
                $draftid = file_get_submitted_draft_itemid('feedback_' . $key);
                $question->{'feedback_' . $key}['text'] = file_prepare_draft_area($draftid,
                        $this->context->id, 'qtype_kprime', 'feedbacktext',
                        !empty($row->id) ? (int) $row->id : null, $this->fileoptions,
                        $row->optionfeedback);
                $question->{'feedback_' . $key}['itemid'] = $draftid;

                ++$key;
            }
        }

        return $question;
    }

    /**
     * (non-PHPdoc).
     *
     * @see question_edit_form::validation()
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        // Check for empty option texts.
        for ($i = 1; $i <= $this->numberofrows; ++$i) {
            $optiontext = $data['option_' . $i]['text'];
            // LMDL-201.
            $optiontext = trim(strip_tags($optiontext, '<img><video><audio><iframe><embed>'));
            // Remove newlines.
            $optiontext = preg_replace("/[\r\n]+/i", '', $optiontext);
            // Remove whitespaces and tabs.
            $optiontext = preg_replace("/[\s\t]+/i", '', $optiontext);
            // Also remove UTF-8 non-breaking whitespaces.
            $optiontext = trim($optiontext, "\xC2\xA0\n");
            // Now check whether the string is empty.
            if (empty($optiontext)) {
                $errors['option_' . $i] = get_string('mustsupplyvalue', 'qtype_kprime');
            }
        }

        // Check for empty response texts.
        for ($j = 1; $j <= $this->numberofcolumns; ++$j) {
            if (trim(strip_tags($data['responsetext_' . $j])) == false) {
                $errors['responsetext_' . $j] = get_string('mustsupplyvalue', 'qtype_kprime');
            }
        }

        return $errors;
    }
}
