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
// Plugin - generic.
$string['pluginname'] = 'Music Theory';
$string['pluginname_help'] = 'This question type enables the creation of several types of music
 theory exercises using a graphical user interface.';
$string['pluginname_link'] = 'question/type/musictheory';
$string['pluginnamesummary'] = 'Enables the creation of several types of music theory exercises.';
$string['pluginnameadding'] = 'Adding a music theory question';
$string['pluginnameediting'] = 'Editing a music theory question';

// Edit form labels and help.
$string['answerlbl'] = 'Answer';
$string['aug6thgr'] = 'German Augmented 6th';
$string['aug6thfr'] = 'French Augmented 6th';
$string['aug6thit'] = 'Italian Augmented 6th';
$string['chordroot'] = 'Chord root';
$string['chordquality'] = 'Chord quality';
$string['chordquality-random'] = 'Possible chord qualities';
$string['clef'] = 'Clef';
$string['clef-random'] = 'Possible clefs';
$string['considerregister'] = 'Include register';
$string['considerregister_help'] = "Indicates whether the note's register is to be
 included as part of the question.";
$string['includealterations'] = 'Include alterations';
$string['includealterations_help'] = 'Indicates whether accidentals are to be
 included as part of the question.';
$string['includestaticnote'] = 'Include given note';
$string['displaykeysignature'] = 'Display key signature';
$string['direction'] = 'Direction';
$string['givennoteelementgroup'] = 'Given note';
$string['harmonicfunctiontype-random'] = 'Possible harmonic functions';
$string['hfidentifyresponsetypes'] = 'Possible harmonic functions in response';
$string['keymode'] = 'Key';
$string['lbl_harmonicfunction'] = 'Harmonic function';
$string['mode-random'] = 'Possible modes';
$string['musicqtype'] = 'Music question type';
$string['musictheory_intervalelementgroup'] = 'Interval';
$string['musictheory_intervalelementgroup_help'] = 'Indicates the type of interval.';
$string['musictheory_intervalelementgroup_random'] = 'Possible intervals';
$string['nosectonic'] = '[No secondary tonic]';
$string['notelbl'] = 'Note';
$string['quality-random'] = 'Possible qualities';
$string['questionoptions'] = 'Question options';
$string['questiontext'] = 'Question text';
$string['questiontext_help'] = "This field isn't required. If left empty, a question text
 appropriate for the given music question type will be automatically displayed within
 activities. If you do enter text in this field, it will replace the automatically
 generated question text.";
$string['rootposition'] = '[root]';
$string['scaletype-random'] = 'Possible scale types';
$string['possiblescalesinresponse'] = 'Possible scale types in response';
$string['size-random'] = 'Possible sizes';
$string['tonic'] = 'Tonic';
$string['updatemusicqtype'] = 'Update options';

// Music question subtype options.
$string['qtype_note-write'] = 'Note writing';
$string['qtype_note-write-random'] = 'Note writing (random)';
$string['qtype_note-identify'] = 'Note identification';
$string['qtype_note-identify-random'] = 'Note identification (random)';
$string['qtype_keyboard-input'] = 'Keyboard Input';
$string['qtype_keysignature-write'] = 'Key signature writing';
$string['qtype_keysignature-write-random'] = 'Key signature writing (random)';
$string['qtype_keysignature-identify'] = 'Key signature identification';
$string['qtype_keysignature-identify-random'] = 'Key signature identification (random)';
$string['qtype_interval-write'] = 'Interval writing';
$string['qtype_interval-write-random'] = 'Interval writing (random)';
$string['qtype_interval-identify'] = 'Interval identification';
$string['qtype_interval-identify-random'] = 'Interval identification (random)';
$string['qtype_scale-write'] = 'Scale writing';
$string['qtype_scale-write-random'] = 'Scale writing (random)';
$string['qtype_scale-identify'] = 'Scale identification';
$string['qtype_scale-identify-random'] = 'Scale identification (random)';
$string['qtype_chordquality-write'] = 'Chord quality writing';
$string['qtype_chordquality-write-random'] = 'Chord quality writing (random)';
$string['qtype_chordquality-identify'] = 'Chord quality identification';
$string['qtype_chordquality-identify-random'] = 'Chord quality identification (random)';
$string['qtype_harmonicfunction-write'] = 'Harmonic function writing';
$string['qtype_harmonicfunction-write-random'] = 'Harmonic function writing (random)';
$string['qtype_harmonicfunction-identify'] = 'Harmonic function identification';
$string['qtype_harmonicfunction-identify-random'] = 'Harmonic function identification (random)';

