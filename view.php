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
 * @package    mod
 * @subpackage groupreg
 * @copyright  2013-2016 TU Darmstadt
 * @author     Olexandr Savchuck, Guido Roessling <roessling@acm.org>, Benedikt Schneider (@Nullmann)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../../config.php");
require_once("lib.php");
require_once($CFG->dirroot . '/group/lib.php');

$id = required_param('id', PARAM_INT); // Course Module ID.
$action = optional_param('action', 'view', PARAM_ALPHA);
$attemptids = optional_param('attemptid', array (), PARAM_INT); // Array of attempt ids for delete action.

$url = new moodle_url('/mod/groupexchange/view.php', array (
        'id' => $id
));
if ($action !== 'view') {
    $url->param('action', $action);
}

$PAGE->set_url($url);

/*
 * Handling errors of misconfiguration and course access
 */
if (! $cm = get_coursemodule_from_id('groupexchange', $id)) {
    print_error('invalidcoursemodule');
}

if (! $course = $DB->get_record("course", array (
        "id" => $cm->course
))) {
    print_error('coursemisconf');
}

require_course_login($course, false, $cm);

if (! $exchange = groupexchange_get_instance($cm->instance)) {
    print_error('invalidcoursemodule');
}

if (! $context = context_module::instance($cm->id)) { // get_context_instance(CONTEXT_MODULE, $cm->id)) {
    print_error('badcontext');
}

$groupmemberships = groupexchange_get_user_groups($USER);

$PAGE->set_title(format_string($exchange->name));
$PAGE->set_heading($course->fullname);

$errors = array ();

echo $OUTPUT->header();

$renderer = $PAGE->get_renderer('mod_groupexchange');
echo $renderer->show_styles();

echo '<div class="clearer"></div>';

if ($exchange->intro) {
    echo $OUTPUT->box(format_module_intro('groupexchange', $exchange, $cm->id), 'generalbox', 'intro');
}

$groupexchangeopen = true;
$timenow = time();
if ($exchange->timeclose != 0) {
    if ($exchange->timeopen > $timenow) {
        echo $OUTPUT->box(get_string("notopenyet", "groupexchange", userdate($exchange->timeopen)), "generalbox notopenyet");
        echo $OUTPUT->footer();
        exit();
    } else if ($timenow > $exchange->timeclose) {
        echo $OUTPUT->box(get_string("expired", "groupexchange", userdate($exchange->timeclose)), "generalbox expired");
        echo $OUTPUT->footer();
        exit();
    }
}

/*
 * Creating a new offer
 */
if ($action == 'offer') {
    // Get input.
    $offergroup = optional_param('offer_group', 0, PARAM_INT);
    $requestgroup = optional_param_array('request_group', array (), PARAM_RAW);

    // Validate input.
    if (! in_array($offergroup, $groupmemberships) || ! isset($exchange->groups [$offergroup])) {
        $errors ['offer_group'] = get_string('error_offer_group', 'groupexchange');
    }
    if (count($requestgroup) < 1) {
        $errors ['request_group'] = get_string('error_request_group_not_enough', 'groupexchange');
    }
    foreach ($requestgroup as $groupid => $x) {
        if (! isset($exchange->groups [$groupid]) || in_array($groupid, $groupmemberships)) {
            $errors ['request_group'] = get_string('error_request_group_bad', 'groupexchange') . ": " . $groupid;
        }
    }

    // Check if there's a standing offer by the user already, offering this group.
    if ($DB->record_exists('groupexchange_offers', array (
            'userid' => $USER->id,
            'group_offered' => $offergroup
    ))) {
        echo $OUTPUT->notification(get_string('error_double_offer', 'groupexchange'), 'notifyproblem');
        $errors ['offer_group'] = get_string('error_double_offer', 'groupexchange');
        $action = 'view';
    }

    if (! $errors) {

        // Show potential fitting offers, let user accept one or ignore them and create his offer.
        $ignoreoffers = optional_param('ignore_offers', 0, PARAM_INT);
        if ($ignoreoffers == 0) {
            $offers = groupexchange_find_offer($exchange, $offergroup, $requestgroup);
            if (! empty($offers)) {
                echo '<i>' . get_string("fitting_offers", "groupexchange") . '</i>';
                echo $renderer->show_offers($cm, $exchange, $offers);
                echo $renderer->show_ignore_form($cm, $offergroup, $requestgroup);
                echo $OUTPUT->footer();
                exit();
            }
        }

        // Finally, create new offer.
        $offerid = groupexchange_create_offer($exchange, $offergroup, $requestgroup);
        // GR should be even
        // add_to_log($course->id, "groupexchange", "create offer", "view.php?id=$cm->id&offer=$offerid", $exchange->id, $cm->id);
        echo $OUTPUT->notification(get_string('offer_created', 'groupexchange'), 'notifysuccess');
        // Reload the exchange object.
        $exchange = groupexchange_get_instance($cm->instance);
    }
}

