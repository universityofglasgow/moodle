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
 * The State object stores the canvas context
 * (e.g. number of staves, clefs, key signature, notes, toolbars, input dialog,
 * etc).
 *
 * @class State
 * @namespace GUIState
 *
 * @constructor
 * @return {undefined}
 */
MusThGUI.GUIState.State = function() {

  this.staffSystem = null;
  this.toolbars = [];

};

/**
 * Sets the state after instantiation.
 *
 * @method setState
 * @param {XMLDoc} stateXML The canvas state as xml, used to build the state
 * object.
 * @return {undefined}
 */
MusThGUI.GUIState.State.prototype.setState = function(stateXML) {

  this.stateXML = stateXML;

  // Canvas editable
  var musThGUI = this.stateXML.getElementsByTagName('MusThGUI');
  this.editable = musThGUI[0].getAttribute('canvasEditable') === 'true';
  this.accCarryOver = musThGUI[0].getAttribute('accidentalCarryOver') ===
      'true';

  // Staff System
  this.staffSystem = new MusThGUI.GUIState.StaffSystem();
  var i, j, k, l, editable, acc;
  var sSys = this.stateXML.getElementsByTagName('StaffSystem');
  for (i = 0; i < sSys.length; i++) {

    this.staffSystem.maxLedgerLines =
        parseInt(sSys[i].getAttribute('maxLedgerLines'), 10);

    //Staves
    var staffXML = sSys[i].getElementsByTagName('Staff');
    for (j = 0; j < staffXML.length; j++) {

      // Clef
      var clef = staffXML[j].getAttribute('clef');

      // Key signature
      var keySignXML = staffXML[j].getElementsByTagName('KeySign')[0];
      var totalAccCol = keySignXML.getAttribute('totalAccColumns');
      var keySign = new MusThGUI.GUIState.KeySign(parseInt(totalAccCol, 10));

      var accXML = keySignXML.getElementsByTagName('Accidental');
      for (k = 0; k < accXML.length; k++) {
        editable = accXML[k].getAttribute('editable') === 'true';
        acc = new MusThGUI.GUIState.KeySignAcc(
            accXML[k].getAttribute('type'), accXML[k].getAttribute('letter'),
            parseInt(accXML[k].getAttribute('register'), 10), editable, false);
        keySign.addAccidental(acc);
      }

      var staff = new MusThGUI.GUIState.Staff(clef, keySign);

      // Note columns
      var noteColXML = staffXML[j].getElementsByTagName('NoteColumn');
      for (k = 0; k < noteColXML.length; k++) {

        var maxNotes = parseInt(noteColXML[k].getAttribute('maxNotes'), 10);

        // Text inputs
        var textInput = null;
        var textInputXML = noteColXML[k].getElementsByTagName('TextInput');
        for (l = 0; l < textInputXML.length; l++) {
          editable = textInputXML[l].getAttribute('editable') === 'true';
          textInput = new MusThGUI.GUIState.TextInput(
              textInputXML[l].getAttribute('value'), editable);
        }

        var colType = noteColXML[k].getAttribute('type');
        var noteColumn;

        // Two-voice note columns
        if (colType === 'two-voice') {
          // Figured bass
          var figBass = new MusThGUI.GUIState.FiguredBass();
          var figBassRow;
          var figBassXML = noteColXML[k].getElementsByTagName('Row');
          for (l = 0; l < figBassXML.length; l++) {
            figBassRow = new MusThGUI.GUIState.FiguredBassRow(
                figBassXML[l].getAttribute('text'));
            figBass.addRow(figBassRow);
          }
          noteColumn = new MusThGUI.GUIState.TwoVoiceNoteCol(figBass,
              textInput);
        }
        // Standard note columns
        else {
          noteColumn = new MusThGUI.GUIState.NoteCol(maxNotes,
              textInput);
        }

        // Column notes
        var noteXML = noteColXML[k].getElementsByTagName('Note');
        for (l = 0; l < noteXML.length; l++) {

          editable = noteXML[l].getAttribute('editable') === 'true';
          acc = (noteXML[l].getAttribute('accidental') === '') ? 'n' :
              noteXML[l].getAttribute('accidental');
          var note = new MusThGUI.GUIState.Note(
              noteXML[l].getAttribute('letter'),
              noteXML[l].getAttribute('register'), acc,
              noteXML[l].getAttribute('noteValue'), editable, false);
          noteColumn.addNote(note);

        } //Columns notes

        staff.addNoteColumn(noteColumn);

      } // Note columns

      this.staffSystem.addStaff(staff);

    } // Staves

  } // Staff system


  // Toolbars
  var toolbar;
  // Accidental toolbar
  var toolbarBtn, toolbarBtnXML;
  var accTlbrXML = this.stateXML.getElementsByTagName('AccidentalToolbar')[0];
  if (typeof(accTlbrXML) !== 'undefined' && accTlbrXML !== null) {
    if (this.editable && accTlbrXML.childNodes.length > 0) {
      this.toolbars.push(new MusThGUI.GUIState.Toolbar('acc'));
      toolbar = this.toolbars[this.toolbars.length - 1];
      toolbarBtnXML = accTlbrXML.getElementsByTagName('Button');
      for (i = 0; i < toolbarBtnXML.length; i++) {
        toolbarBtn = new MusThGUI.GUIState.ToolbarBtn(
            toolbarBtnXML[i].getAttribute('symbol'));
        toolbar.addButton(toolbarBtn);
      }
      toolbar.buttons[0].selected = true;
    }
  }
  // Note value toolbar
  var nValTlbrXML = this.stateXML.getElementsByTagName('NoteValueToolbar')[0];
  if (typeof(nValTlbrXML) !== 'undefined' && nValTlbrXML !== null) {
    if (this.editable && nValTlbrXML.childNodes.length > 0) {
      this.toolbars.push(new MusThGUI.GUIState.Toolbar('noteVal'));
      toolbar = this.toolbars[this.toolbars.length - 1];
      toolbarBtnXML = nValTlbrXML.getElementsByTagName('Button');
      for (i = 0; i < toolbarBtnXML.length; i++) {
        toolbarBtn = new MusThGUI.GUIState.ToolbarBtn(
            toolbarBtnXML[i].getAttribute('symbol'));
        toolbar.addButton(toolbarBtn);
      }
      toolbar.buttons[0].selected = true;
    }
  }

};

