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

};