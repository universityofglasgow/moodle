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
 The Music Theory GUI module allows for display and entry of notes in various
 contexts that are suitable for music theory exercises.

 The user interface is created by instantiating a {{#crossLink "MusThGUI"}}
 {{/crossLink}} object.

 Here is an example describing how to use this module:

 1) Add an HTMLtag for a small canvas in the web page source, with a unique 'id'
 attribute.

	<canvas id="myUniqueID" width="1" height="1"/>

 2) Define an XML string that will describe the interface's initial state. Here
 is an example that would be well-suited for a scale question:

	var initXML = '<MusThGUI canvasEditable="true" accidentalCarryOver="true">' +
	'<StaffSystem maxLedgerLines="3">' +
	'<Staff clef="treble">' +
	'<KeySign totalAccColumns="7" >' +
	'<Accidental type="#" letter="F" register="5" editable="false" />' +
	'<Accidental type="#" letter="C" register="5" editable="false" />' +
	'<Accidental type="#" letter="G" register="5" editable="false" />' +
	'</KeySign>' +
	'<NoteColumns>' +
	'<NoteColumn maxNotes="1" />' +
	'<NoteColumn maxNotes="1" />' +
	'<NoteColumn maxNotes="1" />' +
	'<NoteColumn maxNotes="1" />' +
	'<NoteColumn maxNotes="1" />' +
	'<NoteColumn maxNotes="1" />' +
	'<NoteColumn maxNotes="1" />' +
	'<NoteColumn maxNotes="1" />' +
	'</NoteColumns>' +
	'</Staff>' +
	'</StaffSystem>' +
	'<Toolbars>' +
	'<AccidentalToolbar>' +
	'<Button symbol="n" />' +
	'<Button symbol="#" />' +
	'<Button symbol="b" />' +
	'<Button symbol="##" />' +
	'<Button symbol="bb" />' +
	'</AccidentalToolbar>' +
	'</Toolbars>' +
	'</MusThGUI>';

 For more details on the XML schema that the interface will accept and return,
 see the 'valid_input_xml.xsd' file in the module's XML directory.

 3) Define a callback function that the interface will use to message back its
 current state. This function will be called whenever the user changes the
 interface's state (i.e. by adding or deleting notes on the staff).

	var callBack = function(stateXML) {
	//stateXML will contain the most up-to-date canvas state.
	};

 4) Create a {{#crossLink "MusThGUI"}}{{/crossLink}} object using the canvas id
 from step 1, the XML string from step 2 and the callback function from step 3:

	var musicCanvas = new MusThGUI('myUniqueID', initXML, callBack, true);

 Argument 4 specifies whether the interface should be editable after
 initialization. Use a false value to display a static interface.

 Once instantiated, the MusThGUI object will display an interactive music canvas
 on the web page. Whenever the user makes any changes, the callback function will
 be called and the most up-to-date canvas state will be returned, as an XML
 string.

 @module MusThGUI
 **/

/**
 The MusThGUI object is used to interact with outside components.

 @class MusThGUI
 @constructor

 @param {String} canID The id of the HTMl 5 canvas to which this application
 will be bound.
 @param {String} stateXML The canvas state as xml.
 @param {Function} respCallBack A function that the controller will use
 to send the current canvas answer back the caller, whenever the state
 changes. This function should accept one argument which provides the
 current XML output.
 @param {Boolean} editable Specifies whether the user can change the UI
 after initialization.
 @return {undefined}
 **/
MusThGUI = function(canID, stateXML, respCallBack) {
  this.callBack = function(cBack) {
    return function(stateXML) {
      cBack(stateXML);
    };
  };

  this.UI = new MusThGUI.Control.Controller(canID, stateXML,
      this.callBack(respCallBack));
};