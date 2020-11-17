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
 * The ToolbarBtnRend object takes care of drawing a toolbar button.
 *
 * @class ToolbarBtnRend
 * @namespace Render
 *
 * @constructor
 * @param {Canvas} can An html5 canvas element.
 * @param {Render.GlyphProvider} glyphProvider The library's glyph
 * provider.
 * @param {GUIState.ToolbarBtn} button The toolbar button to be
 * rendered.
 * @param {Number} x The x-coordinate of the upper-left corner of the button.
 * @param {Number} y The y-coordinate of the upper-left corner of the button.
 * @param {Number} width The button's width.
 * @param {Number} height The button's height.
 * @return {undefined}
 */
MusThGUI.Render.ToolbarBtnRend = function(can, glyphProvider, button, x, y,
    width, height) {

  this.can = can;
  this.button = button;
  this.x = x;
  this.y = y;
  this.width = width;
  this.height = height;
  this.glyphProvider = glyphProvider;

};

/**
 * Draws the toolbar button.
 *
 * @method draw
 * @return {undefined}
 */
MusThGUI.Render.ToolbarBtnRend.prototype.draw = function() {

  var ctx = this.can.getContext('2d');
  ctx.beginPath();
  ctx.rect(this.x, this.y, this.width, this.height);
  if (this.button.selected === true) {
    ctx.fillStyle = '#d2d2d2';
  }
  else if (this.button.mouseOverlay === true) {
    ctx.fillStyle = '#eaeaea';
  }
  else {
    ctx.fillStyle = '#f6f6f6';
  }
  ctx.fill();
  ctx.lineWidth = 0.5;
  ctx.strokeStyle = 'black';
  ctx.stroke();

  var img;
  if (this.button.symbol === 'n') {
    img = this.glyphProvider.getAccidental(this.button.symbol, false);
    ctx.drawImage(img,
        this.x + this.width / 2 - img.width / 2,
        this.y + this.height / 2 - img.height / 2);
  }
  else if (this.button.symbol === '#') {
    img = this.glyphProvider.getAccidental(this.button.symbol, false);
    ctx.drawImage(img, this.x + this.width / 2 - img.width / 2 - 1,
        this.y + this.height / 2 - img.height / 2);
  }
  else if (this.button.symbol === 'b') {
    img = this.glyphProvider.getAccidental(this.button.symbol, false);
    ctx.drawImage(img, this.x + this.width / 2 - img.width / 2,
        this.y + this.height / 2 - img.height / 2);
  }
  else if (this.button.symbol === 'x') {
    img = this.glyphProvider.getAccidental(this.button.symbol, false);
    ctx.drawImage(img, this.x + this.width / 2 - img.width / 2,
        this.y + this.height / 2 - img.height / 2 + 1);
  }
  else if (this.button.symbol === 'bb') {
    img = this.glyphProvider.getAccidental(this.button.symbol, false);
    ctx.drawImage(img, this.x + this.width / 2 - img.width / 2 + 1,
        this.y + this.height / 2 - img.height / 2);
  }
  else if (this.button.symbol === 'whole') {
    img = this.glyphProvider.getNoteValue(this.button.symbol, false, false);
    ctx.drawImage(img, this.x + this.width / 2 - img.width / 2,
        this.y + this.height / 2 - img.height / 2 + 1);
  }
  else if (this.button.symbol === 'quarter_stem_up') {
    img = this.glyphProvider.getNoteValue(this.button.symbol, false, true);
    ctx.drawImage(img, this.x + this.width / 2 - img.width / 2,
        this.y + this.height / 2 - img.height / 2);
  }
  else if (this.button.symbol === 'quarter_stem_down') {
    img = this.glyphProvider.getNoteValue(this.button.symbol, false, true);
    ctx.drawImage(img, this.x + this.width / 2 - img.width / 2,
        this.y + this.height / 2 - img.height / 2 + 1);
  }

};