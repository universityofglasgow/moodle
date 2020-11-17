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
 * The Keyboard object stores the canvas state's keyboard.
 *
 * @class Keyboard
 * @namespace GUIState
 *
 * @constructor
 * @return {undefined}
 */
KeyboardInput.GUIState.Keyboard = function() {

  this.keys = [];
  this.maxKeys = 0;

  var i, j;

  for (i = 0; i <= 8; i++) {
    this.keys[i] = [];
  }
  this.keys[0][9] = new KeyboardInput.GUIState.Key(9, 0, true, false, false);
  this.keys[0][10] = new KeyboardInput.GUIState.Key(10, 0, true, false, false);
  this.keys[0][11] = new KeyboardInput.GUIState.Key(11, 0, true, false, false);
  for (i = 1; i <= 7; i++) {
    for (j = 0; j < 12; j++) {
      this.keys[i][j] = new KeyboardInput.GUIState.Key(j, i, true, false,
          false);
    }

  }
  this.keys[8][0] = new KeyboardInput.GUIState.Key(0, 8, true, false, false);
};

/**
 * Returns all keyboard keys or a subset of the keys.
 *
 * @method getKeys
 * @param {String} subset A parameter describing which keys should be returned
 * ('all', 'white', 'black', 'selected', 'givenkeys'). 'givenkeys' returns all
 * keys that are not editable.
 * @return {Array} An array of keyboard keys.
 */
KeyboardInput.GUIState.Keyboard.prototype.getKeys = function(subset) {
  var keys = [];
  Y.Array.each(this.keys, function(register) {
    Y.Array.each(register, function(key) {
      if (subset === 'all' ||
          (subset === 'white' && key.isWhiteKey) ||
          (subset === 'black' && !key.isWhiteKey) ||
          (subset === 'selected' && key.selected) ||
          (subset === 'givenkeys' && !key.editable)) {
        keys.push(key);
      }
    });
  });
  return keys;
};

/**
 * Returns a single keyboard key, given a pitchclass and a register.
 *
 * @method getKey
 * @param {Number} register The key's register.
 * @param {Number} pitchClass The key's pitchClass.
 * @return {GUIState.Key} The requested key.
 */
KeyboardInput.GUIState.Keyboard.prototype.getKey = function(register,
    pitchClass) {
  return this.keys[register][pitchClass];
};