// Edit form - grading strategy options and help.
$string['musictheory_gradingstrategy'] = 'Grading strategy';
$string['musictheory_gradingstrategy_help'] = '<p>The grading strategy specifies how the question
 will be graded.</p>
 <p><b>All or nothing</b>: The response is compared to the correct answer. If the response
 is correct, full marks are given. If the answer is incorrect, a mark of zero is given.</p>
';
$string['musictheory_gradingstrategy_scale-write'] = 'Grading strategy';
$string['musictheory_gradingstrategy_scale-write_help'] = '<p>The grading strategy specifies how
 the question will be graded.</p>
 <p><b>All or nothing</b>: The response is compared to the correct answer. If the response
 is correct, full marks are given. If the answer is incorrect, a mark of zero is given.</p>
 <p><b>Partial credit for each correct note</b>:
 A grading strategy that divides the grade evenly by the number of scale notes the user has
 to enter, and awards partial grade for each correct note.</p>
';
$string['qtype_musictheory_strategy_all_or_nothing'] = 'All or nothing';
$string['qtype_musictheory_strategy_chordqualitywrite_allornothing'] = 'All or nothing';
$string['qtype_musictheory_strategy_harmonicfunctionwrite_allornothing'] = 'All or nothing';
$string['qtype_musictheory_strategy_harmonicfunctionid_allornothing'] = 'All or nothing';
$string['qtype_musictheory_strategy_note_allornothing'] = 'All or nothing';
$string['qtype_musictheory_strategy_keyboard_allornothing'] = 'All or nothing';
$string['qtype_musictheory_strategy_scale_creditbynote'] = 'Partial credit for each correct note';

// Note names.
$string['notec'] = 'C';
$string['noted'] = 'D';
$string['notee'] = 'E';
$string['notef'] = 'F';
$string['noteg'] = 'G';
$string['notea'] = 'A';
$string['noteb'] = 'B';

// Clef options.
$string['treble'] = 'Treble';
$string['bass'] = 'Bass';
$string['alto'] = 'Alto';
$string['tenor'] = 'Tenor';
$string['grandstaff'] = 'Grand Staff';

// Key options.
$string['cnmajor'] = 'C major';
$string['gnmajor'] = 'G major';
$string['dnmajor'] = 'D major';
$string['anmajor'] = 'A major';
$string['enmajor'] = 'E major';
$string['bnmajor'] = 'B major';
$string['fsharpmajor'] = 'F&#9839; major';
$string['csharpmajor'] = 'C&#9839; major';
$string['fnmajor'] = 'F major';
$string['bbmajor'] = 'B&#9837; major';
$string['ebmajor'] = 'E&#9837; major';
$string['abmajor'] = 'A&#9837; major';
$string['dbmajor'] = 'D&#9837; major';
$string['gbmajor'] = 'G&#9837; major';
$string['cbmajor'] = 'C&#9837; major';
$string['anminor'] = 'A minor';
$string['enminor'] = 'E minor';
$string['bnminor'] = 'B minor';
$string['fsharpminor'] = 'F&#9839; minor';
$string['csharpminor'] = 'C&#9839; minor';
$string['gsharpminor'] = 'G&#9839; minor';
$string['dsharpminor'] = 'D&#9839; minor';
$string['asharpminor'] = 'A&#9839; minor';
$string['dnminor'] = 'D minor';
$string['gnminor'] = 'G minor';
$string['cnminor'] = 'C minor';
$string['fnminor'] = 'F minor';
$string['bbminor'] = 'B&#9837; minor';
$string['ebminor'] = 'E&#9837; minor';
$string['abminor'] = 'A&#9837; minor';

