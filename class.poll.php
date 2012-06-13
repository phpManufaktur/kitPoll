<?php
/**
 * project_name
 * 
 * @author Ralf Hertsch (ralf.hertsch@phpmanufaktur.de)
 * @link http://phpmanufaktur.de
 * @copyright 2011
 * @license GNU GPL (http://www.gnu.org/licenses/gpl.html)
 * @version $Id$
 */

// prevent this file from being accessed directly
if (!defined('WB_PATH')) die('invalid call of '.$_SERVER['SCRIPT_NAME']);

class dbPollQuestion extends dbConnectLE {
	
	const field_id							= 'quest_id';
	const field_name						= 'quest_name';
	const field_page_title			= 'quest_page_title';
	const field_page_description= 'quest_page_desc';
	const field_page_keywords		= 'quest_page_keys';
	const field_header					= 'quest_header';
	const field_intro						= 'quest_intro';  
	const field_question				= 'quest_question';
	const field_answers					= 'quest_answers';
	const field_answers_mode		= 'quest_answers_mode';
	const field_access					= 'quest_access';
	const field_kit_groups			= 'quest_kit_groups';
	const field_date_start			= 'quest_date_start';
	const field_date_end				= 'quest_date_end';
	const field_status					= 'quest_status';
	const field_show_results		= 'quest_show_results';
	const field_release					= 'quest_release';
	const field_timestamp				= 'quest_timestamp';

	const answers_single				= 1;
	const answers_multiple			= 2;
	
	public $answers_array = array(
		array('key'	=> self::answers_single,	'value' => poll_answers_single),
		array('key'	=> self::answers_multiple,'value'	=> poll_answers_multiple)
	);
	
	const access_public					= 1;
	const access_kit						= 2;
	
	public $access_array = array(
		array('key' => self::access_public, 	'value'	=> poll_access_public),
		array('key' => self::access_kit, 			'value' => poll_access_kit)
	);
	
	const status_active					= 1;
	const status_locked					= 2;
	const status_deleted				= 3; 

	public $status_array = array(
		array('key' => self::status_active,		'value'	=> poll_status_active),
		array('key' => self::status_locked,		'value' => poll_status_locked),
		array('key' => self::status_deleted,	'value'	=> poll_status_deleted)
	);
	
	const show_immediate				= 1; // sofort
	const show_after_expiration	= 2; // nach Ablauf
	const show_after_release		= 3; // nach Freigabe

	public $show_results_array = array(
		array('key' => self::show_immediate,	'value'	=> poll_show_immediate),
		array('key' => self::show_after_expiration, 'value'	=> poll_show_expiration),
		array('key' => self::show_after_release, 'value' => poll_show_release)
	);
	
	const release_automatic			= 1;
	const release_locked				= 2;
	const release_unlocked			= 3;
	
	public $release_array = array(
		array('key' => self::release_automatic, 'value'	=> poll_release_automatic),
		array('key' => self::release_locked,	'value'	=> poll_release_locked),
		array('key' => self::release_unlocked,'value'	=> poll_release_unlocked)
	);
	
	private $createTables 		= false;
  
