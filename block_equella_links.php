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
require_once($CFG->dirroot.'/mod/equella/lib.php');
require_once($CFG->dirroot.'/mod/equella/common/soap.php');
require_once($CFG->libdir.'/blocklib.php');
require_once($CFG->dirroot.'/blocks/moodleblock.class.php');

class block_equella_links extends block_list {
    function has_config() {
        return true;
    }

    public function instance_allow_config() {
        return true;
    }

    function init() {
        $this->title = get_string('pluginname', 'block_equella_links');
    }

    function instance_allow_multiple() {
        return false;
    }
    function instance_config_save($data, $nolongerused = false) {
        global $DB, $USER;

        $course = $this->page->course;
        $idnumber = $course->idnumber;
        $context = context_course::instance($course->id);
        $contextid = $context->id;
        $DB->delete_records('block_equella_links', array('contextid'=>$contextid, 'tagged'=>1));
        if (!empty($data->includetaggeditems) && !empty($idnumber)) {
            $xml = self::grab_tagged_items(filter_var($data->xmlpath, FILTER_SANITIZE_STRING), $idnumber);
            self::update_records_from_xml($xml, $this->instance->id, $contextid);
        }

        parent::instance_config_save($data, $nolongerused);
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

        $context = context_course::instance($COURSE->id);
        $contextid = $context->id;
        $links = $DB->get_records('block_equella_links', array('contextid'=>$contextid));
        $items = array();
        foreach ($links as $link) {
            if (!filter_var($link->url, FILTER_VALIDATE_URL) === false) {
                $url = new moodle_url(equella_appendtoken($link->url));
            } else {
                $url = new moodle_url(equella_appendtoken(equella_full_url(ltrim($link->url, '/'))));
            }
            $items[] = html_writer::link($url, $link->title, array('target'=>'_blank'));
        }

        $this->content = new stdClass;
        $this->content->items = $items;
        $this->content->icons = array();

        if (has_any_capability(array('block/equella_links:manageanylinks'), $this->context)) {
            $url = new moodle_url('/blocks/equella_links/managelinks.php', array('courseid'=>$this->page->course->id, 'sesskey' => sesskey()));
            $this->content->footer = $OUTPUT->action_icon($url, new pix_icon('t/edit', get_string('edit')));
        }

        return $this->content;
    }
    static function grab_tagged_items($xmlpath, $idnumber) {
        $eq = new EQUELLA(equella_soap_endpoint());
        $where = "where $xmlpath is '$idnumber'";
        $sorttype = 0;
        $q = null;
        return $eq->searchItems($q, null, $where, 1, $sorttype, false, '', 20);
    }
    static function update_records_from_xml($xml, $blockid, $contextid) {
        global $DB;
        foreach ($xml->nodeList('/results/result/xml') as $el)
        {
            $record = new stdclass;
            $record->url = $xml->nodeValue('item/url', $el);
            $record->title = $xml->nodeValue('item/name', $el);
            $record->blockid = $blockid;
            $record->contextid = $contextid;
            $record->tagged = 1;
            $record->created = time();
            $record->itemxml = $el->ownerDocument->saveXML($el);
            $DB->insert_record('block_equella_links', $record);
        }
    }
    public function cron() {
        global $CFG, $DB;
        $blockname = 'equella_links';
        $blocks = $DB->get_recordset('block_instances', array('blockname'=>$blockname));
        foreach($blocks as $record) {
            $block = block_instance($blockname, $record);
            $blockid = $record->id;
            $coursecontext = $block->context->get_course_context();
            $contextid = $coursecontext->id;
            $courseid = $coursecontext->instanceid;
            $idnumber = equella_get_coursecode($courseid);
            if (!empty($block->config->includetaggeditems)) {
                $DB->delete_records('block_equella_links', array('contextid'=>$contextid, 'tagged'=>1));
                $xmlpath = $block->config->xmlpath;
                $xml = self::grab_tagged_items($xmlpath, $idnumber);
                self::update_records_from_xml($xml, $blockid, $contextid);
            }
        }
        return true;
    }

}