/**
 * Returns the canvas state.
 *
 * @method getState
 * @return {String} The canvas state as xml.
 */
MusThGUI.GUIState.State.prototype.getState = function() {

  var outXML = '<MusThGUI canvasEditable="' + this.editable +
      '" accidentalCarryOver="' + this.accCarryOver + '">\n';
  outXML += '    <StaffSystem maxLedgerLines="' +
      this.staffSystem.maxLedgerLines + '">\n';

  // Staves
  Y.Array.each(this.staffSystem.staves, function(staff) {

    // Clef
    outXML += '        <Staff clef="' + staff.clef + '">\n';

    // Key signature
    outXML += '            <KeySign totalAccColumns="'
        + staff.keySign.totalAccColumns + '">\n';

    Y.Array.each(staff.keySign.accidentals, function(acc) {

      if (!(acc.isGhost)) {
        outXML += '                <Accidental type="' + acc.type +
            '" letter="' + acc.letter +
            '" register="' + acc.register +
            '" editable="' + acc.editable +
            '" />\n';
      }

    });

    outXML += '            </KeySign>\n';

    // Note columns
    outXML += '            <NoteColumns>\n';

    Y.Array.each(staff.noteColumns, function(noteCol) {

      if (noteCol instanceof MusThGUI.GUIState.TwoVoiceNoteCol) {
        outXML += '                <NoteColumn type="two-voice" maxNotes="'
            + noteCol.maxNotes + '" >\n';
      }
      else {
        outXML += '                <NoteColumn maxNotes="'
            + noteCol.maxNotes + '" >\n';
      }

      // Column notes
      Y.Array.each(noteCol.notes, function(note) {

        if (!(note.isGhost)) {
          outXML += '                    <Note letter="' + note.letter +
              '" register="' + note.register +
              '" accidental="' + note.accidental +
              '" noteValue="' + note.noteValue +
              '" editable="' + note.editable +
              '" />\n';
        }

      });

      // Figured bass
      if (noteCol instanceof MusThGUI.GUIState.TwoVoiceNoteCol) {
        if (noteCol.figBass.rows.length > 0) {
          outXML += '                    <FiguredBass>\n';
          Y.Array.each(noteCol.figBass.rows, function(row) {
            outXML += '                        <Row text="'
                + row.rowText + '" />\n';
          });
          outXML += '                    </FiguredBass>\n';
        }
      }

      // Text input
      if (noteCol.textInput !== null) {
        outXML += '                    <TextInput value="' +
            noteCol.textInput.value +
            '" editable="' + noteCol.textInput.editable + '" />\n';
      }
      outXML += '                </NoteColumn>\n';

    }); // Note Columns

    outXML += '            </NoteColumns>\n';
    outXML += '        </Staff>\n';

  }); // Staves

  outXML += '    </StaffSystem>\n';

  // Toolbars (identical to xml input)
  var toolbarXML;
  var accTlbrXML = this.stateXML.getElementsByTagName('AccidentalToolbar')[0];
  if (typeof(accTlbrXML) !== 'undefined' && accTlbrXML !== null) {
    toolbarXML = Y.XML.format(accTlbrXML);
    outXML += toolbarXML + '\n';
  }

  // Toolbars (identical to xml input)
  var nValTlbrXML = this.stateXML.getElementsByTagName('NoteValueToolbar')[0];
  if (typeof(nValTlbrXML) !== 'undefined' && nValTlbrXML !== null) {
    toolbarXML = Y.XML.format(nValTlbrXML);
    outXML += toolbarXML + '\n';
  }

  // Input Dialog (identical to xml input)
  var inputDialXML = this.stateXML.getElementsByTagName('InputDialog')[0];
  if (typeof(inputDialXML) !== 'undefined' && inputDialXML !== null) {
    var dialogXML = Y.XML.format(inputDialXML);
    outXML += dialogXML + '\n';
  }

  outXML += '</MusThGUI>';

  return outXML;

};

