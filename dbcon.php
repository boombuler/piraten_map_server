<?php
  require_once('library/System.php');

  function openDB() {

    static $dbh = null;
    if ($__dbcon_dbh == null)
    {
        try {
            $server = System::getConfig('mysql_server');
            $database = System::getConfig('mysql_database');
            $dbh = new PDO("mysql:host=$server;dbname=$database", System::getConfig('mysql_user'), System::getConfig('mysql_password'), array(
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