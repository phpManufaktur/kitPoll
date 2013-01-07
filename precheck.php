<?php

/**
 * kitPoll
 *
 * @author phpManufaktur <team@phpmanufaktur.de>
 * @link http://phpmanufaktur.de
 * @copyright 2011 - 2013
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */


// Checking Requirements

$PRECHECK['PHP_VERSION'] = array('VERSION' => '5.3.3', 'OPERATOR' => '>=');

global $database;
$sql = "SELECT `value` FROM `".TABLE_PREFIX."settings` WHERE `name`='default_charset'";
$result = $database->query($sql);
if ($result) {
  $data = $result->fetchRow(MYSQL_ASSOC);
  $PRECHECK['CUSTOM_CHECKS'] = array(
    'Default Charset' => array(
      'REQUIRED' => 'utf-8',
      'ACTUAL' => $data['value'],
      'STATUS' => ($data['value'] === 'utf-8')
    )
  );
}


?>