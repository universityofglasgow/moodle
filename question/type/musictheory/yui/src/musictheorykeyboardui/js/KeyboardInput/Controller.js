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
 * The Controller object instantiates the canvas and handles mouse
 * events.
 *
 * @class Controller
 * @namespace Control
 *
 * @constructor
 * @param {String} canID The id of the HTMl 5 canvas to which this application
 * will be bound.
 * @param {String} stateXML The canvas state as xml.
 * @param {Function} callBackFunc A function that the controller will use
 * to send the current canvas state back the caller, whenever the state changes.
 * This function should accept one argument which provides the current state of
 * the canvas, as an xml string.
 * @return {undefined}
 */
KeyboardInput.Control.Controller = function(canID, stateXML, callBackFunc) {

  this.canID = canID;
  this.canNode = Y.one('#' + canID);
  this.html5Can = this.canNode.getDOMNode();
  this.ctx = this.html5Can.getContext('2d');
  this.stateXML = Y.XML.parse(stateXML);
  this.state = new KeyboardInput.GUIState.State();
  this.state.setState(this.stateXML);
  this.coordMgr = new KeyboardInput.Render.CoordManager(this.state);
  this.html5Can.width = this.coordMgr.CANVAS_WIDTH;
  this.html5Can.height = this.coordMgr.CANVAS_HEIGHT;
  this.callBack = callBackFunc;
  this.bindEventListeners();
  this.ghostKey = null;
  this.renderer = new KeyboardInput.Render.Renderer(this.html5Can, this.state,
      this.coordMgr);
  this.drawState();

};

/**
 * Translates the window-based point location of a mouse event into a
 * canvas-based location.
 *
 * @method getLocalCoord
 * @param {Event} e The mouse event.
 * @return {Object} An object literal with properties x and y, representing
 * the canvas based location.
 */
KeyboardInput.Control.Controller.prototype.getLocalCoord = function(e) {

  var canOrigin = {x: this.canNode.getXY()[0],
    y: this.canNode.getXY()[1]};
  return {x: e.pageX - canOrigin.x, y: e.pageY - canOrigin.y};

};

/**
 * Binds listener functions to mouse events.
 *
 * @method bindEventListeners
 * @return {undefined}
 */
KeyboardInput.Control.Controller.prototype.bindEventListeners = function() {

  if (!(this.state.editable)) {
    return;
  }

  var parent = this;

  this.canNode.detachAll();

  this.canNode.on('click', function(e) {
    var coord = parent.getLocalCoord(e);
    parent.onMouseClick({x: coord.x, y: coord.y});
  });

  this.canNode.on('mousemove', function(e) {
    var coord = parent.getLocalCoord(e);
    parent.onMouseMove({x: coord.x, y: coord.y});
  });

  this.canNode.on('mouseleave', function() {
    parent.removeGhostKey();
    parent.drawState();
  });

};

/**
 * Handles a canvas mouse click event.
 *
 * @method onMouseClick
 * @param {Object} point An object literal representing the location of the
 * mouse click, with properties x and y.
 * @return {undefined}
 */
KeyboardInput.Control.Controller.prototype.onMouseClick = function(point) {

  var elem = this.coordMgr.getElementFromPoint(point);
  if (elem !== null) {
    var key = this.state.keyboard.getKey(elem.register, elem.pitchClass);
    if (key.editable) {
      var numSelectedKeys = this.state.keyboard.getKeys('selected').length;
      if (!key.selected && numSelectedKeys < this.state.keyboard.maxKeys) {
        key.selected = true;
        this.drawState();
        this.callBack(this.state.getState());
      }
      else if (key.selected) {
        key.selected = false;
        this.drawState();
        this.callBack(this.state.getState());
      }

    }
  }
};

/**
 * Handles a canvas mouse move event.
 *
 * @method onMouseMove
 * @param {Object} point An object literal representing the location of the
 * mouseover event, with properties x and y.
 * @return {undefined}
 */
KeyboardInput.Control.Controller.prototype.onMouseMove = function(point) {

  var elem = this.coordMgr.getElementFromPoint(point);
  if (elem !== null) {
    var key = this.state.keyboard.getKey(elem.register, elem.pitchClass);
    if (!key.selected) {
      if (this.ghostKey !== null) {
        this.ghostKey.isGhost = false;

      }
      key.isGhost = true;
      this.ghostKey = key;
      this.drawState();
    }
  }
  else {
    this.removeGhostKey();
    this.drawState();
  }
};

/**
 * Deletes the current ghost key (if displayed) from the canvas state.
 *
 * @method removeGhostKey
 * @return {undefined}
 */
KeyboardInput.Control.Controller.prototype.removeGhostKey = function() {

  if (this.ghostKey !== null && typeof(this.ghostKey !== 'undefined')) {
    this.ghostKey.isGhost = false;
    this.ghostKey = null;
  }
};

/**
 * Draws the canvas state.
 *
 * @method drawState
 * @return {undefined}
 */
KeyboardInput.Control.Controller.prototype.drawState = function() {

  if (typeof(this.renderer) !== 'undefined') {
    if (this.renderer !== null) {
      this.ctx.fillStyle = 'white';
      this.ctx.fillRect(0, 0, this.coordMgr.canvasWidth,
          this.coordMgr.canvasHeight);
      this.renderer.draw();
    }
  }

};