<?php
session_start();
require_once('dbcon.php');

/**
 * Singleton containing system whide important information.
 */
class System
{
  /**
   * @var bool
   */
  private static $initiated = false;

  private static $configuration = array();

  public static function init()
  {
    if (self::$initiated) {
      return;
    }

    spl_autoload_register('System::autoload');
    setlocale(LC_ALL, 'de_DE.UTF-8');
    self::readConfiguration();
    self::$initiated = true;
  }

  public static function autoload($classname)
  {
    $path = dirname(__FILE__) . '/' . str_replace('_', '/', $classname) . '.php';
    if (!file_exists($path)) {
      throw new Exception('Class ' . $classname . ' not Found');
    }

    include_once($path);
  }

  private static function readConfiguration()
  {
    include 'settings.php';

    self::$configuration = get_defined_vars();
  }

  public static function getConfig($varname)
  {
    return self::$configuration[$varname];
  }

  public static function query($query, $arguments = null)
  {
    $db = openDB();
    $qry = $db->prepare($query);
    $qry->execute($arguments);
    return $qry;
  }

  public static function prepare($query)
  {
    return openDB()->prepare($query);
  }

  public static function lastInsertId()
  {
    return openDB()->lastInsertId();
  }

  public static function getPosterFlags($key = null) {
    $flags = self::getConfig('poster_flags');
    if ($key == null) {
        return $flags;
    } else {
        return $flags[$key];
    }
  }

  public static function getUserData() {
      $usertyp = $_SESSION['sidusertype'];
      if (!isset($usertyp))
          return null;
      return array(
          'username' => $_SESSION['siduser'],
          'usertype' => $usertyp,
          'admin' => isAdmin()
      );
  }

  public static function canSendMails() {
     return self::getConfig('send_mail_adr') != '';
  }
}

System::init();

?>