// Interval direction options.
$string['dirasc'] = 'Above the given note';
$string['dirdesc'] = 'Below the given note';

// Interval qualities.
$string['qualitymaj'] = 'Major';
$string['qualitymin'] = 'Minor';
$string['qualityper'] = 'Perfect';
$string['qualityaug'] = 'Augmented';
$string['qualitydim'] = 'Diminished';
$string['major'] = 'Major';
$string['minor'] = 'Minor';
$string['perfect'] = 'Perfect';
$string['augmented'] = 'Augmented';
$string['diminished'] = 'Diminished';

// Interval sizes.
$string['size2'] = '2nd';
$string['size3'] = '3rd';
$string['size4'] = '4th';
$string['size5'] = '5th';
$string['size6'] = '6th';
$string['size7'] = '7th';
$string['size8'] = '8ve';
$string['size9'] = '9th';
$string['size10'] = '10th';
$string['size11'] = '11th';
$string['size12'] = '12th';
$string['size13'] = '13th';

// Intervals.
$string['dim2'] = 'Diminished 2nd';
$string['min2'] = 'Minor 2nd';
$string['maj2'] = 'Major 2nd';
$string['aug2'] = 'Augmented 2nd';

$string['dim3'] = 'Diminished 3rd';
$string['min3'] = 'Minor 3rd';
$string['maj3'] = 'Major 3rd';
$string['aug3'] = 'Augmented 3rd';

$string['dim4'] = 'Diminished 4th';
$string['per4'] = 'Perfect 4th';
$string['aug4'] = 'Augmented 4th';

$string['dim5'] = 'Diminished 5th ';
$string['per5'] = 'Perfect 5th';
$string['aug5'] = 'Augmented 5th';

$string['dim6'] = 'Diminished 6th';
$string['min6'] = 'Minor 6th';
$string['maj6'] = 'Major 6th';
$string['aug6'] = 'Augmented 6th';

$string['dim7'] = 'Diminished 7th';
$string['min7'] = 'Minor 7th';
$string['maj7'] = 'Major 7th';
$string['aug7'] = 'Augmented 7th';

$string['dim8'] = 'Diminished octave';
$string['per8'] = 'Perfect octave';
$string['aug8'] = 'Augmented octave';

$string['dim9'] = 'Diminished 9th';
$string['min9'] = 'Minor 9th';
$string['maj9'] = 'Major 9th';
$string['aug9'] = 'Augmented 9th';

$string['dim10'] = 'Diminished 10th';
$string['min10'] = 'Minor 10th';
$string['maj10'] = 'Major 10th';
$string['aug10'] = 'Augmented 10th';

$string['dim11'] = 'Diminished 11th';
$string['per11'] = 'Perfect 11th';
$string['aug11'] = 'Augmented 11th';

$string['dim12'] = 'Diminished 12th';
$string['per12'] = 'Perfect 12th';
$string['aug12'] = 'Augmented 12th';

$string['dim13'] = 'Diminished 13th';
$string['min13'] = 'Minor 13th';
$string['maj13'] = 'Major 13th';
$string['aug13'] = 'Augmented 13th';

// Scale types.
$string['scaletype'] = 'Scale type';
$string['scaletype_major'] = 'Major';
$string['scaletype_natural'] = 'Natural minor';
$string['scaletype_harmonic'] = 'Harmonic minor';
$string['scaletype_melodic'] = 'Melodic minor';

