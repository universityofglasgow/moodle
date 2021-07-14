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

namespace theme_hillhead\output;

use coding_exception;
use html_writer;
use tabobject;
use tabtree;
use custom_menu_item;
use custom_menu;
use block_contents;
use navigation_node;
use action_link;
use stdClass;
use moodle_url;
use preferences_groups;
use action_menu;
use help_icon;
use single_button;
use single_select;
use paging_bar;
use url_select;
use context_course;
use context_system;
use pix_icon;
use action_menu_filler;
use action_menu_link;
use action_menu_link_secondary;
use core_text;

defined('MOODLE_INTERNAL') || die;

/**
 * Renderers to align Moodle's HTML with that expected by Bootstrap
 *
 * @package    theme_hillhead
 * @copyright  2012 Bas Brands, www.basbrands.nl
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class core_renderer extends \core_renderer {

    /** @var custom_menu_item language The language menu if created */
    protected $language = null;

    /**
     * We don't like these...
     *
     */
    public function edit_button(moodle_url $url) {
        return '';
    }
    
    public function search_box($id = false) {
        global $CFG;

        // Accessing $CFG directly as using \core_search::is_global_search_enabled would
        // result in an extra included file for each site, even the ones where global search
        // is disabled.
        if (empty($CFG->enableglobalsearch) || !has_capability('moodle/search:query', context_system::instance())) {
            return '';
        }

        if ($id == false) {
            $id = uniqid();
        } else {
            // Needs to be cleaned, we use it for the input id.
            $id = clean_param($id, PARAM_ALPHANUMEXT);
        }

        // JS to animate the form.
        
        $formText = '<label for="header-search-form-input" class="accesshide">Search for:</label><div class="input-group">
      <input type="text" name="q" id="header-search-form-input" class="form-control" placeholder="Search Moodle">
      <span class="input-group-append">
        <button class="btn btn-default" type="submit"><i class="fa fa-search"></i><span class="d-none d-md-inline"> Search</span></button>
      </span>
    </div>';

        $formattrs = array('class' => '', 'action' => $CFG->wwwroot . '/search/index.php');

        $searchinput = html_writer::tag('form', $formText, $formattrs);

        return html_writer::tag('div', $searchinput, array('class' => 'hillhead-search-input-wrapper', 'id' => $id));
    }
    
    protected function render_context_header(\context_header $contextheader) {

        $showheader = empty($this->page->layout_options['nocontextheader']);
        if (!$showheader) {
            return '';
        }

        // All the html stuff goes here.
        $html = html_writer::start_div('page-context-header');

        // Image data.
        if (isset($contextheader->imagedata)) {
            // Header specific image.
            $html .= html_writer::div($contextheader->imagedata, 'page-header-image');
        }

        // Headings.
        if (!isset($contextheader->heading)) {
            $headings = $this->heading($this->page->heading, $contextheader->headinglevel);
        } else {
            $headings = $this->heading($contextheader->heading, $contextheader->headinglevel);
        }

        $html .= html_writer::tag('div', $headings, array('class' => 'page-header-headings'));

        // Buttons.
        if (isset($contextheader->additionalbuttons)) {
            $html .= html_writer::start_div('btn-group header-button-group');
            foreach ($contextheader->additionalbuttons as $button) {
                if (!isset($button->page)) {
                    // Include js for messaging.
                    if ($button['buttontype'] === 'togglecontact') {
                        \core_message\helper::togglecontact_requirejs();
                    }
                    if ($button['buttontype'] === 'message') {
                        \core_message\helper::messageuser_requirejs();
                    }
                    $image = $this->pix_icon($button['formattedimage'], $button['title'], 'moodle', array(
                        'class' => 'iconsmall',
                        'role' => 'presentation'
                    ));
                    $image .= html_writer::span($button['title'], 'header-button-title');
                } else {
                    $image = html_writer::empty_tag('img', array(
                        'src' => $button['formattedimage'],
                        'role' => 'presentation'
                    ));
                }
                $html .= html_writer::link($button['url'], html_writer::tag('span', $image), $button['linkattributes']);
            }
            $html .= html_writer::end_div();
        }
        $html .= html_writer::end_div();

        return $html;
    }
    
        /**
     * Construct a user menu, returning HTML that can be echoed out by a
     * layout file.
     *
     * @param stdClass $user A user object, usually $USER.
     * @param bool $withlinks true if a dropdown should be built.
     * @return string HTML fragment.
     */
    public function user_menu($user = null, $withlinks = null) {
        global $USER, $CFG;
        require_once($CFG->dirroot . '/user/lib.php');

        if (is_null($user)) {
            $user = $USER;
        }

        // Note: this behaviour is intended to match that of core_renderer::login_info,
        // but should not be considered to be good practice; layout options are
        // intended to be theme-specific. Please don't copy this snippet anywhere else.
        if (is_null($withlinks)) {
            $withlinks = empty($this->page->layout_options['nologinlinks']);
        }

        // Add a class for when $withlinks is false.
        $usermenuclasses = 'usermenu';
        if (!$withlinks) {
            $usermenuclasses .= ' withoutlinks';
        }

        $returnstr = "";

        // If during initial install, return the empty return string.
        if (during_initial_install()) {
            return $returnstr;
        }

        $loginpage = $this->is_login_page();
        $loginurl = get_login_url();
        // If not logged in, show the typical not-logged-in string.
        if (!isloggedin()) {
            $returnstr = get_string('loggedinnot', 'moodle');
            if (!$loginpage) {
                $returnstr .= " (<a href=\"$loginurl\">" . get_string('login') . '</a>)';
            }
            return html_writer::div(
                html_writer::span(
                    $returnstr,
                    'login'
                ),
                $usermenuclasses
            );

        }

        // If logged in as a guest user, show a string to that effect.
        if (isguestuser()) {
            $returnstr = get_string('loggedinasguest');
            if (!$loginpage && $withlinks) {
                $returnstr .= " (<a href=\"$loginurl\">".get_string('login').'</a>)';
            }

            return html_writer::div(
                html_writer::span(
                    $returnstr,
                    'login'
                ),
                $usermenuclasses
            );
        }
        
        $extraMenuItems = Array();

        // Get some navigation opts.
        $opts = user_get_user_navigation_info($user, $this->page);

        $avatarclasses = "avatars";
        $avatarcontents = html_writer::span($opts->metadata['useravatar'], 'avatar current');
        $dropdownAvatar = $avatarcontents;
        $usertextcontents = '';
        $loginMenuHeader = '';
        
        if (!empty($opts->metadata['asotheruser'])) {
            $extraMenuItems[] = Array(
                'icon' => new pix_icon('i/user', '', null, array('class' => 'iconsmall')),
                'text' => $opts->metadata['realuserfullname'],
                'link' => new moodle_url('/my')
            );
            $loginMenuHeader .= '<span class="loggedinuser">'.$opts->metadata['realuserfullname'].'</span>';
            $extraMenuItems[] = Array(
                'icon' => new pix_icon('i/users', '', null, array('class' => 'iconsmall')),
                'text' => get_string('loggedinas','moodle', $opts->metadata['userfullname']),
                'link' => false
            );
            $loginMenuHeader .= '<span class="dimmed">'.get_string('loggedinas','moodle', $opts->metadata['userfullname']).'</span>';
            $avatarcontents .= html_writer::span(
                $opts->metadata['realuseravatar'],
                'avatar realuser'
            );
        } else {
            $extraMenuItems[] = Array(
                'icon' => new pix_icon('i/user', '', null, array('class' => 'iconsmall')),
                'text' => $opts->metadata['userfullname'],
                'link' => new moodle_url('/my')
            );
            $loginMenuHeader .= '<span class="loggedinuser">'.$opts->metadata['userfullname'].'</span>';
        }
        
        

        // Role.
        if (!empty($opts->metadata['asotherrole'])) {
            $extraMenuItems[] = Array(
                'icon' => new pix_icon('hillhead/role', '', null, array('class' => 'iconsmall')),
                'text' => 'Viewing as a '.$opts->metadata['rolename'],
                'link' => false
            );
            $loginMenuHeader .= '<span class="dimmed">'.'Viewing as a '.$opts->metadata['rolename'].'</span>';
        }

        $returnstr .= html_writer::span(
            html_writer::span($usertextcontents, 'usertext mr-1') .
            html_writer::span($avatarcontents, $avatarclasses),
            'userbutton'
        );

        // Create a divider (well, a filler).
        $divider = new action_menu_filler();
        $divider->primary = false;

        $am = new action_menu();
        $am->set_menu_trigger(
            $returnstr
        );
        
        $am->add('<div class="media user-login-details">'.$dropdownAvatar.'<div class="media-body m-l-1">'.$loginMenuHeader.'</div></div>');
        
        $am->add($divider);
        
        /*foreach($extraMenuItems as $menuItem) {
            if($menuItem['link'] === false) {
                $am->add($this->render($menuItem['icon']).$menuItem['text']);
            } else {
                $am->add_secondary_action(new action_menu_link_secondary($menuItem['link'], $menuItem['icon'], $menuItem['text']));
            }
        }
        
        $am->add($divider);*/
                
        $am->set_action_label(get_string('usermenu'));
        $am->set_alignment(action_menu::TR, action_menu::BR);
        $am->set_nowrap_on_items();
        if ($withlinks) {
            $navitemcount = count($opts->navitems);
            $idx = 0;
            foreach ($opts->navitems as $key => $value) {

                switch ($value->itemtype) {
                    case 'divider':
                        // If the nav item is a divider, add one and skip link processing.
                        $am->add($divider);
                        break;

                    case 'invalid':
                        // Silently skip invalid entries (should we post a notification?).
                        break;

                    case 'link':
                        // Process this as a link item.
                        $pix = null;
                        if (isset($value->pix) && !empty($value->pix)) {
                            $pix = new pix_icon($value->pix, '', null, array('class' => 'iconsmall'));
                        } else if (isset($value->imgsrc) && !empty($value->imgsrc)) {
                            $value->title = html_writer::img(
                                $value->imgsrc,
                                $value->title,
                                array('class' => 'iconsmall')
                            ) . $value->title;
                        }

                        $al = new action_menu_link_secondary(
                            $value->url,
                            $pix,
                            $value->title,
                            array('class' => 'icon')
                        );
                        if (!empty($value->titleidentifier)) {
                            $al->attributes['data-title'] = $value->titleidentifier;
                        }
                        $am->add($al);
                        break;
                }

                $idx++;

                // Add dividers after the first item and before the last item.
                if ($idx == 1 || $idx == $navitemcount - 1) {
                    $am->add($divider);
                }
            }
        }

        return html_writer::div(
            $this->render($am),
            $usermenuclasses
        );
    }

}
