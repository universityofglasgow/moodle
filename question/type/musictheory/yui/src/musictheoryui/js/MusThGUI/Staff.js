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

};