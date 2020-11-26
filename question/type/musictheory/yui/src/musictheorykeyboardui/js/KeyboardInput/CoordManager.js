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
KeyboardInput.Render.CoordManager = function(state) {

  this.state = state;
  this.CANVAS_PADDING = 15;
  this.WHITE_KEY_WIDTH = 14;
  this.WHITE_KEY_HEIGHT = 80;
  this.BLACK_KEY_WIDTH = 7;
  this.BLACK_KEY_HEIGHT = 60;
  this.CANVAS_WIDTH = this.CANVAS_PADDING * 2 + this.WHITE_KEY_WIDTH * 52;
  this.CANVAS_HEIGHT = this.CANVAS_PADDING * 2 + this.WHITE_KEY_HEIGHT;

};

/**
 * Given a mouse event location, determine if a canvas element (a keyboard key)
 * was involved, and if so, return the element.
 *
 * @method getElementFromPoint
 * @param {Object} point An object literal representing the location of the
 * mouse event, with properties x and y.
 * @return {Object} An object literal with a properties 'pitchClass' and
 * 'register'. Returns null if no element was involved.
 */
KeyboardInput.Render.CoordManager.prototype.getElementFromPoint = function(
    point) {
  if (this.isKeyboardPoint(point)) {
    return this.getKeyFromPoint(point);
  }
  else {
    return null;
  }
};

/**
 * Given a point, determine if the point is located within the keyboard on the
 * canvas.
 *
 * @method isKeyboardPoint
 * @param {Object} point An object literal representing the location of the
 * mouse event, with properties x and y.
 * @return {Boolean} Returns true if the point is located within the keyboard.
 */
KeyboardInput.Render.CoordManager.prototype.isKeyboardPoint = function(point) {
  var keybUL = {x: this.CANVAS_PADDING, y: this.CANVAS_PADDING};
  var keybLR = {x: this.CANVAS_PADDING + this.WHITE_KEY_WIDTH * 52,
    y: this.CANVAS_PADDING + this.WHITE_KEY_HEIGHT};

  if (this.pointWithin(point, keybUL, keybLR)) {
    return true;
  }
  else {
    return false;
  }
};

/**
 * Given a point known to be located within the keyboard, returns the key
 * that referred to by the point.
 *
 * @method getKeyFromPoint
 * @param {Object} point An object literal representing the location of the
 * mouse event, with properties x and y.
 * @return {Object} An object literal with a properties 'pitchClass' and
 * 'register'.
 */
KeyboardInput.Render.CoordManager.prototype.getKeyFromPoint = function(point) {
  var adjPointReg =
      {x: point.x - this.CANVAS_PADDING - this.WHITE_KEY_WIDTH * 2,
        y: point.y - this.CANVAS_PADDING};
  var register = Math.floor(adjPointReg.x / (this.WHITE_KEY_WIDTH * 7) + 1);


  var adjPointKey = {x: adjPointReg.x + -(register - 1) *
        (this.WHITE_KEY_WIDTH * 7),
    y: adjPointReg.y};


  var bKeyCenterIndices = [1, 2, 4, 5, 6];
  var i;

  for (i = 0; i < bKeyCenterIndices.length; i++) {
    var bKeyUL = {x: bKeyCenterIndices[i] * this.WHITE_KEY_WIDTH
          - this.BLACK_KEY_WIDTH / 2, y: 0};
    var bKeyLR = {x: bKeyCenterIndices[i] * this.WHITE_KEY_WIDTH
          + this.BLACK_KEY_WIDTH / 2,
      y: this.BLACK_KEY_HEIGHT};
    if (this.pointWithin(adjPointKey, bKeyUL, bKeyLR)) {
      switch (bKeyCenterIndices[i]) {
        case 1:
          if (register === 8) {
            return {register: register, pitchClass: 0};
          }
          return {register: register, pitchClass: 1};
        case 2:
          return {register: register, pitchClass: 3};
        case 4:
          return {register: register, pitchClass: 6};
        case 5:
          if (register === 0) {
            return {register: register, pitchClass: 9};
          }
          return {register: register, pitchClass: 8};
        case 6:
          return {register: register, pitchClass: 10};
      }
    }
  }

  var whiteKeyID = Math.floor(adjPointKey.x / this.WHITE_KEY_WIDTH) + 1;

  switch (whiteKeyID) {
    case 1:
      return {register: register, pitchClass: 0};
    case 2:
      if (register === 8) {
        return {register: register, pitchClass: 0};
      }
      return {register: register, pitchClass: 2};
    case 3:
      return {register: register, pitchClass: 4};
    case 4:
      return {register: register, pitchClass: 5};
    case 5:
      if (register === 0) {
        return {register: register, pitchClass: 9};
      }
      return {register: register, pitchClass: 7};
    case 6:
      return {register: register, pitchClass: 9};
    case 7:
      return {register: register, pitchClass: 11};

  }
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
KeyboardInput.Render.CoordManager.prototype.pointWithin = function(point,
    llPoint,
    brPoint) {

  if (point.x >= llPoint.x && point.x <= brPoint.x &&
      point.y >= llPoint.y && point.y <= brPoint.y) {
    return true;
  }
  else {
    return false;
  }

};