/**
 * Indicates whether the state contains a toolbar, given the toolbar name.
 *
 * @method hasToolbar
 * @param {String} name The toolbar name.
 * @return {Boolean)
 */
MusThGUI.GUIState.State.prototype.hasToolbar = function(name) {

  var i;
  for (i = 0; i < this.toolbars.length; i++) {
    if (this.toolbars[i].name === name) {
      return true;
    }
  }
  return false;

};

/**
 * Returns a toolbar, given the toolbar name.
 *
 * @method getToolbar
 * @param {String} name The toolbar name.
 * @return {GUIState.Toolbar | null} Returns null if the toolbar
 * isn't found.
 */
MusThGUI.GUIState.State.prototype.getToolbar = function(name) {

  var i;
  for (i = 0; i < this.toolbars.length; i++) {
    if (this.toolbars[i].name === name) {
      return this.toolbars[i];
    }
  }
  return null;

};

/**
 * Returns the number of buttons for the longest toolbar in the state.
 *
 * @method getMaxNumToolbarButtons
 * @return {Number} Returns 0 if the state doesn't have any toolbars.
 */
MusThGUI.GUIState.State.prototype.getMaxNumToolbarButtons = function() {

  var numButtons = 0;
  Y.Array.each(this.toolbars, function(toolbar) {
    numButtons = Math.max(numButtons, toolbar.buttons.length);
  });
  return numButtons;

};