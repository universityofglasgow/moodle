# JSXGraph Moodle filter <!-- [![Build Status](https://travis-ci.org/jsxgraph/moodle-filter_jsxgraph.svg?branch=master)](https://travis-ci.org/github/jsxgraph/moodle-filter_jsxgraph) --> 

Also listed in [Moodle plugins directory](https://moodle.org/plugins/filter_jsxgraph).

### About JSXGraph

JSXGraph is a cross-browser JavaScript library for interactive geometry, function plotting, charting, and data visualization in the web browser.
JSXGraph is implemented in pure JavaScript and does not rely on any other library. Special care has been taken to optimize the performance.

Have a look at [www.jsxgraph.org](https://jsxgraph.org/).

##### Features
- Open source
- High-performance, small footprint
- No dependencies
- Multi-touch support
- Backward compatible down to IE 6

### Our filter

This is a plugin for [Moodle](http://moodle.org) to enable function plotting and dynamic geometry constructions with [JSXGraph](http://jsxgraph.org) within a Moodle platform.
Using the [JSXGraph](http://jsxgraph.org) filter makes it a lot easier to embed [JSXGraph](http://jsxgraph.org) constructions into Moodle online documents, e.g. in contents like page, quiz, link,... .

## Installation

You can download the filter here: [Moodle plugins directory](https://moodle.org/plugins/filter_jsxgraph).
A video about the installation process is available on [YouTube](https://youtu.be/nQvUKg-qD4g).

To find out more about the installation, you can also [read on here](#installing-the-filter-step-by-step).

## Usage

In a Moodle course you can add a board to different types of content, i.e.: `Page`, `Link`, `Quiz`, ...

At the position the construction should appear, create a construction by:
* switching to the code input, i.e. to "HTML source editor" **(*)**
* inserting a `<jsxgraph>` tag with all required parameters
* Each <code><div\></code> that contains a JSXGraph board needs a unique ID on the page. This ID is generated automatically. Reference it within the JavaScript using the constant <code>BOARDID</code>.

**(*) Important notice:**   
Please note that some Moodle editors remove the `<jsxgraph>` tag when saving.
As a result, the construction may not be displayed correctly or at all.
You should therefore always use the "Plain text editor".
Some also report that the "Atto HTML editor" works, too.

Example: 

```html
<jsxgraph width="500" aspect-ratio="1/1">
   var brd = JXG.JSXGraph.initBoard(BOARDID, {boundingbox:[-5,5,5,-5], axis:true});
   var p = brd.create('point', [1,2]);
</jsxgraph>
```
   
Get many examples for constructions at [https://jsxgraph.org/share](https://jsxgraph.org/share). There you can export them to the JSXGraph Moodle filter format.
   
***For tag attributes and global settings have a look at [Attributes and settings](#attributes-and-settings) in this documentation.*** 
 
Have a look to this [video](https://youtu.be/gHsFA1upQLc).

Be aware of the fact, that you don't see the construction unless you leave the editor and save your document.
On reopening it later, you will notice the code rather than the `<jsxgraph>` tag. To edit your content later, again switch to the code input.

### Using multipe boards in one tag

It is possible to replace a `<jsxgraph>` tag with more than one board. To do this, enter a number in the tag attribute `numberOfBoards`. This does the following:

- Instead of `BOARDID`, the unique ids can now be found in `BOARDID0`, `BOARDID1`, `BOARDID2`, ...
- All IDs are stored in an array `BOARDIDS` additionally. It looks like: `BOARDIDS = [BOARDID0, BOARDID1, BOARDID2, ...]`
- The attributes `width`, `height`, `title` and `description` can contain several values. These are separated by commas. The first value applies to the first board, the second value to the second, etc. If not enough values are given (especially only one), the first value is used for the other boards.

Here is an example:
![multiple boards](screenshots/multiple-boards.png)

````html
<jsxgraph width="500,200" aspect-ratio="1/1" numberOfBoards="2">
   var board = JXG.JSXGraph.initBoard(BOARDID0, {boundingbox: [-1.33, 1.33, 1.33, -1.33], axis: true, showNavigation:false});
   var board2 = JXG.JSXGraph.initBoard(BOARDID1, {boundingbox: [-1, 1.33, 7, -1.33], showNavigation:false});

   board.suspendUpdate();
   var b1c1 = board.create('circle', [[0,0], [1,0]], {fixed:true});
   var b1p1 = board.create('point', [2, 0], {slideObject: b1c1});
   var perp = board.create('perpendicular', [board.defaultAxes.x,b1p1],[{strokeColor: '#ff0000', visible: true}, {visible: false}]);
   var perp2 = board.create('perpendicular',[board.defaultAxes.y,b1p1],[{strokeColor: '#0000ff', visible: true}, {visible: false}]);
   board.unsuspendUpdate();

   board2.suspendUpdate();
   var xax2 = board2.create('axis', [[0,0], [1,0]]);
   board2.create('axis', [[0,0], [0,1]]);
   board2.create('ticks', [xax2, [Math.PI, 2*Math.PI]], {strokeColor: 'green', strokeWidth: 2});
   
   // sine:
   var b2p1 = board2.create('point', [
                function(){ return JXG.Math.Geometry.rad([1,0],[0,0],b1p1); }, 
                function() { return b1p1.Y() }], 
                {fixed: true, trace: true, color: '#ff0000', name: 'S'});
   // cosine:
   var b2p2 = board2.create('point', [
                function(){ return JXG.Math.Geometry.rad([1,0],[0,0],b1p1); }, 
                function() { return b1p1.X() }], 
                {fixed: true, trace: true, color: '#0000ff', name: 'C'});
   // Dependencies (only necessary if b2p1 or b2p2 is deleted)
   b1p1.addChild(b2p1);
   b1p1.addChild(b2p2);
   board2.unsuspendUpdate();

   board.addChild(board2);
</jsxgraph>
````

### JSXGraph and formulas - a filter extension

To use an JSXGraph board in a formulas question you can use <a href="https://github.com/jsxgraph/moodleformulas_jsxgraph" target="_blank">our filter extension for formulas</a>.
Its files are already contained in this filter (see [here](libs/formulas_extension)). You can load them by [admin settings](#admin-settings) or [tag attributes](#jsxgraph-tag-attributes).
Please note the [documentation](libs/formulas_extension/README.md) of this extension, especially the installation instructions.

### JSXGraph and STACK 

This filter is not necessary to use JSXGraph with [STACK](https://moodle.org/plugins/qtype_stack). STACK has its own extension for JSXGraph.
Please refer 
[STACK Documentation](https://stack2.maths.ed.ac.uk/demo2018/question/type/stack/doc/doc.php/Authoring/JSXGraph.md) and
[GitHub](https://github.com/maths/moodle-qtype_stack/blob/master/doc/en/Authoring/JSXGraph.md).

<i>Note that this STACK extension is not developed, updated or managed by the JSXGraph developing team.</i>


## Attributes and settings

### Dimensions

In the global settings and in your `<jsxgraph>` tag you can specify several dimensions for the board:

- aspect-ratio
- width
- height
- max-width
- max-height

To use the responsiveness of the boards, you have to use `width` and `aspect-ratio`. If `width` and `height` are given, `aspect-ratio` is ignored.

_***Use-cases:***_

<table>
    <thead>
        <tr>
            <td>#</td>
            <td>given</td>
            <td>behavior</td>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>1</td>
            <td><code>width</code> and <code>height</code> in any combination (max-/...)</td>
            <td>
                The dimensions are applied to the boards <code>div</code>. Layout is like in the css specification defined. See notes (a) and (b). <code>aspect-ratio</code> is ignored in this case. Please note also (c).
            </td>
        </tr>
        <tr>
            <td>2</td>
            <td><code>aspect-ratio</code> and <code>(max-)width</code></td>
            <td>
                The boards width ist fix according its value. The height is automatically regulated following the given <code>aspect-ratio</code>.
            </td>
        </tr>
        <tr>
            <td>3</td>
            <td><code>aspect-ratio</code> and <code>(max-)height</code></td>
            <td>
                The boards height ist fix according its value. The width is automatically regulated following the given <code>aspect-ratio</code>. This case doesn\'t work on browsers which doesn\'t support <code>aspect-ratio</code>. The css trick (see (a)) can not help here.
            </td>
        </tr>
        <tr>
            <td>4</td>
            <td>only <code>aspect-ratio</code></td>
            <td>The <code>fallback width</code> from admin settings is used. Apart from that see case 2.</td>
        </tr>
        <tr>
            <td>5</td>
            <td>nothing</td>
            <td><code>aspect-ratio</code> is set to `fallback aspect-ratio` from admin settings and then see case 4.
            </td>
        </tr>
    </tbody>
</table>

_***Notes:***_

**(a)** Pay attention: the `div` uses the css attribute `aspect-ratio` which is not supported by every browser. If the browser does not support this, a trick with a wrapping `div` and `padding-bottom` is applied. This trick only works, if `aspect-ratio` and `(max-)width` are given, not in combination with `(max-)height`! For an overview of browsers which support `aspect-ratio` see <a href="https://caniuse.com/mdn-css_properties_aspect-ratio" target="_blank">caniuse.com</a>

**(b)** If the css trick is not needed, the result is only the `div` with id `BOARDID` for the board. The value of tag attribute `wrapper-class` is ignored. In the trick the `div` is wrapped by a `div` with id `BOARDID`-wrapper. This wrapper contains the main dimensions and the board-`div` gets only relative dimensions according to the case, e.g. `width: 100%`.

**(c)** If only `width` is given, the height will be `0` like in css. You have to define an aspect-ratio or height to display the board!

### Admin settings

As moodle administrator, you can make the following settings:
<table>
    <tr>
        <th>JSXGraph version</th>
        <td>Our filter delivers all versions of JSXGraph. Here you can choose which version to use. If <code>automatically</code> is selected (recommended), the latest version will be used automatically.</td>
    </tr>
    <tr>
        <th>extension for question type <a href="https://moodle.org/plugins/qtype_formulas" target="_blank">formulas</a></th>
        <td>Here you can determine whether the external library for <a href="https://github.com/jsxgraph/moodleformulas_jsxgraph" target="_blank">using JSXGraph in fomulas questions</a> is loaded or not.<br>If you want to use the library in individual tags (and global setting says "deactivated"), set the corresponding attribute to true.</td>
    </tr>
    <tr>
        <th>HTML entities</th>
        <td>If this setting is set to <code>true</code>, HTMLentities like "&", "<", etc. are supported within the JavaScript code for JSXGraph.</td>
    </tr>
    <tr>
        <th>convert encoding</th>
        <td>Decide wether the encoding of the text between the JSXGraph tags should be converted to UTF-8 or not.</td>
    </tr>
    <tr>
        <th>global JavaScript</th>
        <td>In this textbox you can type a general JavaScript code to be loaded before loading specific tag code.</td>
    </tr>
    <tr>
        <th>width<br>height<br>aspect-ratio<br>max-width<br>max-height</th>
        <td>Default dimensions of JSXGraph container. See <a href="#dimensions">dimensions</a>. Is used if no information is given in the tag.</td>
    </tr>
    <tr>
        <th>fallback width<br>fallback aspect-ratio</th>
        <td>This values are relevant if no dimension or only an aspect-ratio is given. See <a href="#dimensions">dimensions</a> for more information.</td>
    </tr>
    <tr>
        <th>divid</th>
        <td><b>Deprecated</b><br><small>Prefix for the automatically generated divid of every JSX construction.</small></td>
    </tr>
</table>

### `<jsxgraph>` tag attributes

Within the `<jsxgraph>` tag different attributes can be declared, e.g. `<jsxgraph width="..." height="..." entities="..." useGlobalJS="...">` 
<table>
    <tr>
        <th><code>numberOfBoards</code></th>
        <td>Here you can enter the number of boards by which the JSXGraph tag will be replaced. A corresponding number of BOARDIDs is generated. See also <a href="#using-multipe-boards-in-one-tag" target="_self">here</a>.<br>Default: <code>1</code>.</td>
    </tr>
    <tr>
        <th><code>title</code> and <code>description</code></th>
        <td>This information is used for better accessibility. Since JSXGraph version 1.2, the board attributes <code>title</code> and <code>description</code> are used to create elements for <code>aria-labelledby</code> and <code>aria-describedby</code> of the board. Title ans description are set by specification in this tag attributes.</td>
    </tr>
    <tr>
        <th><code>width</code><br><code>height</code><br><code>aspect-ratio</code><br><code>max-width</code><br><code>max-height</code></th>
        <td>Dimensions of JSXGraph container. Overrides the global settings locally. You can use any CSS unit here. If no unit but only an integer is specified, "px" is automatically added. See chapter <a href="#dimensions">dimensions</a> for more information.</td>
    </tr>
    <tr>
        <th><code>class</code></th>
        <td>Here you can specify css classes for the boards <code>&lt;div&gt;</code>.Please have a look at <a href="#dimensions">dimensions</a> for understanding the HTML tree construction.</td>
    </tr>
     <tr>
        <th><code>wrapper-class</code></th>
        <td>Depending on the clients browser and the given dimensions a <code>&lt;div&gt;</code> is wrapping the board. Here you can specify its css classes. It may be that this value is ignored. Please have a look at <a href="#dimensions">dimensions</a>.</td>
    </tr>
     <tr>
        <th><code>force-wrapper</code></th>
        <td>Depending on the clients browser and the given dimensions a <code>&lt;div&gt;</code> is wrapping the board. Here you can force adding this <code>&lt;div&gt;</code>.</td>
    </tr>
     <tr>
        <th><code>ext_formulas</code></th>
        <td>Determine whether the external library for <a href="https://github.com/jsxgraph/moodleformulas_jsxgraph" target="_blank">using JSXGraph in fomulas questions</a> is loaded or not.<br>Possible values: <code>"true"</code>, <code>"false"</code>.</td>
    </tr>
    <tr>
        <th><code>entities</code></th>
        <td>If HTMLentities like "&", "<", etc. should be supported within the JavaScript code set the attribute to <code>"true"</code>. To override a global <code>true</code> type <code>"false"</code>.</td>
    </tr>
    <tr>
        <th><code>useGlobalJS</code></th>
        <td>Decide whether global JavaScript from admin settings should be loaded before your code.<br>Possible values: <code>"true"</code>, <code>"false"</code>.</td>
    </tr>
    <tr>
        <th><code>boardid</code> or <code>box</code></th>
        <td><b>Deprecated</b><br><small>This attribute defines, which id the graph of JSXGraph will have. Please use the id stored in the constant <code>BOARDID</code> within the JavaScript block, especially for the first parameter in <code>JXG.JSXGraph.initBoard(...)</code>. Look at the examples at <a href="#usage">Usage</a>.</small></td>
    </tr>
</table>

These attributes can be defined for each board by separating with `,`:
- title
- description
- width
- height
- aspect-ratio
- max-width
- max-height
- class
- wrapper-class
- box
- boardid

## Using MathJax within the board

To use the pre-installed `MathJax` notation within the board, your **Moodle admin** first has to make some settings:

1. Go to `Moodle -> Site administration -> Plugins -> Filters -> Manage filters`
2. If not already done, enable the `MathJax` filter
3. Arrange the filters so, that `MathJax` is before `JSXGraph`.
4. If the `TeX notation` filter is activated, this must be arranged below `MathJax`

After this changes **everyone** can use `MathJax` notation `$$(...)$$` within the board of JSXGraph as follows:

- Instead of using ` \ ` between `<jsxgraph>` tags you have to escape the backslash by using ` \\ ` <br>
  e.g. `\frac` --> `\\frac`
- To prevent unpredictable behavior you should set `parse: false`
- *optional:* To make the font bigger, use the `fontSize`-attribute

Look at this example:

```html
<jsxgraph width="100%" height="600">
    var brd = JXG.JSXGraph.initBoard(BOARDID, {boundingbox:[-6,6,6,-6], axis:true});
    var t = brd.create('text', [1,4, '$$( \\sqrt{1},\\frac {8}{2} )$$'],{parse: false, fixed: true, fontSize: 20});
    var s = brd.create('text', [-5,2.5, '$$( 1-6,\\sum_{n=0}^\\infty (3/5)^n )$$'], {parse: false});
</jsxgraph>
```

Using the `MathJax` filter within the board is supported in `moodle2.x` and up. 

## Installing the filter step by step

### Installation with Moodle routine (by Moodle admin)

To install the filter for moodle2.9+ you can follow the steps below:

1. Download the entire `master` branch as a ZIP-compressed folder via the GitHub download button<br>
   **Do not unpack the ZIP directory!**
2. In Moodle, navigate to `Site administration -> Plugins -> Install plugins`
3. Under `Install plugin from ZIP file`, drag and drop the downloaded ZIP directory into input field und click on `Show more...`
4. Choose the plugin type `Text filter (filter)`
5. Rename the root directory to `jsxgraph` by filling the input (be sure to write correctly)
6. Click on `Install plugin from ZIP the file` and follow the instructions
7. After installing go to `Moodle -> Site administration -> Plugins -> Filters -> Manage filters` and switch the `Active?`-attribute of JSXGraph to `on`

### Installation in Moodle directory (by file server admin)

Otherwise, you can also install the filter with the following steps:

1. Download the entire `master` branch as a ZIP-compressed folder via the github download button
2. Create a folder `jsxgraph` in the directory `moodle -> filter` of your Moodle installation (be sure to write correctly)
3. Upload the files and folders contained in the ZIP directory to the directory just created
4. Open site root of your Moodle installation and follow the steps to install plugin 
5. After installing go to `Moodle -> Site administration -> Plugins -> Filters -> Manage filters` and switch the `Active?`-attribute of JSXGraph to `on`

## Build Plugin (how to release a new version)

This plugin no longer needs to be explicitly build. To release a **new version of JSXGraph** into the filter follow the steps in: [RELEASE.txt](RELEASE.txt). 

## Feedback

All bugs, feature requests, feedback, etc., are welcome.

## License

http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

[![ITEMS](img/items_logo_blue.png)](https://itemspro.eu)
[![Cofunded by the Erasmus+ programme of the European union](img/eu_flag_co_funded_pos_rgb_left_small.jpg)](https://ec.europa.eu/programmes/erasmus-plus/)
