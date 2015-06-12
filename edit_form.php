<?php

class block_equella_links_edit_form extends block_edit_form {
    protected function specific_definition($mform) {
        global $CFG, $DB, $USER;
        $mform->addElement('header', 'searchitems', 'Find tagged items');
        $strrequired = get_string('required');

        $mform->addElement('checkbox', 'config_includetaggeditems', get_string('configincludetaggeditems', 'block_equella_links'));
        $mform->setType('config_includetaggeditems', PARAM_BOOL);
        
        $mform->addElement('text', 'config_xmlpath', get_string('configxmlpath', 'block_equella_links'));
        $mform->setType('config_xmlpath', PARAM_PATH);
        $mform->setDefault('config_xmlpath', '/xml/integration/moodlecourseidnumber');
    }
}
