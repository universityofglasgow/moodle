YUI.add('moodle-qtype_musictheory-musictheoryqtype', function (Y, NAME) {

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

/**
 * This module takes care of instantiating MusThGUI canvases for the Moodle
 * music theory question type.
 *
 * @module Moodle Music Theory Question Type
 */

/**
 * Although documented as a class, this file is simply a container for module
 * functions.
 *
 * @class musictheoryqtype
 * @static
 */

M.qtype_musictheory = M.qtype_musictheory || {};
M.qtype_musictheory.musictheoryqtype = {};
var NS = M.qtype_musictheory.musictheoryqtype;

NS.questionRender = {};

/**
 * Initializes GUI canvases when a music theory question is displayed.
 *
 * @method initQuestionRender
 * @param {Array} params An array containing various rendering parameters.
 * @return {Undefined}
 */
NS.initQuestionRender = function (params) {

  var HTMLInputID = params.inputname,
      optionsXML = params.optionsxml,
      readOnly = params.readonly,
      initialInput = params.initialinput,
      correctResponse = params.correctresponse,
      correctRespStr = params.correctrespstr,
      additionalParams = params.additionalparams,
      options = NS.questionRender.convertOptionsXMLtoObjectLiteral(optionsXML,
          additionalParams);

  NS.questionRender.renderQuestion(HTMLInputID, options,
      readOnly, initialInput);

  if (correctResponse !== null && typeof (correctResponse) !== 'undefined' &&
      correctResponse !== '') {
    NS.questionRender.renderCorrectResponse(HTMLInputID, options,
        correctResponse,
        correctRespStr);
  }

};

/**
 * Initializes the GUI canvas for the question section.
 *
 * @method renderQuestion
 * @param {String} HTMLInputID The unique id of the text input that the MusThGUI
 * canvas is to replace.
 * @param {Object literal} options An object literal specifying the Moodle music
 * question's settings.
 * @param {Boolean} readOnly Indicates whether the canvas should be editable.
 * @param {String} initialInput The initial input to use when initializing the
 * MusThGUI canvas, formatted in accordance with the music question type.
 * @return {Undefined}
 */
NS.questionRender.renderQuestion = function (HTMLInputID, options,
    readOnly, initialInput) {

  var YInput,
      canvasDiv,
      displayCanvasID,
      xmlConverter,
      replacedDiv,
      canvasNode,
      stateXML,
      callBack;

  HTMLInputID = HTMLInputID.replace(':', '\\:');

  YInput = Y.one('#' + HTMLInputID);

  replacedDiv = Y.one(
      '#' + 'musictheory_div_replacedbycanvas_' + HTMLInputID);

  if (replacedDiv) {
    replacedDiv.hide();
  }

  canvasDiv = Y.one('#musictheory_div_canvas_' + HTMLInputID);

  if (!canvasDiv) {
    return;
  }

  displayCanvasID = 'musictheory_renderMusicCanvas_' + HTMLInputID.replace(
      '\\:', '-');

  canvasNode = Y.one('#' + displayCanvasID);

  if (!canvasNode) {
    canvasNode = Y.Node.create(
        '<div style="margin-top:10px;margin-bottom:15px;overflow:auto"><canvas id="' +
        displayCanvasID + '" width="1" height="1" /></div>');
    canvasDiv.append(canvasNode);
  }

  options.editable = !readOnly;
  options.containsUserInput = true;

  xmlConverter = new NS.XMLConverter(options);
  callBack = function (stateXML) {
    YInput.set('value', xmlConverter.getCanvasTextOutput(stateXML));
  };

  stateXML = xmlConverter.getCanvasXML(initialInput);

  if (options.musicQType === 'keyboard-input') {
    new KeyboardInput(displayCanvasID, stateXML, callBack);
  }
  else {
    new MusThGUI(displayCanvasID, stateXML, callBack);
  }

  canvasDiv.show();
};

/**
 * Initializes GUI canvases when a music theory question is displayed.
 *
 * @method renderCorrectResponse
 * @param {String} HTMLInputID The unique id of the text input that the MusThGUI
 * canvas is to replace.
 * @param {String} options An XML string specifying the Moodle music
 * question's settings.
 * @param {String} correctResponse The correct answer for the question,
 * formatted in accordance with the music question type.
 * @param {String} correctResponseStr The sentence to display when the correct
 * answer is rendered.
 * @return {Undefined}
 */
NS.questionRender.renderCorrectResponse = function (HTMLInputID, options,
    correctResponse, correctResponseStr) {

  var YInput,
      correctCanvasID,
      canvasNode,
      xmlConverter,
      stateXML,
      callBack;


  HTMLInputID = HTMLInputID.replace(':', '\\:');
  YInput = Y.one('#' + 'musictheory_correctanswerdiv_' + HTMLInputID);

  if (!YInput) {
    return;
  }

  correctCanvasID = 'musictheory_renderCorrectResponseCanvas_' +
      HTMLInputID.replace('\\:', '-');

  canvasNode = Y.one('#' + correctCanvasID);

  if (!canvasNode) {
    YInput.setHTML('<p>' + correctResponseStr + '</p>' +
        '<canvas id="' + correctCanvasID + '" width="1" height="1" />');
  }

  options.editable = false;
  options.containsUserInput = false;

  xmlConverter = new NS.XMLConverter(options);
  callBack = function () {
  };

  stateXML = xmlConverter.getCanvasXML(correctResponse);
  if (options.musicQType === 'keyboard-input') {
    new KeyboardInput(correctCanvasID, stateXML, callBack);
  }
  else {
    new MusThGUI(correctCanvasID, stateXML, callBack);
  }

};

/**
 * Converts the question options from XML to an object literal.
 *
 * @method convertOptionsXMLtoObjectLiteral
 * @param {String} optionsXML An XML string specifying the Moodle music
 * question's settings.
 * @param {Array} additionalParams An array containing additional parameters
 * not stored in the saved question settings
 * @return {Object literal}
 */
NS.questionRender.convertOptionsXMLtoObjectLiteral = function (optionsXML,
    additionalParams) {

  var parsedXML = Y.XML.parse(optionsXML),
      options = {},
      optionsNode = parsedXML.getElementsByTagName('options')[0].firstChild,
      musicQType = optionsNode.nodeName;

  options.musicQType = musicQType;

  switch (musicQType) {
    case 'note-write':
    case 'note-identify':
      options.clef = optionsNode.getElementsByTagName(
          'clef')[0].firstChild.nodeValue;
      options.includeAlterations = optionsNode.getElementsByTagName(
          'includealterations')[0].firstChild.nodeValue;
      options.includeAlterations = (options.includeAlterations === 'true');
      break;
    case 'keyboard-input':
      options.includestaticnote = optionsNode.getElementsByTagName(
          'includestaticnote')[0].firstChild.nodeValue;
      if (options.includestaticnote === 'true') {
        options.staticnotepitchclass = additionalParams.pitchclass;
        options.staticnoteregister = additionalParams.register;
      }
      break;
    case 'keysignature-write':
    case 'keysignature-identify':
      options.clef = optionsNode.getElementsByTagName(
          'clef')[0].firstChild.nodeValue;
      options.key = optionsNode.getElementsByTagName(
          'key')[0].firstChild.nodeValue;
      break;
    case 'interval-write':
    case 'interval-identify':
      options.clef = optionsNode.getElementsByTagName(
          'clef')[0].firstChild.nodeValue;
      options.givenNote = [];
      options.givenNote.ltr =
          optionsNode.getElementsByTagName('letter')[0].firstChild.nodeValue;
      options.givenNote.acc =
          optionsNode.getElementsByTagName(
              'accidental')[0].firstChild.nodeValue;
      options.givenNote.reg =
          optionsNode.getElementsByTagName('register')[0].firstChild.nodeValue;
      break;
    case 'scale-write':
    case 'scale-identify':
      options.clef = optionsNode.getElementsByTagName(
          'clef')[0].firstChild.nodeValue;
      options.givenNote = [];
      options.givenNote.ltr =
          optionsNode.getElementsByTagName('letter')[0].firstChild.nodeValue;
      options.givenNote.acc =
          optionsNode.getElementsByTagName(
              'accidental')[0].firstChild.nodeValue;
      options.givenNote.reg =
          optionsNode.getElementsByTagName('register')[0].firstChild.nodeValue;
      options.includeKS =
          optionsNode.getElementsByTagName(
              'displaykeysignature')[0].firstChild.nodeValue;
      options.includeKS = (options.includeKS === 'true');
      options.scaleType = optionsNode.getElementsByTagName(
          'scaletype')[0].firstChild.nodeValue;
      break;
    case 'chordquality-write':
    case 'chordquality-identify':
      options.clef = optionsNode.getElementsByTagName(
          'clef')[0].firstChild.nodeValue;
      options.maxNotes = additionalParams.maxnotes;
      break;
    case 'harmonicfunction-write':
      options.maxNotes = additionalParams.maxnotes;
      options.clef = optionsNode.getElementsByTagName(
          'clef')[0].firstChild.nodeValue;
      options.key = optionsNode.getElementsByTagName(
          'key')[0].firstChild.nodeValue;
      options.includeKS =
          optionsNode.getElementsByTagName(
              'displaykeysignature')[0].firstChild.nodeValue;
      options.includeKS = (options.includeKS === 'true');
      break;
    case 'harmonicfunction-identify':
      options.clef = optionsNode.getElementsByTagName(
          'clef')[0].firstChild.nodeValue;
      options.key = optionsNode.getElementsByTagName(
          'key')[0].firstChild.nodeValue;
      options.includeKS =
          optionsNode.getElementsByTagName(
              'displaykeysignature')[0].firstChild.nodeValue;
      options.includeKS = (options.includeKS === 'true');
      break;
  }

  return options;

};

NS.editForm = {};

/**
 * Initializes the javascript functionalities used during music theory question
 * editing.
 *
 * @method initEditForm
 * @return {Undefined}
 */
NS.initEditForm = function () {

  NS.editForm.setOptionVisibility();
  NS.editForm.setFormOptionListeners();

};

/**
 * Attaches listeners to elements in the edit form.
 *
 * @method setFormOptionListeners
 * @return {Undefined}
 */
NS.editForm.setFormOptionListeners = function () {

  Y.all(
      '#id_musictheory_musicqtype').on('change', function () {
    var typeBtnNode = Y.one('#' + 'id_musictheory_updatemusicqtype');
    if (typeBtnNode) {
      typeBtnNode.simulate('click');
    }
  });

};

/**
 * Hides elements in the edit form as necessary.
 *
 * @method setOptionVisibility
 * @return {Undefined}
 */
NS.editForm.setOptionVisibility = function () {

  var typeBtnNode = Y.one('#' + 'id_musictheory_updatemusicqtype');
  if (typeBtnNode) {
    typeBtnNode.hide();
  }

};

/**
 * Returns the key signature of a given key in the given clef.
 *
 * @method getKeySign
 * @param {String} key - The given key (e.g. 'C#M', 'Ebm', 'FM'). Letter names
 * are to be upper case, and the mode is case sensitive ('M' = major,
 * 'm' = minor).
 * @param {String} clef - The clef ('treble', 'bass', 'alto', 'tenor').
 * @return {Array} Returns an array of note names listing the accidentals in
 * the key signature. If the tonic and mode are invalid, returns null.
 */
NS.getKeySign = function (key, clef) {

  var tonic = key.substring(0, key.length - 1),
      mode = key.substr(key.length - 1, 1),
      trebleSharp = new Array('F#5', 'C#5', 'G#5', 'D#5', 'A#4', 'E#5', 'B#4'),
      bassSharp = new Array('F#3', 'C#3', 'G#3', 'D#3', 'A#2', 'E#3', 'B#2'),
      altoSharp = new Array('F#4', 'C#4', 'G#4', 'D#4', 'A#3', 'E#4', 'B#3'),
      tenorSharp = new Array('F#3', 'C#4', 'G#3', 'D#4', 'A#3', 'E#4', 'B#3'),
      trebleFlat = new Array('Bb4', 'Eb5', 'Ab4', 'Db5', 'Gb4', 'Cb5', 'Fb4'),
      bassFlat = new Array('Bb2', 'Eb3', 'Ab2', 'Db3', 'Gb2', 'Cb3', 'Fb2'),
      altoFlat = new Array('Bb3', 'Eb4', 'Ab3', 'Db4', 'Gb3', 'Cb4', 'Fb3'),
      tenorFlat = new Array('Bb3', 'Eb4', 'Ab3', 'Db4', 'Gb3', 'Cb4', 'Fb3'),
      sharpOrFlat = [],
      accList = [],
      numAccMajor = [],
      relativeMajorKeys = [],
      majorTonic,
      acc;

  sharpOrFlat.Cn = 'sharp';
  sharpOrFlat.Gn = 'sharp';
  sharpOrFlat.Dn = 'sharp';
  sharpOrFlat.An = 'sharp';
  sharpOrFlat.En = 'sharp';
  sharpOrFlat.Bn = 'sharp';
  sharpOrFlat['F#'] = 'sharp';
  sharpOrFlat['C#'] = 'sharp';
  sharpOrFlat.Fn = 'flat';
  sharpOrFlat.Bb = 'flat';
  sharpOrFlat.Eb = 'flat';
  sharpOrFlat.Ab = 'flat';
  sharpOrFlat.Db = 'flat';
  sharpOrFlat.Gb = 'flat';
  sharpOrFlat.Cb = 'flat';

  accList.treblesharp = trebleSharp;
  accList.basssharp = bassSharp;
  accList.altosharp = altoSharp;
  accList.tenorsharp = tenorSharp;
  accList.trebleflat = trebleFlat;
  accList.bassflat = bassFlat;
  accList.altoflat = altoFlat;
  accList.tenorflat = tenorFlat;

  numAccMajor.Cn = 0;
  numAccMajor.Gn = 1;
  numAccMajor.Dn = 2;
  numAccMajor.An = 3;
  numAccMajor.En = 4;
  numAccMajor.Bn = 5;
  numAccMajor['F#'] = 6;
  numAccMajor['C#'] = 7;
  numAccMajor.Fn = 1;
  numAccMajor.Bb = 2;
  numAccMajor.Eb = 3;
  numAccMajor.Ab = 4;
  numAccMajor.Db = 5;
  numAccMajor.Gb = 6;
  numAccMajor.Cb = 7;

  relativeMajorKeys.An = 'Cn';
  relativeMajorKeys.En = 'Gn';
  relativeMajorKeys.Bn = 'Dn';
  relativeMajorKeys['F#'] = 'An';
  relativeMajorKeys['C#'] = 'En';
  relativeMajorKeys['G#'] = 'Bn';
  relativeMajorKeys['D#'] = 'F#';
  relativeMajorKeys['A#'] = 'C#';
  relativeMajorKeys.Dn = 'Fn';
  relativeMajorKeys.Gn = 'Bb';
  relativeMajorKeys.Cn = 'Eb';
  relativeMajorKeys.Fn = 'Ab';
  relativeMajorKeys.Bb = 'Db';
  relativeMajorKeys.Eb = 'Gb';
  relativeMajorKeys.Ab = 'Cb';

  majorTonic = (mode === 'M') ? tonic : relativeMajorKeys[tonic];
  acc = accList[clef + sharpOrFlat[majorTonic]];
  acc = acc.slice(0, numAccMajor[majorTonic]);

  return acc;

};// This file is part of Moodle - http://moodle.org/
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

/**
 * The XMLConverter object takes care of converting canvas
 * options into a GUI XML input.
 *
 * @class XMLConverter
 *
 * @constructor
 * @param {Object literal} options The canvas options. Properties include:
 * <ul>
 *   <li><b>{String} musicQType</b>: Indicates the type of canvas to instantiate.
 *   This index can have the following string values:
 *     <ul>
 *     <li>'note-write'</li>
 *     <li>'keyboard-input'</li>
 *     <li>'keysignature-write'</li>
 *     <li>'keysignature-identify'</li>
 *     <li>'interval-identify'</li>
 *     <li>'keysignature-identify'</li>
 *     <li>'scale-write'</li>
 *     </ul>
 *   </li>
 *   <li><b>{String} clef</b>: Indicates which clef to use ('treble', 'bass',
 *   'alto', 'tenor').
 *   </li>
 *   <li><b>{Boolean} includeKS</b>: Indicates whether a key signature should be
 *   displayed.
 *   </li>
 *   <li><b>{Boolean} includeAlterations</b>: Indicates whether alterations should
 *   be included in note writing/identification questions.
 *   </li>
 *   <li><b>{String} key</b>: Indicates the key and mode, for key signature
 *   display purposes (e.g. 'C#M', 'gm'). Mandatory if includeKS is set to true.
 *   </li>
 *   <li><b>{String} givenNote</b>: Specifies a note to display in terms of
 *   letter name (.ltr), accidental (.acc) and register (.reg)
 *   <li><b>{String} scaleType</b>: Specifies the type of minor scale ('natural',
 *   'harmonic', 'melodic').
 *   <li><b>{String} givenNote</b>: Provides a static note to be
 *   displayed in scale and interval questions (e.g. 'Ab4'). This note will not
 *   be editable. It will be included as part of the answer for scale types, but
 *   not included for interval types.
 *   </li>
 *   <li><b>{Boolean} editable</b>: Indicates whether the canvas will be
 *   editable after initialization. Mandatory for all canvas types.
 *   </li>
 *   <li><b>{Boolean} containsUserInput</b>: Indicates whether the canvas
 *   display elements were added by the user. If not, display all notes
 *   in black (to indicate that Moodle has generated them).
 *   </li>
 *   <li><b>{Boolean} maxNotes</b>: Indicates the maximum number of notes the
 *   note column can contain.
 *   </li>
 * </ul>
 * @return {undefined}
 */
NS.XMLConverter = function(options) {

  this.options = options;
  this.setNotesEditable = (options.containsUserInput) ? true : false;

};

/**
 * Converts the options and initial input into an XML string that can be
 * passed to a GUI instance.
 *
 * @method getCanvasXML
 * @param {String} input Provides the initial input as a string,
 * formatted according to the canvas type. An empty string is used to
 * indicate a blank input. Input formats are the following:
 *    <ul>
 *      <li>for scales: e.g. 'A3,B3,C#4,D4,E4,F#4,G#4,A4'</li>
 *      <li>for intervals: e.g. 'G#3'</li>
 *      <li>for key signatures: e.g. 'F#5,C#5,G#5'</li>
 *    </li>
 *   </ul>
 * @return {String} A MusThGUI compatible XML string.
 */
NS.XMLConverter.prototype.getCanvasXML = function(input) {

  switch (this.options.musicQType) {
    case 'note-write':
      return this.getNoteWriteXML(input);
    case 'note-identify':
      return this.getNoteIdentifyXML(input);
    case 'keyboard-input':
      return this.getKeyboardInputXML(input);
    case 'keysignature-write':
      return this.getKSWriteXML(input);
    case 'keysignature-identify':
      return this.getKSIdentifyXML(input);
    case 'interval-write':
      return this.getIntervalWriteXML(input);
    case 'interval-identify':
      return this.getIntervalIdentifyXML(input);
    case 'scale-write':
      return this.getScaleWriteXML(input);
    case 'scale-identify':
      return this.getScaleIdentifyXML(input);
    case 'chordquality-write':
      return this.getChordQualityWriteXML(input);
    case 'harmonicfunction-write':
      return this.getHarmonicFunctionWriteXML(input);
    case 'harmonicfunction-identify':
    case 'chordquality-identify':
      return this.getHarmonicFunctionIdentifyXML(input);
    default:
      return null;
  }

};

/**
 * Converts the GUI's state from a XML to a simpler string describing
 * the state in a format suited for the Moodle music question subtype.
 *
 * @method getCanvasTextOutput
 * @param {String} stateXML The MusThGUI canvas' state, as XML
 * @return {String} A string describing the state in a format compatible with
 * the Moodle music question subtype.
 */
NS.XMLConverter.prototype.getCanvasTextOutput =
    function(stateXML) {
      switch (this.options.musicQType) {
        case 'note-write':
          return this.getNoteWriteTextOutput(stateXML);
        case 'keyboard-input':
          return this.getKeyboardInputTextOutput(stateXML);
        case 'keysignature-write':
          return this.getKSWriteTextOutput(stateXML);
        case 'interval-write':
          return this.getIntervalWriteTextOutput(stateXML);
        case 'scale-write':
          return this.getScaleWriteTextOutput(stateXML);
        case 'chordquality-write':
        case 'harmonicfunction-write':
          return this.getHarmonicfunctionWriteTextOutput(stateXML);
        default:
          return '';
      }
      return '';
    };

/**
 * Converts the MusThGUI's state from a XML to a simpler string describing
 * the state in a format suited for note writing questions.
 *
 * @method getNoteWriteTextOutput
 * @param {String} stateXML The MusThGUI canvas' state, as XML
 * @return {String} A string describing the state in a format compatible for
 * note writing questions.
 */
NS.XMLConverter.prototype.getNoteWriteTextOutput =
    function(stateXML) {

      var parsedXML,
          acc,
          respString,
          noteXML,
          staffXML,
          i,
          j;


      parsedXML = Y.XML.parse(stateXML);
      respString = '';

      staffXML = parsedXML.getElementsByTagName('Staff');
      for (j = 0; j < staffXML.length; j++) {
        noteXML = staffXML[j].getElementsByTagName('Note');
        for (i = 0; i < noteXML.length; i++) {
          if (noteXML[i].getAttribute('editable') === 'true') {
            acc = noteXML[i].getAttribute('accidental');
            respString += noteXML[i].getAttribute('letter') + acc +
                noteXML[i].getAttribute('register') +
                '-' + staffXML[j].getAttribute('clef') + ',';
          }
        }
      }
      if (respString.length > 0) {
        respString = respString.substr(0, respString.length - 1);
      }
      else {
        respString = '';
      }
      return respString;

    };

/**
 * Converts the options and initial input for a note writing question
 * into an XML string that can be passed to a MusThGUI instance.
 *
 * @method getNoteWriteXML
 * @param {String} input Provides the initial input as a string, formatted
 * as a Moodle note writing response.
 * @return {String} A MusThGUI compatible XML string.
 */
NS.XMLConverter.prototype.getNoteWriteXML = function(input) {

  var stateXML,
      inputParts,
      inputSplit,
      trebleNotes = [],
      bassNotes = [],
      i;

  stateXML = '<MusThGUI canvasEditable="' + this.options.editable +
      '" accidentalCarryOver="true">\n';

  if (this.options.clef !== 'grandstaff') {
    stateXML += '<StaffSystem maxLedgerLines="4">\n';
    stateXML += '<Staff clef="' + this.options.clef + '">\n';
    stateXML += '<KeySign totalAccColumns="0" >';
    stateXML += '</KeySign>';
    stateXML += '<NoteColumns>';
    stateXML += '<NoteColumn maxNotes="1" >';

    if (input !== '' && input !== null && typeof(input) !== 'undefined') {
      inputParts = this.noteNameToParts(input);
      stateXML += '<Note letter="' + inputParts.ltr + '" ';
      stateXML += 'register="' + inputParts.reg + '" ';
      stateXML += 'accidental="' + inputParts.acc + '" ';
      stateXML += 'noteValue="whole" editable="' + this.setNotesEditable + '" />';
    }

    stateXML += '</NoteColumn>';
    stateXML += '</NoteColumns>';

    stateXML += '</Staff>\n';
    stateXML += '</StaffSystem>\n';
  }
  else {
    if (input !== '' && input !== null && typeof(input) !== 'undefined') {
      inputSplit = input.split(',');
      for (i = 0; i < inputSplit.length; i++) {
        inputParts = this.noteNameToParts(inputSplit[i]);
        if (inputParts.clef === 'treble') {
          trebleNotes.push(inputParts);
        }
        else if (inputParts.clef === 'bass') {
          bassNotes.push(inputParts);
        }
        else {
          if (parseInt(inputParts.reg, 10) >= 4) {
            trebleNotes.push(inputParts);
          }
          else {
            bassNotes.push(inputParts);
          }
        }
      }
    }
    stateXML += '<StaffSystem maxLedgerLines="4">\n';
    stateXML += '<Staff clef="treble">\n';
    stateXML += '<KeySign totalAccColumns="0" >';
    stateXML += '</KeySign>';
    stateXML += '<NoteColumns>';
    stateXML += '<NoteColumn maxNotes="1" >';
    if (trebleNotes.length > 0) {
      stateXML += '<Note letter="' + trebleNotes[0].ltr + '" ';
      stateXML += 'register="' + trebleNotes[0].reg + '" ';
      stateXML += 'accidental="' + trebleNotes[0].acc + '" ';
      stateXML += 'noteValue="whole" editable="' + this.setNotesEditable + '" />';
    }
    stateXML += '</NoteColumn>';
    stateXML += '</NoteColumns>';
    stateXML += '</Staff>\n';
    stateXML += '<Staff clef="bass">\n';
    stateXML += '<KeySign totalAccColumns="0" >';
    stateXML += '</KeySign>';
    stateXML += '<NoteColumns>';
    stateXML += '<NoteColumn maxNotes="1" >';
    if (bassNotes.length > 0) {
      stateXML += '<Note letter="' + bassNotes[0].ltr + '" ';
      stateXML += 'register="' + bassNotes[0].reg + '" ';
      stateXML += 'accidental="' + bassNotes[0].acc + '" ';
      stateXML += 'noteValue="whole" editable="' + this.setNotesEditable + '" />';
    }
    stateXML += '</NoteColumn>';
    stateXML += '</NoteColumns>';
    stateXML += '</Staff>\n';
    stateXML += '</StaffSystem>\n';
  }

  if (this.options.includeAlterations) {
    stateXML += '    <Toolbars>\n';
    stateXML += '        <AccidentalToolbar>\n';
    stateXML += '            <Button symbol="n"/>';
    stateXML += '            <Button symbol="#"/>';
    stateXML += '            <Button symbol="b"/>';
    stateXML += '            <Button symbol="x"/>';
    stateXML += '            <Button symbol="bb"/>';
    stateXML += '        </AccidentalToolbar>\n';
    stateXML += '    </Toolbars>\n';
  }
  stateXML += '</MusThGUI>';

  return stateXML;
};

/**
 * Converts the options and initial input for a note identification question
 * into an XML string that can be passed to a MusThGUI instance.
 *
 * @method getNoteIdentifyXML
 * @param {String} input Provides the initial input as a string, formatted
 * as a Moodle key signature writing response.
 * @return {String} A MusThGUI compatible XML string.
 */
NS.XMLConverter.prototype.getNoteIdentifyXML = function(input) {

  var stateXML,
      inputParts;

  inputParts = this.noteNameToParts(input);

  if (this.options.clef !== 'grandstaff') {
    stateXML = '<MusThGUI canvasEditable="' + this.options.editable +
        '" accidentalCarryOver="true">\n';
    stateXML += '<StaffSystem maxLedgerLines="4">\n';
    stateXML += '<Staff clef="' + this.options.clef + '">\n';
    stateXML += '<KeySign totalAccColumns="0" >';
    stateXML += '</KeySign>';
    stateXML += '<NoteColumns>';
    stateXML += '<NoteColumn maxNotes="1" >';

    inputParts = this.noteNameToParts(input);
    stateXML += '<Note letter="' + inputParts.ltr + '" ';
    stateXML += 'register="' + inputParts.reg + '" ';
    stateXML += 'accidental="' + inputParts.acc + '" ';
    stateXML += 'noteValue="whole" editable="false" />';

    stateXML += '</NoteColumn>';
    stateXML += '</NoteColumns>';

    stateXML += '</Staff>\n';
    stateXML += '</StaffSystem>\n';
    stateXML += '</MusThGUI>';
  }
  else {
    stateXML = '<MusThGUI canvasEditable="' + this.options.editable +
        '" accidentalCarryOver="true">\n';
    stateXML += '<StaffSystem maxLedgerLines="4">\n';
    stateXML += '<Staff clef="treble">\n';
    stateXML += '<KeySign totalAccColumns="0" >';
    stateXML += '</KeySign>';
    stateXML += '<NoteColumns>';
    stateXML += '<NoteColumn maxNotes="1" >';

    if (parseInt(inputParts.reg, 10) >= 4) {
      stateXML += '<Note letter="' + inputParts.ltr + '" ';
      stateXML += 'register="' + inputParts.reg + '" ';
      stateXML += 'accidental="' + inputParts.acc + '" ';
      stateXML += 'noteValue="whole" editable="false" />';
    }
    stateXML += '</NoteColumn>';
    stateXML += '</NoteColumns>';

    stateXML += '</Staff>\n';

    stateXML += '<Staff clef="bass">\n';
    stateXML += '<KeySign totalAccColumns="0" >';
    stateXML += '</KeySign>';
    stateXML += '<NoteColumns>';
    stateXML += '<NoteColumn maxNotes="1" >';

    if (parseInt(inputParts.reg, 10) < 4) {
      stateXML += '<Note letter="' + inputParts.ltr + '" ';
      stateXML += 'register="' + inputParts.reg + '" ';
      stateXML += 'accidental="' + inputParts.acc + '" ';
      stateXML += 'noteValue="whole" editable="false" />';
    }

    stateXML += '</NoteColumn>';
    stateXML += '</NoteColumns>';

    stateXML += '</Staff>\n';

    stateXML += '</StaffSystem>\n';
    stateXML += '</MusThGUI>';
  }
  return stateXML;
};

/**
 * Converts the GUI's state from a XML to a simpler string describing
 * the state in a format suited for keyboard input questions.
 *
 * @method getKeyboardInputTextOutput
 * @param {String} stateXML The KeyboardInput canvas' state, as XML
 * @return {String} A string describing the state in a format compatible for
 * keyboard input questions.
 */
NS.XMLConverter.prototype.getKeyboardInputTextOutput =
    function(stateXML) {

      var parsedXML,
          pitchClass,
          register,
          respString,
          selKeysXML,
          i;

      parsedXML = Y.XML.parse(stateXML);
      respString = '';

      selKeysXML = parsedXML.getElementsByTagName('selectedkey');
      for (i = 0; i < selKeysXML.length; i++) {
        pitchClass = selKeysXML[i].getAttribute('pitchclass');
        register = selKeysXML[i].getAttribute('register');
        respString += pitchClass + '-' + register + ',';
      }
      if (respString.length > 0) {
        respString = respString.substr(0, respString.length - 1);
      }
      else {
        respString = '';
      }

      return respString;

    };

/**
 * Converts the options and initial input for a keyboard input question
 * into an XML string that can be passed to a KeyboardInput instance.
 *
 * @method getKeyboardInputXML
 * @param {String} input Provides the initial input as a string, formatted
 * as a Moodle keyboard input writing response.
 * @return {String} A KeyboardInput compatible XML string.
 */
NS.XMLConverter.prototype.getKeyboardInputXML = function(input) {

  var stateXML;

  stateXML = '<keyboardinput maxkeys="1" canvasEditable="' +
      this.options.editable + '">\n';
  stateXML += '<givenkeys>\n';
  if (this.options.includestaticnote === 'true') {
    stateXML += '<givenkey pitchclass="' + this.options.staticnotepitchclass +
        '" register="' + this.options.staticnoteregister + '" />';
  }
  stateXML += '</givenkeys>\n';
  stateXML += '<selectedkeys>\n';
  if (input !== '' && input !== null && typeof(input) !== 'undefined') {
    var resp = input.split('-');
    var pitchClass = resp[0];
    var register = resp[1];
    stateXML += '<selectedkey pitchclass = "' + pitchClass + '" register = "' +
        register + '" />';
  }
  stateXML += '</selectedkeys>\n';
  stateXML += '</keyboardinput>';

  return stateXML;
};

/**
 * Converts the MusThGUI's state from a XML to a simpler string describing
 * the state in a format suited for key signature writing questions.
 *
 * @method getKSWriteTextOutput
 * @param {String} stateXML The MusThGUI canvas' state, as XML
 * @return {String} A string describing the state in a format compatible for key
 * signature writing questions.
 */
NS.XMLConverter.prototype.getKSWriteTextOutput = function(stateXML) {

  var parsedXML = Y.XML.parse(stateXML),
      respString = '',
      accXML = parsedXML.getElementsByTagName('Accidental'),
      i;

  for (i = 0; i < accXML.length; i++) {
    respString += accXML[i].getAttribute('letter') +
        accXML[i].getAttribute('type') +
        accXML[i].getAttribute('register') + ',';
  }
  if (respString.length > 0) {
    respString = respString.substr(0, respString.length - 1);
  }
  else {
    respString = '';
  }
  return respString;

};

/**
 * Converts the options and initial input for a key signature writing question
 * into an XML string that can be passed to a MusThGUI instance.
 *
 * @method getKSWriteXML
 * @param {String} input Provides the initial input as a string, formatted
 * as a Moodle key signature writing response.
 * @return {String} A MusThGUI compatible XML string.
 */
NS.XMLConverter.prototype.getKSWriteXML = function(input) {

  var stateXML,
      resp,
      i;

  stateXML = '<MusThGUI canvasEditable="' + this.options.editable +
      '" accidentalCarryOver="true">\n';
  stateXML += '<StaffSystem maxLedgerLines="0">\n';
  stateXML += '<Staff clef="' + this.options.clef + '">\n';
  stateXML += '<KeySign totalAccColumns="7">\n';

  if (input === '' || input === null || typeof(input) === 'undefined') {
    resp = null;
  }
  else {
    resp = input.split(',');
  }

  if (resp !== null) {
    for (i = 0; i < resp.length; i++) {
      stateXML += '<Accidental type="' + resp[i].substr(1, 1) +
          '" letter="' + resp[i].substr(0, 1) +
          '" register="' + resp[i].substr(2, 1) +
          '" editable="' + this.setNotesEditable + '" />\n';
    }
  }

  stateXML += '</KeySign>\n';
  stateXML += '</Staff>\n';
  stateXML += '</StaffSystem>\n';
  stateXML += '    <Toolbars>\n';
  stateXML += '        <AccidentalToolbar>\n';
  stateXML += '            <Button symbol="#"/>\n';
  stateXML += '            <Button symbol="b"/>\n';
  stateXML += '        </AccidentalToolbar>\n';
  stateXML += '    </Toolbars>\n';
  stateXML += '</MusThGUI>';

  return stateXML;
};

/**
 * Converts the options and initial input for a key signature identification
 * question into an XML string that can be passed to a MusThGUI instance.
 *
 * @method getKSIdentifyXML
 * @param {String} input Provides the initial input as a string, formatted
 * as a Moodle key signature identification response.
 * @return {String} A MusThGUI compatible XML string.
 */
NS.XMLConverter.prototype.getKSIdentifyXML = function(input) {

  var stateXML,
      resp,
      i;

  stateXML = '<MusThGUI canvasEditable="' + this.options.editable +
      '" accidentalCarryOver="true">\n';
  stateXML += '<StaffSystem maxLedgerLines="0">\n';
  stateXML += '<Staff clef="' + this.options.clef + '">\n';
  stateXML += '<KeySign totalAccColumns="7">\n';

  if (input === '' || input === null || typeof(input) === 'undefined') {
    resp = null;
  }
  else {
    resp = input.split(',');
  }

  if (resp !== null) {
    for (i = 0; i < resp.length; i++) {
      stateXML += '<Accidental type="' + resp[i].substr(1, 1) +
          '" letter="' + resp[i].substr(0, 1) +
          '" register="' + resp[i].substr(2, 1) +
          '" editable="false" />\n';
    }
  }

  stateXML += '</KeySign>\n';
  stateXML += '</Staff>\n';
  stateXML += '</StaffSystem>\n';
  stateXML += '    <Toolbars>\n';
  stateXML += '        <AccidentalToolbar>\n';
  stateXML += '            <Button symbol="#"/>\n';
  stateXML += '            <Button symbol="b"/>\n';
  stateXML += '        </AccidentalToolbar>\n';
  stateXML += '    </Toolbars>\n';
  stateXML += '</MusThGUI>';

  return stateXML;
};

/**
 * Converts the MusThGUI's state from a XML to a simpler string describing
 * the state in a format suited for scale writing questions.
 *
 * @method getScaleWriteTextOutput
 * @param {String} stateXML The MusThGUI canvas' state, as XML
 * @return {String} A string describing the state in a format compatible for
 * scale writing questions.
 */
NS.XMLConverter.prototype.getScaleWriteTextOutput =
    function(stateXML) {

      var parsedXML = Y.XML.parse(stateXML),
          respString = '',
          noteColXML = parsedXML.getElementsByTagName('NoteColumn'),
          i = 0,
          noteXML,
          acc;

      for (i = 0; i < noteColXML.length; i++) {
        noteXML = noteColXML[i].getElementsByTagName('Note')[0];
        if (typeof(noteXML) !== 'undefined' && noteXML !== null) {
          acc = noteXML.getAttribute('accidental');
          respString += noteXML.getAttribute('letter') + acc +
              noteXML.getAttribute('register');
        }
        respString += ',';
      }
      for (i; i < 8; i++) {
        respString += ',';
      }
      respString = respString.substr(0, respString.length - 1);
      return respString;

    };

/**
 * Converts the options and initial input for a scale writing
 * question into an XML string that can be passed to a MusThGUI instance.
 *
 * @method getScaleWriteXML
 * @param {String} input Provides the initial input as a string, formatted
 * as a Moodle scale writing response.
 * @return {String} A MusThGUI compatible XML string.
 */
NS.XMLConverter.prototype.getScaleWriteXML = function(input) {

  var i,
      noteParts,
      keySign,
      key = this.options.givenNote.ltr + this.options.givenNote.acc,
      stateXML,
      resp,
      scaleLength;

  if (this.options.scaleType === 'major') {
    key += 'M';
  }
  else {
    key += 'm';
  }
  if (this.options.includeKS) {
    keySign = new NS.getKeySign(key, this.options.clef);
  }
  else {
    keySign = [];
  }

  stateXML = '<MusThGUI canvasEditable="' + this.options.editable +
      '" accidentalCarryOver="' + this.options.includeKS + '">\n';
  stateXML += '<StaffSystem maxLedgerLines="4">\n';
  stateXML += '<Staff clef="' + this.options.clef + '">\n';
  stateXML += '<KeySign totalAccColumns="' + keySign.length + '">\n';
  for (i = 0; i < keySign.length; i++) {
    noteParts = this.noteNameToParts(keySign[i]);
    stateXML += '<Accidental type="' + noteParts.acc + '" letter="' +
        noteParts.ltr + '" register="' + noteParts.reg + '" ' +
        'editable="false" />';
  }
  stateXML += '</KeySign>\n';

  if (input === '' || input === null || typeof(input) === 'undefined') {
    resp = null;
  }
  else {
    resp = input.split(',');
  }

  stateXML += '<NoteColumns>';

  if (resp !== null) {

    for (i = 0; i < resp.length; i++) {
      if (resp[i] !== '') {
        noteParts = this.noteNameToParts(resp[i]);
        stateXML += '<NoteColumn maxNotes="1" >';
        stateXML += '<Note letter="' + noteParts.ltr + '" ';
        stateXML += 'register="' + noteParts.reg + '" ';
        stateXML += 'accidental="' + noteParts.acc + '" ';
        if (i === 0) {
          stateXML += 'noteValue="whole" editable="false" />';
        }
        else {
          stateXML += 'noteValue="whole" editable="' +
              this.setNotesEditable + '" />';
        }
        stateXML += '</NoteColumn>';
      }
      else {
        stateXML += '<NoteColumn maxNotes="1" />';
      }
    }
  }
  else {
    if (this.options.scaleType === 'melodic') {
      scaleLength = 15;
    }
    else {
      scaleLength = 8;
    }
    for (i = 0; i < scaleLength; i++) {
      if (i === 0) {
        stateXML += '<NoteColumn maxNotes="1">';
        stateXML += '<Note letter="' + this.options.givenNote.ltr + '" ';
        stateXML += 'register="' + this.options.givenNote.reg + '" ';
        stateXML += 'accidental="' + this.options.givenNote.acc + '" ';
        stateXML += 'noteValue="whole" editable="false" />';
        stateXML += '</NoteColumn>';
      }
      else {
        stateXML += '<NoteColumn maxNotes="1" />';
      }
    }
  }
  stateXML += '</NoteColumns>';

  stateXML += '</Staff>\n';
  stateXML += '</StaffSystem>\n';
  stateXML += '    <Toolbars>\n';
  stateXML += '        <AccidentalToolbar>\n';
  stateXML += '            <Button symbol="n"/>';
  stateXML += '            <Button symbol="#"/>';
  stateXML += '            <Button symbol="b"/>';
  stateXML += '            <Button symbol="x"/>';
  stateXML += '            <Button symbol="bb"/>';
  stateXML += '        </AccidentalToolbar>\n';
  stateXML += '    </Toolbars>\n';
  stateXML += '</MusThGUI>';

  return stateXML;

};

/**
 * Converts the options and initial input for a scale identification
 * question into an XML string that can be passed to a MusThGUI instance.
 *
 * @method getScaleIdentifyXML
 * @param {String} input Provides the initial input as a string, formatted
 * as a Moodle scale writing response.
 * @return {String} A MusThGUI compatible XML string.
 */
NS.XMLConverter.prototype.getScaleIdentifyXML = function(input) {

  var i,
      noteParts,
      keySign,
      key = this.options.givenNote.ltr + this.options.givenNote.acc,
      stateXML,
      resp,
      scaleLength;

  if (this.options.scaleType === 'major') {
    key += 'M';
  }
  else {
    key += 'm';
  }
  console.log(this.options.scaleType);
  if (this.options.includeKS) {
    keySign = new NS.getKeySign(key, this.options.clef);
  }
  else {
    keySign = [];
  }

  stateXML = '<MusThGUI canvasEditable="' + this.options.editable +
      '" accidentalCarryOver="' + this.options.includeKS + '">\n';
  stateXML += '<StaffSystem maxLedgerLines="4">\n';
  stateXML += '<Staff clef="' + this.options.clef + '">\n';
  stateXML += '<KeySign totalAccColumns="' + keySign.length + '">\n';
  for (i = 0; i < keySign.length; i++) {
    noteParts = this.noteNameToParts(keySign[i]);
    stateXML += '<Accidental type="' + noteParts.acc + '" letter="' +
        noteParts.ltr + '" register="' + noteParts.reg + '" ' +
        'editable="false" />';
  }
  stateXML += '</KeySign>\n';

  if (input === '' || input === null || typeof(input) === 'undefined') {
    resp = null;
  }
  else {
    resp = input.split(',');
  }

  stateXML += '<NoteColumns>';

  if (resp !== null) {

    for (i = 0; i < resp.length; i++) {
      if (resp[i] !== '') {
        noteParts = this.noteNameToParts(resp[i]);
        stateXML += '<NoteColumn maxNotes="1" >';
        stateXML += '<Note letter="' + noteParts.ltr + '" ';
        stateXML += 'register="' + noteParts.reg + '" ';
        stateXML += 'accidental="' + noteParts.acc + '" ';
        stateXML += 'noteValue="whole" editable="false" />';
        stateXML += '</NoteColumn>';
      }
      else {
        stateXML += '<NoteColumn maxNotes="1" />';
      }
    }
  }
  else {
    if (this.options.scaleType === 'melodic') {
      scaleLength = 15;
    }
    else {
      scaleLength = 8;
    }
    for (i = 0; i < scaleLength; i++) {
      if (i === 0) {
        stateXML += '<NoteColumn maxNotes="1">';
        stateXML += '<Note letter="' + this.options.givenNote.ltr + '" ';
        stateXML += 'register="' + this.options.givenNote.reg + '" ';
        stateXML += 'accidental="' + this.options.givenNote.acc + '" ';
        stateXML += 'noteValue="whole" editable="false" />';
        stateXML += '</NoteColumn>';
      }
      else {
        stateXML += '<NoteColumn maxNotes="1" />';
      }
    }
  }
  stateXML += '</NoteColumns>';

  stateXML += '</Staff>\n';
  stateXML += '</StaffSystem>\n';
  stateXML += '</MusThGUI>';

  return stateXML;

};

/**
 * Converts the MusThGUI's state from a XML to a simpler string describing
 * the state in a format suited for interval writing questions.
 *
 * @method getIntervalWriteTextOutput
 * @param {String} stateXML The MusThGUI canvas' state, as XML
 * @return {String} A string describing the state in a format compatible for
 * interval writing questions.
 */
NS.XMLConverter.prototype.getIntervalWriteTextOutput =
    function(stateXML) {

      var parsedXML = Y.XML.parse(stateXML),
          respString = '',
          noteXML = parsedXML.getElementsByTagName('Note'),
          i = 0,
          acc;

      for (i = 0; i < noteXML.length; i++) {
        if (noteXML[i].getAttribute('editable') === 'true') {
          acc = noteXML[i].getAttribute('accidental');
          respString += noteXML[i].getAttribute('letter') + acc +
              noteXML[i].getAttribute('register') + ',';
        }
      }
      if (respString.length > 0) {
        respString = respString.substr(0, respString.length - 1);
      }
      else {
        respString = '';
      }
      return respString;

    };

/**
 * Converts the options and initial input for a interval writing
 * question into an XML string that can be passed to a MusThGUI instance.
 *
 * @method getIntervalWriteXML
 * @param {String} input Provides the initial input as a string, formatted
 * as a Moodle interval writing response.
 * @return {String} A MusThGUI compatible XML string.
 */
NS.XMLConverter.prototype.getIntervalWriteXML = function(input) {

  var stateXML,
      inputParts;

  stateXML = '<MusThGUI canvasEditable="' + this.options.editable +
      '" accidentalCarryOver="true">\n';
  stateXML += '<StaffSystem maxLedgerLines="4">';
  stateXML += '<Staff clef="' + this.options.clef + '">';
  stateXML += '<KeySign totalAccColumns="0" >';
  stateXML += '</KeySign>';
  stateXML += '<NoteColumns>';
  stateXML += '<NoteColumn maxNotes="2" >';
  stateXML += '<Note letter="' + this.options.givenNote.ltr + '" ';
  stateXML += 'register="' + this.options.givenNote.reg + '" ';
  stateXML += 'accidental="' + this.options.givenNote.acc + '" ';
  stateXML += 'noteValue="whole" editable="false" />';

  if (input !== '' && input !== null && typeof(input) !== 'undefined') {
    inputParts = this.noteNameToParts(input);
    stateXML += '<Note letter="' + inputParts.ltr + '" ';
    stateXML += 'register="' + inputParts.reg + '" ';
    stateXML += 'accidental="' + inputParts.acc + '" ';
    stateXML += 'noteValue="whole" editable="' + this.setNotesEditable + '" />';
  }

  stateXML += '</NoteColumn>';
  stateXML += '</NoteColumns>';


  stateXML += '</Staff>';
  stateXML += '</StaffSystem>';
  stateXML += '    <Toolbars>\n';
  stateXML += '        <AccidentalToolbar>\n';
  stateXML += '            <Button symbol="n"/>';
  stateXML += '            <Button symbol="#"/>';
  stateXML += '            <Button symbol="b"/>';
  stateXML += '            <Button symbol="x"/>';
  stateXML += '            <Button symbol="bb"/>';
  stateXML += '        </AccidentalToolbar>\n';
  stateXML += '    </Toolbars>\n';
  stateXML += '</MusThGUI>';

  return stateXML;

};

/**
 * Converts the options and initial input for a interval identification
 * question into an XML string that can be passed to a MusThGUI instance.
 *
 * @method getIntervalWriteXML
 * @param {String} input Provides the initial input as a string, formatted
 * as a Moodle interval identification response.
 * @return {String} A MusThGUI compatible XML string.
 */
NS.XMLConverter.prototype.getIntervalIdentifyXML = function(input) {

  var stateXML,
      inputParts;

  stateXML = '<MusThGUI canvasEditable="' + this.options.editable +
      '" accidentalCarryOver="true">\n';
  stateXML += '<StaffSystem maxLedgerLines="4">';
  stateXML += '<Staff clef="' + this.options.clef + '">';
  stateXML += '<KeySign totalAccColumns="0" >';
  stateXML += '</KeySign>';
  stateXML += '<NoteColumns>';
  stateXML += '<NoteColumn maxNotes="2" >';
  stateXML += '<Note letter="' + this.options.givenNote.ltr + '" ';
  stateXML += 'register="' + this.options.givenNote.reg + '" ';
  stateXML += 'accidental="' + this.options.givenNote.acc + '" ';
  stateXML += 'noteValue="whole" editable="false" />';

  if (input !== '' && input !== null && typeof(input) !== 'undefined') {
    inputParts = this.noteNameToParts(input);
    stateXML += '<Note letter="' + inputParts.ltr + '" ';
    stateXML += 'register="' + inputParts.reg + '" ';
    stateXML += 'accidental="' + inputParts.acc + '" ';
    stateXML += 'noteValue="whole" editable="false" />';
  }

  stateXML += '</NoteColumn>';
  stateXML += '</NoteColumns>';


  stateXML += '</Staff>';
  stateXML += '</StaffSystem>';
  stateXML += '    <Toolbars>\n';
  stateXML += '        <AccidentalToolbar>\n';
  stateXML += '            <Button symbol="n"/>';
  stateXML += '            <Button symbol="#"/>';
  stateXML += '            <Button symbol="b"/>';
  stateXML += '            <Button symbol="x"/>';
  stateXML += '            <Button symbol="bb"/>';
  stateXML += '        </AccidentalToolbar>\n';
  stateXML += '    </Toolbars>\n';
  stateXML += '</MusThGUI>';

  return stateXML;

};

/**
 * Converts the options and initial input for a chord quality writing
 * question into an XML string that can be passed to a MusThGUI instance.
 *
 * @method getHarmonicFunctionWriteXML
 * @param {String} input Provides the initial input as a string, formatted
 * as a Moodle harmonic function writing response.
 * @return {String} A MusThGUI compatible XML string.
 */
NS.XMLConverter.prototype.getChordQualityWriteXML = function(input) {

  var noteParts,
      i,
      stateXML,
      resp;

  stateXML = '<MusThGUI canvasEditable="' + this.options.editable +
      '" accidentalCarryOver="true">\n';
  stateXML += '<StaffSystem maxLedgerLines="4">';
  stateXML += '<Staff clef="' + this.options.clef + '">';

  stateXML += '<KeySign totalAccColumns="0"></KeySign>\n';
  stateXML += '<NoteColumns>';
  stateXML += '<NoteColumn maxNotes="' + this.options.maxNotes + '" >';

  if (input !== '' && input !== null && typeof(input) !== 'undefined') {
    resp = input.split(',');
    for (i = 0; i < resp.length; i++) {
      if (resp[i] !== '') {
        noteParts = this.noteNameToParts(resp[i]);
        stateXML += '<Note letter="' + noteParts.ltr + '" ';
        stateXML += 'register="' + noteParts.reg + '" ';
        stateXML += 'accidental="' + noteParts.acc + '" ';
        stateXML += 'noteValue="whole" editable="' + this.setNotesEditable + '" />';
      }
    }
  }

  stateXML += '</NoteColumn>';
  stateXML += '</NoteColumns>';


  stateXML += '</Staff>';
  stateXML += '</StaffSystem>';
  stateXML += '    <Toolbars>\n';
  stateXML += '        <AccidentalToolbar>\n';
  stateXML += '            <Button symbol="n"/>';
  stateXML += '            <Button symbol="#"/>';
  stateXML += '            <Button symbol="b"/>';
  stateXML += '            <Button symbol="x"/>';
  stateXML += '            <Button symbol="bb"/>';
  stateXML += '        </AccidentalToolbar>\n';
  stateXML += '    </Toolbars>\n';
  stateXML += '</MusThGUI>';

  return stateXML;

};

/**
 * Converts the MusThGUI's state from a XML to a simpler string describing
 * the state in a format suited for harmonic function writing questions.
 *
 * @method getScaleWriteTextOutput
 * @param {String} stateXML The MusThGUI canvas' state, as XML
 * @return {String} A string describing the state in a format compatible for
 * harmonic function writing questions.
 */
NS.XMLConverter.prototype.getHarmonicfunctionWriteTextOutput =
    function(stateXML) {

      var parsedXML = Y.XML.parse(stateXML),
          respString = '',
          noteColXML = parsedXML.getElementsByTagName('NoteColumn'),
          i = 0,
          j = 0,
          noteXML,
          notesXML,
          acc;

      for (i = 0; i < noteColXML.length; i++) {
        notesXML = noteColXML[i].getElementsByTagName('Note');
        for (j = notesXML.length - 1; j >= 0; j--) {
          noteXML = notesXML[j];
          if (typeof(noteXML) !== 'undefined' && noteXML !== null) {
            acc = noteXML.getAttribute('accidental');
            respString += noteXML.getAttribute('letter') + acc +
                noteXML.getAttribute('register');
          }
          respString += ',';
        }
      }

      respString = respString.substr(0, respString.length - 1);
      return respString;

    };

/**
 * Converts the options and initial input for a harmonic function writing
 * question into an XML string that can be passed to a MusThGUI instance.
 *
 * @method getHarmonicFunctionWriteXML
 * @param {String} input Provides the initial input as a string, formatted
 * as a Moodle harmonic function writing response.
 * @return {String} A MusThGUI compatible XML string.
 */
NS.XMLConverter.prototype.getHarmonicFunctionWriteXML = function(input) {

  var keySign,
      noteParts,
      i,
      stateXML,
      resp;

  stateXML = '<MusThGUI canvasEditable="' + this.options.editable +
      '" accidentalCarryOver="true">\n';
  stateXML += '<StaffSystem maxLedgerLines="4">';
  stateXML += '<Staff clef="' + this.options.clef + '">';

  if (this.options.includeKS) {
    keySign = new NS.getKeySign(this.options.key, this.options.clef);
  }
  else {
    keySign = [];
  }
  stateXML += '<KeySign totalAccColumns="' + keySign.length + '">\n';
  for (i = 0; i < keySign.length; i++) {
    noteParts = this.noteNameToParts(keySign[i]);
    stateXML += '<Accidental type="' + noteParts.acc + '" letter="' +
        noteParts.ltr + '" register="' + noteParts.reg + '" ' +
        'editable="false" />';
  }
  stateXML += '</KeySign>\n';
  stateXML += '<NoteColumns>';
  stateXML += '<NoteColumn maxNotes="' + this.options.maxNotes + '" >';

  if (input !== '' && input !== null && typeof(input) !== 'undefined') {
    resp = input.split(',');
    for (i = 0; i < resp.length; i++) {
      if (resp[i] !== '') {
        noteParts = this.noteNameToParts(resp[i]);
        stateXML += '<Note letter="' + noteParts.ltr + '" ';
        stateXML += 'register="' + noteParts.reg + '" ';
        stateXML += 'accidental="' + noteParts.acc + '" ';
        stateXML += 'noteValue="whole" editable="' + this.setNotesEditable + '" />';
      }
    }
  }

  stateXML += '</NoteColumn>';
  stateXML += '</NoteColumns>';


  stateXML += '</Staff>';
  stateXML += '</StaffSystem>';
  stateXML += '    <Toolbars>\n';
  stateXML += '        <AccidentalToolbar>\n';
  stateXML += '            <Button symbol="n"/>';
  stateXML += '            <Button symbol="#"/>';
  stateXML += '            <Button symbol="b"/>';
  stateXML += '            <Button symbol="x"/>';
  stateXML += '            <Button symbol="bb"/>';
  stateXML += '        </AccidentalToolbar>\n';
  stateXML += '    </Toolbars>\n';
  stateXML += '</MusThGUI>';

  return stateXML;

};

/**
 * Converts the options and initial input for a harmonic function identification
 * question into an XML string that can be passed to a MusThGUI instance.
 *
 * @method getHarmonicFunctionIdentifyXML
 * @param {String} input Provides the initial input as a string, formatted
 * as a Moodle harmonic function identification response.
 * @return {String} A MusThGUI compatible XML string.
 */
NS.XMLConverter.prototype.getHarmonicFunctionIdentifyXML = function(input) {

  var keySign,
      noteParts,
      i,
      stateXML,
      resp;

  stateXML = '<MusThGUI canvasEditable="' + this.options.editable +
      '" accidentalCarryOver="true">\n';
  stateXML += '<StaffSystem maxLedgerLines="4">';
  stateXML += '<Staff clef="' + this.options.clef + '">';

  if (this.options.includeKS) {
    keySign = new NS.getKeySign(this.options.key, this.options.clef);
  }
  else {
    keySign = [];
  }
  stateXML += '<KeySign totalAccColumns="' + keySign.length + '">\n';
  for (i = 0; i < keySign.length; i++) {
    noteParts = this.noteNameToParts(keySign[i]);
    stateXML += '<Accidental type="' + noteParts.acc + '" letter="' +
        noteParts.ltr + '" register="' + noteParts.reg + '" ' +
        'editable="false" />';
  }
  stateXML += '</KeySign>\n';
  stateXML += '<NoteColumns>';
  if (input !== '' && input !== null && typeof(input) !== 'undefined') {
    resp = input.split(',');
    stateXML += '<NoteColumn maxNotes="' + resp.length + '" >';
    for (i = 0; i < resp.length; i++) {
      if (resp[i] !== '') {
        noteParts = this.noteNameToParts(resp[i]);
        stateXML += '<Note letter="' + noteParts.ltr + '" ';
        stateXML += 'register="' + noteParts.reg + '" ';
        stateXML += 'accidental="' + noteParts.acc + '" ';
        stateXML += 'noteValue="whole" editable="false" />';
      }
    }
    stateXML += '</NoteColumn>';
  }
  else {
    stateXML += '<NoteColumn maxNotes="0" />';
  }
  stateXML += '</NoteColumns>';

  stateXML += '</Staff>';
  stateXML += '</StaffSystem>';
  stateXML += '</MusThGUI>';

  return stateXML;

};

/**
 * Converts a note represented as a string to an object literal with properties
 * for letter name, accidental and register.
 *
 * @method noteNameToParts
 * @param {String} noteName The note name.
 * @return {Object literal} An object literal with 3 string properties: ltr, acc
 * and reg.
 */
NS.XMLConverter.prototype.noteNameToParts = function(noteName) {
  var parts = [],
      clefSeparator;
  if (noteName.search('-') < 0) {
    clefSeparator = noteName.length;
  }
  else {
    clefSeparator = noteName.search('-');
  }
  parts.ltr = noteName.substr(0, 1);
  parts.acc = noteName.substr(1, clefSeparator - 2);
  parts.reg = noteName.substr(clefSeparator - 1, 1);
  if (noteName.search('-') >= 0) {
    parts.clef = noteName.substr(clefSeparator + 1);
  }
  else {
    parts.clef = '';
  }

  return parts;
};

}, '@VERSION@', {"requires": ["base", "node", "datatype", "node-event-simulate"]});
