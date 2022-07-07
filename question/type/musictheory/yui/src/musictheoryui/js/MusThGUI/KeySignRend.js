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
 * The KeySignRend object takes care of drawing a staff's key
 * signature.
 *
 * @class KeySignRend
 * @namespace Render
 *
 * @constructor
 * @param {Canvas} can An html5 canvas element.
 * @param {Render.CoordManager} coordMgr The library's coordinate
 * manager.
 * @param {Render.GlyphProvider} glyphProvider The library's glyph
 * provider.
 * @param {Number} staffID The zero-based ID of the staff where the key
 * signature is to be rendered.
 * @param {String} clef The staff's clef.
 * @param {GUIState.KeySign} keySign The key signature to render.
 * @return {undefined}
 */
MusThGUI.Render.KeySignRend = function(can, coordMgr, glyphProvider, staffID,
    clef, keySign) {

  this.keySign = keySign;
  this.clef = clef;
  this.staffID = staffID;
  this.coordMgr = coordMgr;
  this.can = can;
  this.glyphProvider = glyphProvider;

};

/**
 * Draws the key signature on the staff.
 *
 * @method draw
 * @return {undefined}
 */
MusThGUI.Render.KeySignRend.prototype.draw = function() {

  var parent = this;
  var ctx = this.can.getContext('2d');
  var accPos = null;
  var img;

  var i;
  for (i = 0; i < this.keySign.accidentals.length; i++) {
    var acc = this.keySign.accidentals[i];
    accPos = parent.coordMgr.getKeySignAccPos(parent.staffID, parent.clef,
        i, acc.letter, acc.register);
    if (acc.isGhost === true) {
      ctx.globalAlpha = 0.5;
    }
    else {
      ctx.globalAlpha = 1;
    }
    img = parent.glyphProvider.getAccidental(acc.type, acc.editable, false);
    if (acc.type === '#') {
      ctx.drawImage(img, accPos.x - (img.width / 2),
          accPos.y - 14);
    }
    else if (acc.type === 'b') {
      ctx.drawImage(img, accPos.x + 2 - (img.width / 2),
          accPos.y - 17.5);
    }
    ctx.globalAlpha = 1;
  }

};