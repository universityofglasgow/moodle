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
 * The NoteCol object represents a note column on a staff.
 *
 * @class NoteCol
 * @namespace GUIState
 *
 * @constructor
 * @param {Number} maxNotes The maximum number of notes that the column can
 * contain.
 * @param {GUIState.TextInput} textInput The text input associated
 * with the note column (null if no text input is present).
 * @return {undefined}
 */
MusThGUI.GUIState.NoteCol = function(maxNotes, textInput) {

  if (arguments.length === 0) {
    return;
  }
  this.notes = [];
  this.maxNotes = parseInt(maxNotes, 10);
  this.textInput = textInput;

};

/**
 * Adds a note to the note column.
 *
 * @method addNote
 * @param {GUIState.Note} note The note to be added.
 * @return {undefined}
 */
MusThGUI.GUIState.NoteCol.prototype.addNote = function(note) {

  this.notes.push(note);
  // Descending sort (top notes first)
  this.notes.sort(function(note1, note2) {
    var comp = -(MusThGUI.GUIState.Note.compare(note1, note2));
    return comp;
  });

};

/**
 * Removes a note from the note column. No action taken if the given note isn't
 * in the column.
 *
 * @method removeNote
 * @param {GUIState.Note} note The note to be removed.
 * @return {undefined}
 */
MusThGUI.GUIState.NoteCol.prototype.removeNote = function(note) {

  var letter = note.letter;
  var register = note.register;
  var i;
  for (i = 0; i < this.notes.length; i++) {
    if (this.notes[i].letter === letter &&
        this.notes[i].register === register) {
      break;
    }
  }
  this.notes.splice(i, 1);

};

/**
 * Removes the ghost note from the column, if present. It is assumed that there
 * can be at most one ghost note in the column at any given time.
 *
 * @method removeGhostNote
 * @return {undefined}
 */
MusThGUI.GUIState.NoteCol.prototype.removeGhostNote = function() {

  var i;
  for (i = 0; i < this.notes.length; i++) {
    if (this.notes[i].isGhost) {
      break;
    }
  }
  this.notes.splice(i, 1);

};

/**
 * Indicates whether the given note is present in the column (ghost notes
 * ignored).
 *
 * @method inColumn
 * @param {GUIState.Note} note The note to look for.
 * @return {GUIState.Note} Returns the found note. If the note
 * isn't found, returns null.
 */
MusThGUI.GUIState.NoteCol.prototype.inColumn = function(note) {

  var foundNote = null;
  var letter = note.letter;
  var register = note.register;
  var i;
  for (i = 0; i < this.notes.length; i++) {
    if (this.notes[i].letter === letter &&
        this.notes[i].register === register &&
        this.notes[i].isGhost === false) {
      foundNote = this.notes[i];
      break;
    }
  }
  return foundNote;

};

/**
 * Indicates whether the number of non-ghost notes in the column is equal to
 * the maximum number of notes allowable in the column.
 *
 * @method isFull
 * @return {Boolean} Returns true if the note column is full.
 */
MusThGUI.GUIState.NoteCol.prototype.isFull = function() {

  var i = 0;
  var cnt = 0;
  for (i = 0; i < this.notes.length; i++) {
    if (!(this.notes[i].isGhost)) {
      cnt++;
    }
  }
  if (cnt >= this.maxNotes) {
    return true;
  }
  else {
    return false;
  }

};

/**
 * Indicates whether the given note can be added to the column.
 *
 * @method noteAddible
 * @param {GUIState.Note} potentialNote The note to consider adding.
 * @return {Boolean} Returns true if the note can be added to the column.
 */
MusThGUI.GUIState.NoteCol.prototype.noteAddible = function(potentialNote) {
  if (this.inColumn(potentialNote) === null && !(this.isFull())) {

    return true;
  }
  else {
    return false;
  }

};

/**
 * Indicates whether the given note can be removed from the column.
 *
 * @method noteRemovable
 * @param {GUIState.Note} note The note to consider removing.
 * @return {Boolean} Returns true if the note can be removed from the column.
 */
MusThGUI.GUIState.NoteCol.prototype.noteRemovable = function(note) {

  var noteClicked = this.inColumn(note);
  if (noteClicked !== null) {
    if (noteClicked.editable) {
      return true;
    }
    else {
      return false;
    }
  }
  else {
    return false;
  }

};