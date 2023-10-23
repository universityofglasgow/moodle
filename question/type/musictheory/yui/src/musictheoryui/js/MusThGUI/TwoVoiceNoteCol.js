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
 * The TwoVoiceNoteCol object represents a note column on a staff,
 * specifically designed to handle a voice-leading context where two voices are
 * shown in the column, with stems in opposite direction.
 *
 * @class TwoVoiceNoteCol
 * @namespace GUIState
 * @extends GUIState.NoteCol
 *
 * @constructor
 * @param {GUIState.FiguredBass} figBass The note column's figured
 * bass indication.
 * @param {GUIState.TextInput} textInput The text input associated
 * with the note column (null if no text input is present).
 * @return {undefined}
 */
MusThGUI.GUIState.TwoVoiceNoteCol = function(figBass, textInput) {

  MusThGUI.GUIState.NoteCol.call(this, 2, textInput);
  this.superclass = MusThGUI.GUIState.NoteCol.prototype;
  this.figBass = figBass;

};

MusThGUI.GUIState.TwoVoiceNoteCol.prototype = new MusThGUI.GUIState.NoteCol();
MusThGUI.GUIState.TwoVoiceNoteCol.prototype.constructor =
    MusThGUI.GUIState.TwoVoiceNoteCol;

/**
 * Adds a note to the note column. Once the note is added, rearranges note stems
 * so that they are in the opposite direction.
 *
 * @method addNote
 * @param {GUIState.Note} note The note to be added.
 * @return {undefined}
 */
MusThGUI.GUIState.TwoVoiceNoteCol.prototype.addNote = function(note) {

  this.superclass.addNote.call(this, note);
  this.rearrangeStems();

};

/**
 * Removes a note from the note column. No action taken if the given note isn't
 * in the column. If two notes are in unison in the column, both notes are
 * removed. Once the note is removed, rearranges note stems so that they are in
 * the opposite direction.
 *
 * @method removeNote
 * @param {GUIState.Note} note The note to be removed.
 * @return {undefined}
 */
MusThGUI.GUIState.TwoVoiceNoteCol.prototype.removeNote = function(note) {

  var letter = note.letter;
  var register = note.register;
  var firstRemoval = null;
  var removalCnt = 0;
  var i;
  for (i = 0; i < this.notes.length; i++) {
    if (this.notes[i].letter === letter &&
        this.notes[i].register === register &&
        this.notes[i].editable) {
      if (firstRemoval === null) {
        firstRemoval = i;
      }
      removalCnt += 1;
    }
  }

  this.notes.splice(firstRemoval, removalCnt);
  this.rearrangeStems();

};

/**
 * Removes the ghost note from the column, if present. It is assumed that there
 * can be at most one ghost note in the column at any given time. Once the ghost
 * note is removed, rearranges note stems so that they are in the opposite
 * direction.
 *
 * @method removeGhostNote
 * @return {undefined}
 */
MusThGUI.GUIState.TwoVoiceNoteCol.prototype.removeGhostNote = function() {

  var i;
  for (i = 0; i < this.notes.length; i++) {
    if (this.notes[i].isGhost) {
      break;
    }
  }
  this.notes.splice(i, 1);
  this.rearrangeStems();

};

/**
 * Rearranges the note stems, as needed, so that they are in opposite direction.
 *
 * @method rearrangeStems
 * @return {undefined}
 */
MusThGUI.GUIState.TwoVoiceNoteCol.prototype.rearrangeStems = function() {

  // It is assumed that this function will only be called on
  // note columns of type 'two-voice' and that the maximum number
  // of notes in the column is 2. It is also assumed that any ghost note
  // will be sorted ahead of another note with same letter and register.


  var n0 = (typeof this.notes[0] !== 'undefined') ? this.notes[0] : null;
  var n1 = (typeof this.notes[1] !== 'undefined') ? this.notes[1] : null;

  if (n0 !== null && n1 !== null) {
    if (n1.isGhost && n0.letter === n1.letter &&
        n0.register === n1.register) {
      var n = n1;
      n1 = n0;
      n0 = n;
    }
  }

  // 2 notes in column
  if (n0 !== null && n1 !== null) {
    if (n0.editable && n1.editable) {
      n0.noteValue = 'quarter_stem_up';
      n1.noteValue = 'quarter_stem_down';
    }
    else if (n0.editable) {
      if (n1.noteValue === 'quarter_stem_up') {
        n0.noteValue = 'quarter_stem_down';
      }
      else {
        n0.noteValue = 'quarter_stem_up';
      }
    }
    else if (n1.editable) {
      if (n0.noteValue === 'quarter_stem_up') {
        n1.noteValue = 'quarter_stem_down';
      }
      else {
        n1.noteValue = 'quarter_stem_up';
      }
    }
  }
  // 1 note in column - audomatic stem up
  else if (n0 !== null) {
    if (n0.editable) {
      n0.noteValue = 'quarter_stem_up';
    }
  }
  else if (n1 !== null) {
    if (n1.editable) {
      n1.noteValue = 'quarter_stem_up';
    }
  }

};

/**
 * Indicates whether the given note can be added to the column.
 *
 * @method noteAddible
 * @param {GUIState.Note} potentialNote The note to consider adding.
 * @return {Boolean} Returns true if the note can be added to the column.
 */
MusThGUI.GUIState.TwoVoiceNoteCol.prototype.noteAddible =
    function(potentialNote) {

      if (this.isFull()) {
        return false;
      }
      else if (this.lengthWithoutGhost() === 0) {
        return true;
      }
      else {
        var firstNonGhost = this.getFirstNonGhost();
        if (!(firstNonGhost.editable)) {
          var comp = MusThGUI.GUIState.Note.compare(potentialNote,
              firstNonGhost);
          if (firstNonGhost.noteValue === 'quarter_stem_up') {
            return (comp <= 0);
          }
          else {
            return (comp >= 0);
          }
        }
        else {
          return true;
        }
      }

    };

/**
 * Indicates whether the given note can be removed from the column.
 *
 * @method noteRemovable
 * @param {GUIState.Note} note The note to consider removing.
 * @return {Boolean} Returns true if the note can be removed from the column.
 */
MusThGUI.GUIState.TwoVoiceNoteCol.prototype.noteRemovable = function(note) {

  var noteClicked = this.inColumn(note);
  if (noteClicked !== null) {
    return true;
  }
  else {
    return false;
  }

};

/**
 * Provides the number of notes currently in the column, excluding the ghost
 * note.
 *
 * @method lengthWithoutGhost
 * @return {Number} Returns the number of non-ghost notes in the column.
 */
MusThGUI.GUIState.TwoVoiceNoteCol.prototype.lengthWithoutGhost = function() {

  var count = 0;
  var i;
  for (i = 0; i < this.notes; i++) {
    if (!(this.notes[i].isGhost)) {
      count += 1;
    }
  }
  return count;

};

/**
 * Provides the first non-ghost note in the column.
 *
 * @method getFirstNonGhost
 * @return {GUIState.Note | null} Returns the first non-ghost note in
 * the column. If the column doesn't have non-ghost notes, returns null.
 */
MusThGUI.GUIState.TwoVoiceNoteCol.prototype.getFirstNonGhost = function() {

  var i;
  for (i = 0; i < this.notes.length; i++) {
    if (!(this.notes[i].isGhost)) {
      return this.notes[i];
    }
  }
  return null;

};