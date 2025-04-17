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
 * The Toolbar object represents canvas toolbars.
 *
 * @class Toolbar
 * @namespace GUIState
 *
 * @constructor
 * @param {String} name The name of the toolbar ('acc' for an accidental
 * toolbar, 'noteVal' for a note value  toolbar).
 * @return {undefined}
 */
MusThGUI.GUIState.Toolbar = function(name) {

  this.buttons = [];
  this.name = name;

};

/**
 * Adds a button to the toolbar.
 *
 * @method addButton
 * @param {GUIState.ToolbarBtn} btn The button to be added.
 * @return {undefined}
 */
MusThGUI.GUIState.Toolbar.prototype.addButton = function(btn) {

  this.buttons.push(btn);

};

/**
 * Set a toolbar button as selected. Only one button may be selected at
 * any one time.
 *
 * @method select
 * @param {Number} btnID The zero-based position of the button to be selected.
 * @return {undefined}
 */
MusThGUI.GUIState.Toolbar.prototype.select = function(btnID) {

  var i;
  for (i = 0; i < this.buttons.length; i++) {
    var button = this.buttons[i];
    if (i === btnID) {
      button.selected = true;
    }
    else {
      button.selected = false;
    }
  }

};

/**
 * Enables the mouseOverlay property of a button in the toolbar.
 *
 * @method setMouseOverlay
 * @param {Number} btnID The zero-based position of the button to be displayed
 * with an overlay. If this argument is null, the mouseOverlay property is
 * disabled for all buttons in the toolbar.
 * @return {undefined}
 */
MusThGUI.GUIState.Toolbar.prototype.setMouseOverlay = function(btnID) {

  var i;
  for (i = 0; i < this.buttons.length; i++) {
    var button = this.buttons[i];
    if (btnID !== null) {
      if (i === btnID) {
        button.mouseOverlay = true;
      }
    }
    else {
      button.mouseOverlay = false;
    }
  }

};

/**
 * Returns the symbol of the button currently selected in the toolbar.
 *
 * @method selectedSymbol
 * @return {String}
 */
MusThGUI.GUIState.Toolbar.prototype.selectedSymbol = function() {

  var i;
  for (i = 0; i < this.buttons.length; i++) {
    if (this.buttons[i].selected === true) {
      return this.buttons[i].symbol;
    }
  }

};

/**
 * Returns the zero-based position of the button currently selected in the
 * toolbar.
 *
 * @method selectedID
 * @return {Number}
 */
MusThGUI.GUIState.Toolbar.prototype.selectedID = function() {

  var i;
  for (i = 0; i < this.buttons.length; i++) {
    if (this.buttons[i].selected === true) {
      return i;
    }
  }

};