// Harmonic function types.
$string['hftype_diatonictriad'] = 'Diatonic triad';
$string['hftype_dom7th'] = 'Dominant seventh';
$string['hftype_nondom7th'] = 'Nondominant seventh';
$string['hftype_leadingtone7thhalfdim'] = 'Leading tone seventh (half diminished)';
$string['hftype_leadingtone7thfullydim'] = 'Leading tone seventh (fully diminished)';
$string['hftype_secdomtriad'] = 'Secondary dominant (triad)';
$string['hftype_secdom7th'] = 'Secondary dominant (seventh)';
$string['hftype_secnondomtriad'] = 'Secondary nondominant (triad)';
$string['hftype_secnondom7th'] = 'Secondary nondominant (seventh)';
$string['hftype_seclttriad'] = 'Secondary leading tone (triad)';
$string['hftype_seclthalfdim'] = 'Secondary leading tone 7th (half diminished)';
$string['hftype_secltfullydim'] = 'Secondary leading tone 7th (fully diminished)';
$string['hftype_neapolitan'] = 'Neapolitan';
$string['hftype_aug6th'] = 'Augmented 6th';
$string['hftype_extendeddom'] = 'Extended dominant (9, 11 or 13)';

// Edit form validation.
$string['validation_noteoutsidestaff'] = "This note's register doesn't fit within the staff
 in the specified clef.";
$string['validation_noteoutsidekeyboard'] = 'This note is outside the range of the piano keyboard.';
$string['validation_samestaticandgivennote'] = 'The given note and the answer cannot correspond to the same key on the keyboard.';
$string['validation_qualitymismatch'] = 'This combination of interval size and quality is invalid.';
$string['validation_harmonicfunctionnotsupported'] = 'This harmonic function is not supported or is invalid.';
$string['validation_harmonicfunctiontypenotselected'] = 'The function type for the specified harmonic function must be selected.';
$string['validation_invalidchordquality'] = 'This type of chord cannot be built on the specified root.';
$string['validation_invalidinterval_above'] = 'This interval cannot be written above the given note.';
$string['validation_invalidinterval_below'] = 'This interval cannot be written below the given note.';
$string['validation_intervaloutsidestaff'] = "The given note's register produces an interval that
 doesn't fit within the staff in the given clef.";
$string['validation_interval_novalidcombo'] = 'The selected possible qualities and sizes do not
 produce at least one valid interval.';
$string['validation_scale_invalidtonic'] = 'Invalid tonic for the specified type of scale.';
$string['validation_scaleoutsidestaff'] = "The given tonic's register produces a scale that doesn't
 fit within the staff in the given clef.";
$string['validation_possiblescaletypenotselected'] = 'The scale type for the specified type of scale must be selected.';
$string['validation_possiblescaletypesnotselected_random'] = 'The possible scale types in the response
 must include all possible scale types in the question.';

// Question rendering.
$string['correctansweris'] = 'The correct answer is:';
$string['correctansweris_morethanone'] = 'One possible correct answer is:';
$string['javascriptrequired'] = 'This text is to be replaced by a graphical user
 interface, which requires Javascript to be enabled in your browser.';
$string['selectanoption'] = '[No secondary tonic]';
$string['selectanoption'] = 'Select';
$string['selectakey'] = 'Select a key';
$string['selectasize'] = 'Select a size';
$string['selectaquality'] = 'Select a quality';
$string['trebleclef'] = 'Treble clef';
$string['bassclef'] = 'Bass clef';
$string['altoclef'] = 'Alto clef';
$string['tenorclef'] = 'Tenor clef';

// Question text and help button.
$string['questiontext_note_write'] = 'Enter the following note';
$string['questiontext_note_identify'] = 'Identify the following note';
$string['questiontext_keyboard_input'] = 'Enter the following note on the keyboard';
$string['questiontext_keysignature_write'] = 'Enter the following key signature';
$string['questiontext_keysignature_identify_major'] = 'Determine the major key represented by the
 following key signature';
$string['questiontext_keysignature_identify_minor'] = 'Determine the minor key represented by the
 following key signature';
