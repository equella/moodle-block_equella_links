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

    function instance_allow_multiple() {
        return false;
    }

    function get_content() {
        global $CFG, $COURSE, $OUTPUT, $DB;

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
        $links = $DB->get_records('block_equella_links');
        $items = array();
        foreach ($links as $link) {
            $items[] = html_writer::link(new moodle_url(equella_appendtoken(equella_full_url(ltrim($link->url, '/')))), $link->title, array('target'=>'_blank'));
        }

        $this->content = new stdClass;
        $this->content->items = $items;
        $this->content->icons = array();

        if (has_any_capability(array('block/equella_links:manageanylinks'), $this->context)) {
            $url = new moodle_url('/blocks/equella_links/managelinks.php', array('courseid'=>$this->page->course->id));
            $this->content->footer = $OUTPUT->action_icon($url, new pix_icon('t/edit', get_string('edit')));
        }

        return $this->content;
    }

    function instance_allow_config() {
        return true;
    }
}