  public function __construct($createTables = false) {
  	$this->createTables = $createTables;
  	parent::__construct();
  	$this->setTableName('mod_kit_poll_question');
  	$this->addFieldDefinition(self::field_id, "INT(11) NOT NULL AUTO_INCREMENT", true);
  	$this->addFieldDefinition(self::field_name, "VARCHAR(50) NOT NULL DEFAULT ''");
  	$this->addFieldDefinition(self::field_page_title, "VARCHAR(128) NOT NULL DEFAULT ''");
  	$this->addFieldDefinition(self::field_page_description, "VARCHAR(255) NOT NULL DEFAULT ''");
  	$this->addFieldDefinition(self::field_page_keywords, "VARCHAR(255) NOT NULL DEFAULT ''");
  	$this->addFieldDefinition(self::field_header, "VARCHAR(128) NOT NULL DEFAULT ''");
  	$this->addFieldDefinition(self::field_intro, "TEXT NOT NULL DEFAULT ''", false, false, true);
  	$this->addFieldDefinition(self::field_question, "TEXT NOT NULL DEFAULT ''");
  	$this->addFieldDefinition(self::field_answers, "VARCHAR(255) NOT NULL DEFAULT ''");
  	$this->addFieldDefinition(self::field_answers_mode, "TINYINT NOT NULL DEFAULT '".self::answers_single."'");
  	$this->addFieldDefinition(self::field_access, "TINYINT NOT NULL DEFAULT '".self::access_public."'");
  	$this->addFieldDefinition(self::field_kit_groups, "VARCHAR(255) NOT NULL DEFAULT ''");
  	$this->addFieldDefinition(self::field_date_start, "DATE NOT NULL DEFAULT '0000-00-00'");
  	$this->addFieldDefinition(self::field_date_end, "DATE NOT NULL DEFAULT '0000-00-00'");  	
  	$this->addFieldDefinition(self::field_status, "TINYINT NOT NULL DEFAULT '".self::status_active."'");
  	$this->addFieldDefinition(self::field_show_results, "TINYINT NOT NULL DEFAULT '".self::show_immediate."'");
  	$this->addFieldDefinition(self::field_release, "TINYINT NOT NULL DEFAULT '".self::release_automatic."'");
  	$this->addFieldDefinition(self::field_timestamp, "TIMESTAMP");	
  	$this->checkFieldDefinitions();
  	// Tabelle erstellen
  	if ($this->createTables) {
  		if (!$this->sqlTableExists()) {
  			if (!$this->sqlCreateTable()) {
  				$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
  			}
  		}
  	}
  } // __construct()
	
} // class dbPollQuestion

class dbPollAnswer extends dbConnectLE {
	
	const field_id						= 'ans_id';
	const field_question_id		= 'quest_id';
	const field_answer				= 'ans_answer';
	const field_clicks				= 'ans_clicks';
	const field_status				= 'ans_status';
	const field_timestamp			= 'ans_timestamp';
	
	const status_active					= 1;
	const status_deleted				= 3;

	public $status_array = array(
		self::status_active			=> poll_status_active,
		self::status_deleted		=> poll_status_deleted
	);
	
	private $createTables 		= false;
  
  public function __construct($createTables = false) {
  	$this->createTables = $createTables;
  	parent::__construct();
  	$this->setTableName('mod_kit_poll_answer');
  	$this->addFieldDefinition(self::field_id, "INT(11) NOT NULL AUTO_INCREMENT", true);
  	$this->addFieldDefinition(self::field_question_id, "INT(11) NOT NULL DEFAULT '-1'");
  	$this->addFieldDefinition(self::field_answer, "TEXT NOT NULL DEFAULT ''");
  	$this->addFieldDefinition(self::field_clicks, "INT(11) NOT NULL DEFAULT '0'");
  	$this->addFieldDefinition(self::field_status, "TINYINT NOT NULL DEFAULT '".self::status_active."'");
  	$this->addFieldDefinition(self::field_timestamp, "TIMESTAMP");	
  	$this->setIndexFields(array(self::field_question_id));
  	$this->checkFieldDefinitions();
  	// Tabelle erstellen
  	if ($this->createTables) {
  		if (!$this->sqlTableExists()) {
  			if (!$this->sqlCreateTable()) {
  				$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
  			}
  		}
  	}
  } // __construct()
	
} // class dbPollAnswer

class dbPollLog extends dbConnectLE {
	
	const field_id							= 'log_id';
	const field_question_id			= 'quest_id';
	const field_ip							= 'log_ip';
	const field_mark						= 'log_mark';
	const field_timestamp				= 'log_timestamp';
	
	private $createTables 		= false;
  