$string['questiontext_interval_write_above'] = 'Enter the following interval <b>above</b> the given note';
$string['questiontext_interval_write_below'] = 'Enter the following interval <b>below</b> the given note';
$string['questiontext_interval_identify'] = 'Identify the following interval';
$string['questiontext_scale_write'] = 'Write the following scale in ascending direction';
$string['questiontext_scale_write_melodic'] = 'Write the following scale in both ascending and
     descending directions';
$string['questiontext_scale_identify'] = 'Identify the following scale';
$string['questiontext_chordquality_write'] = 'Enter the following chord';
$string['questiontext_chordquality_identify'] = 'Identify the root and quality of the following chord';
$string['questiontext_harmonicfunction_write'] = 'Enter the following harmonic function';
$string['questiontext_harmonicfunction_identify'] = 'Identify the following harmonic function';

$string['acc_n'] = '&#9838;';
$string['acc_sharp'] = '&#9839;';
$string['acc_x'] = 'x';
$string['acc_b'] = '&#9837;';
$string['acc_bb'] = '&#9837;&#9837;';

$string['chordquality_write_questionastext'] = 'Chord quality answer entry';
$string['chordquality_write_questionastext_help'] = '<p>Enter a comma-separated list of notes,
  without spaces, using the following syntax:</p><p>[Uppercase letter name][Accidental
  ("n" = natural, "#" = sharp, "b" = flat, "x" = double-sharp, "bb" = double-flat)]4, ... </p>
 <p>Random example: <b>answer=>Cn4,En4,G#4</b></p>';
$string['chordquality_write_questionasui'] = 'Harmonic function answer entry';
$string['chordquality_write_questionasui_help'] = '<p>Enter notes by clicking on the staff, after selecting
 the type of accidental in the right-hand toolbar. To delete a note, click on it again.</p>';
$string['note_write_questionastext'] = 'Note answer entry';
$string['harmonicfunction_write_questionastext'] = 'Harmonic function answer entry';
$string['harmonicfunction_write_questionastext_help'] = '<p>Enter a comma-separated list of notes,
  without spaces, using the following syntax:</p><p>[Uppercase letter name][Accidental
  ("n" = natural, "#" = sharp, "b" = flat, "x" = double-sharp, "bb" = double-flat)]4, ... </p>
 <p>Random example: <b>answer=>Cn4,En4,G#4</b></p>';
$string['harmonicfunction_write_questionasui'] = 'Harmonic function answer entry';
$string['harmonicfunction_write_questionasui_help'] = '<p>Enter notes by clicking on the staff, after selecting
 the type of accidental in the right-hand toolbar. To delete a note, click on it again.</p>';
$string['note_write_questionastext'] = 'Note answer entry';
$string['note_write_questionastext_help'] = '<p>Enter the response note, without spaces, using
 the following syntax:</p><p>[Uppercase letter name] [Accidental ("n" = natural, "#" = sharp,
 "b" = flat, "x" = double-sharp, "bb" = double-flat)] [Register (a digit between 1 and 6, following
 the scientific pitch notation)]</p><p>Examples:
 </p><ul><li><b>Gn5</b></li><li><b>A#4</b></li><li><b>Ebb3</b></li></ul>';
$string['note_write_questionasui'] = 'Note answer entry';
$string['note_write_questionasui_help'] = '<p>Enter the note by clicking on the staff, after
 selecting the type of accidental in the right-hand toolbar. To delete the note, click on it again.</p>';
$string['keyboard_input_questionasui'] = 'Keyboard input answer entry';
$string['keyboard_input_questionasui_help'] = '<p>Enter notes by clicking on the keyboard. To delete a note, click on it again.</p>';
$string['keysignature_write_questionastext'] = 'Key signature answer entry';
$string['keysignature_write_questionastext_help'] = '<p>Enter a comma-separated list of accidentals
 (between 1 and 7 of them), without spaces, using the following syntax:</p><p>[Uppercase letter name]
 [Accidental ("#" = sharp, "b" = flat)] [Register (a digit between 2 and 5, following the scientific
 pitch notation)], ... </p><p>Example: <b>A#4,F#4,G#4</b></p>';
