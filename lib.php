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
 *
 * @package groupexchange
 * @copyright 2012 onwards Olexandr Savchuk
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/group/lib.php');

/**------------------------------------------------------------------------------------------
 * Standard functions
 * ------------------------------------------------------------------------------------------
 */

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @global object
 * @param object $addhandler
 *            ad
 * @return int
 */
function groupexchange_add_instance($groupexchange) {
    global $DB;

    $groupexchange->timemodified = time();

    if (empty($groupexchange->timerestrict)) {
        $groupexchange->timeopen = 0;
        $groupexchange->timeclose = 0;
    }

    $groupexchange->id = $DB->insert_record("groupexchange", $groupexchange);

    foreach ($groupexchange->group as $groupid => $value) {
        if ($value == 1) {
            $option = new stdClass();
            $option->groupexchange = $groupexchange->id;
            $option->groupid = $groupid;
            $DB->insert_record("groupexchange_groups", $option);
        }
    }

    return $groupexchange->id;
}

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @global object
 * @param object $groupexchange
 * @return bool
 */
function groupexchange_update_instance($groupexchange) {
    global $DB;

    $groupexchange->id = $groupexchange->instance;
    $groupexchange->timemodified = time();

    if (empty($groupexchange->timerestrict)) {
        $groupexchange->timeopen = 0;
        $groupexchange->timeclose = 0;
    }

    // update, delete or insert accepted groups
    $DB->delete_records("groupexchange_groups", array (
            'groupexchange' => $groupexchange->id
    ));
    foreach ($groupexchange->group as $groupid => $value) {
        if ($value == 1) {
            $option = new stdClass();
            $option->groupexchange = $groupexchange->id;
            $option->groupid = $groupid;
            $DB->insert_record("groupexchange_groups", $option);
        }
    }

    return $DB->update_record('groupexchange', $groupexchange);
}

/**
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @global object
 * @param int $id
 * @return bool
 */
function groupexchange_delete_instance($id) {
    global $DB;

    if (! $groupexchange = $DB->get_record("groupexchange", array (
            "id" => "$id"
    ))) {
        return false;
    }

    $result = true;

    if (! $DB->delete_records("groupexchange_groups", array (
            "groupexchange" => "$groupexchange->id"
    ))) {
        $result = false;
    }

    $offers = $DB->get_records('groupexchange_offers', array (
            'groupexchange' => "$groupexchange->id"
    ));
    foreach ($offers as $offer) {
        $DB->delete_records("groupexchange_offers_groups", array (
                "offerid" => "$offer->id"
        ));
    }

    if (! $DB->delete_records("groupexchange_offers", array (
            "groupexchange" => "$groupexchange->id"
    ))) {
        $result = false;
    }

    if (! $DB->delete_records("groupexchange", array (
            "id" => "$groupexchange->id"
    ))) {
        $result = false;
    }

    return $result;
}

// ------------------------------------------------------------------------------------------
// Internal functions
// ------------------------------------------------------------------------------------------
function groupexchange_get_instance($id) {
    global $DB, $USER, $_groupexchange_groupmembership_cache;

    $groupexchange = $DB->get_record('groupexchange', array (
            'id' => $id
    ));

    // Get groups data.
    $groupexchange->groups = array ();
    $groups = $DB->get_records_sql('select g.* from
										{groupexchange_groups} gg,
										{groups} g
									where gg.groupexchange = ?
										and g.id = gg.groupid
									order by g.name asc', array (
            $id
    ));
    foreach ($groups as $group) {
        $groupexchange->groups [$group->id] = $group;
    }

    // Get active offers data.
    $groupexchange->offers = array ();
    $offers = $DB->get_records_sql('select
										o.*,
										u.firstname,
										u.lastname
									from
										{groupexchange_offers} o,
										{user} u
									where o.groupexchange = ?
										and o.accepted_by = 0
										and u.id = o.userid
									order by o.time_submitted asc', array (
            $id
    ));
    foreach ($offers as $offer) {
        $offer->groups = array ();
        $groups = $DB->get_records('groupexchange_offers_groups', array (
                'offerid' => $offer->id
        ));
        // Attach full group info to each group in offer.
        foreach ($groups as $group) {
            $offer->groups [$group->groupid] = $groupexchange->groups [$group->groupid];
        }
        $offer->group = $groupexchange->groups [$offer->group_offered];
        $groupexchange->offers [] = $offer;
    }

    // Sort offers. user's offer first, acceptable offers next, all others afterwards.
    $_groupexchange_groupmembership_cache = groupexchange_get_user_groups($USER);
    usort($groupexchange->offers, "groupexchange_compare_offers");

    return $groupexchange;
}

/**
 * Comparator for user-defined sort above
 */