  public function __construct($createTables = false) {
  	$this->createTables = $createTables;
  	parent::__construct();
  	$this->setTableName('mod_kit_poll_log');
  	$this->addFieldDefinition(self::field_id, "INT(11) NOT NULL AUTO_INCREMENT", true);
  	$this->addFieldDefinition(self::field_question_id, "INT(11) NOT NULL DEFAULT '-1'");
  	$this->addFieldDefinition(self::field_ip, "VARCHAR(255) NOT NULL DEFAULT ''");
  	$this->addFieldDefinition(self::field_mark, "VARCHAR(255) NOT NULL DEFAULT ''");
  	$this->addFieldDefinition(self::field_timestamp, "TIMESTAMP");	
  	$this->setIndexFields(array(self::field_question_id));
  	$this->checkFieldDefinitions();
  	// Tabelle erstellen
  	if ($this->createTables) {
  		if (!$this->sqlTableExists()) {
  			if (!$this->sqlCreateTable()) {
  				$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
  			}
  		}
  	}
  } // __construct()
	
} // class dbPollLog

class dbPollTableSort extends dbConnectLE {
	
	const field_id				= 'sort_id';
	const field_table			= 'sort_table';
	const field_value			= 'sort_value';
	const field_order			= 'sort_order';
	const field_timestamp	= 'sort_timestamp';
	
	private $create_tables = false;
	
	public function __construct($create_tables=false) {
		$this->create_tables = $create_tables;
		parent::__construct();
		$this->setTableName('mod_kit_poll_table_sort');
		$this->addFieldDefinition(self::field_id, "INT(11) NOT NULL AUTO_INCREMENT", true);
		$this->addFieldDefinition(self::field_table, "VARCHAR(64) NOT NULL DEFAULT ''");
		$this->addFieldDefinition(self::field_value, "VARCHAR(255) NOT NULL DEFAULT ''");
		$this->addFieldDefinition(self::field_order, "TEXT NOT NULL DEFAULT ''");
		$this->addFieldDefinition(self::field_timestamp, "TIMESTAMP");
		$this->checkFieldDefinitions();
		if ($this->create_tables) {
			if (!$this->sqlTableExists()) {
				if (!$this->sqlCreateTable()) {
					$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
					return false;
				}
			}
		}
	} // __construct()	
	
} // class dbKITformTableSort

class dbPollCfg extends dbConnectLE {
	
	const field_id						= 'cfg_id';
	const field_name					= 'cfg_name';
	const field_type					= 'cfg_type';
	const field_value					= 'cfg_value';
	const field_label					= 'cfg_label';
	const field_description		= 'cfg_desc';
	const field_status				= 'cfg_status';
	const field_timestamp			= 'cfg_timestamp';
	
	const status_active				= 1;
	const status_deleted			= 0;
	
	const type_undefined			= 0;
	const type_array					= 7;
  const type_boolean				= 1;
  const type_email					= 2;
  const type_float					= 3;
  const type_integer				= 4;
  const type_path						= 5;
  const type_string					= 6;
  const type_url						= 8;
  
  public $type_array = array(
  	self::type_undefined		=> '-UNDEFINED-',
  	self::type_array				=> 'ARRAY',
  	self::type_boolean			=> 'BOOLEAN',
  	self::type_email				=> 'E-MAIL',
  	self::type_float				=> 'FLOAT',
  	self::type_integer			=> 'INTEGER',
  	self::type_path					=> 'PATH',
  	self::type_string				=> 'STRING',
  	self::type_url					=> 'URL'
  );
  
  private $createTables 		= false;
  private $message					= '';
    
  const cfgPollExec					= 'cfgPollExec';
  const cfgFormDlgLogin			= 'cfgFormDlgLogin';
  const cfgFormDlgAccount		= 'cfgFormDlgAccount';
  
