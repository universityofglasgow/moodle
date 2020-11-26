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

};