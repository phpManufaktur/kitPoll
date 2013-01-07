<?php

/**
 * kitPoll
 *
 * @author phpManufaktur <team@phpmanufaktur.de>
 * @link http://phpmanufaktur.de
 * @copyright 2011 - 2013
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

include 'vendor/Autoloader.php';

use phpManufaktur\kitPoll\backend as Backend;

define('ADDON_PATH', __DIR__);
define('VENDOR_PATH', __DIR__.'/vendor');

$Tool = new Backend\Tool();
$Tool->Hello();