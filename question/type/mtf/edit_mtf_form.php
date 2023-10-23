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
 * qtype_mtf lib.
 *
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
 * qtype_mtf editing form definition.
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_mtf_edit_form extends question_edit_form {

    /** @var int numberofrows */
    private $numberofrows;

    /** @var int numberofcolumns */
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
     * This adds all the form fields that the default question type supports.
     * If your question type does not support all these fields, then you can
     * override this method and remove the ones you don't want with $mform->removeElement().
     */
    protected function definition() {
        global $DB, $PAGE;

        $mform = $this->_form;

        // Standard fields at the start of the form.
        $mform->addElement('header', 'generalheader', get_string("general", 'form'));

        if (!isset($this->question->id)) {
            if (!empty($this->question->formoptions->mustbeusable)) {
                $contexts = $this->contexts->having_add_and_use();
            } else {
                $contexts = $this->contexts->having_cap('moodle/question:add');
            }

            // Adding question.
            $mform->addElement('questioncategory', 'category', get_string('category', 'question'), array('contexts' => $contexts));
        } else if (!($this->question->formoptions->canmove || $this->question->formoptions->cansaveasnew)) {
            // Editing question with no permission to move from category.
            $mform->addElement('questioncategory', 'category', get_string('category', 'question'),
                            array('contexts' => array($this->categorycontext)));
            $mform->addElement('hidden', 'usecurrentcat', 1);
            $mform->setType('usecurrentcat', PARAM_BOOL);
            $mform->setConstant('usecurrentcat', 1);
        } else {
            // Editing question with permission to move from category or save as new q.
            $currentgrp = array();
            $currentgrp[0] = $mform->createElement('questioncategory', 'category', get_string('categorycurrent', 'question'),
                                                array('contexts' => array($this->categorycontext)));
            // Validate if the question is being duplicated.
            $beingcopied = false;
            if (isset($this->question->beingcopied)) {
                $beingcopied = $this->question->beingcopied;
            }
            if (($this->question->formoptions->canedit || $this->question->formoptions->cansaveasnew) && ($beingcopied)) {
                // Not move only form.
                $currentgrp[1] = $mform->createElement('checkbox', 'usecurrentcat', '',
                                                    get_string('categorycurrentuse', 'question'));
                $mform->setDefault('usecurrentcat', 1);
            }
            $currentgrp[0]->freeze();
            $currentgrp[0]->setPersistantFreeze(false);
            $mform->addGroup($currentgrp, 'currentgrp', get_string('categorycurrent', 'question'), null, false);

            if (($beingcopied)) {
                $mform->addElement('questioncategory', 'categorymoveto', get_string('categorymoveto', 'question'),
                                array('contexts' => array($this->categorycontext)));
                if ($this->question->formoptions->canedit || $this->question->formoptions->cansaveasnew) {
                    // Not move only form.
                    $mform->disabledIf('categorymoveto', 'usecurrentcat', 'checked');
                }
            }
        }

        if (class_exists('qbank_editquestion\\editquestion_helper') && !empty($this->question->id) && !$this->question->beingcopied) {
            // Add extra information from plugins when editing a question (e.g.: Authors, version control and usage).
            $functionname = 'edit_form_display';
            $questiondata = [];
            $plugins = get_plugin_list_with_function('qbank', $functionname);
            foreach ($plugins as $componentname => $plugin) {
                $element = new StdClass();
                $element->pluginhtml = component_callback($componentname, $functionname, [$this->question]);
                $questiondata['editelements'][] = $element;
            }
            $mform->addElement('static', 'versioninfo', get_string('versioninfo', 'qbank_editquestion'),
                            $PAGE->get_renderer('qbank_editquestion')->render_question_info($questiondata));
        }

        $mform->addElement('text', 'name', get_string('tasktitle', 'qtype_mtf'), array('size' => 50, 'maxlength' => 255));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $mform->addElement('float', 'defaultmark', get_string('maxpoints', 'qtype_mtf'), array('size' => 7));
        $mform->setDefault('defaultmark', $this->get_default_value('defaultmark', 1));
        $mform->addRule('defaultmark', null, 'required', null, 'client');

        $mform->addElement('editor', 'questiontext', get_string('stem', 'qtype_mtf'), array('rows' => 15), $this->editoroptions);
        $mform->setType('questiontext', PARAM_RAW);
        $mform->addRule('questiontext', null, 'required', null, 'client');
        $mform->setDefault('questiontext', array('text' => get_string('enterstemhere', 'qtype_mtf')));

        if (class_exists('qbank_editquestion\\editquestion_helper')) {
            $mform->addElement('select', 'status', get_string('status', 'qbank_editquestion'),
                            \qbank_editquestion\editquestion_helper::get_question_status_list());
        }
        $mform->addElement('editor', 'generalfeedback', get_string('generalfeedback', 'question'), array('rows' => 10),
                        $this->editoroptions);
        $mform->setType('generalfeedback', PARAM_RAW);
        $mform->addHelpButton('generalfeedback', 'generalfeedback', 'question');
        $mform->addElement('select', 'answernumbering', get_string('answernumbering', 'qtype_mtf'),
                        qtype_mtf::get_numbering_styles());
        if (!empty($this->question->options->answernumbering)) {
            $mform->setDefault('answernumbering', array($this->question->options->answernumbering));
        } else {
            $mform->setDefault('answernumbering', 'answernumberingnone');
        }
        $mform->addElement('text', 'idnumber', get_string('idnumber', 'question'), 'maxlength="100"  size="10"');
        $mform->addHelpButton('idnumber', 'idnumber', 'question');
        $mform->setType('idnumber', PARAM_RAW);

        // Any questiontype specific fields.
        $this->definition_inner($mform);
        $this->add_interactive_settings();

        if (core_tag_tag::is_enabled('core_question', 'question') && class_exists('qbank_tagquestion\\tags_action_column') &&
             \core\plugininfo\qbank::is_plugin_enabled('qbank_tagquestion')) {
            $this->add_tag_fields($mform);
        }

        if (!empty($this->customfieldpluginenabled) && $this->customfieldpluginenabled) {
            // Add custom fields to the form.
            $this->customfieldhandler = qbank_customfields\customfield\question_handler::create();
            $this->customfieldhandler->set_parent_context($this->categorycontext); // For question handler only.
            $this->customfieldhandler->instance_form_definition($mform, empty($this->question->id) ? 0 : $this->question->id);
        }

        $this->add_hidden_fields();

        $mform->addElement('hidden', 'qtype');
        $mform->setType('qtype', PARAM_ALPHA);

        $mform->addElement('hidden', 'makecopy');
        $mform->setType('makecopy', PARAM_INT);

        $buttonarray = array();
        $buttonarray[] = $mform->createElement('submit', 'updatebutton', get_string('savechangesandcontinueediting', 'question'));
        if ($this->can_preview()) {
            if (class_exists('qbank_editquestion\\editquestion_helper')) {
                if (\core\plugininfo\qbank::is_plugin_enabled('qbank_previewquestion')) {
                    $previewlink = $PAGE->get_renderer('qbank_previewquestion')->question_preview_link($this->question->id,
                                                                                                    $this->context, true);
                    $buttonarray[] = $mform->createElement('static', 'previewlink', '', $previewlink);
                }
            } else {
                $previewlink = $PAGE->get_renderer('core_question')->question_preview_link($this->question->id, $this->context, true);
                $buttonarray[] = $mform->createElement('static', 'previewlink', '', $previewlink);
            }
        }

        $mform->addGroup($buttonarray, 'updatebuttonar', '', array(' '), false);
        $mform->closeHeaderBefore('updatebuttonar');

        $this->add_action_buttons(true, get_string('savechanges'));

        if ((!empty($this->question->id)) && (!($this->question->formoptions->canedit || $this->question->formoptions->cansaveasnew))) {
            $mform->hardFreezeAllVisibleExcept(array('categorymoveto', 'buttonar', 'currentgrp'));
        }
    }

    /**
     * Adds question-type specific form fields.
     *
     * @param object $mform
     *        the form being built.
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
        $mform->addElement('hidden', 'numberofcolumns', '2', array('id' => 'id_numberofcolumns'));
        $mform->setType('numberofcolumns', PARAM_INT);

        // Keep state of numberofrows to validate correctly on submission.
        $mform->addElement('hidden', 'numberofrows');
        $mform->setType('numberofrows', PARAM_INT);
        $mform->setDefault('numberofrows', $this->numberofrows);

        $mform->addElement('header', 'scoringmethodheader', get_string('scoringmethod', 'qtype_mtf'));

        // Add the scoring method radio buttons.
        $attributes = array();
        $scoringbuttons = array();

        $scoringbuttons[] = &$mform->createElement('radio', 'scoringmethod', '', get_string('scoringsubpoints', 'qtype_mtf'),
                                                'subpoints', $attributes);
        if (get_config('qtype_mtf')->allowdeduction) {
            $scoringbuttons[] = &$mform->createElement('radio', 'scoringmethod', '', get_string('scoringsubpointdeduction', 'qtype_mtf'),
                'subpointdeduction', $attributes);
        }
        $scoringbuttons[] = &$mform->createElement('radio', 'scoringmethod', '', get_string('scoringmtfonezero', 'qtype_mtf'),
                                                'mtfonezero', $attributes);

        $mform->addGroup($scoringbuttons, 'radiogroupscoring', get_string('scoringmethod', 'qtype_mtf'), array(' <br/> '), false);
        $mform->addHelpButton('radiogroupscoring', 'scoringmethod', 'qtype_mtf');
        $mform->setDefault('scoringmethod', 'subpoints');
        $mform->addElement('advcheckbox', 'shuffleanswers', get_string('shuffleanswers', 'qtype_mtf'), null, null, array(0, 1));
        $mform->addHelpButton('shuffleanswers', 'shuffleanswers', 'qtype_mtf');

        if (get_config('qtype_mtf')->allowdeduction) {
            $mform->addElement('text', 'deduction', get_string('deduction', 'qtype_mtf'), array('size' => 6));
            $mform->addHelpButton('deduction', 'deduction', 'qtype_mtf');
            $mform->setDefault('deduction', '0.5');
            $mform->setType('deduction', PARAM_FLOAT);
            $mform->hideIf('deduction', 'scoringmethod', 'neq', 'subpointdeduction');
        } else {
            $mform->addElement('hidden', 'deduction');
            $mform->setType('deduction', PARAM_FLOAT);
            $mform->setDefault('deduction', '0');
        }

        $mform->addElement('header', 'answerhdr', get_string('optionsandfeedback', 'qtype_mtf'), '');
        $mform->setExpanded('answerhdr', 1);

        // Add the response text fields.
        $mform->addElement('html', '<span id="judgmentoptionsspan">');

        for ($li = 1; $li <= 2; ++$li) {
            $responsetextslabel = '';

            if ($li == 1) {
                $responsetextslabel = get_string('responsetexts', 'qtype_mtf');
            }

            $mform->addElement('text', 'responsetext_' . $li, $responsetextslabel, array('size' => 6));
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

        $this->add_per_answer_fields($mform, get_string('optionno', 'qtype_mtf', '{no}'), 0, $this->numberofrows, 3);

        // Keep state of number of options to warn user if they go lower.
        $mform->addElement('hidden', 'qtype_mtf_lastnumberofcols');
        $mform->setType('qtype_mtf_lastnumberofcols', PARAM_INT);
        $mform->setDefault('qtype_mtf_lastnumberofcols', $this->numberofrows);

    }

    /**
     * JS Call function
     */
    public function js_call() {
        global $PAGE;

        foreach (array_keys(get_string_manager()->load_component_strings('qtype_mtf', current_language())) as $string) {
            $PAGE->requires->string_for_js($string, 'qtype_mtf');
        }
        $PAGE->requires->jquery();
        $PAGE->requires->yui_module('moodle-qtype_mtf-form', '', array(0));
    }

    /**
     * Add a set of form fields, obtained from get_per_answer_fields, to the form,
     * one for each existing answer, with some blanks for some new ones.
     *
     * @param object $mform
     *        the form being built.
     * @param string $label
     *        the label to use for each option.
     * @param array $gradeoptions
     *        the possible grades for each answer.
     * @param int $minoptions
     *        the minimum number of answer blanks to display. Default QUESTION_NUMANS_START.
     * @param int $addoptions
     *        the number of answer blanks to add. Default QUESTION_NUMANS_ADD.
     */
    protected function add_per_answer_fields(&$mform, $label, $gradeoptions, $minoptions = QUESTION_NUMANS_START, $addoptions = QUESTION_NUMANS_ADD) {
        $answersoption = '';
        $repeatedoptions = array();
        $repeated = $this->get_per_answer_fields($mform, $label, $gradeoptions, $repeatedoptions, $answersoption);

        if (isset($this->question->options->rows)) {
            $repeatsatstart = count($this->question->options->rows);
        } else {
            $repeatsatstart = $minoptions;
        }

        $this->repeat_elements($repeated, $repeatsatstart, $repeatedoptions, 'numberofrows', 'addanswers', $addoptions,
                            $this->get_more_choices_string(), true);
    }

    /**
     * Get the list of form elements to repeat, one for each answer.
     *
     * @param object $mform
     *        the form being built.
     * @param string $label
     *        the label to use for each option.
     * @param array $gradeoptions
     *        the possible grades for each answer.
     * @param array $repeatedoptions
     *        reference to array of repeated options to fill
     * @param string $answersoption
     *        reference to return the name of $question->options field holding an array of answers
     * @return array of form fields.
     */
    protected function get_per_answer_fields($mform, $label, $gradeoptions, &$repeatedoptions, &$answersoption) {
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
        $responses[] = $mform->createElement('editor', 'option', $label, array('rows' => 3), $this->editoroptions);

        $responses[] = $mform->createElement('html', '</div>');
        $responses[] = $mform->createElement('html', '</div>');

        // Add the feedback text editor in a new line.
        $responses[] = $mform->createElement('html', '<div class="feedbacktext">');
        $responses[] = $mform->createElement('editor', 'feedback', get_string('feedbackforoption', 'qtype_mtf') . ' ' . $label,
                                            array('rows' => 1, 'placeholder' => ''), $this->editoroptions);
        $responses[] = $mform->createElement('html', '</div>');
        $responses[] = $mform->createElement('html', '</div>');
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
            $attributes = array('data-colmtf' => $negativeorpositive);

            if (property_exists((object)$this->responsetexts, $j - 1)) {
                $radiobuttons[] = &$mform->createElement('radio', $radiobuttonname, '', $this->responsetexts[$j - 1], $j,
                                                        $attributes);
            } else {
                $radiobuttons[] = &$mform->createElement('radio', $radiobuttonname, '', '', $j, $attributes);
            }
        }

        $responses[] = $mform->createElement('group', 'radiobuttonsgroupname', '', $radiobuttons, array('<br/>'), false);
        $responses[] = $mform->createElement('html', '</div>');
        $responses[] = $mform->createElement('html', '</div>');
        $responses[] = $mform->createElement('html', '</div>');

        $repeatedoptions['feedback']['type'] = PARAM_RAW;
        $repeatedoptions[$radiobuttonname]['default'] = 2;
        $repeatedoptions['option']['type'] = PARAM_RAW;
        $repeatedoptions['option']['text'] = get_string('enteroptionhere', 'qtype_mtf');
        $answersoption = 'option';

        return $responses;
    }

    /**
     * Create the form elements required by one hint.
     *
     * @param string $withclearwrong
     *        whether this quesiton type uses the 'Clear wrong' option on hints.
     * @param string $withshownumpartscorrect
     *        whether this quesiton type uses the 'Show num parts correct' option on hints.
     * @return array form field elements for one hint.
     */
    protected function get_hint_fields($withclearwrong = false, $withshownumpartscorrect = false) {
        list($repeated, $repeatedoptions) = parent::get_hint_fields(false, false);
        return array($repeated, $repeatedoptions);
    }

    /**
     * Perform an preprocessing needed on the data passed to set_data()
     * before it is used to initialise the form.
     *
     * @param object $question
     * @return object $question
     */
    protected function data_preprocessing($question) {
        $question = parent::data_preprocessing($question);
        $question = $this->data_preprocessing_hints($question, true, true);

        if (isset($question->options)) {
            $question->shuffleanswers = $question->options->shuffleanswers;
            $question->scoringmethod = $question->options->scoringmethod;
            $question->deduction = $question->options->deduction;
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
                $question->{"option[$key]"}['text'] = file_prepare_draft_area($draftid, $this->context->id, 'qtype_mtf',
                                                                            'optiontext', !empty($row->id) ? (int)$row->id : null,
                                                                            $this->fileoptions, $row->optiontext);
                $question->{"option[$key]"}['itemid'] = $draftid;

                unset($this->_form->_defaultValues["weightbutton[{$key}]"]);

                // Now do the same for the feedback text.
                $draftid = file_get_submitted_draft_itemid("feedback[$key]");
                $question->{"feedback[$key]"}['text'] = file_prepare_draft_area($draftid, $this->context->id, 'qtype_mtf',
                                                                            'feedbacktext', !empty($row->id) ? (int)$row->id : null,
                                                                            $this->fileoptions, $row->optionfeedback);
                $question->{"feedback[$key]"}['itemid'] = $draftid;

                ++$key;
            }
        }
        $this->js_call();
        return $question;
    }

    /**
     * Validates the form.
     *
     * @param array $data
     * @param array $files
     * @return array
     * @throws coding_exception
     */
    public function validation($data, $files) {
        global $DB;

        $errors = parent::validation($data, $files);
        // Make sure that the user can edit the question.
        if (empty($data['makecopy']) && isset($this->question->id)
            && !$this->question->formoptions->canedit) {
            $errors['currentgrp'] = get_string('nopermissionedit', 'question');
        }

        // Category.
        if (empty($data['category'])) {
            // User has provided an invalid category.
            $errors['category'] = get_string('required');
        }

        // Default mark.
        if (array_key_exists('defaultmark', $data) && $data['defaultmark'] < 0) {
            $errors['defaultmark'] = get_string('defaultmarkmustbepositive', 'question');
        }

        // Can only have one idnumber per category.
        if (strpos($data['category'], ',') !== false) {
            list($category, $categorycontextid) = explode(',', $data['category']);
        } else {
            $category = $data['category'];
        }
        if (isset($data['idnumber']) && ((string) $data['idnumber'] !== '')) {
            if (empty($data['usecurrentcat']) && !empty($data['categorymoveto'])) {
                $categoryinfo = $data['categorymoveto'];
            } else {
                $categoryinfo = $data['category'];
            }
            list($categoryid, $notused) = explode(',', $categoryinfo);
            $conditions = 'questioncategoryid = ? AND idnumber = ?';
            $params = [$categoryid, $data['idnumber']];
            if (!empty($this->question->id)) {
                // Get the question bank entry id to not check the idnumber for the same bank entry.
                $sql = "SELECT DISTINCT qbe.id
                          FROM {question_versions} qv
                          JOIN {question_bank_entries} qbe ON qbe.id = qv.questionbankentryid
                         WHERE qv.questionid = ?";
                $bankentry = $DB->get_record_sql($sql, ['id' => $this->question->id]);
                $conditions .= ' AND id <> ?';
                $params[] = $bankentry->id;
            }

            if ($DB->record_exists_select('question_bank_entries', $conditions, $params)) {
                $errors['idnumber'] = get_string('idnumbertaken', 'error');
            }
        }

        if ($this->customfieldpluginenabled) {
            // Add the custom field validation.
            $errors  = array_merge($errors, $this->customfieldhandler->instance_form_validation($data, $files));
        }
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

        // If deduction is set, it must be >= 0 and <= 1
        if (isset($data['deduction'])) {
            $deduction = $data['deduction'];
            if ($deduction < 0 || $deduction > 1) {
                $errors['deduction'] = get_string('invaliddeduction', 'qtype_mtf');
            }
        }

        // If admin has disallowed deductions, scoring method cannot be subpoints with deductions
        if (get_config('qtype_mtf', 'allowdeduction') === '0') {
            if (!isset($data['scoringmethod'])) {
                $errors['radiogroupscoring'] = get_string('cannotusedeductions', 'qtype_mtf');
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
