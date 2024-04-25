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
    } else {
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
    } else {
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
        }
    );

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

};