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

};