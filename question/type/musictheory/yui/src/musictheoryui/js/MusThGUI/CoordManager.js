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

};