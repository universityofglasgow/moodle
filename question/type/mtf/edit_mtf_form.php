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
 * @package     qtype_mtf
 * @author      Amr Hourani (amr.hourani@id.ethz.ch)
 * @author      Martin Hanusch (martin.hanusch@let.ethz.ch)
 * @copyright   2016 ETHZ {@link http://ethz.ch/}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/edit_question_form.php');
require_once($CFG->dirroot . '/question/type/mtf/lib.php');
require_once($CFG->dirroot . '/question/engine/bank.php');

/**
 * Mtf editing form definition.
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class qtype_mtf_edit_form extends question_edit_form {

    private $numberofrows;

    private $numberofcolumns;

    /**
     * (non-PHPdoc).
     *
     * @see myquestion_edit_form::qtype()
     */
    public function qtype() {
        return 'mtf';
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
        $mform->addElement('text', 'name', get_string('tasktitle', 'qtype_mtf'),
                array('size' => 50, 'maxlength' => 255
                ));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $mform->addElement('text', 'defaultmark', get_string('maxpoints', 'qtype_mtf'),
                array('size' => 7
                ));
        $mform->setType('defaultmark', PARAM_FLOAT);
        $mform->setDefault('defaultmark', 1);
        $mform->addRule('defaultmark', null, 'required', null, 'client');

        $mform->addElement('editor', 'questiontext', get_string('stem', 'qtype_mtf'),
                array('rows' => 15
                ), $this->editoroptions);
        $mform->setType('questiontext', PARAM_RAW);
        $mform->addRule('questiontext', null, 'required', null, 'client');
        $mform->setDefault('questiontext',
                array('text' => get_string('enterstemhere', 'qtype_mtf')
                ));

        $mform->addElement('editor', 'generalfeedback', get_string('generalfeedback', 'question'),
                array('rows' => 10
                ), $this->editoroptions);
        $mform->setType('generalfeedback', PARAM_RAW);
        $mform->addHelpButton('generalfeedback', 'generalfeedback', 'qtype_mtf');
        $mform->addElement('select', 'answernumbering', get_string('answernumbering', 'qtype_mtf'),
                qtype_mtf::get_numbering_styles());
        if (!empty($this->question->options->answernumbering)) {
            $mform->setDefault('answernumbering',
                    array($this->question->options->answernumbering
                    ));
        }
        // Any questiontype specific fields.
        $this->definition_inner($mform);

        // TAGS - See API 3 https://docs.moodle.org/dev/Tag_API_3_Specification.
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
        $mtfconfig = get_config('qtype_mtf');

        if (isset($this->question->options->rows) && count($this->question->options->rows) > 0) {
            $this->numberofrows = count($this->question->options->rows);
        } else {
            $this->numberofrows = 4;
        }
        if (isset($this->question->options->columns) && count($this->question->options->columns) > 0) {
            $this->numberofcolumns = count($this->question->options->columns);
        } else {
            $this->numberofcolumns = 2;
        }
        $this->editoroptions['changeformat'] = 1;
        $mform->addElement('hidden', 'numberofcolumns', '2',
                array('id' => 'id_numberofcolumns'
                ));
        $mform->setType('numberofcolumns', PARAM_INT);

        // Keep state of numberofrows to validate correctly on submission.
        $mform->addElement('hidden', 'numberofrows');
        $mform->setType('numberofrows', PARAM_INT);
        $mform->setDefault('numberofrows', $this->numberofrows);

        $mform->addElement('header', 'scoringmethodheader',
                get_string('scoringmethod', 'qtype_mtf'));
        // Add the scoring method radio buttons.
        $attributes = array();
        $scoringbuttons = array();
        $scoringbuttons[] = &$mform->createElement('radio', 'scoringmethod', '',
                get_string('scoringsubpoints', 'qtype_mtf'), 'subpoints', $attributes);
        $scoringbuttons[] = &$mform->createElement('radio', 'scoringmethod', '',
                get_string('scoringmtfonezero', 'qtype_mtf'), 'mtfonezero', $attributes);
        $mform->addGroup($scoringbuttons, 'radiogroupscoring',
                get_string('scoringmethod', 'qtype_mtf'), array(' <br/> '
                ), false);
        $mform->addHelpButton('radiogroupscoring', 'scoringmethod', 'qtype_mtf');
        $mform->setDefault('scoringmethod', 'subpoints');

        // Add the shuffleanswers checkbox.
        $mform->addElement('advcheckbox', 'shuffleanswers',
                get_string('shuffleanswers', 'qtype_mtf'), null, null, array(0, 1
                ));
        $mform->addHelpButton('shuffleanswers', 'shuffleanswers', 'qtype_mtf');
        $mform->addElement('header', 'answerhdr', get_string('optionsandfeedback', 'qtype_mtf'), '');
        $mform->setExpanded('answerhdr', 1);

        // Add the response text fields.
        $mform->addElement('html', '<span id="judgmentoptionsspan">');

        for ($li = 1; $li <= 2; ++$li) {
            $responsetextslabel = '';
            if ($li == 1) {
                $responsetextslabel = get_string('responsetexts', 'qtype_mtf');
            }
            $mform->addElement('text', 'responsetext_' . $li, $responsetextslabel,
                    array('size' => 6
                    ));
            $mform->setType('responsetext_' . $li, PARAM_TEXT);
            $mform->addRule('responsetext_' . $li, null, 'required', null, 'client');
            $mform->setDefault('responsetext_' . $li, get_string('responsetext' . $li, 'qtype_mtf'));
        }
        $mform->addElement('html', '</span>');

        $this->responsetexts = array();
        if (isset($this->question->options->columns) && !empty($this->question->options->columns)) {
            foreach ($this->question->options->columns as $key => $column) {
                $this->responsetexts[] = format_text($column->responsetext, FORMAT_HTML);
            }
            // What if only one col? have the max just in case.
            if (count($this->responsetexts)) {
                for ($i = count($this->question->options->columns) + 1; $i <= QTYPE_MTF_NUMBER_OF_RESPONSES; $i++) {
                    // Always default it to second options values...
                    $this->responsetexts[] = get_string('responsetext2', 'qtype_mtf');
                }
            }
        } else {
            $this->responsetexts[] = get_string('responsetext1', 'qtype_mtf');
            $this->responsetexts[] = get_string('responsetext2', 'qtype_mtf');
        }

        $this->add_per_answer_fields($mform, get_string('optionno', 'qtype_mtf', '{no}'), 0,
                $this->numberofrows, 3);
        $mform->addElement('hidden', 'qtype');
        $mform->setType('qtype', PARAM_ALPHA);

        // Keep state of number of options to warn user if they go lower.
        $mform->addElement('hidden', 'qtype_mtf_lastnumberofcols');
        $mform->setType('qtype_mtf_lastnumberofcols', PARAM_INT);
        $mform->setDefault('qtype_mtf_lastnumberofcols', $this->numberofrows);
        $mform->addElement('hidden', 'makecopy');
        $mform->setType('makecopy', PARAM_ALPHA);

        $this->add_hidden_fields();
    }

    public function js_call() {
        global $PAGE;
        foreach (array_keys(
                get_string_manager()->load_component_strings('qtype_mtf', current_language())) as $string) {
            $PAGE->requires->string_for_js($string, 'qtype_mtf');
        }
        $PAGE->requires->jquery();
        $PAGE->requires->yui_module('moodle-qtype_mtf-form', '', array(0
        ));
    }

    /**
     * Add a set of form fields, obtained from get_per_answer_fields, to the form,
     * one for each existing answer, with some blanks for some new ones.
     *
     * @param object $mform the form being built.
     * @param $label the label to use for each option.
     * @param $gradeoptions the possible grades for each answer.
     * @param $minoptions the minimum number of answer blanks to display.
     *        Default QUESTION_NUMANS_START.
     * @param $addoptions the number of answer blanks to add. Default QUESTION_NUMANS_ADD.
     */
    protected function add_per_answer_fields(&$mform, $label, $gradeoptions,
            $minoptions = QUESTION_NUMANS_START, $addoptions = QUESTION_NUMANS_ADD) {
        $answersoption = '';
        $repeatedoptions = array();
        $repeated = $this->get_per_answer_fields($mform, $label, $gradeoptions, $repeatedoptions,
                $answersoption);

        if (isset($this->question->options->rows)) {
            $repeatsatstart = count($this->question->options->rows);
        } else {
            $repeatsatstart = $minoptions;
        }

        $this->repeat_elements($repeated, $repeatsatstart, $repeatedoptions, 'numberofrows',
                'addanswers', $addoptions, $this->get_more_choices_string(), true);
    }

    protected function get_per_answer_fields($mform, $label, $gradeoptions, &$repeatedoptions,
            &$answersoption) {
        $responses = array();

        // Add an option text editor, response radio buttons and a feedback editor for each option.
        preg_match_all('!\d+!', $label, $matches);
        $thecurrenteditorcounter = $matches[0];
        if (!$thecurrenteditorcounter) {
            $i = 1;
        } else {
            $i = $thecurrenteditorcounter;
        }
        $responses[] = $mform->createElement('html', '<br/><br/>');
        $mform->setDefault('numberofrows', $i);
        // Add the option editor.
        $responses[] = $mform->createElement('html', '<div class="optionbox" id="qtype_mtf_optionbox_response' . '">');
        $responses[] = $mform->createElement('html', '<div class="option_question">');
        $responses[] = $mform->createElement('html', '<div class="optionandresponses">');
        $responses[] = $mform->createElement('html', '<div class="optiontext">');
        $responses[] = $mform->createElement('editor', 'option', $label,
                array('rows' => 3
                ), $this->editoroptions);

        $responses[] = $mform->createElement('html', '</div>');
        $responses[] = $mform->createElement('html', '</div>'); // Close div.optionsandresponses.

        // Add the feedback text editor in a new line.
        $responses[] = $mform->createElement('html', '<div class="feedbacktext">');
        $responses[] = $mform->createElement('editor', 'feedback',
                get_string('feedbackforoption', 'qtype_mtf') . ' ' . $label,
                        array('rows' => 1, 'placeholder' => ''
                        ), $this->editoroptions);
        $responses[] = $mform->createElement('html', '</div>'); // Close div.feedbacktext.
        $responses[] = $mform->createElement('html', '</div>'); // Close div.option_question.
        $responses[] = $mform->createElement('html', '<div class="option_answer">');
        // Add the radio buttons for responses.
        $responses[] = $mform->createElement('html', '<div class="responses">');
        $radiobuttons = array();
        $radiobuttonname = 'weightbutton';
        for ($j = 1; $j <= 2; ++$j) {
            if ($j == 1) {
                $negativeorpositive = 'positive'; // Usually TRUE.
            } else {
                $negativeorpositive = 'negative'; // Usually FALSE.
            }
            $attributes = array('data-colmtf' => $negativeorpositive
            );

            if (array_key_exists($j - 1, $this->responsetexts)) {
                $radiobuttons[] = &$mform->createElement('radio', $radiobuttonname, '',
                        $this->responsetexts[$j - 1], $j, $attributes);
            } else {
                $radiobuttons[] = &$mform->createElement('radio', $radiobuttonname, '', '', $j,
                        $attributes);
            }
        }
        $responses[] = $mform->createElement('group', 'radiobuttonsgroupname', '', $radiobuttons,
                array('<br/>'
                ), false);
        $responses[] = $mform->createElement('html', '</div>'); // Close div.responses.
        $responses[] = $mform->createElement('html', '</div>'); // Close div.option_answer.
        $responses[] = $mform->createElement('html', '</div>'); // Close div.optionbox.

        $repeatedoptions['feedback']['type'] = PARAM_RAW;
        $repeatedoptions[$radiobuttonname]['default'] = 2;
        $repeatedoptions['option']['type'] = PARAM_RAW;
        $repeatedoptions['option']['text'] = get_string('enteroptionhere', 'qtype_mtf');
        $answersoption = 'option';
        return $responses;
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
            $question->numberofrows = $question->options->numberofrows;
            $question->numberofcolumns = $question->options->numberofcolumns;
        }

        if (isset($this->question->id)) {
            $key = 0;

            foreach ($question->options->rows as $row) {
                // Restore all images in the option text.
                $draftid = file_get_submitted_draft_itemid("option[$key]");
                $question->{"option[$key]"}['text'] = file_prepare_draft_area($draftid,
                        $this->context->id, 'qtype_mtf', 'optiontext',
                        !empty($row->id) ? (int) $row->id : null, $this->fileoptions,
                        $row->optiontext);
                $question->{"option[$key]"}['itemid'] = $draftid;

                unset($this->_form->_defaultValues["weightbutton[{$key}]"]);

                // Now do the same for the feedback text.
                $draftid = file_get_submitted_draft_itemid("feedback[$key]");
                $question->{"feedback[$key]"}['text'] = file_prepare_draft_area($draftid,
                        $this->context->id, 'qtype_mtf', 'feedbacktext',
                        !empty($row->id) ? (int) $row->id : null, $this->fileoptions,
                        $row->optionfeedback);
                $question->{"feedback[$key]"}['itemid'] = $draftid;

                ++$key;
            }
        }
        $this->js_call();
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
        $countfulloption = 0;
        for ($i = 1; $i <= count($data["option"]); ++$i) {
            $optiontext = $data["option"][$i - 1]['text'];
            // Remove HTML tags.
            $optiontext = trim(strip_tags($optiontext, '<img><video><audio><iframe><embed>'));
            // Remove newlines.
            $optiontext = preg_replace("/[\r\n]+/i", '', $optiontext);
            // Remove whitespaces and tabs.
            $optiontext = preg_replace("/[\s\t]+/i", '', $optiontext);
            // Also remove UTF-8 non-breaking whitespaces.
            $optiontext = trim($optiontext, "\xC2\xA0\n");
            // Now check whether the string is empty.
            if (!empty($optiontext)) {
                $countfulloption++;
            }
        }

        if ($countfulloption == 0) {
            $errors["option[0]"] = get_string('notenoughanswers', 'qtype_mtf', 1);
        }
        // Check for empty response texts.
        for ($j = 1; $j <= $data['numberofcolumns']; ++$j) {
            if (trim(strip_tags($data["responsetext_" . $j])) == false) {
                $errors["responsetext_" . $j] = get_string('mustsupplyvalue', 'qtype_mtf');
            }
        }

        return $errors;
    }
}
