<?php
// This file is part of JSXGraph Moodle Filter.
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

namespace filter_jsxgraph;

defined('MOODLE_INTERNAL') || die;

if (class_exists('\core_filters\text_filter')) {
    class_alias('\core_filters\text_filter', 'filter_jsxgraph_base_text_filter');
} else {
    class_alias('\moodle_text_filter', 'filter_jsxgraph_base_text_filter');
}

global $PAGE, $CFG;

require_once($CFG->libdir . '/pagelib.php');

/**
 * Class filter_jsxgraph
 *
 * @package    filter_jsxgraph
 * @copyright  2023 JSXGraph team - Center for Mobile Learning with Digital Technology – Universität Bayreuth
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class text_filter extends \filter_jsxgraph_base_text_filter {
    /**
     * Path to jsxgraphcores
     *
     * @var \string
     */
    public const PATH_FOR_CORES = '/filter/jsxgraph/amd/src/';

    /**
     * Path to library folders
     *
     * @var \string
     */
    public const PATH_FOR_LIBS = '/filter/jsxgraph/libs/';

    /**
     * Const for tag name (<jsxgraph></jsxgraph>).
     *
     * @var \string
     */
    private const TAG = "jsxgraph";

    /**
     * Name for JavaScript constant for board ids.
     * This is supplemented by a counter (BOARDID0, BOARDID1, ...).
     *
     * @var \string
     */
    private const BOARDID_CONST = "BOARDID";

    /**
     * Name for JavaScript constant array of board ids.
     *
     * @var \string
     */
    private const BOARDIDS_CONST = "BOARDIDS";

    /**
     * HTML encoding.
     *
     * @var \string
     */
    private const ENCODING = "UTF-8";

    /**
     * Allowed dimension attributes.
     *
     * @var \array
     */
    private const ALLOWED_DIMS = ["aspect-ratio", "width", "height", "max-width", "max-height"];

    /**
     * Attribute for aspect ratio.
     *
     * @var \string
     */
    private const AR = "aspect-ratio";

    /**
     * Allowed dimension attributes without aspect ratio.
     *
     * @var \array
     */
    private const ALLOWED_DIMS_EXCEPT_AR = ["width", "height", "max-width", "max-height"];

    /**
     * Attributes for width.
     *
     * @var \array
     */
    private const WIDTHS = ["width", "max-width"];

    /**
     * Parsed DOM node.
     *
     * @var \domDocument
     */
    private $document = null;

    /**
     * List of <jsxgraph> tags.
     *
     * @var \array
     */
    private $taglist = null;

    /**
     * Global admin settings.
     *
     * @var \object
     */
    private $settings = null;

    /**
     * List of used unique board ids. Length >= length of $taglist.
     *
     * @var \array
     */
    private $ids = [];

    /**
     * Used version of JSXGraph.
     *
     * @var \null|\object
     */
    private $versionjsx = null;

    /**
     * Used version of Moodle.
     *
     * @var \null|\object
     */
    private $versionmoodle = null;

    /**
     * Main filter function.
     *
     * @param \string $text Moodle standard.
     * @param \array  $options Moodle standard.
     *
     * @return \string
     */
    public function filter($text, $options = []) {
        // To optimize speed, search for a <jsxgraph> tag (avoiding to parse everything on every text).
        if (!is_int(strpos($text, '<' . self::TAG))) {
            return $text;
        }

        // 0. STEP: Do some initial stuff.

        $this->settings = $this->get_adminsettings();
        $this->set_versions();
        if (!isset($this->versionjsx) || !isset($this->versionmoodle)) {
            return $text;
        }

        // 1. STEP: Convert HTML string to a dom object.

        // Create a new dom object.
        $this->document = new \domDocument('1.0', self::ENCODING);
        $this->document->formatOutput = true;

        // Load the html into the object.
        libxml_use_internal_errors(true);
        if ($this->settings["convertencoding"]) {
            // Fix #47: see https://aruljohn.com/blog/php-deprecated-mbstring-htmlentities/ for more information.
            $this->document->loadHTML(
                htmlspecialchars_decode(
                    iconv(
                        self::ENCODING,
                        'ISO-8859-1',
                        htmlentities($text, ENT_COMPAT, self::ENCODING)
                    ),
                    ENT_QUOTES
                )
            );
        } else {
            $this->document->loadHTML($text);
        }
        libxml_use_internal_errors(false);

        // Discard white space.
        $this->document->preserveWhiteSpace = false;
        $this->document->strictErrorChecking = false;
        $this->document->recover = true;

        // 2. STEP: Get tag elements.

        $this->taglist = $this->document->getElementsByTagname(self::TAG);

        // 3.+4. STEP: Load library (if needed) and iterate backwards through the <jsxgraph> tags.

        if (!empty($this->taglist)) {
            $this->load_jsxgraph();

            for ($i = $this->taglist->length - 1; $i > -1; $i--) {
                $node = $this->taglist->item($i);
                $this->ids = [];
                $new = $this->get_replaced_node($node, $i);

                // Replace <jsxgraph>-node.
                $node->parentNode->replaceChild($this->document->appendChild($new), $node);

                $this->apply_js($node);
            }
        }

        // 5. STEP: Paste new div node in web page.

        // Remove DOCTYPE.
        $this->document->removeChild($this->document->firstChild);
        // Remove <html><body></body></html>.
        $str = $this->document->saveHTML();
        $str = str_replace("<body>", "", $str);
        $str = str_replace("</body>", "", $str);
        $str = str_replace("<html>", "", $str);
        $str = str_replace("</html>", "", $str);

        // Cleanup.
        $this->taglist = null;
        $this->document = null;
        $this->settings = null;

        return $str;
    }

    /**
     * Create a new div node for a given JSXGraph node.
     *
     * @param \domNode $node JSXGraph node.
     * @param \int     $index Index in taglist.
     *
     * @return \domNode
     */
    private function get_replaced_node($node, $index) {
        $attributes = $this->get_tagattributes($node);

        // Create div node.
        $new = $this->document->createElement('div');
        $a = $this->document->createAttribute('class');
        $a->value = 'jsxgraph-boards';
        $new->appendChild($a);

        for ($i = 0; $i < $attributes['numberOfBoards']; $i++) {

            // Create div id.
            $divid = $this->string_or($attributes['boardid'][$i], $attributes['box'][$i]);
            if ($this->settings['usedivid']) {
                $divid = $this->string_or($divid, $this->settings['divid'] . $index);
            } else {
                $divid = $this->string_or($divid, 'JSXGraph_' . strtoupper(uniqid()));
            }
            $this->ids[$i] = $divid;

            // Create new div element containing JSXGraph.
            $dimensions = [
                "width" => $this->string_or($attributes['width'][$i], $this->settings['fixwidth']),
                "height" => $this->string_or($attributes['height'][$i], $this->settings['fixheight']),
                "aspect-ratio" => $this->string_or($attributes['aspect-ratio'][$i], $this->settings['aspectratio']),
                "max-width" => $this->string_or($attributes['max-width'][$i], $this->settings['maxwidth']),
                "max-height" => $this->string_or($attributes['max-height'][$i], $this->settings['maxheight']),
            ];
            $div = $this->get_board_html(
                $divid,
                $dimensions,
                $attributes['class'][$i],
                $attributes['wrapper-class'][$i],
                $attributes['force-wrapper'][$i],
                $this->settings['fallbackaspectratio'],
                $this->settings['fallbackwidth']
            );

            $divdom = new \DOMDocument;
            libxml_use_internal_errors(true);
            $divdom->loadHTML($div);
            libxml_use_internal_errors(false);

            $new->appendChild($this->document->importNode($divdom->documentElement, true));

            // Load formulas extension.
            if ($this->settings['formulasextension'] || $attributes['ext_formulas'][$i]) {
                $this->load_library('formulas');
            }
        }

        return $new;
    }

    /**
     * Combine global code and code contained in $node. Define some JavaScript constants. Apply this code to the dom.
     *
     * @param \domNode $node JSXGraph node.
     *
     * @return void
     */
    private function apply_js($node) {
        global $PAGE;
        $attributes = $this->get_tagattributes($node);
        $code = "";

        // Load global JavaScript code from administrator settings.

        if ($this->settings['globalJS'] !== '' && $attributes['useGlobalJS'][0]) {
            $code .=
                "// Global JavaScript code from administrator settings.\n" .
                "//////////////////////////////////////////////////////\n\n" .
                $this->settings['globalJS'] .
                "\n\n";
        }

        // Define BOARDID constants and some attributes for accessibility.

        $code .=
            "// Define BOARDID constants.\n" .
            "////////////////////////////\n\n";
        for ($i = 0; $i < count($this->ids); $i++) {
            $name = self::BOARDID_CONST . $i;
            $code .=
                "const $name = '" . $this->ids[$i] . "';\n" .
                "console.log('$name = `'+$name+'` has been prepared.');\n";
        }
        $code .=
            "const " . self::BOARDID_CONST . " = " . self::BOARDID_CONST . "0" . ";\n" .
            "const " . self::BOARDIDS_CONST . " = ['" . implode("', '", $this->ids) . "'];\n" .
            "\n";

        $code .=
            "// Accessibility.\n" .
            "/////////////////\n\n";
        $code .=
            "if(JXG.exists(JXG.Options.board)) {\n" .
            "JXG.Options.board.title = '" . $attributes['title'][0] . "';\n" .
            "JXG.Options.board.description = '" . $attributes['description'][0] . "';\n" .
            "}\n";

        // Load the code from <jsxgraph>-node.

        $usercode = $this->document->saveHTML($node);
        // Remove <jsxgraph> tags.
        $usercode = preg_replace("(</?" . self::TAG . "[^>]*\>)i", "", $usercode);
        // In order not to terminate the JavaScript part prematurely, the backslash has to be escaped.
        $usercode = str_replace("</script>", "<\/script>", $usercode);

        $code .=
            "// Code from user input.\n" .
            "////////////////////////\n";
        $code .= $usercode;

        // Surround the code with version-specific strings, e.g. "require".

        $surroundings = $this->get_code_surroundings();
        $code = $surroundings["pre"] . "\n\n" . $code . $surroundings["post"];

        // Convert the HTML-entities in the variable $code.

        if ($this->settings['html_entities'] && $attributes['entities']) {
            $code = html_entity_decode($code);
        }

        // Paste the code.

        // POI: Version differences. Here no differences.
        $PAGE->requires->js_init_call($code);
    }

    /**
     * Helper Function for apply_js(...). Returns the pre and post part of JavaScript code.
     *
     * @return string[]
     */
    private function get_code_surroundings() {
        $result = [
            'pre' => '',
            'post' => '',
        ];

        $condition = '';
        for ($i = 0; $i < count($this->ids); $i++) {
            $condition .= "document.getElementById('" . $this->ids[$i] . "') != null && ";
        }
        $condition = substr($condition, 0, -4);

        // Build from the inside out.

        // POI: Version differences.
        if ($this->versionmoodle["is_newer_version"]) {

            if ($this->versionjsx["version_number"] >= $this->jxg_to_version_number("1.5.0")) {

                $result["pre"] =
                    "require(['" . $this->get_core_url() . "'], function (JXG) {\n" .
                    "if ($condition) {\n" .
                    $result["pre"];
                $result["post"] =
                    $result["post"] .
                    "}\n " .
                    "});\n";

            } else {

                $result["pre"] =
                    "if ($condition) {\n" .
                    $result["pre"];
                $result["post"] =
                    $result["post"] .
                    "}\n ";

            }

        } else {

            if ($this->versionjsx["version_number"] >= $this->jxg_to_version_number("1.5.0")) {

                $result["pre"] =
                    "require(['" . $this->get_core_url() . "'], function (JXG) {\n" .
                    "if ($condition) {\n" .
                    $result["pre"];
                $result["post"] =
                    $result["post"] .
                    "}\n " .
                    "});\n";

            } else if ($this->versionjsx["version_number"] > $this->jxg_to_version_number("0.99.6")) {

                $result["pre"] =
                    "require(['jsxgraphcore'], function (JXG) {\n" .
                    "if ($condition) { \n" .
                    $result["pre"];
                $result["post"] =
                    $result["post"] .
                    "}\n " .
                    "});\n";

            } else {

                $result["pre"] =
                    "if ($condition) {\n" .
                    $result["pre"];
                $result["post"] =
                    $result["post"] .
                    "}\n ";

            }

        }

        $result["pre"] =
            "\n//< ![CDATA[\n" .
            $result["pre"];
        $result["post"] =
            $result["post"] .
            "\n//]]>\n";

        $result["pre"] =
            "\n\n// ###################################################" .
            "\n// JavaScript code for JSXGraph board '" . $this->ids[0] . "' and other\n" .
            $result["pre"];
        $result["post"] =
            $result["post"] .
            "\n// End code for JSXGraph board '" . $this->ids[0] . "' and other " .
            "\n// ###################################################\n\n";

        return $result;
    }

    /**
     * Returns the path to the core file.
     *
     * @return \moodle_url
     */
    private function get_core_url() {
        return new \moodle_url(self::PATH_FOR_CORES . $this->versionjsx["file"]);
    }

    /**
     * Load JSXGraph code from local or from server
     */
    private function load_jsxgraph() {
        global $PAGE;

        // POI: Version differences.
        if ($this->versionmoodle["is_newer_version"]) {

            if ($this->versionjsx["version_number"] >= $this->jxg_to_version_number("1.5.0")) {

                // Nothing to do!
                return;

            } else {

                $t = $this->document->createElement('script', '');
                $a = $this->document->createAttribute('type');
                $a->value = 'text/javascript';
                $t->appendChild($a);
                $a = $this->document->createAttribute('src');
                $a->value = $this->get_core_url();
                $t->appendChild($a);
                $this->document->appendChild($t);

            }

        } else {

            if ($this->versionjsx["version_number"] >= $this->jxg_to_version_number("1.5.0")) {

                // Nothing to do!
                return;

            } else {

                $PAGE->requires->js($this->get_core_url());

            }

        }
    }

    /**
     * Sets $this->versionjsx and $this->versionmoodle objects.
     * Needs $this->settings to be set!
     *
     * @return void
     *
     * @see $versionjsx
     * @see $versionmoodle
     */
    private function set_versions() {
        $this->versionjsx = null;
        $this->versionmoodle = null;

        // Resolve JSXGraph version.
        if (!empty($this->settings)) {
            $jsxversion = $this->settings['versionJSXGraph'];
            $versions = json_decode(get_config('filter_jsxgraph', 'versions'), true);
            if (empty($jsxversion) || $jsxversion === 'auto') {
                $jsxversion = $versions[1]["id"];
            }
            foreach ($versions as $v) {
                if ($v["id"] === $jsxversion) {
                    $this->versionjsx = $v;
                    break;
                }
            }
            $this->versionjsx["version"] = $this->versionjsx["id"];
            $this->versionjsx["version_number"] = $this->jxg_to_version_number($this->versionjsx["version"]);
        }

        // Resolve Moodle version.
        $this->versionmoodle = [
            "version" => get_config('moodle', 'version'),
            "is_supported" => get_config('moodle', 'version') >= get_config('filter_jsxgraph', 'requires'),
            "is_newer_version" => get_config('moodle', 'version') >= 2023042400,
        ];

        if (!$this->versionmoodle["is_supported"]) {
            $this->versionmoodle = null;
        }
    }

    /**
     * Returns true if variable is empty, 0 or equal to $default.
     *
     * @param \mixed $var Some variable
     * @param \null  $default Default value
     *
     * @return \bool
     */
    public static function empty_or_0_or_default($var, $default = null) {
        return empty($var) || $var === 0 || $var === '0' || $var === '0px' || $var === $default;
    }

    /**
     * Returns a css value or $default,
     *
     * @param \mixed  $var Some variable
     * @param \string $default Default value
     *
     * @return string
     */
    public static function css_norm($var, $default = '') {
        if (substr('' . $var, 0, 1) === '0') {
            $var = 0;
        } else if (empty($var)) {
            $var = $default;
        } else if (is_numeric($var)) {
            $var .= 'px';
        }

        return "" . $var;
    }

    /**
     * Build a <div> for board.
     *
     * This function creates the HTML for a board according to the given dimensions. It is possible, to define an aspect-ratio.
     * If there are given width and height, aspect-ratio is ignored.
     *
     * There are the following use-cases:
     *  ===========================================================================================================================
     *  |  nr  |              given              |                                    behavior                                    |
     *  ===========================================================================================================================
     *  |   1  |  width and height in any com-   |  The dimensions are applied to the boards <div>. Layout is like in the css     |
     *  |      |  bination (min-/max-/...)       |  specification defined. See notes (a) and (b). Aspect-ratio is ignored in      |
     *  |      |                                 |  this case. Please note also (c).                                              |
     *  ---------------------------------------------------------------------------------------------------------------------------
     *  |   2  |  aspect-ratio and               |  The boards width ist fix according its value. The height is automatically     |
     *  |      |  (min-/max-)width               |  regulated following the given aspect-ratio.                                   |
     *  ---------------------------------------------------------------------------------------------------------------------------
     *  |   3  |  aspect-ratio and               |  The boards height ist fix according its value. The width is automatically     |
     *  |      |  (min-/max-)height              |  regulated following the given aspect-ratio. This case doesn't work on         |
     *  |      |                                 |  browsers which doesn't support aspect-ratio. The css trick (see (a)) can      |
     *  |      |                                 |  not help here.                                                                |
     *  ---------------------------------------------------------------------------------------------------------------------------
     *  |   4  |  only aspect-ratio              |  The $defaultwidth is used. Apart from that see case 2.                       |
     *  ---------------------------------------------------------------------------------------------------------------------------
     *  |   5  |  nothing                        |  Aspect-ratio is set to $defaultaspectratio and then see case 4.             |
     *  ===========================================================================================================================
     *
     * Notes:
     *  (a) Pay attention: the <div> uses the css attribute "aspect-ratio" which is not supported by every browser. If the browser
     *      does not support this, a trick with a wrapping <div> and padding-bottom is applied. This trick only works, if
     *      aspect-ratio and (min-/max-)width are given, not in combination with (min-/max-)height! For an overview of browsers
     *      which support aspect-ratio see {@link https://caniuse.com/mdn-css_properties_aspect-ratio.}
     *  (b) If the css trick is not needed, the result is only the <div> with id $id for the board. The value of $wrapperclasses
     *      is ignored.
     *      In the trick the div is wrapped by a <div> with id $id + '-wrapper'. This wrapper contains the main dimensions and the
     *      board-<div> gets only relative dimensions according to the case, e.g. width: 100%.
     *      You can force adding an wrapper by setting $forcewrapper to true.
     *  (c) If only width is given, the height will be 0 like in css. You have to define an aspect-ratio or height to display the
     *      board!
     *
     * @param \string $id
     * @param \object $dimensions with possible attributes
     *                                      aspect-ratio  (the ratio of width / height)
     *                                      width         (px, rem, vw, ...; if only a number is given, its interpreted as px)
     *                                      height        (px, rem, vh, ...; if only a number is given, its interpreted as px)
     *                                      max-width     (px, rem, vw, ...; if only a number is given, its interpreted as px)
     *                                      min-width     (px, rem, vw, ...; if only a number is given, its interpreted as px)
     *                                      max-height    (px, rem, vh, ...; if only a number is given, its interpreted as px)
     *                                      min-height    (px, rem, vh, ...; if only a number is given, its interpreted as px)
     * @param \string $classes Additional css classes for the board.
     * @param \string $wrapperclasses Additional css classes for the boards container.
     *                                      (If it is needed. In the other case this is merged with $classes.)
     * @param \bool   $forcewrapper Default: false.
     * @param \string $defaultaspectratio Default: "1 / 1".
     * @param \string $defaultwidth Default: "100%".
     * @param \bool   $perventjsdimreg Default: false.
     *
     * @return string                       The <div> for the board.
     */
    private function get_board_html(
        $id, $dimensions = [], $classes = "", $wrapperclasses = "", $forcewrapper = false,
        $defaultaspectratio = "1 / 1", $defaultwidth = "100%",
        $perventjsdimreg = false
    ) {

        // Tmp vars.
        $styles = "";
        $wrapperstyles = "";

        $tmp = true;
        foreach (self::ALLOWED_DIMS_EXCEPT_AR as $attr) {
            $tmp = $tmp && self::empty_or_0_or_default($dimensions[$attr]);
        }
        if ($tmp && self::empty_or_0_or_default($dimensions[self::AR])) {
            $dimensions[self::AR] = $defaultaspectratio;
            $dimensions["width"] = $defaultwidth;
        }

        // At this point there is at least an aspect-ratio.

        foreach (self::ALLOWED_DIMS as $attr) {
            if (!self::empty_or_0_or_default($dimensions[$attr])) {
                $styles .= "$attr: " . self::css_norm($dimensions[$attr]) . "; ";
            }
        }

        $styles = substr($styles, 0, -1);
        $classes = !empty($classes) ? ' ' . $classes : '';
        $board = '<div id="' . $id . '" class="jxgbox' . $classes . '" style="' . $styles . '"></div>';

        if (!$perventjsdimreg) {

            foreach (self::WIDTHS as $attr) {
                if (!self::empty_or_0_or_default($dimensions[$attr])) {
                    $wrapperstyles .= "$attr: " . self::css_norm($dimensions[$attr]) . "; ";
                }
            }

            $js = "\n" .
                '<script type="text/javascript">
    (function() {
        let addWrapper = function (boardid, classes = [], styles = "") {
            let board = document.getElementById(boardid),
                wrapper, wrapperid = boardid + "-wrapper";

            wrapper = document.createElement("div");
            wrapper.id = wrapperid;
            wrapper.classList.add("jxgbox-wrapper");

            for (let c of classes)
                wrapper.classList.add(c);

            wrapper.style = styles;

            board.parentNode.insertBefore(wrapper, board.nextSibling);
            wrapper.appendChild(board);
        }

        const FORCE_WRAPPER = false || ' . ($forcewrapper ? 'true' : 'false') . ';

        let boardid = "' . $id . '",
            wrapper_classes = "' . $wrapperclasses . '".split(" "),
            wrapper_styles = "' . $wrapperstyles . '",
            board = document.getElementById(boardid),
            ar, ar_h, ar_w, padding_bottom;

        if (!CSS.supports("aspect-ratio", "1 / 1") && board.style["aspect-ratio"] !== "") {

            ar = board.style["aspect-ratio"].split("/", 3);
            ar_w = ar[0].trim();
            ar_h = ar[1].trim();
            padding_bottom = ar_h / ar_w * 100;

            if (wrapper_styles !== "")
                addWrapper(boardid, wrapper_classes, wrapper_styles);

            board.style = "height: 0; padding-bottom: " + padding_bottom + "%; /*" + board.style + "*/";

        } else if (FORCE_WRAPPER) {

            wrapper_styles = "";
            if (board.style.width.indexOf("%") > -1) {
                wrapper_styles += "width: " + board.style.width + "; "
                board.style.width = "100%";
            }
            if (board.style.height.indexOf("%") > -1) {
                wrapper_styles += "height: " + board.style.height + "; "
                board.style.height = "100%";
            }
            addWrapper(boardid, wrapper_classes, wrapper_styles);
        }
    })();
        </script>';

        } else {
            $js = "";
        }

        return $board . $js;
    }

    /**
     * Load additional library
     *
     * @param \string $libname
     *
     */
    private function load_library($libname) {
        global $PAGE;

        $libs = [
            'formulas' => 'formulas_extension/JSXQuestion.js',
        ];

        if (!array_key_exists($libname, $libs)) {
            return;
        }
        $url = self::PATH_FOR_LIBS . $libs[$libname];

        // POI: Version differences.
        if ($this->versionmoodle["is_newer_version"]) {

            $t = $this->document->createElement('script', '');
            $a = $this->document->createAttribute('type');
            $a->value = 'text/javascript';
            $t->appendChild($a);
            $a = $this->document->createAttribute('src');
            $a->value = new \moodle_url($url);
            $t->appendChild($a);
            $this->document->appendChild($t);

        } else {

            $PAGE->requires->js(new \moodle_url($url));

        }
    }

    /**
     * Determine the attributes
     *
     * @param \domNode $node
     *
     * @return \array
     */
    private function get_tagattributes($node) {
        $numberofboardsattr = 'numberOfBoards';
        $numberofboardsval = 1;
        $attributes = [
            'title' => '',
            'description' => '',
            'width' => '',
            'height' => '',
            'aspect-ratio' => '',
            'max-width' => '',
            'max-height' => '',
            'class' => '',
            'wrapper-class' => '',
            'force-wrapper' => '',
            'entities' => '',
            'useGlobalJS' => '',
            'ext_formulas' => '',
            'box' => '',
            'boardid' => '',
        ];
        $boolattributes = [
            'force-wrapper' => false,
            'entities' => true,
            'useGlobalJS' => true,
            'ext_formulas' => null,
        ];
        $possiblearrayattributes = [
            'title',
            'description',
            'width',
            'height',
            'aspect-ratio',
            'max-width',
            'max-height',
            'class',
            'wrapper-class',
            'box',
            'boardid',
        ];

        $numberofboardsval =
            $node->getAttribute($numberofboardsattr) ? :
                $node->getAttribute(strtolower($numberofboardsattr)) ? : $numberofboardsval;

        foreach ($attributes as $attr => $value) {
            $a = $node->getAttribute($attr) ? : $node->getAttribute(strtolower($attr));
            if (isset($a) && !empty($a)) {
                $a = explode(',', $a);
            } else {
                $a = [''];
            }
            $attributes[$attr] = [];
            $arrattr = in_array($attr, $possiblearrayattributes);

            for ($i = 0; $i < $numberofboardsval; $i++) {
                if (!isset($a[$i]) || empty($a[$i]) || !$arrattr) {
                    $attributes[$attr][$i] = $a[0];
                } else {
                    $attributes[$attr][$i] = $a[$i];
                }
                if (array_key_exists($attr, $boolattributes)) {
                    $attributes[$attr][$i] = $this->get_bool_value($node, $attr, $attributes[$attr][$i], $boolattributes[$attr]);
                }
            }
        }

        $attributes[$numberofboardsattr] = $numberofboardsval;

        return $attributes;
    }

    /**
     * Get settings made by administrator
     *
     * @return \array settings from administration
     */
    private function get_adminsettings() {
        // Set defaults.
        $defaults = [
            'versionJSXGraph' => 'auto',
            'formulasextension' => true,
            'html_entities' => true,
            'convertencoding' => true,
            'globalJS' => '',
            'usedivid' => false,
            'divid' => 'box',
            'fixwidth' => '',
            'fixheight' => '',
            'aspectratio' => '',
            'maxwidth' => '',
            'maxheight' => '',
            'fallbackaspectratio' => '1 / 1',
            'fallbackwidth' => '100%',
        ];

        $bools = [
            'formulasextension',
            'html_entities',
            'convertencoding',
            'usedivid',
        ];

        $trims = [
            'globalJS',
        ];

        // Read and save settings.
        foreach ($defaults as $a => &$default) {
            $tmp = get_config('filter_jsxgraph', $a);

            if (in_array($a, $bools)) {
                $tmp = $this->convert_bool($tmp);
            }
            if (in_array($a, $trims)) {
                $tmp = trim($tmp);
            }
            $default = $tmp;
        }

        return $defaults;
    }

    /**
     * Converts a version string like 1.2.3 to an integer.
     *
     * @param \string $versionstring
     *
     * @return \int
     */
    private function jxg_to_version_number($versionstring) {
        $arr = explode('.', $versionstring);

        return
            intval($arr[0]) * 10000 +
            intval($arr[1]) * 100 +
            intval($arr[2]) * 1;
    }

    /**
     * Gives the value of $attribute in $node as bool. If the attribute does not exist, $stdval is returned.
     *
     * @param \domNode      $node
     * @param \string       $attribute
     * @param \string       $givenval
     * @param \bool|\string $stdval
     *
     * @return \bool
     */
    private function get_bool_value($node, $attribute, $givenval, $stdval) {
        if ($node->hasAttribute($attribute)) {
            if ($givenval == '') {
                return true;
            } else {
                return $this->convert_bool($givenval, $stdval);
            }
        } else {
            return $stdval;
        }
    }

    /**
     * Convert string to bool
     *
     * @param \string $string
     * @param \bool   $default
     *
     * @return \bool
     */
    private function convert_bool($string, $default = false) {
        if ($string === false || $string === "false" || $string === 0 || $string === "0") {
            return false;
        } else if ($string === true || $string === "true" || $string === 1 || $string === "1") {
            return true;
        } else {
            return $default;
        }
    }

    /**
     * Decide between two strings
     *
     * @param \string $choice1
     * @param \string $choice2
     *
     * @return \string
     */
    private function string_or($choice1, $choice2) {
        if (!empty($choice1)) {
            return $choice1;
        } else {
            return $choice2;
        }
    }
}
