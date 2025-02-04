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

defined('MOODLE_INTERNAL') || die();

function xmldb_groupexchange_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager(); // Loads ddl manager and xmldb classes.

    if ($oldversion < 2012120300) {

        // Define field completionsubmit to be added to choicegroup.
        $table = new xmldb_table('groupexchange');
        $field = new xmldb_field('studentsonly', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'limitexchanges');

        // Conditionally launch add field completionsubmit.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Choicegroup savepoint reached.
        upgrade_mod_savepoint(true, 2012120300, 'groupexchange');
    }

    return true;
}


