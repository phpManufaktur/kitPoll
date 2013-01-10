<?php

namespace phpManufaktur\kitPoll\Setup;

// need the I18n service
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\MessageSelector;
use Symfony\Component\Translation\Loader\ArrayLoader;

// need Doctrine DBAL
use Doctrine\Common\ClassLoader;
require VENDOR_PATH.'/Doctrine/Common/ClassLoader.php';

class Install {

  protected $db = null;
  protected $translator = null;
  protected $classLoader = null;

  public function __construct() {
    $this->classLoader = new ClassLoader('Doctrine', VENDOR_PATH);
    $this->classLoader->register();
  } // __construct()

  public function action() {
    $config = new \Doctrine\DBAL\Configuration();
    //..
    $connectionParams = array(
        'dbname' => 'dev_test',
        'user' => 'test',
        'password' => 'f2QF6dQW9ahSvncKlzVc',
        'host' => 'localhost',
        'driver' => 'pdo_mysql',
    );
    $conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);

    $sql = "SELECT * FROM lep_mod_kit_countries";
    $stmt = $conn->query($sql); // Simple, but has several drawbacks
    while ($row = $stmt->fetch()) {
      echo $row['land_name']."<br>";
    }

  }

} // class Install