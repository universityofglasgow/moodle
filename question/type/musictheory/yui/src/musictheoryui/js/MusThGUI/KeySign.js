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

};