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
 * Base class for persistence collection.
 *
 * @package    local_template
 * @copyright  2023 David Aylmer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_template\collections;
use coding_exception;
use Countable;
use renderable;
use renderer_base;
use templatable;
use local_template\models;
use paging_bar;

/**
 * Base class for persistence collection.
 *
 * TODO: create interface for classes extending persistent.
 *
 * @copyright  2023 David Aylmer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class persistentcollection implements Countable, renderable, templatable {

    /** @var string The fully qualified classname. */
    protected $persistentclass = null;
    protected $parentid = 0;
    protected $persistentproperties = [];

    protected $collectionproperties = [];

    protected $title = '';
    protected $view = null;
    protected $totalcount = 0;
    protected $collection = null;
    protected $fields = [];
    protected $displayheadings = true;
    protected $page = 0;
    protected $perpage = 10;
    protected $pageparam = 'page';
    protected $addnew = false;


    /**
     * @param $persistentclass
     * @param $parentid
     * @param $view
     * @param $displayheadings
     * @param $params
     * @param $sort
     * @param $order
     * @param int $page The page you are currently viewing
     * @param int $perpage The number of entries that should be shown per page
     * @throws coding_exception
     */
    public function __construct($persistentclass = '',
                                $parentid = 0,
                                $view = 'table',
                                $displayheadings = true,
                                $select = '',
                                $params = null,
                                $sort = 'timemodified',
                                $order = 'DESC',
                                $page = 0,
                                $perpage = 10,
                                $addnew = false) {

        if (empty($persistentclass)) {
            throw new coding_exception('Static property $persistentclass must be set.');
        }

        if (empty($params)) {
            $params = [];
        }

        $this->persistentclass = $persistentclass;
        $this->parentid = $parentid;
        $this->title = $persistentclass::title();
        $this->view = $view;
        $this->displayheadings = $displayheadings;

        $this->totalcount = $persistentclass::count_records_select($select, $params);

        $this->page = $page;
        $this->perpage = $perpage;
        $this->addnew = $addnew;

        if (property_exists($persistentclass, 'pageparam')) {
            $this->pageparam = $persistentclass::$pageparam;
        } else {
            $this->pageparam = 'page';
        }

        $limitfrom = $page * $perpage;
        $limitnum = $perpage;

        $orderby = '';
        if (!empty($sort)) {
            $orderby = $sort . ' ' . $order;
        }

        $fields = '*';

        // $this->collection = $persistentclass::get_records($params, $sort, $order, $limitfrom, $limitnum);
        $this->collection = $persistentclass::get_records_select($select, $params, $orderby, $fields, $limitfrom, $limitnum);
        $this->persistentproperties = $persistentclass::properties_definition($parentid);
        $this->collectionproperties = $persistentclass::collection_properties();

        /*
        if ($persistentclass != 'local_template\\models\\template') {
            echo '<pre>' . var_export($persistentclass, true) . '</pre>';
            echo '<pre>' . var_export($params, true) . '</pre>';
            echo '<pre>' . var_export($sort, true) . '</pre>';
            echo '<pre>' . var_export($order, true) . '</pre>';
            echo '<pre>' . var_export($skip, true) . '</pre>';
            echo '<pre>' . var_export($limit, true) . '</pre>';
            echo '<pre>' . var_export($this->totalcount, true) . '</pre>';
            echo '<pre>' . var_export($this->collection, true) . '</pre>';
        }
        */

        $this->define_fields();
    }



    public function totalcount() {
        return $this->totalcount;
    }

    public function count() {
        return count($this->collection);
    }

    public function hide_headings() {
        $this->displayheadings = false;
    }

    protected function define_fields() {

        foreach ($this->collectionproperties as $propertyname => $property) {
            $isproperty = true;
            if (!empty($propertyname)) {
                if (array_key_exists($propertyname, $this->persistentproperties)) {
                    $callback = $propertyname;
                } else {
                    if (!array_key_exists('callback', $property)) {
                        throw new coding_exception(
                            'Function argument $field (' . $propertyname .
                            ') must be present in persistent define properties or via callback.'
                        );
                    } else {
                        $isproperty = false;
                        $callback = $property['callback'];
                        if (!method_exists($this->persistentclass, $property['callback'])) {
                            throw new coding_exception(
                                'Property (' . $propertyname . ') does not contain callback ('. $callback .').'
                            );
                        }
                    }
                }
            }

            if (!array_key_exists('label', $property)) {
                throw new coding_exception('Property (' . $propertyname . ') must contain label.');
            }
            $label = $property['label'];

            if (!array_key_exists('alignment', $property)) {
                throw new coding_exception('Property (' . $propertyname . ') must contain alignment.');
            }
            $alignment = $property['alignment'];

            $count = count($this->fields);
            if (array_key_exists($count - 1, $this->fields)) {
                $this->fields[$count - 1]['lastcol'] = 'lastcol';
            }
            $this->fields[] = [
                'columnindex' => $count,
                'text' => $label,
                'isproperty' => $isproperty,
                'callback' => $callback,
                'alignment' => $alignment,
                'lastcol' => 'lastcol',
            ];
        }
    }

    public function export_for_template(renderer_base $output) {

        $rows = [];
        $lastrow = array_key_last($this->collection);
        foreach ($this->collection as $rowindex => $persistent) {

            $row = [];
            $row['cells'] = [];
            $lastcolumn = array_key_last($this->fields);
            $children = null;

            foreach ($this->fields as $columnindex => $heading) {
                $cell = [];
                $cell['columnindex'] = $columnindex;
                $cell['heading'] = $heading['text'];
                $cell['alignment'] = $heading['alignment'];

                $callback = $heading['callback'];
                if ($heading['isproperty']) {
                    $callbackvalue = $persistent->get($callback);
                } else {
                    if ($callback == 'get_actions') {
                        $callbackvalue = $persistent->get_actions($this->totalcount);
                    } else {
                        $callbackvalue = $persistent->{$callback}();
                    }
                }

                $cell['text'] = $callbackvalue;

                $cell['lastcol'] = '';
                if ($columnindex == $lastcolumn) {
                    $cell['lastcol'] = 'lastcol';
                }

                $row['cells'][] = $cell;
            }

            $row['rowid'] = $persistent->get('id');
            $row['identifier'] = $persistent->get_identifier();
            $row['lastrow'] = ($rowindex == $lastrow) ? 'lastrow' : '';

            $rows[] = $row;
        }
        $data = [
            'class' => str_replace('\\', '-', $this->persistentclass),
            'table' => ($this->view == 'table'),
            'collapse' => ($this->view == 'header'),
            'list' => ($this->view == 'list'),
            'displayheadings' => $this->displayheadings,
            'fields' => $this->fields,
            'rows' => $rows,
            'addnew' => $this->addnew,
            'addnewurl' => $this->persistentclass::add_new($this->parentid, true),
        ];

        return $data;
    }

    public function render() {
        if ($this->count() == 0) {
            return $this->persistentclass::no_records($this->parentid);
        }
        global $PAGE;
        $renderer = $PAGE->get_renderer('local_template');
        return $renderer->render($this);
    }

    public function render_paging_bar($path) {
        global $OUTPUT;
        $pagingbar = new paging_bar($this->totalcount(), $this->page, $this->perpage, $path, $this->pageparam);
        return $OUTPUT->render($pagingbar);
    }
}
