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
 * Represents the state of a toolbar button.
 *
 * @class ToolbarBtn
 * @namespace GUIState
 *
 * @constructor
 * @param {String} symbol A string indicating the type of musical symbol to
 * display in a toolbar button ('n' [natural], '#' [sharp], 'b' [flat],
 * 'x' [double sharp], 'bb' [double flat], 'whole' [whole note],
 * 'quarter_stem_up' [quarter note with stem up],
 * 'quarter_stem_down' [quarter note with stem down]).
 * @return {undefined}
 */
MusThGUI.GUIState.ToolbarBtn = function(symbol) {

  this.selected = false;
  this.mouseOverlay = false;
  this.symbol = symbol;

};