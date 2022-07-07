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
 * The Renderer object takes care of drawing all canvas elements.
 *
 * @class Renderer
 * @namespace Render
 *
 * @constructor
 * @param {Canvas} can An html5 canvas element.
 * @param {GUIState.State} state The canvas' state.
 * @param {Render.CoordManager} coordMgr The library's coordinate
 * manager.
 * @return {undefined}
 */
KeyboardInput.Render.Renderer = function(can, state, coordMgr) {

  this.can = can;
  this.state = state;
  this.coordMgr = coordMgr;

};

/**
 * Draws all canvas elements.
 *
 * @method draw
 * @return {undefined}
 */
KeyboardInput.Render.Renderer.prototype.draw = function() {
  var parent = this;
  var keyRend;

  // Draw white keys first
  Y.Array.each(this.state.keyboard.getKeys('white'), function(key) {
    keyRend = new KeyboardInput.Render.KeyRend(parent.can, parent.coordMgr,
        key);
    keyRend.draw();
  });

  // Black keys next
  Y.Array.each(this.state.keyboard.getKeys('black'), function(key) {
    keyRend = new KeyboardInput.Render.KeyRend(parent.can, parent.coordMgr,
        key);
    keyRend.draw();
  });


};