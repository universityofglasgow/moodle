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

};