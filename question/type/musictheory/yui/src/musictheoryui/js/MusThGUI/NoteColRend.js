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
 * The NoteColRend object takes care of drawing the elements of a
 * staff's note column.
 *
 * @class NoteColRend
 * @namespace Render
 *
 * @constructor
 * @param {Canvas} can An html5 canvas element.
 * @param {Render.CoordManager} coordMgr The library's coordinate
 * manager.
 * @param {Render.GlyphProvider} glyphProvider The library's glyph
 * provider.
 * @param {GUIState.Staff} staff The staff containing this note
 * column.
 * @param {Number} staffID The zero-based ID of the staff where containing the
 * note column.
 * @param {Number} noteColID The zero-based ID of this note column.
 * @param {Boolean} accCarryOver Indicates whether accidentals are to carry
 * over
 * @return {undefined}
 */
MusThGUI.Render.NoteColRend = function(can, coordMgr, glyphProvider, staff,
    staffID, noteColID, accCarryOver) {

  this.can = can;
  this.coordMgr = coordMgr;
  this.noteCol = staff.noteColumns[noteColID];
  this.noteColID = noteColID;
  this.staff = staff;
  this.staffID = staffID;
  this.accCarryOver = accCarryOver;
  this.clef = staff.clef;
  this.keySign = staff.keySign;
  this.maxNotes = this.noteCol.maxNotes;
  this.glyphProvider = glyphProvider;

};

/**
 * Draws the note column's elements  on the staff.
 *
 * @method draw
 * @return {undefined}
 */
MusThGUI.Render.NoteColRend.prototype.draw = function() {

  var parent = this;
  var noteRend;
  // It is assumed that notes are ordered from top to bottom

  // Two traversals in different directions are required to compute the
  // note offset and the accidental placement.
  var accResetNote = null;
  var noteName, accPos;
  var noteRends = [];

  var i, note, displayAcc;
  var displayAccPar = false;
  var prevColNote = null;
  var nextColNote = null;
  for (i = 0; i < this.noteCol.notes.length; i++) {
    note = this.noteCol.notes[i];
    if (i < (this.noteCol.notes.length - 1)) {
      nextColNote = this.noteCol.notes[i + 1];
    }
    noteName = note.letter + note.register;
    if (!this.accCarryOver) {
      if (note.accidental !== 'n') {
        displayAcc  = true;
      }
      else {
        displayAcc = parent.displayAccidental(note, prevColNote, nextColNote);
        if (displayAcc) {
          displayAccPar = true;
        }
      }
    }
    else {
      displayAcc = parent.displayAccidental(note, prevColNote, nextColNote);
    }
    if (displayAcc) {
      if (accResetNote === null) {
        accPos = 0;
        accResetNote = noteName;
      }
      else if (parent.coordMgr.getIntervalSize(accResetNote, noteName) <= 6) {
        accPos++;
      }
      else {
        accPos = 0;
        accResetNote = noteName;
      }
    }
    noteRend = new MusThGUI.Render.NoteRend(parent.can, parent.coordMgr,
        parent.glyphProvider, note, parent.noteColID, parent.staffID,
        parent.clef, parent.keySign.totalAccColumns, displayAcc, accPos,
        displayAccPar);
    noteRends.push(noteRend);
    prevColNote = note;
  }

  var previousNote, noteOffset;
  for (i = noteRends.length - 1; i >= 0; i--) {
    note = noteRends[i];
    noteName = noteRends[i].note.letter + noteRends[i].note.register;
    if (i === noteRends.length - 1) {
      previousNote = noteName;
      noteOffset = false;
      note.draw(noteOffset);
    }
    else {
      if (this.coordMgr.getIntervalSize(noteName, previousNote) === 2) {
        previousNote = noteName;
        noteOffset = !(noteOffset);
        note.draw(noteOffset);
      }
      else {
        previousNote = noteName;
        noteOffset = false;
        note.draw(noteOffset);
      }
    }
  }

  // Draw Text Input
  if (this.noteCol.textInput !== null) {
    var tInputHeight = this.coordMgr.textInputHeight;
    var textCenterX = this.coordMgr.getNoteColCenterX(this.staffID,
        this.noteColID, this.keySign.totalAccColumns);
    var textY = this.can.height - tInputHeight;
    var ctx = this.can.getContext('2d');

    var textInputWidth = this.coordMgr.textInputWidth;

    if (this.noteCol.textInput.editable) {
      ctx.beginPath();
      ctx.rect(textCenterX - textInputWidth / 2, textY - (tInputHeight / 2),
          textInputWidth, (tInputHeight));
      ctx.fillStyle = '#eaeaea';
      ctx.fill();
      ctx.lineWidth = 0.5;
      ctx.strokeStyle = 'black';
      ctx.stroke();
    }

    ctx.font = '11px Verdana';
    ctx.textAlign = 'center';
    ctx.fillStyle = 'black';
    ctx.fillText(this.noteCol.textInput.value, textCenterX, textY + 4);
  }

  // Draw figured bass
  if (this.noteCol instanceof MusThGUI.GUIState.TwoVoiceNoteCol) {
    var figBassRend = new MusThGUI.GUIState.FiguredBassRend(
        this.can, this.coordMgr, this.glyphProvider, this.noteCol.figBass,
        this.staffID, this.noteColID, this.keySign.totalAccColumns);
    figBassRend.draw();
  }

};

/**
 * Indicates whether a given note's accidental should be displayed. Accidentals
 * are assumed to carry over from one note column to the next.
 *
 * @method displayAccidental
 * @param {GUIState.Note} note The note whose accidental display is
 * to be considered.
 * @param {GUIState.Note} prevColNote The previous note in the note
 * column, if present (null is assumed otherwise).
 * @param {GUIState.Note} nextColNote The next note in the note
 * column, if present (null is assumed otherwise).
 * @return {Boolean} Returns true if the note can be removed from the column.
 */
MusThGUI.Render.NoteColRend.prototype.displayAccidental = function(note,
    prevColNote,
    nextColNote) {

  var previousAcc = null;

  // Check if the note is in the key signature, and then check whether a
  // previous note with same letter name/register has been altered in the
  // staff.

  var i, acc;
  for (i = 0; i < this.keySign.accidentals.length; i++) {
    acc = this.keySign.accidentals[i];
    if (acc.letter === note.letter) {
      previousAcc = acc.type;
      break;
    }
  }

  var j, prevNote;
  for (i = 0; i < this.noteColID; i++) {
    for (j = 0; j < this.staff.noteColumns[i].notes.length; j++) {
      prevNote = this.staff.noteColumns[i].notes[j];
      if (prevNote.letter === note.letter &&
          prevNote.register === note.register) {
        previousAcc = prevNote.accidental;
      }
    }
  }

  if (prevColNote !== null) {
    if (note.letter === prevColNote.letter
        && note.register === prevColNote.register &&
        note.accidental !== prevColNote.accidental) {
      return true;
    }
  }
  if (nextColNote !== null) {
    if (note.letter === nextColNote.letter
        && note.register === nextColNote.register &&
        note.accidental !== nextColNote.accidental) {
      return true;
    }
  }
  if (prevColNote !== null) {
    if (note.letter === prevColNote.letter
        && note.register === prevColNote.register &&
        note.accidental === prevColNote.accidental) {
      return false;
    }
  }

  if (previousAcc === null && note.accidental === 'n') {
    return false;
  }

  if (previousAcc === note.accidental) {
    return false;
  }
  else {
    return true;
  }

};