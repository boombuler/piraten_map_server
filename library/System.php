<?php

require_once('dbcon.php');

/**
 * Singleton containing system whide important information.
 */
class System
{
  /**
   * @var System
   */
  private static $instance = null;

  private $configuration = array();
  
  /**
   * @var mysqli
   */
  private $db = null;

  public static function getInstance()
  {
    if (!$instance) {
      self::$instance = new System();
    }

    return self::$instance;
  }

  public static function init()
  {
    return self::getInstance();
  }

  public static function __callStatic($method, $arguments)
  {
    if (!is_callable(array(self::getInstance(), $method))) {
      throw new Exception('Method ' . $method . ' does not exist.');
    }

    return call_user_func(array(self::getInstance(), $method), $arguments);
  }

  public static function autoload($classname)
  {
    $path = dirname(__FILE__);
    $path .= str_replace('_', '/', $classname) . '.php';
    if (!file_exists($path)) {
      throw new Exception('Class ' . $classname . ' not Found');
    }

    include_once($path);
  }

  private function __construct()
  {
    spl_autoload_register('System::autoload');
    $this->readConfiguration();
  }

  private function readConfiguration()
  {
    include 'settings.php';

    $this->configuration = get_defined_vars();
  }

  public function getConfig($varname)
  {
    return $this->configuration[$varname];
  }

  public function query($query, $arguments = null)
  {
    $db = openDB();
    $qry = $db->prepare($query);
    $qry->execute($arguments);
    return $qry;
  }

  public function getDb()
  {
    return $this->db;
  }
}

System::init();

?>