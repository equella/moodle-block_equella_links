<?php

// This file is part of the EQUELLA Moodle Integration - https://github.com/equella/moodle-block-tasks
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

require_once($CFG->dirroot.'/mod/equella/common/lib.php');

class block_equella_links extends block_list {

    function init() {
        $this->title = get_string('pluginname', 'block_equella_links');
    }

    function get_content() {
        global $CFG, $COURSE, $SESSION;

        if( $this->content !== NULL ) {
            return $this->content;
        }

        if( empty($this->instance) || !isloggedin() || isguestuser() ) {
            return null;
        }

        if (empty($this->instance->pageid)) { // sticky
            if (!empty($COURSE)) {
                $this->instance->pageid = $COURSE->id;
            }
        }

        $items = array();
        $items[]= '<a target="_blank" href="'.equella_appendtoken(equella_full_url('/access/search.do')).'">Search</a>';
        $items[]= '<a target="_blank" href="'.equella_appendtoken(equella_full_url('/access/contribute.do')).'">Contribute</a>';
        $items[]= '<a target="_blank" href="'.equella_appendtoken(equella_full_url('/access/myresources.do')).'">My Resources</a>';
        
        $this->content = new stdClass;
        $this->content->items = $items;
        $this->content->icons = array();
        $this->content->footer = '';

        return $this->content;
    }
}
