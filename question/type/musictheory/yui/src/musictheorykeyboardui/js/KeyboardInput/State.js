/* Copyright (c) 2014 Eric Brisson

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
 *
 * @class State
 * @namespace GUIState
 *
 * @constructor
 * @return {undefined}
 */
KeyboardInput.GUIState.State = function() {

  this.keyboard = new KeyboardInput.GUIState.Keyboard();

};

/**
 * Sets the state after instantiation.
 *
 * @method setState
 * @param {XMLDoc} stateXML The canvas state as xml, used to build the state
 * object.
 * @return {undefined}
 */
KeyboardInput.GUIState.State.prototype.setState = function(stateXML) {

  this.stateXML = stateXML;

  var keyboardInput = this.stateXML.getElementsByTagName('keyboardinput');
  var maxKeys = parseInt(keyboardInput[0].getAttribute('maxkeys'), 10);
  this.editable = keyboardInput[0].getAttribute('canvasEditable') === 'true';

  this.keyboard.maxKeys = maxKeys;

  var givenNotes = this.stateXML.getElementsByTagName('givenkey');
  var selectedNotes = this.stateXML.getElementsByTagName('selectedkey');

  var i;
  var key, register, pitchClass;

  for (i = 0; i < givenNotes.length; i++) {
    register = parseInt(givenNotes[i].getAttribute('register'), 10);
    pitchClass = parseInt(givenNotes[i].getAttribute('pitchclass'), 10);
    key = this.keyboard.getKey(register, pitchClass);
    key.editable = false;
  }

  for (i = 0; i < selectedNotes.length; i++) {
    register = parseInt(selectedNotes[i].getAttribute('register'), 10);
    pitchClass = parseInt(selectedNotes[i].getAttribute('pitchclass'), 10);
    key = this.keyboard.getKey(register, pitchClass);
    key.selected = true;
    key.editable = true;
  }

};

/**
 * Returns the canvas state.
 *
 * @method getState
 * @return {String} The canvas state as xml.
 */
KeyboardInput.GUIState.State.prototype.getState = function() {

  var outXML = '<keyboardinput>\n';

  var givenKeys = this.keyboard.getKeys('givenkeys');
  var selectedKeys = this.keyboard.getKeys('selected');

  var i;
  outXML += '  <givenkeys>\n';
  for (i = 0; i < givenKeys.length; i++) {
    outXML += '    <givenkey pitchclass = "' + givenKeys[i].pitchClass +
        '" register = "' + givenKeys[i].register + '" />\n';
  }
  outXML += '  </givenkeys>\n';

  outXML += '  <selectedkeys>\n';
  for (i = 0; i < selectedKeys.length; i++) {
    outXML += '    <selectedkey pitchclass = "' + selectedKeys[i].pitchClass +
        '" register = "' + selectedKeys[i].register + '" />\n';
  }
  outXML += '  </selectedkeys>\n';

  outXML += '</keyboardinput>';

  return outXML;

};