  public $config_array = array(
  	array('poll_label_cfg_exec', self::cfgPollExec, self::type_boolean, '1', 'poll_desc_cfg_exec'),
  	array('poll_label_cfg_form_dlg_login', self::cfgFormDlgLogin, self::type_string, 'kit_login', 'poll_desc_cfg_form_dlg_login'),
  	array('poll_label_cfg_form_dlg_account', self::cfgFormDlgAccount, self::type_string, 'kit_account', 'poll_desc_cfg_form_dlg_account'),
  );  
  
  
  public function __construct($createTables = false) {
  	$this->createTables = $createTables;
  	parent::__construct();
  	$this->setTableName('mod_kit_poll_config');
  	$this->addFieldDefinition(self::field_id, "INT(11) NOT NULL AUTO_INCREMENT", true);
  	$this->addFieldDefinition(self::field_name, "VARCHAR(32) NOT NULL DEFAULT ''");
  	$this->addFieldDefinition(self::field_type, "TINYINT UNSIGNED NOT NULL DEFAULT '".self::type_undefined."'");
  	$this->addFieldDefinition(self::field_value, "VARCHAR(255) NOT NULL DEFAULT ''", false, false, true);
  	$this->addFieldDefinition(self::field_label, "VARCHAR(64) NOT NULL DEFAULT 'ed_str_undefined'");
  	$this->addFieldDefinition(self::field_description, "VARCHAR(255) NOT NULL DEFAULT 'ed_str_undefined'");
  	$this->addFieldDefinition(self::field_status, "TINYINT UNSIGNED NOT NULL DEFAULT '".self::status_active."'");
  	$this->addFieldDefinition(self::field_timestamp, "TIMESTAMP");
  	$this->setIndexFields(array(self::field_name));
  	$this->setAllowedHTMLtags('<a><abbr><acronym><span>');
  	$this->checkFieldDefinitions();
  	// Tabelle erstellen
  	if ($this->createTables) {
  		if (!$this->sqlTableExists()) {
  			if (!$this->sqlCreateTable()) {
  				$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
  			}
  		}
  	}
  	// Default Werte garantieren
  	if ($this->sqlTableExists()) {
  		$this->checkConfig();
  	}
  	date_default_timezone_set(tool_cfg_time_zone);
  } // __construct()
  
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
   * Aktualisiert den Wert $new_value des Datensatz $name
   * 
   * @param $new_value STR - Wert, der uebernommen werden soll
   * @param $id INT - ID des Datensatz, dessen Wert aktualisiert werden soll
   * 
   * @return BOOL Ergebnis
   * 
   */
  public function setValueByName($new_value, $name) {
  	$where = array();
  	$where[self::field_name] = $name;
  	$config = array();
  	if (!$this->sqlSelectRecord($where, $config)) {
  		$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
  		return false;
  	}
  	if (sizeof($config) < 1) {
  		$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tool_error_cfg_name, $name)));
  		return false;
  	}
  	return $this->setValue($new_value, $config[0][self::field_id]);
  } // setValueByName()
  
  /**
   * Haengt einen Slash an das Ende des uebergebenen Strings
   * wenn das letzte Zeichen noch kein Slash ist
   *
   * @param STR $path
   * @return STR
   */
  public function addSlash($path) {
  	$path = substr($path, strlen($path)-1, 1) == "/" ? $path : $path."/";
  	return $path;  
  }
  
  /**
   * Wandelt einen String in einen Float Wert um.
   * Geht davon aus, dass Dezimalzahlen mit ',' und nicht mit '.'
   * eingegeben wurden.
   *
   * @param STR $string
   * @return FLOAT
   */
  public function str2float($string) {
  	$string = str_replace('.', '', $string);
		$string = str_replace(',', '.', $string);
		$float = floatval($string);
		return $float;
  }

  public function str2int($string) {
  	$string = str_replace('.', '', $string);
		$string = str_replace(',', '.', $string);
		$int = intval($string);
		return $int;
  }
  
	/**
	 * Ueberprueft die uebergebene E-Mail Adresse auf logische Gueltigkeit
	 *
	 * @param STR $email
	 * @return BOOL
	 */
	public function validateEMail($email) {
		if(preg_match("/^([0-9a-zA-Z]+[-._+&])*[0-9a-zA-Z]+@([-0-9a-zA-Z]+[.])+[a-zA-Z]{2,6}$/i", $email)) {
			return true; }
		else {
			return false; }
	}
  
  /**
   * Aktualisiert den Wert $new_value des Datensatz $id
   * 
   * @param $new_value STR - Wert, der uebernommen werden soll
   * @param $id INT - ID des Datensatz, dessen Wert aktualisiert werden soll
   * 
   * @return BOOL Ergebnis
   */
  public function setValue($new_value, $id) {
  	$value = '';
  	$where = array();
  	$where[self::field_id] = $id;
  	$config = array();
  	if (!$this->sqlSelectRecord($where, $config)) {
  		$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
  		return false;
  	}
  	if (sizeof($config) < 1) {
  		$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tool_error_cfg_id, $id)));
  		return false;
  	}
  	$config = $config[0];
  	switch ($config[self::field_type]):
  	case self::type_array:
  		// Funktion geht davon aus, dass $value als STR uebergeben wird!!!
  		$worker = explode(",", $new_value);
  		$data = array();
  		foreach ($worker as $item) {
  			$data[] = trim($item);
  		};
  		$value = implode(",", $data);  			
  		break;
  	case self::type_boolean:
  		$value = (bool) $new_value;
  		$value = (int) $value;
  		break;
  	case self::type_email:
  		if ($this->validateEMail($new_value)) {
  			$value = trim($new_value);
  		}
  		else {
  			$this->setMessage(sprintf(tool_msg_invalid_email, $new_value));
  			return false;			
  		}
  		break;
  	case self::type_float:
  		$value = $this->str2float($new_value);
  		break;
  	case self::type_integer:
  		$value = $this->str2int($new_value);
  		break;
  	case self::type_url:
  	case self::type_path:
  		$value = $this->addSlash(trim($new_value));
  		break;
  	case self::type_string:
  		$value = (string) trim($new_value);
  		// Hochkommas demaskieren
  		$value = str_replace('&quot;', '"', $value);
  		break;
  	endswitch;
  	unset($config[self::field_id]);
  	$config[self::field_value] = (string) $value;
  	if (!$this->sqlUpdateRecord($config, $where)) {
  		$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
  		return false;
  	}
  	return true;
  } // setValue()
  
  /**
   * Gibt den angeforderten Wert zurueck
   * 
   * @param $name - Bezeichner 
   * 
   * @return WERT entsprechend des TYP
   */
  public function getValue($name) {
  	$result = '';
  	$where = array();
  	$where[self::field_name] = $name;
  	$config = array();
  	if (!$this->sqlSelectRecord($where, $config)) {
  		$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
  		return false;
  	}
  	if (sizeof($config) < 1) {
  		$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tool_error_cfg_name, $name)));
  		return false;
  	}
  	$config = $config[0];
  	switch ($config[self::field_type]):
  	case self::type_array:
  		$result = explode(",", $config[self::field_value]);
  		break;
  	case self::type_boolean:
  		$result = (bool) $config[self::field_value];
  		break;
  	case self::type_email:
  	case self::type_path:
  	case self::type_string:
  	case self::type_url:
  		$result = (string) utf8_decode($config[self::field_value]);
  		break;
  	case self::type_float:
  		$result = (float) $config[self::field_value];
  		break;
  	case self::type_integer:
  		$result = (integer) $config[self::field_value];
  		break;
  	default:
  		$result = utf8_decode($config[self::field_value]);
  		break;
  	endswitch;
  	return $result;
  } // getValue()
  
  public function checkConfig() {
  	foreach ($this->config_array as $item) {
  		$where = array();
  		$where[self::field_name] = $item[1];
  		$check = array();
  		if (!$this->sqlSelectRecord($where, $check)) {
  			$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
  			return false;
  		}
  		if (sizeof($check) < 1) {
  			// Eintrag existiert nicht
  			$data = array();
  			$data[self::field_label] = $item[0];
  			$data[self::field_name] = $item[1];
  			$data[self::field_type] = $item[2];
  			$data[self::field_value] = $item[3];
  			$data[self::field_description] = $item[4];
  			if (!$this->sqlInsertRecord($data)) {
  				$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
  				return false;
  			}
  		}
  	}
  	return true;
  }
	  
} // class dbPollCfg


?>