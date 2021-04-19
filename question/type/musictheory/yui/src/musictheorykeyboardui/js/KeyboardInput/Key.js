/* Copyright (c) 2014 Eric Brisson

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
 * The Key object represents a keyboard key.
 *
 * @class Key
 * @namespace GUIState
 *
 * @constructor
 * @param {Number} pitchClass The zero-based pitch class of the key, with C=0.
 * @param {Number} register The keys's register.
 * @param {Boolean} editable Indicates whether the key can be edited
 * after initialization.
 * @param {Boolean} selected Indicates whether the key is selected.
 * @param {Boolean} isGhost Indicates whether the key is to be
 * displayed as a ghost symbol.
 * @return {undefined}
 */
KeyboardInput.GUIState.Key = function(pitchClass, register, editable,
    selected, isGhost) {

  this.pitchClass = pitchClass;
  this.register = parseInt(register, 10);
  this.editable = editable;
  this.selected = selected;
  this.isGhost = isGhost;
  switch (pitchClass) {
    case 0:
    case 2:
    case 4:
    case 5:
    case 7:
    case 9:
    case 11:
      this.isWhiteKey = true;
      break;
    default:
      this.isWhiteKey = false;
  }

};

/**
 * Returns the zero-based subset ID of the key within the register, with C=0
 * for white keys and C#/Db=0 for black keys.
 *
 * For example, the subset ID of G is 4 (i.e. G is the 5th white key above C),
 * and the subset ID of F#/Gb is 2 (F#/Gb is the 3rd black key above C#/Db).
 *
 * @method getSubsetIDWithinRegister
 * @return {Number} The key's subset ID within the register.
 */
KeyboardInput.GUIState.Key.prototype.getSubsetIDWithinRegister = function() {
  if (this.isWhiteKey) {
    switch (this.pitchClass) {
      case 0:
        return 0;
      case 2:
        return 1;
      case 4:
        return 2;
      case 5:
        return 3;
      case 7:
        return 4;
      case 9:
        return 5;
      case 11:
        return 6;
      default:
        return null;
    }
  }
  else {
    switch (this.pitchClass) {
      case 1:
        return 0;
      case 3:
        return 1;
      case 6:
        return 2;
      case 8:
        return 3;
      case 10:
        return 4;
      default:
        return null;
    }
  }
};