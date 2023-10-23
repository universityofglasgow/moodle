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
 * @package    qtype
 * @subpackage musictheory
 * @copyright  2013 Eric Brisson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

/**
 * The musictheory question type.
 *
 * @copyright  2013 Eric Brisson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_musictheory extends question_type {

    public function extra_question_fields() {
        return array(
            'qtype_musictheory',
            'musictheory_musicqtype',
            'musictheory_gradingstrategy',
            'musictheory_optionsxml',
            'musictheory_randomoptionsxml'
        );
    }

    protected function make_question_instance($questiondata) {
        question_bank::load_question_definition_classes($this->name());

        switch ($questiondata->options->musictheory_musicqtype) {
            case 'note-write':
                $class = 'qtype_musictheory_note_write';
                break;
            case 'note-write-random';
                $class = 'qtype_musictheory_note_write_random';
                break;
            case 'note-identify':
                $class = 'qtype_musictheory_note_identify';
                break;
            case 'note-identify-random':
                $class = 'qtype_musictheory_note_identify_random';
                break;
            case 'keyboard-input':
                $class = 'qtype_musictheory_keyboard_input';
                break;
            case 'keysignature-write':
                $class = 'qtype_musictheory_keysignature_write';
                break;
            case 'keysignature-write-random':
                $class = 'qtype_musictheory_keysignature_write_random';
                break;
            case 'keysignature-identify':
                $class = 'qtype_musictheory_keysignature_identify';
                break;
            case 'keysignature-identify-random':
                $class = 'qtype_musictheory_keysignature_identify_random';
                break;
            case 'interval-write':
                $class = 'qtype_musictheory_interval_write';
                break;
            case 'interval-write-random':
                $class = 'qtype_musictheory_interval_write_random';
                break;
            case 'interval-identify':
                $class = 'qtype_musictheory_interval_identify';
                break;
            case 'interval-identify-random':
                $class = 'qtype_musictheory_interval_identify_random';
                break;
            case 'scale-write':
                $class = 'qtype_musictheory_scale_write';
                break;
            case 'scale-write-random':
                $class = 'qtype_musictheory_scale_write_random';
                break;
            case 'scale-identify':
                $class = 'qtype_musictheory_scale_identify';
                break;
            case 'scale-identify-random':
                $class = 'qtype_musictheory_scale_identify_random';
                break;
            case 'chordquality-write':
                $class = 'qtype_musictheory_chordquality_write';
                break;
            case 'chordquality-write-random':
                $class = 'qtype_musictheory_chordquality_write_random';
                break;
            case 'chordquality-identify':
                $class = 'qtype_musictheory_chordquality_identify';
                break;
            case 'chordquality-identify-random':
                $class = 'qtype_musictheory_chordquality_identify_random';
                break;
            case 'harmonicfunction-write':
                $class = 'qtype_musictheory_harmonicfunction_write';
                break;
            case 'harmonicfunction-write-random':
                $class = 'qtype_musictheory_harmonicfunction_write_random';
                break;
            case 'harmonicfunction-identify':
                $class = 'qtype_musictheory_harmonicfunction_identify';
                break;
            case 'harmonicfunction-identify-random':
                $class = 'qtype_musictheory_harmonicfunction_identify_random';
                break;
        }

        $gradingstrategyname = $questiondata->options->musictheory_gradingstrategy;
        $gradingstrategy = new $gradingstrategyname();
        return new $class($gradingstrategy);
    }

    public function set_default_options($question) {
        $question->musictheory_musicqtype = 'note-write';
        $question->musictheory_gradingStrategy = 'all_or_nothing';
        $question->musictheory_clef = 'treble';
        $question->musictheory_keymode = 'GnM';
        $question->musictheory_displaykeysignature = '0';
        $question->musictheory_considerregister = '0';
        $question->musictheory_includealterations = '0';
        $question->musictheory_direction = 'above';
        $question->musictheory_givennoteletter = 'C';
        $question->musictheory_givennoteaccidental = 'n';
        $question->musictheory_givennoteregister = '4';
        $question->musictheory_staticnoteletter = 'C';
        $question->musictheory_staticnoteaccidental = 'n';
        $question->musictheory_staticnoteregister = '4';
        $question->musictheory_quality = 'P';
        $question->musictheory_size = '5';
        $question->musictheory_scaletype = 'major';
        $question->musictheory_chordquality = 'major';
        $question->musictheory_hfprimary = 'I';
        $question->musictheory_hfinvext = '';
        $question->musictheory_hfsecondary = '';
    }

    protected function initialise_question_instance(question_definition $question, $questiondata) {
        parent::initialise_question_instance($question, $questiondata);
        $this->initialise_options_from_xml($question);
        $this->initialise_random_options_from_xml($question);
        /* The generated question text for random sybtypes is set in
           [random_question_class]->start_attempt(). */
        if (strpos($question->musictheory_musicqtype, 'random') === false) {
            if (empty($question->questiontext)) {
                $question->questiontext = '<p>' . $question->get_question_text() . '</p>';
            }
        }
    }

    /**
     * Parses additional music subtype options stored in the
     * question's musictheory_optionsxml database field, and sets them as options
     * to the question. Given that the options for various subtypes are variable,
     * storing the additional options as XML in the database allows a greater
     * amount of option flexibility. Also, changes of options in future versions
     * will not necessitate altering the structure of the options database table.
     *
     * @param question_definition $question The question definition whose options
     * are to be set.
     * @return question_definition The question definition passed as argument to the
     * method, after the additional options have been added.
     */
    public function initialise_options_from_xml($question) {
        if (!isset($question->musictheory_optionsxml)) {
            return;
        }
        if (empty($question->musictheory_optionsxml)) {
            return;
        }

        $optionsxml = new SimpleXMLElement($question->musictheory_optionsxml);
        $children = $optionsxml->children();
        $options = $children[0];

        switch ($question->musictheory_musicqtype) {
            case 'note-write':
            case 'note-identify';
                $considerreg = (((string) $options->considerregister) == 'true') ? 1 : 0;
                $question->musictheory_considerregister = $considerreg;
                $includealterations = (((string) $options->includealterations) == 'true') ? 1 : 0;
                $question->musictheory_includealterations = $includealterations;
                $question->musictheory_clef = (string) $options->clef;
                $question->musictheory_givennoteletter = (string) $options->note[0]->letter;
                $question->musictheory_givennoteaccidental = (string) $options->note[0]->accidental;
                $question->musictheory_givennoteregister = (string) $options->note[0]->register;
                break;
            case 'keyboard-input':
                $includestaticnote = (((string) $options->includestaticnote) == 'true') ? 1 : 0;
                $question->musictheory_includestaticnote = $includestaticnote;
                if ($includestaticnote) {
                    $question->musictheory_staticnoteletter = (string) $options->staticnote[0]->letter;
                    $question->musictheory_staticnoteaccidental = (string) $options->staticnote[0]->accidental;
                    $question->musictheory_staticnoteregister = (string) $options->staticnote[0]->register;
                }
                $considerreg = (((string) $options->considerregister) == 'true') ? 1 : 0;
                $question->musictheory_considerregister = $considerreg;
                $question->musictheory_givennoteletter = (string) $options->note[0]->letter;
                $question->musictheory_givennoteaccidental = (string) $options->note[0]->accidental;
                $question->musictheory_givennoteregister = (string) $options->note[0]->register;
                break;
            case 'keysignature-write':
            case 'keysignature-identify':
                $question->musictheory_clef = (string) $options->clef;
                $question->musictheory_keymode = (string) $options->key;
                break;
            case 'interval-write':
            case 'interval-identify':
                $question->musictheory_clef = (string) $options->clef;
                $question->musictheory_givennoteletter = (string) $options->givennote[0]->letter;
                $question->musictheory_givennoteaccidental = (string) $options->givennote[0]->accidental;
                $question->musictheory_givennoteregister = (string) $options->givennote[0]->register;
                $question->musictheory_direction = (string) $options->direction;
                $question->musictheory_quality = (string) $options->quality;
                $question->musictheory_size = (string) $options->size;
                break;
            case 'scale-identify':
                $question->musictheory_possiblescalesinresponse = array();
                $possiblescales = 'possiblescalesinresponse';
                foreach ($options->$possiblescales->children() as $scaletype) {
                    array_push($question->musictheory_possiblescalesinresponse, (string) $scaletype);
                }
            case 'scale-write':
                $question->musictheory_clef = (string) $options->clef;
                $question->musictheory_givennoteletter = (string) $options->tonic[0]->letter;
                $question->musictheory_givennoteaccidental = (string) $options->tonic[0]->accidental;
                $question->musictheory_givennoteregister = (string) $options->tonic[0]->register;
                $displayks = (((string) $options->displaykeysignature) == 'true') ? 1 : 0;
                $question->musictheory_displaykeysignature = $displayks;
                $question->musictheory_scaletype = (string) $options->scaletype;
                break;
            case 'chordquality-write':
            case 'chordquality-identify':
                $question->musictheory_clef = (string) $options->clef;
                $question->musictheory_givennoteletter = (string) $options->chordroot[0]->letter;
                $question->musictheory_givennoteaccidental = (string) $options->chordroot[0]->accidental;
                $question->musictheory_chordquality = (string) $options->chordquality;
                break;
            case 'harmonicfunction-identify':
                $question->musictheory_hfidentifyresponsetypes = array();
                $hfresponsetypes = 'harmonicfunction-responsetypes';
                foreach ($options->$hfresponsetypes->children() as $hf) {
                    array_push($question->musictheory_hfidentifyresponsetypes, (string) $hf);
                }
            case 'harmonicfunction-write':
                $question->musictheory_clef = (string) $options->clef;
                $question->musictheory_keymode = (string) $options->key;
                $displayks = (((string) $options->displaykeysignature) == 'true') ? 1 : 0;
                $question->musictheory_displaykeysignature = $displayks;
                $question->musictheory_hfprimary = (string) $options->harmonicfunction[0]->hfprimary;
                $question->musictheory_hfinvext = (string) $options->harmonicfunction[0]->hfinvext;
                $question->musictheory_hfsecondary = (string) $options->harmonicfunction[0]->hfsecondary;
                break;
        }

        return $question;
    }

    /**
     * Parses additional random music subtype options stored in the
     * question's musictheory_randomoptionsxml database field, and sets them
     * as options to the question.
     *
     * @param question_definition $question The question definition whose options
     * are to be set.
     * @return question_definition The question definition passed as argument to the
     * method, after the additional options have been added.
     */
    public function initialise_random_options_from_xml($question) {
        if (!isset($question->musictheory_randomoptionsxml)) {
            return;
        }
        if (empty($question->musictheory_randomoptionsxml)) {
            return;
        }

        $optionsxml = new SimpleXMLElement($question->musictheory_randomoptionsxml);
        $children = $optionsxml->children();
        $options = $children[0];

        switch ($question->musictheory_musicqtype) {
            case 'note-write-random':
            case 'note-identify-random':
                $question->musictheory_clef_random = array();
                $clefrandom = 'clef-random';
                foreach ($options->$clefrandom->children() as $clef) {
                    array_push($question->musictheory_clef_random, (string) $clef);
                }
                $considerreg = (((string) $options->considerregister) == 'true') ? 1 : 0;
                $question->musictheory_considerregister = $considerreg;
                $includealterations = (((string) $options->includealterations) == 'true') ? 1 : 0;
                $question->musictheory_includealterations = $includealterations;
                break;
            case 'keysignature-write-random':
            case 'keysignature-identify-random':
                $question->musictheory_clef_random = array();
                $clefrandom = 'clef-random';
                foreach ($options->$clefrandom->children() as $clef) {
                    array_push($question->musictheory_clef_random, (string) $clef);
                }
                $question->musictheory_mode_random = array();
                $moderandom = 'mode-random';
                foreach ($options->$moderandom->children() as $mode) {
                    array_push($question->musictheory_mode_random, (string) $mode);
                }
                break;
            case 'interval-write-random':
                $question->musictheory_direction_random = array();
                $directionrandom = 'direction-random';
                foreach ($options->$directionrandom->children() as $direction) {
                    array_push($question->musictheory_direction_random, (string) $direction);
                }
            case 'interval-identify-random':
                $question->musictheory_clef_random = array();
                $clefrandom = 'clef-random';
                foreach ($options->$clefrandom->children() as $clef) {
                    array_push($question->musictheory_clef_random, (string) $clef);
                }
                $question->musictheory_quality_random = array();
                $qualityrandom = 'quality-random';
                foreach ($options->$qualityrandom->children() as $quality) {
                    array_push($question->musictheory_quality_random, (string) $quality);
                }
                $question->musictheory_size_random = array();
                $sizerandom = 'size-random';
                foreach ($options->$sizerandom->children() as $size) {
                    array_push($question->musictheory_size_random, (string) $size);
                }
                break;
            case 'scale-identify-random':
                $question->musictheory_possiblescalesinresponse = array();
                $possiblescales = 'possiblescalesinresponse';
                foreach ($options->$possiblescales->children() as $scaletype) {
                    array_push($question->musictheory_possiblescalesinresponse, (string) $scaletype);
                }
            case 'scale-write-random':
                $displayks = (((string) $options->displaykeysignature) == 'true') ? 1 : 0;
                $question->musictheory_displaykeysignature = $displayks;
                $question->musictheory_clef_random = array();
                $clefrandom = 'clef-random';
                foreach ($options->$clefrandom->children() as $clef) {
                    array_push($question->musictheory_clef_random, (string) $clef);
                }
                $question->musictheory_scaletype_random = array();
                $scaletyperandom = 'scaletype-random';
                foreach ($options->$scaletyperandom->children() as $scaletype) {
                    array_push($question->musictheory_scaletype_random, (string) $scaletype);
                }
                break;
            case 'chordquality-write-random':
            case 'chordquality-identify-random':
                $question->musictheory_clef_random = array();
                $clefrandom = 'clef-random';
                foreach ($options->$clefrandom->children() as $clef) {
                    array_push($question->musictheory_clef_random, (string) $clef);
                }
                $question->musictheory_chordquality_random = array();
                $chordqualityrandom = 'chordquality-random';
                foreach ($options->$chordqualityrandom->children() as $chordquality) {
                    array_push($question->musictheory_chordquality_random, (string) $chordquality);
                }
                break;
            case 'harmonicfunction-write-random':
            case 'harmonicfunction-identify-random':
                $question->musictheory_clef_random = array();
                $clefrandom = 'clef-random';
                foreach ($options->$clefrandom->children() as $clef) {
                    array_push($question->musictheory_clef_random, (string) $clef);
                }
                $question->musictheory_mode_random = array();
                $moderandom = 'mode-random';
                foreach ($options->$moderandom->children() as $mode) {
                    array_push($question->musictheory_mode_random, (string) $mode);
                }
                $displayks = (((string) $options->displaykeysignature) == 'true') ? 1 : 0;
                $question->musictheory_displaykeysignature = $displayks;
                $question->musictheory_harmonicfunctiontype_random = array();
                $harmonicfunctionrandom = 'harmonicfunction-random';
                foreach ($options->$harmonicfunctionrandom->children() as $hf) {
                    array_push($question->musictheory_harmonicfunctiontype_random, (string) $hf);
                }
                break;
        }

        return $question;
    }

    public function save_question_options($question) {
        global $DB;

        $extraquestionfields = $this->extra_question_fields();

        if (is_array($extraquestionfields)) {
            $questionextensiontable = array_shift($extraquestionfields);

            $function = 'update_record';
            $questionidcolname = $this->questionid_column_name();
            $options = $DB->get_record($questionextensiontable, array($questionidcolname => $question->id));
            if (!$options) {
                $function = 'insert_record';
                $options = new stdClass();
                $options->$questionidcolname = $question->id;
            }

            $options->musictheory_musicqtype = $question->musictheory_musicqtype;
            if (strpos($question->musictheory_musicqtype, 'random')) {
                $options->musictheory_randomoptionsxml = $this->get_randomoptions_xml($question, $question->musictheory_musicqtype);
            } else {
                $options->musictheory_optionsxml = $this->get_options_xml($question, $question->musictheory_musicqtype);
            }
            $options->musictheory_gradingstrategy = $question->musictheory_gradingstrategy;
            $DB->{$function}($questionextensiontable, $options);
        }

        $this->save_hints($question);
    }

    /**
     * Creates the XML string needed to store the question options
     * in the database's musictheory_optionsxml field.
     *
     * @param question_definition $question The question whose options
     * are to be stored.
     * @param string $musicqtype The music theory question subtype.
     * @return string The question options as XML.
     */
    public function get_options_xml($question, $musicqtype) {
        $outxml = '<?xml version="1.0" encoding="UTF-8"?>';

        $outxml .= '<options>';
        $outxml .= '<' . $musicqtype . '>';

        switch ($musicqtype) {
            case 'note-write':
                $considerreg = ($question->musictheory_considerregister == 1) ? 'true' : 'false';
                $outxml .= '<considerregister>' . $considerreg . '</considerregister>';
                $includealterations = ($question->musictheory_includealterations == 1) ? 'true' : 'false';
                $outxml .= '<includealterations>' . $includealterations . '</includealterations>';
                $outxml .= '<note>';
                $outxml .= '<letter>' . $question->musictheory_givennoteletter . '</letter>';
                $outxml .= '<accidental>' . $question->musictheory_givennoteaccidental . '</accidental>';
                $outxml .= '<register>' . $question->musictheory_givennoteregister . '</register>';
                $outxml .= '</note>';
                $outxml .= '<clef>' . $question->musictheory_clef . '</clef>';
                break;
            case 'note-identify':
                $considerreg = ($question->musictheory_considerregister == 1) ? 'true' : 'false';
                $outxml .= '<considerregister>' . $considerreg . '</considerregister>';
                $includealterations = ($question->musictheory_includealterations == 1) ? 'true' : 'false';
                $outxml .= '<includealterations>' . $includealterations . '</includealterations>';
                $outxml .= '<note>';
                $outxml .= '<letter>' . $question->musictheory_givennoteletter . '</letter>';
                if ($question->musictheory_includealterations == 1) {
                    $outxml .= '<accidental>' . $question->musictheory_givennoteaccidental . '</accidental>';
                } else {
                    $outxml .= '<accidental>n</accidental>';
                }
                $outxml .= '<register>' . $question->musictheory_givennoteregister . '</register>';
                $outxml .= '</note>';
                $outxml .= '<clef>' . $question->musictheory_clef . '</clef>';
                break;
            case 'keyboard-input':
                $includestaticnote = ($question->musictheory_includestaticnote == 1) ? 'true' : 'false';
                $outxml .= '<includestaticnote>' . $includestaticnote . '</includestaticnote>';
                if ($question->musictheory_includestaticnote == 1) {
                    $outxml .= '<staticnote>';
                    $outxml .= '<letter>' . $question->musictheory_staticnoteletter . '</letter>';
                    $outxml .= '<accidental>' . $question->musictheory_staticnoteaccidental . '</accidental>';
                    $outxml .= '<register>' . $question->musictheory_staticnoteregister . '</register>';
                    $outxml .= '</staticnote>';
                }
                $considerreg = ($question->musictheory_considerregister == 1) ? 'true' : 'false';
                $outxml .= '<considerregister>' . $considerreg . '</considerregister>';
                $outxml .= '<note>';
                $outxml .= '<letter>' . $question->musictheory_givennoteletter . '</letter>';
                $outxml .= '<accidental>' . $question->musictheory_givennoteaccidental . '</accidental>';
                $outxml .= '<register>' . $question->musictheory_givennoteregister . '</register>';
                $outxml .= '</note>';
                break;
            case 'keysignature-write':
            case 'keysignature-identify':
                $outxml .= '<clef>' . $question->musictheory_clef . '</clef>';
                $outxml .= '<key>' . $question->musictheory_keymode . '</key>';
                break;
            case 'interval-write':
            case 'interval-identify':
                $outxml .= '<clef>' . $question->musictheory_clef . '</clef>';
                $outxml .= '<givennote>';
                $outxml .= '<letter>' . $question->musictheory_givennoteletter . '</letter>';
                $outxml .= '<accidental>' . $question->musictheory_givennoteaccidental . '</accidental>';
                $outxml .= '<register>' . $question->musictheory_givennoteregister . '</register>';
                $outxml .= '</givennote>';
                $outxml .= '<direction>' . $question->musictheory_direction . '</direction>';
                $outxml .= '<size>' . $question->musictheory_size . '</size>';
                $outxml .= '<quality>' . $question->musictheory_quality . '</quality>';
                break;
            case 'scale-identify':
                $outxml .= '<possiblescalesinresponse>';
                foreach ($question->musictheory_possiblescalesinresponse as $scaletype) {
                    $outxml .= '<possiblescaletype>' . $scaletype . '</possiblescaletype>';
                }
                $outxml .= '</possiblescalesinresponse>';
            case 'scale-write':
                $outxml .= '<clef>' . $question->musictheory_clef . '</clef>';
                $outxml .= '<tonic>';
                $outxml .= '<letter>' . $question->musictheory_givennoteletter . '</letter>';
                $outxml .= '<accidental>' . $question->musictheory_givennoteaccidental . '</accidental>';
                $outxml .= '<register>' . $question->musictheory_givennoteregister . '</register>';
                $outxml .= '</tonic>';
                $displayks = ($question->musictheory_displaykeysignature == 1) ? 'true' : 'false';
                $outxml .= '<displaykeysignature>' . $displayks . '</displaykeysignature>';
                $outxml .= '<scaletype>' . $question->musictheory_scaletype . '</scaletype>';
                break;
            case 'chordquality-write':
            case 'chordquality-identify':
                $outxml .= '<clef>' . $question->musictheory_clef . '</clef>';
                $outxml .= '<chordroot>';
                $outxml .= '<letter>' . $question->musictheory_givennoteletter . '</letter>';
                $outxml .= '<accidental>' . $question->musictheory_givennoteaccidental . '</accidental>';
                $outxml .= '</chordroot>';
                $outxml .= '<chordquality>' . $question->musictheory_chordquality . '</chordquality>';
                break;
            case 'harmonicfunction-identify':
                $outxml .= '<harmonicfunction-responsetypes>';
                foreach ($question->musictheory_hfidentifyresponsetypes as $hftype) {
                    $outxml .= '<harmonicfunctiontype>' . $hftype . '</harmonicfunctiontype>';
                }
                $outxml .= '</harmonicfunction-responsetypes>';
            case 'harmonicfunction-write':
                $outxml .= '<clef>' . $question->musictheory_clef . '</clef>';
                $outxml .= '<key>' . $question->musictheory_keymode . '</key>';
                $displayks = ($question->musictheory_displaykeysignature == 1) ? 'true' : 'false';
                $outxml .= '<displaykeysignature>' . $displayks . '</displaykeysignature>';
                $outxml .= '<harmonicfunction>';
                $outxml .= '<hfprimary>' . $question->musictheory_hfprimary . '</hfprimary>';
                $outxml .= '<hfinvext>' . $question->musictheory_hfinvext . '</hfinvext>';
                $outxml .= '<hfsecondary>' . $question->musictheory_hfsecondary . '</hfsecondary>';
                $outxml .= '</harmonicfunction>';
                break;
        }

        $outxml .= '</' . $musicqtype . '>';
        $outxml .= '</options>';

        return $outxml;
    }

    /**
     * Creates the XML string needed to store the random question options
     * in the database's musictheory_randomoptionsxml field.
     *
     * @param question_definition $question The question whose options
     * are to be stored.
     * @param string $musicqtype The music theory random question subtype.
     * @return string The question options as XML.
     */
    public function get_randomoptions_xml($question, $musicqtype) {
        $outxml = '<?xml version="1.0" encoding="UTF-8"?>';

        $outxml .= '<options>';
        $outxml .= '<' . $musicqtype . '>';

        switch ($musicqtype) {
            case 'note-write-random':
            case 'note-identify-random':
                $outxml .= '<clef-random>';
                foreach ($question->musictheory_clef_random as $clef) {
                    $outxml .= '<clef>' . $clef . '</clef>';
                }
                $outxml .= '</clef-random>';
                $considerreg = ($question->musictheory_considerregister == 1) ? 'true' : 'false';
                $outxml .= '<considerregister>' . $considerreg . '</considerregister>';
                $includealterations = ($question->musictheory_includealterations == 1) ? 'true' : 'false';
                $outxml .= '<includealterations>' . $includealterations . '</includealterations>';
                break;
            case 'keysignature-write-random':
            case 'keysignature-identify-random':
                $outxml .= '<clef-random>';
                foreach ($question->musictheory_clef_random as $clef) {
                    $outxml .= '<clef>' . $clef . '</clef>';
                }
                $outxml .= '</clef-random>';
                $outxml .= '<mode-random>';
                foreach ($question->musictheory_mode_random as $mode) {
                    $outxml .= '<mode>' . $mode . '</mode>';
                }
                $outxml .= '</mode-random>';
                break;
            case 'interval-write-random':
                $outxml .= '<clef-random>';
                foreach ($question->musictheory_clef_random as $clef) {
                    $outxml .= '<clef>' . $clef . '</clef>';
                }
                $outxml .= '</clef-random>';
                $outxml .= '<direction-random>';
                foreach ($question->musictheory_direction_random as $direction) {
                    $outxml .= '<direction>' . $direction . '</direction>';
                }
                $outxml .= '</direction-random>';
                $outxml .= '<quality-random>';
                foreach ($question->musictheory_quality_random as $quality) {
                    $outxml .= '<quality>' . $quality . '</quality>';
                }
                $outxml .= '</quality-random>';
                $outxml .= '<size-random>';
                foreach ($question->musictheory_size_random as $size) {
                    $outxml .= '<size>' . $size . '</size>';
                }
                $outxml .= '</size-random>';
                break;
            case 'interval-identify-random':
                $outxml .= '<clef-random>';
                foreach ($question->musictheory_clef_random as $clef) {
                    $outxml .= '<clef>' . $clef . '</clef>';
                }
                $outxml .= '</clef-random>';
                $outxml .= '<quality-random>';
                foreach ($question->musictheory_quality_random as $quality) {
                    $outxml .= '<quality>' . $quality . '</quality>';
                }
                $outxml .= '</quality-random>';
                $outxml .= '<size-random>';
                foreach ($question->musictheory_size_random as $size) {
                    $outxml .= '<size>' . $size . '</size>';
                }
                $outxml .= '</size-random>';
                break;
            case 'scale-identify-random':
                $outxml .= '<possiblescalesinresponse>';
                foreach ($question->musictheory_possiblescalesinresponse as $scaletype) {
                    $outxml .= '<possiblescaletype>' . $scaletype . '</possiblescaletype>';
                }
                $outxml .= '</possiblescalesinresponse>';
            case 'scale-write-random':
                $displayks = ($question->musictheory_displaykeysignature == 1) ? 'true' : 'false';
                $outxml .= '<displaykeysignature>' . $displayks . '</displaykeysignature>';
                $outxml .= '<clef-random>';
                foreach ($question->musictheory_clef_random as $clef) {
                    $outxml .= '<clef>' . $clef . '</clef>';
                }
                $outxml .= '</clef-random>';
                $outxml .= '<scaletype-random>';
                foreach ($question->musictheory_scaletype_random as $scaletype) {
                    $outxml .= '<scaletype>' . $scaletype . '</scaletype>';
                }
                $outxml .= '</scaletype-random>';
                break;
            case 'chordquality-write-random':
            case 'chordquality-identify-random':
                $outxml .= '<clef-random>';
                foreach ($question->musictheory_clef_random as $clef) {
                    $outxml .= '<clef>' . $clef . '</clef>';
                }
                $outxml .= '</clef-random>';
                $outxml .= '<chordquality-random>';
                foreach ($question->musictheory_chordquality_random as $chordquality) {
                    $outxml .= '<chordquality>' . $chordquality . '</chordquality>';
                }
                $outxml .= '</chordquality-random>';
                break;
            case 'harmonicfunction-write-random':
            case 'harmonicfunction-identify-random':
                $outxml .= '<clef-random>';
                foreach ($question->musictheory_clef_random as $clef) {
                    $outxml .= '<clef>' . $clef . '</clef>';
                }
                $outxml .= '</clef-random>';
                $outxml .= '<mode-random>';
                foreach ($question->musictheory_mode_random as $mode) {
                    $outxml .= '<mode>' . $mode . '</mode>';
                }
                $outxml .= '</mode-random>';
                $displayks = ($question->musictheory_displaykeysignature == 1) ? 'true' : 'false';
                $outxml .= '<displaykeysignature>' . $displayks . '</displaykeysignature>';
                $outxml .= '<harmonicfunction-random>';
                foreach ($question->musictheory_harmonicfunctiontype_random as $hftype) {
                    $outxml .= '<harmonicfunctiontype>' . $hftype . '</harmonicfunctiontype>';
                }
                $outxml .= '</harmonicfunction-random>';
                break;
        }

        $outxml .= '</' . $musicqtype . '>';
        $outxml .= '</options>';

        return $outxml;
    }

    public function import_from_xml($data, $question, qformat_xml $format, $extra = null) {
        $questiontype = $data['@']['type'];
        if ($questiontype != $this->name()) {
            return false;
        }

        $extraquestionfields = $this->extra_question_fields();
        if (!is_array($extraquestionfields)) {
            return false;
        }

        // Omit table name.
        array_shift($extraquestionfields);
        $qo = $format->import_headers($data);
        $qo->qtype = $questiontype;

        foreach ($extraquestionfields as $field) {
            $qo->$field = $format->getpath($data, array('#', $field, 0, '#'), '');
        }

        $this->initialise_options_from_xml($qo);
        $this->initialise_random_options_from_xml($qo);

        return $qo;
    }

}
