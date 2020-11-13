YUI.add('moodle-qtype_musictheory-musictheoryui', function (Y, NAME) {

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

M.qtype_musictheory = M.qtype_musictheory || {};

M.qtype_musictheory.musictheoryui = {
  init: function() {
  }
};

/* Copyright (c) 2013 Eric Brisson

 Permission is hereby granted, free of charge, to any person obtaining a copy
 of this software and associated documentation files (the "Software"), to deal
 in the Software without restriction, including without limitation the rights
 to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 copies of the Software, and to permit persons to whom the Software is
 furnished to do so, subject to the following conditions:

 The above copyright notice and this permission notice shall be included in
 all copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 THE SOFTWARE. */

/**
 The Music Theory GUI module allows for display and entry of notes in various
 contexts that are suitable for music theory exercises.

 The user interface is created by instantiating a {{#crossLink "MusThGUI"}}
 {{/crossLink}} object.

 Here is an example describing how to use this module:

 1) Add an HTMLtag for a small canvas in the web page source, with a unique 'id'
 attribute.

	<canvas id="myUniqueID" width="1" height="1"/>

 2) Define an XML string that will describe the interface's initial state. Here
 is an example that would be well-suited for a scale question:

	var initXML = '<MusThGUI canvasEditable="true" accidentalCarryOver="true">' +
	'<StaffSystem maxLedgerLines="3">' +
	'<Staff clef="treble">' +
	'<KeySign totalAccColumns="7" >' +
	'<Accidental type="#" letter="F" register="5" editable="false" />' +
	'<Accidental type="#" letter="C" register="5" editable="false" />' +
	'<Accidental type="#" letter="G" register="5" editable="false" />' +
	'</KeySign>' +
	'<NoteColumns>' +
	'<NoteColumn maxNotes="1" />' +
	'<NoteColumn maxNotes="1" />' +
	'<NoteColumn maxNotes="1" />' +
	'<NoteColumn maxNotes="1" />' +
	'<NoteColumn maxNotes="1" />' +
	'<NoteColumn maxNotes="1" />' +
	'<NoteColumn maxNotes="1" />' +
	'<NoteColumn maxNotes="1" />' +
	'</NoteColumns>' +
	'</Staff>' +
	'</StaffSystem>' +
	'<Toolbars>' +
	'<AccidentalToolbar>' +
	'<Button symbol="n" />' +
	'<Button symbol="#" />' +
	'<Button symbol="b" />' +
	'<Button symbol="##" />' +
	'<Button symbol="bb" />' +
	'</AccidentalToolbar>' +
	'</Toolbars>' +
	'</MusThGUI>';

 For more details on the XML schema that the interface will accept and return,
 see the 'valid_input_xml.xsd' file in the module's XML directory.

 3) Define a callback function that the interface will use to message back its
 current state. This function will be called whenever the user changes the
 interface's state (i.e. by adding or deleting notes on the staff).

	var callBack = function(stateXML) {
	//stateXML will contain the most up-to-date canvas state.
	};

 4) Create a {{#crossLink "MusThGUI"}}{{/crossLink}} object using the canvas id
 from step 1, the XML string from step 2 and the callback function from step 3:

	var musicCanvas = new MusThGUI('myUniqueID', initXML, callBack, true);

 Argument 4 specifies whether the interface should be editable after
 initialization. Use a false value to display a static interface.

 Once instantiated, the MusThGUI object will display an interactive music canvas
 on the web page. Whenever the user makes any changes, the callback function will
 be called and the most up-to-date canvas state will be returned, as an XML
 string.

 @module MusThGUI
 **/

/**
 The MusThGUI object is used to interact with outside components.

 @class MusThGUI
 @constructor

 @param {String} canID The id of the HTMl 5 canvas to which this application
 will be bound.
 @param {String} stateXML The canvas state as xml.
 @param {Function} respCallBack A function that the controller will use
 to send the current canvas answer back the caller, whenever the state
 changes. This function should accept one argument which provides the
 current XML output.
 @param {Boolean} editable Specifies whether the user can change the UI
 after initialization.
 @return {undefined}
 **/
MusThGUI = function(canID, stateXML, respCallBack) {
  this.callBack = function(cBack) {
    return function(stateXML) {
      cBack(stateXML);
    };
  };

  this.UI = new MusThGUI.Control.Controller(canID, stateXML,
      this.callBack(respCallBack));
};/* Copyright (c) 2013 Eric Brisson

 Permission is hereby granted, free of charge, to any person obtaining a copy
 of this software and associated documentation files (the "Software"), to deal
 in the Software without restriction, including without limitation the rights
 to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 copies of the Software, and to permit persons to whom the Software is
 furnished to do so, subject to the following conditions:

 The above copyright notice and this permission notice shall be included in
 all copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 THE SOFTWARE. */

MusThGUI.GUIState = {};/* Copyright (c) 2013 Eric Brisson

 Permission is hereby granted, free of charge, to any person obtaining a copy
 of this software and associated documentation files (the "Software"), to deal
 in the Software without restriction, including without limitation the rights
 to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 copies of the Software, and to permit persons to whom the Software is
 furnished to do so, subject to the following conditions:

 The above copyright notice and this permission notice shall be included in
 all copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 THE SOFTWARE. */

MusThGUI.Render = {};/* Copyright (c) 2013 Eric Brisson

 Permission is hereby granted, free of charge, to any person obtaining a copy
 of this software and associated documentation files (the "Software"), to deal
 in the Software without restriction, including without limitation the rights
 to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 copies of the Software, and to permit persons to whom the Software is
 furnished to do so, subject to the following conditions:

 The above copyright notice and this permission notice shall be included in
 all copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 THE SOFTWARE. */

/**
 * The Renderer object takes care of drawing all canvas elements.
 *
 * @class Renderer
 * @namespace Render
 *
 * @constructor
 * @param {Canvas} can An html5 canvas element.
 * @param {GUIState.State} state The canvas' state.
 * @param {Render.CoordManager} coordMgr The library's coordinate
 * manager.
 * @param {Render.GlyphProvider} glyphProvider The library's glyph
 * provider.
 * @return {undefined}
 */
MusThGUI.Render.Renderer = function(can, state, coordMgr, glyphProvider) {

  this.can = can;
  this.state = state;
  this.coordMgr = coordMgr;
  this.glyphProvider = glyphProvider;

};

/**
 * Draws all canvas elements.
 *
 * @method draw
 * @return {undefined}
 */
MusThGUI.Render.Renderer.prototype.draw = function() {

  var staffSystemRend = new MusThGUI.Render.StaffSystemRend(this.can,
      this.coordMgr, this.glyphProvider, this.state.staffSystem,
      this.state.accCarryOver);
  staffSystemRend.draw();

  var i;
  for (i = 0; i < this.state.toolbars.length; i++) {
    var toolbarRend = new MusThGUI.Render.ToolbarRend(this.can, this.coordMgr,
        this.glyphProvider, this.state.toolbars[i]);
    toolbarRend.draw(i);
  }

};/* Copyright (c) 2013 Eric Brisson

 Permission is hereby granted, free of charge, to any person obtaining a copy
 of this software and associated documentation files (the "Software"), to deal
 in the Software without restriction, including without limitation the rights
 to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 copies of the Software, and to permit persons to whom the Software is
 furnished to do so, subject to the following conditions:

 The above copyright notice and this permission notice shall be included in
 all copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 THE SOFTWARE. */

MusThGUI.Control = {};/* Copyright (c) 2013 Eric Brisson

 Permission is hereby granted, free of charge, to any person obtaining a copy
 of this software and associated documentation files (the "Software"), to deal
 in the Software without restriction, including without limitation the rights
 to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 copies of the Software, and to permit persons to whom the Software is
 furnished to do so, subject to the following conditions:

 The above copyright notice and this permission notice shall be included in
 all copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 THE SOFTWARE. */

/**
 * The Controller object instantiates the canvas and handles mouse
 * events.
 *
 * @class Controller
 * @namespace Control
 *
 * @constructor
 * @param {String} canID The id of the HTMl 5 canvas to which this application
 * will be bound.
 * @param {String} stateXML The canvas state as xml.
 * @param {Function} callBackFunc A function that the controller will use
 * to send the current canvas state back the caller, whenever the state changes.
 * This function should accept one argument which provides the current state of
 * the canvas, as an xml string.
 * @return {undefined}
 */
MusThGUI.Control.Controller = function(canID, stateXML, callBackFunc) {

	this.canID = canID;
	this.canNode = Y.one('#' + canID);
	this.html5Can = this.canNode.getDOMNode();
	this.ctx = this.html5Can.getContext('2d');
	this.stateXML = Y.XML.parse(stateXML);
	this.state = new MusThGUI.GUIState.State();
	this.state.setState(this.stateXML);
	this.coordMgr = new MusThGUI.Render.CoordManager(this.state);
	this.html5Can.width = this.coordMgr.canvasWidth;
	this.html5Can.height = this.coordMgr.canvasHeight;
	this.callBack = callBackFunc;
	this.inputDialog = null;
	this.bindEventListeners();
	this.ghostNote = null;
	this.ghostAcc = null;

	var parent = this;
	this.glyphProvider = new MusThGUI.Render.GlyphProvider(function() {
		parent.onImagesLoaded();
	});

};

/**
 * Callback function indicating that canvas images have been loaded. Finishes
 * canvases initialization.
 *
 * @method onImagesLoaded
 * @return {undefined}
 */
MusThGUI.Control.Controller.prototype.onImagesLoaded = function() {

	this.renderer = new MusThGUI.Render.Renderer(this.html5Can, this.state,
			this.coordMgr, this.glyphProvider);
	this.drawState();

};

/**
 * Translates the window-based point location of a mouse event into a
 * canvas-based location.
 *
 * @method getLocalCoord
 * @param {Event} e The mouse event.
 * @return {Object} An object literal with properties x and y, representing
 * the canvas based location.
 */
MusThGUI.Control.Controller.prototype.getLocalCoord = function(e) {

	var canOrigin = {x: this.canNode.getXY()[0],
		y: this.canNode.getXY()[1]};
	return {x: e.pageX - canOrigin.x, y: e.pageY - canOrigin.y};

};

/**
 * Binds listener functions to mouse events.
 *
 * @method bindEventListeners
 * @return {undefined}
 */
MusThGUI.Control.Controller.prototype.bindEventListeners = function() {

	if (!(this.state.editable)) {
		return;
	}

	var parent = this;

	this.canNode.detachAll();

	this.canNode.on('click', function(e) {
		var coord = parent.getLocalCoord(e);
		parent.onMouseClick({x: coord.x, y: coord.y});
	});

	this.canNode.on('mousemove', function(e) {
		var coord = parent.getLocalCoord(e);
		parent.onMouseMove({x: coord.x, y: coord.y});
	});

	this.canNode.on('mouseleave', function() {
		parent.removeGhostNote();
		parent.removeGhostAcc();
		parent.clearToolbarOverlays();
		parent.drawState();
	});

};

/**
 * Handles a canvas mouse click event.
 *
 * @method onMouseClick
 * @param {Object} point An object literal representing the location of the
 * mouse click, with properties x and y.
 * @return {undefined}
 */
MusThGUI.Control.Controller.prototype.onMouseClick = function(point) {

	var elem = this.getElementFromPoint(point);
	var letter, register;

	if (elem.elemType !== null) {
		// Note
		if (elem.elemType === 'note') {
			var staff = this.state.staffSystem.staves[elem.staffID];
			var noteCol = staff.noteColumns[elem.colID];
			letter = elem.noteName.substr(0, 1);
			register = parseInt(elem.noteName.substr(1, 1), 10);
			var acc = (this.state.hasToolbar('acc')) ?
					this.state.getToolbar('acc').selectedSymbol() :
					'n';
			var noteVal = (this.state.hasToolbar('noteVal')) ?
					this.state.getToolbar('noteVal').selectedSymbol() :
					'whole';
			var newNote = new MusThGUI.GUIState.Note(letter, register, acc,
					noteVal, true, false);
			if (noteCol.noteAddible(newNote)) {

				this.removeGhostNote();
				noteCol.addNote(newNote);
				this.callBack(this.state.getState());
			}
			else if (noteCol.noteRemovable(newNote)) {
				noteCol.removeNote(newNote);
				this.callBack(this.state.getState());
			}
		}
		// key signature
		else if (elem.elemType === 'keysign' && this.state.hasToolbar('acc')) {
			var keySign =
					this.state.staffSystem.staves[elem.staffID].keySign;
			var col = (elem.colID > (keySign.totalAccColumns - 1)) ?
					keySign.totalAccColumns : elem.colID;
			letter = elem.noteName.substr(0, 1);
			register = parseInt(elem.noteName.substr(1, 1), 10);
			if (keySign.containsEditableAccidental(letter, register, col)) {
				keySign.removeAccidental(col);
				this.callBack(this.state.getState());
			}
			else {
				if (!(keySign.isFull()) && !(keySign.columnIsFull(col))) {
					if (this.state.hasToolbar('acc')) {
						var tbrSel = this.state.getToolbar('acc').selectedSymbol();
						if (tbrSel === '#' || tbrSel === 'b') {
							var newAcc = new MusThGUI.GUIState.KeySignAcc(
									this.state.getToolbar('acc').selectedSymbol(),
									letter, register, true, false);
							this.removeGhostAcc();
							keySign.addAccidental(newAcc);
							this.callBack(this.state.getState());
						}
					}
				}
			}
		}
		// Toolbar button
		else if (elem.elemType === 'toolbar') {
			this.state.getToolbar(elem.toolbarName).select(elem.buttonID);
		}
		// Text input
		else if (elem.elemType === 'textInput') {
			var parent = this;
			Y.Array.each(this.state.staffSystem.staves, function(staff) {
				var textInput = staff.noteColumns[elem.colID].textInput;
				if (textInput !== null) {
					if (typeof(parent.inputDialog) === 'undefined') {
						parent.inputDialog = new MusThGUI.Control.InputDialog(
								parent, parent.parsedXML);
					}
					parent.inputDialog.colID = elem.colID;
					parent.inputDialog.show();
				}
			});
		}
		this.drawState();
	}

};

/**
 * Handles a canvas mouse move event.
 *
 * @method onMouseMove
 * @param {Object} point An object literal representing the location of the
 * mouseover event, with properties x and y.
 * @return {undefined}
 */
MusThGUI.Control.Controller.prototype.onMouseMove = function(point) {

	this.removeGhostNote();
	this.removeGhostAcc();
	this.clearToolbarOverlays();
	var letter, register;

	var elem = this.getElementFromPoint(point);
	if (elem.elemType !== null) {
		if (elem.elemType === 'note') {
			var staff = this.state.staffSystem.staves[elem.staffID];
			var noteCol = staff.noteColumns[elem.colID];
			letter = elem.noteName.substr(0, 1);
			register = elem.noteName.substr(1, 1);
			var acc = (this.state.hasToolbar('acc')) ?
					this.state.getToolbar('acc').selectedSymbol() :
					'n';
			var noteVal = (this.state.hasToolbar('noteVal')) ?
					this.state.getToolbar('noteVal').selectedSymbol() :
					'whole';
			var newNote = new MusThGUI.GUIState.Note(letter, register, acc,
					noteVal, true, true);
			if (noteCol.noteAddible(newNote)) {
				noteCol.addNote(newNote);
				this.ghostNote = {note: newNote, staffID: elem.staffID,
					colID: elem.colID};
			}
		}
		else if (elem.elemType === 'keysign' && this.state.hasToolbar('acc')) {
			var keySign =
					this.state.staffSystem.staves[elem.staffID].keySign;
			var col = (elem.colID > (keySign.totalAccColumns - 1)) ?
					keySign.totalAccColumns : elem.colID;
			letter = elem.noteName.substr(0, 1);
			register = parseInt(elem.noteName.substr(1, 1), 10);

			if (!(keySign.isFull()) && !(keySign.columnIsFull(col))) {
				if (this.state.hasToolbar('acc')) {
					var tbrSel = this.state.getToolbar('acc').selectedSymbol();
					if (tbrSel === '#' || tbrSel === 'b') {
						var newAcc = new MusThGUI.GUIState.KeySignAcc(
								this.state.getToolbar('acc').selectedSymbol(),
								letter, register, true, true);
						keySign.addAccidental(newAcc);
						this.ghostAcc = {staffID: elem.staffID};
					}
				}
			}
		}
		else if (elem.elemType === 'toolbar') {
			if (elem.buttonID !==
					this.state.getToolbar(elem.toolbarName).selectedID()) {
				this.state.getToolbar(elem.toolbarName).
						setMouseOverlay(elem.buttonID);
			}
		}
	}
	else {
		this.clearToolbarOverlays();
	}
	this.drawState();
};

/**
 * Given a mouse event location, determine if a canvas element (note,
 * key signature accidental, toolbar button, text input) was involved, and if
 * so, return the element.
 *
 * @method getElementFromPoint
 * @param {Object} point An object literal representing the location of the
 * mouse event, with properties x and y.
 * @return {Object} An object literal with a property 'elemType' indicating the
 * type of element that was involved (if null, no element was involved).
 * Additional properties, customized for a given type, provide additional
 * details on the element.
 */
MusThGUI.Control.Controller.prototype.getElementFromPoint = function(point) {

	var staves = [];

	Y.Array.each(this.state.staffSystem.staves, function(staff) {

		staves.push(staff.clef);

	});

	var numCols = this.state.staffSystem.staves[0].noteColumns.length;
	var totalAccColumns =
			this.state.staffSystem.staves[0].keySign.totalAccColumns;
	var elem =
			this.coordMgr.getElementByPos(point, staves, numCols, totalAccColumns,
			this.state.toolbars, this.state.staffSystem.hasTextInput());

	return elem;

};

/**
 * Deletes the current ghost note (if displayed) from the canvas state.
 *
 * @method removeGhostNote
 * @return {undefined}
 */
MusThGUI.Control.Controller.prototype.removeGhostNote = function() {

	if (this.ghostNote !== null && typeof(this.ghostNote !== 'undefined')) {
		var gNoteSt = this.state.staffSystem.staves[this.ghostNote.staffID];
		gNoteSt.noteColumns[this.ghostNote.colID].removeGhostNote();
		this.ghostNote = null;
	}

};

/**
 * Deletes the current ghost accidental (if displayed) from the canvas state.
 *
 * @method removeGhostAcc
 * @return {undefined}
 */
MusThGUI.Control.Controller.prototype.removeGhostAcc = function() {

	if (this.ghostAcc !== null && typeof(this.ghostAcc) !== 'undefined') {
		var gAccSt = this.state.staffSystem.staves[this.ghostAcc.staffID];
		if (gAccSt.keySign.accidentals.length > 0) {
			var i = gAccSt.keySign.accidentals.length - 1;
			if (gAccSt.keySign.accidentals[i].isGhost) {
				gAccSt.keySign.removeAccidental(i);
			}
		}
	}

};

/**
 * Disables the overlay effect for all toolbar buttons for which it is enabled.
 *
 * @method clearToolbarOverlays
 * @return {undefined}
 */
MusThGUI.Control.Controller.prototype.clearToolbarOverlays = function() {

	if (this.state.toolbars.length > 0) {
		var i;
		for (i = 0; i < this.state.toolbars.length; i++) {
			this.state.toolbars[i].setMouseOverlay(null);
		}
	}

};

/**
 * Draws the canvas state.
 *
 * @method drawState
 * @return {undefined}
 */
MusThGUI.Control.Controller.prototype.drawState = function() {

	if (typeof(this.renderer) !== 'undefined') {
		if (this.renderer !== null) {
			this.ctx.fillStyle = 'white';
			this.ctx.fillRect(0, 0, this.coordMgr.canvasWidth,
					this.coordMgr.canvasHeight);
			this.renderer.draw();
		}
	}

};

/**
 * Given a column ID and a text input value, sets the value of the column's text
 * input to the given value.
 *
 * @method setColTextInput
 * @param {Number} colID The zero-based ID of the column.
 * @param {String} value The value to be assigned to the text input.
 * @return {undefined}
 */
MusThGUI.Control.Controller.prototype.setColTextInput = function(colID,
		value) {

	var parent = this;

	Y.Array.each(this.state.staffSystem.staves, function(staff) {

		var textInput = staff.noteColumns[colID].textInput;
		if (textInput !== null) {
			textInput.value = value;
			parent.callBack(parent.state.getState());
			parent.drawState();
		}

	});

};/* Copyright (c) 2013 Eric Brisson

 Permission is hereby granted, free of charge, to any person obtaining a copy
 of this software and associated documentation files (the "Software"), to deal
 in the Software without restriction, including without limitation the rights
 to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 copies of the Software, and to permit persons to whom the Software is
 furnished to do so, subject to the following conditions:

 The above copyright notice and this permission notice shall be included in
 all copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 THE SOFTWARE. */

/**
 * The CoordManager object provides canvas coordinates for rendering
 * purposes. It also takes care of converting mouse event coordinates into
 * canvas elements.
 *
 * @class CoordManager
 * @namespace Render
 *
 * @constructor
 * @param {GUIState.State} state The canvas state.
 * @return {undefined}
 */
MusThGUI.Render.CoordManager = function(state) {

  this.state = state;

  // Hard-coded constants
  this.PADDING_BETWEEN_STAVES = 10;
  this.CANVAS_PADDING_Y = 20;
  this.CANVAS_PADDING_X = 20;
  this.STAFF_END_PADDING = 10;
  this.TEXT_INPUT_PORTION_HEIGHT = 40;
  this.TOOLBARBUTTON_SIZE = 36;
  this.STAFF_LINE_DISTANCE = 10;
  this.CLEF_COL_WIDTH = 40;
  this.KEY_SIGN_ACC_COL_WIDTH = 12;

  // Variables that become constants after being initialized once
  this.max_num_ledger_lines = this.state.staffSystem.maxLedgerLines;
  this.staff_ledger_line_portion_height = (this.max_num_ledger_lines + 1) *
      this.STAFF_LINE_DISTANCE;
  var highestColNoteMax = 0;
  var i;
  for (i = 0; i < this.state.staffSystem.staves[0].noteColumns.length; i++) {
    var noteCol = this.state.staffSystem.staves[0].noteColumns[i];
    highestColNoteMax = Math.max(noteCol.maxNotes, highestColNoteMax);
  }
  this.note_col_width = this.getColWidth(highestColNoteMax);
  this.textInputWidth = this.note_col_width - 10;
  this.textInputHeight = this.TEXT_INPUT_PORTION_HEIGHT / 2;
  this.canvasWidth = this.getCanvasWidth(
      this.state.staffSystem.staves[0].noteColumns.length,
      this.state.staffSystem.staves[0].keySign.totalAccColumns,
      this.state.toolbars.length);
  this.canvasHeight = this.getCanvasHeight(
      this.state.staffSystem.staves.length,
      this.state.getMaxNumToolbarButtons(),
      this.state.staffSystem.hasTextInput());
  this.notes = this.noteArrayBuild();
  this.noteLedgerLines = this.setLedgerLines();
  var trebleTopNoteID = this.notes.F5 + this.max_num_ledger_lines * 2 + 1;
  var bassTopNoteID = this.notes.A3 + this.max_num_ledger_lines * 2 + 1;
  var altoTopNoteID = this.notes.G4 + this.max_num_ledger_lines * 2 + 1;
  var tenorTopNoteID = this.notes.E4 + this.max_num_ledger_lines * 2 + 1;
  this.topNotes = {treble: this.notes[trebleTopNoteID],
    bass: this.notes[bassTopNoteID],
    alto: this.notes[altoTopNoteID],
    tenor: this.notes[tenorTopNoteID]};
};

/**
 * Determines the width of the canvas.
 *
 * @method getCanvasWidth
 * @param {Number} numNoteCols The number of note columns in one of the
 * canvas' staves (with the assumption that each staff will have the same number
 * of note columns).
 * @param {Number} totalAccColumns The number of accidental columns
 * in the key signature portion of one of the canvas' staves (with the
 * assumption that each staff will have the same number of key signature
 * accidental columns).
 * @param {Number} numToolbars The number of canvas toolbars.
 * @return {Number} The width of the canvas.
 */
MusThGUI.Render.CoordManager.prototype.getCanvasWidth = function(numNoteCols,
    totalAccColumns, numToolbars) {

  var staffWidth = this.getStaffWidth(numNoteCols, totalAccColumns);
  staffWidth += (numToolbars * this.TOOLBARBUTTON_SIZE);
  staffWidth += this.CANVAS_PADDING_X * Math.max((numToolbars - 1), 0);
  return staffWidth + this.CANVAS_PADDING_X * 2;

};

/**
 * Determines the height of the canvas.
 *
 * @method getCanvasHeight
 * @param {Number} numStaves The number of staves in the staff system.
 * @param {Number} maxNumToolbarBtns The number of buttons for the longest
 * toolbar in the state.
 * @param {Boolean} hasTextInput Indicating whether at least one column in the
 * staff system has a text input.
 * @return {Number} The height of the canvas.
 */
MusThGUI.Render.CoordManager.prototype.getCanvasHeight = function(numStaves,
    maxNumToolbarBtns, hasTextInput) {

  var heightWithStaves = this.getStaffSystemHeight(numStaves) +
      this.CANVAS_PADDING_Y * 2;
  // in cases when the toolbar is taller than the staff system
  var heightWithToolbar = maxNumToolbarBtns * this.TOOLBARBUTTON_SIZE;
  var height = Math.max(heightWithStaves, heightWithToolbar);
  if (hasTextInput) {
    height += this.TEXT_INPUT_PORTION_HEIGHT;
  }
  return height;

};

/**
 * Determine an appropriate note column width for the staff system, given the
 * maximum number of notes in the columns.
 *
 * @method getColWidth
 * @param {Number} maxNotes The maximum number of notes in the staff system's
 * note columns, with the assumption that all note columns in the staff system
 * will have the same maximum number of notes).
 * @return {Number} The note column width.
 */
MusThGUI.Render.CoordManager.prototype.getColWidth = function(maxNotes) {

  if (typeof this.note_col_width !== 'undefined') {
    if (this.note_col_width !== null) {
      return this.note_col_width;
    }
  }
  switch (maxNotes) {
    case 0:
      return 0;
    case 1:
      return 50;
    case 2:
      return 80;
    case 3:
      return 100;
    case 4:
      return 126;
    default:
      return 150;
  }

};

/**
 * Builds an array listing all white notes on the piano keyboard. All notes are
 * assigned an integer ID. This array can be used to determine the stepwise
 * distance between two notes on the staff.
 *
 * @method noteArrayBuild
 * @return {Object[]} The keyboard note array, which can be referenced by ID
 * (i.e. array.A0 returns 0) or by note name (i.e. array[0] returns 'A0').
 */
MusThGUI.Render.CoordManager.prototype.noteArrayBuild = function() {

  var noteArr = [];

  var letters = [];
  letters[0] = 'C';
  letters[1] = 'D';
  letters[2] = 'E';
  letters[3] = 'F';
  letters[4] = 'G';
  letters[5] = 'A';
  letters[6] = 'B';

  // B0 and A0
  var i = 0;
  noteArr.A0 = i;
  noteArr[i] = 'A0';

  i++;
  noteArr.B0 = i;
  noteArr[i] = 'B0';

  // C1 -> B7
  var reg;
  var ltr;
  for (reg = 1; reg <= 7; reg++) {
    for (ltr = 0; ltr <= 6; ltr++) {
      i++;
      noteArr[letters[ltr] + reg] = i;
      noteArr[i] = letters[ltr] + reg;
    }
  }

  // C8
  i++;
  noteArr.C8 = i;
  noteArr[i] = 'C8';

  return noteArr;

};

/**
 * For each note in the coordinate manager's note array (this.notes), determines
 * the number of ledger lines required to draw the note and whether the ledger
 * lines are to be drawn above or below the note head.
 *
 * @method setLedgerLines
 * @return {Object[]} An array of object literals with two properties:
 * numLines, which provides the number of lines, and loc, which is set to
 * 'above' or 'below'. Each literal is accessed using the clef and the note
 * name, separated by a hyphen (e.g. array.treble-G3 returns {numlines: 2,
 * loc: 'above'}).
 */
MusThGUI.Render.CoordManager.prototype.setLedgerLines = function() {

  var ledgerArr = [];

  var clefs = new Array('treble', 'bass', 'alto', 'tenor');
  var topNoteID = new Array(this.notes.F5, this.notes.A3,
      this.notes.G4, this.notes.E4);
  var bottomNoteID = new Array(this.notes.E4, this.notes.G2,
      this.notes.F3, this.notes.D3);

  var i, j;
  var clef, noteName, stepDist, numLines;
  for (i = 0; i < this.notes.length; i++) {
    noteName = this.notes[i];
    for (j = 0; j < clefs.length; j++) {
      clef = clefs[j];
      if (i < (bottomNoteID[j] - 1)) {
        stepDist = Math.abs(bottomNoteID[j] - i);
        numLines = Math.floor(stepDist / 2);
        ledgerArr[clef + '-' + noteName] =
            {numLines: numLines, loc: 'below'};
      }
      else if (i >= (bottomNoteID[j] - 1) && i <= (topNoteID[j] + 1)) {
        ledgerArr[clef + '-' + noteName] =
            {numLines: 0, loc: null};
      }
      else {
        stepDist = Math.abs(i - topNoteID[j]);
        numLines = Math.floor(stepDist / 2);
        ledgerArr[clef + '-' + noteName] =
            {numLines: numLines, loc: 'above'};
      }
    }
  }

  return ledgerArr;

};

/**
 * Computes the width of a staff.
 *
 * @method getStaffWidth
 * @param {Number} numNoteCols The number of note columns in the staff.
 * @param {Number} totalAccColumns The number of accidental columns
 * in the key signature portion of the staff.
 * @return {Number} The width of the staff.
 */
MusThGUI.Render.CoordManager.prototype.getStaffWidth = function(numNoteCols,
    totalAccColumns) {

  var clefWidth = this.CLEF_COL_WIDTH;
  var keySignWidth = totalAccColumns *
      this.KEY_SIGN_ACC_COL_WIDTH;
  var noteColsWidth = numNoteCols * this.note_col_width;

  return clefWidth + keySignWidth + noteColsWidth + this.STAFF_END_PADDING;

};

/**
 * Computes the height of a staff.
 *
 * @method getStaffHeight
 * @param {Boolean} withLedgerLines Indicates whether the ledger line portion
 * of the staff should be included in the computation.
 * @return {Number} The height of the staff.
 */
MusThGUI.Render.CoordManager.prototype.getStaffHeight =
    function(withLedgerLines) {

      var height;
      if (withLedgerLines) {
        height = this.STAFF_LINE_DISTANCE * 4 +
            ((this.max_num_ledger_lines + 1) * this.STAFF_LINE_DISTANCE) * 2;
      }
      else {
        height = height = this.STAFF_LINE_DISTANCE * 4;
      }
      return height;

    };

/**
 * Computes the height of the staff system.
 *
 * @method getStaffSystemHeight
 * @param {Number} numStaves The number of staves in the staff system.
 * @return {Number} The height of the staff system.
 */
MusThGUI.Render.CoordManager.prototype.getStaffSystemHeight =
    function(numStaves) {

      return this.getStaffHeight(true) * numStaves +
          this.PADDING_BETWEEN_STAVES * (numStaves - 1);

    };

/**
 * Provides the location of the upper left corner of the staff system.
 *
 * @method getStaffSystemOrigin
 * @return {Object} An object literal of the form {x:__, y:__} representing
 * the staff system's origin.
 */
MusThGUI.Render.CoordManager.prototype.getStaffSystemOrigin = function() {

  return {x: this.CANVAS_PADDING_X, y: this.CANVAS_PADDING_Y};

};

/**
 * Provides the location of the upper left corner a staff.
 *
 * @method getStaffOrigin
 * @param {Number} staffID The zero-based ID of the staff.
 * @return {Object} An object literal of the form {x:__, y:__} representing
 * the staff's origin.
 */
MusThGUI.Render.CoordManager.prototype.getStaffOrigin = function(staffID) {

  var y = this.getStaffSystemOrigin().y +
      this.staff_ledger_line_portion_height +
      staffID * (this.getStaffHeight(true) +
      this.PADDING_BETWEEN_STAVES);
  return {x: this.getStaffSystemOrigin().x, y: y};

};

/**
 * Provides the location of the upper left corner a staff line.
 *
 * @method getStaffLinePos
 * @param {Number} staffID The zero-based ID of the staff.
 * @param {Number} lineID The zero-based ID of the staff line.
 * @return {Object} An object literal of the form {x:__, y:__} representing
 * the staff line's origin.
 */
MusThGUI.Render.CoordManager.prototype.getStaffLinePos = function(staffID,
    lineID) {

  var ulCorner = this.getStaffOrigin(staffID);
  var y = ulCorner.y + lineID * this.STAFF_LINE_DISTANCE;
  return {x: ulCorner.x, y: y};

};

/**
 * Provides the location of the clef in a given staff.
 *
 * @method getClefPos
 * @param {Number} staffID The zero-based ID of the staff.
 * @return {Object} An object literal of the form {x:__, y:__} representing
 * the clef's location.
 */
MusThGUI.Render.CoordManager.prototype.getClefPos = function(staffID) {

  var clefColULCorner = this.getStaffOrigin(staffID);
  var colCenterX = clefColULCorner.x + this.CLEF_COL_WIDTH / 2;
  return {x: colCenterX, y: clefColULCorner.y};

};

/**
 * Provides the location of a key signature accidental.
 *
 * @method getKeySignAccPos
 * @param {Number} staffID The zero-based ID of the staff.
 * @param {String} clef The staff's clef.
 * @param {Number} keySignColID The zero-based ID of the key signature column
 * where the accidental lies.
 * @param {String} letter The accidental's letter name.
 * @param {Number} register The accidental's register.
 * @return {Object} An object literal of the form {x:__, y:__} representing
 * the key signature accidental's location.
 */
MusThGUI.Render.CoordManager.prototype.getKeySignAccPos =
    function(staffID, clef, keySignColID, letter, register) {

      var x = this.getKeySignColCenterX(staffID, keySignColID);
      var y = this.getLetterNameY(staffID, clef, letter, register);
      return {x: x, y: y};

    };

/**
 * Provides the x-coordinate of the center of a given key signature accidental
 * column.
 *
 * @method getKeySignColCenterX
 * @param {Number} staffID The zero-based ID of the staff.
 * @param {Number} keySignColID The key signature column.
 * @return {Number}
 */
MusThGUI.Render.CoordManager.prototype.getKeySignColCenterX =
    function(staffID, keySignColID) {

      return this.getStaffOrigin(staffID).x + this.CLEF_COL_WIDTH +
          (keySignColID * this.KEY_SIGN_ACC_COL_WIDTH) +
          (this.KEY_SIGN_ACC_COL_WIDTH / 2);

    };

/**
 * Provides the y-coordinate of a given staff's highest possible note.
 *
 * @method getTopNoteY
 * @param {Number} staffID The zero-based ID of the staff.
 * @return {Number}
 */
MusThGUI.Render.CoordManager.prototype.getTopNoteY = function(staffID) {

  var topY = this.getStaffOrigin(staffID).y
      - this.staff_ledger_line_portion_height +
      this.STAFF_LINE_DISTANCE / 2;
  return topY;

};

/**
 * Provides the y of a given note on a staff.
 *
 * @method getLetterNameY
 * @param {Number} staffID The zero-based ID of the staff.
 * @param {String} clef The staff's clef.
 * @param {String} letter The note's letter.
 * @param {Number} register The note's register.
 * @return {Number}
 */
MusThGUI.Render.CoordManager.prototype.getLetterNameY = function(staffID, clef,
    letter, register) {

  var stepDistFromTopNote = this.getStepwiseNoteDist(this.topNotes[clef],
      letter + register);
  var topY = this.getTopNoteY(staffID);
  var noteY = topY + stepDistFromTopNote * (this.STAFF_LINE_DISTANCE / 2);
  return noteY;

};

/**
 * Provides the stepwise distance between two notes on the staff.
 *
 * @method getStepwiseNoteDist
 * @param {String} note1 The first note's name as letter + register
 * (e.g. 'A4').
 * @param {String} note2 The second note's name as letter + register
 * (e.g. 'A4').
 * @return {Number} The number of steps on the staff between the two notes.
 */
MusThGUI.Render.CoordManager.prototype.getStepwiseNoteDist =
    function(note1, note2) {

      return Math.abs(this.notes[note1] - this.notes[note2]);

    };

/**
 * Provides the x-coordinate of the center of a given note column.
 *
 * @method getNoteColCenterX
 * @param {Number} staffID The zero-based ID of the staff.
 * @param {Number} noteColID The zero-based ID of the note column.
 * @param {Number} totalAccColumns The number of accidental columns
 * in the key signature portion of the staff.
 * @return {Number}
 */
MusThGUI.Render.CoordManager.prototype.getNoteColCenterX =
    function(staffID, noteColID, totalAccColumns) {

      var colWidth = this.note_col_width;

      return this.getStaffOrigin(staffID).x + this.CLEF_COL_WIDTH +
          (totalAccColumns * this.KEY_SIGN_ACC_COL_WIDTH) +
          (noteColID * colWidth) + (colWidth / 2);

    };

/**
 * Provides the location of a given note on a staff.
 *
 * @method getNotePos
 * @param {String} letter The note's letter.
 * @param {Number} register The note's register.
 * @param {Number} staffID The zero-based ID of the staff.
 * @param {String} clef The staff's clef.
 * @param {Number} noteColID The zero-based ID of the note column.
 * @param {Number} totalAccColumns The number of accidental columns
 * in the key signature portion of the staff.
 * @return {Number}
 */
MusThGUI.Render.CoordManager.prototype.getNotePos =
    function(letter, register, staffID, clef, noteColID,
        totalAccColumns) {

      var x = this.getNoteColCenterX(staffID, noteColID,
          totalAccColumns);
      var y = this.getLetterNameY(staffID, clef, letter, register);

      return {x: x, y: y};

    };

/**
 * Provides the y-coordinate of a given note's ledger lines.
 *
 * @method getLedgerLinesY
 * @param {Number} staffID The zero-based ID of the staff.
 * @param {String} clef The staff's clef.
 * @param {String} letter The note's letter.
 * @param {Number} register The note's register.
 * @return {Number[]} An array providing the y-coordinate for each line. If the
 * given note is above the staff, the ledger lines are provided in descending
 * order, otherwise they are provided in ascending order.
 */
MusThGUI.Render.CoordManager.prototype.getLedgerLinesY =
    function(staffID, clef, letter, register) {

      var topLineY = this.getStaffLinePos(staffID, 0).y;
      var bottomLineY = topLineY + this.getStaffHeight(false);

      var numLines = this.noteLedgerLines
          [clef + '-' + letter + register].numLines;
      var loc = this.noteLedgerLines
          [clef + '-' + letter + register].loc;

      var linesY = [];
      if (numLines === 0) {
        return linesY;
      }

      var i;
      var startY = (loc === 'above') ? topLineY -
          (numLines * this.STAFF_LINE_DISTANCE) :
          bottomLineY +
          (numLines * this.STAFF_LINE_DISTANCE);
      if (loc === 'above') {
        for (i = 0; i < numLines; i++) {
          linesY.push(startY + (i * this.STAFF_LINE_DISTANCE));
        }
      }
      else {
        for (i = 0; i < numLines; i++) {
          linesY.push(startY - (i * this.STAFF_LINE_DISTANCE));
        }
      }

      return linesY;

    };

/**
 * Provides the interval size between two notes on the staff.
 *
 * @method getIntervalSize
 * @param {String} note1 The first note's name as letter + register
 * (e.g. 'A4').
 * @param {String} note2 The second note's name as letter + register
 * (e.g. 'A4').
 * @return {Number} The interval size between the two notes.
 */
MusThGUI.Render.CoordManager.prototype.getIntervalSize =
    function(note1, note2) {

      return Math.abs(this.notes[note2] - this.notes[note1]) + 1;

    };

/**
 * Given a canvas point, determine if a canvas element (note, key signature
 * accidental, toolbar button, text input) is located at this point, and if
 * so, return the element.
 *
 * @method getElementByPos
 * @param {Object} point An object literal representing the point, with
 * properties x and y.
 * @param {Number} staves The number of staves in the staff system.
 * @param {Number} numCols The number of note columns in one of the
 * canvas' staves (with the assumption that each staff will have the same number
 * of note columns).
 * @param {Number} totalAccColumns The number of accidental columns
 * in the key signature portion of one of the canvas' staves (with the
 * assumption that each staff will have the same number of key signature
 * accidental columns).
 * @param {GUIState.Toolbar} toolbars An array of the canvas'
 * toolbars.
 * @param {Boolean} hasTextInput Indicates whether at least one column in the
 * staff system has a text input.
 * @return {Object} An object literal with a property 'elemType' indicating the
 * type of element that was involved (if null, no element was involved).
 * Additional properties, customized for a given type, provide additional
 * details on the element.
 */
MusThGUI.Render.CoordManager.prototype.getElementByPos = function(point,
    staves, numCols, totalAccColumns,
    toolbars, hasTextInput) {

  var i, elem;
  for (i = 0; i < staves.length; i++) {
    // Check for note event
    elem = this.pointToStaffNote(point, i, staves[i], numCols,
        totalAccColumns);
    if (elem !== undefined && elem !== null) {
      if (elem.elemType !== null) {
        return elem;
      }
    }
    // Check for key signature accidental event
    elem = this.pointToKeySignAcc(point, i, staves[i],
        totalAccColumns);
    if (elem !== undefined && elem !== null) {
      if (elem.elemType !== null) {
        return elem;
      }
    }
  }

  for (i = 0; i < toolbars.length; i++) {
    elem = this.pointToToolbarBtn(point, i, toolbars[i].name,
        toolbars[i].buttons.length);
    if (elem.elemType !== null) {
      return elem;
    }
  }

  // Check for text input event
  if (hasTextInput) {
    elem = this.pointToTextInput(point, numCols, totalAccColumns);
    if (elem.elemType !== null) {
      return elem;
    }
  }
  return {elemType: null};

};

/**
 * Given a canvas point, determine if a text input element is located at this
 * point, and if so, return the text input.
 *
 * @method pointToTextInput
 * @param {Object} point An object literal representing the point, with
 * properties x and y.
 * @param {Number} numCols The number of note columns in one of the
 * canvas' staves (with the assumption that each staff will have the same number
 * of note columns).
 * @param {Number} totalAccColumns The number of accidental columns
 * in the key signature portion of one of the canvas' staves (with the
 * assumption that each staff will have the same number of key signature
 * accidental columns).
 * @return {Object} If a text input is found, returns an object literal of the
 * form {elemType: 'textInput', colID: __} representing the text input.
 * Otherwise, returns an object literal of the form {elemType: null}.
 */
MusThGUI.Render.CoordManager.prototype.pointToTextInput = function(point,
    numCols, totalAccColumns) {

  var i;
  var leftX, rightX, topY, bottomY;
  for (i = 0; i < numCols; i++) {
    leftX = this.getNoteColCenterX(0, i, totalAccColumns) -
        this.textInputWidth / 2;
    rightX = leftX + this.textInputWidth;
    topY = this.canvasHeight - this.TEXT_INPUT_PORTION_HEIGHT / 2 -
        this.textInputHeight / 2;
    bottomY = topY + this.textInputHeight;
    if (point.x >= leftX && point.x <= rightX &&
        point.y >= topY && point.y <= bottomY) {
      return {elemType: 'textInput', colID: i};
    }
  }
  return {elemType: null};

};

/**
 * Given a canvas point and a staff, determine if a note is located at this
 * point, and if so, return the note.
 *
 * @method pointToStaffNote
 * @param {Object} point An object literal representing the point, with
 * properties x and y.
 * @param {Number} staffID The zero-based ID of the staff.
 * @param {String} clef The staff's clef.
 * @param {Number} numCols The number of note columns in one of the
 * canvas' staves (with the assumption that each staff will have the same number
 * of note columns).
 * @param {Number} totalAccColumns The number of accidental columns
 * in the key signature portion of one of the canvas' staves (with the
 * assumption that each staff will have the same number of key signature
 * accidental columns).
 * @return {Object} If a note is found, an object literal of the form
 * {elemType: 'note', staffID: __, colID: __, noteName: __} representing
 * the note. Otherwise, returns an object literal of the form {elemType: null}.
 */
MusThGUI.Render.CoordManager.prototype.pointToStaffNote = function(point,
    staffID, clef, numCols, totalAccColumns) {

  var staffOrigin = this.getStaffOrigin(staffID);
  var stColsOr = {x: staffOrigin.x +
        this.CLEF_COL_WIDTH +
        this.KEY_SIGN_ACC_COL_WIDTH * totalAccColumns,
    y: staffOrigin.y -
        this.staff_ledger_line_portion_height +
        this.STAFF_LINE_DISTANCE * 0.25};
  // One pixel removed from X to avoid cases when the staff is approached
  // from the right and the coordinates get reduced to an unexisting column
  var stColBR = {x: staffOrigin.x +
        this.CLEF_COL_WIDTH +
        this.KEY_SIGN_ACC_COL_WIDTH * totalAccColumns +
        numCols * this.note_col_width - 1,
    y: staffOrigin.y +
        this.getStaffHeight(false) +
        this.staff_ledger_line_portion_height -
        this.STAFF_LINE_DISTANCE * 0.25};

  if (this.pointWithin(point, stColsOr, stColBR)) {
    var col = Math.floor((Math.abs(point.x - stColsOr.x) /
        this.note_col_width));

    var noteName = this.getNoteFromY(staffID, clef, point.y);

    return {elemType: 'note', staffID: staffID, colID: col, noteName: noteName};

  }
  else {
    return {elemType: null};
  }

};

/**
 * Given a canvas point and a staff, determine if a key signature accidental is
 * located at this point, and if so, return the key signature accidental.
 *
 * @method pointToKeySignAcc
 * @param {Object} point An object literal representing the point, with
 * properties x and y.
 * @param {Number} staffID The zero-based ID of the staff.
 * @param {String} clef The staff's clef.
 * @param {Number} totalAccCols The number of accidental columns
 * in the key signature portion of one of the canvas' staves (with the
 * assumption that each staff will have the same number of key signature
 * accidental columns).
 * @return {Object} If a key signature accidental is found, return an object
 * literal of the form {elemType: 'keySign', staffID: __, colID: __,
 * noteName: __} representing the key signature accidental. Otherwise, returns
 * an object literal of the form {elemType: null}.
 */
MusThGUI.Render.CoordManager.prototype.pointToKeySignAcc = function(point,
    staffID, clef, totalAccCols) {

  var stPos = this.getStaffOrigin(staffID);
  var keySignLl = {x: stPos.x + this.CLEF_COL_WIDTH,
    y: stPos.y - this.STAFF_LINE_DISTANCE * 0.75};
  var keySignBr = {x: stPos.x + this.CLEF_COL_WIDTH +
        totalAccCols * this.KEY_SIGN_ACC_COL_WIDTH - 1,
    y: stPos.y + this.getStaffHeight(false) +
        this.STAFF_LINE_DISTANCE * 0.75};
  if (this.pointWithin(point, keySignLl, keySignBr)) {
    var noteName = this.getNoteFromY(staffID, clef, point.y);
    var col = Math.floor((Math.abs(point.x - keySignLl.x) /
        this.KEY_SIGN_ACC_COL_WIDTH));
    return {elemType: 'keysign', staffID: staffID, colID: col,
      noteName: noteName};
  }
  else {
    return {elemType: null};
  }

};

/**
 * Given a canvas point and a toolbar, determine if a toolbar button is located
 * at this point, and if so, return the toolbar button.
 *
 * @method pointToToolbarBtn
 * @param {Object} point An object literal representing the point, with
 * properties x and y.
 * @param {Number} toolbarID The zero-based ID of the toolbar.
 * @param {String} toolbarName The name of the toolbar.
 * @param {Number} numToolbarBtns The number of buttons in the toolbar.
 * @return {Object} If a toolbar button is found, return an object
 * literal of the form {elemType: 'toolbar', toolbarName: __, buttonID: __}
 * representing the toolbar button. Otherwise, returns an object literal of the
 * form {elemType: null}.
 */
MusThGUI.Render.CoordManager.prototype.pointToToolbarBtn = function(point,
    toolbarID, toolbarName, numToolbarBtns) {

  var toolLX = this.canvasWidth - this.TOOLBARBUTTON_SIZE * (toolbarID + 1) -
      this.CANVAS_PADDING_X * toolbarID;

  var toolLl = {x: toolLX,
    y: this.getToolbarY(numToolbarBtns)};
  var toolBr = {x: toolLX + this.TOOLBARBUTTON_SIZE,
    y: this.getToolbarY(numToolbarBtns) +
        numToolbarBtns * this.TOOLBARBUTTON_SIZE};

  if (this.pointWithin(point, toolLl, toolBr)) {
    var buttonID = Math.floor((point.y -
        this.getToolbarY(numToolbarBtns)) /
        this.TOOLBARBUTTON_SIZE);
    return {elemType: 'toolbar', toolbarName: toolbarName,
      buttonID: buttonID};
  }
  else {
    return {elemType: null};
  }

};

/**
 * Given a y coordinate and a staff, returns the note name associated with the
 * y coordinate.
 *
 * @method getNoteFromY
 * @param {Number} staffID The zero-based ID of the staff.
 * @param {String} clef The staff's clef.
 * @param {Number} noteY The y coordinate.
 * @return {String} The note name as letter + register (e.g. 'A4').
 */
MusThGUI.Render.CoordManager.prototype.getNoteFromY = function(staffID, clef,
    noteY) {

  var topContainerY = this.getTopNoteY(staffID)
      - (this.STAFF_LINE_DISTANCE / 2) / 2;
  var pixDist = Math.abs(topContainerY - noteY);
  var stepDist = Math.floor(pixDist / (this.STAFF_LINE_DISTANCE / 2));


  var noteName = this.notes[this.notes[this.topNotes[clef]] - stepDist];

  return noteName;

};

/**
 * Given a point and a rectangle's upper left and lower right corners, determine
 * if the point lies within the rectangle.
 *
 * @method pointWithin
 * @param {Object} point An object literal representing the point, with
 * properties x and y.
 * @param {Object} llPoint An object literal representing the rectangle's upper
 * left corner, with properties x and y.
 * @param {Object} brPoint An object literal representing the rectangle's lower
 * right corner, with properties x and y.
 * @return {Boolean} Returns true if the point lies within the rectangle.
 */
MusThGUI.Render.CoordManager.prototype.pointWithin = function(point, llPoint,
    brPoint) {

  if (point.x >= llPoint.x && point.x <= brPoint.x &&
      point.y >= llPoint.y && point.y <= brPoint.y) {
    return true;
  }
  else {
    return false;
  }

};

/**
 * Povides the size of toolbar buttons. It is assumed that all toolbar buttons
 * are square.
 *
 * @method getToolbarBtnSize
 * @return {Number}
 */
MusThGUI.Render.CoordManager.prototype.getToolbarBtnSize = function() {

  return {width: this.TOOLBARBUTTON_SIZE,
    height: this.TOOLBARBUTTON_SIZE};

};

/**
 * Povides the x-coordinate of the upper left corner of a given toolbar.
 *
 * @method getToolbarX
 * @param {Number} toolbarID The zero-based ID of the toolbar.
 * @return {Number}
 */
MusThGUI.Render.CoordManager.prototype.getToolbarX = function(toolbarID) {

  return this.canvasWidth - (this.TOOLBARBUTTON_SIZE * (toolbarID + 1)) -
      this.CANVAS_PADDING_X * toolbarID;

};

/**
 * Povides the y-coordinate of the upper left corner of a given toolbar.
 *
 * @method getToolbarY
 * @param {Number} numToolbarBtns The number of toolbar buttons in the
 * toolbar.
 * @return {Number}
 */
MusThGUI.Render.CoordManager.prototype.getToolbarY = function(numToolbarBtns) {

  var toolbarHeight = this.TOOLBARBUTTON_SIZE * numToolbarBtns;
  var y = (this.canvasHeight - toolbarHeight) / 2;
  return y;

};/* Copyright (c) 2013 Eric Brisson

 Permission is hereby granted, free of charge, to any person obtaining a copy
 of this software and associated documentation files (the "Software"), to deal
 in the Software without restriction, including without limitation the rights
 to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 copies of the Software, and to permit persons to whom the Software is
 furnished to do so, subject to the following conditions:

 The above copyright notice and this permission notice shall be included in
 all copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 THE SOFTWARE. */

/**
 * The StaffSystem object stores the canvas state's staff system.
 *
 * @class StaffSystem
 * @namespace GUIState
 *
 * @constructor
 * @return {undefined}
 */
MusThGUI.GUIState.StaffSystem = function() {

  this.staves = [];
  this.maxLedgerLines = 3;

};

/**
 * Adds a staff to the canvas state's staff system.
 *
 * @method addStaff
 * @param {GUIState.Staff} staff The staff to be added.
 * @return {undefined}
 */
MusThGUI.GUIState.StaffSystem.prototype.addStaff = function(staff) {

  this.staves.push(staff);

};

/**
 * Indicates whether at least one column in the staff system has a text input.
 *
 * @method hasTextInput
 * @return {undefined}
 */
MusThGUI.GUIState.StaffSystem.prototype.hasTextInput = function() {

  var hasTextInput = false;
  var i, j, noteCol;
  for (i = 0; i < this.staves.length; i++) {
    var staff = this.staves[i];
    for (j = 0; j < staff.noteColumns.length; j++) {
      noteCol = staff.noteColumns[j];
      if (noteCol.textInput !== null) {
        if (noteCol.textInput.editable ||
            noteCol.textInput.value !== '') {
          hasTextInput = true;
          break;
        }
      }
    }
  }
  return hasTextInput;

};/* Copyright (c) 2013 Eric Brisson

 Permission is hereby granted, free of charge, to any person obtaining a copy
 of this software and associated documentation files (the "Software"), to deal
 in the Software without restriction, including without limitation the rights
 to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 copies of the Software, and to permit persons to whom the Software is
 furnished to do so, subject to the following conditions:

 The above copyright notice and this permission notice shall be included in
 all copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 THE SOFTWARE. */

/**
 * The Staff object represents a staff.
 *
 * @class Staff
 * @namespace GUIState
 *
 * @constructor
 * @param {String} clef The staff's clef.
 * @param {GUIState.KeySign} keySign The staff's key signature.
 * @return {undefined}
 */
MusThGUI.GUIState.Staff = function(clef, keySign) {

  this.clef = clef;
  this.keySign = keySign;
  this.noteColumns = [];

};

/**
 * Adds a note column to the staff.
 *
 * @method addNoteColumn
 * @param {GUIState.NoteCol} noteCol The note column to be added.
 * @return {undefined}
 */
MusThGUI.GUIState.Staff.prototype.addNoteColumn = function(noteCol) {

  this.noteColumns.push(noteCol);

};/* Copyright (c) 2013 Eric Brisson

 Permission is hereby granted, free of charge, to any person obtaining a copy
 of this software and associated documentation files (the "Software"), to deal
 in the Software without restriction, including without limitation the rights
 to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 copies of the Software, and to permit persons to whom the Software is
 furnished to do so, subject to the following conditions:

 The above copyright notice and this permission notice shall be included in
 all copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 THE SOFTWARE. */

/**
 * The NoteCol object represents a note column on a staff.
 *
 * @class NoteCol
 * @namespace GUIState
 *
 * @constructor
 * @param {Number} maxNotes The maximum number of notes that the column can
 * contain.
 * @param {GUIState.TextInput} textInput The text input associated
 * with the note column (null if no text input is present).
 * @return {undefined}
 */
MusThGUI.GUIState.NoteCol = function(maxNotes, textInput) {

  if (arguments.length === 0) {
    return;
  }
  this.notes = [];
  this.maxNotes = parseInt(maxNotes, 10);
  this.textInput = textInput;

};

/**
 * Adds a note to the note column.
 *
 * @method addNote
 * @param {GUIState.Note} note The note to be added.
 * @return {undefined}
 */
MusThGUI.GUIState.NoteCol.prototype.addNote = function(note) {

  this.notes.push(note);
  // Descending sort (top notes first)
  this.notes.sort(function(note1, note2) {
    var comp = -(MusThGUI.GUIState.Note.compare(note1, note2));
    return comp;
  });

};

/**
 * Removes a note from the note column. No action taken if the given note isn't
 * in the column.
 *
 * @method removeNote
 * @param {GUIState.Note} note The note to be removed.
 * @return {undefined}
 */
MusThGUI.GUIState.NoteCol.prototype.removeNote = function(note) {

  var letter = note.letter;
  var register = note.register;
  var i;
  for (i = 0; i < this.notes.length; i++) {
    if (this.notes[i].letter === letter &&
        this.notes[i].register === register) {
      break;
    }
  }
  this.notes.splice(i, 1);

};

/**
 * Removes the ghost note from the column, if present. It is assumed that there
 * can be at most one ghost note in the column at any given time.
 *
 * @method removeGhostNote
 * @return {undefined}
 */
MusThGUI.GUIState.NoteCol.prototype.removeGhostNote = function() {

  var i;
  for (i = 0; i < this.notes.length; i++) {
    if (this.notes[i].isGhost) {
      break;
    }
  }
  this.notes.splice(i, 1);

};

/**
 * Indicates whether the given note is present in the column (ghost notes
 * ignored).
 *
 * @method inColumn
 * @param {GUIState.Note} note The note to look for.
 * @return {GUIState.Note} Returns the found note. If the note
 * isn't found, returns null.
 */
MusThGUI.GUIState.NoteCol.prototype.inColumn = function(note) {

  var foundNote = null;
  var letter = note.letter;
  var register = note.register;
  var i;
  for (i = 0; i < this.notes.length; i++) {
    if (this.notes[i].letter === letter &&
        this.notes[i].register === register &&
        this.notes[i].isGhost === false) {
      foundNote = this.notes[i];
      break;
    }
  }
  return foundNote;

};

/**
 * Indicates whether the number of non-ghost notes in the column is equal to
 * the maximum number of notes allowable in the column.
 *
 * @method isFull
 * @return {Boolean} Returns true if the note column is full.
 */
MusThGUI.GUIState.NoteCol.prototype.isFull = function() {

  var i = 0;
  var cnt = 0;
  for (i = 0; i < this.notes.length; i++) {
    if (!(this.notes[i].isGhost)) {
      cnt++;
    }
  }
  if (cnt >= this.maxNotes) {
    return true;
  }
  else {
    return false;
  }

};

/**
 * Indicates whether the given note can be added to the column.
 *
 * @method noteAddible
 * @param {GUIState.Note} potentialNote The note to consider adding.
 * @return {Boolean} Returns true if the note can be added to the column.
 */
MusThGUI.GUIState.NoteCol.prototype.noteAddible = function(potentialNote) {
  if (this.inColumn(potentialNote) === null && !(this.isFull())) {

    return true;
  }
  else {
    return false;
  }

};

/**
 * Indicates whether the given note can be removed from the column.
 *
 * @method noteRemovable
 * @param {GUIState.Note} note The note to consider removing.
 * @return {Boolean} Returns true if the note can be removed from the column.
 */
MusThGUI.GUIState.NoteCol.prototype.noteRemovable = function(note) {

  var noteClicked = this.inColumn(note);
  if (noteClicked !== null) {
    if (noteClicked.editable) {
      return true;
    }
    else {
      return false;
    }
  }
  else {
    return false;
  }

};/* Copyright (c) 2013 Eric Brisson

 Permission is hereby granted, free of charge, to any person obtaining a copy
 of this software and associated documentation files (the "Software"), to deal
 in the Software without restriction, including without limitation the rights
 to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 copies of the Software, and to permit persons to whom the Software is
 furnished to do so, subject to the following conditions:

 The above copyright notice and this permission notice shall be included in
 all copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 THE SOFTWARE. */

/**
 * The TwoVoiceNoteCol object represents a note column on a staff,
 * specifically designed to handle a voice-leading context where two voices are
 * shown in the column, with stems in opposite direction.
 *
 * @class TwoVoiceNoteCol
 * @namespace GUIState
 * @extends GUIState.NoteCol
 *
 * @constructor
 * @param {GUIState.FiguredBass} figBass The note column's figured
 * bass indication.
 * @param {GUIState.TextInput} textInput The text input associated
 * with the note column (null if no text input is present).
 * @return {undefined}
 */
MusThGUI.GUIState.TwoVoiceNoteCol = function(figBass, textInput) {

  MusThGUI.GUIState.NoteCol.call(this, 2, textInput);
  this.superclass = MusThGUI.GUIState.NoteCol.prototype;
  this.figBass = figBass;

};

MusThGUI.GUIState.TwoVoiceNoteCol.prototype = new MusThGUI.GUIState.NoteCol();
MusThGUI.GUIState.TwoVoiceNoteCol.prototype.constructor =
    MusThGUI.GUIState.TwoVoiceNoteCol;

/**
 * Adds a note to the note column. Once the note is added, rearranges note stems
 * so that they are in the opposite direction.
 *
 * @method addNote
 * @param {GUIState.Note} note The note to be added.
 * @return {undefined}
 */
MusThGUI.GUIState.TwoVoiceNoteCol.prototype.addNote = function(note) {

  this.superclass.addNote.call(this, note);
  this.rearrangeStems();

};

/**
 * Removes a note from the note column. No action taken if the given note isn't
 * in the column. If two notes are in unison in the column, both notes are
 * removed. Once the note is removed, rearranges note stems so that they are in
 * the opposite direction.
 *
 * @method removeNote
 * @param {GUIState.Note} note The note to be removed.
 * @return {undefined}
 */
MusThGUI.GUIState.TwoVoiceNoteCol.prototype.removeNote = function(note) {

  var letter = note.letter;
  var register = note.register;
  var firstRemoval = null;
  var removalCnt = 0;
  var i;
  for (i = 0; i < this.notes.length; i++) {
    if (this.notes[i].letter === letter &&
        this.notes[i].register === register &&
        this.notes[i].editable) {
      if (firstRemoval === null) {
        firstRemoval = i;
      }
      removalCnt += 1;
    }
  }

  this.notes.splice(firstRemoval, removalCnt);
  this.rearrangeStems();

};

/**
 * Removes the ghost note from the column, if present. It is assumed that there
 * can be at most one ghost note in the column at any given time. Once the ghost
 * note is removed, rearranges note stems so that they are in the opposite
 * direction.
 *
 * @method removeGhostNote
 * @return {undefined}
 */
MusThGUI.GUIState.TwoVoiceNoteCol.prototype.removeGhostNote = function() {

  var i;
  for (i = 0; i < this.notes.length; i++) {
    if (this.notes[i].isGhost) {
      break;
    }
  }
  this.notes.splice(i, 1);
  this.rearrangeStems();

};

/**
 * Rearranges the note stems, as needed, so that they are in opposite direction.
 *
 * @method rearrangeStems
 * @return {undefined}
 */
MusThGUI.GUIState.TwoVoiceNoteCol.prototype.rearrangeStems = function() {

  // It is assumed that this function will only be called on
  // note columns of type 'two-voice' and that the maximum number
  // of notes in the column is 2. It is also assumed that any ghost note
  // will be sorted ahead of another note with same letter and register.


  var n0 = (typeof this.notes[0] !== 'undefined') ? this.notes[0] : null;
  var n1 = (typeof this.notes[1] !== 'undefined') ? this.notes[1] : null;

  if (n0 !== null && n1 !== null) {
    if (n1.isGhost && n0.letter === n1.letter &&
        n0.register === n1.register) {
      var n = n1;
      n1 = n0;
      n0 = n;
    }
  }

  // 2 notes in column
  if (n0 !== null && n1 !== null) {
    if (n0.editable && n1.editable) {
      n0.noteValue = 'quarter_stem_up';
      n1.noteValue = 'quarter_stem_down';
    }
    else if (n0.editable) {
      if (n1.noteValue === 'quarter_stem_up') {
        n0.noteValue = 'quarter_stem_down';
      }
      else {
        n0.noteValue = 'quarter_stem_up';
      }
    }
    else if (n1.editable) {
      if (n0.noteValue === 'quarter_stem_up') {
        n1.noteValue = 'quarter_stem_down';
      }
      else {
        n1.noteValue = 'quarter_stem_up';
      }
    }
  }
  // 1 note in column - audomatic stem up
  else if (n0 !== null) {
    if (n0.editable) {
      n0.noteValue = 'quarter_stem_up';
    }
  }
  else if (n1 !== null) {
    if (n1.editable) {
      n1.noteValue = 'quarter_stem_up';
    }
  }

};

/**
 * Indicates whether the given note can be added to the column.
 *
 * @method noteAddible
 * @param {GUIState.Note} potentialNote The note to consider adding.
 * @return {Boolean} Returns true if the note can be added to the column.
 */
MusThGUI.GUIState.TwoVoiceNoteCol.prototype.noteAddible =
    function(potentialNote) {

      if (this.isFull()) {
        return false;
      }
      else if (this.lengthWithoutGhost() === 0) {
        return true;
      }
      else {
        var firstNonGhost = this.getFirstNonGhost();
        if (!(firstNonGhost.editable)) {
          var comp = MusThGUI.GUIState.Note.compare(potentialNote,
              firstNonGhost);
          if (firstNonGhost.noteValue === 'quarter_stem_up') {
            return (comp <= 0);
          }
          else {
            return (comp >= 0);
          }
        }
        else {
          return true;
        }
      }

    };

/**
 * Indicates whether the given note can be removed from the column.
 *
 * @method noteRemovable
 * @param {GUIState.Note} note The note to consider removing.
 * @return {Boolean} Returns true if the note can be removed from the column.
 */
MusThGUI.GUIState.TwoVoiceNoteCol.prototype.noteRemovable = function(note) {

  var noteClicked = this.inColumn(note);
  if (noteClicked !== null) {
    return true;
  }
  else {
    return false;
  }

};

/**
 * Provides the number of notes currently in the column, excluding the ghost
 * note.
 *
 * @method lengthWithoutGhost
 * @return {Number} Returns the number of non-ghost notes in the column.
 */
MusThGUI.GUIState.TwoVoiceNoteCol.prototype.lengthWithoutGhost = function() {

  var count = 0;
  var i;
  for (i = 0; i < this.notes; i++) {
    if (!(this.notes[i].isGhost)) {
      count += 1;
    }
  }
  return count;

};

/**
 * Provides the first non-ghost note in the column.
 *
 * @method getFirstNonGhost
 * @return {GUIState.Note | null} Returns the first non-ghost note in
 * the column. If the column doesn't have non-ghost notes, returns null.
 */
MusThGUI.GUIState.TwoVoiceNoteCol.prototype.getFirstNonGhost = function() {

  var i;
  for (i = 0; i < this.notes.length; i++) {
    if (!(this.notes[i].isGhost)) {
      return this.notes[i];
    }
  }
  return null;

};/* Copyright (c) 2013 Eric Brisson

 Permission is hereby granted, free of charge, to any person obtaining a copy
 of this software and associated documentation files (the "Software"), to deal
 in the Software without restriction, including without limitation the rights
 to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 copies of the Software, and to permit persons to whom the Software is
 furnished to do so, subject to the following conditions:

 The above copyright notice and this permission notice shall be included in
 all copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 THE SOFTWARE. */

/**
 * The KeySign object represents a staff's key signature.
 *
 * @class KeySign
 * @namespace GUIState
 *
 * @constructor
 * @param {Number} totalAccColumns The total number of accidentals columns
 * available in the key signature. Providing a total number of columns that is
 * higher than the number of accidentals initially added to the key signature
 * allows the user to enter additional accidentals in the key signature.
 * @return {undefined}
 */
MusThGUI.GUIState.KeySign = function(totalAccColumns) {

  this.accidentals = [];
  this.totalAccColumns = totalAccColumns;

};

/**
 * Adds an accidental to the key signature.
 *
 * @method addAccidental
 * @param {GUIState.KeySignAcc} accidental The key signature
 * accidental to be added.
 * @return {undefined}
 */
MusThGUI.GUIState.KeySign.prototype.addAccidental = function(accidental) {

  this.accidentals.push(accidental);

};

/**
 * Removes the accidental from the specified accidental column.
 *
 * @method removeAccidental
 * @param {Number} accColID The zero-based ID of the accidental column from
 * which the accidental will be removed.
 * @return {undefined}
 */
MusThGUI.GUIState.KeySign.prototype.removeAccidental = function(accColID) {

  this.accidentals.splice(accColID, 1);

};

/**
 * Given a letter name, register and accidental column ID, indicates whether an
 * editable accidental is present at that locationin the key signature.
 *
 * @method containsEditableAccidental
 * @param {String} ltr The accidental's letter name.
 * @param {Number} reg The accidental's register.
 * @param {Number} colID The zero-based ID of the accidental column where the
 * editable is to be found.
 * @return {Boolean} Returns true if an editable accidental is found at the
 * specified location.
 */
MusThGUI.GUIState.KeySign.prototype.containsEditableAccidental =
    function(ltr, reg, colID) {

      if (colID < this.accidentals.length) {
        if (!(this.accidentals[colID].isGhost) &&
            this.accidentals[colID].letter === ltr &&
            this.accidentals[colID].register === reg &&
            this.accidentals[colID].editable === true) {
          return true;
        }
      }
      else {
        return false;
      }

    };

/**
 * Indicates whether an accidental has been assigned to all accidental columns
 * in the key signature.
 *
 * @method isFull
 * @return {Boolean} Returns true if all accidental columns in the key
 * signature have an accidental.
 */
MusThGUI.GUIState.KeySign.prototype.isFull = function() {

  var i;
  var cnt = 0;
  for (i = 0; i < this.accidentals.length; i++) {
    if (!(this.accidentals[i].isGhost)) {
      cnt++;
    }
  }
  return (cnt >= this.totalAccColumns);

};

/**
 * Indicates whether an accidental is present in a given accidental column.
 *
 * @method columnIsFull
 * @param {Number} colID The zero-based ID of the accidental column.
 * @return {Boolean} Returns true an accidental is found in the given column.
 */
MusThGUI.GUIState.KeySign.prototype.columnIsFull = function(colID) {

  var ret = false;
  if (colID < this.accidentals.length) {
    if (!(this.accidentals[colID].isGhost)) {
      ret = true;
    }
  }
  return ret;

};/* Copyright (c) 2013 Eric Brisson

 Permission is hereby granted, free of charge, to any person obtaining a copy
 of this software and associated documentation files (the "Software"), to deal
 in the Software without restriction, including without limitation the rights
 to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 copies of the Software, and to permit persons to whom the Software is
 furnished to do so, subject to the following conditions:

 The above copyright notice and this permission notice shall be included in
 all copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 THE SOFTWARE. */

/**
 * The KeySignAcc object represents key signature accidentals.
 *
 * @class KeySignAcc
 * @namespace GUIState
 *
 * @constructor
 * @param {String} type The accidental's type (('n' [natural], '#' [sharp],
 * 'b' [flat], 'x' [double sharp], 'bb' [double flat]).
 * @param {String} letter The accidental's letter name.
 * @param {Number} register The accidental's register.
 * @param {Boolean} editable Indicates whether the accidental can be edited
 * after initialization.
 * @param {Boolean} isGhost Indicates whether the accidental is to be
 * displayed as a ghost symbol.
 * @return {undefined}
 */
MusThGUI.GUIState.KeySignAcc = function(type, letter, register, editable,
    isGhost) {

  this.type = type;
  this.letter = letter;
  this.register = parseInt(register, 10);
  this.editable = editable;
  this.isGhost = isGhost;

};/* Copyright (c) 2013 Eric Brisson

 Permission is hereby granted, free of charge, to any person obtaining a copy
 of this software and associated documentation files (the "Software"), to deal
 in the Software without restriction, including without limitation the rights
 to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 copies of the Software, and to permit persons to whom the Software is
 furnished to do so, subject to the following conditions:

 The above copyright notice and this permission notice shall be included in
 all copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 THE SOFTWARE. */

/**
 * The Note object represents a staff note.
 *
 * @class Note
 * @namespace GUIState
 *
 * @constructor
 * @param {String} letter The note's letter name.
 * @param {Number} register The note's register.
 * @param {String} accidental The note's accidental (('n' [natural],
 * '#' [sharp], 'b' [flat], 'x' [double sharp], 'bb' [double flat]).
 * @param {String} noteValue The note's value ('whole', 'quarter_stem_up',
 * 'quarter_stem_down').
 * @param {Boolean} editable Indicates whether the note can be edited
 * after initialization.
 * @param {Boolean} isGhost Indicates whether the note is to be
 * displayed as a ghost symbol.
 * @return {undefined}
 */

MusThGUI.GUIState.Note = function(letter, register, accidental, noteValue,
    editable, isGhost) {

  this.letter = letter;
  this.register = parseInt(register, 10);
  this.accidental = accidental;
  this.noteValue = noteValue;
  this.editable = editable;
  this.isGhost = isGhost;

};

/**
 * Compares two notes with respect to letter and register (accidentals ignored).
 *
 * @method compare
 * @param {GUIState.Note} note1 The first note to be compared.
 * @param {GUIState.Note} note2 The second note to be compared.
 * @return {Number} Returns 1 is note1 > note 2, 0 if note1 = note 2, and
 * -1 and note1 < note2.
 */
MusThGUI.GUIState.Note.compare = function(note1, note2) {

  var letters = [];
  letters.C = 0;
  letters.D = 1;
  letters.E = 2;
  letters.F = 3;
  letters.G = 4;
  letters.A = 5;
  letters.B = 6;

  if (note1.register > note2.register) {
    return 1;
  }
  else if (note1.register === note2.register) {
    if (letters[note1.letter] > letters[note2.letter]) {
      return 1;
    }
    else if (letters[note1.letter] === letters[note2.letter]) {
      return 0;
    }
    else {
      return -1;
    }
  }
  else {
    return -1;
  }

};/* Copyright (c) 2013 Eric Brisson

 Permission is hereby granted, free of charge, to any person obtaining a copy
 of this software and associated documentation files (the "Software"), to deal
 in the Software without restriction, including without limitation the rights
 to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 copies of the Software, and to permit persons to whom the Software is
 furnished to do so, subject to the following conditions:

 The above copyright notice and this permission notice shall be included in
 all copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 THE SOFTWARE. */

/**
 * The State object stores the canvas context
 * (e.g. number of staves, clefs, key signature, notes, toolbars, input dialog,
 * etc).
 *
 * @class State
 * @namespace GUIState
 *
 * @constructor
 * @return {undefined}
 */
MusThGUI.GUIState.State = function() {

  this.staffSystem = null;
  this.toolbars = [];

};

/**
 * Sets the state after instantiation.
 *
 * @method setState
 * @param {XMLDoc} stateXML The canvas state as xml, used to build the state
 * object.
 * @return {undefined}
 */
MusThGUI.GUIState.State.prototype.setState = function(stateXML) {

  this.stateXML = stateXML;

  // Canvas editable
  var musThGUI = this.stateXML.getElementsByTagName('MusThGUI');
  this.editable = musThGUI[0].getAttribute('canvasEditable') === 'true';
  this.accCarryOver = musThGUI[0].getAttribute('accidentalCarryOver') ===
      'true';

  // Staff System
  this.staffSystem = new MusThGUI.GUIState.StaffSystem();
  var i, j, k, l, editable, acc;
  var sSys = this.stateXML.getElementsByTagName('StaffSystem');
  for (i = 0; i < sSys.length; i++) {

    this.staffSystem.maxLedgerLines =
        parseInt(sSys[i].getAttribute('maxLedgerLines'), 10);

    //Staves
    var staffXML = sSys[i].getElementsByTagName('Staff');
    for (j = 0; j < staffXML.length; j++) {

      // Clef
      var clef = staffXML[j].getAttribute('clef');

      // Key signature
      var keySignXML = staffXML[j].getElementsByTagName('KeySign')[0];
      var totalAccCol = keySignXML.getAttribute('totalAccColumns');
      var keySign = new MusThGUI.GUIState.KeySign(parseInt(totalAccCol, 10));

      var accXML = keySignXML.getElementsByTagName('Accidental');
      for (k = 0; k < accXML.length; k++) {
        editable = accXML[k].getAttribute('editable') === 'true';
        acc = new MusThGUI.GUIState.KeySignAcc(
            accXML[k].getAttribute('type'), accXML[k].getAttribute('letter'),
            parseInt(accXML[k].getAttribute('register'), 10), editable, false);
        keySign.addAccidental(acc);
      }

      var staff = new MusThGUI.GUIState.Staff(clef, keySign);

      // Note columns
      var noteColXML = staffXML[j].getElementsByTagName('NoteColumn');
      for (k = 0; k < noteColXML.length; k++) {

        var maxNotes = parseInt(noteColXML[k].getAttribute('maxNotes'), 10);

        // Text inputs
        var textInput = null;
        var textInputXML = noteColXML[k].getElementsByTagName('TextInput');
        for (l = 0; l < textInputXML.length; l++) {
          editable = textInputXML[l].getAttribute('editable') === 'true';
          textInput = new MusThGUI.GUIState.TextInput(
              textInputXML[l].getAttribute('value'), editable);
        }

        var colType = noteColXML[k].getAttribute('type');
        var noteColumn;

        // Two-voice note columns
        if (colType === 'two-voice') {
          // Figured bass
          var figBass = new MusThGUI.GUIState.FiguredBass();
          var figBassRow;
          var figBassXML = noteColXML[k].getElementsByTagName('Row');
          for (l = 0; l < figBassXML.length; l++) {
            figBassRow = new MusThGUI.GUIState.FiguredBassRow(
                figBassXML[l].getAttribute('text'));
            figBass.addRow(figBassRow);
          }
          noteColumn = new MusThGUI.GUIState.TwoVoiceNoteCol(figBass,
              textInput);
        }
        // Standard note columns
        else {
          noteColumn = new MusThGUI.GUIState.NoteCol(maxNotes,
              textInput);
        }

        // Column notes
        var noteXML = noteColXML[k].getElementsByTagName('Note');
        for (l = 0; l < noteXML.length; l++) {

          editable = noteXML[l].getAttribute('editable') === 'true';
          acc = (noteXML[l].getAttribute('accidental') === '') ? 'n' :
              noteXML[l].getAttribute('accidental');
          var note = new MusThGUI.GUIState.Note(
              noteXML[l].getAttribute('letter'),
              noteXML[l].getAttribute('register'), acc,
              noteXML[l].getAttribute('noteValue'), editable, false);
          noteColumn.addNote(note);

        } //Columns notes

        staff.addNoteColumn(noteColumn);

      } // Note columns

      this.staffSystem.addStaff(staff);

    } // Staves

  } // Staff system


  // Toolbars
  var toolbar;
  // Accidental toolbar
  var toolbarBtn, toolbarBtnXML;
  var accTlbrXML = this.stateXML.getElementsByTagName('AccidentalToolbar')[0];
  if (typeof(accTlbrXML) !== 'undefined' && accTlbrXML !== null) {
    if (this.editable && accTlbrXML.childNodes.length > 0) {
      this.toolbars.push(new MusThGUI.GUIState.Toolbar('acc'));
      toolbar = this.toolbars[this.toolbars.length - 1];
      toolbarBtnXML = accTlbrXML.getElementsByTagName('Button');
      for (i = 0; i < toolbarBtnXML.length; i++) {
        toolbarBtn = new MusThGUI.GUIState.ToolbarBtn(
            toolbarBtnXML[i].getAttribute('symbol'));
        toolbar.addButton(toolbarBtn);
      }
      toolbar.buttons[0].selected = true;
    }
  }
  // Note value toolbar
  var nValTlbrXML = this.stateXML.getElementsByTagName('NoteValueToolbar')[0];
  if (typeof(nValTlbrXML) !== 'undefined' && nValTlbrXML !== null) {
    if (this.editable && nValTlbrXML.childNodes.length > 0) {
      this.toolbars.push(new MusThGUI.GUIState.Toolbar('noteVal'));
      toolbar = this.toolbars[this.toolbars.length - 1];
      toolbarBtnXML = nValTlbrXML.getElementsByTagName('Button');
      for (i = 0; i < toolbarBtnXML.length; i++) {
        toolbarBtn = new MusThGUI.GUIState.ToolbarBtn(
            toolbarBtnXML[i].getAttribute('symbol'));
        toolbar.addButton(toolbarBtn);
      }
      toolbar.buttons[0].selected = true;
    }
  }

};

/**
 * Returns the canvas state.
 *
 * @method getState
 * @return {String} The canvas state as xml.
 */
MusThGUI.GUIState.State.prototype.getState = function() {

  var outXML = '<MusThGUI canvasEditable="' + this.editable +
      '" accidentalCarryOver="' + this.accCarryOver + '">\n';
  outXML += '    <StaffSystem maxLedgerLines="' +
      this.staffSystem.maxLedgerLines + '">\n';

  // Staves
  Y.Array.each(this.staffSystem.staves, function(staff) {

    // Clef
    outXML += '        <Staff clef="' + staff.clef + '">\n';

    // Key signature
    outXML += '            <KeySign totalAccColumns="'
        + staff.keySign.totalAccColumns + '">\n';

    Y.Array.each(staff.keySign.accidentals, function(acc) {

      if (!(acc.isGhost)) {
        outXML += '                <Accidental type="' + acc.type +
            '" letter="' + acc.letter +
            '" register="' + acc.register +
            '" editable="' + acc.editable +
            '" />\n';
      }

    });

    outXML += '            </KeySign>\n';

    // Note columns
    outXML += '            <NoteColumns>\n';

    Y.Array.each(staff.noteColumns, function(noteCol) {

      if (noteCol instanceof MusThGUI.GUIState.TwoVoiceNoteCol) {
        outXML += '                <NoteColumn type="two-voice" maxNotes="'
            + noteCol.maxNotes + '" >\n';
      }
      else {
        outXML += '                <NoteColumn maxNotes="'
            + noteCol.maxNotes + '" >\n';
      }

      // Column notes
      Y.Array.each(noteCol.notes, function(note) {

        if (!(note.isGhost)) {
          outXML += '                    <Note letter="' + note.letter +
              '" register="' + note.register +
              '" accidental="' + note.accidental +
              '" noteValue="' + note.noteValue +
              '" editable="' + note.editable +
              '" />\n';
        }

      });

      // Figured bass
      if (noteCol instanceof MusThGUI.GUIState.TwoVoiceNoteCol) {
        if (noteCol.figBass.rows.length > 0) {
          outXML += '                    <FiguredBass>\n';
          Y.Array.each(noteCol.figBass.rows, function(row) {
            outXML += '                        <Row text="'
                + row.rowText + '" />\n';
          });
          outXML += '                    </FiguredBass>\n';
        }
      }

      // Text input
      if (noteCol.textInput !== null) {
        outXML += '                    <TextInput value="' +
            noteCol.textInput.value +
            '" editable="' + noteCol.textInput.editable + '" />\n';
      }
      outXML += '                </NoteColumn>\n';

    }); // Note Columns

    outXML += '            </NoteColumns>\n';
    outXML += '        </Staff>\n';

  }); // Staves

  outXML += '    </StaffSystem>\n';

  // Toolbars (identical to xml input)
  var toolbarXML;
  var accTlbrXML = this.stateXML.getElementsByTagName('AccidentalToolbar')[0];
  if (typeof(accTlbrXML) !== 'undefined' && accTlbrXML !== null) {
    toolbarXML = Y.XML.format(accTlbrXML);
    outXML += toolbarXML + '\n';
  }

  // Toolbars (identical to xml input)
  var nValTlbrXML = this.stateXML.getElementsByTagName('NoteValueToolbar')[0];
  if (typeof(nValTlbrXML) !== 'undefined' && nValTlbrXML !== null) {
    toolbarXML = Y.XML.format(nValTlbrXML);
    outXML += toolbarXML + '\n';
  }

  // Input Dialog (identical to xml input)
  var inputDialXML = this.stateXML.getElementsByTagName('InputDialog')[0];
  if (typeof(inputDialXML) !== 'undefined' && inputDialXML !== null) {
    var dialogXML = Y.XML.format(inputDialXML);
    outXML += dialogXML + '\n';
  }

  outXML += '</MusThGUI>';

  return outXML;

};

/**
 * Indicates whether the state contains a toolbar, given the toolbar name.
 *
 * @method hasToolbar
 * @param {String} name The toolbar name.
 * @return {Boolean)
 */
MusThGUI.GUIState.State.prototype.hasToolbar = function(name) {

  var i;
  for (i = 0; i < this.toolbars.length; i++) {
    if (this.toolbars[i].name === name) {
      return true;
    }
  }
  return false;

};

/**
 * Returns a toolbar, given the toolbar name.
 *
 * @method getToolbar
 * @param {String} name The toolbar name.
 * @return {GUIState.Toolbar | null} Returns null if the toolbar
 * isn't found.
 */
MusThGUI.GUIState.State.prototype.getToolbar = function(name) {

  var i;
  for (i = 0; i < this.toolbars.length; i++) {
    if (this.toolbars[i].name === name) {
      return this.toolbars[i];
    }
  }
  return null;

};

/**
 * Returns the number of buttons for the longest toolbar in the state.
 *
 * @method getMaxNumToolbarButtons
 * @return {Number} Returns 0 if the state doesn't have any toolbars.
 */
MusThGUI.GUIState.State.prototype.getMaxNumToolbarButtons = function() {

  var numButtons = 0;
  Y.Array.each(this.toolbars, function(toolbar) {
    numButtons = Math.max(numButtons, toolbar.buttons.length);
  });
  return numButtons;

};/* Copyright (c) 2013 Eric Brisson

 Permission is hereby granted, free of charge, to any person obtaining a copy
 of this software and associated documentation files (the "Software"), to deal
 in the Software without restriction, including without limitation the rights
 to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 copies of the Software, and to permit persons to whom the Software is
 furnished to do so, subject to the following conditions:

 The above copyright notice and this permission notice shall be included in
 all copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 THE SOFTWARE. */

/**
 * The StaffSystemRend object takes care of drawing a staff system.
 *
 * @class StaffSystemRend
 * @namespace Render
 *
 * @constructor
 * @param {Canvas} can An html5 canvas element.
 * @param {Render.CoordManager} coordMgr The library's coordinate
 * manager.
 * @param {Render.GlyphProvider} glyphProvider The library's glyph
 * provider.
 * @param {GUIState.StaffSystem} staffSystem The staff system to be
 * rendered.
 * @param {Boolean} accCarryOver Indicates whether accidentals are to carry
 * over
 * @return {undefined}
 */
MusThGUI.Render.StaffSystemRend = function(can, coordMgr, glyphProvider,
    staffSystem, accCarryOver) {

  this.can = can;
  this.staffSystem = staffSystem;
  this.coordMgr = coordMgr;
  this.glyphProvider = glyphProvider;
  this.accCarryOver = accCarryOver;

};

/**
 * Draws the staff system.
 *
 * @method draw
 * @return {undefined}
 */
MusThGUI.Render.StaffSystemRend.prototype.draw = function() {

  var parent = this;

  var i;
  for (i = 0; i < this.staffSystem.staves.length; i++) {
    var staff = this.staffSystem.staves[i];
    var staffRend = new MusThGUI.Render.StaffRend(parent.can, parent.coordMgr,
        parent.glyphProvider, staff, i, parent.accCarryOver);
    staffRend.draw();
  }

  var sysWidth = this.coordMgr.getStaffWidth(
      this.staffSystem.staves[0].noteColumns.length,
      this.staffSystem.staves[0].keySign.totalAccColumns);

  // draw left vertical height
  var x = this.coordMgr.getStaffOrigin(0).x;
  var startY = this.coordMgr.getStaffOrigin(0).y;
  var endY = this.coordMgr.getStaffOrigin(
      this.staffSystem.staves.length - 1).y +
      this.coordMgr.getStaffHeight(false);
  var ctx = this.can.getContext('2d');
  ctx.beginPath();
  ctx.moveTo(x, startY);
  ctx.lineTo(x, endY);
  ctx.stroke();

  // draw right vertical height
  x += sysWidth;
  ctx.beginPath();
  ctx.moveTo(x, startY);
  ctx.lineTo(x, endY);
  ctx.stroke();

};/* Copyright (c) 2013 Eric Brisson

 Permission is hereby granted, free of charge, to any person obtaining a copy
 of this software and associated documentation files (the "Software"), to deal
 in the Software without restriction, including without limitation the rights
 to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 copies of the Software, and to permit persons to whom the Software is
 furnished to do so, subject to the following conditions:

 The above copyright notice and this permission notice shall be included in
 all copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 THE SOFTWARE. */

/**
 * The StaffRend object takes care of drawing a staff.
 *
 * @class StaffRend
 * @namespace Render
 *
 * @constructor
 * @param {Canvas} can An html5 canvas element.
 * @param {Render.CoordManager} coordMgr The library's coordinate
 * manager.
 * @param {Render.GlyphProvider} glyphProvider The library's glyph
 * provider.
 * @param {GUIState.Staff} staff The staff to be rendered.
 * @param {Number} staffID The zero-based ID of the staff to be rendered.
 * @param {Boolean} accCarryOver Indicates whether accidentals are to carry
 * over
 * @return {undefined}
 */
MusThGUI.Render.StaffRend = function(can, coordMgr, glyphProvider, staff,
    staffID, accCarryOver) {

  this.can = can;
  this.staff = staff;
  this.coordMgr = coordMgr;
  this.staffID = staffID;
  this.glyphProvider = glyphProvider;
  this.accCarryOver = accCarryOver;

};

/**
 * Draws the staff.
 *
 * @method draw
 * @return {undefined}
 */
MusThGUI.Render.StaffRend.prototype.draw = function() {

  this.drawLines();
  this.drawClef();
  this.drawKeySign();
  this.drawNoteColumns();

};

/**
 * Draws staff lines.
 *
 * @method drawLines
 * @return {undefined}
 */
MusThGUI.Render.StaffRend.prototype.drawLines = function() {

  var stWidth = this.coordMgr.getStaffWidth(this.staff.noteColumns.length,
      this.staff.keySign.totalAccColumns);
  var i;
  var ctx = this.can.getContext('2d');
  ctx.lineWidth = 1;
  var x;
  var y;
  for (i = 0; i < 5; i++) {
    x = this.coordMgr.getStaffLinePos(this.staffID, i).x;
    y = this.coordMgr.getStaffLinePos(this.staffID, i).y;
    ctx.beginPath();
    ctx.moveTo(x, y);
    ctx.lineTo(x + stWidth, y);
    ctx.stroke();
  }

};

/**
 * Draws the staff's clef.
 *
 * @method drawClef
 * @return {undefined}
 */
MusThGUI.Render.StaffRend.prototype.drawClef = function() {

  var clefPos = this.coordMgr.getClefPos(this.staffID);

  var x;
  var y;
  var ctx = this.can.getContext('2d');
  var img = this.glyphProvider.getClef(this.staff.clef);
  if (this.staff.clef === 'treble') {
    x = parseInt(clefPos.x - (img.width / 2), 10);
    y = clefPos.y - 17;
    ctx.drawImage(img, x, y);
  }
  else if (this.staff.clef === 'bass') {
    x = parseInt(clefPos.x - (img.width / 2), 10);
    y = clefPos.y;
    ctx.drawImage(img, x, y);
  }
  if (this.staff.clef === 'alto') {
    x = parseInt(clefPos.x - (img.width / 2), 10);
    y = clefPos.y + 1;
    ctx.drawImage(img, x, y);
  }
  if (this.staff.clef === 'tenor') {
    x = parseInt(clefPos.x - (img.width / 2), 10);
    y = clefPos.y - 9;
    ctx.drawImage(img, x, y);
  }

};

/**
 * Draws the staff's key signature.
 *
 * @method drawKeySign
 * @return {undefined}
 */
MusThGUI.Render.StaffRend.prototype.drawKeySign = function() {

  var keySign = new MusThGUI.Render.KeySignRend(this.can, this.coordMgr,
      this.glyphProvider, this.staffID, this.staff.clef, this.staff.keySign);
  keySign.draw();

};

/**
 * Draws the staff's note columns.
 *
 * @method drawNoteColumns
 * @return {undefined}
 */
MusThGUI.Render.StaffRend.prototype.drawNoteColumns = function() {

  var parent = this;
  var noteColRend;
  var i;
  for (i = 0; i < this.staff.noteColumns.length; i++) {
    noteColRend = new MusThGUI.Render.NoteColRend(parent.can, parent.coordMgr,
        parent.glyphProvider, parent.staff, parent.staffID, i,
        parent.accCarryOver);
    noteColRend.draw();
  }

};/* Copyright (c) 2013 Eric Brisson

 Permission is hereby granted, free of charge, to any person obtaining a copy
 of this software and associated documentation files (the "Software"), to deal
 in the Software without restriction, including without limitation the rights
 to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 copies of the Software, and to permit persons to whom the Software is
 furnished to do so, subject to the following conditions:

 The above copyright notice and this permission notice shall be included in
 all copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 THE SOFTWARE. */

/**
 * The KeySignRend object takes care of drawing a staff's key
 * signature.
 *
 * @class KeySignRend
 * @namespace Render
 *
 * @constructor
 * @param {Canvas} can An html5 canvas element.
 * @param {Render.CoordManager} coordMgr The library's coordinate
 * manager.
 * @param {Render.GlyphProvider} glyphProvider The library's glyph
 * provider.
 * @param {Number} staffID The zero-based ID of the staff where the key
 * signature is to be rendered.
 * @param {String} clef The staff's clef.
 * @param {GUIState.KeySign} keySign The key signature to render.
 * @return {undefined}
 */
MusThGUI.Render.KeySignRend = function(can, coordMgr, glyphProvider, staffID,
    clef, keySign) {

  this.keySign = keySign;
  this.clef = clef;
  this.staffID = staffID;
  this.coordMgr = coordMgr;
  this.can = can;
  this.glyphProvider = glyphProvider;

};

/**
 * Draws the key signature on the staff.
 *
 * @method draw
 * @return {undefined}
 */
MusThGUI.Render.KeySignRend.prototype.draw = function() {

  var parent = this;
  var ctx = this.can.getContext('2d');
  var accPos = null;
  var img;

  var i;
  for (i = 0; i < this.keySign.accidentals.length; i++) {
    var acc = this.keySign.accidentals[i];
    accPos = parent.coordMgr.getKeySignAccPos(parent.staffID, parent.clef,
        i, acc.letter, acc.register);
    if (acc.isGhost === true) {
      ctx.globalAlpha = 0.5;
    }
    else {
      ctx.globalAlpha = 1;
    }
    img = parent.glyphProvider.getAccidental(acc.type, acc.editable, false);
    if (acc.type === '#') {
      ctx.drawImage(img, accPos.x - (img.width / 2),
          accPos.y - 14);
    }
    else if (acc.type === 'b') {
      ctx.drawImage(img, accPos.x + 2 - (img.width / 2),
          accPos.y - 17.5);
    }
    ctx.globalAlpha = 1;
  }

};/* Copyright (c) 2013 Eric Brisson

 Permission is hereby granted, free of charge, to any person obtaining a copy
 of this software and associated documentation files (the "Software"), to deal
 in the Software without restriction, including without limitation the rights
 to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 copies of the Software, and to permit persons to whom the Software is
 furnished to do so, subject to the following conditions:

 The above copyright notice and this permission notice shall be included in
 all copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 THE SOFTWARE. */

/**
 * The NoteColRend object takes care of drawing the elements of a
 * staff's note column.
 *
 * @class NoteColRend
 * @namespace Render
 *
 * @constructor
 * @param {Canvas} can An html5 canvas element.
 * @param {Render.CoordManager} coordMgr The library's coordinate
 * manager.
 * @param {Render.GlyphProvider} glyphProvider The library's glyph
 * provider.
 * @param {GUIState.Staff} staff The staff containing this note
 * column.
 * @param {Number} staffID The zero-based ID of the staff where containing the
 * note column.
 * @param {Number} noteColID The zero-based ID of this note column.
 * @param {Boolean} accCarryOver Indicates whether accidentals are to carry
 * over
 * @return {undefined}
 */
MusThGUI.Render.NoteColRend = function(can, coordMgr, glyphProvider, staff,
    staffID, noteColID, accCarryOver) {

  this.can = can;
  this.coordMgr = coordMgr;
  this.noteCol = staff.noteColumns[noteColID];
  this.noteColID = noteColID;
  this.staff = staff;
  this.staffID = staffID;
  this.accCarryOver = accCarryOver;
  this.clef = staff.clef;
  this.keySign = staff.keySign;
  this.maxNotes = this.noteCol.maxNotes;
  this.glyphProvider = glyphProvider;

};

/**
 * Draws the note column's elements  on the staff.
 *
 * @method draw
 * @return {undefined}
 */
MusThGUI.Render.NoteColRend.prototype.draw = function() {

  var parent = this;
  var noteRend;
  // It is assumed that notes are ordered from top to bottom

  // Two traversals in different directions are required to compute the
  // note offset and the accidental placement.
  var accResetNote = null;
  var noteName, accPos;
  var noteRends = [];

  var i, note, displayAcc;
  var displayAccPar = false;
  var prevColNote = null;
  var nextColNote = null;
  for (i = 0; i < this.noteCol.notes.length; i++) {
    note = this.noteCol.notes[i];
    if (i < (this.noteCol.notes.length - 1)) {
      nextColNote = this.noteCol.notes[i + 1];
    }
    noteName = note.letter + note.register;
    if (!this.accCarryOver) {
      if (note.accidental !== 'n') {
        displayAcc  = true;
      }
      else {
        displayAcc = parent.displayAccidental(note, prevColNote, nextColNote);
        if (displayAcc) {
          displayAccPar = true;
        }
      }
    }
    else {
      displayAcc = parent.displayAccidental(note, prevColNote, nextColNote);
    }
    if (displayAcc) {
      if (accResetNote === null) {
        accPos = 0;
        accResetNote = noteName;
      }
      else if (parent.coordMgr.getIntervalSize(accResetNote, noteName) <= 6) {
        accPos++;
      }
      else {
        accPos = 0;
        accResetNote = noteName;
      }
    }
    noteRend = new MusThGUI.Render.NoteRend(parent.can, parent.coordMgr,
        parent.glyphProvider, note, parent.noteColID, parent.staffID,
        parent.clef, parent.keySign.totalAccColumns, displayAcc, accPos,
        displayAccPar);
    noteRends.push(noteRend);
    prevColNote = note;
  }

  var previousNote, noteOffset;
  for (i = noteRends.length - 1; i >= 0; i--) {
    note = noteRends[i];
    noteName = noteRends[i].note.letter + noteRends[i].note.register;
    if (i === noteRends.length - 1) {
      previousNote = noteName;
      noteOffset = false;
      note.draw(noteOffset);
    }
    else {
      if (this.coordMgr.getIntervalSize(noteName, previousNote) === 2) {
        previousNote = noteName;
        noteOffset = !(noteOffset);
        note.draw(noteOffset);
      }
      else {
        previousNote = noteName;
        noteOffset = false;
        note.draw(noteOffset);
      }
    }
  }

  // Draw Text Input
  if (this.noteCol.textInput !== null) {
    var tInputHeight = this.coordMgr.textInputHeight;
    var textCenterX = this.coordMgr.getNoteColCenterX(this.staffID,
        this.noteColID, this.keySign.totalAccColumns);
    var textY = this.can.height - tInputHeight;
    var ctx = this.can.getContext('2d');

    var textInputWidth = this.coordMgr.textInputWidth;

    if (this.noteCol.textInput.editable) {
      ctx.beginPath();
      ctx.rect(textCenterX - textInputWidth / 2, textY - (tInputHeight / 2),
          textInputWidth, (tInputHeight));
      ctx.fillStyle = '#eaeaea';
      ctx.fill();
      ctx.lineWidth = 0.5;
      ctx.strokeStyle = 'black';
      ctx.stroke();
    }

    ctx.font = '11px Verdana';
    ctx.textAlign = 'center';
    ctx.fillStyle = 'black';
    ctx.fillText(this.noteCol.textInput.value, textCenterX, textY + 4);
  }

  // Draw figured bass
  if (this.noteCol instanceof MusThGUI.GUIState.TwoVoiceNoteCol) {
    var figBassRend = new MusThGUI.GUIState.FiguredBassRend(
        this.can, this.coordMgr, this.glyphProvider, this.noteCol.figBass,
        this.staffID, this.noteColID, this.keySign.totalAccColumns);
    figBassRend.draw();
  }

};

/**
 * Indicates whether a given note's accidental should be displayed. Accidentals
 * are assumed to carry over from one note column to the next.
 *
 * @method displayAccidental
 * @param {GUIState.Note} note The note whose accidental display is
 * to be considered.
 * @param {GUIState.Note} prevColNote The previous note in the note
 * column, if present (null is assumed otherwise).
 * @param {GUIState.Note} nextColNote The next note in the note
 * column, if present (null is assumed otherwise).
 * @return {Boolean} Returns true if the note can be removed from the column.
 */
MusThGUI.Render.NoteColRend.prototype.displayAccidental = function(note,
    prevColNote,
    nextColNote) {

  var previousAcc = null;

  // Check if the note is in the key signature, and then check whether a
  // previous note with same letter name/register has been altered in the
  // staff.

  var i, acc;
  for (i = 0; i < this.keySign.accidentals.length; i++) {
    acc = this.keySign.accidentals[i];
    if (acc.letter === note.letter) {
      previousAcc = acc.type;
      break;
    }
  }

  var j, prevNote;
  for (i = 0; i < this.noteColID; i++) {
    for (j = 0; j < this.staff.noteColumns[i].notes.length; j++) {
      prevNote = this.staff.noteColumns[i].notes[j];
      if (prevNote.letter === note.letter &&
          prevNote.register === note.register) {
        previousAcc = prevNote.accidental;
      }
    }
  }

  if (prevColNote !== null) {
    if (note.letter === prevColNote.letter
        && note.register === prevColNote.register &&
        note.accidental !== prevColNote.accidental) {
      return true;
    }
  }
  if (nextColNote !== null) {
    if (note.letter === nextColNote.letter
        && note.register === nextColNote.register &&
        note.accidental !== nextColNote.accidental) {
      return true;
    }
  }
  if (prevColNote !== null) {
    if (note.letter === prevColNote.letter
        && note.register === prevColNote.register &&
        note.accidental === prevColNote.accidental) {
      return false;
    }
  }

  if (previousAcc === null && note.accidental === 'n') {
    return false;
  }

  if (previousAcc === note.accidental) {
    return false;
  }
  else {
    return true;
  }

};/* Copyright (c) 2013 Eric Brisson

 Permission is hereby granted, free of charge, to any person obtaining a copy
 of this software and associated documentation files (the "Software"), to deal
 in the Software without restriction, including without limitation the rights
 to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 copies of the Software, and to permit persons to whom the Software is
 furnished to do so, subject to the following conditions:

 The above copyright notice and this permission notice shall be included in
 all copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 THE SOFTWARE. */

/**
 * The NoteRend object takes care of drawing a note on a staff.
 *
 * @class NoteRend
 * @namespace Render
 *
 * @constructor
 * @param {Canvas} can An html5 canvas element.
 * @param {Render.CoordManager} coordMgr The library's coordinate
 * manager.
 * @param {Render.GlyphProvider} glyphProvider The library's glyph
 * provider.
 * @param {GUIState.Note} note The note to be rendered.
 * @param {Number} noteColID The zero-based ID of this note column containing
 * the note.
 * @param {Number} staffID The zero-based ID of the staff where the note is to
 * be rendered.
 * @param {String} clef The staff's clef.
 * @param {Number} keySignTotalAccColumns The total number of accidentals
 * columns in the key signature.
 * @param {Boolean} displayAcc Indicates whether the note's accidental should
 * be displayed.
 * @param {Number} accPos A number indicating whether and how much an
 * accidental should be offset to the left of the note (e.g. 0 = no offset, 1 =
 * one offset level to the left, 2 = two offset levels to teh left, etc.).
 * @param {Boolean} displayAccPar Indicates whether the note's accidental should
 * be parenthesized.
 * @return {undefined}
 */
MusThGUI.Render.NoteRend = function(can, coordMgr, glyphProvider, note,
    noteColID, staffID, clef, keySignTotalAccColumns, displayAcc, accPos,
    displayAccPar) {

  this.can = can;
  this.coordMgr = coordMgr;
  this.note = note;
  this.noteColID = noteColID;
  this.staffID = staffID;
  this.clef = clef;
  this.keySignTotalAccColumns = keySignTotalAccColumns;
  this.displayAcc = displayAcc;
  this.accPos = accPos;
  this.displayAccPar = displayAccPar;
  this.accColWidth = 15;
  this.glyphProvider = glyphProvider;

};

/**
 * Draws the note on the staff.
 *
 * @method draw
 * @param {Boolean} noteOffset Indicates whether the note should be offset to
 * the right so that its note head doesn't overlap with a note displayed a step
 * below.
 * @return {undefined}
 */
MusThGUI.Render.NoteRend.prototype.draw = function(noteOffset) {

  var notePos = this.coordMgr.getNotePos(this.note.letter, this.note.register,
      this.staffID, this.clef, this.noteColID, this.keySignTotalAccColumns);
  notePos.x -= 8;

  // Draw note head

  var ctx = this.can.getContext('2d');

  // Make ghost note transparent
  if (this.note.isGhost === true) {
    ctx.globalAlpha = 0.5;
  }
  else {
    ctx.globalAlpha = 1;
  }
  var noteImageWidth, x, y, offsetSize;
  var img = this.glyphProvider.getNoteValue(this.note.noteValue,
      this.note.editable, false);
  if (this.note.noteValue === 'whole') {
    noteImageWidth = img.width;
    offsetSize = (noteOffset) ? 15 : 0;
    x = notePos.x + offsetSize + 1;
    y = notePos.y - img.height / 2;
    ctx.drawImage(img, x, y);
  }
  else if (this.note.noteValue === 'quarter_stem_up') {
    noteImageWidth = img.width;
    offsetSize = (noteOffset) ? 11 : 0;
    x = notePos.x + 1 + offsetSize;
    y = notePos.y - img.height / 2 - 13;
    ctx.drawImage(img, x, y);
  }
  else if (this.note.noteValue === 'quarter_stem_down') {
    noteImageWidth = img.width;
    offsetSize = (noteOffset) ? 11 : 0;
    x = notePos.x + 1 + offsetSize;
    y = notePos.y - img.height / 2 + 13;
    ctx.drawImage(img, x, y);
  }

  // Draw ledger lines
  var ledgerLines = this.coordMgr.getLedgerLinesY(this.staffID, this.clef,
      this.note.letter, this.note.register);


  if (this.note.editable) {
    ctx.strokeStyle = '#415ded';
  }
  else {
    ctx.strokeStyle = 'black';
  }
  ctx.lineWidth = 1;
  var i;
  for (i = 0; i < ledgerLines.length; i++) {
    var lLineY = ledgerLines[i];
    if (i === 0 || !(noteOffset)) {
      ctx.beginPath();
      ctx.moveTo(notePos.x - 4, lLineY);
      ctx.lineTo(notePos.x + noteImageWidth + offsetSize + 5, lLineY);
      ctx.stroke();
    }
  }
  ctx.strokeStyle = 'black';

  // Draw accidental
  img = this.glyphProvider.getAccidental(this.note.accidental,
      this.note.editable, false, this.displayAccPar);
  if (this.displayAcc) {
    if (this.note.accidental === '#') {
      ctx.drawImage(img, notePos.x - (img.width) - 6 -
          this.accPos * this.accColWidth, notePos.y - 14);
    }
    else if (this.note.accidental === 'b') {
      ctx.drawImage(img, notePos.x - (img.width) - 7 -
          this.accPos * this.accColWidth, notePos.y - 17);
    }
    else if (this.note.accidental === 'bb') {
      ctx.drawImage(img, notePos.x - (img.width) - 5 -
          this.accPos * this.accColWidth, notePos.y - 17);
    }
    else if (this.note.accidental === 'x') {
      ctx.drawImage(img, notePos.x - (img.width) - 7 -
          this.accPos * this.accColWidth, notePos.y - 5);
    }
    else if (this.note.accidental === 'n') {
      ctx.drawImage(img, notePos.x - (img.width) - 4 -
          this.accPos * this.accColWidth, notePos.y - 15);
    }
  }

  ctx.globalAlpha = 1;

};/* Copyright (c) 2013 Eric Brisson

 Permission is hereby granted, free of charge, to any person obtaining a copy
 of this software and associated documentation files (the "Software"), to deal
 in the Software without restriction, including without limitation the rights
 to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 copies of the Software, and to permit persons to whom the Software is
 furnished to do so, subject to the following conditions:

 The above copyright notice and this permission notice shall be included in
 all copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 THE SOFTWARE. */

/**
 * The Toolbar object represents canvas toolbars.
 *
 * @class Toolbar
 * @namespace GUIState
 *
 * @constructor
 * @param {String} name The name of the toolbar ('acc' for an accidental
 * toolbar, 'noteVal' for a note value  toolbar).
 * @return {undefined}
 */
MusThGUI.GUIState.Toolbar = function(name) {

  this.buttons = [];
  this.name = name;

};

/**
 * Adds a button to the toolbar.
 *
 * @method addButton
 * @param {GUIState.ToolbarBtn} btn The button to be added.
 * @return {undefined}
 */
MusThGUI.GUIState.Toolbar.prototype.addButton = function(btn) {

  this.buttons.push(btn);

};

/**
 * Set a toolbar button as selected. Only one button may be selected at
 * any one time.
 *
 * @method select
 * @param {Number} btnID The zero-based position of the button to be selected.
 * @return {undefined}
 */
MusThGUI.GUIState.Toolbar.prototype.select = function(btnID) {

  var i;
  for (i = 0; i < this.buttons.length; i++) {
    var button = this.buttons[i];
    if (i === btnID) {
      button.selected = true;
    }
    else {
      button.selected = false;
    }
  }

};

/**
 * Enables the mouseOverlay property of a button in the toolbar.
 *
 * @method setMouseOverlay
 * @param {Number} btnID The zero-based position of the button to be displayed
 * with an overlay. If this argument is null, the mouseOverlay property is
 * disabled for all buttons in the toolbar.
 * @return {undefined}
 */
MusThGUI.GUIState.Toolbar.prototype.setMouseOverlay = function(btnID) {

  var i;
  for (i = 0; i < this.buttons.length; i++) {
    var button = this.buttons[i];
    if (btnID !== null) {
      if (i === btnID) {
        button.mouseOverlay = true;
      }
    }
    else {
      button.mouseOverlay = false;
    }
  }

};

/**
 * Returns the symbol of the button currently selected in the toolbar.
 *
 * @method selectedSymbol
 * @return {String}
 */
MusThGUI.GUIState.Toolbar.prototype.selectedSymbol = function() {

  var i;
  for (i = 0; i < this.buttons.length; i++) {
    if (this.buttons[i].selected === true) {
      return this.buttons[i].symbol;
    }
  }

};

/**
 * Returns the zero-based position of the button currently selected in the
 * toolbar.
 *
 * @method selectedID
 * @return {Number}
 */
MusThGUI.GUIState.Toolbar.prototype.selectedID = function() {

  var i;
  for (i = 0; i < this.buttons.length; i++) {
    if (this.buttons[i].selected === true) {
      return i;
    }
  }

};/* Copyright (c) 2013 Eric Brisson

 Permission is hereby granted, free of charge, to any person obtaining a copy
 of this software and associated documentation files (the "Software"), to deal
 in the Software without restriction, including without limitation the rights
 to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 copies of the Software, and to permit persons to whom the Software is
 furnished to do so, subject to the following conditions:

 The above copyright notice and this permission notice shall be included in
 all copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 THE SOFTWARE. */

/**
 * Represents the state of a toolbar button.
 *
 * @class ToolbarBtn
 * @namespace GUIState
 *
 * @constructor
 * @param {String} symbol A string indicating the type of musical symbol to
 * display in a toolbar button ('n' [natural], '#' [sharp], 'b' [flat],
 * 'x' [double sharp], 'bb' [double flat], 'whole' [whole note],
 * 'quarter_stem_up' [quarter note with stem up],
 * 'quarter_stem_down' [quarter note with stem down]).
 * @return {undefined}
 */
MusThGUI.GUIState.ToolbarBtn = function(symbol) {

  this.selected = false;
  this.mouseOverlay = false;
  this.symbol = symbol;

};/* Copyright (c) 2013 Eric Brisson

 Permission is hereby granted, free of charge, to any person obtaining a copy
 of this software and associated documentation files (the "Software"), to deal
 in the Software without restriction, including without limitation the rights
 to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 copies of the Software, and to permit persons to whom the Software is
 furnished to do so, subject to the following conditions:

 The above copyright notice and this permission notice shall be included in
 all copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 THE SOFTWARE. */

/**
 * The ToolbarRend object takes care of drawing a toolbar.
 *
 * @class ToolbarRend
 * @namespace Render
 *
 * @constructor
 * @param {Canvas} can An html5 canvas element.
 * @param {Render.CoordManager} coordMgr The library's coordinate
 * manager.
 * @param {Render.GlyphProvider} glyphProvider The library's glyph
 * provider.
 * @param {GUIState.Toolbar} toolbar The toolbar to be rendered.
 * @return {undefined}
 */
MusThGUI.Render.ToolbarRend = function(can, coordMgr, glyphProvider, toolbar) {

  this.can = can;
  this.toolbar = toolbar;
  this.coordMgr = coordMgr;
  this.glyphProvider = glyphProvider;

};

/**
 * Draws the toolbar.
 *
 * @method draw
 * @param {Number} toolbarID The zero-based ID of the toolbar to be rendered.
 * @return {undefined}
 */
MusThGUI.Render.ToolbarRend.prototype.draw = function(toolbarID) {

  var parent = this;
  var btnSize = this.coordMgr.getToolbarBtnSize();
  var x = this.coordMgr.getToolbarX(toolbarID);
  var y = this.coordMgr.getToolbarY(this.toolbar.buttons.length);

  var btnRend;
  var i;
  for (i = 0; i < this.toolbar.buttons.length; i++) {
    var button = this.toolbar.buttons[i];
    btnRend = new MusThGUI.Render.ToolbarBtnRend(parent.can,
        parent.glyphProvider, button, x, y + i * btnSize.height,
        btnSize.width, btnSize.height);
    btnRend.draw();
  }

};/* Copyright (c) 2013 Eric Brisson

 Permission is hereby granted, free of charge, to any person obtaining a copy
 of this software and associated documentation files (the "Software"), to deal
 in the Software without restriction, including without limitation the rights
 to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 copies of the Software, and to permit persons to whom the Software is
 furnished to do so, subject to the following conditions:

 The above copyright notice and this permission notice shall be included in
 all copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 THE SOFTWARE. */

/**
 * The ToolbarBtnRend object takes care of drawing a toolbar button.
 *
 * @class ToolbarBtnRend
 * @namespace Render
 *
 * @constructor
 * @param {Canvas} can An html5 canvas element.
 * @param {Render.GlyphProvider} glyphProvider The library's glyph
 * provider.
 * @param {GUIState.ToolbarBtn} button The toolbar button to be
 * rendered.
 * @param {Number} x The x-coordinate of the upper-left corner of the button.
 * @param {Number} y The y-coordinate of the upper-left corner of the button.
 * @param {Number} width The button's width.
 * @param {Number} height The button's height.
 * @return {undefined}
 */
MusThGUI.Render.ToolbarBtnRend = function(can, glyphProvider, button, x, y,
    width, height) {

  this.can = can;
  this.button = button;
  this.x = x;
  this.y = y;
  this.width = width;
  this.height = height;
  this.glyphProvider = glyphProvider;

};

/**
 * Draws the toolbar button.
 *
 * @method draw
 * @return {undefined}
 */
MusThGUI.Render.ToolbarBtnRend.prototype.draw = function() {

  var ctx = this.can.getContext('2d');
  ctx.beginPath();
  ctx.rect(this.x, this.y, this.width, this.height);
  if (this.button.selected === true) {
    ctx.fillStyle = '#d2d2d2';
  }
  else if (this.button.mouseOverlay === true) {
    ctx.fillStyle = '#eaeaea';
  }
  else {
    ctx.fillStyle = '#f6f6f6';
  }
  ctx.fill();
  ctx.lineWidth = 0.5;
  ctx.strokeStyle = 'black';
  ctx.stroke();

  var img;
  if (this.button.symbol === 'n') {
    img = this.glyphProvider.getAccidental(this.button.symbol, false);
    ctx.drawImage(img,
        this.x + this.width / 2 - img.width / 2,
        this.y + this.height / 2 - img.height / 2);
  }
  else if (this.button.symbol === '#') {
    img = this.glyphProvider.getAccidental(this.button.symbol, false);
    ctx.drawImage(img, this.x + this.width / 2 - img.width / 2 - 1,
        this.y + this.height / 2 - img.height / 2);
  }
  else if (this.button.symbol === 'b') {
    img = this.glyphProvider.getAccidental(this.button.symbol, false);
    ctx.drawImage(img, this.x + this.width / 2 - img.width / 2,
        this.y + this.height / 2 - img.height / 2);
  }
  else if (this.button.symbol === 'x') {
    img = this.glyphProvider.getAccidental(this.button.symbol, false);
    ctx.drawImage(img, this.x + this.width / 2 - img.width / 2,
        this.y + this.height / 2 - img.height / 2 + 1);
  }
  else if (this.button.symbol === 'bb') {
    img = this.glyphProvider.getAccidental(this.button.symbol, false);
    ctx.drawImage(img, this.x + this.width / 2 - img.width / 2 + 1,
        this.y + this.height / 2 - img.height / 2);
  }
  else if (this.button.symbol === 'whole') {
    img = this.glyphProvider.getNoteValue(this.button.symbol, false, false);
    ctx.drawImage(img, this.x + this.width / 2 - img.width / 2,
        this.y + this.height / 2 - img.height / 2 + 1);
  }
  else if (this.button.symbol === 'quarter_stem_up') {
    img = this.glyphProvider.getNoteValue(this.button.symbol, false, true);
    ctx.drawImage(img, this.x + this.width / 2 - img.width / 2,
        this.y + this.height / 2 - img.height / 2);
  }
  else if (this.button.symbol === 'quarter_stem_down') {
    img = this.glyphProvider.getNoteValue(this.button.symbol, false, true);
    ctx.drawImage(img, this.x + this.width / 2 - img.width / 2,
        this.y + this.height / 2 - img.height / 2 + 1);
  }

};/* Copyright (c) 2013 Eric Brisson

 Permission is hereby granted, free of charge, to any person obtaining a copy
 of this software and associated documentation files (the "Software"), to deal
 in the Software without restriction, including without limitation the rights
 to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 copies of the Software, and to permit persons to whom the Software is
 furnished to do so, subject to the following conditions:

 The above copyright notice and this permission notice shall be included in
 all copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 THE SOFTWARE. */

/**
 * The FiguredBass object represents a note column's figured bass
 * notation.
 *
 * @class FiguredBass
 * @namespace GUIState
 *
 * @constructor
 * @return {undefined}
 */
MusThGUI.GUIState.FiguredBass = function() {

  this.rows = [];

};

/**
 * Add a figured bass row to the  figured bass notation.
 *
 * @method addRow
 * @param {GUIState.FiguredBassRow} row The figured bass row to be
 * added.
 * @return {undefined}
 */
MusThGUI.GUIState.FiguredBass.prototype.addRow = function(row) {

  this.rows.push(row);

};/* Copyright (c) 2013 Eric Brisson

 Permission is hereby granted, free of charge, to any person obtaining a copy
 of this software and associated documentation files (the "Software"), to deal
 in the Software without restriction, including without limitation the rights
 to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 copies of the Software, and to permit persons to whom the Software is
 furnished to do so, subject to the following conditions:

 The above copyright notice and this permission notice shall be included in
 all copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 THE SOFTWARE. */

/**
 * The FiguredBassRow object represents a row in a figured bass
 * notation (e.g. in the figured notation for a second inversion triad (64),
 * both 6 and 4 contitute a row of the figured bass).
 *
 * @class FiguredBassRow
 * @namespace GUIState
 *
 * @constructor
 * @param {String} rowText The text to display in the figured bass row.
 * @return {undefined}
 */
MusThGUI.GUIState.FiguredBassRow = function(rowText) {
  this.cols = [];
  this.rowText = rowText;
  var i;
  for (i = 0; i < rowText.length; i++) {
    this.cols.push(rowText.substr(i, 1));
  }
};/* Copyright (c) 2013 Eric Brisson

 Permission is hereby granted, free of charge, to any person obtaining a copy
 of this software and associated documentation files (the "Software"), to deal
 in the Software without restriction, including without limitation the rights
 to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 copies of the Software, and to permit persons to whom the Software is
 furnished to do so, subject to the following conditions:

 The above copyright notice and this permission notice shall be included in
 all copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 THE SOFTWARE. */

/**
 * The FiguredBassRend object takes care of drawing the figuration
 * notation on the canvas.
 *
 * @class FiguredBassRend
 * @namespace GUIState
 *
 * @constructor
 * @param {Canvas} can An html5 canvas element.
 * @param {Render.CoordManager} coordMgr The library's coordinate
 * manager.
 * @param {Render.GlyphProvider} glyphProvider The library's glyph
 * provider.
 * @param {GUIState.FiguredBass} figBass The figured bass to be
 * rendered.
 * @param {Number} staffID The zero-based ID of the staff.
 * @param {Number} colID The zero-based ID of the note column.
 * @param {Number} totalAccCols The number of accidental columns
 * in the key signature portion of the staff.
 * @return {undefined}
 */
MusThGUI.GUIState.FiguredBassRend = function(can, coordMgr, glyphProvider,
    figBass, staffID, colID, totalAccCols) {

  this.can = can;
  this.coordMgr = coordMgr;
  this.figBass = figBass;
  this.staffID = staffID;
  this.colID = colID;
  this.totalAccCols = totalAccCols;
  this.glyphProvider = glyphProvider;

};

/**
 * Draws the figured bass on the canvas.
 *
 * @method draw
 * @return {undefined}
 */
MusThGUI.GUIState.FiguredBassRend.prototype.draw = function() {

  // Set lower left corner at the first ledger line below the staff
  var lLCorner = this.coordMgr.getNotePos('A', 3, this.staffID, 'treble',
      this.colID, this.totalAccCols);

  var ctx = this.can.getContext('2d');
  ctx.font = 'bold 13px Times New Roman';
  ctx.textAlign = 'left';
  ctx.fillStyle = 'black';

  var i, j, x, y, col, img;
  var forwardStroke = false;
  for (i = 0; i < this.figBass.rows.length; i++) {
    col = 0;
    for (j = 0; j < this.figBass.rows[i].cols.length; j++) {
      var char = this.figBass.rows[i].cols[j];

      if (char === '/') {
        forwardStroke = true;
      }
      else if (char === '#') {
        img = this.glyphProvider.getAccidental(char, false, true);
        ctx.drawImage(img,
            lLCorner.x + 1 + col * 8,
            lLCorner.y + i * 17 - 12);
        col += 1;
      }
      else if (char === 'b') {
        img = this.glyphProvider.getAccidental(char, false, true);
        ctx.drawImage(img,
            lLCorner.x + 1 + col * 8,
            lLCorner.y + i * 17 - 13);
        col += 1;
      }
      else if (char === 'n') {
        img = this.glyphProvider.getAccidental(char, false, true);
        ctx.drawImage(img,
            lLCorner.x + 1 + col * 8,
            lLCorner.y + i * 17 - 12);
        col += 1;
      }
      else {
        x = lLCorner.x + 1 + col * 8;
        y = lLCorner.y + i * 17;
        ctx.fillText(char, x, y);
        col += 1;
        if (forwardStroke) {
          ctx.beginPath();
          ctx.moveTo(x - 2, y);
          ctx.lineTo(x + 10, y - 9);
          ctx.lineWidth = 0.75;
          ctx.stroke();
          forwardStroke = false;
        }
      }
    }
  }

};/* Copyright (c) 2013 Eric Brisson

 Permission is hereby granted, free of charge, to any person obtaining a copy
 of this software and associated documentation files (the "Software"), to deal
 in the Software without restriction, including without limitation the rights
 to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 copies of the Software, and to permit persons to whom the Software is
 furnished to do so, subject to the following conditions:

 The above copyright notice and this permission notice shall be included in
 all copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 THE SOFTWARE. */

/**
 * The TextInput object represents a staff.
 *
 * @class TextInput
 * @namespace GUIState
 *
 * @constructor
 * @param {String} value The text input's value.
 * @param {Boolean} editable Indicates whether the text input's value can be
 * changed after initialization.
 * @return {undefined}
 */
MusThGUI.GUIState.TextInput = function(value, editable) {

  this.value = value;
  this.editable = editable;

};/* Copyright (c) 2013 Eric Brisson

 Permission is hereby granted, free of charge, to any person obtaining a copy
 of this software and associated documentation files (the "Software"), to deal
 in the Software without restriction, including without limitation the rights
 to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 copies of the Software, and to permit persons to whom the Software is
 furnished to do so, subject to the following conditions:

 The above copyright notice and this permission notice shall be included in
 all copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 THE SOFTWARE. */

/**
 * The GlyphProvider object takes care of loading and returning
 * image objects to be drawn on the canvas.
 *
 * @class GlyphProvider
 * @namespace Render
 *
 * @constructor
 * @param {Function} imagesLoadedCallback Function to callback once all images
 * have been loaded.
 * @return {undefined}
 */
MusThGUI.Render.GlyphProvider = function(imagesLoadedCallback) {

  this.imagesLoadedCallback = imagesLoadedCallback;
  this.numImagesLoaded = 0;
  var parent = this;

  this.wholeNote = new Image();
  this.wholeNote.src = 'data:image/png;base64, ' +
      'iVBORw0KGgoAAAANSUhEUgAAABEAAAAKCAQAAAD4dX0pAAAAv0lEQVQY01XNPUpDQRQG0PMe' +
      'koASxUbEJiCCWLgBm5QWLsNScBGCYpc6G5C0QXegnUrAxs7Cn8agjbFTb4p383jeab755sxM' +
      'oZoNew6s2rKg682NkVuveap04VMYaIGedyFMDCqwYiyEP+35Hdsi14cWJ7m505zDGg35yvjw' +
      'j7S9zFFZl7s6DfJjnOm31K/r4wYJ00xXLLvPJyd2si7wJIRHS9W/Z4mmeon6QjjVqXwhrDmy' +
      'r2vdtWebFl069w0ziIFGBFp4rmoAAAAASUVORK5CYII=';
  this.wholeNote.onload = function() {
    parent.imgLoaded();
  };

  this.wholeNoteColor = new Image();
  this.wholeNoteColor.src = 'data:image/png;base64, ' +
      'iVBORw0KGgoAAAANSUhEUgAAABEAAAAKCAYAAABSfLWiAAABHElEQVQoz3XSv0tWURgH8M+9' +
      'RFAgUaciW8IQokFoinCJOyn0BzjI3QSHUAz/B3UI3B07bkIQuZ9AN0FXXUM3b0PooJRvy3nh' +
      'dvD9jt9fz+E5T6WHpu2eYxof8BCTuIMXOMM+vuEgxXA6zFU5XGMbszm8haUUw3XW32MHT3Lu' +
      'HF9TDItQNW33AD/wJhsGuJdiuCpe+QrH/scvjNdY7RXAYVkAKYYTLBT0I3yp8akQ7hqNbZwW' +
      '3Fx9i3GqabuxESV/cFRwf2ts3mJeHlEywEXB7db4jMNCWGna7nWx2CrFcIN3PfoY83WK4Xe+' +
      'jfWe+BgH+WuHix00bbeJiUyt4W2K4bLqTRk0bfcUHzGTD+wZ9vATL3Ef37GRYrgcDvgHQYtR' +
      '/HvclQsAAAAASUVORK5CYII=';
  this.wholeNoteColor.onload = function() {
    parent.imgLoaded();
  };

  this.qNoteStemUp = new Image();
  this.qNoteStemUp.src = 'data:image/png;base64, ' +
      'iVBORw0KGgoAAAANSUhEUgAAAAsAAAAkCAQAAAD1XwhOAAAAgklEQVQoz+3OoQ0CQRAF0McG' +
      'dwJBgkEQFF0gKAB1kgJQNIWCDlCECjgJCoNBEAwGzCIIlyO7JdwfM3n5mQxpBk4ZNRSDbFpu' +
      '+S/deutZmXrZ2P+oby3WcxWhcG/gdxTOCb6ZJxjdgmXmkR1V7kRwTLozGHs0mhcT6GBkoRQc' +
      'bFWe8AGFfUDrkMtAfAAAAABJRU5ErkJggg==';
  this.qNoteStemUp.onload = function() {
    parent.imgLoaded();
  };

  this.qNoteStemUpColor = new Image();
  this.qNoteStemUpColor.src = 'data:image/png;base64, ' +
      'iVBORw0KGgoAAAANSUhEUgAAAAsAAAAkCAYAAABfVsDFAAAAtElEQVQ4y+3UMU7DQBAF0OcV' +
      'qVIgYSkFFOkCp0AyuUJKTE3FIThB7uAKbkC1AlEDR0BIKa2koEFu0tiIwsAeYKd+M9r5I20h' +
      'oaq6neEpSKsJTlMxyDjjjDPGwcjvc4gbnOMLd4hQ/EAl1rgcGbrBSdHDKT5w9Oebe/jyH0QX' +
      'sMQiYb9twHViGA8Bxwmwi015FfCagC+Go9xi9wt6x1lsyufvnKu6nff5rvoBj7jHW2zKz6Fz' +
      'DxqCIyfsYg9VAAAAAElFTkSuQmCC';
  this.qNoteStemUpColor.onload = function() {
    parent.imgLoaded();
  };

  this.qNoteStemUpToolbar = new Image();
  this.qNoteStemUpToolbar.src = 'data:image/png;base64, ' +
      'iVBORw0KGgoAAAANSUhEUgAAAAkAAAAbCAQAAAAEhm0LAAAAcklEQVQoz+3IsRHBcBjG4UeS' +
      '1pnApTGLAVhFZ4uUBjBBdApqhQFUHKXeXbhPQZF/cmcCv6/53oe0zKUjcpHp9acf1C+K7zNW' +
      'Gnq4fWZhZu3q6a4WDCy8ROtMkxmCbQc2nBI4m7Bswd6c3FFjpHGwUtnxBmZsNrKFQDp/AAAA' +
      'AElFTkSuQmCC';
  this.qNoteStemUpToolbar.onload = function() {
    parent.imgLoaded();
  };

  this.qNoteStemDown = new Image();
  this.qNoteStemDown.src = 'data:image/png;base64, ' +
      'iVBORw0KGgoAAAANSUhEUgAAAAsAAAAkCAQAAAD1XwhOAAAAgUlEQVQoz+3OsQkCQRAF0HcX' +
      'CIIaiQ2YWYFgFRZwFRymNmNmFZqYWIJgBWtmYOIlgmewIKe7Hej/2WOYmULM0Fxl5mFv4xJx' +
      'IWjfvZvAskOxR6aahNtSre87V27prJUMnsnxGJqPNVujuD7oqR3srLvHg0HyiVI2f/5dLvJ8' +
      '8kz5Beo8SqCGCGcrAAAAAElFTkSuQmCC';
  this.qNoteStemDown.onload = function() {
    parent.imgLoaded();
  };

  this.qNoteStemDownColor = new Image();
  this.qNoteStemDownColor.src = 'data:image/png;base64, ' +
      'iVBORw0KGgoAAAANSUhEUgAAAAsAAAAkCAYAAABfVsDFAAAAtUlEQVQ4y+3RMWoCURSG0fMG' +
      'm0DEYkqraJcVCEJgliCppwmkyi7cgqXlq9LY2ziNO7DIBrRzAjYKBkKaBCRB89KG+erD5Ycb' +
      'nFSUdRsDlLjFG+aYVjHfhBM4xDO6frbHTfiEI8xcbhmKsu5jhatfsAxPKRDbDA/SGmfoJMCX' +
      'KuaTLPHq3dfmA3ZnUESnivkWWnhFD4+4xxELTKqYH3z72roo6+uULambG9zgBv97HP6CV3hP' +
      'wR/6KieA6EyktQAAAABJRU5ErkJggg==';
  this.qNoteStemDownColor.onload = function() {
    parent.imgLoaded();
  };

  this.qNoteStemDownToolbar = new Image();
  this.qNoteStemDownToolbar.src = 'data:image/png;base64, ' +
      'iVBORw0KGgoAAAANSUhEUgAAAAkAAAAcCAQAAAAZg12zAAAAXklEQVQoz2NggIAchoMM9xiO' +
      'MRRBuOoMvxj+w2E5A4MUEhcCGdahCfxiZPjNwMKADOIZ0NScYWBgYPjO8A/K/cswBaLwLAMD' +
      'Qy3DdIYIhN6zDGiAiYFhVIgGQs/QhQAJby5DVGAeeQAAAABJRU5ErkJggg==';
  this.qNoteStemDownToolbar.onload = function() {
    parent.imgLoaded();
  };

  this.tClef = new Image();
  this.tClef.src = 'data:image/png;base64, ' +
      'iVBORw0KGgoAAAANSUhEUgAAABwAAABKCAYAAACo2jR+AAAFQElEQVRo3r2Zf2iVVRjHP7t3' +
      '6q5m5DRjtcpZK3HSpB9Ds2TpSjPsh5SYKRhIRFBmRf9EVNSKSowKs5KKjFLoB6UWVAtaP9yW' +
      'RjHtd+lgZqZbuWXbnbV7+6PvW4fDOe99773v9sBh5915zvm+z3Oe9/l1kxRHC4AlwDdAH0NM' +
      '1wJpIAt8CUwcSrCLJVHWGE8CpUMBNgY4aoEFY+5QAL7jAcsC7wMj4wRbHAIWjFlxArY5AF4H' +
      'bgfWS9VtcYHNBboNoD6gXoZSIp5ntXai75BEHoAXAuWa/wEsBz4E/hYIwDYgAywtVrpjgDcN' +
      '6R708NVIrc3FAlYALQLryqGFo0Cnz1qjqvQ44BTN7w/hO0P3mTTUXxDgCCCl+VshfPUCKwOq' +
      'izWaBPAb0OFZnwBMl4QJYHQxgIGafs8hXSBVGvjOxRTV2XZJsp8868cCy4BRes4ItGCaAHwE' +
      '7PGsz7G8z7fFfvhdwPcejZTKw9gusGi6TW+fMu41sFrbv14aB+BCYFDqs1/CHD1xOe9TgQPA' +
      'Y3qeJ59qA66MMzz9COwAJslqbbBdwNg4ATcqOnR4gu+iuNOLG0Ii/TN5eq6cdD6w3wPWGnea' +
      'OAP42QP2uRFJYqEaRw4ajB1xG8mkkDv7DBgfJ9hk+UQX2F/AdYUcmghx1s8BZzrWdilMnR6X' +
      'ZElgrUeyQ7LGT4HNiuxF0xIPWCdQJZ61UvdJcZi/C+wIcKVVF2aBqcWAlauwdAHeavFO1f8X' +
      'FAP4kFIDG+xVB2+lHPndhRgIwBSg0ZFL7gYu8lh3PTBNecw5suzgvP5cGdlNDsm69SKuun6j' +
      'VdgEI600/y7FT29F+4Fj82qHVOuAwxFqxKxUPs8FON7BvNt6w1HAhohA9lhlA860GAaBNRbP' +
      '8gLBnEnVamuxHzjXWC8D9nqSpV81vxN4KcT3HjA19qi1aDd4VjoOaJaXadRzpcqz44HL9TIm' +
      'f8ZIvnjEWtxiAb5rrW831i7T/xY7Uv+d1r6dQGUC+NNi3mc9TzPmB5VqBNRkNIpM6gWut86a' +
      'AlQnHHWA/TzOmN9nrQ0APwBne8LYemV5wedXnlCoMWm29fy1Md/uOLhJ9+nqXKzTS/2XTCd0' +
      'we2WIzDpY2OecRy6RVpwBeseq04cm9BnsNUqLE163JhXOQ5tkYXWejxZpzFvT0jH2xTNA9/a' +
      'YDB1AA9rfo9VOQVS7LOMC4dWDtsdkJeNb+YVR9ukVetrHIduUsHq6lwc0r424ARzYbo8QlZJ' +
      '70xH9dSs9c1GKAJYoaz8NGtPnRzJIHCvS/ylRivrbXWgbLoDeM0yklrtOc/ifcEIBmN84eoa' +
      'q8vr69vYGUC3ip3gfi+RU8kAZ+UKyFcZkjZFyBzG6fN5Xvur5JWywPx8MrivpP+9eh4dkqo8' +
      'xb/N9mUC6lDqkReVArfIw6SB91TXz5eR1cqtXQ28IaBeXUWFT31EVFmNEqo6WeyAHPIXMq5f' +
      'FGhvlkuLhYIW2AiNkfqbBE6W6hsZRmpRlMi7eiqU2uXiksMF2Kq/s4YLsFnf74wwsw+jlNrP' +
      'KXn63hz8e+RZagsBnA3cqKbrRDmBrUobwijj8cGhdIGkyTqS5A059vaT588IKbVDwjLphpD9' +
      'aRU7kY1mspV546mgfM2IhLqMkQGrI2jB10xYJM+zKR/AXJbo+3Umpch/BPgknzssU2rnu78+' +
      'RwAuUVTJAlcU4qAXqttkAg3IddU5CtpV4ik4SiQV9YNybD/wAP//LoEysBVG9fyEtV4QVQBP' +
      'G3XjQWP0GG3nOVHdZNQAXC6JGyRFiVLJF/XNRqZ/APf0/Zema8OiAAAAAElFTkSuQmCC';
  this.tClef.onload = function() {
    parent.imgLoaded();
  };

  this.bClef = new Image();
  this.bClef.src = 'data:image/png;base64, ' +
      'iVBORw0KGgoAAAANSUhEUgAAABsAAAAgCAQAAABJYMuwAAAB6ElEQVQ4y5XUT0hUURTH8c+M' +
      'jpn9FQwjLReSCAlFUBlJEEHUIkhwEVKQixZCi9omLYqwXavauIho0yKUKAzaBbUzQaIsWxRk' +
      '/lklRtZU6m3h6+mMM/r8PXicd8/93nvuPeedlEVtVq1FoyYZ7/3yzCdTZhRQKnoHF11R7pEh' +
      '49apc0KbrAG97ppVRL2Cc0qjZRaWOioI5n21txCS0Sc4VsBzWFYQ/HUmHitTCWndggtFojgv' +
      'CIJZB6IYnhrWxjYTJt3ywwdd1udhVYYi8Bs4JAhGaYmGF57nNuaBD2LfdaQMmtRJdw4W3MjD' +
      '2mPPCxXYYidpZctOk6vB2Kq3HdNGSevPm1aR9/0ztrba9N9Me2c8Z9r9ggUBQVjq6PQnjn98' +
      'IStLtCv2TWnKdZ3yxJjP7mhYlrn6GBtRk+8skVEqXSDhp2PspQ1SzupRXho558wVqZP9sTVs' +
      'RrWH2JO2slIOxnYf5iHvEguoxkgU4mQ00qjDqmqNT9ZuDXodQR+X5G9VLd7i8eRQlbEIeiyT' +
      'HLsXQVklyaHWGGpIDjVHXeR7wR5TRPv8jvY6khw6aSLqGM3JoZvRTq/UJr/y/qildiVFKl0y' +
      'JZjWY3fOX72COnwRBLftSJalWlfNyBpyLXndpVxWZ8Bbb9ZS4f8AkCq9FpfsQ28AAAAASUVO' +
      'RK5CYII=';
  this.bClef.onload = function() {
    parent.imgLoaded();
  };

  this.cClef = new Image();
  this.cClef.src = 'data:image/png;base64, ' +
      'iVBORw0KGgoAAAANSUhEUgAAABgAAAAmCAQAAAB0DpOuAAACIUlEQVQ4y5XVS4iOURgH8N/H' +
      'NzOIJCXl0kwuoYhpopmFGBs2g71y2UlZIMYl5ZoGSzYWUjakkA0l1+QuFE3kmiRyK5fBfF6L' +
      '78zL9zlHed7Fc/7/9/2/T+e5nFOQKdtaO5QstyfgPsZrNUJJp9Oe+Vmme4nbZBct9cghx9W5' +
      '4YIxPa+y8LSjZBkYJ5Pp0hS+adXtm8Z0hL2gTkfAZzxQ66YJKcHz4D/lzAGwX01csNptvLUz' +
      'Zy6DJqOLUcFr0/T1w9ecKYUUzS0msvTd9wp8KfiJqbSmbFxKUFCI8ldSgpUaKvCw4J+kBFuN' +
      'qMAjgz8RF4xVZ3YFMxA88CIu2IV2vf9gWsEan2OCejNCQ/QJTI0lWO9YvDUWGACmu6cNE5wz' +
      '2Hzbobpw3zTaEtZzvbPONm+dN0tXvL13exPwkfC+troifwq6c/TesHjpUnXIFPLh/YegYJ8P' +
      'YFC+k5rKSNURrpoTVou0anbYDWe15wmOzvSGnHsU0npLZl5aUO+jTOZ8/t+iNzIrUpt+6nZo' +
      'h57cdzuK3eb9LShveRU2h7Es20mwUb9qwUtw3SenIvwUw1N12OVVJDJtqUNgcxXuDL6hmKx0' +
      '3Kb+76lxPxWhVl8/fMlxS/DX4hGGuOyDJ6bmTDGcf2fjgg6NGGJDzjSDux7HBT2HSv+cWRiG' +
      't6u6l2YmL5SsjOKb7jTZQXcN1aJokk2uWOxhWsAdLUaZoV5Jp6bfl+Iv+S6xhZ7Q1G4AAAAA' +
      'SUVORK5CYII=';
  this.cClef.onload = function() {
    parent.imgLoaded();
  };

  this.sharp = new Image();
  this.sharp.src = 'data:image/png;base64, ' +
      'iVBORw0KGgoAAAANSUhEUgAAAAkAAAAcCAQAAAAZg12zAAAAxElEQVQoz6XQMUpDQRCA4W8T' +
      'BUEwjZWxEAyrRDyFjdhJLmDrKeIFPIu3iFpbCAqmiIKQREHB1xjWYo0vT1IomWlmfgbmnwGW' +
      'waELqIEbu1jVLNGOFZBKVIn/o4mExtJ3+yo6sufBkJZjqZI+foGhn7IwVjizQfLo06noxFXe' +
      'GGyq67nzLsxKhD+rpnz2VHVN1LZlZMy2ruRZMdUJeXgmBirmLzqaPLk1cS46cJkH15Hso+M6' +
      'S4xAfZFHz0Fv+mVzLyLkJV//bUd1gpAvqQAAAABJRU5ErkJggg==';
  this.sharp.onload = function() {
    parent.imgLoaded();
  };

  this.sharpColor = new Image();
  this.sharpColor.src = 'data:image/png;base64, ' +
      'iVBORw0KGgoAAAANSUhEUgAAAAkAAAAcCAYAAACzipU4AAABLElEQVQ4y8XSv0ocURQG8N+u' +
      'I1EirDBFBBUMSv4UsnVsZDofQATB6S208wEUfABbu8B0gZAXME7nA2gngoIowjoKK4iNbJo7' +
      'OCy7SwrF09zvu+fjnnO+ewiRpMVwBS8lafGn5HUvcZKkxbeAP2Kyl+grRiq800vUN95e9Fz2' +
      'lKRFo1YZu4MZfMAWVtFCK0rSYg7zQXvR9eIYPkc4xuiAlm7rPQRPuAvnDppRSFxhApv4iwWs' +
      '51m8DfU8i2t5Fk9hCEd5Fp/iAbV+FtRe3cxO9YO7zVzEDVawgQhFlKTFLNaC9hcaXSsTRzir' +
      'XHzqUfpyUOP3WMaPCNdo4wv2sI9p7OZZ/Lucrpln8feAfwYzx6uT1/Msvq2UGHqf9f0vURvn' +
      'JYkqiTM8BnyAwzLxDz5LRoo+XtMwAAAAAElFTkSuQmCC';
  this.sharpColor.onload = function() {
    parent.imgLoaded();
  };

  this.sharpFigBass = new Image();
  this.sharpFigBass.src = 'data:image/png;base64, ' +
      'iVBORw0KGgoAAAANSUhEUgAAAAcAAAAPCAMAAAAf3yWMAAAA/1BMVEVOTk4AAAAiIiLw8PDP' +
      'z8+Hh4fMzMwjIyNTU1MJCQlhYWGcnJwCAgIUFBQTExMWFhYcHBz4+Pjv7++EhIQkJCT6+vrX' +
      '19f39/d/f3/R0dH8/PzW1tb29vY2NjbNzc2GhoZQUFBGRkagoKCdnZ1UVFSoqKirq6uVlZVK' +
      'Skqvr696enqWlpZkZGSOjo6NjY3+/v5dXV1JSUlISEgqKirm5ubU1NRFRUX09PTd3d1eXl59' +
      'fX0SEhLg4OD19fViYmK5ublEREQyMjKxsbGJiYkeHh6AgIAEBAQ6OjodHR22trb9/f3p6em+' +
      'vr6IiIiKiopBQUFzc3PQ0NBSUlLc3NzGxsappTWHAAAAAXRSTlMAQObYZgAAAAFiS0dEAIgF' +
      'HUgAAAAJcEhZcwAACxMAAAsTAQCanBgAAAAHdElNRQfdChgTJA0sUEKyAAAAg0lEQVQIHQF4' +
      'AIf/AAAHAAAHAAAAAAcAAAcAAAAABwAABwAAAAAHAAAzBwcABwEBAQcHBwAHB0ZBBwAAAAAH' +
      'AAAHAAAAAAcAAAcAAAAABwAABwAAAAAHUDAOAQcABw8BAQcBBwAHB0c6BwAAAAAHAAAHAAAA' +
      'AAcAAAcAAAAABwAABwAAojUC3ILbwfkAAAAASUVORK5CYII=';
  this.sharpFigBass.onload = function() {
    parent.imgLoaded();
  };


  this.flat = new Image();
  this.flat.src = 'data:image/png;base64, ' +
      'iVBORw0KGgoAAAANSUhEUgAAAAgAAAAYCAQAAABt0HSbAAAAkUlEQVQY083OsQpBARhA4e/e' +
      'JK9gttoUs8kjGIwWm8HDeANZlAxKmU3KbLAahYi68RukLk/gbOdMh6WKLy4aeU09xHf44Z/C' +
      'U8dWOOgrcLMVNjIhDAgrZUU9mbDnqgmabsI9lbmAqiIWqUSCkpYUU07qaLgLM8kn7IT5++So' +
      'ZiSMkcDZRBgqfF5D6Obn19p5fQFtRC8aqop0JQAAAABJRU5ErkJggg==';
  this.flat.onload = function() {
    parent.imgLoaded();
  };

  this.flatColor = new Image();
  this.flatColor.src = 'data:image/png;base64, ' +
      'iVBORw0KGgoAAAANSUhEUgAAAAgAAAAYCAYAAADH2bwQAAAA6ElEQVQoz+3RoUpEURDG8d9e' +
      'LrrFoNdkVDAJRq0HBMFgMCl6mtVkUxB8A4tgEYSLCzbBIIJwn0Cw+gSmg0Usuq7Bs7As6z6B' +
      'HwwzMH+Y+WaEmB5DTAv+UIFVzI4DuuiNA8bqH/hVmfN3iGkXJ1jEG05xXmIC17nxjC9M4wxl' +
      'gTYS5rCCgwzBYYkPHDd19QohppcMlJgp8In3gb2W8lh4KNDKIcTUxvqAu9thm8tYy/Udrsoh' +
      'oINJ3Dd1tdk/VA/dEFMH87hp6mojxNTqAwWOsIML7EFTV73+Jaewhf2mri5H/eIJ26Oa8AMr' +
      'MTaLY1mrzwAAAABJRU5ErkJggg==';
  this.flatColor.onload = function() {
    parent.imgLoaded();
  };

  this.flatFigBass = new Image();
  this.flatFigBass.src = 'data:image/png;base64, ' +
      'iVBORw0KGgoAAAANSUhEUgAAAAYAAAAPCAMAAADwHU6yAAAAolBMVEWnp6ckJCQAAAAvLy/q' +
      '6upoaGgnJydwcHAqKiodHR1ERESdnZ22trakpKRMTExPT0/19fXv7+/29vaurq61tbXY2NgM' +
      'DAzMzMxUVFSNjY0iIiKsrKwCAgIBAQEpKSn9/f2lpaUyMjLPz88FBQWWlpbS0tLDw8PQ0NA+' +
      'Pj4mJiYSEhKHh4cXFxeoqKg1NTUTExMREREZGRnf39+cnJwDAwOenp4V5r8VAAAAAXRSTlMA' +
      'QObYZgAAAAFiS0dEAIgFHUgAAAAJcEhZcwAACxMAAAsTAQCanBgAAAAHdElNRQfdChgTKjDq' +
      'uyMtAAAAMUlEQVQI12NgZAADvBQjlAIzmA3BtCaQyaQIVsLECKIYmUAadHTAKkEkAxtEnxyY' +
      'BAAquAFD8JXS1AAAAABJRU5ErkJggg==';
  this.flatFigBass.onload = function() {
    parent.imgLoaded();
  };

  this.natural = new Image();
  this.natural.src = 'data:image/png;base64, ' +
      'iVBORw0KGgoAAAANSUhEUgAAAAgAAAAeCAQAAAC7iZeGAAAAv0lEQVQY05XQMUtCcRQF8N97' +
      '/InEskEIHJpyDQdBEPEjiBAEra429F36IO1tDuVUWyDRUJD5CQJ5kr2Gl+iLQt650znce+65' +
      'lwuXcngw2qSxxCIv/MJ2IeTYnjhggRNNDceiINV1r+ZAORtJVTT/M12uTadePKvqc+fJqZZD' +
      'DKRB5NX1T99O5hEViJ5kW5bgSNtZFqzuSs++kl24lW7WH+evss48KusEE7EbY+/enOsEQ7HE' +
      'J/gimBd9clHhw/wbjBksgn0qOpgAAAAASUVORK5CYII=';
  this.natural.onload = function() {
    parent.imgLoaded();
  };

  this.naturalColor = new Image();
  this.naturalColor.src = 'data:image/png;base64, ' +
      'iVBORw0KGgoAAAANSUhEUgAAAAgAAAAeCAYAAAARgF8NAAAA8UlEQVQoz7XSvUrEUBCG4Sch' +
      'iOJfkcrCSluxsBKxyAWIiAo2abRTxHvxFtILllbprCwFQQsFf7p0wrKL69okcFzcgMoOnOac' +
      '98x8883I8uoky6szIyLGIXbagC56bUBr/B9IRj1keTWDuAF69eUK1rCKJUQJBtjM8uoGC5jH' +
      'dFhigLn6569E9n8S+YJHPCDFdowI99itz15ZpEe4bDJEeCqL9GIo20SoIRqL1d2wi35g8SLW' +
      'sR8atZzl1Tm2MIspTH4DcPrncQ+7+YbbemAbCe7qTFe4xiuecdAAx83ylkX6EXTzCUlZpJ3x' +
      'Lu14gXd0vgC9Azb8J/WQGQAAAABJRU5ErkJggg==';
  this.naturalColor.onload = function() {
    parent.imgLoaded();
  };

  this.naturalPar = new Image();
  this.naturalPar.src = 'data:image/png;base64, ' +
      'iVBORw0KGgoAAAANSUhEUgAAABMAAAAeCAYAAADOziUSAAAABmJLR0QA/wD/AP+gvaeTAAAA' +
      'CXBIWXMAAFxGAABcRgEUlENBAAAAB3RJTUUH3wcaDykVgP9TjAAAAm5JREFUSMel1s2LV2UU' +
      'B/DPXO+MSpo6qTmMyNjAaJhoC3VhLgohwiwK3IW4kVy5dq3gRlf+AZW0cVNgviwUF0qLcKFI' +
      'o0KJvViKRYQvqY05Pxd9L1wuvzvzqzlweZ5zn3PPc873+Z7zXLrLOtzASv9Bipb3I1iG5TNx' +
      'thHv4ykm87TJx1jV5mw2DuN1dHoIZAs+bVvcmUiG8S7uY/MUzl7LprubC4txDucT4fYenMF3' +
      'GMeceppr8RZO4O+acTPdBcFpQfRjWI0PK2f9+CCLlxofzw+Gu3AIn+AMdmT9JGbhbfSXGMBH' +
      'uI3fGo6OJO2Xq1Qiy1HiWvRNGC3xKhbhKv5spDfWgtUk+jCBbwPTSIE3YnA7oE8lT7s4vZr5' +
      'aIn1Uf7AXw3jCfye9H/Iye0MTlUGt+rOXolyv8b4vhgezglfxz0sxLYaCzo1nFeUGIzypBHV' +
      'AxzHxdq7uV1K8FHGl4rGLnXpC22mk38ylgWeRZnl/8lAdThFAK4KfTqZ6NJJ5mW8W6YJvpkS' +
      'KWthd/Aw8zVplBsyPqtBsTTzn8taCS3GCzk1eBEHs/NwiD1Y41dVjiOZ3yhxIcpwHNyrHcY7' +
      '2b1N+hJ1BzcL/IRfMZrdm8Zt0gnOY7iCH4uA+jmWYKhLB66IOY6vw78iqW6IzTe4WabevsK+' +
      '3AFnE+nl4Dme7jAegp4JjSbxXvrf6TpP58fhpbC8TJsZaKQ2FJv90e8kqqKezgN8lr6+IvT4' +
      'JRC0dY+tuQ4PVKdbx+bL3AN7eiDvY+zFqTz/1lPDaG+Pt/gcfJFGoM3Z93mmk9k42uvvwVQk' +
      'rb7pn6mzh+msj7r9OjwHDDOOLnjafMoAAAAASUVORK5CYII=';
    this.naturalPar.onload = function() {
    parent.imgLoaded();
  };

  this.naturalParColor = new Image();
  this.naturalParColor.src = 'data:image/png;base64, ' +
      'iVBORw0KGgoAAAANSUhEUgAAABMAAAAeCAYAAADOziUSAAAABmJLR0QA/wD/AP+gvaeTAAAA' +
      'CXBIWXMAAAsTAAALEwEAmpwYAAAAB3RJTUUH3wcaEBAyPAuPEAAAAB1pVFh0Q29tbWVudAAA' +
      'AAAAQ3JlYXRlZCB3aXRoIEdJTVBkLmUHAAAC1UlEQVRIx5XVS4gdVRAG4O82d5wYdSLpiaL4' +
      'wCxCDCiIoqKodMxGESQBV9Ki4saFiru4EFyIoiBkqQtRaEQxBAwIJtm0QQQRGR/4gIiCSALR' +
      'nFmMMeqM5rpItZ7puddcC5o+dR7/qVP1V9VAT6o6wb14Dbe1TfmVKaXogWibEi7DHNafDaA7' +
      'B8Mxi1diBaP4JgFcghNtU65UddI25RnLOqWq0zM4jF+meNFbeDx7zRmwANqKR/HhOIt7MsIn' +
      '2F3Vadsan+FBXITXMftfSG1TjrAP89he1WlQ1emMBVWdtuC+uPEwHo5zf2V+KnBFBOczHMWP' +
      'eBZ726Y8XlR1GuAObMbbbVMuZ0+5uqpTXdXpCbyBvTiIXQH2PjZgZxfNWdwVAIciUiPMYA/O' +
      'xTnxdXJV25R/VnX6IvSH8PIQ63B3TH7cc8+mCW47Hf8fcAo3VnW6sMAtYd1RLE0RxVyO4KcY' +
      '3z/EjlAWg6x9OYmEr8OKndnaiViHHUNcE8rP6Jw/iP9zEd1FfI6bMv8K8O7MtUOUYzjX0WJf' +
      '25QLGT1ms4u6Szt9rsB1Y542CP8Uppf5YoKfJlWGicmP0wW+PRtYl8jBy0nWLg3xPbZl3Mmt' +
      'WKzqdHE4/eYIwLrens7SY0N8insiEDPZphl8EMw/L0AGvQs3RIbAQoFDoVya3TrK5ubjwGDM' +
      '0zbh/Bi/VwR/lmNhzv+Ty7Exxge6aO6PiesnlPMVLEThzGVzuOCjtikXC/yRPXVXRO4bfBdl' +
      '5xHcHh3rRfwWdBnihjj36j9pE8VxP7bigkiT9WHRclRWVZ224108HxX5ywDf0jbl0jB4dKSq' +
      '0zvYjVvbpjyYJXA/MzrZGD5+smtARcbuPVGGn5qy3z4Wlh3oLC86drdNeRwv4PcpI3kSL7VN' +
      'eWxVd8qsewUPTAE0Cpe82e/qa9r8msV/5++s6nSqqtPT4/YWY5J51bgnCb9mBXHV3r8BTYTp' +
      'IcgiIbUAAAAASUVORK5CYII=';
  this.naturalParColor.onload = function() {
    parent.imgLoaded();
  };

  this.naturalFigBass = new Image();
  this.naturalFigBass.src = 'data:image/png;base64, ' +
      'iVBORw0KGgoAAAANSUhEUgAAAAYAAAAPCAMAAADwHU6yAAAAilBMVEWVlZUSEhIAAACsrKw4' +
      'ODizs7OWlpavr6+tra329vb7+/siIiJHR0cpKSkCAgKXl5fS0tKysrLa2to2NjYuLi74+PgL' +
      'CwtQUFA+Pj7k5OQwMDCRkZEhISFiYmKgoKC8vLze3t6Dg4PJycmjo6MGBgZfX18zMzOUlJQm' +
      'Jia9vb1gYGD+/v5ubm4EBAQYyJtoAAAAAXRSTlMAQObYZgAAAAFiS0dEAIgFHUgAAAAJcEhZ' +
      'cwAACxMAAAsTAQCanBgAAAAHdElNRQfdChgTGQlB1c4VAAAALUlEQVQI12NgZAADDIqJkZuB' +
      'kYkJQqkogiiQBAYlzcCNrJIJJMgAkQMBbhgPADXrASb/uuETAAAAAElFTkSuQmCC';
  this.naturalFigBass.onload = function() {
    parent.imgLoaded();
  };

  this.doubleSharp = new Image();
  this.doubleSharp.src = 'data:image/png;base64, ' +
      'iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAQAAAAnOwc2AAAAdUlEQVQI11XOoQ3CYBiE4ae0' +
      'Bo0sSccoYQoMIzTFoNiEMaowKCZgDBKCQKCKa/Ihfpq0J+7yirscIYQDqBMtTBUpCgNyS1Bh' +
      'oLATVs6eep3GSzYW90JoE4ybX7w9EuRqpa2LjZurD9b+l07gOL/Ug/t8M5u4Hy58IZKVjiCm' +
      'AAAAAElFTkSuQmCC';
  this.doubleSharp.onload = function() {
    parent.imgLoaded();
  };

  this.doubleSharpColor = new Image();
  this.doubleSharpColor.src = 'data:image/png;base64, ' +
      'iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAYAAACNMs+9AAAAn0lEQVQY03XQIQ7CUBAE0Peh' +
      'Bl08xwDbG2CQlSSgMJwEBboOywFq6TFICIKkrjgSML/hh6RrZpKZ3Z3dUJTtx6+2dZUfoSjb' +
      'Oa69MDJc6QAZ3pGPMUm0WcR3b1zG7hyHomzv6HDGGg+EkI4vynYVDbCpq/w0lPEV8YlbKoR4' +
      '3SdmOmOBKS7YoUHI0hdgX1d5E2PscBha3SW8SYV/YxjgvqQXJ1gHIRdPAAAAAElFTkSuQmCC';
  this.doubleSharpColor.onload = function() {
    parent.imgLoaded();
  };

  this.doubleFlat = new Image();
  this.doubleFlat.src = 'data:image/png;base64, ' +
      'iVBORw0KGgoAAAANSUhEUgAAAA4AAAAXCAQAAACRmLYJAAAA/klEQVQoz83SvyvEcRgH8Nf9' +
      '8CM5m0vpNt0gZ7Io/4AYmA0Gf4JRSTGKusmg3HDKbLzIopRkc4NNSnR+lStX+H4Mvs7dcrP3' +
      '9H6ed8/T+/lBxZBfbFrWgrQJGfdxNCjbLkZCMwotHEkd8M/Fvk6jFHy1L4GGnHEZt3pEmDLs' +
      '04UbaorqguBDUHEmCIJneYK6bUtqcfLEon2R4IiGabAqCE7jA7wLoqS6a5AHVTAqhcskEug1' +
      '2+J+UheK6dj1nH6RFwl0W0DVYTqu3JCyJSuFFWPezHvlUc6BYAclewqCBwM/DZ+UBWug5Nid' +
      'cyOtrzEf893mMDGuzDT5urLeP+kbi0lPkzNfV2AAAAAASUVORK5CYII=';
  this.doubleFlat.onload = function() {
    parent.imgLoaded();
  };

  this.doubleFlatColor = new Image();
  this.doubleFlatColor.src = 'data:image/png;base64, ' +
      'iVBORw0KGgoAAAANSUhEUgAAAA4AAAAXCAYAAAA7kX6CAAABdklEQVQ4y+3TPWhUURAF4G/X' +
      'VYM/hT4VIWojWIixskgwzQvapdBASCEPRCzsUwpCQLARCwtBLC/RdIKFohavslIsU6QTC0V9' +
      'RNQiwurazMJl2U2xtVMNM5xzz8ycq6yaV2XVHDUQZdXcLatm2Yjo4Bz24/NA7zCObAf8i96Q' +
      'Xm9EHbSNGf+BYwL3jHuOKfzZzgCwVVbNcZwNF33E7jCHsmpmMYku3tWp+NAqq+YrnuAa9gZR' +
      'N0hfB9F09tgmpts4FKBHuI5vmZKL2MJVPI6ZD+BBB7+xWKfiRciaxEoA39SpKKP+HAuYwFwb' +
      'v7CRSTmV5etZfho7In/f32orWCcwP2LrM9gZ+f3OwJYvYV9sczMj3IUrmYpnnfxF3A459+IT' +
      '96XdxBn8xOU6Fd/b2R3XcBIP61Qsx/F7ZdVM4Ra+4Fidio3+DC3cwRJW6lTcyKSfwEu8xfk6' +
      'FT9y5xwM/Qt1Kp5moC7m4iSzw7y6jvkBEHzCKi4M8+o/M7lkrKLCiyoAAAAASUVORK5CYII=';
  this.doubleFlatColor.onload = function() {
    parent.imgLoaded();
  };

};

/**
 * Callback function called whenever an image has finished loading. Once
 * all images have loaded, call the controller's callback function so that
 * canvas initialization can complete.
 *
 * @method imgLoaded
 * @return {undefined}
 */
MusThGUI.Render.GlyphProvider.prototype.imgLoaded = function() {

  this.numImagesLoaded += 1;
  if (this.numImagesLoaded === 26) {
    this.imagesLoadedCallback();
  }

};

/**
 * Returns the specified accidental image.
 *
 * @method getAccidental
 * @param {String} accType The type of accidental image to return ('n', '#',
 * 'b', 'x', 'bb').
 * @param {Boolean} inColor If true, returns a color version of the
 * accidental.
 * @param {Boolean} figuredBass If true, returns a version to display within the
 * figured bass (only available for sharps, flat and naturals).
 * @param {Boolean} parenthesized If true, returns a parenthesized version of
 * the accidental
 * @return {Image} Returns the accidental image.
 */
MusThGUI.Render.GlyphProvider.prototype.getAccidental = function(accType,
    inColor, figuredBass, parenthesized) {

  switch (accType) {
    case 'n':
      if (figuredBass) {
        return this.naturalFigBass;
      }
      else if (parenthesized) {
        return (inColor) ? this.naturalParColor : this.naturalPar;
      }
      else {
        return (inColor) ? this.naturalColor : this.natural;
      }
      break;
    case '#':
      if (figuredBass) {
        return this.sharpFigBass;
      }
      else {
        return (inColor) ? this.sharpColor : this.sharp;
      }
      break;
    case 'b':
      if (figuredBass) {
        return this.flatFigBass;
      }
      else {
        return (inColor) ? this.flatColor : this.flat;
      }
      break;
    case 'x':
      return (inColor) ? this.doubleSharpColor : this.doubleSharp;
    case 'bb':
      return (inColor) ? this.doubleFlatColor : this.doubleFlat;
  }

};

/**
 * Returns the specified accidental image.
 *
 * @method getNoteValue
 * @param {String} noteValueType The type of accidental image to return
 * ('whole', 'quarter_stem_up', 'quarter_stem_down').
 * @param {Boolean} inColor If true, returns a color version of the
 * accidental.
 * @param {Boolean} forToolbar If true, returns a smaller version for toolbar
 * display (only available for quarter notes).
 * @return {Image} Returns the note value image.
 */
MusThGUI.Render.GlyphProvider.prototype.getNoteValue = function(noteValueType,
    inColor, forToolbar) {

  switch (noteValueType) {
    case 'whole':
      return (inColor) ? this.wholeNoteColor : this.wholeNote;
    case 'quarter_stem_up':
      if (forToolbar) {
        return this.qNoteStemUpToolbar;
      }
      else {
        return (inColor) ? this.qNoteStemUpColor : this.qNoteStemUp;
      }
      break;
    case 'quarter_stem_down':
      if (forToolbar) {
        return this.qNoteStemDownToolbar;
      }
      else {
        return (inColor) ? this.qNoteStemDownColor : this.qNoteStemDown;
      }
  }

};

/**
 * Returns the specified clef image.
 *
 * @method getClef
 * @param {String} clefType The type of clef ('treble', 'bass', 'alto',
 * 'tenor').
 * @return {Image} Returns the clef image.
 */
MusThGUI.Render.GlyphProvider.prototype.getClef = function(clefType) {

  switch (clefType) {
    case 'treble':
      return this.tClef;
    case 'bass':
      return this.bClef;
    case 'alto':
    case 'tenor':
      return this.cClef;
  }

};/* Copyright (c) 2013 Eric Brisson

 Permission is hereby granted, free of charge, to any person obtaining a copy
 of this software and associated documentation files (the "Software"), to deal
 in the Software without restriction, including without limitation the rights
 to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 copies of the Software, and to permit persons to whom the Software is
 furnished to do so, subject to the following conditions:

 The above copyright notice and this permission notice shall be included in
 all copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 THE SOFTWARE. */

/**
 * The InputDialog object represents a javascript dialog used to
 * enter an answer in one of the canvas' text input. The use of this dialog
 * allows the use of form elements to format the answer (in particular, the use
 * of select elements).
 *
 * @class InputDialog
 * @namespace Control
 *
 * @constructor
 * @param {Control.Controller} controller The library's controller
 * object.
 * @param {XMLDoc} stateXML The canvas state as xml. The xml specifies the
 * html code to include in the dialog.
 * @return {undefined}
 */
MusThGUI.Control.InputDialog = function(controller, stateXML) {

  this.controller = controller;
  this.parsedXML = stateXML;
  this.panel = null;
  this.colID = null;
  this.dialogNode = this.parsedXML.getElementsByTagName('InputDialog')[0];

  if (typeof(this.dialogNode) !== 'undefined' && this.dialogNode !== null) {
    this.formDivContentXML = this.dialogNode.getElementsByTagName('div')[0];
    this.dialogID = this.formDivContentXML.getAttribute('id');
    this.width = this.dialogNode.getAttribute('width');
    this.height = this.dialogNode.getAttribute('height');
    this.btnTxtOK = this.dialogNode.getAttribute('OKButtonText');
    this.btnTxtCancel = this.dialogNode.getAttribute('cancelButtonText');
  }
};

/**
 * Initializes the input dialog.
 *
 * @method init
 * @return {undefined}
 */
MusThGUI.Control.InputDialog.prototype.init = function() {

  Y.one('body').addClass('yui3-skin-sam');

  var parent = this;
  this.panel = new Y.Panel({
    bodyContent: Y.XML.format(this.formDivContentXML),
    headerContent: this.dialogNode.getAttribute('headerContent'),
    zIndex: 6,
    centered: true,
    modal: true,
    visible: false,
    render: true,
    plugins: [Y.Plugin.Drag],
    buttons: [
      {
        value: this.btnTxtOK,
        section: Y.WidgetStdMod.FOOTER,
        action: function(e) {
          e.preventDefault();
          var combinedInput = '';
          var divNode = parent.Y.one('#' + parent.dialogID);
          var selectElems = divNode.all('select');
          selectElems.each(function(selectElem) {
            combinedInput += selectElem.get('value');
          });
          parent.callBack(combinedInput);
          this.hide();
        }
      },
      {
        value: this.btnTxtCancel,
        section: Y.WidgetStdMod.FOOTER,
        action: function(e) {
          e.preventDefault();
          this.hide();
        }
      }
    ]
  });

};

/**
 * Displays the input dialog.
 *
 * @method show
 * @return {undefined}
 */
MusThGUI.Control.InputDialog.prototype.show = function() {
  if (typeof(this.panel) === 'undefined') {
    this.init();
  }
  this.resetDialogForm();
  this.panel.show();
};

/**
 * Resets all form elements to empty values.
 *
 * @method resetDialogForm
 * @return {undefined}
 */
MusThGUI.Control.InputDialog.prototype.resetDialogForm = function() {
  var divNode = Y.one('#' + this.dialogID);
  var selectElems = divNode.all('select');
  selectElems.each(function(selectElem) {
    selectElem.set('value', '');
  });
};

/**
 * Resets all form elements to empty values.
 *
 * @method callBack
 * @param {String} combinedInput The form's input, to be sent back to the
 * controller object passed as argument to the constructor.
 * @return {undefined}
 */
MusThGUI.Control.InputDialog.prototype.callBack = function(combinedInput) {

  this.controller.setColTextInput(this.colID, combinedInput);

};

}, '@VERSION@', {"requires": ["base", "node", "datatype", "panel", "dd-plugin"]});
