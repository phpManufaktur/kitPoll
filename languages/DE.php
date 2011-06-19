<?php
/**
 * kitPoll
 * 
 * @author Ralf Hertsch (ralf.hertsch@phpmanufaktur.de)
 * @link http://phpmanufaktur.de
 * @copyright 2011
 * @license GNU GPL (http://www.gnu.org/licenses/gpl.html)
 * @version $Id$
 */

// prevent this file from being accessed directly
if (!defined('WB_PATH')) die('invalid call of '.$_SERVER['SCRIPT_NAME']);

define('poll_access_kit',												'KIT Gruppe');
define('poll_access_public',										'Öffentlich');

define('poll_answers_single',										'nur eine Antwort möglich');
define('poll_answers_multiple',									'mehrere Antworten möglich');

define('poll_desc_cfg_exec',										'kiPoll ausführen = 1, nicht ausführen = 0');
define('poll_desc_cfg_form_dlg_account',				'Der kitForm Dialog, der von kitPoll für die Verwaltung der Kundendaten (Account) verwendet wird.');
define('poll_desc_cfg_form_dlg_login',					'Der kitForm Dialog, der von kitPoll für die Anmeldung von Benutzern angezeigt wird.'); 

define('poll_error_data_integrity_invalid',			'Die Daten sind unvollständig oder manipuliert. Programmausführung gestoppt.');
define('poll_error_invalid_poll_name',					'Zu der Umfrage <b>%s</b> wurden keine Daten gefunden!');
define('poll_error_kit_group_invalid',					'Die KIT Gruppe <b>%s</b> exisitiert nicht, pruefen Sie die Angaben in der Umfrage!');
define('poll_error_no_poll_name',								'Es wurde keine Umfrage über den Parameter <b>name</b> festgelegt!');
define('poll_error_poll_not_active',						'Die Umfrage <b>%s</b> ist gesperrt oder gelöscht und kann nicht angezeigt werden!');
define('poll_error_create_dir',									'Das Verzeichnis <b>%s</b> konnte nicht angelegt werden!');
define('poll_error_unknown_chart_type',					'Der Diagrammtyp <b>%s</b> ist leider nicht definiert und kann nicht dargestellt werden!');

define('poll_header_question_edit',							'Umfrage erstellen oder bearbeiten');
define('poll_header_question_list',							'Übersicht über die Umfragen');

define('poll_hint_access',											'');
define('poll_hint_answer',											'');
define('poll_hint_answers_mode',								'');
define('poll_hint_date_end',										'');
define('poll_hint_date_start',									'');
define('poll_hint_header',											'');
define('poll_hint_id',													'');
define('poll_hint_intro',												'');
define('poll_hint_kit_groups',									'Erlaubt sind Gruppen aus den Kategorien "Intern" oder "Newsletter" - trennen Sie die Gruppen durch ein Komma.');
define('poll_hint_page_description',						'');
define('poll_hint_page_keywords',								'');
define('poll_hint_page_title',									'Wenn das Ausgabe-Droplet den Titel der Seite setzen soll, auf dem die Umfrage angezeigt wird, können Sie ihn hier angeben.');
define('poll_hint_poll_name',										'Legen Sie einen eindeutigen Bezeichner für diese Umfrage fest. Der Bezeichner wird später zum Aufruf der Umfrage im Frontend benötigt.');
define('poll_hint_question',										'');
define('poll_hint_release',											'');
define('poll_hint_show_results',								'');
define('poll_hint_status',											'');
define('poll_hint_timestamp',										'');

define('poll_intro_question_edit',							'Mit diesem Dialog erstellen oder bearbeiten Sie eine Umfrage.');
define('poll_intro_question_list',							'Übersicht über die erstellten Umfragen.');