function groupexchange_compare_offers($a, $b) {
    global $USER, $_groupexchange_groupmembership_cache;

    // User's own offers come first.
    if ($a->userid == $USER->id && $b->userid != $USER->id) {
        return - 1;
    } else if ($a->userid != $USER->id && $b->userid == $USER->id) {
        return 1;
    }

    // Acceptable offers come next.
    if (groupexchange_offer_acceptable($a, $_groupexchange_groupmembership_cache) &&
        !groupexchange_offer_acceptable($b, $_groupexchange_groupmembership_cache)) {
        return - 1;
    } else if (! groupexchange_offer_acceptable($a, $_groupexchange_groupmembership_cache) &&
                 groupexchange_offer_acceptable($b, $_groupexchange_groupmembership_cache)) {
        return 1;
    }

    // Otherwise equal offers are sorted by time submitted, oldest first.
    return ($a->time_submitted < $b->time_submitted) ? - 1 : (($a->time_submitted > $b->time_submitted) ? 1 : 0);
}

function groupexchange_get_offer($offerid) {
    global $DB;

    $offer = $DB->get_record_sql('select
										o.*,
										u.firstname,
										u.lastname
									from
										{groupexchange_offers} o,
										{user} u
									where o.id = ?
										and u.id = o.userid
									order by o.time_submitted asc', array (
            $offerid
    ));
    $groups = $DB->get_records_sql('select g.* from
										{groupexchange_offers_groups} og,
										{groups} g
									where og.offerid = ?
										and g.id = og.groupid
									order by g.name asc', array (
            $offerid
    ));
    foreach ($groups as $group) {
        $offer->groups [$group->id] = $group;
    }

    $offer->group = $DB->get_record('groups', array (
            'id' => $offer->group_offered
    ));

    return $offer;
}

function groupexchange_get_user_groups($user) {
    global $DB;
    $dbgroups = $DB->get_records('groups_members', array (
            'userid' => $user->id
    ));
    $groupmembership = array ();
    foreach ($dbgroups as $m) {
        $groupmembership [] = $m->groupid;
    }
    return $groupmembership;
}

$_groupexchange_user_eligible = - 1;

/**
 * Check if the user is eligible to partake in the group exchange
 */
function groupexchange_is_user_eligible($exchange) {
    if (isset($exchange->studentsonly)) {
        return true;
    }

    global $_groupexchange_user_eligible;
    if ($_groupexchange_user_eligible !== - 1) {
        return $_groupexchange_user_eligible;
    }

    global $USER;

    // Check that the user has student role in the course.
    $context = \context_course::instance($exchange->course);
    $roles = get_user_roles($context, $USER->id, false);
    foreach ($roles as $role) {
        if ($role->shortname == 'student') {
            $_groupexchange_user_eligible = true;
            return true;
        }
    }

    $_groupexchange_user_eligible = false;
    return false;
}

/**
 * Returns true if the currently logged in user can accept the given exchange offer
 */
function groupexchange_offer_acceptable($offer, $groupmembership = null) {
    global $DB, $USER;

    if ($USER->id == $offer->userid) {
        return false;
    }

    if ($groupmembership === null) {
        $groupmembership = groupexchange_get_user_groups($USER);
    }

    foreach ($offer->groups as $groupid => $group) {
        if (in_array($groupid, $groupmembership)) {
            return true;
        }
    }

    return false;
}

/**
 * Given submitted "create offer" form data, check if there is a standing offer satisfying the conditions.
 * Return the found offer array or empty array
 */
