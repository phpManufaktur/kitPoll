<?php

/**
 * kitPoll
 *
 * @author Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @link http://phpmanufaktur.de
 * @copyright 2011 - 2012
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

// include class.secure.php to protect this file and the whole CMS!
if (defined('WB_PATH')) {
  if (defined('LEPTON_VERSION'))
    include(WB_PATH.'/framework/class.secure.php');
}
else {
  $oneback = "../";
  $root = $oneback;
  $level = 1;
  while (($level < 10) && (!file_exists($root.'/framework/class.secure.php'))) {
    $root .= $oneback;
    $level += 1;
  }
  if (file_exists($root.'/framework/class.secure.php')) {
    include($root.'/framework/class.secure.php');
  }
  else {
    trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
  }
}
// end include class.secure.php

require_once(WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/initialize.php');
require_once(WB_PATH.'/include/captcha/captcha.php');
require_once(WB_PATH.'/modules/kit_form/class.frontend.php');

//require_once(WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/include/pchart/pChart/pData.class');
//require_once(WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/include/pchart/pChart/pChart.class');

/* pChart library inclusions */
include(WB_PATH.'/modules/pchart/class/pDraw.class.php');
include(WB_PATH.'/modules/pchart/class/pImage.class.php');
include(WB_PATH.'/modules/pchart/class/pData.class.php');
include(WB_PATH.'/modules/pchart/class/pPie.class.php');

global $dbPollQuestion;
global $dbPollAnswer;
global $dbPollLog;
global $dbPollCfg;

if (!is_object($dbPollQuestion)) 	$dbPollQuestion = new dbPollQuestion();
if (!is_object($dbPollAnswer))		$dbPollAnswer = new dbPollAnswer();
if (!is_object($dbPollLog))				$dbPollLog = new dbPollLog();
if (!is_object($dbPollCfg))				$dbPollCfg = new dbPollCfg();


class pollFrontend {

	const request_action			= 'act';

	const action_default			= 'def';
	const action_login				= 'aut';
	const action_logout				= 'out';
	const action_error				= 'err';
	const action_message			= 'msg';
	const action_poll_check		= 'polc';

	private $page_link 								= '';
	private $img_url									= '';
	private $template_path						= '';
	private $error										= '';
	private $message									= '';
	private $pchart_path							= '';
	private $pchart_img_path					= '';
	private $pchart_img_url						= '';

	const session_temp_vars						= 'ptv';
	const session_check_cookie				= 'kit_poll_scc';
	const session_identity						= 'kit_poll_sid';

	const cookie_identity							= 'kit_poll_id';

	const param_preset									= 'preset';
	const param_name										= 'name';
	const param_css											= 'css';
	const param_chart										= 'chart';
	const param_chart_width							= 'chart_width';
	const param_chart_height						= 'chart_height';
	const param_chart_max_words					= 'chart_max_words';
	const param_chart_bkgnd_color_start	= 'chart_bkgnd_color_start';
	const param_chart_bkgnd_color_end		= 'chart_bkgnd_color_end';
	const param_chart_bkgnd_alpha				= 'chart_bkgnd_alpha';
	const param_chart_border_color			= 'chart_border_color';
	const param_chart_legend_alpha			= 'chart_legend_alpha';
	const param_chart_text_color				= 'chart_text_color';

	const chart_pie										= 'pie';
	const chart_bar										= 'bar';

	private $params = array(
		self::param_preset										=> 1,
		self::param_name											=> '',
		self::param_css												=> true,
		self::param_chart											=> self::chart_pie,
		self::param_chart_width								=> 300,
		self::param_chart_height							=> 300,
		self::param_chart_max_words						=> 5,
		self::param_chart_bkgnd_color_start		=> '#ffefd5',
		self::param_chart_bkgnd_color_end			=> '#ffe4b5',
		self::param_chart_bkgnd_alpha					=> 100,
		self::param_chart_border_color				=> '#ffdead',
		self::param_chart_legend_alpha				=> 40,
		self::param_chart_text_color					=> '#505050'
	);

	private $question					= array();
	private $answers					= array();
	private $chart						= array('active' => 0);
	private $identity_id			= '';
	private $identity_ip 			= '';
	private $poll_allowed			= false;
	private $polling					= array();