/*
 * Deleting an offer
 */
if ($action == 'delete') {
    $deleteoffer = optional_param('offer', 0, PARAM_INT);

    if (groupexchange_delete_offer($deleteoffer)) {
        // GR should be event.
        // add_to_log($course->id, "groupexchange", "delete offer", "view.php?id=$cm->id&offer=$delete_offer", $exchange->id, $cm->id);
        echo $OUTPUT->notification(get_string('offer_deleted', 'groupexchange'), 'notifysuccess');
        // Reload the exchange object.
        $exchange = groupexchange_get_instance($cm->instance);
    } else {
        echo $OUTPUT->notification(get_string('error_delete_offer', 'groupexchange'), 'notifyproblem');
    }

    $action = 'view';
}

/*
 * Accepting an offer
 */
if ($action == 'accept') {
    $acceptoffer = optional_param('offer', 0, PARAM_INT);
    $offer = null;

    // Validate input.
    if (!$DB->record_exists('groupexchange_offers', array (
            'id' => $acceptoffer,
            'groupexchange' => $exchange->id
    ))) {
        $errors [] = get_string('offer_doesnt_exist', 'groupexchange');
    } else {
        $offer = groupexchange_get_offer($acceptoffer);
        if ($offer->userid == $USER->id || in_array($offer->group_offered, $groupmemberships)) {
            $errors [] = get_string('not_acceptable', 'groupexchange');
        }

        if ($offer->accepted_by != 0) {
            $errors [] = get_string('offer_already_taken', 'groupexchange');
        }

        $oldgroupid = 0;
        foreach ($offer->groups as $groupid => $group) {
            if (in_array($groupid, $groupmemberships)) {
                $oldgroupid = $groupid;
                break;
            }
        }

        if ($oldgroupid == 0) {
            $errors [] = get_string('not_acceptable', 'groupexchange');
        }
    }

    if (! empty($errors)) {
        echo $OUTPUT->notification(implode('<br>', $errors), 'notifyproblem');
    } else {
        // Accept the exchange offer.
        groupexchange_accept_offer($exchange, $offer, $oldgroupid, $course);
        // GR should be event
        // add_to_log($course->id, "groupexchange", "accept offer", "view.php?id=$cm->id", $exchange->id, $cm->id);
        echo $OUTPUT->notification(get_string('offer_accepted', 'groupexchange'), 'notifysuccess');
        // Reload the exchange object.
        $exchange = groupexchange_get_instance($cm->instance);
    }

    $action = 'view';
}

// Render standing offers.
if ($action == 'view' || ($action == 'offer' && empty($errors))) {
    // GR should be event.
    // add_to_log($course->id, "groupexchange", "view offers", "view.php?id=$cm->id", $exchange->id, $cm->id);
    echo $renderer->show_offers($cm, $exchange);
    echo '<br><br>';
}

// If no offer is standing, render offer form.
if (($action == 'offer' && ! empty($errors)) || ($action == 'view' && ! $DB->record_exists('groupexchange_offers', array (
        'groupexchange' => $exchange->id,
        'userid' => $USER->id,
        'accepted_by' => 0
)))) {
    echo $renderer->show_offer_form($cm, $exchange, $errors);
}

echo $OUTPUT->footer();