function groupexchange_find_offer($exchange, $offergroup, $requestgroups) {
    global $DB;

    // Find fitting offers.
    $placeholders = array ();
    $arguments = array ();
    $arguments [] = $exchange->id;
    foreach ($requestgroups as $key => $x) {
        $placeholders [] = '?';
        $arguments [] = $key;
    }
    $arguments [] = $offergroup;

    $offers = $DB->get_records_sql('select
			o.id
		from
			{groupexchange_offers} o
		where
			o.groupexchange = ? and
			o.accepted_by = 0 and
			o.group_offered in (' . implode(',', $placeholders) . ') and
			exists(select * from {groupexchange_offers_groups} g
					where g.offerid = o.id
					and g.groupid = ?)', $arguments);

    $results = array ();

    // Fetch full offer information for found offers.
    foreach ($offers as $offer) {
        $results [] = groupexchange_get_offer($offer->id);
    }

    return $results;
}

/**
 * Creates a new offer from submitted form data
 */
function groupexchange_create_offer($exchange, $offergroup, $requestgroups) {
    global $DB, $USER;

    $offer = new stdClass();
    $offer->groupexchange = $exchange->id;
    $offer->userid = $USER->id;
    $offer->time_submitted = time();
    $offer->group_offered = $offergroup;

    $offer->id = $DB->insert_record('groupexchange_offers', $offer);

    foreach ($requestgroups as $groupid => $x) {
        $obj = new stdClass();
        $obj->offerid = $offer->id;
        $obj->groupid = $groupid;
        $DB->insert_record('groupexchange_offers_groups', $obj);
    }

    return $offer->id;
}
function groupexchange_delete_offer($offerid, $force = false) {
    global $DB, $USER;

    if (! $force && ! $DB->record_exists('groupexchange_offers', array (
            'id' => $offerid,
            'userid' => $USER->id
    ))) {
        return false;
    }

    if (! $DB->delete_records('groupexchange_offers_groups', array (
            'offerid' => $offerid
    ))) {
        return false;
    }

    return $DB->delete_records('groupexchange_offers', array (
            'id' => $offerid
    ));
}

/**
 * If possible, accepts the given offer (with the logged in user).
 *
 * Switches logged in user and offer author between groups
 * Deactivates the offer
 * Sends out email confirmations to both users
 */
function groupexchange_accept_offer($exchange, $offer, $oldgroupid, $course) {
    global $DB, $USER;

    // Prepare data.
    $offerer = $offer->userid;
    $offereroldgroup = $offer->group_offered;
    $offerernewgroup = $oldgroupid;

    $accepter = $USER->id;
    $accepteroldgroup = $oldgroupid;
    $accepternewgroup = $offer->group_offered;

    // Remove standing offers by accepting user.
    $offers = $DB->get_records('groupexchange_offers', array (
            'userid' => $accepter,
            'groupexchange' => $exchange->id
    ));
    foreach ($offers as $_offer) {
        groupexchange_delete_offer($_offer->id, true);
    }

    // Update standing offer.
    $offer->accepted_by = $accepter;
    $offer->accepted_groupid = $accepteroldgroup;
    $DB->update_record('groupexchange_offers', $offer);

    // remove users from old groups.
    groups_remove_member($offereroldgroup, $offerer);
    groups_remove_member($accepteroldgroup, $accepter);

    // Assign users to new groups.
    groups_add_member($offerernewgroup, $offerer);
    groups_add_member($accepternewgroup, $accepter);

    // Notify the offerer about the exchange.
    $eventdata = new \core\message\message();
    $eventdata->component = 'mod_groupexchange';
    $eventdata->name = 'offer_accepted';
    $eventdata->userfrom = $USER;
    $eventdata->userto = $DB->get_record('user', array (
            'id' => $offerer
    ));
    $eventdata->subject = get_string('email_subject', 'groupexchange', array (
            'course' => $course->fullname,
            'exchange' => $exchange->name
    ));
    $eventdata->fullmessage = get_string('email_body', 'groupexchange', array (
            'user' => $USER->firstname . ' ' . $USER->lastname,
            'course' => $course->fullname,
            'exchange' => $exchange->name,
            'groupfrom' => $exchange->groups [$offereroldgroup]->name,
            'groupto' => $exchange->groups [$offerernewgroup]->name
    ));
    $eventdata->fullmessageformat = FORMAT_PLAIN; // text format
    $eventdata->fullmessagehtml = $eventdata->fullmessage;
    $eventdata->smallmessage = $eventdata->fullmessage;
    $eventdata->notification = 1;

    message_send($eventdata);
}

/**
 * Moodle event handler for group deletion.
 * Remove all offers offering this group, offer acceptances accepting this group,
 * and all group availabilities in exchanges
 */
function groupexchange_eventhandler_groupdelete($group) {
    global $DB;

    // Offer acceptances.
    $DB->delete_records('groupexchange_offers_groups', array (
            'groupid' => $group->id
    ));

    // Unaccepted offers.
    $offers = $DB->get_records('groupexchange_offers', array (
            'group_offered' => $group->id,
            'accepted_by' => 0
    ));
    foreach ($offers as $offer) {
        groupexchange_delete_offer($offer->id, true);
        // add_to_log($course->id, "groupexchange", "delete offer", "view.php?id=$cm->id&offer=$offer->id", $exchange->id, $cm->id);
    }

    // Group availabilities.
    $DB->delete_records('groupexchange_groups', array (
            'groupid' => $group->id
    ));
}

/**
 * Moodle event handler for user group removal.
 * Remove all unaccepted offers by this user offering this group for exchange.
 */
function groupexchange_eventhandler_memberremove($event) {
    global $DB;

    $offers = $DB->get_records('groupexchange_offers', array (
            'userid' => $event->userid,
            'group_offered' => $event->groupid,
            'accepted_by' => 0
    ));
    foreach ($offers as $offer) {
        groupexchange_delete_offer($offer->id, true);
        // add_to_log($course->id, "groupexchange", "delete offer", "view.php?id=$cm->id&offer=$offer->id", $exchange->id, $cm->id);
    }
}
