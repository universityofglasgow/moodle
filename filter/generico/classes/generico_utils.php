<?php
/**
 * Created by PhpStorm.
 * User: justin
 * Date: 17/03/24
 * Time: 15:04
 */

namespace filter_generico;


class generico_utils
{
 const FILTER_GENERICO_TEMPLATE_COUNT = 20;


    public static function fetch_emptyproparray(){
        $proparray=array();
        $proparray['AUTOID'] = '';
        $proparray['CSSLINK'] = '';
        return $proparray;
    }

    /**
     * Return an array of variable names
     * @param string template containing @@variable@@ variables
     * @return array of variable names parsed from template string
     */
    public static function fetch_variables($template){
        $matches = array();
        $t = preg_match_all('/@@(.*?)@@/s', $template, $matches);
        if(count($matches)>1){
            return($matches[1]);
        }else{
            return array();
        }
    }

    public static function fetch_filter_properties($filterstring){
        //lets do a general clean of all input here
        //see: https://github.com/justinhunt/moodle-filter_generico/issues/7
        $filterstring=clean_param($filterstring,PARAM_TEXT);

        //this just removes the {GENERICO: .. }
        $rawproperties = explode ("{GENERICO:", $filterstring);
        //here we remove any html tags we find. They should not be in here
        $rawproperties = $rawproperties[1];
        $rawproperties = explode ("}", $rawproperties);
        //here we remove any html tags we find. They should not be in here
        //and we return the guts of the filter string for parsing
        $rawproperties = strip_tags($rawproperties[0]);

        //Now we just have our properties string
        //Lets run our regular expression over them
        //string should be property=value,property=value
        //got this regexp from http://stackoverflow.com/questions/168171/regular-expression-for-parsing-name-value-pairs
        $regexpression='/([^=,]*)=("[^"]*"|[^,"]*)/';
        $matches=array();

        //here we match the filter string and split into name array (matches[1]) and value array (matches[2])
        //we then add those to a name value array.
        $itemprops = array();
        if (preg_match_all($regexpression, $rawproperties,$matches,PREG_PATTERN_ORDER)){
            $propscount = count($matches[1]);
            for ($cnt =0; $cnt < $propscount; $cnt++){
                // echo $matches[1][$cnt] . "=" . $matches[2][$cnt] . " ";
                $newvalue = $matches[2][$cnt];
                //this could be done better, I am sure. WE are removing the quotes from start and end
                //this wil however remove multiple quotes id they exist at start and end. NG really
                $newvalue = trim($newvalue,'"');
                $itemprops[trim($matches[1][$cnt])]=$newvalue;
            }
        }
        return $itemprops;
    }


    /**
     * Returns URL to the stored file via pluginfile.php.
     *
     * theme revision is used instead of the itemid.
     *
     * @param string $setting
     * @param string $filearea
     * @return string protocol relative URL or null if not present
     */
    public static function setting_file_url($filepath, $filearea) {
        global $CFG;


        $component = 'filter_generico';
        $itemid = 0;
        $syscontext = \context_system::instance();

        $url = \moodle_url::make_file_url("$CFG->wwwroot/pluginfile.php", "/$syscontext->id/$component/$filearea/$itemid".$filepath);

        // Now this is tricky because the we can not hardcode http or https here, lets use the relative link.
        // Note: unfortunately moodle_url does not support //urls yet.
        // $url = preg_replace('|^https?://|i', '//', $url->out(false));
        return $url;
    }


    public static function setting_file_serve($filearea, $args, $forcedownload, $options) {
        global $CFG;
        require_once("$CFG->libdir/filelib.php");

        $syscontext = \context_system::instance();
        $component = 'filter_generico';

        $revision = array_shift($args);
        if ($revision < 0) {
            $lifetime = 0;
        } else {
            $lifetime = 60*60*24*60;
        }

        $fs = get_file_storage();
        $relativepath = implode('/', $args);

        $fullpath = "/{$syscontext->id}/{$component}/{$filearea}/0/{$relativepath}";
        $fullpath = rtrim($fullpath, '/');
        if ($file = $fs->get_file_by_hash(sha1($fullpath))) {
            send_stored_file($file, $lifetime, 0, $forcedownload, $options);
            return true;
        } else {
            send_file_not_found();
        }
    }

    public static function update_revision() {
        set_config('revision', time(), 'filter_generico');
    }
}