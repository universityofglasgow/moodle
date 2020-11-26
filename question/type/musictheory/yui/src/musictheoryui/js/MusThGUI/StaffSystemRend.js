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
 * The StaffSystemRend object takes care of drawing a staff system.
 *
 * @class StaffSystemRend
 * @namespace Render
 *
 * @constructor
 * @param {Canvas} can An html5 canvas element.
 * @param {Render.CoordManager} coordMgr The library's coordinate
 * manager.
 * @param {Render.GlyphProvider} glyphProvider The library's glyph
 * provider.
 * @param {GUIState.StaffSystem} staffSystem The staff system to be
 * rendered.
 * @param {Boolean} accCarryOver Indicates whether accidentals are to carry
 * over
 * @return {undefined}
 */
MusThGUI.Render.StaffSystemRend = function(can, coordMgr, glyphProvider,
    staffSystem, accCarryOver) {

  this.can = can;
  this.staffSystem = staffSystem;
  this.coordMgr = coordMgr;
  this.glyphProvider = glyphProvider;
  this.accCarryOver = accCarryOver;

};

/**
 * Draws the staff system.
 *
 * @method draw
 * @return {undefined}
 */
MusThGUI.Render.StaffSystemRend.prototype.draw = function() {

  var parent = this;

  var i;
  for (i = 0; i < this.staffSystem.staves.length; i++) {
    var staff = this.staffSystem.staves[i];
    var staffRend = new MusThGUI.Render.StaffRend(parent.can, parent.coordMgr,
        parent.glyphProvider, staff, i, parent.accCarryOver);
    staffRend.draw();
  }

  var sysWidth = this.coordMgr.getStaffWidth(
      this.staffSystem.staves[0].noteColumns.length,
      this.staffSystem.staves[0].keySign.totalAccColumns);

  // draw left vertical height
  var x = this.coordMgr.getStaffOrigin(0).x;
  var startY = this.coordMgr.getStaffOrigin(0).y;
  var endY = this.coordMgr.getStaffOrigin(
      this.staffSystem.staves.length - 1).y +
      this.coordMgr.getStaffHeight(false);
  var ctx = this.can.getContext('2d');
  ctx.beginPath();
  ctx.moveTo(x, startY);
  ctx.lineTo(x, endY);
  ctx.stroke();

  // draw right vertical height
  x += sysWidth;
  ctx.beginPath();
  ctx.moveTo(x, startY);
  ctx.lineTo(x, endY);
  ctx.stroke();

};