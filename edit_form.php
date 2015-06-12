<?php

class block_equella_links_edit_form extends block_edit_form {
    protected function specific_definition($mform) {
        global $CFG, $DB, $USER;
        $mform->addElement('header', 'searchitems', 'Find tagged items');
        $strrequired = get_string('required');

        $options = array(0 => get_string('no'), 1 => get_string('yes'));
        $mform->addElement('select', 'config_includetaggeditems',
            get_string('configincludetaggeditems', 'block_equella_links'),
            $options
        );
        $mform->setType('config_includetaggeditems', PARAM_BOOL);

        $mform->addElement('text', 'config_xmlpath', get_string('configxmlpath', 'block_equella_links'));
        $mform->setType('config_xmlpath', PARAM_PATH);
        $mform->setDefault('config_xmlpath', '/xml/integration/moodlecourseidnumber');
    }
}
