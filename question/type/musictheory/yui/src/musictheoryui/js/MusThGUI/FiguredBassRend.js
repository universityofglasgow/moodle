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
 * The FiguredBassRend object takes care of drawing the figuration
 * notation on the canvas.
 *
 * @class FiguredBassRend
 * @namespace GUIState
 *
 * @constructor
 * @param {Canvas} can An html5 canvas element.
 * @param {Render.CoordManager} coordMgr The library's coordinate
 * manager.
 * @param {Render.GlyphProvider} glyphProvider The library's glyph
 * provider.
 * @param {GUIState.FiguredBass} figBass The figured bass to be
 * rendered.
 * @param {Number} staffID The zero-based ID of the staff.
 * @param {Number} colID The zero-based ID of the note column.
 * @param {Number} totalAccCols The number of accidental columns
 * in the key signature portion of the staff.
 * @return {undefined}
 */
MusThGUI.GUIState.FiguredBassRend = function(can, coordMgr, glyphProvider,
    figBass, staffID, colID, totalAccCols) {

  this.can = can;
  this.coordMgr = coordMgr;
  this.figBass = figBass;
  this.staffID = staffID;
  this.colID = colID;
  this.totalAccCols = totalAccCols;
  this.glyphProvider = glyphProvider;

};

/**
 * Draws the figured bass on the canvas.
 *
 * @method draw
 * @return {undefined}
 */
MusThGUI.GUIState.FiguredBassRend.prototype.draw = function() {

  // Set lower left corner at the first ledger line below the staff
  var lLCorner = this.coordMgr.getNotePos('A', 3, this.staffID, 'treble',
      this.colID, this.totalAccCols);

  var ctx = this.can.getContext('2d');
  ctx.font = 'bold 13px Times New Roman';
  ctx.textAlign = 'left';
  ctx.fillStyle = 'black';

  var i, j, x, y, col, img;
  var forwardStroke = false;
  for (i = 0; i < this.figBass.rows.length; i++) {
    col = 0;
    for (j = 0; j < this.figBass.rows[i].cols.length; j++) {
      var char = this.figBass.rows[i].cols[j];

      if (char === '/') {
        forwardStroke = true;
      }
      else if (char === '#') {
        img = this.glyphProvider.getAccidental(char, false, true);
        ctx.drawImage(img,
            lLCorner.x + 1 + col * 8,
            lLCorner.y + i * 17 - 12);
        col += 1;
      }
      else if (char === 'b') {
        img = this.glyphProvider.getAccidental(char, false, true);
        ctx.drawImage(img,
            lLCorner.x + 1 + col * 8,
            lLCorner.y + i * 17 - 13);
        col += 1;
      }
      else if (char === 'n') {
        img = this.glyphProvider.getAccidental(char, false, true);
        ctx.drawImage(img,
            lLCorner.x + 1 + col * 8,
            lLCorner.y + i * 17 - 12);
        col += 1;
      }
      else {
        x = lLCorner.x + 1 + col * 8;
        y = lLCorner.y + i * 17;
        ctx.fillText(char, x, y);
        col += 1;
        if (forwardStroke) {
          ctx.beginPath();
          ctx.moveTo(x - 2, y);
          ctx.lineTo(x + 10, y - 9);
          ctx.lineWidth = 0.75;
          ctx.stroke();
          forwardStroke = false;
        }
      }
    }
  }

};