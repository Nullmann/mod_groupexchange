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
 * The mod_groupexchange post created event.
 *
 * @package    mod_groupexchange
 * @copyright  2017 Guido Roessling <roessling@acm.org>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_groupexchange\event;

defined('MOODLE_INTERNAL') || die();

/**
 * The mod_groupexchange post created event class.
 *
 * @package    mod_groupexchange
 * @since      Moodle 3.3
 * @copyright  2017 Guido Roessling <roessling@acm.org>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class exchange_removed extends \core\event\base {
    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        $this->data['crud'] = 'd';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'groupexchange';
    }

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        $a = new \stdClass();
        $a->userid = $this->userid;
        $a->contextinstanceid = $this->contextinstanceid;
        return get_string('event:removed_desc', 'mod_groupexchange', $a);
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('event:removed', 'mod_groupexchange');
    }

    /**
     * Get URL related to the action
     *
     * @return \moodle_url
     */
    public function get_url() {
        return new \moodle_url('/mod/groupexchange/view.php', array('id' => $this->contextinstanceid));
    }

    /**
     * Return the legacy event log data.
     *
     * @return array|null
     */
    protected function get_legacy_logdata() {
        // The legacy log table expects a relative path to /mod/groupexchange/.
        $logurl = substr($this->get_url()->out_as_local_url(), strlen('/mod/groupexchange/'));

        return array($this->courseid, 'groupexchange', 'choice removed', $logurl, $this->objectid, $this->contextinstanceid);
    }


}

