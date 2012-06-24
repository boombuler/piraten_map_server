<?php
/**
 * Singleton containing system whide important information.
 */
class System
{
    /**
     * @var bool
     * @static
     */
    private static $initiated = false;

    /**
     * @var array contains the current system settings
     * @static
     */
    private static $configuration = array();

    public static function init()
    {
        if (self::$initiated) {
            return;
        }

        session_start();
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

    private static function getDB()
    {
        static $dbh = null;
        if ($dbh == null) {
            try {
                $server = self::getConfig('mysql_server');
                $database = self::getConfig('mysql_database');
                $dbh = new PDO("mysql:host=$server;dbname=$database",
                    self::getConfig('mysql_user'),
                    self::getConfig('mysql_password'),
                    array(
                        PDO::ATTR_PERSISTENT => true,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                    ));
                $dbh->exec('SET CHARACTER SET utf8');
                $dbh->exec('SET character_set_connection = utf8');
                $dbh->exec('SET character_set_results = utf8');
                $dbh->exec('SET character_set_client = utf8');
            } catch (PDOException $e) {
                if (self::getConfig('debug'))
                    die("Error!: " . $e->getMessage() . "<br/>");
                die();
            }
        }
        return $dbh;
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

    /**
     *
     * @param string $query
     * @param array $arguments
     * @return PDOStatement
     */
    public static function query($query, array $arguments = null)
    {
        try {
            $db = self::getDB();
            $qry = $db->prepare($query);
            $qry->execute($arguments);
            return $qry;
        } catch (Exception $e) {
            if (self::getConfig('debug')) {
                print $query;
            }
            throw $e;
        }
    }

    public static function prepare($query)
    {
        return self::getDB()->prepare($query);
    }

    public static function lastInsertId()
    {
        return self::getDB()->lastInsertId();
    }

    public static function canSendMails()
    {
        return self::getConfig('send_mail_adr') != '';
    }
}

System::init();