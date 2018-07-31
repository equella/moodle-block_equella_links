<?php

require_once(dirname(__FILE__) . '/../../config.php');
require_once(dirname(__FILE__) . '/link_edit_form.php');
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/tablelib.php');

$courseid = required_param('courseid', PARAM_INT);
$action = optional_param('action', '', PARAM_ALPHA);
$linkid = optional_param('linkid', '', PARAM_INT);

$baseurl = new moodle_url('/blocks/equella_links/managelinks.php', array('courseid'=>$courseid, 'sesskey' => sesskey()));
$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$context = context_course::instance($courseid);

$PAGE->set_course($course);
$PAGE->set_url($baseurl);
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('linksaddedit', 'block_equella_links'));

require_capability('block/equella_links:manageanylinks', $context);
require_sesskey();

if (!empty($action)) {
    $link = $DB->get_record('block_equella_links', array('id' => $linkid, 'contextid'=>$context->id), '*', MUST_EXIST);
    if ($action == 'edit') {
        $mform = new equella_links_edit_form($PAGE->url, false);
        $mform->set_data(array('title'=>$link->title, 'url'=>$link->url, 'linkid'=>$link->id, 'contextid'=>$context->id));
    } else if ($action == 'delete') {
        $DB->delete_records('block_equella_links', array('id' => $linkid));
        // we are done here
        redirect($baseurl);
    }
} else {
    $mform = new equella_links_edit_form($PAGE->url, true);
}

if ($mform->is_cancelled()) {
    redirect($baseurl);
}

if ($formdata = $mform->get_data()) {
    if (!empty($formdata->linkid)) {
        $editinglink = new stdClass;
        $editinglink->id = $formdata->linkid;
        $editinglink->title = $formdata->title;
        $editinglink->url   = $formdata->url
        $editinglink->contextid  = $context->id;
        $DB->update_record('block_equella_links', $editinglink);
    } else {
        $addinglink = new stdClass;
        $addinglink->title = $formdata->title;
        $addinglink->url   = $formdata->url;
        $addinglink->created = time();
        $addinglink->contextid  = $context->id;
        $DB->insert_record('block_equella_links', $addinglink);
    }
    redirect($baseurl);
}


echo $OUTPUT->header();

$table = new flexible_table('equella-links-display');
$table->define_columns(array('link', 'title', 'actions'));
$table->define_headers(array(get_string('equellaurl', 'block_equella_links'), get_string('linktitle', 'block_equella_links'), get_string('actions', 'moodle')));
$table->define_baseurl($baseurl);
$table->set_attribute('cellspacing', '0');
$table->set_attribute('class', 'generaltable generalbox');

$table->setup();
$links = $DB->get_records('block_equella_links', array('contextid'=>$context->id));
foreach ($links as $link) {
    $editurl = new moodle_url('/blocks/equella_links/managelinks.php', array('linkid'=>$link->id, 'action'=>'edit', 'courseid'=>$courseid, 'sesskey' => sesskey()));
    $editaction = $OUTPUT->action_icon($editurl, new pix_icon('t/edit', get_string('edit')));

    $deleteurl = new moodle_url('/blocks/equella_links/managelinks.php', array('linkid'=>$link->id, 'action'=>'delete', 'courseid'=>$courseid, 'sesskey' => sesskey()));
    $deleteicon = new pix_icon('t/delete', get_string('delete'));
    $deleteaction = $OUTPUT->action_icon($deleteurl, $deleteicon, new confirm_action(get_string('deletelinkconfirm', 'block_equella_links')));
    $action = $editaction . ' ' . $deleteaction;
    $table->add_data(array($link->url, $link->title, $action));
}

$table->print_html();

$mform->display();

echo $OUTPUT->footer();
