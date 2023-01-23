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
 * The KeyRend object takes care of drawing a key on the keyboard.
 *
 * @class KeyRend
 * @namespace Render
 *
 * @constructor
 * @param {Canvas} can An html5 canvas element.
 * @param {Render.CoordManager} coordMgr The library's coordinate
 * manager.
 * @param {GUIState.Key} key The key to be rendered.
 * @return {undefined}
 */
KeyboardInput.Render.KeyRend = function(can, coordMgr, key) {

  this.can = can;
  this.coordMgr = coordMgr;
  this.key = key;

};

/**
 * Draws the key on the keyboard.
 *
 * @method draw
 * @return {undefined}
 */
KeyboardInput.Render.KeyRend.prototype.draw = function() {

  var context = this.can.getContext('2d');
  var keybLeftAdj = 5 * this.coordMgr.WHITE_KEY_WIDTH;

  if (this.key.isWhiteKey) {
    var whiteKeyX = this.key.register * (7 * this.coordMgr.WHITE_KEY_WIDTH) +
        this.key.getSubsetIDWithinRegister() * this.coordMgr.WHITE_KEY_WIDTH;

    context.beginPath();
    context.rect(this.coordMgr.CANVAS_PADDING + whiteKeyX - keybLeftAdj,
        this.coordMgr.CANVAS_PADDING,
        this.coordMgr.WHITE_KEY_WIDTH,
        this.coordMgr.WHITE_KEY_HEIGHT);
    if (!this.key.editable) {
      context.fillStyle = 'red';
    }
    else if (this.key.selected) {
      context.fillStyle = '#415ded';
    }
    else if (this.key.isGhost) {
      context.fillStyle = 'grey';
    }
    else {
      context.fillStyle = 'white';
    }
    context.fill();
    context.lineWidth = 1;
    context.strokeStyle = 'black';
    context.stroke();
  }
  else {
    var blackKeyXWithinReg;
    switch (this.key.getSubsetIDWithinRegister()) {
      case 0:
        blackKeyXWithinReg = 1 * this.coordMgr.WHITE_KEY_WIDTH -
            this.coordMgr.BLACK_KEY_WIDTH / 2;
        break;
      case 1:
        blackKeyXWithinReg = 2 * this.coordMgr.WHITE_KEY_WIDTH -
            this.coordMgr.BLACK_KEY_WIDTH / 2;
        break;
      case 2:
        blackKeyXWithinReg = 4 * this.coordMgr.WHITE_KEY_WIDTH -
            this.coordMgr.BLACK_KEY_WIDTH / 2;
        break;
      case 3:
        blackKeyXWithinReg = 5 * this.coordMgr.WHITE_KEY_WIDTH -
            this.coordMgr.BLACK_KEY_WIDTH / 2;
        break;
      case 4:
        blackKeyXWithinReg = 6 * this.coordMgr.WHITE_KEY_WIDTH -
            this.coordMgr.BLACK_KEY_WIDTH / 2;
        break;
      default:
        blackKeyXWithinReg = 0;
    }

    var blackKeyX = this.key.register * (7 * this.coordMgr.WHITE_KEY_WIDTH) +
        blackKeyXWithinReg;

    context.beginPath();
    context.rect(this.coordMgr.CANVAS_PADDING + blackKeyX - keybLeftAdj,
        this.coordMgr.CANVAS_PADDING,
        this.coordMgr.BLACK_KEY_WIDTH,
        this.coordMgr.BLACK_KEY_HEIGHT);
    if (!this.key.editable) {
      context.fillStyle = 'red';
    }
    else if (this.key.selected) {
      context.fillStyle = '#415ded';
    }
    else if (this.key.isGhost) {
      context.fillStyle = 'grey';
    }
    else {
      context.fillStyle = 'black';
    }
    context.fill();
    context.lineWidth = 1;
    context.strokeStyle = 'black';
    context.stroke();

  }

};