	const identity_unknown		= 0;
	const identity_kit				= 1;
	const identity_cookie			= 2;
	const identity_session		= 3;

	private $identity_method  = self::identity_unknown;

	public function __construct() {
		global $kitLibrary;
		$url = '';
		$_SESSION['FRONTEND'] = true;
		$kitLibrary->getPageLinkByPageID(PAGE_ID, $url);
		$this->page_link = $url;
		$this->template_path = WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/htt/'.$this->params[self::param_preset].'/'.KIT_POLL_LANGUAGE.'/' ;
		$this->img_url = WB_URL. '/modules/'.basename(dirname(__FILE__)).'/images/';
		$this->pchart_path = WB_PATH.'/modules/pchart/';
		$this->pchart_img_path = WB_PATH.MEDIA_DIRECTORY.'/kit_poll/poll/'.PAGE_ID.'/';
		$this->pchart_img_url = str_replace(WB_PATH, WB_URL, $this->pchart_img_path);
		date_default_timezone_set(tool_cfg_time_zone);
	} // __construct()

	public function getParams() {
		return $this->params;
	} // getParams()

	public function setParams($params = array()) {
		$this->params = $params;
		$this->template_path = WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/htt/'.$this->params[self::param_preset].'/'.KIT_POLL_LANGUAGE.'/';
		if (!file_exists($this->template_path)) {
			$this->setError(sprintf(form_error_preset_not_exists, '/modules/'.basename(dirname(__FILE__)).'/htt/'.$this->params[self::param_preset].'/'.KIT_POLL_LANGUAGE.'/'));
			return false;
		}
		return true;
	} // setParams()