$string['keysignature_write_questionasui'] = 'Key signature answer entry';
$string['keysignature_write_questionasui_help'] = '<p>Enter accidentals by clicking on the staff,
 after selecting the type of accidental in the right-hand toolbar. To delete an accidental, click on
 it again.</p>';
$string['interval_write_questionastext'] = 'Interval answer entry';
$string['interval_write_questionastext_help'] = '<p>Enter the response note, without spaces, using
 the following syntax:</p><p>[Uppercase letter name] [Accidental ("n" = natural, "#" = sharp,
 "b" = flat, "x" = double-sharp, "bb" = double-flat)] [Register (a digit between 1 and 6, following
 the scientific pitch notation)]</p><p>Examples:
 </p><ul><li><b>Gn5</b></li><li><b>A#4</b></li><li><b>Ebb3</b></li></ul>';
$string['interval_write_questionasui'] = 'Interval answer entry';
$string['interval_write_questionasui_help'] = '<p>Enter the note by clicking on the staff, after
 selecting the type of accidental in the right-hand toolbar. To delete the note, click on it again.</p>';
$string['scale_write_questionastext'] = 'Scale answer entry';
$string['scale_write_questionastext_help'] = '<p>Enter a comma-separated list of notes, including
 the specified tonic, without spaces, using the following syntax:</p><p>[Uppercase letter name]
 [Accidental ("n" = natural, "#" = sharp, "b" = flat, "x" = double-sharp, "bb" = double-flat)]
 [Register (a digit between 2 and 6, following the scientific pitch notation)], ... </p>
 <p>Random example: <b>Tonic = An4 answer=>An4,Bb4,C#5,D#5,E#5,F#5,G#5,An5</b></p>';
$string['scale_write_questionasui'] = 'Scale answer entry';
$string['scale_write_questionasui_help'] = '<p>Enter notes by clicking on the staff, after selecting
 the type of accidental in the right-hand toolbar. To delete a note, click on it again.</p>
 <p>Note that accidentals carry over to subsequent notes.</p>';
$string['emptykeysignature'] = 'Empty key signature';

// Question answer validation.
$string['validationerror_chordquality_identify'] = 'Incomplete answer. The letter, accidental and chord quality must be selected.';
$string['validationerror_notewrite_twonotesingrandstaff'] = 'Two notes were entered. Please enter only one note.';
$string['validationerror_note_identify'] = 'Incomplete answer. The letter name, accidental
 and register must be selected.';
$string['validationerror_note_identify_ltr_acc'] = 'Incomplete answer. Both the letter name and accidental
 must be selected.';
$string['validationerror_note_identify_ltr_reg'] = 'Incomplete answer. Both the letter name and register
 must be selected.';
$string['validationerror_empty'] = 'No answer was provided.';
$string['validationerror_incompletechordquality'] = 'Incomplete answer. Number of notes in this chord';
$string['validationerror_harmonicfunction_identify'] = 'Incomplete answer. All dropdown menus must be selected.';
$string['validationerror_incompleteharmonicfunction'] = 'Incomplete answer. Number of notes in this harmonic function';
$string['validationerror_interval_identify'] = 'Incomplete answer. Both the quality and the size
 must be selected.';
$string['validationerror_invalidsyntax'] = 'Invalid answer syntax.';
$string['validationerror_scale_incomplete'] = 'Incomplete answer. A total of 8 notes is required,
 including the given tonic.';
$string['validationerror_scale_incomplete_melodic'] = 'Incomplete answer. A total of 15 notes is
 required, including the given tonic.';
$string['validationerror_scale_identify'] = 'Incomplete answer. All dropdown menus must be selected.';
$string['validationerror_whitespace'] = 'Invalid answer syntax: remove all spaces from your answer.';

// Privacy.
$string['privacy:metadata'] = 'The Music Theory question type plugin does not store any personal data.';
