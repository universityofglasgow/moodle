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
 * The NoteRend object takes care of drawing a note on a staff.
 *
 * @class NoteRend
 * @namespace Render
 *
 * @constructor
 * @param {Canvas} can An html5 canvas element.
 * @param {Render.CoordManager} coordMgr The library's coordinate
 * manager.
 * @param {Render.GlyphProvider} glyphProvider The library's glyph
 * provider.
 * @param {GUIState.Note} note The note to be rendered.
 * @param {Number} noteColID The zero-based ID of this note column containing
 * the note.
 * @param {Number} staffID The zero-based ID of the staff where the note is to
 * be rendered.
 * @param {String} clef The staff's clef.
 * @param {Number} keySignTotalAccColumns The total number of accidentals
 * columns in the key signature.
 * @param {Boolean} displayAcc Indicates whether the note's accidental should
 * be displayed.
 * @param {Number} accPos A number indicating whether and how much an
 * accidental should be offset to the left of the note (e.g. 0 = no offset, 1 =
 * one offset level to the left, 2 = two offset levels to teh left, etc.).
 * @param {Boolean} displayAccPar Indicates whether the note's accidental should
 * be parenthesized.
 * @return {undefined}
 */
MusThGUI.Render.NoteRend = function(can, coordMgr, glyphProvider, note,
    noteColID, staffID, clef, keySignTotalAccColumns, displayAcc, accPos,
    displayAccPar) {

  this.can = can;
  this.coordMgr = coordMgr;
  this.note = note;
  this.noteColID = noteColID;
  this.staffID = staffID;
  this.clef = clef;
  this.keySignTotalAccColumns = keySignTotalAccColumns;
  this.displayAcc = displayAcc;
  this.accPos = accPos;
  this.displayAccPar = displayAccPar;
  this.accColWidth = 15;
  this.glyphProvider = glyphProvider;

};

/**
 * Draws the note on the staff.
 *
 * @method draw
 * @param {Boolean} noteOffset Indicates whether the note should be offset to
 * the right so that its note head doesn't overlap with a note displayed a step
 * below.
 * @return {undefined}
 */
MusThGUI.Render.NoteRend.prototype.draw = function(noteOffset) {

  var notePos = this.coordMgr.getNotePos(this.note.letter, this.note.register,
      this.staffID, this.clef, this.noteColID, this.keySignTotalAccColumns);
  notePos.x -= 8;

  // Draw note head

  var ctx = this.can.getContext('2d');

  // Make ghost note transparent
  if (this.note.isGhost === true) {
    ctx.globalAlpha = 0.5;
  }
  else {
    ctx.globalAlpha = 1;
  }
  var noteImageWidth, x, y, offsetSize;
  var img = this.glyphProvider.getNoteValue(this.note.noteValue,
      this.note.editable, false);
  if (this.note.noteValue === 'whole') {
    noteImageWidth = img.width;
    offsetSize = (noteOffset) ? 15 : 0;
    x = notePos.x + offsetSize + 1;
    y = notePos.y - img.height / 2;
    ctx.drawImage(img, x, y);
  }
  else if (this.note.noteValue === 'quarter_stem_up') {
    noteImageWidth = img.width;
    offsetSize = (noteOffset) ? 11 : 0;
    x = notePos.x + 1 + offsetSize;
    y = notePos.y - img.height / 2 - 13;
    ctx.drawImage(img, x, y);
  }
  else if (this.note.noteValue === 'quarter_stem_down') {
    noteImageWidth = img.width;
    offsetSize = (noteOffset) ? 11 : 0;
    x = notePos.x + 1 + offsetSize;
    y = notePos.y - img.height / 2 + 13;
    ctx.drawImage(img, x, y);
  }

  // Draw ledger lines
  var ledgerLines = this.coordMgr.getLedgerLinesY(this.staffID, this.clef,
      this.note.letter, this.note.register);


  if (this.note.editable) {
    ctx.strokeStyle = '#415ded';
  }
  else {
    ctx.strokeStyle = 'black';
  }
  ctx.lineWidth = 1;
  var i;
  for (i = 0; i < ledgerLines.length; i++) {
    var lLineY = ledgerLines[i];
    if (i === 0 || !(noteOffset)) {
      ctx.beginPath();
      ctx.moveTo(notePos.x - 4, lLineY);
      ctx.lineTo(notePos.x + noteImageWidth + offsetSize + 5, lLineY);
      ctx.stroke();
    }
  }
  ctx.strokeStyle = 'black';

  // Draw accidental
  img = this.glyphProvider.getAccidental(this.note.accidental,
      this.note.editable, false, this.displayAccPar);
  if (this.displayAcc) {
    if (this.note.accidental === '#') {
      ctx.drawImage(img, notePos.x - (img.width) - 6 -
          this.accPos * this.accColWidth, notePos.y - 14);
    }
    else if (this.note.accidental === 'b') {
      ctx.drawImage(img, notePos.x - (img.width) - 7 -
          this.accPos * this.accColWidth, notePos.y - 17);
    }
    else if (this.note.accidental === 'bb') {
      ctx.drawImage(img, notePos.x - (img.width) - 5 -
          this.accPos * this.accColWidth, notePos.y - 17);
    }
    else if (this.note.accidental === 'x') {
      ctx.drawImage(img, notePos.x - (img.width) - 7 -
          this.accPos * this.accColWidth, notePos.y - 5);
    }
    else if (this.note.accidental === 'n') {
      ctx.drawImage(img, notePos.x - (img.width) - 4 -
          this.accPos * this.accColWidth, notePos.y - 15);
    }
  }

  ctx.globalAlpha = 1;

};