define('poll_label_access',											'Zugriff');
define('poll_label_answer',											'Anwort #%d');
define('poll_label_answers',										'Antworten');
define('poll_label_answers_mode',								'Antworten');
define('poll_label_answer_add',									'(%d) Anwort hinzufügen');
define('poll_label_cfg_exec',										'kitPoll ausführen');
define('poll_label_cfg_form_dlg_login',					'kitForm: Login Dialog');
define('poll_label_cfg_form_dlg_account',				'kitForm: Account Dialog');
define('poll_label_clicks',											'Klicks');
define('poll_label_date_end',										'Ende (Datum)');
define('poll_label_date_start',									'Start (Datum)');
define('poll_label_export',											'Ergebnisse der Umfragen exportieren');
define('poll_label_header',											'Überschrift');
define('poll_label_id',													'ID');
define('poll_label_intro',											'Einleitungstext (Intro)');
define('poll_label_kit_groups',									'KIT Gruppe(n)');
define('poll_label_page_description',						'Beschreibung (Seite)');
define('poll_label_page_keywords',							'Keywords (Seite)');
define('poll_label_page_title',									'Titel (Seite)');
define('poll_label_poll_name',									'Bezeichner');
define('poll_label_question',										'Frage');
define('poll_label_release',										'Freigabe');
define('poll_label_show_results',								'Ergebnisse anzeigen');
define('poll_label_status',											'Status');
define('poll_label_timestamp',									'Letzte Änderung');

define('poll_msg_authenticated_wrong_group',		'<p>Sie sind leider nicht berechtigt an dieser Umfrage teilzunehmen.</p>');
define('poll_msg_poll_csv_export_success',			'<p>Die Umfragen wurden <a href="%s">erfolgreich exportiert</a>!.</p>');
define('poll_msg_no_answer',										'<p>Sie haben keine Antwort ausgewählt!</p>');
define('poll_msg_thanks_for_answer',						'<p>Vielen Dank für Ihre Antwort!</p>');
define('poll_msg_question_empty',								'<p>Die Frage darf nicht leer oder kürzer als 5 Zeichen sein!</p>');
define('poll_msg_question_name_empty',					'<p>Der Bezeichner für die Umfrage darf nicht leer sein!</p>');
define('poll_msg_question_name_rename_rejected','<p>Der Bezeicher für die Umfrage kann nicht in in <b>%s</b> geändert werden, dieser wird bereits von der Umfrage mit der <b>ID %03d</b> verwendet.</p>');
define('poll_msg_question_name_rejected',				'<p>Der Bezeichner <b>%s</b> wird bereits von der Umfrage mit der <b>ID %03d</b> verwendet, bitte verwenden Sie einen anderen Bezeichner.</p>');
define('poll_msg_question_inserted',						'<p>Die Umfrage wurde angelegt.</p>');
define('poll_msg_question_updated',							'<p>Die Umfrage wurde aktualisiert.</p>');

define('poll_release_automatic',								'Automatisch');
define('poll_release_locked',										'Gesperrt');
define('poll_release_unlocked',									'Freigegeben');

define('poll_show_immediate',										'Sofort');
define('poll_show_expiration',									'Nach Ablauf');
define('poll_show_release',											'Nach Freigabe');

define('poll_status_active',										'Aktiv');
define('poll_status_locked',										'Gesperrt');
define('poll_status_deleted',										'Gelöscht');

define('poll_tab_about',												'?');
define('poll_tab_config',												'Einstellungen');
define('poll_tab_edit',													'Umfrage bearbeiten');
define('poll_tab_list',													'Übersicht');

define('poll_th_answer_x',											'Antwort #%d');
define('poll_th_answer_x_clicks',								'Klicks #%d');
define('poll_th_clicks_total',									'Klicks gesamt');
define('poll_th_id',														'ID');
define('poll_th_name',													'Bezeichner');
define('poll_th_header',												'Überschrift');
define('poll_th_intro',													'Einleitung');
define('poll_th_question',											'Frage');
define('poll_th_access',												'Zugriff');
define('poll_th_date_start',										'Start');
define('poll_th_date_end',											'Ende');
define('poll_th_status',												'Status');
define('poll_th_show_results',									'Ergebnisse');
define('poll_th_release',												'Freigabe');
define('poll_th_timestamp',											'Letzte Änderung');
define('poll_th_clicks',												'Antworten (Klicks)');

?>