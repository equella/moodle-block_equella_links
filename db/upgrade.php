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

function xmldb_block_equella_links_upgrade($oldversion, $block) {
    global $DB, $USER;

    $dbman = $DB->get_manager();

    if ($oldversion < 2013112805) {

        // Define table block_equella_links to be created
        $table = new xmldb_table('block_equella_links');

        // Adding fields to table block_equella_links
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('url', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('contextid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('title', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table block_equella_links
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for block_equella_links
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // equella_links savepoint reached
        upgrade_block_savepoint(true, 2013112805, 'equella_links');
    }
    if ($oldversion < 2013112806) {
        $syscontext = get_system_context();
        $contextid = $syscontext->id;
        $link = new stdClass;
        $link->contextid = $contextid;
        $link->userid = $USER->id;
        $link->url = '/access/search.do';
        $link->title = 'Search';
        $DB->insert_record('block_equella_links', $link);
        $link->url = '/access/contribute.do';
        $link->title = 'Contribute';
        $DB->insert_record('block_equella_links', $link);
        $link->url = '/access/myresources.do';
        $link->title = 'My Resources';
        $DB->insert_record('block_equella_links', $link);
        // equella_links savepoint reached
        upgrade_block_savepoint(true, 2013112806, 'equella_links');
    }

    if ($oldversion < 2015061101) {
        // Define field created to be added to block_equella_links.
        $table = new xmldb_table('block_equella_links');
        $field = new xmldb_field('created', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'contextid');

        // Conditionally launch add field created.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('tagged', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'created');

        // Conditionally launch add field tagged.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field itemxml to be added to block_equella_links.
        $table = new xmldb_table('block_equella_links');
        $field = new xmldb_field('itemxml', XMLDB_TYPE_TEXT, null, null, null, null, null, 'contextid');

        // Conditionally launch add field itemxml.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Equella_links savepoint reached.
        upgrade_block_savepoint(true, 2015061101, 'equella_links');
    }
    return true;
}
