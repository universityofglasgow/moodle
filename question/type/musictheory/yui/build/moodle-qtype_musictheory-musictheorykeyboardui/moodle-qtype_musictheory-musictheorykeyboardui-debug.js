YUI.add('moodle-qtype_musictheory-musictheorykeyboardui', function (Y, NAME) {

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package    qtype
 * @subpackage musictheory
 * @copyright  2014 Eric Brisson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

M.qtype_musictheory = M.qtype_musictheory || {};

M.qtype_musictheory.musictheorykeyboardui = {
  init: function() {
  }
};

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
 The Keyboard Input module allows for display and selection of notes on a piano
 keyboard.

 The user interface is created by instantiating a {{#crossLink "KeyboardInput"}}
 {{/crossLink}} object.

 Here is an example describing how to use this module:

 1) Add an HTMLtag for a small canvas in the web page source, with a unique 'id'
 attribute.

	<canvas id="myUniqueID" width="1" height="1"/>

 2) Define an XML string that will describe the interface's initial state. Here
 is an example:

	var initXML = '<keyboardinput maxkeys="2" canvasEditable="true">' +
	'<givenkeys>' +
	'<givenkey pitchclass="4" register="5" />' +
	'</givenkeys>' +
	'<selectedkeys>' +
	'<selectedkey pitchclass="5" register="2" />' +
	'</selectedkeys>' +
	'</keyboardinput>';

 The maxkeys attribute indicates the maximum number of keys that can be selected
 at any given time. The given keys are keys that will be highlighted on the
 keyboard but will not be selectable/unselectable. The selected keys are keys
 that will be initially selected - these keys can be unselected.

 For more details on the XML schema that the interface will accept and return,
 see the 'valid_input_xml.xsd' file in the module's XML directory.

 3) Define a callback function that the interface will use to message back its
 current state. This function will be called whenever the user changes the
 interface's state (i.e. by selecting or unselecting keys on the keyboard).

	var callBack = function(stateXML) {
	//stateXML will contain the most up-to-date canvas state.
	};

 4) Create a {{#crossLink "KeyboardInput"}}{{/crossLink}} object using the
 canvas id from step 1, the XML string from step 2 and the callback function
 from step 3:

	var musicCanvas = new KeyboardInput('myUniqueID', initXML, callBack, true);

 Argument 4 specifies whether the interface should be editable after
 initialization. Use a false value to display a static interface.

 Once instantiated, the KeyboardInput object will display an interactive
 keyboard on the web page. Whenever the user selects or unselects a key, the
 callback function is called and the most up-to-date canvas state is
 returned, as an XML string.

 @module KeyboardInput
 **/

/**
 The KeyboardInput object is used to interact with outside components.

 @class KeyboardInput
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
KeyboardInput = function(canID, stateXML, respCallBack, editable) {
  this.callBack = function(cBack) {
    return function(stateXML) {
      cBack(stateXML);
    };
  };

  this.UI = new KeyboardInput.Control.Controller(canID, stateXML,
      this.callBack(respCallBack), editable);
};/* Copyright (c) 2014 Eric Brisson

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

KeyboardInput.GUIState = {};/* Copyright (c) 2014 Eric Brisson

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

KeyboardInput.Render = {};/* Copyright (c) 2014 Eric Brisson

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
 * The Renderer object takes care of drawing all canvas elements.
 *
 * @class Renderer
 * @namespace Render
 *
 * @constructor
 * @param {Canvas} can An html5 canvas element.
 * @param {GUIState.State} state The canvas' state.
 * @param {Render.CoordManager} coordMgr The library's coordinate
 * manager.
 * @return {undefined}
 */
KeyboardInput.Render.Renderer = function(can, state, coordMgr) {

  this.can = can;
  this.state = state;
  this.coordMgr = coordMgr;

};

/**
 * Draws all canvas elements.
 *
 * @method draw
 * @return {undefined}
 */
KeyboardInput.Render.Renderer.prototype.draw = function() {
  var parent = this;
  var keyRend;

  // Draw white keys first
  Y.Array.each(this.state.keyboard.getKeys('white'), function(key) {
    keyRend = new KeyboardInput.Render.KeyRend(parent.can, parent.coordMgr,
        key);
    keyRend.draw();
  });

  // Black keys next
  Y.Array.each(this.state.keyboard.getKeys('black'), function(key) {
    keyRend = new KeyboardInput.Render.KeyRend(parent.can, parent.coordMgr,
        key);
    keyRend.draw();
  });


};/* Copyright (c) 2014 Eric Brisson

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

KeyboardInput.Control = {};/* Copyright (c) 2014 Eric Brisson

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

};/* Copyright (c) 2014 Eric Brisson

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
 * The CoordManager object provides canvas coordinates for rendering
 * purposes. It also takes care of converting mouse event coordinates into
 * canvas elements.
 *
 * @class CoordManager
 * @namespace Render
 *
 * @constructor
 * @param {GUIState.State} state The canvas state.
 * @return {undefined}
 */
KeyboardInput.Render.CoordManager = function(state) {

  this.state = state;
  this.CANVAS_PADDING = 15;
  this.WHITE_KEY_WIDTH = 14;
  this.WHITE_KEY_HEIGHT = 80;
  this.BLACK_KEY_WIDTH = 7;
  this.BLACK_KEY_HEIGHT = 60;
  this.CANVAS_WIDTH = this.CANVAS_PADDING * 2 + this.WHITE_KEY_WIDTH * 52;
  this.CANVAS_HEIGHT = this.CANVAS_PADDING * 2 + this.WHITE_KEY_HEIGHT;

};

/**
 * Given a mouse event location, determine if a canvas element (a keyboard key)
 * was involved, and if so, return the element.
 *
 * @method getElementFromPoint
 * @param {Object} point An object literal representing the location of the
 * mouse event, with properties x and y.
 * @return {Object} An object literal with a properties 'pitchClass' and
 * 'register'. Returns null if no element was involved.
 */
KeyboardInput.Render.CoordManager.prototype.getElementFromPoint = function(
    point) {
  if (this.isKeyboardPoint(point)) {
    return this.getKeyFromPoint(point);
  }
  else {
    return null;
  }
};

/**
 * Given a point, determine if the point is located within the keyboard on the
 * canvas.
 *
 * @method isKeyboardPoint
 * @param {Object} point An object literal representing the location of the
 * mouse event, with properties x and y.
 * @return {Boolean} Returns true if the point is located within the keyboard.
 */
KeyboardInput.Render.CoordManager.prototype.isKeyboardPoint = function(point) {
  var keybUL = {x: this.CANVAS_PADDING, y: this.CANVAS_PADDING};
  var keybLR = {x: this.CANVAS_PADDING + this.WHITE_KEY_WIDTH * 52,
    y: this.CANVAS_PADDING + this.WHITE_KEY_HEIGHT};

  if (this.pointWithin(point, keybUL, keybLR)) {
    return true;
  }
  else {
    return false;
  }
};

/**
 * Given a point known to be located within the keyboard, returns the key
 * that referred to by the point.
 *
 * @method getKeyFromPoint
 * @param {Object} point An object literal representing the location of the
 * mouse event, with properties x and y.
 * @return {Object} An object literal with a properties 'pitchClass' and
 * 'register'.
 */
KeyboardInput.Render.CoordManager.prototype.getKeyFromPoint = function(point) {
  var adjPointReg =
      {x: point.x - this.CANVAS_PADDING - this.WHITE_KEY_WIDTH * 2,
        y: point.y - this.CANVAS_PADDING};
  var register = Math.floor(adjPointReg.x / (this.WHITE_KEY_WIDTH * 7) + 1);


  var adjPointKey = {x: adjPointReg.x + -(register - 1) *
        (this.WHITE_KEY_WIDTH * 7),
    y: adjPointReg.y};


  var bKeyCenterIndices = [1, 2, 4, 5, 6];
  var i;

  for (i = 0; i < bKeyCenterIndices.length; i++) {
    var bKeyUL = {x: bKeyCenterIndices[i] * this.WHITE_KEY_WIDTH
          - this.BLACK_KEY_WIDTH / 2, y: 0};
    var bKeyLR = {x: bKeyCenterIndices[i] * this.WHITE_KEY_WIDTH
          + this.BLACK_KEY_WIDTH / 2,
      y: this.BLACK_KEY_HEIGHT};
    if (this.pointWithin(adjPointKey, bKeyUL, bKeyLR)) {
      switch (bKeyCenterIndices[i]) {
        case 1:
          if (register === 8) {
            return {register: register, pitchClass: 0};
          }
          return {register: register, pitchClass: 1};
        case 2:
          return {register: register, pitchClass: 3};
        case 4:
          return {register: register, pitchClass: 6};
        case 5:
          if (register === 0) {
            return {register: register, pitchClass: 9};
          }
          return {register: register, pitchClass: 8};
        case 6:
          return {register: register, pitchClass: 10};
      }
    }
  }

  var whiteKeyID = Math.floor(adjPointKey.x / this.WHITE_KEY_WIDTH) + 1;

  switch (whiteKeyID) {
    case 1:
      return {register: register, pitchClass: 0};
    case 2:
      if (register === 8) {
        return {register: register, pitchClass: 0};
      }
      return {register: register, pitchClass: 2};
    case 3:
      return {register: register, pitchClass: 4};
    case 4:
      return {register: register, pitchClass: 5};
    case 5:
      if (register === 0) {
        return {register: register, pitchClass: 9};
      }
      return {register: register, pitchClass: 7};
    case 6:
      return {register: register, pitchClass: 9};
    case 7:
      return {register: register, pitchClass: 11};

  }
};

/**
 * Given a point and a rectangle's upper left and lower right corners, determine
 * if the point lies within the rectangle.
 *
 * @method pointWithin
 * @param {Object} point An object literal representing the point, with
 * properties x and y.
 * @param {Object} llPoint An object literal representing the rectangle's upper
 * left corner, with properties x and y.
 * @param {Object} brPoint An object literal representing the rectangle's lower
 * right corner, with properties x and y.
 * @return {Boolean} Returns true if the point lies within the rectangle.
 */
KeyboardInput.Render.CoordManager.prototype.pointWithin = function(point,
    llPoint,
    brPoint) {

  if (point.x >= llPoint.x && point.x <= brPoint.x &&
      point.y >= llPoint.y && point.y <= brPoint.y) {
    return true;
  }
  else {
    return false;
  }

};/* Copyright (c) 2014 Eric Brisson

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
 * The Keyboard object stores the canvas state's keyboard.
 *
 * @class Keyboard
 * @namespace GUIState
 *
 * @constructor
 * @return {undefined}
 */
KeyboardInput.GUIState.Keyboard = function() {

  this.keys = [];
  this.maxKeys = 0;

  var i, j;

  for (i = 0; i <= 8; i++) {
    this.keys[i] = [];
  }
  this.keys[0][9] = new KeyboardInput.GUIState.Key(9, 0, true, false, false);
  this.keys[0][10] = new KeyboardInput.GUIState.Key(10, 0, true, false, false);
  this.keys[0][11] = new KeyboardInput.GUIState.Key(11, 0, true, false, false);
  for (i = 1; i <= 7; i++) {
    for (j = 0; j < 12; j++) {
      this.keys[i][j] = new KeyboardInput.GUIState.Key(j, i, true, false,
          false);
    }

  }
  this.keys[8][0] = new KeyboardInput.GUIState.Key(0, 8, true, false, false);
};

/**
 * Returns all keyboard keys or a subset of the keys.
 *
 * @method getKeys
 * @param {String} subset A parameter describing which keys should be returned
 * ('all', 'white', 'black', 'selected', 'givenkeys'). 'givenkeys' returns all
 * keys that are not editable.
 * @return {Array} An array of keyboard keys.
 */
KeyboardInput.GUIState.Keyboard.prototype.getKeys = function(subset) {
  var keys = [];
  Y.Array.each(this.keys, function(register) {
    Y.Array.each(register, function(key) {
      if (subset === 'all' ||
          (subset === 'white' && key.isWhiteKey) ||
          (subset === 'black' && !key.isWhiteKey) ||
          (subset === 'selected' && key.selected) ||
          (subset === 'givenkeys' && !key.editable)) {
        keys.push(key);
      }
    });
  });
  return keys;
};

/**
 * Returns a single keyboard key, given a pitchclass and a register.
 *
 * @method getKey
 * @param {Number} register The key's register.
 * @param {Number} pitchClass The key's pitchClass.
 * @return {GUIState.Key} The requested key.
 */
KeyboardInput.GUIState.Keyboard.prototype.getKey = function(register,
    pitchClass) {
  return this.keys[register][pitchClass];
};/* Copyright (c) 2014 Eric Brisson

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
 * The Key object represents a keyboard key.
 *
 * @class Key
 * @namespace GUIState
 *
 * @constructor
 * @param {Number} pitchClass The zero-based pitch class of the key, with C=0.
 * @param {Number} register The keys's register.
 * @param {Boolean} editable Indicates whether the key can be edited
 * after initialization.
 * @param {Boolean} selected Indicates whether the key is selected.
 * @param {Boolean} isGhost Indicates whether the key is to be
 * displayed as a ghost symbol.
 * @return {undefined}
 */
KeyboardInput.GUIState.Key = function(pitchClass, register, editable,
    selected, isGhost) {

  this.pitchClass = pitchClass;
  this.register = parseInt(register, 10);
  this.editable = editable;
  this.selected = selected;
  this.isGhost = isGhost;
  switch (pitchClass) {
    case 0:
    case 2:
    case 4:
    case 5:
    case 7:
    case 9:
    case 11:
      this.isWhiteKey = true;
      break;
    default:
      this.isWhiteKey = false;
  }

};

/**
 * Returns the zero-based subset ID of the key within the register, with C=0
 * for white keys and C#/Db=0 for black keys.
 *
 * For example, the subset ID of G is 4 (i.e. G is the 5th white key above C),
 * and the subset ID of F#/Gb is 2 (F#/Gb is the 3rd black key above C#/Db).
 *
 * @method getSubsetIDWithinRegister
 * @return {Number} The key's subset ID within the register.
 */
KeyboardInput.GUIState.Key.prototype.getSubsetIDWithinRegister = function() {
  if (this.isWhiteKey) {
    switch (this.pitchClass) {
      case 0:
        return 0;
      case 2:
        return 1;
      case 4:
        return 2;
      case 5:
        return 3;
      case 7:
        return 4;
      case 9:
        return 5;
      case 11:
        return 6;
      default:
        return null;
    }
  }
  else {
    switch (this.pitchClass) {
      case 1:
        return 0;
      case 3:
        return 1;
      case 6:
        return 2;
      case 8:
        return 3;
      case 10:
        return 4;
      default:
        return null;
    }
  }
};/* Copyright (c) 2014 Eric Brisson

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

};/* Copyright (c) 2014 Eric Brisson

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
 * The KeyRend object takes care of drawing a key on the keyboard.
 *
 * @class KeyRend
 * @namespace Render
 *
 * @constructor
 * @param {Canvas} can An html5 canvas element.
 * @param {Render.CoordManager} coordMgr The library's coordinate
 * manager.
 * @param {GUIState.Key} key The key to be rendered.
 * @return {undefined}
 */
KeyboardInput.Render.KeyRend = function(can, coordMgr, key) {

  this.can = can;
  this.coordMgr = coordMgr;
  this.key = key;

};

/**
 * Draws the key on the keyboard.
 *
 * @method draw
 * @return {undefined}
 */
KeyboardInput.Render.KeyRend.prototype.draw = function() {

  var context = this.can.getContext('2d');
  var keybLeftAdj = 5 * this.coordMgr.WHITE_KEY_WIDTH;

  if (this.key.isWhiteKey) {
    var whiteKeyX = this.key.register * (7 * this.coordMgr.WHITE_KEY_WIDTH) +
        this.key.getSubsetIDWithinRegister() * this.coordMgr.WHITE_KEY_WIDTH;

    context.beginPath();
    context.rect(this.coordMgr.CANVAS_PADDING + whiteKeyX - keybLeftAdj,
        this.coordMgr.CANVAS_PADDING,
        this.coordMgr.WHITE_KEY_WIDTH,
        this.coordMgr.WHITE_KEY_HEIGHT);
    if (!this.key.editable) {
      context.fillStyle = 'red';
    }
    else if (this.key.selected) {
      context.fillStyle = '#415ded';
    }
    else if (this.key.isGhost) {
      context.fillStyle = 'grey';
    }
    else {
      context.fillStyle = 'white';
    }
    context.fill();
    context.lineWidth = 1;
    context.strokeStyle = 'black';
    context.stroke();
  }
  else {
    var blackKeyXWithinReg;
    switch (this.key.getSubsetIDWithinRegister()) {
      case 0:
        blackKeyXWithinReg = 1 * this.coordMgr.WHITE_KEY_WIDTH -
            this.coordMgr.BLACK_KEY_WIDTH / 2;
        break;
      case 1:
        blackKeyXWithinReg = 2 * this.coordMgr.WHITE_KEY_WIDTH -
            this.coordMgr.BLACK_KEY_WIDTH / 2;
        break;
      case 2:
        blackKeyXWithinReg = 4 * this.coordMgr.WHITE_KEY_WIDTH -
            this.coordMgr.BLACK_KEY_WIDTH / 2;
        break;
      case 3:
        blackKeyXWithinReg = 5 * this.coordMgr.WHITE_KEY_WIDTH -
            this.coordMgr.BLACK_KEY_WIDTH / 2;
        break;
      case 4:
        blackKeyXWithinReg = 6 * this.coordMgr.WHITE_KEY_WIDTH -
            this.coordMgr.BLACK_KEY_WIDTH / 2;
        break;
      default:
        blackKeyXWithinReg = 0;
    }

    var blackKeyX = this.key.register * (7 * this.coordMgr.WHITE_KEY_WIDTH) +
        blackKeyXWithinReg;

    context.beginPath();
    context.rect(this.coordMgr.CANVAS_PADDING + blackKeyX - keybLeftAdj,
        this.coordMgr.CANVAS_PADDING,
        this.coordMgr.BLACK_KEY_WIDTH,
        this.coordMgr.BLACK_KEY_HEIGHT);
    if (!this.key.editable) {
      context.fillStyle = 'red';
    }
    else if (this.key.selected) {
      context.fillStyle = '#415ded';
    }
    else if (this.key.isGhost) {
      context.fillStyle = 'grey';
    }
    else {
      context.fillStyle = 'black';
    }
    context.fill();
    context.lineWidth = 1;
    context.strokeStyle = 'black';
    context.stroke();

  }

};

}, '@VERSION@', {"requires": ["base", "node", "datatype"]});
