/**
 * MIT License
 * Copyright (c) 2020 JSXGraph
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

/**
 * This is a plugin to enable function plotting and dynamic geometry constructions with JSXGraph
 * within a formulas question of Moodle platform.
 *
 * JSXGraph is a cross-browser JavaScript library for interactive geometry,
 * function plotting, charting, and data visualization in the web browser.
 * JSXGraph is implemented in pure JavaScript and does not rely on any other
 * library. Special care has been taken to optimize the performance.
 *
 * @package    filter_jsxgraph
 * @copyright  2023 JSXGraph team - Center for Mobile Learning with Digital Technology – Universität Bayreuth
 *             Andreas Walter <andreas.walter@uni-bayreuth.de>,
 *             Alfred Wassermann <alfred.wassermann@uni-bayreuth.de>
 *             based on work by Tim Kos and Marc Bernat
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

'use strict';

// For code prechecks:
var JXG = JXG || {};

/**
 * @param {String|String[]} boardIDs ID of the HTML element containing the JSXGraph board. Has to be set with local const BOARDID0.
 *                                   If more than one board is used, an array of BOARDIDS must be given.
 * @param {Function} jsxGraphCode JavaScript function containing the construction code.
 * @param {Boolean} [allowInputEntry=false] Should the original inputs from formulas be displayed and linked to the construction?
 * @param {Number} [decimalPrecision=2] Number of digits to round to.
 */
