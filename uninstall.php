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
 
// include language file
if(!file_exists(WB_PATH .'/modules/kit_tools/languages/' .LANGUAGE .'.php')) {
	require_once(WB_PATH .'/modules/kit_tools/languages/DE.php'); // Vorgabe: DE verwenden 
	if (!defined('KIT_POLL_LANGUAGE')) define('KIT_POLL_LANGUAGE', 'DE');
}
else {
	require_once(WB_PATH .'/modules/kit_tools/languages/' .LANGUAGE .'.php');
	if (!defined('KIT_POLL_LANGUAGE')) define('KIT_POLL_LANGUAGE', LANGUAGE); 
}

// include language file for kitPoll
if(!file_exists(WB_PATH .'/modules/'.basename(dirname(__FILE__)).'/languages/' .LANGUAGE .'.php')) {
	require_once(WB_PATH .'/modules/'.basename(dirname(__FILE__)).'/languages/DE.php'); // Vorgabe: DE verwenden 
	if (!defined('KIT_POLL_LANGUAGE')) define('KIT_POLL_LANGUAGE', 'DE'); 
}
else {
	require_once(WB_PATH .'/modules/'.basename(dirname(__FILE__)).'/languages/' .LANGUAGE .'.php');
	if (!defined('KIT_POLL_LANGUAGE')) define('KIT_POLL_LANGUAGE', LANGUAGE);
}

if (!class_exists('dbconnectle')) 				require_once(WB_PATH.'/modules/dbconnect_le/include.php');
require_once(WB_PATH.'/modules/kit_poll/class.poll.php');

global $admin;

$tables = array('dbPollQuestion', 'dbPollAnswer', 'dbPollLog', 'dbPollTableSort', 'dbPollCfg');
$error = '';

foreach ($tables as $table) {
	$delete = null;
	$delete = new $table();
	if ($delete->sqlTableExists()) {
		if (!$delete->sqlDeleteTable()) {
			$error .= sprintf('[UNINSTALL] %s', $delete->getError());
		}
	}
}

// Prompt Errors
if (!empty($error)) {
	$admin->print_error($error);
}

?>