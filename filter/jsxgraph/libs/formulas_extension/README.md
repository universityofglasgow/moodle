# Formulas extension for Moodle JSXGraph filter

The [moodle question type formulas](https://moodle.org/plugins/qtype_formulas), see also [dynamiccourseware.org](https://dynamiccourseware.org/), allows to create questions containing random values and multiple answer fields.
This extension was created for the [Moodle JSXGraph filter](https://github.com/jsxgraph/moodle-filter_jsxgraph) and is used to use JSXGraph constructions in formulas questions.
It supplies the transfer of values between the formulas question and [JSXGraph](https://jsxgraph.org) constructions. 

### About JSXGraph

JSXGraph is a cross-browser JavaScript library for interactive geometry, function plotting, charting, and data visualization in the web browser.
JSXGraph is implemented in pure JavaScript and does not rely on any other library. Special care has been taken to optimize the performance.

Have a look at [www.jsxgraph.org](https://jsxgraph.org/).

### About Moodle JSXGraph filter

The [Moodle JSXGraph filter](https://github.com/jsxgraph/moodle-filter_jsxgraph) is a plugin for [Moodle](http://moodle.org) to enable function plotting and dynamic geometry constructions with [JSXGraph](http://jsxgraph.org) within a Moodle platform.

*The plugin for Moodle also contains these files.*

## Installation

This extension does not need to be installed separately. 
Instead, it is already included in the Moodle filter and is delivered with it.

Follow the installation instructions [here](https://github.com/jsxgraph/moodle-filter_jsxgraph#installation).

## Usage

The formulas extension is used within a JSXGraph tag.
To do this, either the global setting "formulasextension" must be activated in the filter 

![settings](screenshots/settings.png)

or the tag must contain the attribute `ext_formulas`:

```html
<jsxgraph width="500" height="500" ext_formulas>
    ...
</jsxgraph>
```

### Insert a board into a question

To use a JSXGraph board in a formulas question, first create a question in this category. Then follow the steps below:

1. As usual, assign a meaningful name for the question.
2. Variables can be defined for the use of formulas. Do this in the "Variables" section. A detailed example of this can be found below.
3. Write a question in "Question text".
4. In "Part 1" define the inputs. Several inputs can be defined with `[...]` under "Answer".
5. Under "Part's text" a JSXGraph board can now be integrated in the usual filter notation. If the extension is not activated globally, the tag attribute "ext_formulas" must be used.
    * Warning: the code that is inside the `<jsxgraph>` tag must now be declared within a function.
    * Finally, this function is transferred to an object of the JSXQuestion class with the following call:
      ```html
      <jsxgraph width="500" height="500" ext_formulas>
          var jsxGraphCode = function (question) { ... };
          new JSXQuestion(BOARDID, jsxGraphCode);
      </jsxgraph>
      ```
      More information on using the JSXQuestion class can be found below.
    * Within the function `jsxGraphCode` the object of the class JSXQuestion can be accessed via the parameter `question` and its variables and methods can be used. An overview of this can be found below.
    
### Insert more than one board into a question

As in a normal Moodle site, you can also use several boards in a formulas question. The JSXQuestion class even offers a number of helpful methods for this case.

To use multiple boards, you can proceed in the same way as above with one board. In contrast to point 5, you must note the following:

- Always hand over all boards that were declared in the tag. The best way to do this is to use the `BOARDIDS` array. E.g.:
  ```html
  <jsxgraph width="500" height="500" numberOfBoards="2" ext_formulas>
      var jsxGraphCode = function (question) { ... };
      new JSXQuestion(BOARDIDS, jsxGraphCode);
  </jsxgraph>
  ```
  
- As attributes and methods for JSXQuestion you should now use the following in your code:
    * In the attribute `BOARDID` (= `firstBOARDID`) only the ID of the **first** board is saved. `BOARDIDS` contains all IDs as an array.
    * The attribute `board` (= `firstBoard`) also only contains the reference to the **first** board. Use the `boards` array instead.
    * The method `initBoards` initializes **all** given boards, just like the old` initBoard`. The only difference is that `initBoard` only returns the first board,` initBoards` all as an array.
    
        **Attention! Use the `initBoards` method of the` JSXQuestion` class instead of `JXG.JSXGraph.initBoard`, because with our method all attributes are automatically set to the correct value in the JSXQuestion object. How to assign different attributes to the individual boards can be read under [Methods](#methods).**
    
    * All other attributes and methods can be used normally.
    * We aditionally offer the functions `addChildsAsc` and `addChildsDesc` to link the boards (see [Methods](#methods)).

### Using the class JSXQuestion

#### Initialization

The constructor `new JSXQuestion(boardIDs, jsxGraphCode, allowInputEntry, decimalPrecision)` of class JSXQuestion takes the following parameters:

<table>
    <tr>
        <td>
            <i>{String&nbsp;|&nbsp;String[&nbsp;]}</i>&nbsp;<b>boardIDs</b>
        </td>
        <td>
            ID of the HTML element containing the JSXGraph board. 
            The board can be addressed within a tag using the constant <code>BOARDID</code>. 
            Therefore this parameter has to be set to <code>BOARDID</code>.
            If more than one board is used, the array <code>BOARDIDS</code> must be given.
        </td>
    </tr>
    <tr>
        <td>
            <i>{Function}</i>&nbsp;<b>jsxGraphCode</b>
        </td>
        <td>
            JavaScript function containing the construction code.
            The function must expect the object of class JSXQuestion as input.
        </td>
    </tr>
    <tr>
        <td>
           <i>{Boolean}</i>&nbsp;<b>allowInputEntry</b> Optional,&nbsp;Default:&nbsp;<code>false</code>
        </td>
        <td>
            Should the original inputs from formulas be displayed and linked to the construction?<br>
            If this parameter is <code>false</code>, the input fields for users are hidden.<br>
            If it is set to <code>true</code>, the inputs are displayed and linked to the 
            construction, so that the function <code>jsxGraphCode(...)</code> is executed again 
            if there is a change in an input field.
        </td>
    </tr>
    <tr>
        <td>
            <i>{Number}</i>&nbsp;<b>decimalPrecision</b> Optional,&nbsp;Default:&nbsp;<code>2</code>
        </td>
        <td>
            Number of digits to round to.
        </td>
    </tr>
</table>

#### Attributes

<table>
    <tr>
        <td>
            <i>{String[&nbsp;]}</i>&nbsp;<b>BOARDIDS</b>
        </td>
        <td>
            IDs of the involved boards in an array.
        </td>
    </tr>
    <tr>
        <td>
            <i>{String}</i>&nbsp;<b>firstBOARDID</b><br>
            <i>{String}</i>&nbsp;<b>BOARDID</b>&nbsp;(deprecated)
        </td>
        <td>
            ID of the <b>first</b> board.
        </td>
    </tr>
    <tr>
        <td>
            <i>{JXG.Board[&nbsp;]}</i>&nbsp;<b>boards</b>
        </td>
        <td>
            Array with the stored JSXGraph boards.
        </td>
    </tr>
    <tr>
        <td>
            <i>{JXG.Board}</i>&nbsp;<b>firstBoard</b><br>
            <i>{JXG.Board}</i>&nbsp;<b>board</b>&nbsp;(deprecated)
        </td>
        <td>
            Stored <b>first</b> JSXGraph board.
        </td>
    </tr>
    <tr>
        <td>
            <i>{HTMLElement[&nbsp;]}</i>&nbsp;<b>inputs</b>
        </td>
        <td>
            Stores the input tags from the formulas question.
        </td>
    </tr>
    <tr>
        <td>
            <i>{Boolean}</i>&nbsp;<b>isSolved</b>
        </td>
        <td>
            Indicator if the question has been solved.
        </td>
    </tr>
</table>

#### Methods

<table>
    <tr>
        <td>
            <i>{JXG.Board}</i>&nbsp;<b>initAndAddBoard(id, attributes)</b><br><br><small>
            <b>Parameters:</b><br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i>{String}</i>&nbsp;<b>id</b><br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i>{Object}</i>&nbsp;<b>attributes</b>&nbsp;[<i>default: {}</i>]
            </small>
        </td>
        <td>
            Initializes and adds one board to the boards array. The <code>id</code> must be part of the <code>BOARDIDS</code> array. 
            The resulting board, which is also returned, is added to boards at this index where <code>id</code> is in the array <code>BOARDIDS</code>.
            If a board already exists for this <code>id</code>, it will be deleted.
        </td>
    </tr>
    <tr>
        <td>
            <i>{JXG.Board[&nbsp;]}</i>&nbsp;<b>initBoards(attributes)</b><br><br><small>
            <b>Parameters:</b><br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i>{Object&nbsp;|&nbsp;Object[&nbsp;]}</i>&nbsp;<b>attributes</b>&nbsp;[<i>default: {}</i>]
            </small>
        </td>
        <td>
            Initializes the board(s), saves it/them in the attributes of JSXQuestion and returns an array of boards.
            For this, the object in <code>attributes</code> is forwarded to the function 
            <code>JXG.JSXGraph.initBoard(...)</code>. The string passed during initialization of JSXQuestion object
            is used as the id for the board. If two parameters are specified (as in the 
            specification of <code>JXG.JSXGraph.initBoard(...)</code>), the first parameter
            is ignored.<br>
            <code>attributes</code> can also be an array of attribute objects.
            If there are given fewer attributes than there are boards, the first attributes are used as standard.<br> The attribute <code>boards</code> is cleared and new initialized in this method.
        </td>
    </tr>
    <tr>
        <td>
            <i>{JXG.Board[&nbsp;]}</i>&nbsp;<b>initBoard(firstParam, secondParam)</b><br><br><small>
            <b>Parameters:</b><br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i>{String&nbsp;|&nbsp;Object&nbsp;|&nbsp;Object[&nbsp;]}</i>&nbsp;<b>firstParam</b><br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i>{Object&nbsp;|&nbsp;Object[&nbsp;]}</i>&nbsp;<b>secondParam</b>
            </small>
        </td>
        <td>
            If <code>firstParam</code> is an string or an single (attributes-) object, this function call is forwarded to <code>initAndAddBoard</code>. Otherwise this function is an alias for <code>initBoards</code>.<br>
             <i>For backward compatibility.</i>
        </td>
    </tr>
    <tr>
        <td>
            <i>{void}</i>&nbsp;<b>addChildsAsc()</b>
        </td>
        <td>
            Calls the function <code>addChild</code> ascending for each board.
            After this function <code>boards[0]</code> is child of <code>boards[1]</code>, <code>boards[1]</code> is child of <code>boards[2]</code> etc.
        </td>
    </tr>
    <tr>
            <td>
                <i>{void}</i>&nbsp;<b>addChildsDesc()</b>
            </td>
            <td>
                Calls the function <code>addChild</code> descending for each board.
                After this function <code>boards[0]</code> is parent of <code>boards[1]</code>, <code>boards[1]</code> is parent of <code>boards[2]</code> etc.
            </td>
        </tr>
    <tr>
        <td>
            <i>{void}</i>&nbsp;<b>bindInput(inputNumber,&nbsp;valueFunction)</b><br><br><small>
            <b>Parameters:</b><br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i>{Number}</i>&nbsp;<b>inputNumber</b><br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i>{Function}</i>&nbsp;<b>valueFunction</b>
            </small>
        </td>
        <td>
            Links the board to the inputs. If a change has been made in the board, the 
            input with the number <code>inputNumber</code> is assigned the value that 
            the function <code>valueFunction()</code> returns.
        </td>
    </tr>
    <tr>
        <td>
            <i>{void}</i>&nbsp;<b>set(inputNumber,&nbsp;value)</b><br><br><small>
            <b>Parameters:</b><br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i>{Number}</i>&nbsp;<b>inputNumber</b><br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i>{Number}</i>&nbsp;<b>value</b>
            </small>
        </td>
        <td>
            Fill input element of index <code>inputNumber</code> of the formulas question 
            with <code>value</code>.
        </td>
    </tr>
    <tr>
        <td>
            <i>{void}</i>&nbsp;<b>setAllValues(values)</b><br><br><small>
            <b>Parameters:</b><br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i>{Number[&nbsp;]}</i>&nbsp;<b>values</b>
            </small>
        </td>
        <td>
            Set values for all formulas input fields. The array <code>values</code> 
            contains the values in the appropriate order.
        </td>
    </tr>
    <tr>
        <td>
            <i>{Number}</i>&nbsp;<b>get(inputNumber,&nbsp;defaultValue)</b><br><br><small>
            <b>Parameters:</b><br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i>{Number}</i>&nbsp;<b>inputNumber</b><br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i>{Number}</i>&nbsp;<b>defaultValue</b>&nbsp;[<i>default: 0</i>]
            </small>
        </td>
        <td>
            Get the content of input element of index <code>inputNumber</code> of the
            formulas question as number. If the value of the input could not be read 
            or is not a number the optional <code>defaultValue</code> is returned.
        </td>
    </tr>
    <tr>
        <td>
            <i>{Number[&nbsp;]}</i>&nbsp;<b>getAllValues(defaultValues)</b><br><br><small>
            <b>Parameters:</b><br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i>{Number&nbsp;|&nbsp;Number[&nbsp;]}</i>&nbsp;<b>defaultValues</b>&nbsp;[<i>default: 0</i>]
            </small>
        </td>
        <td>
            Fetch all values from the formulas input fields. If the value of the input 
            could not be read or is not a number the optional entry of the array 
            <code>defaultValues</code> is returned.
        </td>
    </tr>
    <tr>
        <td>
            <i>{void}</i>&nbsp;<b>reload()</b><br>
            <i>{void}</i>&nbsp;<b>update()</b>
        </td>
        <td>
            Reload the construction by executing the given function <code>jsxGraphCode</code>.
        </td>
    </tr>
</table>


## Example

Consider the following *formulas* question:

![screen1](screenshots/screen1.png)

The students should drag the red points such that the blue curve has the equation *y = 2x + 10*.
After having done so, the student clicks on the Check-button to check the correctness of the 
solution. The correct solution is

![screen2](screenshots/screen2.png)

The above question can be realized with *formulas* by supplying the following data:

![screen 3](screenshots/screen3.png)

The variable *a* takes a random value out of the set *{2, 3}* and the variable *b* takes a 
random value out of the set *{10, 20}*. Since the student has to compute *ax+b* for the 
values *1, 2, 3, 4*, the correct values are precomputed in the global variables 
*y1, y2, y3, y4*. As correct answer we demand from the student the four values: 
*[y1, y2, y3, y4]*. If the question does not use JSXGraph there would be four input fields 
for the answers.

![screen 4](screenshots/screen4.png)

Without JSXGraph the student would have to type the four numbers of the solution into 
four input fields. Now this question is enriched with a JSXgraph construction.
This can be done by adding the following code into the field "Part's text" in Part 1:

```html
<jsxgraph width="400" height="400" ext_formulas>

    // JavaScript code to create the construction.
    var jsxCode = function (question) {

        // Import the initial y-coordinates of the four points from formulas
        var t1, t2, t3, t4;
        [t1, t2, t3, t4] = question.getAllValues();

        // Initialize the construction
        var board = question.initBoard(BOARDID, {
                axis:true,
                boundingbox: [-0.5, 35, 5.5, -5],
                showCopyright: true,
                showNavigation: true
            });
        // Four invisible, vertical lines
        var line1 = board.create('segment', [[1,-5], [1,35]], {visible:false}),
            line2 = board.create('segment', [[2,-5], [2,35]], {visible:false}),
            line3 = board.create('segment', [[3,-5], [3,35]], {visible:false}),
            line4 = board.create('segment', [[4,-5], [4,35]], {visible:false});

        // The four points fixated to the lines, called 'gliders'.
        var point_attr = {fixed: question.isSolved, snapToGrid: true, withLabel: false},
            p = [];
        p.push(board.create('glider', [1, t1, line1], point_attr));
        p.push(board.create('glider', [2, t2, line2], point_attr));
        p.push(board.create('glider', [3, t3, line3], point_attr));
        p.push(board.create('glider', [4, t4, line4], point_attr));

        // The polygonal chain, aka. polyline, through the four points
        board.create('polygonalchain', p, {borders: {strokeWidth: 3}});

        // Whenever the construction is altered the values of the points are sent to formulas.
        question.bindInput(0, () => { return p[0].Y(); });
        question.bindInput(1, () => { return p[1].Y(); });
        question.bindInput(2, () => { return p[2].Y(); });
        question.bindInput(3, () => { return p[3].Y(); });
    };

    // Execute the JavaScript code.
    new JSXQuestion(BOARDID, jsxCode, /* if you want to see inputs: */ true);

</jsxgraph>
```

## Feedback

All bugs, feature requests, feedback, etc., are welcome.

## Contributors

The project is based on work by [Tim Kos](https://github.com/timkos) and [Marc Bernart](https://github.com/marcbern-at).
At the moment it is developed by [The Center of Mobile Learning with Digital Technology](http://mobile-learning.uni-bayreuth.de/) (contact: Alfred Wassermann and Andreas Walter).


## License

See [here](./LICENSE).

