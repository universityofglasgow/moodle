<?php
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
 * Filter converting Sketchfab embeds created by atto_sketchfab in the text to embedded Sketchfab viewers.
 *
 * @package    filter_sketchfab
 * @copyright  2015 Jetha Chan <jetha@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined ('MOODLE_INTERNAL') || die();

/**
 * Converts Sketchfab embeds created by atto_sketchfab to embedded Sketchfab viewers.
 */
class filter_sketchfab extends moodle_text_filter {

    /**
     * @const string OEmbed endpoint for Sketchfab.
     */
    const SKETCHFAB_OEMBED_ENDPOINT = 'https://sketchfab.com/oembed';

    /**
     * @const string Sketchfab's home URL.
     */
    const SKETCHFAB_HOME_URL = 'https://sketchfab.com';

    /**
     * @const string Base URL for a Sketchfab model's page.
     */
    const SKETCHFAB_MODELPAGE_URL = 'https://sketchfab.com/models';

    /**
     * @const string v2 API endpoint for Sketchfab.
     */
    const SKETCHFAB_API_ENDPOINT = 'https://api.sketchfab.com/v2/models';

    /**
     * @var int Width of Sketchfab embed.
     */
    private $width = 448;

    /**
     * @var int Height of Sketchfab embed.
     */
    private $height = 241;

    /**
     * Apply the filter to the text
     *
     * @see filter_manager::apply_filter_chain()
     * @param string $text to be processed by the text
     * @param array $options filter options
     * @return string text after processing
     */
    public function filter($text, array $options = array()) {

        // Early out if necessary.
        if (!is_string($text) ||
            empty($text) ||
            stripos($text, '</a>') === false ||
            stripos($text, 'sketchfab.com/models') === false
        ) {
            return $text;
        }

        $this->convert_sketchfablinks_into_embeds($text);

        return $text;
    }

    /**
     * Given some text, this function searches for Sketchfab embeds created by atto_sketchfab.
     *
     *
     * @param string $text Passed in by reference, the string to be searched for embeds.
     */
    protected function convert_sketchfablinks_into_embeds (&$text) {

        global $CFG;

        $regex = "/(<div(?:.*?)class=\"atto_sketchfab-embed\"(?:.*?>)(.*?<\\/div>)<\\/div>)/";
        $rval = array();
        $success = preg_match_all($regex, $text, $rval);
        $targets = $rval[0];
        $modelids = $rval[1];
        $linktext = $rval[2];

        $embeds = array();

        if (!$success) {
            return;
        }

        // Create a new instance of the Moodle cURL class to use.
        $curl = new curl();

        // Build all embeds.
        for ($i = 0; $i < count($targets); $i++) {

            $dom = new DOMDocument();
            $dom->loadHTML($targets[$i]);

            $links = $dom->getElementsByTagName('a');
            $thumblink = null;
            $modelhref = '';
            foreach ($links as $link) {
                $thisclass = $link->getAttribute('class');
                if ($thisclass === 'atto_sketchfab-embed-thumb') {
                    $modelhref = $link->getAttribute('href');
                    $thumblink = $link;
                }
            }
            if (!empty($modelhref)) {

                $modelidregex = '"http:\\/\\/www.sketchfab.com\\/models\\/(\\w*).*"';
                $modelidmatches = array();

                if (preg_match($modelidregex, $modelhref, $modelidmatches)) {
                    $modelid = $modelidmatches[1];

                    // Build a Sketchfab embed.
                    // - make a curl request to get metadata.
                    $metadata = $curl->get(self::SKETCHFAB_API_ENDPOINT . '/' . $modelid);
                    if (!$metadata) {
                        $embeds[] = $targets[$i];
                        continue;
                    }
                    $metajson = json_decode($metadata, true);
                    $author = $metajson['user']['displayName'];
                    $modelname = $metajson['name'];

                    // Consider replacing with Mustache-based approach for Moodle 2.9.
                    // - iframe.
                    $iframe = $dom->createElement('iframe');
                    $iframeattrs = array(
                        'width' => $this->width,
                        'height' => $this->height,
                        'src' => self::SKETCHFAB_MODELPAGE_URL . '/' . $modelid . '/embed',
                        'frameborder' => 0,
                        'allowfullscreen' => 'true',
                        'mozallowfullscreen' => 'true',
                        'webkitallowfullscreen' => 'true',
                        'onmousewheel' => ''
                    );
                    foreach ($iframeattrs as $key => $value) {
                        $iframe->setAttribute($key, $value);
                    }
                    $thumblink->parentNode->replaceChild($iframe, $thumblink);

                    // Push onto the embed array.
                    $embeds[] = $dom->saveHTML();
                }
            }
        }

        // Replace.
        $text = str_replace($targets, $embeds, $text);
    }
}