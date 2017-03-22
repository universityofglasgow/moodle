YUI.add('moodle-atto_pastespecial-button', function (Y, NAME) {

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
 * @package    atto_pastespecial
 * @copyright  2015 Joseph Inhofer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @module  moodl-atto_pastespecial-button
 */

/**
 * Atto text editor pastespecial plugin
 *
 * @namespace M.atto_pastespecial
 * @class button
 * @extends M.editor_atto.EditorPlugin
 */

var COMPONENTNAME = 'atto_pastespecial',
    CSS = {
        PASTEAREA: 'atto_pastespecial_pastearea',
        PASTEFROMWORD: 'atto_pastespecial_pastefromword',
        PASTEFROMGDOC: 'atto_pastespecial_pastefromgdoc',
        PASTEFROMLIBRE: 'atto_pastespecial_pastefromlibre',
        PASTEFROMOTHER: 'atto_pastespecial_pastefromother',
        PASTEUNFORMATTED: 'atto_pastespecial_pasteunformatted',
        PASTESTRAIGHT: 'atto_pastespecial_pastestraight',
        IFRAME: 'atto_pastespecial_iframe',
        IFRAME_VIEW: 'atto_pastespecial_iframe_view'
    },
    SELECTORS = {
        PASTEAREA: '.atto_pastespecial_pastearea',
        PASTEFROMWORD: '.atto_pastespecial_pastefromword',
        PASTEFROMGDOC: '.atto_pastespecial_pastefromgdoc',
        PASTEFROMLIBRE: '.atto_pastespecial_pastefromlibre',
        PASTEFROMOTHER: '.atto_pastespecial_pastefromother',
        PASTEUNFORMATTED: '.atto_pastespecial_pasteunformatted',
        PASTESTRAIGHT: '.atto_pastespecial_pastestraight',
        IFRAME: '.atto_pastespecial_iframe',
        IFRAMEID: '#atto_pastespecial_iframe',
        IFRAME_VIEW: '.atto_pastespecial_iframe_view'
    },
    STYLES = {
        GDOC: ['background-color',
               'color',
               'font-family',
               'font-size',
               'font-weight',
               'font-style',
               'text-decoration',
               'list-style-type',
               'text-align'],
        LIBRE: ['background',
                'color',
                'font-size'],
        WORD: ['font-family',
               'font-size',
               'background',
               'color',
               'background-color']
    },
    TEMPLATE = '' +
        '<form class="atto_pastespecial_form_{{elementid}} atto_form atto_pastespecial_form">' +
            '<div class="atto_pastespecial_contenteditable atto_pastespecial_preview">' +
                '<label for="{{elementid}}_{{CSS.IFRAME}}" class="atto_pastespecial_helptext">' +
                    '{{get_string "pastehere" component}}' +
                '</label>' +
                '<div id="{{elementid}}_{{CSS.IFRAME}}" class="{{CSS.IFRAME}}" contentEditable="true"></div>' +
            '</div>' +
            '<div class="atto_pastespecial_contenteditable atto_pastespecial_handled">' +
                '<label for="{{elementid}}_{{CSS.IFRAME_VIEW}}" class="atto_pastespecial_helptext">' +
                    '{{get_string "pasteview" component}}' +
                '</label>' +
                '<div id="{{elementid}}_{{CSS.IFRAME_VIEW}}" class="{{CSS.IFRAME_VIEW}}" contentEditable="true"></div>' +
            '</div>' +
            '<div class="atto_pastespecial_radios">' +
                '<div class="atto_pastespecial_helptext">{{get_string "step2" component}}</div>' +
                '{{#if straight}}' +
                    '<input type="radio" class="{{CSS.PASTESTRAIGHT}}" name="from"' +
                    'id="{{elementid}}_{{CSS.PASTESTRAIGHT}}_1" checked/>' +
                    '<label for="{{elementid}}_{{CSS.PASTESTRAIGHT}}_1">{{get_string "pastefrommoodle" component}}</label>' +
                    '<br>' +
                    '<input type="radio" class="{{CSS.PASTEFROMWORD}}" name="from" id="{{elementid}}_{{CSS.PASTEFROMWORD}}"/>' +
                '{{/if}}' +
                '{{#if word}}' +
                    '<input type="radio" class="{{CSS.PASTEFROMWORD}}" name="from"' +
                    'id="{{elementid}}_{{CSS.PASTEFROMWORD}}"checked />' +
                '{{/if}}' +
                '<label for="{{elementid}}_{{CSS.PASTEFROMWORD}}">{{get_string "pastefromword" component}}</label>' +
                '<br>' +
                '<input type="radio" class="{{CSS.PASTEFROMGDOC}}" name="from" id="{{elementid}}_{{CSS.PASTEFROMGDOC}}"/>' +
                '<label for="{{elementid}}_{{CSS.PASTEFROMGDOC}}">{{get_string "pastefromgdoc" component}}</label>' +
                '<br>' +
                '<input type="radio" class="{{CSS.PASTEFROMLIBRE}}" name="from" id="{{elementid}}_{{CSS.PASTEFROMLIBRE}}"/>' +
                '<label for="{{elementid}}_{{CSS.PASTEFROMLIBRE}}">{{get_string "pastefromlibre" component}}</label>' +
                '<br>' +
                '<input type="radio" class="{{CSS.PASTEFROMOTHER}}" name="from" id="{{elementid}}_{{CSS.PASTEFROMOTHER}}"/>' +
                '<label for="{{elementid}}_{{CSS.PASTEFROMOTHER}}">{{get_string "pastefromother" component}}</label>' +
                '<br>' +
                '<input type="radio" class="{{CSS.PASTEUNFORMATTED}}" name="from" id="{{elementid}}_{{CSS.PASTEUNFORMATTED}}"/>' +
                '<label for="{{elementid}}_{{CSS.PASTEUNFORMATTED}}">{{get_string "pasteunformatted" component}}</label>' +
                '{{#if straight}}' +
                    '<br>' +
                    '<input type="radio" class="{{CSS.PASTESTRAIGHT}}" name="from" id="{{elementid}}_{{CSS.PASTESTRAIGHT}}"/>' +
                    '<label for="{{elementid}}_{{CSS.PASTESTRAIGHT}}">{{get_string "pastestraight" component}}</label>' +
                '{{/if}}' +
            '</div>' +
            '<div class="atto_pastespecial_help hidden">{{get_string "help_text" component}}</div>' +
            '<div class="mdl-align atto_pastespecial_button">' +
                '<p class="atto_pastespecial_helptext">{{get_string "clickthebutton" component}}</p>' +
                '<button value="Help" type="button" class="help">{{get_string "help" component}}</button>' +
                '<button value="Paste" type="submit" class="submit">{{get_string "paste" component}}</button>' +
                '<br>' +
                '<button value="Cancel" type="button" class="cancel">{{get_string "cancel" component}}</button>' +
            '</div>' +
        '</form>';
Y.namespace('M.atto_pastespecial').Button = Y.Base.create('button', Y.M.editor_atto.EditorPlugin, [], {

    // Will become the content to be loaded.
    _content: null,

    // Will point to the iframe where the information will be pasted.
    _iframe: null,

    // Will be a string that contains the CSS properties to be used with Google Docs.
    _gdocStyle: null,

    // Will be a string that contains the CSS properties to be used with Libre
    _libreStyle: null,

    // Will be a string that contains the CSS properties to be used with Word
    _wordStyle: null,

    // Will be a string that contains the CSS properties to be used with Other
    _otherStyle: null,

    // Will be a boolean whether or not we allow straight pasting
    _straight: null,

    // Will be an integer value for height in %
    _height: null,

    // Will be an integer value for width in %
    _width: null,

    // Will be a boolean as to whether or not we have set the content source.
    _setOnce: false,

    // Will point to and hold the current selection when we handle pasting.
    _currentSelection: null,

    initializer: function(params) {
        // Pull in the settings if they are not empty
        // If they are empty, set to the default above
        if(params.wordCSS !== '') {
            this._wordStyle = params.wordCSS.split(',');
        } else {
            this._wordStyle = STYLES.WORD;
        }
        if(params.gdocCSS !== '') {
            this._gdocStyle = params.gdocCSS.split(',');
        } else {
            this._gdocStyle = STYLES.GDOC;
        }
        if(params.libreCSS !== '') {
            this._libreStyle = params.libreCSS.split(',');
        } else {
            this._libreStyle = STYLES.LIBRE;
        }
        if(params.otherCSS !== '') {
            this._otherStyle = params.otherCSS.split(',');
        } else {
            this._otherStyle = this._gdocStyle + this._wordStyle + this._libreStyle;
        }
        if(params.straight) {
            this._straight = (params.straight === '1');
        }
        if(params.height) {
            this._height = params.height;
        } else {
            this._height = '90';
        }
        if(params.width) {
            this._width = params.width;
        } else {
            this._width = '90';
        }

        // Add the button
        this.addButton({
            icon: 'e/paste',
            callback: this._displayDialogue
        });
        // Add keys.
        if(params.keys === '2') {
            this.editor.on('key', function(e) {
                if (e.ctrlKey || e.metaKey) {
                    e.preventDefault();
                    this._displayDialogue();
                }
            }, 'down:86+shift', this);
        } else if (params.keys === '1') {
            this.editor.on('key', function(e) {
                if ((e.ctrlKey || e.metaKey) && !e.shiftKey) {
                    this._displayDialogue();
                }
            }, 'down:86', this);
        }
    },

    /**
     * Display the paste dialogue
     *
     * @method _displayDialogue
     * @private
     */
    _displayDialogue: function() {
        // Set the HTML of the dialogue to be loaded
        var dialogue = this.getDialogue({
            headerContent: M.util.get_string('pluginname', COMPONENTNAME),
            focusAfterHide: true,
            focusOnShowSelector: SELECTORS.PASTEAREA,
            width: this._width + '%',
            height: this._height + '%'
        });

        this._setOnce = false;

        // Save the current selection of the editor.
        this._currentSelection = this.get('host').getSelection();

        // Send the dialogue to the page.
        dialogue.set('bodyContent', this._getDialogueContent());

        // Show the dialogue.
        dialogue.show();

        // Set the click handler for the submit button.
        this._content.one('.submit').on('click', this._pasteContent, this);
        this._content.one('.help').on('click', function() {
            this._content.all('.atto_pastespecial_radios, .atto_pastespecial_help, atto_pastespecial_help' +
            '.atto_pastespecial_contenteditable, .submit, .cancel').each(function() {
                this.toggleClass('hidden');
            });
        }, this);
        this._content.one('.cancel').on('click', function() {
            this._content.ancestor().ancestor().one('button.closebutton').simulate('click');
        }, this);
        this._content.all('input[type="radio"]').on('click', this._changeContent, this);
        this._content.one(SELECTORS.IFRAME).on('valuechange', this._changeContent, this);
        this._content.delegate('key', function(e) {
            if (e.ctrlKey && e.shiftKey) {
                this._pasteContent(e);
            }
        }, 'enter', '.atto_pastespecial_iframe', this);
        this._content.ancestor().setStyle('height', '100%');

        // Set the iframe target for later use.
        this._iframe = this._content.one(SELECTORS.IFRAME);
        this._iframe.focus();
    },

    /**
     * Pastes the content into the editor
     *
     * @method _pasteContent
     * @param e Sent click
     *
     */
    _pasteContent: function(e) {
        var value = this._content.one(SELECTORS.IFRAME_VIEW).getHTML(),
            host = this.get('host');
        // Prevent anything else from being done and hide our dialogue.
        e.preventDefault();
        this.getDialogue({
            focusAfterHide: null
        }).hide();

        // If they had not selected anything in the editor, paste the content at their cursor.
        if(this._currentSelection === false) {
            this.editor.focus();
            this.editor.append(value);
        }
        // Instead, replace the selected content.
        else {
            host.setSelection(this._currentSelection);
            host.insertContentAtFocusPoint(value);
        }
        this.markUpdated();
    },

    /**
     * Check the pasted content to see what the source is
     *
     * @method _findSource
     *
     */
    _findSource: function(value) {
        if (value.indexOf('docs-internal-guid') !== -1) {
            this._content.one(SELECTORS.PASTEFROMGDOC).set('checked','true');
        } else if (/<o:p><\/o:p>|class="MsoNormal"|style="(.|[\n\r])*?;mso-.*?:/.test(value)) {
            this._content.one(SELECTORS.PASTEFROMWORD).set('checked','true');
        } else {
            this._content.one(SELECTORS.PASTEFROMOTHER).set('checked','true');
        }

        this._setOnce = true;
    },

    /**
     * Handle the pasted information when the user changes view options
     *
     * @method _changeContent
     *
     */
    _changeContent: function() {
        var value,
            checked;

        // Obtain the pasted content.
        value = this._iframe.getHTML();

        if (!this._setOnce) {
            this._findSource(value);
        }

        // Figure out which option is checked.
        checked = this._content.one('input[name=from]:checked');

        if (value === '') {
            this._setOnce = false;
        } else if (!checked.hasClass(CSS.PASTESTRAIGHT)) {
            if (checked.hasClass(CSS.PASTEFROMWORD)) {
                value = this._cleanWord(value);
            }

            // Remove nasty browser/generator specific stuffs
            value = value.replace(/<!--StartFragment-->/g,'');
            value = value.replace(/<!--EndFragment-->/g,'');
            value = value.replace(/<!--\[if support(.|[\n\r])*?-->/g,'');
            value = value.replace(/<!--(.|[\n\r])*?<!\[endif\]-->/g,'');
            value = value.replace(/<!--\[endif\]-->/g,'');

            // If they put something in there, let's handle it based on where it's from.
            if (value !== '') {
                if(checked.hasClass(CSS.PASTEFROMWORD)) {
                    value = this._handleWord(value);
                } else if(checked.hasClass(CSS.PASTEFROMGDOC)) {
                    value = this._handleGDoc(value);
                } else if(checked.hasClass(CSS.PASTEFROMLIBRE)) {
                    value = this._handleLibre(value);
                } else if(checked.hasClass(CSS.PASTEFROMOTHER)) {
                    value = this._handleOther(value);
                } else {
                    value = this._handleUnformatted(value);
                }
            }
        }
        this._content.one(SELECTORS.IFRAME_VIEW).setHTML(value);
    },

    /**
     * Obtain the HTML for the Dialogue and prepare handlers
     *
     * @method _getDialogueContent
     * @return HTML dialogue box
     */
    _getDialogueContent: function() {
        var template = Y.Handlebars.compile(TEMPLATE);

        // Set the HTML content.
        this._content = Y.Node.create(template({
            component: COMPONENTNAME,
            elementid: this.get('host').get('elementid'),
            straight: this._straight,
            word: !this._straight,
            CSS: CSS
        }));

        // Return the HTML of the dialogue box.
        return this._content;
    },

    /**
     * Handle the text coming from Microsoft Word
     *
     * @method _handleWord
     * @param String text The text pasted in the iframe
     * @return String handled text to paste
     */
    _handleWord: function(text) {
        return this._findTags(text, 'word');
    },

    /**
     * Handle the text coming from Google Documents
     *
     * @method _handleGDoc
     * @param String text The text pasted in the iframe
     * @return String handled text to paste
     */
    _handleGDoc: function(text) {
        return this._findTags(text, 'gdoc');
    },

    /**
     * Cleans the bullet points created in Word by comments
     *
     * @method _cleanWord
     * @param String text The text pasted into the iframe
     * @return String cleaned text
     */
    _cleanWord: function(text) {
        var front = '',
            mid = '',
            end = '',
            tag = '',
            tag2 = '',
            poshelper1,
            poshelper2,
            poshelper3,
            pos,
            reg = />[^<\n\r].*/gi,
            list = '';
        var count = (text.match(/MsoList/g) || []).length;
        // Errors can be thrown if the function is recursive, but what about here?
        for (var i=0; i<count; i++) {
            tag = text.substring(text.indexOf('MsoList'), text.length);
            tag2 = text.substring(0, text.indexOf(tag));
            tag2 = text.substring(tag2.lastIndexOf('<p'), text.length);
            tag = tag2 + tag;
            tag = tag.substring(0, tag.indexOf('>'));
            front = text.substring(0, text.indexOf(tag));
            poshelper1 = text.indexOf('&nbsp;', text.indexOf(tag)) + 1;
            poshelper2 = text.substring(poshelper1, text.length);
            poshelper3 = reg.exec(poshelper2);
            pos = text.indexOf(poshelper3);
            reg.lastIndex = 1;
            mid = text.substring(pos + 1, text.indexOf('</p>', pos));
            list = text.substring(text.indexOf(tag), text.indexOf('</p>', text.indexOf(mid)));
            end = text.substring(text.indexOf('</p>', text.indexOf(mid)) + 4, text.length);
            mid = mid.replace('<o:p></o:p>', '');
            if(tag.indexOf('First') !== -1) {
                if(list.indexOf('Wingdings') !== -1 || list.indexOf('Symbol') !== -1) {
                    text = front + '<ul>' + '<li>' + mid + '</li>' + end;
                } else {
                    text = front + '<ol>' + '<li>' + mid + '</li>' + end;
                }
            } else if (tag.indexOf('Middle') !== -1) {
                text = front + '<li>' + mid + '</li>' + end;
            } else if (tag.indexOf('Last') !== -1) {
                if(list.indexOf('Wingdings') !== -1 || list.indexOf('Symbol') !== -1) {
                    text = front + '<li>' + mid + '</li></ul>' + end;
                } else {
                    text = front + '<li>' + mid + '</li></ol>' + end;
                }
            }
        }
        return text;
    },

    /**
     * Handle the tags of the pasted text
     *
     * @method _findTags
     * @param String text The text pasted in the iframe
     * @param String origin From where the text was pasted
     * @return String Cleaned HTML text
     */
    _findTags: function(text, origin) {
        var output = '',
            first,
            second,
            last;

        while(true) {
            if(text === '') {
                break;
            }
            first = text.indexOf('<');
            second = text.indexOf('<', first+1);
            last = text.indexOf('>');
            // Make sure that there is no inline < added.
            output += text.substring(0, first);
            if(last < second) {
                // Found the first tag, now what?
                if(text.substring(first, first+6) === '<table') {
                    output += text.substring(first, text.indexOf('</table>') + 8);
                    text = text.substring(text.indexOf('</table>') + 8, text.length);
                } else if(text.substring(first, last+1) === '<br>'
                    || text.substring(first, last+13) === '<o:p>&nbsp;</o:p>') {
                    // A nice clean line break.
                    output += '<br>';
                    text = text.substring(last+1, text.length);
                } else if(text.substring(first, last+7) === '<o:p></o:p>'
                        || text.substring(first + 1, first + 6) === '/font') {
                    // Weird thing word does for end of line, skip it.
                    text = text.substring(last+1, text.length);
                } else if(text.substring(first + 1, first + 5) === 'font') {
                    // Woaw, found a weird font tag, must be Libre.
                    var fontArray = this._handleFont(text.substring(first, last+1), output, text, origin);
                    output = fontArray[0];
                    text = fontArray[1];
                } else if(text.substring(first + 1, first + 6) === 'style') {
                    // You're probably coming from Libre, aren't you?
                    text = text.substring(text.indexOf('</style>') + 8, text.length);
                } else {
                    // It's a tag (not inline style) we want to handle, so let's handle it.
                    output += this._handleTags(text.substring(first, last+1), origin);
                    text = text.substring(last+1, text.length);
                }
            } else if(second !== -1){
                // Somebody put in a plain character.
                output += '<';
                text = text.substring(first+1, text.length);
            } else {
                // No more tags, let's step out.
                text = text.substring(first, text.length);
                output += text;
                break;
            }
        }

        // Clean up that messy stuff.
        output = this._cleanOutput(output);

        return output;
    },

    /**
     * Handle the text imported from Libre
     *
     * @method _handleLibre
     * @param String text The text from the iframe
     * @return String The cleaned up text to be imported
     */
    _handleLibre: function(text) {
        return this._findTags(text, 'libre');
    },

    /**
     * Handle the text imported from Other
     *
     * @method _handleLibre
     * @param String text The text from the iframe
     * @return String The cleaned up text to be imported
     */
    _handleOther: function(text) {
        return this._findTags(text, 'other');
    },

    /**
     * Handle the text imported from anywhere
     *
     * @method _handleUnformatted
     * @param String text The text to be cleaned
     * @return String The text that has been stripped of tags
     */
    _handleUnformatted: function(text) {
        var output;

        output = this._stripTags(text);

        return output;
    },

    /**
     * Handle the <font> tags from Libre
     *
     * @method _handleFont
     * @param String text The content within the font tag
     * @param String current The current handled text to be output
     * @param String incoming The text that we are still handling
     * @param String origin From where the text was pasted
     * @return String Formatted text
     */
     _handleFont: function(tag, current, text, origin) {
        var outputStyle = '',
            outputText = '',
            face,
            color,
            noBreaks,
            tagEnd = tag.length - 1,
            newString = '',
            outputArray = ['', ''];

        // Get rid of pesky spaces and line breaks.
        // Only for comparison.
        noBreaks = current.replace(/\s+/g, '');
        noBreaks = noBreaks.replace(/(\r\n|\n|\r)/gm,"");
        // This only ever happens in LibreOffice, so specific reference.
        face = tag.indexOf('face="');
        color = tag.indexOf('color="');

        // Check to see if the tag has style within it, handle appropriately.
        if(color !== -1) {
            outputStyle = 'color:' + text.substring(color + 7, text.indexOf('"', color + 8));
        }

        // If there is font-face in the tag, add it to the styling to be output.
        if(face !== -1) {
            outputStyle = 'font-family:' + text.substring(face + 6, text.indexOf('"', face + 7));
        }

        var multiFont = text.substring(tagEnd + 1, text.indexOf('</font>')),
            multiFontEnd = text.substring(text.indexOf('</font>'), text.length),
            iterate = -1;
        while (true) {
            if (multiFont.indexOf('<font', iterate + 1) !== -1) {
                multiFont = multiFont + multiFontEnd.substring(0, multiFontEnd.indexOf('</font>') + 7);
                multiFontEnd = multiFontEnd.substring(multiFontEnd.indexOf('</font>') + 7, multiFontEnd.length);
                iterate = multiFont.indexOf('<font', iterate);
            } else {
                break;
            }
        }

        outputText = this._findTags(multiFont, origin);
        outputArray[1] = multiFontEnd;

        // See if previous tag has a style attribute.
        if(noBreaks[noBreaks.length-1] !== '>') {
            outputArray[0] = current + '<span style="' + outputStyle + '">' + outputText + '</span>';
            return outputArray;
        } else if(noBreaks.substring(noBreaks.length - 2, noBreaks.length) !== '">') {
            // Empty tag preceeding, add as style.
            newString = current.substring(0, current.lastIndexOf('>')) + ' style="' + outputStyle + '">' + outputText;
        } else if(noBreaks.substring(noBreaks.length - 2, noBreaks.length) === '">') {
            // Found a previous style, let's compound on it.
            newString = current.substring(0, current.lastIndexOf('>')) + ';' + outputStyle + '">' + outputText;
        }

        outputArray[0] = newString;

        return outputArray;
    },

    /**
     * Handle the content within the tags
     *
     * @method _handleTags
     * @param String text The text contained within the HTML tags
     * @param String origin From where the text is being pasted
     * @return String Properly formatted tag for importing
     */
    _handleTags: function(text, origin) {
        var tag = text.substring(1, text.indexOf(' ')),
            styleStart,
            styleEnd,
            align,
            href,
            additional = [],
            output = '',
            styles = '';

        // If there are no spaces in the tag, it's a plain tag.
        if(text.indexOf(' ') === -1) {
            tag = text.substring(1, text.indexOf('>'));
        }

        // Let's see if there are any styles.
        styleStart = text.indexOf('style="') + 7;
        styleEnd = text.indexOf('"', styleStart);

        if(text.indexOf(' ') !== -1) {
            styles = this._handleStyle(text.substring(styleStart, styleEnd), origin);
        }

        // Anything else?
        align = text.match(/align=".*?"/g);
        href = text.match(/href="[^#].*?"/g);
        additional.align = align;
        additional.href = href;

        if(text.substring(0, 2) === '</') {
            // Closing tags have nothing we need to handle.
            // Close the tag and be done.
            return text;
        } else if(tag === 'span') {
            output += '<span';
        } else if(tag.substring(0, 1) === 'h') {
            output += '<h' + tag[1];
        } else if(tag === 'div') {
            output += '<div';
        } else if(tag === 'ul') {
            output += '<ul';
        } else if(tag === 'ol') {
            output += '<ol';
        } else if(tag === 'li') {
            output += '<li';
        } else if(tag === 'b') {
            output += '<b';
        } else if(tag === 'i') {
            output += '<i';
        } else if(tag === 'u') {
            output += '<u';
        } else if(tag === 's') {
            output += '<s';
        } else if(tag === 'sup') {
            output += '<sup';
        } else if(tag === 'sub') {
            output += '<sub';
        } else if(tag === 'a') {
            output += '<a';
        } else {
            // What's the worst that could happen? Let's go with <p>.
            output += '<p';
        }

        if (additional.href) {
            output += ' ' + additional.href;
        }
        if (additional.align) {
            output += ' ' + additional.align;
        }
        // Add spaces in from of styling information if present.
        if(styles !== '') {
            output += ' style="' + styles + '"';
        }

        // Close it all out.
        output += '>';

        return output;
    },

    /**
     * Clean the HTML tags of the output
     *
     * @param String text The text to clean prior to output
     * @return String Cleaned text to be output
     */
    _cleanOutput: function(text) {
        var span,
            front,
            end;

        while(true) {
            // Remove all spans without style.
            span = text.indexOf('<span>');
            if(span !== -1) {
                front = text.substring(0, span);
                end = text.substring(span + 6, text.length);
                end.replace('</span>', '');
                text = front + end;
            } else {
                break;
            }
        }

        return text;
    },

    /**
     * Handles the information within the style of an HTML tag
     *
     * @param String style The text within the style informaiton
     * @param String origin From where the text was pasted
     * @return String The style information that we desire to keep
     */
    _handleStyle: function(style, origin) {
        var option,
            value,
            output = '',
            comparison,
            clean = '';

        // Where are we bringing this information in from?
        if(origin === 'gdoc') {
            comparison = this._gdocStyle;
        } else if(origin === 'libre') {
            comparison = this._libreStyle;
        } else if(origin === 'word') {
            comparison = this._wordStyle;
        } else {
            comparison = this._otherStyle;
        }

        // Strip the spaces for the CSS options.
        // Keep the spaces for the values.
        clean = style.replace(/\s+/g, '');
        clean = clean.replace(/&quot;/g, '\'');
        style = style.replace(/&quot;/g, '\'');

        // Loops through the styles, and decides whether or not to keep them.
        while(true) {
            // Obtain the property to evaluate
            option = clean.substring(0, clean.indexOf(':'));
            if(style.indexOf(';') !== -1) {
                value = style.substring(style.indexOf(':') + 1, style.indexOf(';'));
            } else {
                // We are at the last CSS entry, and it does not end with ;
                // For SHAME!
                value = style.substring(style.indexOf(':') + 1, style.length);
            }
            // What options do we care about?
            if(comparison.indexOf(option) !== -1 && value !== '') {
                if(value !== 'initial'
                && value !== 'inherit'
                && value !== 'normal'
                && value !== 'tansparent') {
                    output += option + ':' + value + ';';
                }
            }
            if(style.indexOf(';') === -1) {
                // We just looked at the last style
                break;
            }
            style = style.substring(style.indexOf(';') + 1, style.length);
            clean = clean.substring(clean.indexOf(';') + 1, clean.length);
        }
        return output;
    },

    /**
     * Strips all of the HTML tags and replaces them with <p>
     *
     * @param String text The HTML text to be stripped
     * @return String Text that contains only <p> tags
     */
    _stripTags: function(text) {
        var raw,
            first,
            second,
            last;

        // Start it all off with a clean p tag
        raw = '';
        text = text.replace(/<{1}\/{0,1}(?:[ibus]|(?:sub)|(?:sup)|(?:span)){1}(?:.|\r|\n)*?>{1}/g,'');

        while(true) {
            first = text.indexOf('<');
            second = text.indexOf('<', first+1);
            last = text.indexOf('>');
            if(first === -1
                || last === -1) {
                // We found no tags, let's step out
                raw += text;
                break;
            } else if(last < second || second === -1) {
                // We found a tag, what's inside?
                raw += text.substring(0, first);
                if(text.substring(first, first+6) === '<table') {
                    raw += text.substring(first, text.indexOf('</table>') + 8);
                    text = text.substring(text.indexOf('</table>') + 8, text.length);
                } else if(text.substring(first, first+4) === '</p>' &&
                        raw.substring(raw.length-4, raw.length) !== '</p>') {
                    // Let's close out the </p>
                    raw += '</p>';
                    text = text.substring(last+1, text.length);
                } else if(text.substring(first, first+2) === '<p' &&
                        raw.substring(raw.length-3, raw.length) !== '<p>') {
                    //Found an open p
                    raw += '<p>';
                    text = text.substring(last+1, text.length);
                } else if(text.substring(first, last+1) === '<br>') {
                    // A nice clean break
                    raw += '<br>';
                    text = text.substring(last+1, text.length);
                } else if(text[first+1] === '/' &&
                        raw.substring(raw.length-4, raw.length) !== '</p>') {
                    raw += '</p>';
                    text = text.substring(last+1, text.length);
                } else if(raw.substring(raw.length-3, raw.length) !== '<p>' &&
                        text[first+1] !== '/') {
                    raw += '<p>';
                    text = text.substring(last+1, text.length);
                } else {
                    text = text.substring(last+1, text.length);
                }
                if(last === text.length-1) {
                    // We are at the end of the text
                    break;
                }
            } else {
                // Somebody put '<' in as a character
                raw += text.substring(0, second);
                text = text.substring(second, text.length);
            }
        }
        if(raw.substring(raw.length-3, raw.length) === '<p>') {
            return raw.substring(0, raw.length-3);
        } else {
            return raw;
        }
    }
});


}, '@VERSION@', {"requires": ["moodle-editor_atto-plugin"]});
