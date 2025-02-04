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
 * The mod_forum instance list viewed event.
 *
 * @package    mod_groupexchange
 * @copyright  2017 Guido Roessling <roessling@acm.org>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_groupexchange\event;

defined('MOODLE_INTERNAL') || die();

/**
 * The mod_forum instance list viewed event class.
 *
 * @package    mod_groupexchange
 * @since      Moodle 3.3
 * @copyright  2017 Guido Roessling <roessling@acm.org>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_module_instance_list_viewed extends \core\event\course_module_instance_list_viewed {
    // No need for any code here as everything is handled by the parent class.
}
