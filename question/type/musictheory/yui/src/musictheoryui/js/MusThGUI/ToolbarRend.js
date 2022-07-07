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
 * The ToolbarRend object takes care of drawing a toolbar.
 *
 * @class ToolbarRend
 * @namespace Render
 *
 * @constructor
 * @param {Canvas} can An html5 canvas element.
 * @param {Render.CoordManager} coordMgr The library's coordinate
 * manager.
 * @param {Render.GlyphProvider} glyphProvider The library's glyph
 * provider.
 * @param {GUIState.Toolbar} toolbar The toolbar to be rendered.
 * @return {undefined}
 */
MusThGUI.Render.ToolbarRend = function(can, coordMgr, glyphProvider, toolbar) {

  this.can = can;
  this.toolbar = toolbar;
  this.coordMgr = coordMgr;
  this.glyphProvider = glyphProvider;

};

/**
 * Draws the toolbar.
 *
 * @method draw
 * @param {Number} toolbarID The zero-based ID of the toolbar to be rendered.
 * @return {undefined}
 */
MusThGUI.Render.ToolbarRend.prototype.draw = function(toolbarID) {

  var parent = this;
  var btnSize = this.coordMgr.getToolbarBtnSize();
  var x = this.coordMgr.getToolbarX(toolbarID);
  var y = this.coordMgr.getToolbarY(this.toolbar.buttons.length);

  var btnRend;
  var i;
  for (i = 0; i < this.toolbar.buttons.length; i++) {
    var button = this.toolbar.buttons[i];
    btnRend = new MusThGUI.Render.ToolbarBtnRend(parent.can,
        parent.glyphProvider, button, x, y + i * btnSize.height,
        btnSize.width, btnSize.height);
    btnRend.draw();
  }

};