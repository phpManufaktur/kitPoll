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

require_once(WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/initialize.php');
require_once(WB_PATH.'/framework/functions.php');

global $dbPollQuestion;
global $dbPollAnswer;
global $dbPollLog;
global $dbPollSorter;
global $dbPollCfg;

if (!is_object($dbPollQuestion)) 	$dbPollQuestion = new dbPollQuestion();
if (!is_object($dbPollAnswer))		$dbPollAnswer = new dbPollAnswer();
if (!is_object($dbPollLog))				$dbPollLog = new dbPollLog();
if (!is_object($dbPollSorter))		$dbPollSorter = new dbPollTableSort();
if (!is_object($dbPollCfg))				$dbPollCfg = new dbPollCfg();

class pollBackend {
	
	const request_action							= 'act';
	const request_add_answer					= 'aans';
	const request_items								= 'its';
	
	const action_about								= 'abt';
	const action_config								= 'cfg';
	const action_config_check					= 'cfgc';
	const action_edit									= 'edt';
	const action_edit_check						= 'edtc';
	const action_export								= 'exp';
	const action_default							= 'def';
	const action_list									= 'lst';
	
	private $tab_navigation_array = array(
		self::action_list								=> poll_tab_list,
		self::action_edit								=> poll_tab_edit,
		self::action_config							=> poll_tab_config,
		self::action_about							=> poll_tab_about
		
	);
	
	private $page_link 								= '';
	private $img_url									= '';
	private $template_path						= '';
	private $error										= '';
	private $message									= '';
	
	public function __construct() {
		$this->page_link = ADMIN_URL.'/admintools/tool.php?tool=kit_poll';
		$this->template_path = WB_PATH . '/modules/' . basename(dirname(__FILE__)) . '/htt/' ;
		$this->img_url = WB_URL. '/modules/'.basename(dirname(__FILE__)).'/images/';
		date_default_timezone_set(tool_cfg_time_zone);
	} // __construct()
	
	/**
    * Set $this->error to $error
    * 
    * @param STR $error
    */
  public function setError($error) {
  	$debug = debug_backtrace();
    $caller = next($debug);
  	$this->error = sprintf('[%s::%s - %s] %s', basename($caller['file']), $caller['function'], $caller['line'], $error);
  } // setError()

  /**
    * Get Error from $this->error;
    * 
    * @return STR $this->error
    */
  public function getError() {
    return $this->error;
  } // getError()

  /**
    * Check if $this->error is empty
    * 
    * @return BOOL
    */
  public function isError() {
    return (bool) !empty($this->error);
  } // isError

  /**
   * Reset Error to empty String
   */
  public function clearError() {
  	$this->error = '';
  }

  /** Set $this->message to $message
    * 
    * @param STR $message
    */
  public function setMessage($message) {
    $this->message = $message;
  } // setMessage()

  /**
    * Get Message from $this->message;
    * 
    * @return STR $this->message
    */
  public function getMessage() {
    return $this->message;
  } // getMessage()

  /**
    * Check if $this->message is empty
    * 
    * @return BOOL
    */
  public function isMessage() {
    return (bool) !empty($this->message);
  } // isMessage
  
  /**
   * Return Version of Module
   *
   * @return FLOAT
   */
  public function getVersion() {
    // read info.php into array
    $info_text = file(WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/info.php');
    if ($info_text == false) {
      return -1; 
    }
    // walk through array
    foreach ($info_text as $item) {
      if (strpos($item, '$module_version') !== false) {
        // split string $module_version
        $value = explode('=', $item);
        // return floatval
        return floatval(preg_replace('([\'";,\(\)[:space:][:alpha:]])', '', $value[1]));
      } 
    }
    return -1;
  } // getVersion()
  
  public function getTemplate($template, $template_data) {
  	global $parser;
  	try {
  		$result = $parser->get($this->template_path.$template, $template_data); 
  	} catch (Exception $e) {
  		$this->setError(sprintf(tool_error_template_error, $template, $e->getMessage()));
  		return false;
  	}
  	return $result;
  } // getTemplate()
  
  
  /**
   * Verhindert XSS Cross Site Scripting
   * 
   * @param REFERENCE $_REQUEST Array
   * @return $request
   */
	public function xssPrevent(&$request) { 
  	if (is_string($request)) {
	    $request = html_entity_decode($request);
	    $request = strip_tags($request);
	    $request = trim($request);
	    $request = stripslashes($request);
  	}
	  return $request;
  } // xssPrevent()
	
  public function action() {
  	$html_allowed = array();
  	foreach ($_REQUEST as $key => $value) {
  		if (!in_array($key, $html_allowed)) {
  			$_REQUEST[$key] = $this->xssPrevent($value);	  			
  		} 
  	}
    isset($_REQUEST[self::request_action]) ? $action = $_REQUEST[self::request_action] : $action = self::action_default;
    
  	switch ($action):
  	case self::action_about:
  		$this->show(self::action_about, $this->dlgAbout());
  		break;
  	case self::action_config:
  		$this->show(self::action_config, $this->dlgConfig());
  		break;
  	case self::action_config_check:
  		$this->show(self::action_config, $this->checkConfig());
  		break;
  	case self::action_edit:
  		$this->show(self::action_edit, $this->dlgEdit());
  		break;
  	case self::action_edit_check:
  		$this->show(self::action_edit, $this->checkEdit());
  		break;
  	case self::action_export:
  		$this->show(self::action_list, $this->exportList());
  		break;
  	case self::action_list:
  	case self::action_default:
  	default:
  		$this->show(self::action_list, $this->dlgList());
  		break;
  	endswitch;
  } // action
	
  	
  /**
   * Ausgabe des formatierten Ergebnis mit Navigationsleiste
   * 
   * @param $action - aktives Navigationselement
   * @param $content - Inhalt
   * 
   * @return ECHO RESULT
   */
  public function show($action, $content) {
  	$navigation = array();
  	foreach ($this->tab_navigation_array as $key => $value) {
  		$navigation[] = array(
  			'active' 	=> ($key == $action) ? 1 : 0,
  			'url'			=> sprintf('%s&%s=%s', $this->page_link, self::request_action, $key),
  			'text'		=> $value
  		);
  	}
  	$data = array(
  		'WB_URL'			=> WB_URL,
  		'navigation'	=> $navigation,
  		'error'				=> ($this->isError()) ? 1 : 0,
  		'content'			=> ($this->isError()) ? $this->getError() : $content
  	);
  	echo $this->getTemplate('backend.body.htt', $data);
  } // show()
	
  public function dlgAbout() {
  	$data = array(
  		'version'					=> sprintf('%01.2f', $this->getVersion()),
  		'img_url'					=> $this->img_url.'/kit_poll_logo_425x350.jpg',
  		'release_notes'		=> file_get_contents(WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/info.txt'),
  	);
  	return $this->getTemplate('backend.about.htt', $data);
  } // dlgAbout()
  
  public function dlgList() {
  	global $dbPollQuestion;
  	global $dbPollAnswer;
  	
  	$SQL = sprintf( "SELECT * FROM %s WHERE %s!='%s' ORDER BY %s DESC",
  									$dbPollQuestion->getTableName(),
  									dbPollQuestion::field_status,
  									dbPollQuestion::status_deleted,
  									dbPollQuestion::field_date_start);
  	$polls = array();
  	if (!$dbPollQuestion->sqlExec($SQL, $polls)) {
  		$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbPollQuestion->getError()));
  		return false;
  	}
  	$items = array();
  	foreach ($polls as $poll) { 
  		$SQL = sprintf( "SELECT SUM(%s) AS antworten FROM %s WHERE %s='%s' AND %s='%s'",
  										dbPollAnswer::field_clicks,
  										$dbPollAnswer->getTableName(),
  										dbPollAnswer::field_question_id,
  										$poll[dbPollQuestion::field_id],
  										dbPollAnswer::field_status,
  										dbPollAnswer::status_active);
  		$result = array();	
  		if (!$dbPollAnswer->sqlExec($SQL, $result)) {
  			$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbPollAnswer->getError()));
  			return false;
  		}
  		$antworten = $result[0]['antworten'];
  		$items[] = array(
  			'id'					=> $poll[dbPollQuestion::field_id],
  			'name'				=> $poll[dbPollQuestion::field_name],
  			'header'			=> $poll[dbPollQuestion::field_header],
  			'question'		=> $poll[dbPollQuestion::field_question],
  			'access'			=> $poll[dbPollQuestion::field_access],
  			'date_start'	=> $poll[dbPollQuestion::field_date_start],
  			'date_end'		=> $poll[dbPollQuestion::field_date_end],
  			'status'			=> $poll[dbPollQuestion::field_status],
  			'show_results'=> $poll[dbPollQuestion::field_show_results],
  			'release'			=> $poll[dbPollQuestion::field_release],
  			'timestamp'		=> $poll[dbPollQuestion::field_timestamp],
  			'clicks'			=> $antworten,
  			'link_edit'		=> sprintf(	'%s&%s', 
  																$this->page_link, 
  																http_build_query(array(	self::request_action => self::action_edit,
  																												dbPollQuestion::field_id => $poll[dbPollQuestion::field_id])))
  		);
  	} // foreach
  	
  	// Kopfzeilen
  	$header = array(
  		'id'					=> poll_th_id,
  		'name'				=> poll_th_name,
  		'header'			=> poll_th_header,
  		'question'		=> poll_th_question,
  		'access'			=> poll_th_access,
  		'date_start'	=> poll_th_date_start,
  		'date_end'		=> poll_th_date_end,
  		'status'			=> poll_th_status,
  		'show_results'=> poll_th_show_results,
  		'release'			=> poll_th_release, 
  		'timestamp'		=> poll_th_timestamp,
  		'clicks'			=> poll_th_clicks
  	);
  	
  	$data = array(
  		'title'				=> poll_header_question_list,
  		'is_intro'		=> $this->isMessage() ? 0 : 1,
  		'intro'				=> $this->isMessage() ? $this->getMessage() : poll_intro_question_list,
  		'polls'				=> $items,
  		'header'			=> $header,
  		'export_link'	=> sprintf('%s&%s=%s', $this->page_link, self::request_action, self::action_export),
  		'export_text'	=> poll_label_export			
  	);
  	return $this->getTemplate('backend.question.list.htt', $data);
  } // dlgList()
  
  public function dlgEdit() {
  	global $dbPollQuestion;
  	global $dbPollAnswer;
  	global $dbPollSorter;
  	
  	$question_id = (isset($_REQUEST[dbPollQuestion::field_id])) ? $_REQUEST[dbPollQuestion::field_id] : -1;
  	
  	if ($question_id > 0) {
  		// Fragen auslesen
  		$where = array(dbPollQuestion::field_id => $question_id);
  		$question = array();
  		if (!$dbPollQuestion->sqlSelectRecord($where, $question)) {
  			$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbPollQuestion->getError()));
  			return false;
  		}
  		if (sizeof($question) < 1) {
  			$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tool_error_id_invalid, $question_id)));
  			return false;
  		}
  		$question = $question[0];
  		// Antworten auslesen
  		$SQL = sprintf( "SELECT * FROM %s WHERE %s='%s' AND %s='%s' ORDER BY FIND_IN_SET(%s, '%s')",
  										$dbPollAnswer->getTableName(),
  										dbPollAnswer::field_question_id,
  										$question_id,
  										dbPollAnswer::field_status,
  										dbPollAnswer::status_active,
  										dbPollAnswer::field_id,
  										$question[dbPollQuestion::field_answers]);
  		$answers = array();
  		if (!$dbPollAnswer->sqlExec($SQL, $answers)) {
  			$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbPollAnswer->getError()));
  			return false;
  		}
  	}
  	else {
  		// Defaults setzen
  		$question = $dbPollQuestion->getFields();
  		$question[dbPollQuestion::field_id] = $question_id;
  		$question[dbPollQuestion::field_access] = dbPollQuestion::access_public;
  		$question[dbPollQuestion::field_status] = dbPollQuestion::status_active;
  		$question[dbPollQuestion::field_show_results] = dbPollQuestion::show_immediate;
  		$question[dbPollQuestion::field_release] = dbPollQuestion::release_automatic;
  		$question[dbPollQuestion::field_timestamp] = date('Y-m-d H:i:s');
  		$question[dbPollQuestion::field_date_start] = date('Y-m-d H:i:s');
  		$question[dbPollQuestion::field_date_end] = date('Y-m-d H:i:s', strtotime('+4 weeks'));
  		$answers = array();
  		$answers[] = $dbPollAnswer->getFields();
  	}
  	
  	$poll_answers = array();
  	$add_answers = array();
  	$i=1;
  	$answer_array = array();
  	foreach ($answers as $answer) {
  		if ($answer[dbPollAnswer::field_id] < 1) continue;
  		$poll_answers[] = array(
  			'id'			=> $answer[dbPollAnswer::field_id],
  			'label' 	=> sprintf(poll_label_answer, $i),
  			'name'		=> sprintf('%s_%d', dbPollAnswer::field_id, $answer[dbPollAnswer::field_id]),
  			'value'		=> $answer[dbPollAnswer::field_answer],
  			'hint'		=> poll_hint_answer
  		);
  		$answer_array[] = $answer[dbPollAnswer::field_id];
  		$i++;
  	}
  	for ($x=1; $x<4; $x++) {
  		$add_answers[] = array(
  			'label' 	=> sprintf(poll_label_answer_add, $i),
  			'name'		=> sprintf('%s_%d', self::request_add_answer, $x),
  			'value'		=> '',
  			'hint'		=> poll_hint_answer
  		);
  		$i++;
  	}
  	
  	$kit_groups = array();
  	
  	$poll = array(
  		'id'					=> array( 'label'		=> poll_label_id,
  														'name'		=> dbPollQuestion::field_id,
  														'value'		=> $question[dbPollQuestion::field_id],
  														'hint'		=> poll_hint_id),
  		'timestamp'		=> array(	'label'		=> poll_label_timestamp,
  														'name'		=> dbPollQuestion::field_timestamp,
  														'value'		=> $question[dbPollQuestion::field_timestamp],
  														'hint'		=> poll_hint_timestamp),
  		'name'				=> array(	'label'		=> poll_label_poll_name,
  														'name'		=> dbPollQuestion::field_name,
  														'value'		=> $question[dbPollQuestion::field_name],
  														'hint'		=> poll_hint_poll_name ),
  		'page_title'	=> array( 'label'		=> poll_label_page_title,
  														'name'		=> dbPollQuestion::field_page_title,
  														'value'		=> $question[dbPollQuestion::field_page_title],
  														'hint'		=> poll_hint_page_title),
  		'page_description' => array( 'label' => poll_label_page_description,
  														'name'		=> dbPollQuestion::field_page_description,
  														'value'		=> $question[dbPollQuestion::field_page_description],	
  														'hint'		=> poll_hint_page_description),
  		'page_keywords'	=> array('label'	=> poll_label_page_keywords,
  														'name'		=> dbPollQuestion::field_page_keywords,
  														'value'		=> $question[dbPollQuestion::field_page_keywords],
  														'hint'		=> poll_hint_page_keywords),
  		'header'			=> array(	'label'		=> poll_label_header,
  														'name'		=> dbPollQuestion::field_header,
  														'value'		=> $question[dbPollQuestion::field_header],
  														'hint'		=> poll_hint_header),
  		'intro'				=> array(	'label'		=> poll_label_intro,
  														'name'		=> dbPollQuestion::field_intro,
  														'value'		=> $question[dbPollQuestion::field_intro],
  														'hint'		=> poll_hint_intro),
  		'question'		=> array(	'label'		=> poll_label_question,
  														'name'		=> dbPollQuestion::field_question,
  														'value'		=> $question[dbPollQuestion::field_question],
  														'hint'		=> poll_hint_question),
  		'access'			=> array( 'label'		=> poll_label_access,
  														'name'		=> dbPollQuestion::field_access,	
  														'active'	=> $question[dbPollQuestion::field_access],
  														'options'	=> $dbPollQuestion->access_array,
  														'hint'		=> poll_hint_access),
  		'kit_groups'	=> array(	'label'		=> poll_label_kit_groups,
  														'name'		=> dbPollQuestion::field_kit_groups,
  														'value'		=> $question[dbPollQuestion::field_kit_groups],
  														'hint'		=> poll_hint_kit_groups),
  		'date_start'	=> array( 'label'		=> poll_label_date_start,
  														'name'		=> dbPollQuestion::field_date_start,
  														'value'		=> $question[dbPollQuestion::field_date_start],
  														'hint'		=> poll_hint_date_start),
  		'date_end'		=> array( 'label'		=> poll_label_date_end,
  														'name'		=> dbPollQuestion::field_date_end,
  														'value'		=> $question[dbPollQuestion::field_date_end],
  														'hint'		=> poll_hint_date_end),
  		'status'			=> array( 'label'		=> poll_label_status, 
  														'name'		=> dbPollQuestion::field_status,
  														'active'	=> $question[dbPollQuestion::field_status],
  														'options'	=> $dbPollQuestion->status_array,
  														'hint'		=> poll_hint_status),
  		'show_results'=> array( 'label'		=> poll_label_show_results,
  														'name'		=> dbPollQuestion::field_show_results,
  														'active'	=> $question[dbPollQuestion::field_show_results],
  														'options'	=> $dbPollQuestion->show_results_array,
  														'hint'		=> poll_hint_show_results),
  		'release'			=> array( 'label'		=> poll_label_release,
  														'name'		=> dbPollQuestion::field_release,
  														'active'	=> $question[dbPollQuestion::field_release],
  														'options'	=> $dbPollQuestion->release_array,
  														'hint'		=> poll_hint_release),
  		'answers_mode'=> array(	'label'		=> poll_label_answers_mode,
  														'name'		=> dbPollQuestion::field_answers_mode,
  														'options'	=> $dbPollQuestion->answers_array,
  														'active'	=> $question[dbPollQuestion::field_answers_mode],
  														'hint'		=> poll_hint_answers_mode),
  		'answers'			=> $poll_answers,
  		'add_answers'	=> $add_answers
  	);
  	
  	$sorter_table = 'mod_kit_poll';
  	$sorter_active = 0;
  	if ($question_id > 0) {
  		// pruefen ob die Sorter Tabelle bereits existiert
  		$SQL = sprintf( "SELECT * FROM %s WHERE %s='%s' AND %s='%s'",
  										$dbPollSorter->getTableName(),
  										dbPollTableSort::field_table,
  										$sorter_table,
  										dbPollTableSort::field_value,
  										$question_id);
  		$sorter = array();
  		if (!$dbPollSorter->sqlExec($SQL, $sorter)) {
  			$this->setError($dbPollTableSort->getError()); 
  			return false;
  		} 
  		if (count($sorter) < 1) {
  			// Sorter Tabelle anlegen
  			$data = array(
  				dbPollTableSort::field_table => $sorter_table,
  				dbPollTableSort::field_value => $question_id,
  				dbPollTableSort::field_order => ''
  			); 
  			if (!$dbPollSorter->sqlInsertRecord($data)) {
  				$this->setError($dbPollTableSort->getError()); 
  				return false;
  			}
  		}
  		$sorter_active = 1;
  	}
  	
  	
  	$data = array(
  		'form_action'				=> $this->page_link,
  		'language'					=> (LANGUAGE == 'EN') ? '' : strtolower(LANGUAGE),
  		'action_name'				=> self::request_action,
  		'action_value'			=> self::action_edit_check,
  		'header'						=> poll_header_question_edit,
  		'is_intro'					=> $this->isMessage() ? 0 : 1,
  		'intro'							=> $this->isMessage() ? $this->getMessage() : poll_intro_question_edit,
  		'btn_ok'						=> tool_btn_ok,
  		'btn_abort'					=> tool_btn_abort,
  		'abort_location'		=> $this->page_link,
  		'poll'							=> $poll,
  		'sorter_table'			=> $sorter_table,
  		'sorter_active'			=> $sorter_active,
  		'sorter_value'			=> $question_id,  		
  	);
  	return $this->getTemplate('backend.question.edit.htt', $data);
  } // dlgEdit()
  
  public function checkEdit() {
  	global $dbPollQuestion;
  	global $dbPollAnswer;
  	global $dbPollSorter;
  	
  	// Pflichtfelder: Bezeichner, Frage und mind. 2 Antworten
  	$checked = true;
  	$message = '';
  	$question_id = (isset($_REQUEST[dbPollQuestion::field_id])) ? $_REQUEST[dbPollQuestion::field_id] : -1;
  	$question_array = $dbPollQuestion->getFields();
  	$question = array();
  	foreach ($question_array as $field => $value) {
  		switch ($field):
  		case dbPollQuestion::field_access:
  			$question[$field] = (isset($_REQUEST[$field])) ? $_REQUEST[$field] : dbPollQuestion::access_public;
  			break;
  		case dbPollQuestion::field_date_start:
  		case dbPollQuestion::field_date_end:
  			$question[$field] = (isset($_REQUEST[$field])) ? date('Y-m-d H:i:s', strtotime($_REQUEST[$field])) : '0000-00-00 00:00:00';
  			break;
  		case dbPollQuestion::field_page_description:
  		case dbPollQuestion::field_page_keywords:
  		case dbPollQuestion::field_page_title:	
  		case dbPollQuestion::field_intro:
  		case dbPollQuestion::field_header:
  			$question[$field] = (isset($_REQUEST[$field])) ? $_REQUEST[$field] : '';
  			break;
  		case dbPollQuestion::field_kit_groups:
  			// Pruefung der KIT Gruppe fehlt noch!
  			$groups = (isset($_REQUEST[$field])) ? $_REQUEST[$field] : '';
  			$ga = explode(',', $groups);
  			$gn = array();
  			foreach ($ga as $gi) {
  				$gi = trim($gi);
  				if (!empty($gi)) $gn[] = $gi;
  			}
  			$question[$field] = implode(',', $gn);
  			break;
  		case dbPollQuestion::field_name:
  			$question[$field] = isset($_REQUEST[$field]) ? $_REQUEST[$field] : '';
  			if (empty($question[$field])) {
  				$message .= poll_msg_question_name_empty;
  				$checked = false;
  				break;
  			}
  			$name = str_replace(' ', '_', strtolower(media_filename(trim($question[$field]))));
  			$SQL = sprintf( "SELECT %s FROM %s WHERE %s='%s' AND %s!='%s'",
  											dbPollQuestion::field_id,
  											$dbPollQuestion->getTableName(),
  											dbPollQuestion::field_name,
  											$name,
  											dbPollQuestion::field_status,
  											dbPollQuestion::status_deleted);
  			$result = array();
  			if (!$dbPollQuestion->sqlExec($SQL, $result)) {
  				$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbPollQuestion->getError())); 
  				return false;
  			}
  			if (count($result) > 0) {
  				if (($question_id > 0) && ($result[0][dbPollQuestion::field_id] !== $question_id)) {
  					// Umfrage kann nicht umbenannt werden, der Bezeichner wird bereits verwendet
  					$message .= sprintf(poll_msg_question_name_rename_rejected, $name, $result[0][dbPollQuestion::field_id]);
  					unset($_REQUEST[$field]);
  					$checked = false;
  					break;
  				}
  				elseif ($question_id < 1) {
  					// Der Bezeichner wird bereits verwendet
  					$message .= sprintf(poll_msg_question_name_rejected, $name, $result[0][dbPollQuestion::field_id]);
  					unset($_REQUEST[$field]);
  					$checked = false;
  					break; 
  				}
  			}
  			$question[$field] = $name;  			
  			break;
  		case dbPollQuestion::field_question: 
  			$question[$field] = (isset($_REQUEST[$field])) ? trim($_REQUEST[$field]) : '';
  			if (empty($question[$field]) || (strlen($question[$field]) < 5)) {
  				$message .= poll_msg_question_empty;
  				$checked = false;
  			}
  			break;
  		case dbPollQuestion::field_answers:
  			// Sortierreihenfolge festhalten
		  	$SQL = sprintf( "SELECT %s FROM %s WHERE %s='%s' AND %s='%s'",
		  									dbPollTableSort::field_order,
		  									$dbPollSorter->getTableName(),
		  									dbPollTableSort::field_value,
		  									$question_id,
		  									dbPollTableSort::field_table,
		  									'mod_kit_poll');
		  	$sorter = array();
		  	if (!$dbPollSorter->sqlExec($SQL, $sorter)) {
		  		$$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbPollSorter->getError()));
		  		return false;
		  	}
		  	$question[$field] = (count($sorter) > 0) ? $sorter[0][dbPollTableSort::field_order] : '';
		  	break;
  		case dbPollQuestion::field_release:
  			$question[$field] = (isset($_REQUEST[$field])) ? $_REQUEST[$field] : dbPollQuestion::release_automatic;
  			break;
  		case dbPollQuestion::field_show_results:
  			$question[$field] = (isset($_REQUEST[$field])) ? $_REQUEST[$field] : dbPollQuestion::show_immediate;
  			break;
  		case dbPollQuestion::field_status:
  			$question[$field] = (isset($_REQUEST[$field])) ? $_REQUEST[$field] : dbPollQuestion::status_active;
  			break;
  		case dbPollQuestion::field_answers_mode:
  			$question[$field] = (isset($_REQUEST[$field])) ? $_REQUEST[$field] : dbPollQuestion::answers_single;
  			break;
  		case dbPollQuestion::field_timestamp:
  		case dbPollQuestion::field_id:
  		default:
  			// Feld ueberspringen...
  			continue;	
  		endswitch;
  	}	
  	
  	if (!$checked) {
  		$this->setMessage($message);
  		return $this->dlgEdit();
  	}
  	
  	// ok - Datensatz einfuegen oder aktualisieren
  	if ($question_id > 0) {
  		// aktualisieren
  		$where = array(dbPollQuestion::field_id => $question_id);
  		if (!$dbPollQuestion->sqlUpdateRecord($question, $where)) {
  			$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbPollQuestion->getError()));
  			return false;
  		}
  		$message .= poll_msg_question_updated;
  	}
  	else {
  		// neuer Datensatz
  		if (!$dbPollQuestion->sqlInsertRecord($question, $question_id)) {
  			$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbPollQuestion->getError()));
  			return false;
  		}
  		$message .= poll_msg_question_inserted;
  		$_REQUEST[dbPollQuestion::field_id] = $question_id;
  	}
  	
  	// Antworten pruefen
  	$SQL = sprintf( "SELECT * FROM %s WHERE %s='%s' AND %s='%s'",
  									$dbPollAnswer->getTableName(),
  									dbPollAnswer::field_question_id,
  									$question_id,
  									dbPollAnswer::field_status,
  									dbPollAnswer::status_active);
  	$old_answers = array();
  	if (!$dbPollAnswer->sqlExec($SQL, $old_answers)) {
  		$this->setError(sprintf('[%s - %] %s', __METHOD__, __LINE__, $dbPollAnswer->getError()));
  		return false;
  	}
  	$answers = array();
  	foreach ($old_answers as $ans) {
  		$is_ans = (isset($_REQUEST[sprintf('%s_%s', dbPollAnswer::field_id, $ans[dbPollAnswer::field_id])])) ? $_REQUEST[sprintf('%s_%s', dbPollAnswer::field_id, $ans[dbPollAnswer::field_id])] : '';
  		if (empty($is_ans)) {
  			// Antwort loeschen
  			$where = array(dbPollAnswer::field_id => $ans[dbPollAnswer::field_id]);
  			$data = array(dbPollAnswer::field_status => dbPollAnswer::status_deleted);
  			if (!$dbPollAnswer->sqlUpdateRecord($data, $where)) {
  				$this->setError(sprintf('[%s - %] %s', __METHOD__, __LINE__, $dbPollAnswer->getError()));
  				return false;
  			}
  		}
  		elseif ($is_ans != $ans[dbPollAnswer::field_answer]) {
  			// Datensatz aktualisieren
  			$where = array(dbPollAnswer::field_id => $ans[dbPollAnswer::field_id]);
  			$data = array(dbPollAnswer::field_answer => $is_ans);
  			if (!$dbPollAnswer->sqlUpdateRecord($data, $where)) {
  				$this->setError(sprintf('[%s - %] %s', __METHOD__, __LINE__, $dbPollAnswer->getError()));
  				return false;
  			}
  		}
  	}
  	
  	// neue Fragen eintragen?
  	for ($i=1; $i<4; $i++) {
  		$is_ans = (isset($_REQUEST[sprintf('%s_%s', self::request_add_answer, $i)])) ? trim($_REQUEST[sprintf('%s_%s', self::request_add_answer, $i)]) : '';
  		if (!empty($is_ans)) {
  			$data = array(dbPollAnswer::field_question_id => $question_id,
  										dbPollAnswer::field_clicks			=> 0,
  										dbPollAnswer::field_answer			=> $is_ans,
  										dbPollAnswer::field_status			=> dbPollAnswer::status_active);
  			$aid = -1;
  			if (!$dbPollAnswer->sqlInsertRecord($data, $aid)) {
  				$this->setError(sprintf('[%s - %] %s', __METHOD__, __LINE__, $dbPollAnswer->getError()));
  				return false;
  			}
  		}
  	}
  	
  	
  	$this->setMessage($message);
  	return $this->dlgEdit();
  } // checkEdit()
  
  /**
   * Dialog zur Konfiguration und Anpassung von kitPoll
   * 
   * @return STR dialog
   */
  public function dlgConfig() {
		global $dbPollCfg;
		$SQL = sprintf(	"SELECT * FROM %s WHERE NOT %s='%s' ORDER BY %s",
										$dbPollCfg->getTableName(),
										dbPollCfg::field_status,
										dbPollCfg::status_deleted,
										dbPollCfg::field_name);
		$config = array();
		if (!$dbPollCfg->sqlExec($SQL, $config)) {
			$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbPollCfg->getError()));
			return false;
		}
		$count = array();
		$header = array(
			'identifier'	=> tool_header_cfg_identifier,
			'value'				=> tool_header_cfg_value,
			'description'	=> tool_header_cfg_description
		);
		
		$items = array();
		// bestehende Eintraege auflisten
		foreach ($config as $entry) {
			$id = $entry[dbPollCfg::field_id];
			$count[] = $id;
			$value = (isset($_REQUEST[dbPollCfg::field_value.'_'.$id])) ? $_REQUEST[dbPollCfg::field_value.'_'.$id] : $entry[dbPollCfg::field_value];
			$value = str_replace('"', '&quot;', stripslashes($value));
			$items[] = array(
				'id'					=> $id,
				'identifier'	=> constant($entry[dbPollCfg::field_label]),
				'value'				=> $value,
				'name'				=> sprintf('%s_%s', dbPollCfg::field_value, $id),
				'description'	=> constant($entry[dbPollCfg::field_description])  
			);
		}
		$data = array(
			'form_name'						=> 'poll_cfg',
			'form_action'					=> $this->page_link,
			'action_name'					=> self::request_action,
			'action_value'				=> self::action_config_check,
			'items_name'					=> self::request_items,
			'items_value'					=> implode(",", $count), 
			'head'								=> tool_header_cfg,
			'intro'								=> $this->isMessage() ? $this->getMessage() : sprintf(tool_intro_cfg, 'kitMarketPlace'),
			'is_message'					=> $this->isMessage() ? 1 : 0,
			'items'								=> $items,
			'btn_ok'							=> tool_btn_ok,
			'btn_abort'						=> tool_btn_abort,
			'abort_location'			=> $this->page_link,
			'header'							=> $header
		);
		return $this->getTemplate('backend.config.htt', $data);
	} // dlgConfig()
	
	/**
	 * Ueberprueft Aenderungen die im Dialog dlgConfig() vorgenommen wurden
	 * und aktualisiert die entsprechenden Datensaetze.
	 * 
	 * @return STR DIALOG dlgConfig()
	 */
	public function checkConfig() {
		global $dbPollCfg;
		$message = '';
		// ueberpruefen, ob ein Eintrag geaendert wurde
		if ((isset($_REQUEST[self::request_items])) && (!empty($_REQUEST[self::request_items]))) {
			$ids = explode(",", $_REQUEST[self::request_items]);
			foreach ($ids as $id) {
				if (isset($_REQUEST[dbPollCfg::field_value.'_'.$id])) {
					$value = $_REQUEST[dbPollCfg::field_value.'_'.$id];
					$where = array();
					$where[dbPollCfg::field_id] = $id; 
					$config = array();
					if (!$dbPollCfg->sqlSelectRecord($where, $config)) {
						$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbPollCfg->getError()));
						return false;
					}
					if (sizeof($config) < 1) {
						$this->setError(sprintf(tool_error_cfg_id, $id));
						return false;
					}
					$config = $config[0];
					if ($config[dbPollCfg::field_value] != $value) {
						// Wert wurde geaendert
							if (!$dbPollCfg->setValue($value, $id) && $dbPollCfg->isError()) {
								$this->setError($dbPollCfg->getError());
								return false;
							}
							elseif ($dbPollCfg->isMessage()) {
								$message .= $dbPollCfg->getMessage();
							}
							else {
								// Datensatz wurde aktualisiert
								$message .= sprintf(tool_msg_cfg_id_updated, $config[dbPollCfg::field_name]);
							}
					}
				}
			}		
		}		
		$this->setMessage($message);
		return $this->dlgConfig();
	} // checkConfig()
  
	public function exportList() {
		global $dbPollQuestion;
		global $dbPollAnswer;
		global $dbPollLog;
		
		$SQL = sprintf( "SELECT * FROM %s WHERE %s!='%s'",
										$dbPollQuestion->getTableName(),
										dbPollQuestion::field_status,
										dbPollQuestion::status_deleted);
		$questions = array();
		if (!$dbPollQuestion->sqlExec($SQL, $questions)) {
			$this->setError(sprintf('[%s - %] %s', __METHOD__, __LINE__, $dbPollQuestion->getError()));
			return false;
		}
		$polls = array();
		
		$access_array = array(
			dbPollQuestion::access_kit			=> poll_access_kit,
			dbPollQuestion::access_public		=> poll_access_public
		);
		
		$status_array = array(
			dbPollQuestion::status_active		=> poll_status_active,
			dbPollQuestion::status_deleted	=> poll_status_deleted,
			dbPollQuestion::status_locked		=> poll_status_locked
		);
		
		foreach ($questions as $question) {
			
			$poll = array(
				poll_th_id				=> $question[dbPollQuestion::field_id],
				poll_th_name			=> $question[dbPollQuestion::field_name],
				poll_th_status		=> $status_array[$question[dbPollQuestion::field_status]],
				poll_th_access		=> $access_array[$question[dbPollQuestion::field_access]],
				poll_th_header		=> $question[dbPollQuestion::field_header],
				poll_th_intro			=> $question[dbPollQuestion::field_intro],
				poll_th_question	=> $question[dbPollQuestion::field_question]				
			);
			$SQL = sprintf( "SELECT * FROM %s WHERE %s='%s' AND %s='%s' ORDER BY FIND_IN_SET(%s, '%s')",
											$dbPollAnswer->getTableName(),
											dbPollAnswer::field_question_id,
											$question[dbPollQuestion::field_id],
											dbPollAnswer::field_status,
											dbPollAnswer::status_active,
											dbPollAnswer::field_id,
											$question[dbPollQuestion::field_answers]);
			$answers = array();
			if (!$dbPollAnswer->sqlExec($SQL, $answers)) {
				$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbPollAnswer->getError()));
				return false;
			}
			$i = 1;
			$clicks_total = 0;
			foreach ($answers as $answer) {
				$poll[sprintf(poll_th_answer_x, $i)] = $answer[dbPollAnswer::field_answer];
				$poll[sprintf(poll_th_answer_x_clicks, $i)] = $answer[dbPollAnswer::field_clicks];
				$clicks_total += $answer[dbPollAnswer::field_clicks];
				$i++;
			}
			$poll[poll_th_clicks_total] = $clicks_total;
			$polls[] = $poll;
		}
		
		$path = WB_PATH.'/media/poll.csv';
		$fp = fopen($path, 'w');
		$header = array();
		foreach ($polls[0] as $key => $value) {
			$header[] = $key;
		}
		fputcsv($fp, $header, ';');
		foreach ($polls as $fields) {
    	fputcsv($fp, $fields, ';');
		}
		fclose($fp);
		
		$this->setMessage(sprintf(poll_msg_poll_csv_export_success, str_replace(WB_PATH, WB_URL, $path)));
		return $this->dlgList();
	} // exportList()
	
} // class pollBackend

?>