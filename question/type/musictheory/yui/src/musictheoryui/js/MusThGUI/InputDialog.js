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
 * The InputDialog object represents a javascript dialog used to
 * enter an answer in one of the canvas' text input. The use of this dialog
 * allows the use of form elements to format the answer (in particular, the use
 * of select elements).
 *
 * @class InputDialog
 * @namespace Control
 *
 * @constructor
 * @param {Control.Controller} controller The library's controller
 * object.
 * @param {XMLDoc} stateXML The canvas state as xml. The xml specifies the
 * html code to include in the dialog.
 * @return {undefined}
 */
MusThGUI.Control.InputDialog = function(controller, stateXML) {

  this.controller = controller;
  this.parsedXML = stateXML;
  this.panel = null;
  this.colID = null;
  this.dialogNode = this.parsedXML.getElementsByTagName('InputDialog')[0];

  if (typeof(this.dialogNode) !== 'undefined' && this.dialogNode !== null) {
    this.formDivContentXML = this.dialogNode.getElementsByTagName('div')[0];
    this.dialogID = this.formDivContentXML.getAttribute('id');
    this.width = this.dialogNode.getAttribute('width');
    this.height = this.dialogNode.getAttribute('height');
    this.btnTxtOK = this.dialogNode.getAttribute('OKButtonText');
    this.btnTxtCancel = this.dialogNode.getAttribute('cancelButtonText');
  }
};

/**
 * Initializes the input dialog.
 *
 * @method init
 * @return {undefined}
 */
MusThGUI.Control.InputDialog.prototype.init = function() {

  Y.one('body').addClass('yui3-skin-sam');

  var parent = this;
  this.panel = new Y.Panel({
    bodyContent: Y.XML.format(this.formDivContentXML),
    headerContent: this.dialogNode.getAttribute('headerContent'),
    zIndex: 6,
    centered: true,
    modal: true,
    visible: false,
    render: true,
    plugins: [Y.Plugin.Drag],
    buttons: [
      {
        value: this.btnTxtOK,
        section: Y.WidgetStdMod.FOOTER,
        action: function(e) {
          e.preventDefault();
          var combinedInput = '';
          var divNode = parent.Y.one('#' + parent.dialogID);
          var selectElems = divNode.all('select');
          selectElems.each(function(selectElem) {
            combinedInput += selectElem.get('value');
          });
          parent.callBack(combinedInput);
          this.hide();
        }
      },
      {
        value: this.btnTxtCancel,
        section: Y.WidgetStdMod.FOOTER,
        action: function(e) {
          e.preventDefault();
          this.hide();
        }
      }
    ]
  });

};

/**
 * Displays the input dialog.
 *
 * @method show
 * @return {undefined}
 */
MusThGUI.Control.InputDialog.prototype.show = function() {
  if (typeof(this.panel) === 'undefined') {
    this.init();
  }
  this.resetDialogForm();
  this.panel.show();
};

/**
 * Resets all form elements to empty values.
 *
 * @method resetDialogForm
 * @return {undefined}
 */
MusThGUI.Control.InputDialog.prototype.resetDialogForm = function() {
  var divNode = Y.one('#' + this.dialogID);
  var selectElems = divNode.all('select');
  selectElems.each(function(selectElem) {
    selectElem.set('value', '');
  });
};

/**
 * Resets all form elements to empty values.
 *
 * @method callBack
 * @param {String} combinedInput The form's input, to be sent back to the
 * controller object passed as argument to the constructor.
 * @return {undefined}
 */
MusThGUI.Control.InputDialog.prototype.callBack = function(combinedInput) {

  this.controller.setColTextInput(this.colID, combinedInput);

};