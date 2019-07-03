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
 * Strings for component 'groupreg', language 'de'
 *
 * @package   groupreg
 * @copyright 2011 onwards Olexandr Savchuk
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['event:answered'] ='Gruppentauschbörse erstellt';
$string['event:answered_desc'] ='Der Benutzer mit der ID \'{$a->userid}\' hat eine Gruppe im Gruppenaustausch mit der Kursmodul-ID \'{$a->contextinstanceid}\' geändert.';
$string['event:removed'] ='Gruppentauschbörse entfernt';
$string['event:removed_desc'] ='Der Benutzer mit der ID \'{$a->userid}\' hat seine Auswahl im Gruppenaustausch mit der Kursmodul-ID \'{$a->contextinstanceid}\' entfernt.';
$string['event:reportviewed'] ='Bericht angesehen';
$string['event:reportviewed_desc'] ='Der Benutzer mit der ID \'{$a->userid}\' hat den Bericht für die Gruppentauschbörse mit der Kursmodul-ID \'{$a->contextinstanceid}\'angesehen.';

$string['groupexchange:addinstance'] ='Fügt eine neue Instanz der Gruppentauschbörse hinzu';

$string['modulename'] = 'Gruppentauschbörse';
$string['pluginname'] = 'Gruppentauschbörse';
$string['modulename_help'] = 'Mit der Gruppentauschbörse können Kursverwalter auswählen, welche Gruppen die Kursteilnehmer untereinander tauschen können.';
$string['modulenameplural'] = 'Gruppentauschbörsen';
$string['pluginadministration'] = 'Einstellungen der Gruppentauschbörse';

$string['messageprovider:offer_accepted'] = 'Benachrichtigung über angenommene Gruppentauschangebote';
$string['email_subject'] = '{$a->course}: {$a->exchange}: Gruppentauschangebot wurde angenommen';
$string['email_body'] = 'Dein Gruppentauschangebot in \'{$a->exchange}\' (Kurs: \'{$a->course}\') wurde von einem anderen Teilnehmer angenommen.

Du wurdest von der Gruppe \'{$a->groupfrom}\' in die Gruppe \'{$a->groupto}\' verschoben.';

$string['groupexchangename'] = 'Name der Gruppentauschbörse';
$string['choose_groups_help'] = 'Markiere die Gruppen, die in der Tauschbörse verfügbar sein sollen. Kursteilnehmer, die einer dieser Gruppen angehören, können an der Tauschbörse teilnehmen, und ihre Gruppenmitgliedschaft mit anderen Studierenden in diesen Gruppen tauschen.';
$string['choose_groups'] = 'Gruppen für die Tauschbörse';
$string['setting_anonymous'] = 'Tauschangebote anonymisieren';
$string['setting_anonymous_help'] = 'Wenn gesetzt, werden bei den Tauschangeboten keine Benutzernamen angezeigt, nur die angebotene Gruppe und erwünschte Gruppen.';
$string['setting_limitexchanges'] = 'Limit exchanges per user';
$string['setting_limitexchanges_help'] = 'Set a limit to how often a student can exchange his group membership in this activity. Default is no limit.';
$string['unlimited'] = 'No limit';
$string['timerestrict'] = 'Aktivität der Tauschbörse auf diese Zeitspanne eingrenzen';
$string['expired'] = 'Diese Aktivität wurde am {$a} geschlossen und ist nicht mehr verfügbar.';
$string['notopenyet'] = 'Diese Aktivität ist bis {$a} nicht verfügbar.';
$string['groupexchangeopen'] = 'Offen';
$string['groupexchangeclose'] = 'Bis';
$string['checkallornone'] = 'Alle/keine markieren';
$string['standing_offers'] = 'Aktive Gruppentauschangebote';
$string['author_name'] = 'Angeboten von:';
$string['group_offer'] = 'Bietet Gruppe:';
$string['groups_accepted'] = 'Sucht Gruppe:';
$string['action'] = 'Aktion:';
$string['your_offer'] = 'Das ist Ihr Angebot.';
$string['not_acceptable'] = 'Du kannst dieses Angebot nicht annehmen.';
$string['accept'] = 'Angebot annehmen';
$string['cancel'] = 'Angebot entfernen';
$string['no_offers'] = 'Es gibt derzeit keine aktiven Angebote.';
$string['cannot_offer'] = 'Du musst Mitglied in einer der zum Austausch stehenden Gruppen sein.';
$string['post_offer'] = 'Neuen Gruppentauschangebot erstellen';
$string['offer_group'] = 'Du möchtest aus folgender Gruppe raus:';
$string['request_group'] = 'Du möchtest in eine dieser Gruppen rein:';
$string['submit_offer'] = 'Gruppentauschangebot erstellen';
$string['offer_created'] = 'Gruppentauschangebot wurde erstellt.';
$string['error_request_group_not_enough'] = 'Du musst mindestens eine erwünschte Gruppe auswählen.';
$string['error_request_group_bad'] = 'Bad group data submitted.';
$string['error_offer_group'] = 'Bad offered group data submitted: must be one admissible group ID';
$string['error_double_offer'] = 'Du hast bereits ein Angebot mit dieser Gruppe in dieser Börse. Angebot wurde nicht nochmal erstellt.';
$string['error_delete_offer'] = 'Angebot konnten nicht entfernt werden.';
$string['offer_deleted'] = 'Dein Angebot wurde entfernt.';
$string['confirm_delete'] = 'Bist du sicher, dass du dein Angebot entfernen möchtest?';
$string['confirm_accept'] = 'Bist du sicher, dass du dieses Angebot annehmen und die Gruppen tauschen möchtest? Der Tausch kann nicht rückgängig gemacht werden.';
$string['offer_doesnt_exist'] = 'Das gewähte Angebot existiert nicht. Vielleicht wurde es mittlerweile vom Anbieter entfernt.';
$string['offer_already_taken'] = 'Das gewähte Angebot wurde von jemandem anders angenommen. Zu langsam.';
$string['offer_accepted'] = 'Das Angebot wurde angenommen, die Gruppen wurden getauscht.';
$string['error_notenoughgroups'] = 'Du solltest mindestens zwei Gruppen für die Tauschbörse wählen!';
$string['fitting_offers'] = 'Es wurden folgende aktive Tauschangebote gefunden, die deinen Angaben entsprechen. Vielleicht möchtest du einen davon annehmen, anstatt einen eigenen Angebot zu erstellen.';
$string['submit_offer_ignore'] = 'Aktive Angebote ignorieren, meinen Tauschangebot erstellen';
$string['setting_students_only'] = 'Nur für Studierende';
$string['setting_students_only_help'] = 'Nur Studierenden erlauben, an dieser Tauschbörse teilzunehmen.';
$string['error_students_only'] = 'Nur Studierende können an dieser Tauschbörse teilnehmen.';
