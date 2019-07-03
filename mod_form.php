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
 * Version information
 *
 * @package mod
 * @subpackage groupreg
 * @copyright 2013-2016 TU Darmstadt
 * @author Olexandr Savchuck, Guido Roessling <roessling@acm.org>, Benedikt Schneider (@Nullmann)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/moodleform_mod.php');

class mod_groupexchange_mod_form extends moodleform_mod {
    public function definition() {
        global $CFG, $DB, $COURSE;

        $mform = & $this->_form;

        // New section.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('groupexchangename', 'groupexchange'), array (
                'size' => '64'
        ));
        if (! empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');

        $this->standard_intro_elements();

        $mform->addElement('hidden', 'assigned', '', '0');
        $mform->setType('assigned', PARAM_BOOL);

        // New section.
        $mform->addElement('header', 'groupshdr', get_string('choose_groups', 'groupexchange'));
        // $mform->addHelpButton('groupshdr', 'choose_groups', 'groupexchange');
        $mform->addElement('static', 'groups', '', get_string('choose_groups_help', 'groupexchange'));

        $this->add_checkbox_controller(1, get_string("checkallornone", 'groupexchange'));
        $dbgroups = $DB->get_records('groups', array (
                'courseid' => $COURSE->id
        ), 'name');
        foreach ($dbgroups as $group) {
            $mform->addElement('advcheckbox', 'group[' . $group->id . ']', '', $group->name, array (
                    'group' => 1
            ));
        }

        // New section.
        $mform->addElement('header', 'miscellaneoussettingshdr', get_string('miscellaneoussettings', 'form'));

        $mform->addElement('advcheckbox', 'anonymous', get_string('setting_anonymous', 'groupexchange'));
        $mform->addHelpButton('anonymous', 'setting_anonymous', 'groupexchange');

        $mform->addElement('advcheckbox', 'studentsonly', get_string('setting_students_only', 'groupexchange'));
        $mform->addHelpButton('studentsonly', 'setting_students_only', 'groupexchange');

        $limitoptions = array (
                0 => get_string('unlimited', 'groupexchange'),
                1,
                2,
                3,
                4,
                5,
                6,
                7,
                8,
                9,
                10
        );
        $mform->addElement('hidden', 'limitexchanges', 0);
        $mform->setType('limitexchanges', PARAM_BOOL);
        // $mform->addHelpButton('limitexchanges', 'setting_limitexchanges', 'groupexchange');

        // New section.
        $mform->addElement('header', 'timerestricthdr', get_string('timerestrict', 'groupexchange'));
        $mform->addElement('checkbox', 'timerestrict', get_string('timerestrict', 'groupexchange'));

        $mform->addElement('date_time_selector', 'timeopen', get_string("groupexchangeopen", "groupexchange"));
        $mform->disabledIf('timeopen', 'timerestrict');

        $mform->addElement('date_time_selector', 'timeclose', get_string("groupexchangeclose", "groupexchange"));
        $mform->disabledIf('timeclose', 'timerestrict');

        // Add features.
        $features = new stdclass();
        $features->groups = false;
        $features->groupings = false;
        $features->groupmembersonly = true;
        $this->standard_coursemodule_elements($features);

        $this->add_action_buttons();
    }
    public function data_preprocessing(&$defaultvalues) {
        global $DB;

        if (! empty($this->_instance) && $groups = $DB->get_records('groupexchange_groups', array (
                'groupexchange' => $this->_instance
        ))) {
            foreach ($groups as $group) {
                $defaultvalues ['group'] [$group->groupid] = 1;
            }
        }

        if (empty($defaultvalues ['timeopen'])) {
            $defaultvalues ['timerestrict'] = 0;
        } else {
            $defaultvalues ['timerestrict'] = 1;
        }
    }
    public function validation($data, $files) {
        global $USER, $COURSE;
        $errors = parent::validation($data, $files);

        // Ensure at least group is selected.
        $choices = 0;
        foreach ($data ['group'] as $group) {
            if ($group == '1') {
                $choices ++;
            }
        }

        if ($choices < 2) {
            $errors ['groups'] = get_string('error_notenoughgroups', 'groupexchange');
        }

        return $errors;
    }
}