var JSXQuestion = function(boardIDs, jsxGraphCode, allowInputEntry, decimalPrecision) {
    var that = this,
        topEl;

    if (allowInputEntry === undefined || allowInputEntry === null) {
        allowInputEntry = false;
    }
    if (decimalPrecision === undefined || decimalPrecision === null) {
        decimalPrecision = 2;
    }

    /**
     * Array with the IDs of the boards.
     * @type {String[]}
     */
    this.BOARDIDS = boardIDs;

    if (!JXG.isArray(boardIDs)) {
        this.BOARDIDS = [boardIDs];
    }

    /**
     * ID of the first board.
     * @type {String}
     */
    this.firstBOARDID = this.BOARDIDS[0];

    /**
     * ID of the *first* board.
     * @type {String}
     * @deprecated use array {@see BOARDIDS} instead
     */
    this.BOARDID = this.firstBOARDID;

    /**
     * HTML element containing the board.
     * @deprecated
     * @type {HTMLElement}
     */
    this.elm = document.getElementById(this.firstBOARDID);

    // Get the first ancestor of the board having class ".formulaspart" (should be a div).
    // ATTENTION!!! The class used here depends on formulas and can make the extension useless when updating formulas!
    topEl = this.elm.closest('.formulaspart');

    /**
     * Stores the input tags from the formulas question.
     * @type {HTMLElement[]}
     */
    this.inputs = topEl.querySelectorAll('input');

    // Hide the input elements
    if (allowInputEntry) {
        this.inputs.forEach(function(el) {
            el.addEventListener('input', function() {
                if (!that.board.inUpdate) {
                    that.update();
                }
            });
        });
        this.inputs.forEach(function(el) {
            el.addEventListener('change', function() {
                if (!that.board.inUpdate) {
                    that.update();
                }
            });
        });
    } else {
        this.inputs.forEach(function(el) {
            el.style.display = 'none';
        });
    }

    if (Object.defineProperties) {
        var boards = [];

        Object.defineProperties(this, {
            boards: {
                'default': [],
                get: function() {
                    return boards;
                },
                set: function(value) {
                    if (JXG.isArray(value)) {
                        boards = value;
                    } else {
                        boards = [value];
                    }
                },
            },

            firstBoard: {
                'default': null,
                get: function() {
                    return boards[0];
                },
                set: function(value) {
                    boards = [value];
                },
            },

            board: {
                'default': null,
                get: function() {
                    return boards[0];
                },
                set: function(value) {
                    boards = [value];
                },
            },

            brd: {
                'default': null,
                get: function() {
                    return boards[0];
                },
                set: function(value) {
                    boards = [value];
                },
            },
        });

    } else {

        /**
         * Array with the stored JSXGraph boards.
         * @type {JXG.Board[]}
         */
        this.boards = [];

        /**
         * Stored *first* JSXGraph board.
         * @type {JXG.Board}
         */
        this.firstBoard = null;

        /**
         * Stored *first* JSXGraph board.
         * @type {JXG.Board}
         */
        this.board = this.firstBoard;
        /**
         * @type {JXG.Board}
         * @deprecated use attribute {@see board} instead
         */
        this.brd = this.firstBoard;

    }

    /**
     * Alias for function initBoards and initAndAddBoard. Returns only the first board (for backward compatibility).
     * @see JSXQuestion.initBoards
     * @see JSXQuestion.initAndAddBoard
     *
     * @param {String|Object|Object[]} firstParam   Attributes for function JXG.JSXGraph.initBoard(...).
     *                                              Can also be an array. Otherwise each board gets the same attributes.
     * @param {Object|Object[]} secondParam         Guarantees compatibility with the function JXG.JSXGraph.initBoard(...).
     *                                              The ID that was then passed in the first parameter is ignored!
     *                                              Can also be an array. Otherwise each board gets the same attributes.
     *
     * @returns {JXG.Board}                         JSXGraph board
     */
    this.initBoard = function(firstParam, secondParam) {
        if (JXG.isString(firstParam)) { /* Parameter firstParam is id. */
            return that.initAndAddBoard(firstParam, secondParam);
        } else /* Parameter firstParam are attributes. */ if (!JXG.isArray(firstParam)) {
            return that.initAndAddBoard(that.BOARDIDS[that.boards.length], firstParam);
        } else {
            return that.initBoards(firstParam, secondParam)[0];
        }
    };

    /**
     * Initializes the board(s), saves it/them in the attributes of JSXQuestion and returns an array of boards.
     *
     * @param {Object|Object[]} [attributes={}]             Attributes for function JXG.JSXGraph.initBoard(...).
     *                                                      Can also be an array. Otherwise each board gets the same attributes.
     * @param {Object|Object[]} [attributesIfBoxIsGiven={}] Guarantees compatibility with the function JXG.JSXGraph.initBoard(...).
     *                                                      The ID that was then passed in the first parameter is ignored!
     *                                                      Can also be an array. Otherwise each board gets the same attributes.
     *
     * @returns {JXG.Board[]}                               JSXGraph board
     */
    this.initBoards = function(attributes, attributesIfBoxIsGiven) {
        var board, attr, i;

        if (attributes === undefined || attributes === null) {
            attributes = {};
        }
        if (attributesIfBoxIsGiven === undefined || attributesIfBoxIsGiven === null) {
            attributesIfBoxIsGiven = {};
        }
        if (JXG.isString(attributes)) { // Backward compatibility!
            attributes = attributesIfBoxIsGiven;
        }

        if (!JXG.isArray(attributes)) {
            attributes = [attributes];
        }
        // From here attributes is an array.

        for (i = 0; i < that.boards.length; i++) {
            JXG.JSXGraph.freeBoard(that.boards[i]);
        }
        that.boards = [];

        for (i = 0; i < that.BOARDIDS.length; i++) {
            attr = attributes[i] || attributes[0]; // First attributes are default.
            attr.id = attr.id ?? that.BOARDIDS[i] + '_board';
            board = JXG.JSXGraph.initBoard(that.BOARDIDS[i], attr);
            that.boards.push(board);
        }

        return that.boards;
    };

    /**
     * Initialize and add one board to the boards array. Returns the just added board.
     *
     * @param {String} id              Id of the board.
     * @param {Object} [attributes={}] Attributes for function JXG.JSXGraph.initBoard(...).
     *
     * @returns {JXG.Board}            JSXGraph board
     */
    this.initAndAddBoard = function(id, attributes) {
        var index = that.BOARDIDS.indexOf(id);

        if (index === -1) {
            throw new Error('The id "' + id + '" was not passed in Initialization of JSXQuestion.');
        }

        if (attributes === undefined || attributes === null) {
            attributes = {};
        }

        if (JXG.exists(that.boards[index])) {
            JXG.JSXGraph.freeBoard(that.boards[index]);
        }

        attributes.id = attributes.id ?? that.BOARDIDS[index] + '_board';
        that.boards[index] = JXG.JSXGraph.initBoard(that.BOARDIDS[index], attributes);
        return that.boards[index];
    };

    /**
     * Calls the function addChild ascending for each board.
     * After this function boards[0] is child of boards[1], boards[1] is child of boards[2] etc.
     */
    this.addChildsAsc = function() {
        var i;

        for (i = that.boards.length - 1; i > 0; i--) {
            that.boards[i].addChild(that.boards[i - 1]);
        }
    };

    /**
     * Calls the function addChild descending for each board.
     * After this function boards[0] is parent of boards[1], boards[1] is parent of boards[2] etc.
     */
    this.addChildsDesc = function() {
        var i;

        for (i = 0; i < that.boards.length - 1; i++) {
            that.boards[i].addChild(that.boards[i + 1]);
        }
    };

    /**
     * Links the board to the inputs. If a change has been made in the board,
     * the input with the number inputNumber is assigned the value that the function valueFunction returns.
     *
     * @param {Number} inputNumber
     * @param {Function} valueFunction
     */
    this.bindInput = function(inputNumber, valueFunction) {
        var i;

        for (i = 0; i < that.boards.length; i++) {
            that.boards[i].on('up', function() {
                that.set(inputNumber, valueFunction());
            });
            that.boards[i].update();
            that.boards[i].triggerEventHandlers(['up'], []);
        }
    };

    /**
     * Indicator if the question has been solved.
     * @type {Boolean}
     */
    this.isSolved = false;
    if (this.inputs && this.inputs[0]) {
        this.isSolved = this.inputs[0].readOnly;
    }
    /**
     * @deprecated use attribute {@see isSolved} instead
     * @type {Boolean}
     */
    this.solved = this.isSolved;

    /**
     * Fill input element of index inputNumber of the formulas question with value.
     *
     * @param {Number} inputNumber Index of the input element, starting at 0.
     * @param {Number} value  Number to be set.
     */
    this.set = function(inputNumber, value) {
        if (!that.isSolved && that.inputs && that.inputs[inputNumber]) {
            that.inputs[inputNumber].value = Math.round(value * Math.pow(10, decimalPrecision)) / Math.pow(10, decimalPrecision);
        }
    };

    /**
     * Set values for all formulas input fields
     *
     * @param {Number[]} values Array containing the numbers to be set.
     */
    this.setAllValues = function(values) {
        var inputNumber,
            len = values.length;

        for (inputNumber = 0; inputNumber < len; inputNumber++) {
            that.set(inputNumber, values[inputNumber]);
        }
    };

    /**
     * Get the content of input element of index inputNumber of the formulas question.
     *
     * @param {Number} inputNumber Index of the input form, starting at 0.
     * @param {Number} [defaultValue=0] Number that is returned if the value of the input could not be read or is not a number.
     *
     * @returns {Number} Entry of the formulas input field.
     */
    this.get = function(inputNumber, defaultValue) {
        var n;

        if (defaultValue === undefined || defaultValue === null) {
            defaultValue = 0;
        }

        if (that.inputs && that.inputs[inputNumber]) {
            n = parseFloat(that.inputs[inputNumber].value);
            if (!isNaN(n)) {
                return Math.round(n * Math.pow(10, decimalPrecision)) / Math.pow(10, decimalPrecision);
            }
        }
        return defaultValue;
    };

    /**
     * Fetch all values from the formulas input fields.
     *
     * @param {Number|Number[]} [defaultValues=0] Default values if the fields are empty.
     *
     * @returns {Number[]} Array containing the entries of all associated formulas input fields.
     */
    this.getAllValues = function(defaultValues) {
        var inputNumber,
            len = that.inputs.length,
            values = [],
            defaultValue;

        if (defaultValues === undefined || defaultValues === null) {
            defaultValues = 0;
        }

        if (Array.isArray(defaultValues)) {
            if (defaultValues.length !== len) {
                return null;
            }
        } else {
            if (isNaN(defaultValues)) {
                return null;
            } else {
                defaultValue = defaultValues;
            }
        }

        for (inputNumber = 0; inputNumber < len; inputNumber++) {
            values.push(
                that.get(inputNumber, defaultValue || defaultValues[inputNumber])
            );
        }
        return values;
    };

    /**
     * Reload the construction.
     */
    this.reload = this.update = function() {
        var i;
        for (i = 0; i < that.boards.length; i++) {
            that.boards[i].update();
        }
        jsxGraphCode(that);
    };

    // Execute the JSXGraph JavaScript code.
    this.update();
};

JSXQuestion.toString = function() {
    return JSXQuestion.name;
};

// Polyfill for element.closest:
if (!Element.prototype.matches) {
    Element.prototype.matches = Element.prototype.msMatchesSelector ||
        Element.prototype.webkitMatchesSelector;
}

if (!Element.prototype.closest) {
    Element.prototype.closest = function(s) {
        var el = this;

        do {
            if (Element.prototype.matches.call(el, s)) {
                return el;
            }
            el = el.parentElement || el.parentNode;
        } while (el !== null && el.nodeType === 1);
        return null;
    };
}