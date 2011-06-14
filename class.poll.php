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
	const field_access					= 'quest_access';
	const field_kit_groups			= 'quest_kit_groups';
	const field_date_start			= 'quest_date_start';
	const field_date_end				= 'quest_date_end';
	const field_status					= 'quest_status';
	const field_show_results		= 'quest_show_results';
	const field_release					= 'quest_release';
	const field_timestamp				= 'quest_timestamp';

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

?>