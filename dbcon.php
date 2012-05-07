<?php
  require_once('settings.php');

  function openDB() {
    global $mysql_password, $mysql_user, $mysql_server, $mysql_database;
    static $dbh = null;
    if ($__dbcon_dbh == null)
    {
        try {
            $dbh = new PDO("mysql:host=$mysql_server;dbname=$mysql_database", $mysql_user, $mysql_password, array(
                PDO::ATTR_PERSISTENT => true,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
            ));
            $dbh->exec("SET CHARACTER SET utf8");
            $dbh->exec("SET character_set_connection = utf8");
            $dbh->exec("SET character_set_results = utf8");
            $dbh->exec("SET character_set_client = utf8");
        } catch (PDOException $e) {
            if ($debug)
                print "Error!: " . $e->getMessage() . "<br/>";
            die();
        }
    }
    return $dbh;
  }
?>