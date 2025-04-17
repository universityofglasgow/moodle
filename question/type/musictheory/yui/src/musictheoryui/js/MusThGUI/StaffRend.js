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
 * The StaffRend object takes care of drawing a staff.
 *
 * @class StaffRend
 * @namespace Render
 *
 * @constructor
 * @param {Canvas} can An html5 canvas element.
 * @param {Render.CoordManager} coordMgr The library's coordinate
 * manager.
 * @param {Render.GlyphProvider} glyphProvider The library's glyph
 * provider.
 * @param {GUIState.Staff} staff The staff to be rendered.
 * @param {Number} staffID The zero-based ID of the staff to be rendered.
 * @param {Boolean} accCarryOver Indicates whether accidentals are to carry
 * over
 * @return {undefined}
 */
MusThGUI.Render.StaffRend = function(can, coordMgr, glyphProvider, staff,
    staffID, accCarryOver) {

  this.can = can;
  this.staff = staff;
  this.coordMgr = coordMgr;
  this.staffID = staffID;
  this.glyphProvider = glyphProvider;
  this.accCarryOver = accCarryOver;

};

/**
 * Draws the staff.
 *
 * @method draw
 * @return {undefined}
 */
MusThGUI.Render.StaffRend.prototype.draw = function() {

  this.drawLines();
  this.drawClef();
  this.drawKeySign();
  this.drawNoteColumns();

};

/**
 * Draws staff lines.
 *
 * @method drawLines
 * @return {undefined}
 */
MusThGUI.Render.StaffRend.prototype.drawLines = function() {

  var stWidth = this.coordMgr.getStaffWidth(this.staff.noteColumns.length,
      this.staff.keySign.totalAccColumns);
  var i;
  var ctx = this.can.getContext('2d');
  ctx.lineWidth = 1;
  var x;
  var y;
  for (i = 0; i < 5; i++) {
    x = this.coordMgr.getStaffLinePos(this.staffID, i).x;
    y = this.coordMgr.getStaffLinePos(this.staffID, i).y;
    ctx.beginPath();
    ctx.moveTo(x, y);
    ctx.lineTo(x + stWidth, y);
    ctx.stroke();
  }

};

/**
 * Draws the staff's clef.
 *
 * @method drawClef
 * @return {undefined}
 */
MusThGUI.Render.StaffRend.prototype.drawClef = function() {

  var clefPos = this.coordMgr.getClefPos(this.staffID);

  var x;
  var y;
  var ctx = this.can.getContext('2d');
  var img = this.glyphProvider.getClef(this.staff.clef);
  if (this.staff.clef === 'treble') {
    x = parseInt(clefPos.x - (img.width / 2), 10);
    y = clefPos.y - 17;
    ctx.drawImage(img, x, y);
  }
  else if (this.staff.clef === 'bass') {
    x = parseInt(clefPos.x - (img.width / 2), 10);
    y = clefPos.y;
    ctx.drawImage(img, x, y);
  }
  if (this.staff.clef === 'alto') {
    x = parseInt(clefPos.x - (img.width / 2), 10);
    y = clefPos.y + 1;
    ctx.drawImage(img, x, y);
  }
  if (this.staff.clef === 'tenor') {
    x = parseInt(clefPos.x - (img.width / 2), 10);
    y = clefPos.y - 9;
    ctx.drawImage(img, x, y);
  }

};

/**
 * Draws the staff's key signature.
 *
 * @method drawKeySign
 * @return {undefined}
 */
MusThGUI.Render.StaffRend.prototype.drawKeySign = function() {

  var keySign = new MusThGUI.Render.KeySignRend(this.can, this.coordMgr,
      this.glyphProvider, this.staffID, this.staff.clef, this.staff.keySign);
  keySign.draw();

};

/**
 * Draws the staff's note columns.
 *
 * @method drawNoteColumns
 * @return {undefined}
 */
MusThGUI.Render.StaffRend.prototype.drawNoteColumns = function() {

  var parent = this;
  var noteColRend;
  var i;
  for (i = 0; i < this.staff.noteColumns.length; i++) {
    noteColRend = new MusThGUI.Render.NoteColRend(parent.can, parent.coordMgr,
        parent.glyphProvider, parent.staff, parent.staffID, i,
        parent.accCarryOver);
    noteColRend.draw();
  }

};