	/**
    * Set $this->error to $error
    *
    * @param STR $error
    */
  public function setError($error) {
  	$this->error = $error;
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
   * Gibt das gewuenschte Template zurueck
   *
   * @param STR $template
   * @param ARRAY $template_data
   */
  public function getTemplate($template, $template_data) {
  	global $parser;
  	try {
  		$result = $parser->get($this->template_path.$template, $template_data);
  	} catch (Exception $e) {
  		$this->setError(sprintf(form_error_template_error, $template, $e->getMessage()));
  		return false;
  	}
  	return $result;
  } // getTemplate()

  private function setTempVars($vars=array()) {
		$_SESSION[self::session_temp_vars] = http_build_query($vars);
	} // setTempVars()

	private function getTempVars() {
		if (isset($_SESSION[self::session_temp_vars])) {
			parse_str($_SESSION[self::session_temp_vars], $vars);
			foreach ($vars as $key => $value) {
				if (!isset($_REQUEST[$key])) $_REQUEST[$key] = $value;
			}
			unset($_SESSION[self::session_temp_vars]);
		}
	} // getTempVars()

	/**
   * Verhindert XSS Cross Site Scripting
   *
   * @param REFERENCE ARRAY $request
   * @return ARRAY $request
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

  /**
   * ACTION HANDLER
   * @return STR result
   */
  public function action() {
  	global $kitContactInterface;
  	// temporaere Variablen in $_REQUESTs umschreiben...
  	$this->getTempVars();
  	// CSS laden?
    if ($this->params[self::param_css]) {
			if (!is_registered_droplet_css('kit_poll', PAGE_ID)) {
	  		if (!register_droplet_css('kit_poll', PAGE_ID, 'kit_poll', 'frontend.css'));
			}
    }
    elseif (is_registered_droplet_css('kit_poll', PAGE_ID)) {
		  unregister_droplet_css('kit_poll', PAGE_ID);
    }


  	// Umfrage Daten einlesen
  	if ($this->getPollData()) {
  		// nur ausfuehren, wenn die Daten eingelesen werden konnten
	  	$html_allowed = array();
	  	foreach ($_REQUEST as $key => $value) {
	  		if (!in_array($key, $html_allowed)) {
	  			$_REQUEST[$key] = $this->xssPrevent($value);
	  		}
	  	}
	    $action = isset($_REQUEST[self::request_action]) ? $_REQUEST[self::request_action] : self::action_default;

	    // pruefen ob eine Authentifizierung erforderlich ist...
	    if ((!$action == self::action_logout) || !$this->isAuthenticated()) {
	    	if (!$this->isError() && !$this->isMessage()) {
	    		// Login Dialog anzeigen
	    		$action = self::action_login;
	    	}
	    	elseif ($this->isMessage()) {
	    		$action = self::action_message;
	    	}
			}

			// Identitaet des Besuchers ermitteln
			$this->checkIdentity();

			$this->checkPollingAllowed();

			switch ($action):
	    case self::action_logout:
	   		$kitContactInterface->logout();
  			// wichtig: kein break, direkt den login Dialog anzeigen!
	    case self::action_login:
	    	// Login Dialog
	    	$login = $this->accountLogin();
				if (is_string($login)) {
					$result = $login; // Login ist noch nicht erfolgreich
				}
				elseif (is_bool($login) && ($login == false)) {
					$result = false; // Fehler...
				}
				elseif ($this->isAuthenticated()) {
					// Login OK - Umfrage anzeigen
					$result = $this->showPoll();
				}
				else {
					$result = $login;
				}
	    	break;
	    case self::action_message:
	    	// Mitteilung anzeigen
	    	$data = array('message' => $this->getMessage());
	    	$result = $this->getTemplate('message.htt', $data);
	    	break;
	    case self::action_poll_check:
	    	$result = $this->checkPoll();
	    	break;
	  	case self::action_default:
		  default:
		  	$result = $this->showPoll();
		  endswitch;
  	}
  	if ($this->isError()) {
  		$data = array('error' => $this->getError());
  		$result = $this->getTemplate('error.htt', $data);
  	}
		return $result;
  } // action


  /**
	 * Convert a hexa decimal color code to its RGB equivalent
	 *
	 * @param string $hexStr (hexadecimal color value)
	 * @param boolean $returnAsString (if set true, returns the value separated by the separator character. Otherwise returns associative array)
	 * @param string $seperator (to separate RGB values. Applicable only if second parameter is true.)
	 * @return array or string (depending on second parameter. Returns False if invalid hex color value)
	 */
	public function hex2RGB($hexStr, $returnAsString = false, $separator = ',') {
    $hexStr = preg_replace("/[^0-9A-Fa-f]/", '', $hexStr); // Gets a proper hex string
    $rgbArray = array();
    if (strlen($hexStr) == 6) { //If a proper hex code, convert using bitwise operation. No overhead... faster
      $colorVal = hexdec($hexStr);
      $rgbArray['red'] = 0xFF & ($colorVal >> 0x10);
      $rgbArray['green'] = 0xFF & ($colorVal >> 0x8);
      $rgbArray['blue'] = 0xFF & $colorVal;
    }
    elseif (strlen($hexStr) == 3) { //if shorthand notation, need some string manipulations
      $rgbArray['red'] = hexdec(str_repeat(substr($hexStr, 0, 1), 2));
      $rgbArray['green'] = hexdec(str_repeat(substr($hexStr, 1, 1), 2));
      $rgbArray['blue'] = hexdec(str_repeat(substr($hexStr, 2, 1), 2));
    }
    else {
      return false; //Invalid hex color code
    }
    return $returnAsString ? implode($separator, $rgbArray) : $rgbArray; // returns the rgb string or the associative array
	}

  public function checkPollingAllowed() {
  	global $dbPollLog;
  	// Umfragedaten / Statistik laden
  	if ($this->identity_method == self::identity_session) {
  		// IP zur Identifizierung verwenden
  		$SQL = sprintf( "SELECT * FROM %s WHERE %s='%s' AND %s='%s'",
  										$dbPollLog->getTableName(),
  										dbPollLog::field_question_id,
  										$this->question[dbPollQuestion::field_id],
  										dbPollLog::field_ip,
  										$this->identity_ip);
  	}
  	else {
	  	$SQL = sprintf( "SELECT * FROM %s WHERE %s='%s' AND %s='%s'",
	  									$dbPollLog->getTableName(),
	  									dbPollLog::field_question_id,
	  									$this->question[dbPollQuestion::field_id],
	  									dbPollLog::field_mark,
	  									$this->identity_id);
  	}
  	$log = array();
  	if (!$dbPollLog->sqlExec($SQL, $log)) {
  		$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbPollLog->getError()));
  		return false;
  	}
  	if (count($log) > 0) {
  		$this->polling = $log[0];
  	}
  	else {
  		$this->polling = array(	dbPollLog::field_ip 					=> $this->identity_ip,
  														dbPollLog::field_mark					=> '',
  														dbPollLog::field_question_id	=> $this->question[dbPollQuestion::field_id] );
  	}
  	if ((($this->identity_method == self::identity_cookie) || ($this->identity_method == self::identity_kit)) &&
  			($this->polling[dbPollLog::field_mark] == $this->identity_id)) {
  		$this->poll_allowed = false;
  	}
  	elseif (($this->identity_method == self::identity_session) && ($this->polling[dbPollLog::field_ip] == $this->identity_ip)) {
  		$this->poll_allowed = false;
  	}
  	else {
  		$this->poll_allowed = true;
  	}
  	return true;
  } // isPollingAllowed()

  /**
   * Ueberprueft die Identitaet des Besuchers und legt eine eindeutige
   * ID fest um doppelte Abstimmungen zu verhindern
   * Setzt self::identity_ip, self::identity_id und self::identity_method
   *
   * @return BOOL
   */
  public function checkIdentity() {
  	global $kitContactInterface;
  	global $kitLibrary;

  	if (!isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$this->identity_ip = $_SERVER['REMOTE_ADDR'];
		}
		else {
			$this->identity_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}

  	// bevorzugt: ueber KIT identifizieren
  	if ($kitContactInterface->isAuthenticated() && isset($_SESSION[kitContactInterface::session_kit_key])) {
  		$this->identity_id = $_SESSION[kitContactInterface::session_kit_key];
  		$this->identity_method = self::identity_kit;
  		return true;
  	}
  	// Versuche ueber Cookie zu identifizieren
  	if (isset($_COOKIE[self::cookie_identity])) {
  		$this->identity_id = $_COOKIE[self::cookie_identity];
  		$this->identity_method = self::identity_cookie;
  		return true;
  	}
  	// ist eine Session Identity gesetzt ?
  	if (isset($_SESSION[self::session_identity])) {
  		$this->identity_id = $_SESSION[self::session_identity];
  		$this->identity_method = self::identity_session;
  		return true;
  	}

  	if (isset($_SESSION[self::session_check_cookie])) {
  		// es laesst sich kein Cookie setzen, Sesssion ID verwenden...
  		$_SESSION[self::session_identity] = $_SESSION[self::session_check_cookie];
  		unset($_SESSION[self::session_check_cookie]);
  		$this->identity_id = $_SESSION[self::session_identity];
  		$this->identity_method = self::identity_session;
  		return true;
  	}

  	// setze ein Cookie wenn noch nicht probiert...
  	$guid = $kitLibrary->createGUID();
	  $_SESSION[self::session_check_cookie] = $guid;
	  if (setcookie(self::cookie_identity, $guid, time()+(3600*24*30))) {
	  	$this->identity_id = $guid;
	  	$this->identity_method = self::identity_unknown;
	  	return true;
	  }
  	return false;
  } // checkIdentity

  /**
   * Liest die Daten der Umfrage aus der Datenbank aus und speichert sie
   * in self::question und self::answers
   *
   * @return BOOL
   */
  public function getPollData() {
  	global $dbPollQuestion;
  	global $dbPollAnswer;
  	global $dbPollLog;

  	if (!isset($this->params[self::param_name]) || empty($this->params[self::param_name])) {
  		$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, poll_error_no_poll_name));
  		return false;
  	}

  	$where = array(dbPollQuestion::field_name => $this->params[self::param_name]);
  	$questions = array();
  	if (!$dbPollQuestion->sqlSelectRecord($where, $questions)) {
  		$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbPollQuestion->getError()));
  		return false;
  	}
  	if (count($questions) > 0) {
  		$this->question = $questions[0];
  		if ($this->question[dbPollQuestion::field_status] != dbPollQuestion::status_active) {
  			$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(poll_error_poll_not_active, $this->params[self::param_name])));
  			return false;
  		}
  		// Fragen in der richtigen Reihenfolge laden
  		$SQL = sprintf( "SELECT * FROM %s WHERE %s='%s' AND %s='%s' ORDER BY FIND_IN_SET(%s, '%s')",
  										$dbPollAnswer->getTableName(),
  										dbPollAnswer::field_question_id,
  										$this->question[dbPollQuestion::field_id],
  										dbPollAnswer::field_status,
  										dbPollAnswer::status_active,
  										dbPollAnswer::field_id,
  										$this->question[dbPollQuestion::field_answers]);
  		$answers = array();
  		if (!$dbPollAnswer->sqlExec($SQL, $answers)) {
  			$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbPollAnswer->getError()));
  			return false;
  		}
  		if (count($answers) > 0) {
  			$this->answers = array();
  			foreach ($answers as $answer) {
  				$this->answers[$answer[dbPollAnswer::field_id]] = $answer;
  			}
  		}
  		else {
  			$this->answers = array();
  		}
  	}
  	else {
  		$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(poll_error_invalid_poll_name, $this->params[self::param_name])));
  		return false;
  	}
  	return true;
  } // getPollData()

  /**
   * Ermittelt ob der Besucher berechtigt ist auf die Umfrage zuzugreifen
   *
   * @return BOOL
   */
  public function isAuthenticated() {
  	global $kitContactInterface;
		if (!isset($this->question[dbPollQuestion::field_access])) {
  		// Daten sind unvollstaendig oder ungueltig!
  		$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, poll_error_data_integrity_invalid));
  		return false;
  	}
  	if ($this->question[dbPollQuestion::field_access] == dbPollQuestion::access_public) {
  		// die Umfrage ist oeffentlich, keine Authentifizierung erforderlich
  		return true;
  	}
  	if ($kitContactInterface->isAuthenticated()) {
  		// der Besucher ist angemeldet
  		if (empty($this->question[dbPollQuestion::field_kit_groups])) {
  			// keine weitere Pruefung erforderlich
  			return true;
  		}
  		else {
  			// es sind KIT Gruppen angegeben, weitere Pruefung...
  			$groups = explode(',', $this->question[dbPollQuestion::field_kit_groups]);
  			// Kategorien des Besuchers auslesen
  			$categories = array();
  			if (!$kitContactInterface->getCategories($_SESSION[kitContactInterface::session_kit_contact_id], $categories)) {
					$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $kitContactInterface->getError()));
					return false;
				}
  			$checked = false;
  			foreach ($groups as $group) {
  				if (!$kitContactInterface->existsCategory(kitContactInterface::category_type_intern, $group) &&
  						!$kitContactInterface->existsCategory(kitContactInterface::category_type_newsletter, $group)) {
  					// die angegebene Gruppe existiert nicht!
  					$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(poll_error_kit_group_invalid, $group)));
  					return false;
  				}
  				if (in_array($group, $categories)) {
  					// Besucher ist Mitglied in einer erlaubten Gruppe
  					return true;
  				}
  			}
  			// der Besucher ist nicht berechtigt
  			$this->setMessage(poll_msg_authenticated_wrong_group);
  			return false;
  		}
  	}
  	return false;
  } // isAuthenticated()

  /**
   * Bindet den kitForm Login Dialog ein
   *
   * @return STR Dialog oder BOOL TRUE bei Erfolg bzw. BOOL FALSE bei Meldungen bzw. Fehlern
   */
  public function accountLogin() {
  	global $kitContactInterface;
		global $dbPollCfg;

		$dlg = $dbPollCfg->getValue(dbPollCfg::cfgFormDlgLogin);

		$form = new formFrontend();
		$params = $form->getParams();
		$params['form'] = $dlg;
		$params['return'] = true;
		$form->setParams($params);

		$result = $form->action();
		if (is_string($result)) {
			return $result;
		}
		elseif (is_bool($result) && ($result == false) && $form->isError()) {
			$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $form->getError()));
			return false;
		}
		elseif (is_bool($result) && ($result == true)) {
			return true;
		}
		else {
			$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, market_error_undefined));
			return false;
		}

  } // login()

  public function showPoll() {

  	$answers = array();
  	foreach ($this->answers as $answer) {
  		$answers[] = array(
  			'text'		=> $answer[dbPollAnswer::field_answer],
  			'name'		=> dbPollAnswer::field_id,
  			'value'		=> $answer[dbPollAnswer::field_id]
  		);
  	}

  	$question = array(
  		'head'						=> $this->question[dbPollQuestion::field_header],
  		'intro'		 				=> $this->question[dbPollQuestion::field_intro],
  		'question'				=> $this->question[dbPollQuestion::field_question],
  		'single_answer'		=> $this->question[dbPollQuestion::field_answers_mode],
  		'show_result'			=> $this->question[dbPollQuestion::field_show_results],
  		'release_result'	=> $this->question[dbPollQuestion::field_release],
  		'poll_allowed'		=> ($this->poll_allowed) ? 1 : 0,
  		'answers'					=> $answers
  	);
  	$this->createChart();
  	$data = array(
  		'form_name'			=> 'poll_form',
  		'form_action'		=> $this->page_link,
  		'action_name'		=> self::request_action,
  		'action_value'	=> self::action_poll_check,
  		'is_message'		=> $this->isMessage() ? 1 : 0,
  		'message'				=> $this->getMessage(),
  		'question'			=> $question,
  		'chart'					=> $this->chart,
  	);
  	return $this->getTemplate('poll.htt', $data);
  } // showPoll()

  public function checkPoll() {
  	global $dbPollLog;
  	global $dbPollAnswer;
  	if (!isset($_REQUEST[dbPollAnswer::field_id])) {
  		$this->setMessage(poll_msg_no_answer);
  		return $this->showPoll();
  	}
  	// Antworten immer in ein Array einlesen
  	if ($this->question[dbPollQuestion::field_answers_mode] == dbPollQuestion::answers_single) {
  		// nur eine Antwort erlaubt
  		$answers = array($_REQUEST[dbPollAnswer::field_id]);
  	}
  	else {
  		// mehrere Antworten moeglich
  		$answers = $_REQUEST[dbPollAnswer::field_id];
  	}
  	if (count($answers) < 1) {
  		$this->setMessage(poll_msg_no_answer);
  		return $this->showPoll();
  	}
  	foreach ($answers as $answer_id) {
  		$this->answers[$answer_id][dbPollAnswer::field_clicks]++;
  		$data = array(dbPollAnswer::field_clicks => $this->answers[$answer_id][dbPollAnswer::field_clicks]);
  		$where = array(dbPollAnswer::field_id => $answer_id);
  		if (!$dbPollAnswer->sqlUpdateRecord($data, $where)) {
  			$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbPollAnswer->getError()));
  			return false;
  		}
  	}
  	$this->polling[dbPollLog::field_ip] = $this->identity_ip;
  	$this->polling[dbPollLog::field_mark] = $this->identity_id;

  	if (isset($this->polling[dbPollLog::field_id])) {
  		$where = array(dbPollLog::field_id => $this->polling[dbPollLog::field_id]);
  		$data = $this->polling;
  		if (!$dbPollLog->sqlUpdateRecord($data, $where)) {
  			$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbPollLog->getError()));
  			return false;
  		}
  	}
  	else {
  		$data = $this->polling;
  		$id = -1;
  		if (!$dbPollLog->sqlInsertRecord($data, $id)) {
  			$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbPollLog->getError()));
  			return false;
  		}
  		$this->polling[dbPollLog::field_id] = $id;
  	}
  	$this->setMessage(poll_msg_thanks_for_answer);
  	$this->poll_allowed=false;
  	return $this->showPoll();
  } // checkPoll()

	public function createChart() {

    if (!file_exists($this->pchart_img_path)) {
    	if (!mkdir($this->pchart_img_path, 0755, true)) {
    		$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(poll_error_create_dir, $this->pchart_img_path)));
    		return false;
    	}
    }

    $answers_values = array();
    $answers_text		= array();
    foreach ($this->answers as $answer) {
    	$answers_values[] = $answer[dbPollAnswer::field_clicks];
    	$aa = explode(' ', $answer[dbPollAnswer::field_answer]);
    	$ant = '';
    	$i = 1;
    	foreach ($aa as $aw) {
    		if (!empty($ant)) $ant .= ' ';
    		if ($i > $this->params[self::param_chart_max_words]) {
    			$ant .= '...';
    			break;
    		}
    		$ant .= $aw;
    		$i++;
    	}
    	$answers_text[] = $ant;
    }

    // Tortengrafik
    if ($this->params[self::param_chart] == self::chart_pie) {
    	/* pData object creation */
			$MyData = new pData();

			/* Data definition */
			$MyData->addPoints($answers_values,"Value");

			/* Labels definition */
			$MyData->addPoints($answers_text,"Legend");
			$MyData->setAbscissa("Legend");

			/* Create the pChart object */
			$width = $this->params[self::param_chart_width];
	    $height = $this->params[self::param_chart_height];

			$myPicture = new pImage($width,$height,$MyData);

			/* Draw a gradient background */
			$cStart = $this->hex2RGB($this->params[self::param_chart_bkgnd_color_start]);
			$cEnd 	= $this->hex2RGB($this->params[self::param_chart_bkgnd_color_end]);
			$alpha 	= $this->params[self::param_chart_bkgnd_alpha];
			$myPicture->drawGradientArea(0,0,$width,$height,DIRECTION_HORIZONTAL,array("StartR"=>$cStart['red'],"StartG"=>$cStart['green'],"StartB"=>$cStart['blue'],"EndR"=>$cEnd['red'],"EndG"=>$cEnd['red'],"EndB"=>$cEnd['red'],"Alpha"=>$alpha));

			$cText 	= $this->hex2RGB($this->params[self::param_chart_text_color]);
			$cBorder	= $this->hex2RGB($this->params[self::param_chart_border_color]);

			/* Add a border to the picture */
			$myPicture->drawRectangle(0,0,$width-1,$height-1,array("R"=>$cBorder['red'],"G"=>$cBorder['green'],"B"=>$cBorder['blue']));

			/* Create the pPie object */
			$PieChart = new pPie($myPicture,$MyData);

			/* Enable shadow computing */
			$myPicture->setShadow(false);

			/* Set the default font properties */
			$myPicture->setFontProperties(array("FontName"=>$this->pchart_path.'fonts/Forgotte.ttf',"FontSize"=>10,"R"=>$cText['red'],"G"=>$cText['green'],"B"=>$cText['blue']));

			/* Draw a splitted pie chart */
			$PieChart->draw3DPie($width/2,($height/3)*2,array('Radius'=>$width/3,"WriteValues"=>TRUE,"DataGapAngle"=>10,"DataGapRadius"=>6,"Border"=>TRUE, 'ValueR'=>$cText['red'], 'ValueG'=>$cText['green'], 'ValueB'=>$cText['blue']));

			/* Write the legend box */
			$myPicture->setShadow(false);
			$alpha = $this->params[self::param_chart_legend_alpha];
			$PieChart->drawPieLegend($width/20,($height/20)*4,array("Alpha"=>$alpha));

			/* Write the picture title */
			$myPicture->setFontProperties(array("FontName"=>$this->pchart_path."fonts/Silkscreen.ttf","FontSize"=>6));
			$myPicture->drawText($width/20,($height/20)*2,$this->question[dbPollQuestion::field_question], array("R"=>$cText['red'],"G"=>$cText['green'],"B"=>$cText['blue']));

			$path = sprintf('%s%s_%s.png', $this->pchart_img_path, $this->params[self::param_chart], $this->question[dbPollQuestion::field_name]);
			$url = str_replace(WB_PATH, WB_URL, $path);
			$myPicture->Render($path);
			$this->chart['active'] = 1;
	    $this->chart['src'] = $url;
	    $this->chart['width'] = $width;
	    $this->chart['height'] = $height;
	    $this->chart['title'] = $this->question[dbPollQuestion::field_header];
    }
    elseif ($this->params[self::param_chart] == self::chart_bar) {
    	// Balkengrafik
		 	$MyData = new pData();
		 	$MyData->loadPalette($this->pchart_path."palettes/blind.color", true);

		 	for ($i = 0; $i < count($answers_values); $i++) {
		 		$MyData->addPoints(array($answers_values[$i]), $answers_text[$i]);
		 	}
		 	$MyData->setAxisName(0, poll_label_clicks);
		 	$MyData->addPoints(array(poll_label_answers),"Answers");
		 	$MyData->setSerieDescription("Answers","Answer");
		 	$MyData->setAbscissa("Answers");

		 	$cText 	= $this->hex2RGB($this->params[self::param_chart_text_color]);
			$cBorder	= $this->hex2RGB($this->params[self::param_chart_border_color]);
			$cStart = $this->hex2RGB($this->params[self::param_chart_bkgnd_color_start]);
			$cEnd 	= $this->hex2RGB($this->params[self::param_chart_bkgnd_color_end]);
			$alpha 	= $this->params[self::param_chart_bkgnd_alpha];

		 	$width = $this->params[self::param_chart_width];
	    $height = $this->params[self::param_chart_height];

			$myPicture = new pImage($width,$height,$MyData);
			$myPicture->drawGradientArea(0,0,$width,$height,DIRECTION_VERTICAL,array("StartR"=>$cStart['red'],"StartG"=>$cStart['green'],"StartB"=>$cStart['blue'],"EndR"=>$cEnd['red'],"EndG"=>$cEnd['green'],"EndB"=>$cEnd['blue'],"Alpha"=>100));
		 	$myPicture->drawGradientArea(0,0,$width,$height,DIRECTION_HORIZONTAL,array("StartR"=>$cStart['red'],"StartG"=>$cStart['green'],"StartB"=>$cStart['blue'],"EndR"=>$cEnd['red'],"EndG"=>$cEnd['green'],"EndB"=>$cEnd['blue'],"Alpha"=>20));
		 	$myPicture->setFontProperties(array("FontName"=>$this->pchart_path."fonts/pf_arma_five.ttf","FontSize"=>6));

		 	$cBorder	= $this->hex2RGB($this->params[self::param_chart_border_color]);

			/* Add a border to the picture */
			$myPicture->drawRectangle(0,0,$width-1,$height-1,array("R"=>$cBorder['red'],"G"=>$cBorder['green'],"B"=>$cBorder['blue']));

		 	/* Draw the scale  */
		 	$myPicture->setGraphArea(($width/20)*3,($height/10)*4,($width/20)*18,($height/10)*9);
		 	$myPicture->drawScale(array("CycleBackground"=>TRUE,"DrawSubTicks"=>TRUE,"GridR"=>0,"GridG"=>0,"GridB"=>0,"GridAlpha"=>10));

		 	/* Turn on shadow computing */
		 	$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));

		 	/* Draw the chart */
		 	$settings = array("Gradient"=>TRUE,"DisplayPos"=>LABEL_POS_INSIDE,"DisplayValues"=>TRUE,"DisplayR"=>255,"DisplayG"=>255,"DisplayB"=>255,"DisplayShadow"=>TRUE,"Surrounding"=>10);
 			$myPicture->drawBarChart($settings);

		 	/* Write the chart legend */
		 	$myPicture->setShadow(false);
			$alpha = $this->params[self::param_chart_legend_alpha];
			$myPicture->drawLegend(($width/20)*2,($height/10)*2,array("Style"=>LEGEND_BOX,"Mode"=>LEGEND_VERTICAL, 'Alpha'=>$alpha));

		 	/* Write the picture title */
			$myPicture->setFontProperties(array("FontName"=>$this->pchart_path."fonts/Silkscreen.ttf","FontSize"=>6));
			$myPicture->drawText(($width/20)*2,($height/10)*1,$this->question[dbPollQuestion::field_question], array("R"=>$cText['red'],"G"=>$cText['green'],"B"=>$cText['blue']));

		 	$path = sprintf('%s%s_%s.png', $this->pchart_img_path, $this->params[self::param_chart], $this->question[dbPollQuestion::field_name]);
			$url = str_replace(WB_PATH, WB_URL, $path);
			$myPicture->Render($path);
			$this->chart['active'] = 1;
	    $this->chart['src'] = $url;
	    $this->chart['width'] = $width;
	    $this->chart['height'] = $height;
	    $this->chart['title'] = $this->question[dbPollQuestion::field_header];
    }
    else {
    	// unbekannter Diagrammtyp...
    	$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(poll_error_unknown_chart_type, $this->params[self::param_chart])));
    	return false;
    }
  }

} // class pollFrontend

?>