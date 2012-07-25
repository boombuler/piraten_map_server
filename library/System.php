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
	
	private static $defaultconf = array('url' => '', 
							   		    'send_mail_adr' => '', 
									    'curl_path' => '/usr/bin/curl', 
									    'mysql_server' => 'localhost', 
									    'mysql_user' => '', 
									    'mysql_password' => '', 
									    'mysql_database' => '',
									    'tbl_prefix' => 'plakate_',
										'use_ssl' => true, 
									    'allow_view_public' => true, 
									    'debug' => false,
										'max_resolve_count' => 5, 
										'start_zoom' => 6,
										'start_lat' => 53.37, 
										'start_lon' => 10.39,
										'language' => 'de_DE');

									 
    private static $alltables = null;

    public static function init()
    {
        if (self::$initiated) {
            return;
        }

        session_start();
        spl_autoload_register('System::autoload');        
        self::readConfiguration();
		self::setLanguage(self::getConfig('language'));
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
		self::$configuration = self::$defaultconf;
		$path = dirname(dirname(__FILE__)) . '/settings.php';
		if (file_exists($path)) {
			include $path;
			self::$configuration = array_merge(self::$defaultconf, get_defined_vars());
		}
    }

    public static function getConfig($varname)
    {
        return self::$configuration[$varname];
    }
	
	public static function setConfig($varname, $value)
	{
		// Check if the varname is a valid config name.
		if (array_key_exists($varname, self::$defaultconf)) {
			self::$configuration[$varname];
			// Write the configuration:
			$outStr = '<?php\n';
			foreach (self::$configuration as $key => $value) {
				$outStr .= '$' . $key . ' = ' . var_export($value) . ';\n';
			}
			$path = dirname(dirname(__FILE__)) . '/settings.php';
			file_put_contents($path, $outStr);
		}
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

    public static function tableExists($tableName)
    {
        if (self::$alltables == null) {
            $res = self::query("SHOW TABLES LIKE '". self::getConfig('tbl_prefix') . "%'"); // Fetch all tables with the prefix.
            self::$alltables = $res->fetchAll(PDO::FETCH_COLUMN, 0);
        }
        return in_array(System::getConfig('tbl_prefix') . $tableName, self::$alltables);
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
	
	public static function setLanguage($language)
	{
		$domain = "messages";

		putenv("LANG=" . $language);
		setlocale(LC_ALL, $language . '.UTF-8');
		bindtextdomain($domain, dirname(dirname(__FILE__)) . '/locale');
		bind_textdomain_codeset($domain, 'UTF-8');
		textdomain($domain);
	}
}

System::init();